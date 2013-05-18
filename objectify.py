"""
What is next:
        start outputting digital control strings: 1)standard DIO strings and 2) delay train strings
"""


import gnosis.xml.objectify # standard package used for conversion of xml structure to Pythonic objects
import StringIO # standard package used for creating file-like object out of string
import sys
import inspect
import pdb, traceback, sys, code
import uuid
import numpy
import scipy
import math

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
            
    def getFirstParentByType(self,tagtype):
        """
        Searches up the XTSM tree for the first ancestor of the type tagtype,
        returning the match, or None if it reaches toplevel without a match
        """
        if self.get_tag()==tagtype:
            return self
        else:
            if self.__parent__:
                return self.__parent__.getFirstParentByType(tagtype)
            else: return None
        
    def getChildNodes(self):
        """
        Returns all XTSM children of the node as a list
        """
        if (type(self._seq).__name__=='NoneType'): return []
        return [a for a in self._seq if type(a)!=type(u'')]
        
    def getDescendentsByType(self,targetType):
        """
        Returns a list of all descendents of given type
        """
        res=[]
        for child in self.getChildNodes():
            if hasattr(child,'getDescendentsByType'):
                res.extend(child.getDescendentsByType(targetType)) 
        if hasattr(self,targetType): res.extend(getattr(self,targetType))
        return res
    
    def getItemByFieldValue(self,itemType,itemField,itemFieldValue,multiple=False):
        """
        Returns the first XTSM subelement of itemType type with field itemField equal to itemFieldValue
        Note: will search all descendent subelements!
        """
        hits=set()
        if hasattr(self,itemType):
            for item in getattr(self,itemType): # advance to matching element
                if getattr(item,itemField).PCDATA==itemFieldValue:
                    if multiple: hits=hits.union({item})
                    else: return item
        for subelm in self.getChildNodes():
            if not hasattr(subelm,'getItemByFieldValue'): continue
            item=subelm.getItemByFieldValue(itemType,itemField,itemFieldValue,multiple)
            if item!=None and item!={}:
                if multiple: hits=hits.union(item) 
                else: return item
        if multiple: return hits
        else: return None 

    def getItemByFieldValueSet(self,itemType,FieldValueSet):
        """
        Returns all subelements of type which have all given fields with specified values
        FieldValueSet should be a dictionary of field:value pairs
        """
        for step,pair in enumerate(FieldValueSet.items()):
            if step==0: hits=self.getItemByFieldValue(itemType,pair[0],pair[1],True)
            else: hits=hits.intersection(self.getItemByFieldValue(itemType,pair[0],pair[1],True))
            if len(hits)==0: return hits
        return hits

    def getItemByAttributeValue(self,attribute,Value,multiple=False):
        """
        Returns the first subelement with (non-XTSM) attribute of specified value
        if multiple is true returns a list of all such elements
        """
        hits=set()
        if hasattr(self,attribute):
            if getattr(self,attribute)==Value: 
                if multiple: hits=hits.union({self})
                else: return self
        for subelm in self.getChildNodes():
            a=subelm.getItemByAttributeValue(attribute,Value,multiple) 
            if a: 
                if multiple: hits=hits.union({a})
                else: return a
        if multiple: return hits
        else: return None
                
    def parse(self,additional_scope=None):
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
            if additional_scope!=None:       
                tscope=dict(self.scope.items()+additional_scope.items())
            else: tscope=self.scope
            try: 
                self._parseValue=eval(self.PCDATA,__builtins__.__dict__,tscope)  # evaluate expression
                suc=True
            except NameError as NE:
                self.addAttribute('parse_error',NE.message)
        if suc:
            if hasattr(self._parseValue,'__len__'):
                if len(self._parseValue)<2:
                    self.addAttribute('current_value',str(self._parseValue))
            else: self.addAttribute('current_value',str(self._parseValue))  # tag the XTSM
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
        """
        returns the tagname of the node
        """
        return type(self).__name__.split('_XO_')[-1]
        
    def set_value(self, value):
        """
        sets the textual value of a node
        """
        pos=0
        if (self.PCDATA in self._seq):
            pos=self._seq.index(unicode(str(value)))
            self._seq.remove(unicode(str(value)))
        self._seq.insert(pos,unicode(str(value)))
        self.PCDATA=unicode(str(value))
        return self
        
    def generate_guid(self, depth=None):
        """
        generate a unique id number for this element and all descendents to
        supplied depth; if no depth is given, for all descendents.
        if element already has guid, does not generate a new one
        """
        if (not hasattr(self,'guid')):
            self.addAttribute('guid',str(uuid.uuid1()))
        if (depth == None):
            for child in self.getChildNodes(): child.generate_guid(None)
        else:
            if (depth>0):
                for child in self.getChildNodes(): child.generate_guid(depth-1)
        return
    
    def generate_guid_lookup(self,target=True):
        """
        creates a lookup list of guid's for all descendent elements that possess one
        adds the result as an element 'guid_lookup' if target input is default value True
        """
        if hasattr(self,'guid'): 
            guidlist=[self.guid]
        else:
            guidlist=[]
        for child in self.getChildNodes():
            guidlist.extend(child.generate_guid_lookup(False))
        if target: self.guid_lookup=guidlist
        return guidlist
    
    def generate_fasttag(self,running_index,topelem=None):
        """
        creates a lookup index and reference table
        """
        self._fasttag=running_index
        if topelem==None: 
             topelem=self
             topelem._fasttag_dict={running_index:self}
        else: topelem._fasttag_dict.update({running_index:self}) 
        running_index+=1
        for child in self.getChildNodes(): running_index=child.generate_fasttag(running_index,topelem)
        return running_index
    
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
        
    def countDescendents(self,tagname):
        """
        Counts number of descendent nodes of a particular type
        """
        cnt=0
        for child in self.getChildNodes():
            cnt+=child.countDescendents(tagname)
        if hasattr(self,tagname):
            return cnt+getattr(self,tagname).__len__()
        else: return cnt
        
    def get_childNodeValue_orDefault(self,tagType,default):
        """
        gets the value of the first child Node of a given tagtype, 
        or returns a default value if the node does not exist
        or if it is empty
        """
        if not hasattr(self,tagType): return default
        val=getattr(self,tagType).PCDATA     
        if val != u'': return val
        else: return default
        
        
