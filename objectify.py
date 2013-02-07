import gnosis.xml.objectify # standard package used for conversion of xml structure to Pythonic objects
import StringIO # standard package used for creating file-like object out of string
import sys
import inspect
import pdb

XO_IGNORE=['PCDATA']  # ignored elements on XML_write operations

class XTSM_core(object):
    """
    Default Class for all elements appearing in an XTSM tree
    """
    scope={}  # dictionary of names:values for parsed variables in element's scope  
    scoped=False  # records whether scope has been determined
    scopePeers={} # contains a list of {TargetType,TargetField,SourceField} which are scope-peers, to be found by matching TargetType objects with TargetField's value equal to SourceField's value

    def __init__(self,value=None):
        self._seq=[]
        if value==None:
            self.PCDATA=''
        else:
            self.PCDATA=value
            self._seq={value}
        self.__parent__=None

    def insert(self,node,pos=0):
        "inserts a node node at the position pos into XTSM_core object"
        node.__parent__=self        
        if pos < 0: pos=0
        if pos > self._seq.__len__(): pos=self._seq.__len__()
        nodeType=type(node).__name__.split('_XO_')[-1]
        if hasattr(self, nodeType):  # begin insertion process
            if getattr(self,nodeType).__len__() > 1:
                getattr(self,nodeType).append(node)
                getattr(self,'_seq').insert(pos,node)
            else:
                setattr(self,nodeType,[getattr(self,nodeType),node])
                getattr(self,'_seq').insert(pos,node)
        else:
            setattr(self, nodeType, node)
            getattr(self,'_seq').insert(pos,node)
        return node
    
    def addAttribute(self,name,value):
        "adds an (XML) attribute to the node with assigned name and value"
        setattr(self,name,value)
        return getattr(self,name)
        
    def getOwnerXTSM(self):
        """
        Returns top-level XTSM element
        """
        if self.__parent__:
            return self.__parent__.getOwnerXTSM()
        else:
            return self
    
    def getChildNodes(self):
        if (type(self._seq).__name__=='NoneType'): return []
        return [a for a in self._seq if type(a)!=type(u'')]
    
    def getItemByFieldValue(self,itemType,itemField,itemFieldValue):
        """
        Returns the first subelement of itemType type with field itemField equal to itemFieldValue
        entirely untested
        """
        if hasattr(self,itemType):
            for item in getattr(self,itemType): # advance to matching element
                if getattr(item,itemField).PCDATA==itemFieldValue: 
                    return item
        for subelm in self.getChildNodes():
            item=subelm.getItemByFieldValue(itemType,itemField,itemFieldValue)
            if item!=None: return item
        return None # otherwise return the match
        
    def parse(self):
        """
        parses the content of the node if the node has only 
        textual content. 
        ->>if it has parsable subnodes, it will NOT parse them -(NEED TO TAG PARSABLE NODES)
        this is done within the node's scope
        side-effect: if scope not already built, will build
        entirely untested
        """
        if (not hasattr(self,'PCDATA')): return None  # catch unparsable
        if (self.PCDATA==None): return None
        if type(self.PCDATA)!=type(u''): return None
        suc=False        
        try:        # try to directly convert to floating point
            self._parseValue=float(self.PCDATA)
            suc=True
        except ValueError:    # if necessary, evaluate as expression
            if (not self.scoped): self.buildScope()  # build variable scope
            try: 
                self._parseValue=eval(self.PCDATA,__builtins__.__dict__,self.scope)  # evaluate expression
                suc=True
            except NameError as NE:
                self.addAttribute('parse_error',NE.message)
        if suc:
            self.addAttribute('current_value',str(self._parseValue))  # tag the XTSM
            return self._parseValue
        else: return None
    
    def buildScope(self):
        """
        constructs the parameter scope of this node as dictionary of variable name, value
        first collects ancestors' scopes starting from eldest
        then collects scopes of peers defined in scopePeers
        then collects parameter children 
        those later collected will shadow/overwrite coincident names
        entirely untested
        """
        if self.__parent__: # if there is a parent, get its scope first (will execute recursively up tree)
            self.__parent__.buildScope()
            self.scope.update(self.__parent__.scope) # append parent's scope (children with coincident parameters will overwrite/shadow)
        if (not self.scoped):
            if hasattr(self,'scopePeers'):
                root=self.getOwnerXTSM()
                for peerRelation in self.scopePeers:
                    peerElm=root.getItemByFieldValue(peerRelation[0],peerRelation[1],getattr(self,peerRelation[2]).PCDATA) # loop through scope peers
                    if (peerElm != None):
                        if (not hasattr(peerElm,'scoped')): peerElm.buildScope() # build their scopes if necessary
                        self.scope.update(peerElm.scope) # append their scopes
            if hasattr(self,'Parameter'):
                for parameter in self.Parameter: # collect/parse parameter children
                    if (not hasattr(parameter,'_parseValue')): parameter.Value[0].parse()
                    self.scope.update({parameter.Name.PCDATA: parameter.Value[0]._parseValue})
        self.scoped=True # mark this object as scoped
        return None

    def get_tag(self):
        return type(self).__name__.split('_XO_')[-1]
        
    def set(self, value):
        self.PCDATA=str(value)
        return None
        
    def remove_all(self, name):
        delattr(self,name)
        
        pass

    def write_xml(self, out=None, tablevel=0, whitespace='True'):
        """
        Serialize an _XO_ object back into XML to stream out; if no argument 'out' supplied, returns string
        If tablevel is supplied, xml will be indented by level.  If whitespace is set to true, original whitespace
        will be preserved.
        """
        global XO_IGNORE
        mode=False
        newline=False
        firstsub=True
        # if no filestream provided, create a stream into a string (may not be efficient)
        if (not out):
            mode=True
            out=StringIO.StringIO()
        # write tag opening
        out.write(tablevel*"  "+"<%s" % self.get_tag())
        # attribute output; ignore any listed in global XO_IGNORE
        for attr in self.__dict__:
            if (isinstance(self.__dict__[attr], basestring)):
                if (attr in XO_IGNORE): continue 
                out.write(' ' + attr + '="' + self.__dict__[attr] + '"')
        out.write('>')
        # write nodes in original order using _XO_ class _seq list
        if self._seq:            
            for node in self._seq:
                if isinstance(node, basestring):
                    if (not whitespace):
                        if node.isspace():
                            pass
                        else: 
                            out.write(node)                            
                    else: out.write(node)
                else:
                    if firstsub:
                        out.write("\n")
                        firstsub=False
                    node.write_xml(out,0 if (tablevel==0) else (tablevel+1),whitespace)
                    newline=True
        # close XML tag
        if newline:
            out.write(tablevel*"  ")
        out.write("</%s>\n" % self.get_tag())
        # if mode is stringy, return string        
        if mode:
            nout=out.getvalue()
            out.close()
            return nout
        return None