class XTSM_element(gnosis.xml.objectify._XO_,XTSM_core):
    pass

class ClockPeriod(gnosis.xml.objectify._XO_,XTSM_core):
    pass

class body(gnosis.xml.objectify._XO_,XTSM_core):
    def parseActiveSequence(self):
        """
        finds/parses SequenceSelector node, identifies active Sequence, initiates subnodeParsing
        """
        if self.SequenceSelector:
            if (not self.SequenceSelector[0].current_value): self.SequenceSelector[0].parse() # parse SS if not done already
            self.getItemByFieldValue('Sequence','Name',self.SequenceSelector[0].current_value).collectTimingProffers() # identify active sequence by name and begin collection
        else: pass # needs error trapping if no sequenceSelector node exists
        return None

class TimingProffer():
    """
    A helper class defined to aid XTSM parsing; holds data about request for timing events
    from edges, intervals... Provides methods for compromise timing when requests
    are conflictory or require arbitration of timing resolution, etc...
    """
    data={}  # a dictionary to be filled with arrays for each type of timing request: edge,interval, etc...
    valid_entries={'Edge':0,'Interval':0} # will hold the number of elements in the array which are currently valid data
    data_per_elem={'Edge':5,'Interval':7} # the number of elements needed to understand a request: time, value, timing group, channel...
    data_types={'Edge':numpy.float32,'Interval':numpy.float32}  # the data_type to store

    def __init__(self,generator):
        """
        Creates the timingProffer from a root element (such as a sequence)
        """
        # the following loops through all elements defined above as generating
        # proffers, creates a blank array to hold proffer data, stored in data dictionary
        for gentype in self.valid_entries.keys():
            self.data.update({gentype:numpy.empty([generator.countDescendents(gentype),self.data_per_elem[gentype]],self.data_types[gentype])})
    
    def insert(self,typename,elem):
        self.data[typename][self.valid_entries[typename]]=elem
        self.valid_entries[typename]=self.valid_entries[typename]+1        

class Emulation_Matrix():
    """
    A helper class defined to store result of hardware emulation for a sequence
    should follow constructs at line 674 of IDL version
    NOT STARTED
    """
    pass

class ParserOutput(gnosis.xml.objectify._XO_,XTSM_core):
    """
    An XTSM class to store the control array structure generated by the parser
    """
    pass

class GroupNumber(gnosis.xml.objectify._XO_,XTSM_core):
    pass

class ControlData(gnosis.xml.objectify._XO_,XTSM_core):
    """
    An XTSM class to store the control arrays generated by the parser
    """
class ControlArray(gnosis.xml.objectify._XO_,XTSM_core):
    """
    An XTSM class: control arrays generated by the parser
    """
    def construct(self,sequence,channelMap,tGroup):
        """
        constructs the timing control array for a timingGroup in given sequence
        sequence should already have collected TimingProffers
        """
        # bind arguments to object
        self.sequence=sequence
        self.channelMap=channelMap
        self.tGroup=tGroup
        # get biographical data about the timing group: number of channels, its clock period 
        tGroupNode=channelMap.getItemByFieldValue('TimingGroupData','GroupNumber',str(tGroup))
        self.clockgenresolution=channelMap.tGroupClockResolutions[tGroup]
        self.numchan=int(tGroupNode.ChannelCount.PCDATA)
        self.scale=float(tGroupNode.Scale.PCDATA)     
        self.ResolutionBits=int(tGroupNode.ResolutionBits.PCDATA)
        self.bytespervalue=int(math.ceil(self.ResolutionBits/8.))
        if (tGroupNode.ClockedBy.PCDATA != u'self'):
            self.clocksourceNode=channelMap.getChannel(tGroupNode.ClockedBy.PCDATA)
            self.clocksourceTGroup=channelMap.getItemByFieldValue('TimingGroupData'
                                                                  ,'GroupNumber',
                                                                  self.clocksourceNode.TimingGroup.PCDATA)
            parentgenresolution=channelMap.tGroupClockResolutions[int(self.clocksourceTGroup.GroupNumber.PCDATA)]
            if hasattr(self.clocksourceTGroup,'DelayTrain'): self.bytesperrepeat=0
            else: self.bytesperrepeat=4
            if self.ResolutionBits==u'1': self.ResolutionBits=0
        else: 
            parentgenresolution=self.clockgenresolution
            self.bytesperrepeat=4
        # Find the end time for the sequence
        if hasattr(sequence,'endtime'): seqendtime=sequence.endtime
        else: seqendtime=sequence.get_endtime()
        # locate just the edges for this timing group        
        if len(sequence.TimingProffer.data['Edge'])>0:
            self.groupEdges=(sequence.TimingProffer
                             .data['Edge'])[(((sequence.TimingProffer
                                            .data['Edge'])[:,0]==int(tGroup)).nonzero())[0],]
        else: self.groupEdges=sequence.TimingProffer.data['Edge']
        if len(sequence.TimingProffer.data['Interval'])>0:
            self.groupIntervals=(sequence.TimingProffer
                                 .data['Interval'])[(((sequence.TimingProffer
                                            .data['Interval'])[:,0]==int(tGroup)).nonzero())[0],]
        else: self.groupIntervals=sequence.TimingProffer.data['Interval']
        # transpose to match IDL syntax
        self.groupEdges=self.groupEdges.transpose()
        self.groupIntervals=self.groupIntervals.transpose()
        # coerce timing edges to a multiple of the parent clock's timebase
        # (using the parent's resolution allows us to subresolution step.)
        self.groupEdges[2,:]=((self.groupEdges[2,:]/parentgenresolution).round())*parentgenresolution
        self.groupIntervals[2,:]=((self.groupIntervals[2,:]/parentgenresolution).round())*parentgenresolution
        self.groupIntervals[3,:]=((self.groupIntervals[3,:]/parentgenresolution).round())*parentgenresolution
        self.lasttimecoerced=float(math.ceil(seqendtime/self.clockgenresolution))*self.clockgenresolution
        # create a sorted list of all times at beginning or end of interval, at an edge, or beginning or start of sequence
        self.alltimes=numpy.unique(numpy.append(self.groupIntervals[2:4,],self.groupEdges[2,])) 
        self.alltimes=numpy.unique(numpy.append(self.alltimes,[0,self.lasttimecoerced]))
        # create a list of all update times necessary in the experiment 
        self.denseT=numpy.array([0])
        # loop through all windows between members of alltimes
        for starttime,endtime in zip(self.alltimes[0:-1],self.alltimes[1:]):
            # identify all intervals in this group active in this window
            if self.groupIntervals.size!=0:
                pre=(self.groupIntervals[2,]<=starttime).nonzero()
                post=(self.groupIntervals[3,]>starttime).nonzero()
                if ((len(pre)>0) and (len(post)>0)):
                    aInts=numpy.intersect1d(numpy.array(pre),numpy.array(post))
                    if aInts.size>0:
                        # find compromise timing resolution (minimum requested, coerced to parent clock multiple)
                        Tres=math.ceil(round(min(self.groupIntervals[5,aInts])/self.clockgenresolution,3))*self.clockgenresolution
                        # define the times in this window using universal time
                        T=numpy.linspace(starttime,endtime,(endtime-starttime)/Tres+1)
                        self.denseT=numpy.append(self.denseT,T)
                    else: self.denseT=numpy.append(self.denseT,starttime)   
                else: self.denseT=numpy.append(self.denseT,starttime)
            else: self.denseT=numpy.append(self.denseT,starttime)
        self.denseT=numpy.append(self.denseT,self.lasttimecoerced)
        self.denseT=numpy.unique(self.denseT)
        # sort edges and intervals by group index (channel), then by (start)time
        if self.groupEdges.size > 0:
            self.groupEdges=self.groupEdges[:,numpy.lexsort((self.groupEdges[2,:],self.groupEdges[1,:]))] 
        if self.groupIntervals.size > 0:
            self.groupIntervals=self.groupIntervals[:,numpy.lexsort((self.groupIntervals[2,:],self.groupIntervals[1,:]))]
        # now re-parse all edges and intervals to generate accurate values consistent with these times,
        # accumulate update values and times for all output changes on this timing group,
        # organized by channel, in time-order, and replace timingGroup number with the index of its update time
        numalledges=0
        # find out how many edges this timinggroup will have
        for intervalInd in self.groupIntervals[6,]:
            # get the interval, find how many samples it will generate
            interval=sequence._fasttag_dict[intervalInd]
            numalledges+=interval.expected_samples(self.denseT)
        numalledges+=self.groupEdges.shape[1]
        # create an array to hold all edges        
        self.intedges=numpy.empty([numalledges,5])
        # self.intedges[0:self.groupEdges.shape[1],]=self.groupEdges.transpose()
        edgeind=0 # create pointers for indexing
        allind=0
        lastChan=-1 # wrap around pointer for next for-loop
        lastStart=0
        self.Chanbreaks=numpy.array([])  # will point to beginning of each channel set in intedges
        for intervalInd in self.groupIntervals[6,]:
            # locate the interval 
            interval=sequence._fasttag_dict[intervalInd]
            intdata=self.groupIntervals[:,(self.groupIntervals[6,:]==intervalInd).nonzero()]
            if intdata[1].flatten()!=lastChan:
                for cs in range(lastChan+1,intdata[1].flatten()):
                    self.Chanbreaks=numpy.append(self.Chanbreaks,[allind])
                self.Chanbreaks=numpy.append(self.Chanbreaks,[allind])
            # find all edges on prior channels not inserted already
            addedgeindices1=(self.groupEdges[1,:]<intdata[1].flatten()).nonzero()[0]
            addedgeindices1=addedgeindices1[(addedgeindices1>=edgeind).nonzero()]
            # find and add all edges on this channel with time equal to or before interval start
            addedgeindices2=((self.groupEdges[1,:]==intdata[1].flatten())&(self.groupEdges[2,:]<=intdata[2].flatten())).nonzero()[0]            
            addedgeindices2=addedgeindices2[(addedgeindices2>=edgeind).nonzero()]
            # now add them and advance pointers
            addedges=self.groupEdges[:,numpy.concatenate((addedgeindices1,addedgeindices2))]
            self.intedges[allind:(allind+addedges.shape[1]),]=addedges.transpose()
            edgeind+=addedges.shape[1]
            allind+=addedges.shape[1]
            # now replace timingGroup numbers with update index
            self.intedges[allind:(allind+addedges.shape[1]):,0]=self.denseT.searchsorted(self.intedges[allind:(allind+addedges.shape[1]):,2])
            # add edges from interval itself
            numedges=interval.expected_samples(self.denseT)
            self.intedges[allind:(allind+numedges),]=interval.parse_harvest(self.denseT).transpose()
            allind+=numedges
            lastChan=(intdata[1]).flatten()
            lastStart=intdata[2].flatten()
        # finish adding edges which belong after the last interval
        if numalledges!=0:
            # find all edges on later channels not inserted already
            addedgeindices1=(self.groupEdges[1,:]>lastChan).nonzero()[0]
            addedgeindices1=addedgeindices1[(addedgeindices1>=edgeind).nonzero()]            
            # find and add all edges on this channel with time after the last interval's end
            addedgeindices2=((self.groupEdges[1,:]==lastChan)&(self.groupEdges[2,:]>lastStart)).nonzero()[0]            
            addedgeindices2=addedgeindices2[(addedgeindices2>=edgeind).nonzero()]
            # now add them and advance pointers
            addedges=self.groupEdges[:,numpy.concatenate((addedgeindices2,addedgeindices1))]
            # locate the channel breaks therein
            pdb.set_trace()
            self.intedges[allind:(allind+addedges.shape[1]),]=addedges.transpose()
            edgeind+=addedges.shape[1]
            allind+=addedges.shape[1]
            
        for chan in range(int(lastChan),self.numchan-1):
            self.Chanbreaks=numpy.append(self.Chanbreaks,[allind])
        # Convert times and values to integers according to resolutions
        #self.controlTV=(self.intedges[:,2:4]/[parentgenresolution,(self.scale/pow(2.,self.bytespervalue))]).astype(numpy.int32)
        #self.controlTV[:,1]+=pow(2.,self.bytespervalue-1)
        ### scale these into proper integers        
        
        finalTime=long(seqendtime/parentgenresolution)
        # Break these into channel control strings - first determine necessary size and define empty array
        # find which channels need start and end edges inserted        
        self.haveEdges=numpy.append((self.Chanbreaks[:-1]!=self.Chanbreaks[1:]),[self.Chanbreaks[-1]<self.intedges.shape[0]])  # channels that have some edges already (those that don't need both start and end values)
        self.needsStart=[]     # channels needing just a start value   
        self.needsEnd=[]     # channels needing just an end value
        for chan in (self.haveEdges.nonzero())[0]:   # for the edges that have values, determine if any are at start and end times, if not, mark as needing them
                if self.intedges[self.Chanbreaks[chan],2]!=0:
                    self.needsStart.append(chan)
                if chan<(self.numchan-1):
                    if self.Chanbreaks[chan+1]>=self.intedges.shape[0]:
                        self.needsEnd.append(chan)
                        continue
                    if self.intedges[self.Chanbreaks[chan+1]-1,2]!=finalTime:
                        self.needsEnd.append(chan)
                else: 
                    if self.intedges[-1,2]!=finalTime:
                        self.needsEnd.append(chan)
        # clockstring management: save the current timinggroup's clocking string to the ParserOutput node,
        # also find and eventually insert clocking strings for other timinggroups which should occur on a channel in the current timinggroup
        if not hasattr(sequence.ParserOutput,"clockStrings"):
            sequence.ParserOutput.clockStrings={}
        sequence.ParserOutput.clockStrings.update({tGroup:(self.denseT/parentgenresolution).astype('uint32')})
        self.cStrings={}  # dictionary of clock times for each channel that clocks another timingGroup
        clockedgesadded=0  # number of edges that will be added in swapping these in instead of explicitly defined edges already in self.intedges
        for channel in range(self.numchan):
            if channelMap.isClock(tGroup,channel):
                self.cStrings.update({channel:sequence.ParserOutput.clockStrings[channelMap.isClock(tGroup,channel)]})
                if channel!= self.numchan-1:
                    clockedgesadded+=len(self.cStrings[channel])-(self.Chanbreaks[channel+1]-self.Chanbreaks[channel])
                else: clockedgesadded+=len(self.cStrings[channel])-(self.intedges.shape[0]-self.Chanbreaks[channel])
        # calculate the total number of edges on the timingGroup
        addEdges=2*(self.numchan-(self.haveEdges.nonzero())[0].size)+len(self.needsEnd)+len(self.needsStart)+clockedgesadded
        # calculate length of control string for entire timing group in bytes
        self.tsbytes=(self.intedges.shape[0]+addEdges)*(self.bytesperrepeat+self.bytespervalue)+4*self.numchan+7  ### last two terms account for header and segment headers
        self.tGroup=tGroup
        self.channelMap=channelMap
        self.finalind=self.denseT.shape[0]
        if (self.ResolutionBits>1 and self.bytesperrepeat!=0):
            self.TimingStringConstruct('Analog_VR')
        if (self.ResolutionBits==1 and self.bytesperrepeat!=0):
            self.TimingStringConstruct('Digital_R')
        return self

    def TimingStringConstruct(self,ts_type='Analog_VR'):
        self.timingstring=numpy.ones(self.tsbytes,numpy.uint8)  # will hold timingstring
        # create the timingstring header
        self.timingstring[0:7]=numpy.append(
                                  numpy.asarray([self.numchan,self.bytespervalue,self.bytesperrepeat], dtype='<u1').view('u1'),
                                  numpy.asarray([self.denseT.size], dtype='<u4').view('u1'))
        tsptr=7  # a pointer to the current position for writing data into the timingstring
        if ts_type=='Digital_R':
            # sort edges by time, threshhold values to zero and one if greater or equal to 0.5
            self.intedges[:,3]=self.intedges[:,3]>=0.5
            # identify duplicates
            nonreps=((self.intedges[1:,3]!=self.intedges[0:-2,3]).nonzero())[0]+1
        if self.tGroup == 1: pdb.set_trace()
        # now output each channel's timingstring:
        for chan in range(self.numchan):
            if not chan in self.cStrings: # if not a clock
                if chan < (self.numchan-1):   # determine end index
                    end=self.Chanbreaks[chan+1]
                else: end=self.intedges.shape[0]
                # find the channel for data                
                chanObj=self.channelMap.getItemByFieldValueSet('Channel',{'TimingGroup':str(self.tGroup),'TimingGroupIndex':str(chan)})    # find the channel
                if chanObj : chanObj=chanObj.pop()                   
                length=(self.bytesperrepeat+self.bytespervalue)*(end-self.Chanbreaks[chan])
                addedges=0
                if not self.haveEdges[chan]: addedges+=2
                if self.needsStart.count(chan)>=1: addedges+=1
                if self.needsEnd.count(chan)>=1: addedges+=1
                # insert the edges as repeat, value pairs
                # first insert a first-edge if necessary
                iv=hv=''
                if hasattr(chanObj,'InitialValue'): iv=chanObj.InitialValue[0].parse()
                if iv=='' or iv==None: iv=0
                if hasattr(chanObj,'HoldingValue'): hv=chanObj.HoldingValue[0].parse()
                if hv=='' or hv==None: hv=0
                iv=iv*(pow(2.,8*self.bytespervalue-1)-1)/(self.scale/2.)+pow(2.,8*self.bytespervalue-1)
                hv=hv*(pow(2.,8*self.bytespervalue-1)-1)/(self.scale/2.)+pow(2.,8*self.bytespervalue-1)
                # first a header denoting this channel's length in bytes as 4bytes, LSB leading
                self.timingstring[tsptr:(tsptr+4)]=numpy.asarray([int(length+addedges*(self.bytesperrepeat+self.bytespervalue))], dtype='<u4').view('u1')
                tsptr+=4
                # now insert start edge if necessary
                if (self.needsStart.count(chan)>=1):
                    self.timingstring[tsptr:(tsptr+self.bytesperrepeat+self.bytespervalue)]=numpy.append(
                        numpy.asarray([iv], dtype='<u'+str(self.bytespervalue)).view('u1'),
                        numpy.asarray([self.intedges[self.Chanbreaks[chan],0]], dtype='<u'+str(self.bytesperrepeat)).view('u1'))
                    tsptr+=self.bytesperrepeat+self.bytespervalue
                # if there are no edges, insert start and end
                if not self.haveEdges[chan]:
                    self.timingstring[tsptr:(tsptr+self.bytesperrepeat+self.bytespervalue)]=numpy.append(
                        numpy.asarray([iv], dtype='<u'+str(self.bytespervalue)).view('u1'),
                        numpy.asarray([self.finalind], dtype='<u'+str(self.bytesperrepeat)).view('u1'))
                    tsptr+=(self.bytesperrepeat+self.bytespervalue)
                    self.timingstring[tsptr:(tsptr+self.bytesperrepeat+self.bytespervalue)]=numpy.append(
                        numpy.asarray([hv], dtype='<u'+str(self.bytespervalue)).view('u1'),
                        numpy.asarray([1], dtype='<u'+str(self.bytesperrepeat)).view('u1'))
                    tsptr+=(self.bytesperrepeat+self.bytespervalue)
                # if there are declared edges, insert them now
                if length>0:
                    # Perform channel hardware limit transformations here:
                    if hasattr(chanObj,'Calibration'): 
                        if not (chanObj.Calibration[0].PCDATA == '' or chanObj.Calibration[0].PCDATA == None):
                            V=self.intedges[self.Chanbreaks[chan]:end,3] # the variable V can be referenced in channel calibration formula
                            self.intedges[self.Chanbreaks[chan]:end,3]=eval(chanObj.Calibration[0].PCDATA)
                    minv=maxv=''
                    if hasattr(chanObj,'MaxValue'): maxv=chanObj.MaxValue[0].parse()
                    if hasattr(chanObj,'MinValue'): minv=chanObj.MinValue[0].parse()
                    if minv=='': minv=min(self.intedges[self.Chanbreaks[chan]:end,3])
                    if maxv=='': maxv=max(self.intedges[self.Chanbreaks[chan]:end,3])
                    numpy.clip(self.intedges[self.Chanbreaks[chan]:end,3],max(-self.scale/2.,minv),min(self.scale/2.,maxv),self.intedges[self.Chanbreaks[chan]:end,3])
                    # scale & offset values into integer ranges
                    numpy.multiply((pow(2.,8*self.bytespervalue-1)-1)/(self.scale/2.),self.intedges[self.Chanbreaks[chan]:end,3],self.intedges[self.Chanbreaks[chan]:end,3])
                    numpy.add(pow(2.,8*self.bytespervalue-1),self.intedges[self.Chanbreaks[chan]:end,3],self.intedges[self.Chanbreaks[chan]:end,3])
                    # calculate repeats
                    reps=numpy.empty(end-self.Chanbreaks[chan],dtype=numpy.int32)
                    reps[:-1]=self.intedges[self.Chanbreaks[chan]+1:end,0]-self.intedges[self.Chanbreaks[chan]:(end-1),0]
                    reps[-1]=1  # add last repeat of one
                    # now need to interweave values / repeats
                    # create a numedges x (self.bytespervalue+self.bytesperrepeat) sized byte array
                    interweaver=numpy.empty([(end-self.Chanbreaks[chan]),(self.bytesperrepeat+self.bytespervalue)],numpy.byte)
                    # load values into first bytespervalue columns
                    interweaver[:,0:self.bytespervalue]=numpy.lib.stride_tricks.as_strided(
                        numpy.asarray(self.intedges[self.Chanbreaks[chan]:end,3],dtype='<u'+str(self.bytespervalue)).view('u1'),
                        [end-self.Chanbreaks[chan],self.bytespervalue],[self.bytespervalue,1])
                    # load repeats into last bytesperrepeat columns
                    interweaver[:,self.bytespervalue:(self.bytespervalue+self.bytesperrepeat)]=numpy.lib.stride_tricks.as_strided(
                        numpy.asarray(reps,dtype='<u'+str(self.bytesperrepeat)).view('u1'),
                        [end-self.Chanbreaks[chan],self.bytesperrepeat],[self.bytesperrepeat,1])
                    # copy into timingstring
                    self.timingstring[tsptr:(tsptr+length)]=interweaver.view('u1').reshape(interweaver.shape[0]*(self.bytesperrepeat+self.bytespervalue))
                    tsptr+=length
                # if needs a holding edge at sequence end, add it now
                if (self.needsEnd.count(chan)>=1):
                    self.timingstring[tsptr:(tsptr+self.bytesperrepeat+self.bytespervalue)]=numpy.append(
                        numpy.asarray([hv], dtype='<u'+str(self.bytespervalue)).view('u1'),
                        numpy.asarray([1], dtype='<u'+str(self.bytesperrepeat)).view('u1'))
                    tsptr+=self.bytesperrepeat+self.bytespervalue
            else:
                ### output clocking pulses on this channel from dictionary
                length=2*(self.bytesperrepeat+self.bytespervalue)*len(self.cStrings[chan])
                self.generate_clockstring(self.cStrings[chan],chan)
        return None

    def generate_clockstring(self,ctimes,chan):
        # find channel data for this clock: active rise / active fall, pulse-width, delaytrain        
        chanObj=self.channelMap.getItemByFieldValueSet('Channel',{'TimingGroup':str(self.tGroup),'TimingGroupIndex':str(chan)})        
        aEdge=chanObj.get_childNodeValue_orDefault('ActiveEdge','rise')
        pWidth=chanObj.get_childNodeValue_orDefault('PulseWidth','')
        dTrain=chanObj.get_childNodeValue_orDefault('DelayTrain','No')
        if not str.upper(dTrain)=='YES':  # algorithm for using a standard value,repeat pair
            if pWidth=='':  # if pWidth not defined create bisecting falltimes
                falltimes=(ctimes[0:-1]+ctimes[1:])/2.
            else: # if pulsewidth defined, use it to create falltimes
                falltimes=numpy.add(ctimes,pWidth)
            if self.ResolutionBits>1: # algorithm for an analog clock channel (should rarely be used)
                high=chanObj.get_childNodeValue_orDefault('HighLevel',5.)
                low=chanObj.get_childNodeValue_orDefault('HighLevel',0.1)
                interweaver=numpy.empty((len(falltimes),4))
                if str.upper(aEdge)=='RISE':
                    interweaver[:,0]=high
                    interweaver[:,2]=low
                else:
                    interweaver[:,2]=high
                    interweaver[:,0]=low
                interweaver[:,1]=ctimes
                interweaver[:,3]=falltimes
            else: # algorithm for digital clock channel:
                interweaver=numpy.empty((len(falltimes),1))
                interweaver[:,0]=ctimes
                interweaver[:,1]=falltimes
        else: # algorithm for a delay train pulser (returns only delay between successive pulses)
            delays=ctimes[1:]-ctimes[:-1]
            return numpy.asarray(delays, dtype='<u'+str(self.bytesperrepeat)).view('u1') # return as bytestring
            