class XTSM_element(gnosis.xml.objectify._XO_,XTSM_core):
    pass

class body(gnosis.xml.objectify._XO_,XTSM_core):
    def parseActiveSequence(self):
        """
        finds/parses SequenceSelector node, identifies active Sequence, initiates subnodeParsing
        """
        if self.SequenceSelector:
            if (not self.SequenceSelector[0].current_value): self.SequenceSelector[0].parse() # parse SS if not done already
            self.getItemByFieldValue('Sequence','Name',self.SequenceSelector[0].current_value).sowTimingEdges() # identify active sequence by name and begin collection
        else: pass # needs error trapping if no sequenceSelector node exists
        return None

class Sequence(gnosis.xml.objectify._XO_,XTSM_core):
    def sowTimingEdges(self):
        """
        Performs a first pass through all edges and intervals, parsing and collecting all ((Start/ /End)time/value/channel) entries necessary to arbitrate clocking edges
        """
        for subsequence in self.SubSequence:
            subsequence.sowTimingEdges()
        return None     

class ChannelMap(gnosis.xml.objectify._XO_,XTSM_core):
    """
    see http://google.com
    """
    pass

class Edge(gnosis.xml.objectify._XO_,XTSM_core):
    scopePeers=[['Channel','ChannelName','OnChannel']]
    def proffer(self,startTime):
        t=self.Time[0].parse()+startTime
        v=self.Value[0].parse()
        c=self.OnChannel[0]
        return [[tg,c,t,v]]
        
class Interval(gnosis.xml.objectify._XO_,XTSM_core):
    scopePeers=[['Channel','ChannelName','OnChannel']]

class OnChannel(gnosis.xml.objectify._XO_,XTSM_core):
    def getTimingGroupIndex(self):
        """
        This should return the timingGroup number and and index of the channel.
        Unfinished!
        """
        [tg,c]=self.getOwnerXTSM().head[0].ChannelMap[0].getChannelIndices(self.PCDATA)

class Parameter(gnosis.xml.objectify._XO_,XTSM_core):
    def __init__(self, name=None, value=None):
        XTSM_core.__init__(self)
        if name!=None:
            self.insert(gnosis.xml.objectify._XO_Name(name))
        if value!=None:
            self.insert(gnosis.xml.objectify._XO_Value(value))
        return None

# override the objectify default class
gnosis.xml.objectify._XO_ = XTSM_element
# identify all XTSM classes defined above, override the objectify _XO_ subclass for each
allclasses=inspect.getmembers(sys.modules[__name__],inspect.isclass)
XTSM_Classes=[tclass[1] for tclass in allclasses if (issubclass(getattr(sys.modules[__name__],tclass[0]),getattr(sys.modules[__name__],'XTSM_core')))]
for XTSM_Class in XTSM_Classes:
    setattr(gnosis.xml.objectify, "_XO_"+XTSM_Class.__name__, XTSM_Class)
# parse the XML file
xml_obj = gnosis.xml.objectify.XML_Objectify('c:/wamp/vortex/sequences/12-1-2012/sampling_added.xtsm')
py_obj = xml_obj.make_instance()
py_obj.insert(Parameter(u'shotnumber',u'23'))