class Sequence(gnosis.xml.objectify._XO_,XTSM_core):
    def collectTimingProffers(self):
        """
        Performs a first pass through all edges and intervals, parsing and collecting all ((Start/ /End)time/value/channel) entries necessary to arbitrate clocking edges
        """
        self.TimingProffer=TimingProffer(self)
        if (not hasattr(self,'guid_lookup')): 
            self.generate_guid()            
            self.generate_guid_lookup()
        if (not hasattr(self,'_fasttag_dict')): 
            self.generate_fasttag(0)
        for subsequence in self.SubSequence:
            subsequence.collectTimingProffers(self.TimingProffer)
        return None
        
    def parse(self):
        """
        top-level algorithm for parsing a sequence;
        equivalent to XTSM_parse in IDL code
        """
        # first decipher channelMap, get resolutions
        cMap=self.getOwnerXTSM().getDescendentsByType("ChannelMap")[0]
        channelHeir=cMap.createTimingGroupHeirarchy()        
        channelRes=cMap.findTimingGroupResolutions()
        # collect requested timing edges, intervals, etc...
        self.collectTimingProffers()
        # create an element to hold parser output
        pOutNode=self.insert(ParserOutput())
        # step through timing groups, starting from lowest on heirarchy
        for tgroup in sorted(channelHeir, key=channelHeir.__getitem__):
            cA=pOutNode.insert(ControlData()) # create a control array node tagged with group number
            cA.insert(GroupNumber().set_value(tgroup))
            cA.insert(ControlArray().construct(self,cMap,tgroup))

    def get_endtime(self):
        """
        gets the endtime for the sequence from XTSM tags (a parsed 'EndTime' tag)
        or coerces to final time in edge and intervals, or to default maximum length
        of 100s.
        """
        maxlasttime=100000 # a default maximum sequence length in ms!!! 
        if hasattr(self,'EndTime'):
            endtime=self.EndTime[0].parse()
            if ((endtime < maxlasttime) and (endtime > 0)): 
                self.endtime = endtime
            else: 
                self.endtime = maxlasttime
                self.EndTime[0].addAttribute('parser_error','Invalid value: Coerced to '+str(maxlasttime)+' ms.')
        else:
            if hasattr(self,'TimingProffer'):
                self.endtime=max(self.TimingProffer.data['Edge'][:,2].max(),
                             self.TimingProffer.data['Interval'][:,2].max(),
                             self.TimingProffer.data['Interval'][:,3].max())
        return self.endtime

class SubSequence(gnosis.xml.objectify._XO_,XTSM_core):
    def collectTimingProffers(self,timingProffer):
        """
        Performs a first pass through all edges and intervals, parsing and collecting all ((Start/ /End)time/value/channel) entries necessary to arbitrate clocking edges
        """
        if (hasattr(self,'Edge')):
            for edge in self.Edge:
                timingProffer.insert('Edge',edge.parse_proffer(0))
        if (hasattr(self,'Interval')):
            for interval in self.Interval:
                timingProffer.insert('Interval',interval.parse_proffer(0)) 
        if (hasattr(self,'SubSequence')):
            for subsequence in self.SubSequence:
                subsequence.collectTimingProffers(timingProffer)
        return None

class ChannelMap(gnosis.xml.objectify._XO_,XTSM_core):
    def getChannelIndices(self,channelName):
        """
        returns the timingGroup number and timingGroup index for a channel of specified name as a pair [tg,tgi]
        """
        if (not hasattr(self,'Channel')): return [-1,-1]
        for chan in self.Channel:
            if hasattr(chan,'ChannelName'):
                if chan.ChannelName[0].PCDATA==channelName: 
                    return [float(chan.TimingGroup[0].PCDATA),float(chan.TimingGroupIndex[0].PCDATA)]
        else: return [-1,-1]

    def getClocks(self):
        """
        Assembles a list of all clocking channels and stores as self.Clocks
        """
        self.Clocks={}
        for tg in self.TimingGroupData:
            if hasattr(tg,'ClockedBy'):
                clck=self.getChannel(tg.ClockedBy[0].PCDATA)
                if clck != None:
                    self.Clocks.update({int(tg.GroupNumber[0].PCDATA):[int(clck.TimingGroup[0].PCDATA),int(clck.TimingGroupIndex[0].PCDATA)]})
        return

    def isClock(self,timingGroup,channelIndex):
        """
        returns False if channel is not a clock; otherwise returns timingGroup number
        the channel is a clock for
        """
        if (not hasattr(self,'Clocks')):
            self.getClocks()
        for tg,c in self.Clocks.items():
            if c==[timingGroup,channelIndex]:
                return tg
        return False

    def getChannel(self,channelName):
        """
        returns the timingGroup number and timingGroup index for a channel of specified name as a pair [tg,tgi]
        """
        if (not hasattr(self,'Channel')): return None
        for chan in self.Channel:
            if hasattr(chan,'ChannelName'):
                if chan.ChannelName[0].PCDATA==channelName: 
                    return chan
        else: return None
        
    def createTimingGroupHeirarchy(self):
        """
        Creates a heirarchy of timing-groups by associating each with a level.
        The lowest level, 0, clocks nothing
        one of level 1 clocks one of level 0        
        one of level 2 clocks one of level 1...etc
        results are stored in the object as attribute tGroupClockLevels,
        which is a dictionary of (timingGroup# : timingGroupLevel) pairs
        """
        tgroups=self.getDescendentsByType('TimingGroupData')
        tGroupClockLevels=numpy.zeros(len(tgroups),numpy.byte)
        bumpcount=1
        while bumpcount!=0:
            maxlevel=max(tGroupClockLevels)
            if maxlevel>len(tgroups): pass #raise exception for circular loop
            bumpcount=0
            tgn=[]
            for s in range(len(tgroups)):
                if hasattr(tgroups[s],"GroupNumber"):
                    gn=int(tgroups[s].GroupNumber[0].PCDATA)
                else: pass
                    # raise 'All timing groups must have groupnumbers; offending <TimingGroupData> node has position '+s+' in channelmap'
                if maxlevel==0: tgn=[tgn,gn]
                if (tGroupClockLevels[s] == maxlevel):
                    if hasattr(tgroups[s],'ClockedBy'): clocksource=tgroups[s].ClockedBy.PCDATA
                    else: pass # raise 'TimingGroup '+gn+' must have a channel as clock source (<ClockedBy> node)' 
                    if (clocksource != 'self'):
                        clockgroup=self.getChannel(clocksource).TimingGroup[0].PCDATA
                        for k in range(len(tgroups)):
                            if tgroups[k].GroupNumber[0].PCDATA==clockgroup: break
                        tGroupClockLevels[k]=maxlevel+1
                        bumpcount+=1
        tGroupNumbers = [int(a.GroupNumber.PCDATA) for a in py_obj.head.ChannelMap.getDescendentsByType('TimingGroupData')]
        self.tGroupClockLevels = dict(zip(tGroupNumbers,tGroupClockLevels)) # dictionary of tg#:tgLevel pairs
        return self.tGroupClockLevels
        
    def findTimingGroupResolutions(self):
        """
        establish clocking resolutions which work for each clocking chain, 
        such that descendents clock at a multiple of their clocker's base frequency
        stores result at self.tGroupClockResolutions
        (first establishes a timingGroup heirarchy if not already existent on node)        
        Untested - should follow block at line 627 in IDL version
        """
        if (not hasattr(self,'tGroupClockLevels')): self.createTimingGroupHeirarchy()
        cl=max(self.tGroupClockLevels.values()) 
        res={}        
        while (cl >=0):
            tgThisLevel=[tg for tg,tgl in self.tGroupClockLevels.items() if tgl==cl]
            for tg in tgThisLevel:
                tgNode=self.getItemByFieldValue('TimingGroupData','GroupNumber',str(tg))
                if hasattr(tgNode,'ClockPeriod'):
                    cpNode=tgNode.ClockPeriod[0]
                    clockperiod=cpNode.PCDATA
                else: 
                    clockperiod=0.0002   # add a clockperiod node with default value
                    cpNode=tgNode.insert(ClockPeriod(str(clockperiod)))
                if hasattr(cpNode,'current_value'): 
                    if cpNode.current_value != u'':
                        clockperiod=cpNode.current_value
                if hasattr(tgNode,'ClockedBy'):
                    if tgNode.ClockedBy[0].PCDATA!='self':
                        clocksource=tgNode.ClockedBy[0].PCDATA
                        clockgroup=self.getItemByFieldValue('TimingGroupData','GroupNumber',self.getChannel(clocksource).TimingGroup[0].PCDATA)
                        if hasattr(clockgroup,'ClockPeriod'):
                            if hasattr(clockgroup.ClockPeriod[0],'current_value'):
                                timerperiod=clockgroup.ClockPeriod[0].current_value
                            else: timerperiod=clockgroup.ClockPeriod[0].PCDATA
                        else: timerperiod=0.0002
                        clockperiod=math.ceil(float(clockperiod)/float(timerperiod))*float(timerperiod)
                if hasattr(tg,'current_value'):
                    tgNode.current_value=str(clockperiod)
                else: tgNode.addAttribute('current_value',str(clockperiod))
                res.update({tg:clockperiod})
            cl-=1
        self.tGroupClockResolutions=res
        return res
        
class Edge(gnosis.xml.objectify._XO_,XTSM_core):
    scopePeers=[['Channel','ChannelName','OnChannel']]
    def parse_proffer(self,startTime):
        """
        returns parsed values for [[timinggroup,channel,time,edge]]
        times will be returned relative to the provided (numeric) start-time
        unfinished!
        """
        t=self.Time[0].parse()+startTime
        v=self.Value[0].parse()
        [tg,c]=self.OnChannel[0].getTimingGroupIndex()
        if (not hasattr(self,'guid')):
            self.generate_guid(1)
        if hasattr(self,'_fasttag'): return [tg,c,t,v,self._fasttag]
        else: return [tg,c,t,v,-1]
        
class Interval(gnosis.xml.objectify._XO_,XTSM_core):
    scopePeers=[['Channel','ChannelName','OnChannel']]
    def parse_proffer(self,startTime):
        """
        returns parsed values for [[timinggroup,channel,starttime,endttime,Vresolution,Tresolution,edge]]
        times will be returned relative to the provided (numeric) start-time
        unfinished!
        """
        st=self.StartTime[0].parse()+startTime
        et=self.EndTime[0].parse()+startTime
        self.endtime=et
        self.starttime=st
        #v=self.Value[0].parse()
        vres=self.VResolution[0].parse()
        tres=self.TResolution[0].parse()
        [tg,c]=self.OnChannel[0].getTimingGroupIndex()
        self.tg=tg
        self.c=c
        if (not hasattr(self,'guid')):
            self.generate_guid(1)
        if hasattr(self,'_fasttag'): return [tg,c,st,et,vres,tres,self._fasttag]
        else: return [tg,c,st,et,vres,tres,-1]
        
    def expected_samples(self, dense_time_array):
        """
        Returns number of samples interval will generate given a dense time array
        """
        return ((dense_time_array>=self.starttime)&(dense_time_array<=self.endtime)).sum()
        
    def parse_harvest(self, dense_time_array):
        """
        Returns all edges according to times in this interval
        first index (timinggroup number) replaced with time index
        """
        T=numpy.extract((dense_time_array>=self.starttime)*
                        (dense_time_array<=self.endtime),dense_time_array)
        TI=T-self.starttime
        # now re-evaluate values using these times
        V=self.Value[0].parse({'T':T,'TI':TI})
        numelm=T.size
        startind=dense_time_array.searchsorted(self.starttime)
        endind=dense_time_array.searchsorted(self.endtime)
        if hasattr(self,'_fasttag'): 
            return numpy.array([numpy.arange(startind,endind+1),numpy.repeat(self.c,numelm),T,V,numpy.repeat(self._fasttag,numelm)])

class OnChannel(gnosis.xml.objectify._XO_,XTSM_core):
    def getTimingGroupIndex(self):
        """
        This should return the timingGroup number and and index of the channel.
        Unfinished!
        """
        [tg,c]=self.getOwnerXTSM().head[0].ChannelMap[0].getChannelIndices(self.PCDATA)
        return [tg,c]

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
# c:/wamp/www/sequences/8-6-2012/alternate.xtsm
# /12-1-2012/FPGA_based.xtsm
xml_obj = gnosis.xml.objectify.XML_Objectify(u'c:/wamp/vortex/sequences/12-1-2012/FPGA_based_complex.xtsm')
py_obj = xml_obj.make_instance()
py_obj.insert(Parameter(u'shotnumber',u'23'))
try: py_obj.body.Sequence[0].parse()
except:
    type, value, tb = sys.exc_info()
    traceback.print_exc()
    last_frame = lambda tb=tb: last_frame(tb.tb_next) if tb.tb_next else tb
    frame = last_frame().tb_frame
    ns = dict(frame.f_globals)
    ns.update(frame.f_locals)
    code.interact(local=ns)