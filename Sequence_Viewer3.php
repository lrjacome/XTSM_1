<!-- KNOWN ISSUES:  

IF a necessary element is not defined in source file (e.g. start time of a subsequence) it will not be added on update
scroll up and down page using mouse wheel gets stuck in open graphs

 -->


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Glab Experiment Sequence Viewer</title>

<style type="text/css" id="stylesheet0">
<!--    .CodeMirror {border-top: 1px solid black; border-bottom: 1px solid black;}-->
  .CodeMirror {
  overflow-y: auto;
  overflow-x: scroll;
  width: 1800px;
  height: auto;
  line-height: 1em;
  font-family: monospace;
  _position: relative; /* IE6 hack */
}
    span.CodeMirror-matchhighlight 						{ background: #e9e9e9 ;}
    span.CodeMirror-matchhighlight2 					{ background: #ffcccc ;}
    .CodeMirror-focused span.CodeMirror-matchhighlight 	{ background: #e7e4ff; !important }
	ul{font-family: monospace;}
	.xtsm_TimingGroupData_head 	{ font-family: monospace;background: #ddddaa ;
	 							border-top: solid thin black; }
	.xtsm_TimingGroupData_body 	{ font-family: monospace;background: #eeeebb ;	 								 
								border-left: dotted thin black ;
								border-bottom: dotted thin black;}								
    .xtsm_SubSequence_body 	{ background: #ffeeee ;}
	.xtsm_Sequence_body 	{ background: #eeffee ;}
	.xtsm_Edge_body 		{ background: #eeeeff ;}
	.xtsm_XTSM_body 		{ background: #ffffee ;}
	.xtsm_SubSequence_head 	{ font-family: monospace; background: #ffcccc;
	   							border-top: solid thin black ;
								border-bottom: dashed thin black ;}
	.xtsm_Sequence_head 	{ font-family: monospace; background: #ccffcc;
	   							border-top: solid thin black ;
								border-bottom: dashed thin black ;}
	.xtsm_XTSM_head 		{ font-family: monospace;background: #ffffcc;
	   							border-top: solid thin black ;
								border-bottom: dashed thin black ;}
	.xtsm_Edge_head 		{ font-family: monospace;background: #ccccff ;
	 							border-top: solid thin black ;
								border-bottom: dashed thin black;}
	.xtsm_Parameter_head 	{ font-family: monospace;background: #ffffaa ;
	 							border-top: solid thin black ;
								border-bottom: dashed thin black;}
	.xtsm_Interval_head 	{ font-family: monospace;background: #cceeff ;
	 							border-top: solid thin black; }
	.xtsm_Interval_body 	{ font-family: monospace;background: #eeeeff ;	 								 
								border-left: dotted thin black ;
								border-bottom: dotted thin black;}
	.xtsm_SerialTransfer_head 	{ font-family: monospace;background: #ddaa77 ;
	 							border-top: solid thin black; }
	.xtsm_SerialTransfer_body 	{ font-family: monospace;background: #eecc99 ;	 								 
								border-left: dotted thin black ;
								border-bottom: dotted thin black;}
	.xtsm_Graph_body 		{ font-family: monospace;background: #ffffff ;
								border-bottom: dotted thin black;}
	.xtsm_Edge_Time 		{ font-family: monospace;background: #ffccff;
	   							width:70px;	}
	.xtsm_Edge_Value 		{ font-family: monospace;background: #ffccff;
	   							width:50px;	}
	.highlighted			{background-color:#99FF33;}
	.disabled				{background-color:#ffffff;color: #aaaaaa;}
</style>

</head>

<!-- HERE BEGINS THE INCLUSION OF OUTSIDE ROUTINES -->
<!-- the following are required supporting files written in-house -->
<!-- the togglediv library of javascript routines expands and collapses divisions -->
<script language="javascript" type="text/javascript" src="js/togglediv.js"></script>
<!-- the following are required supporting files written externally to support packages -->
<!-- jquery is a widely used library of javascript functions.  -->
<script language="javascript" type="text/javascript" src="jquery/jquery-1.7.1.js"></script>
<script language="javascript" type="text/javascript" src="jquery/jquery.hotkeys-0.7.9.js"></script>
<!--autocomplete taken from http://www.codeproject.com/Articles/8020/Auto-complete-Control: abbreviated by actb-->
<script language="javascript" type="text/javascript" src="js/actb.js"></script>
<script language="javascript" type="text/javascript" src="js/common.js"></script>

<script type="text/javascript" src="flot/jquery.js"></script> 
<!-- plotting is handled by flot, an externally developed package of javascript routines: -->
<script type="text/javascript" src="flot/jquery.flot.js"></script> 
<script language="javascript" type="text/javascript" src="flot/jquery.flot.navigate.js"></script>
<!-- flot/test_alljava is my internally-modified flot plotting routines: -->
<script language="javascript" type="text/javascript" src="flot/test_alljava.js"></script>
<!-- Help declarations unique to sequence viewer -->
<script language="javascript" type="text/javascript" src="SequenceViewer/help.js"></script>
<script language="javascript" type="text/javascript" src="js/colors.js"></script>
<!-- Data Encoding Utility: -->
<script language="javascript" type="text/javascript" src="jquery/jquery-base64.js"></script>
<script type="text/javascript" src="jquery/jqueryui.js" ></script>
<script type="text/javascript" src="jquery/jquery.mousewheel.min.js" ></script>
<script language="javascript" type="text/javascript" src="jquery/jquery.iviewer.js"></script>
<link rel="stylesheet" href="jquery/jquery.iviewer.css" />


    <script src="codemirror/lib/codemirror.js"></script>
    <script src="codemirror/lib/util/formatting.js"></script>
    <script src="codemirror/mode/css/css.js"></script>
    <script src="codemirror/mode/xml/xml.js"></script>
    <script src="codemirror/mode/javascript/javascript.js"></script>
    <script src="codemirror/mode/htmlmixed/htmlmixed.js"></script>
    
    
<!-- THIS ENDS THE INCLUSION OF OUTSIDE ROUTINES -->


<script type="text/javascript">

//this is a global variable to hold all channels - used in autocomplete
channel_list=new Array('mot detuning','monkey altitude');


//Hotkey definitions - this package seems unstable, KEEP code in case it can be made to work
//$(function () { $(document).bind('keydown', 'Ctrl+p', save_doc);});
//function save_doc() {document.codeform.submit();}
//$(function () { $(document).bind('keydown', 'Ctrl+c', show_channels);});
//function show_channels() {alert('channels are:'+channel_list);}

// the following functions "elem_childepilogue_XXX" are called in buiding the XML/DOM input tree to alter look or contents of child divisions after all children have been output - they need not exist, and will be ignored if they do not exist
function elem_childepilogue_body(div) {}
function elem_childepilogue_Graph(div,elem) {
	//This inserts plot (generated with flot) into child division of a graph element
	var newdiv=div.appendChild(document.createElement('div'));
	
	//next line gets data from first sibling Data node
	//if (elem.previousSibling.previousSibling.nodeName == 'Data') var list=elem.previousSibling.previousSibling.firstChild.nodeValue; 
	var list='';
	var labels=new Array();
	for (var j=0;j<elem.parentNode.getElementsByTagName('Data')[0].getElementsByTagName('ARRAY2D').length;j++){
		if (j==0) list=elem.parentNode.getElementsByTagName('Data')[0].getElementsByTagName('ARRAY2D')[0].firstChild.nodeValue; else list=list+'___'+elem.parentNode.getElementsByTagName('Data')[0].getElementsByTagName('ARRAY2D')[j].firstChild.nodeValue;
		labels.push(elem.parentNode.getElementsByTagName('Data')[0].getElementsByTagName('ARRAY2D')[j].getAttribute('Name'));
	}
	
	//Build graph only if division, and all parent divisions are expanded (saves time if many graphs present)
	var s=elem;
	while (s.nodeName != 'XTSM') { if(s.getAttribute('expanded')!='1') break; else s=s.parentNode; }
	if (s.nodeName=='XTSM') make_graph_master(newdiv,list,labels);  //this function is defined in flot/test_alljava.js	
	}

function elem_childepilogue_EdgeGraph(div,elem) {
	//This inserts plot (generated with flot) into child division of a graph element
	var newdiv=div.appendChild(document.createElement('div'));
	var edges=collect_edges_below(elem.parentNode,null);
	var list= new Array();
	for (var j=0;j<(edges.length);j++) {
		var newelm= new Object;
		if (edges[j].getElementsByTagName('OnChannel').length > 0) if (edges[j].getElementsByTagName('OnChannel').item(0).firstChild != null)  newelm.chan=edges[j].getElementsByTagName('OnChannel').item(0).firstChild.nodeValue; else newelm.chan='';
		if (edges[j].getElementsByTagName('Value').length > 0) if (edges[j].getElementsByTagName('Value').item(0).firstChild != null)  newelm.val=edges[j].getElementsByTagName('Value').item(0).firstChild.nodeValue; else newelm.val='';
		if (edges[j].getElementsByTagName('Time').length > 0) if (edges[j].getElementsByTagName('Time').item(0).firstChild != null)  newelm.time=edges[j].getElementsByTagName('Time').item(0).firstChild.nodeValue; else newelm.time='';
		if ((!isNaN(newelm.time)) && (!isNaN(newelm.val))) list.push(newelm);
		}
	//alert(edges.length);
	list.sort(edge_comp);
	edgestring='';
	for  (var j=0;j<list.length;j++) edgestring=edgestring+list[j].chan+'; ';
	
	//alert(edgestring);
//  Stopped here.   next command plots default data - need next to call make_graph instead, passing the list constructed above (will also need to remove non-numeric data, and then group by channel(label) into sets)
	make_edgegraph_master(newdiv);  //this function is defined in flot/test_alljava.js	
	}

function edge_comp(edge1,edge2) {
	if (edge1.chan<edge2.chan) return -1;
	if (edge1.chan>edge2.chan) return 1;
	if (edge1.time<edge2.time) return -1;
	if (edge1.time>edge2.time) return 1;
	return 0;
	}
//the following copies data from an input field in _,__ delimited format into an origin-ready string
function copydata(fromfield){
	text=(fromfield.value.replaceAll('__','\n')).replaceAll('_','   ');
	alert(text);

	}
function toggle_edit(elem){
	//alert('hey');
	inputfield=elem.parentNode.getElementsByTagName('input').item(0);
	if (inputfield.getAttribute('hidden')!='hidden') {
		content=inputfield.value;
		inputfield.setAttribute('hidden','hidden');
		htmldiv=elem.parentNode.appendChild(document.createElement('div')); 
		htmldiv.innerHTML="<b>"+content+"</b>";
		//alert(content);
		} else {
		inputfield.removeAttribute('hidden');
		elem.parentNode.removeChild(elem.parentNode.getElementsByTagName('div').item(0));			
		}
	}
//The following functions "elem_inputepilogue_XXX(inputfield,elem)" are called as epilogue to input fields, with XXX the xml tag type that generated the input field; they need not exist.  the first argument 'inputfield' is mandatory and is the webpage input field itself. the second argument 'elem' is optional, and is the corresponding element in the xml DOM tree 
function elem_inputepilogue_Description(inputfield) {
	inputfield.setAttribute('size','100');
	editlink=inputfield.parentNode.appendChild(document.createElement('img'));
	editlink.setAttribute('src','images/sequence_edit_description.png');
	editlink.setAttribute('height','20px');
	editlink.setAttribute('title','Toggle Edit/View');
	editlink.setAttribute('onclick','javascript:toggle_edit(this);');
	if (inputfield.value!='') toggle_edit(inputfield);
	}
function elem_inputepilogue_Comments(inputfield) {inputfield.setAttribute('size','100');}
function elem_inputepilogue_Name(inputfield) {
	inputfield.setAttribute('size','40');
//  Another attempt at grid-alignment of inputs
//	var x = getOffset(inputfield).left; 
//	inputfield.value='pos:'+x+'=>'+Math.round(x/20)*20;
//	inputfield.setAttribute('style','position:relative;left:'+(-Math.round(x/50)*50+x)+'px;');
	}
function elem_inputepilogue_ARRAY2D(inputfield,elem) {
	alink=inputfield.parentNode.appendChild(document.createElement('a'));
	alink.appendChild(document.createTextNode('copy->'));
	alink.setAttribute('onclick','javascript:copydata(this.previousSibling);');
	inputfield.setAttribute('title',elem.getAttribute('Name'));
	}
function elem_inputepilogue_ChannelName(inputfield) {
	inputfield.setAttribute('size','40');
	//build channel list; old values will not be removed until page is reloaded
	if (inputfield.parentNode.getAttribute('class') == 'xtsm_Channel_head') {channel_list.push(inputfield.getAttribute('value'));}
	}
function elem_inputepilogue_Time(inputfield) {inputfield.setAttribute('size','10');}
function elem_inputepilogue_Enabled(inputfield,elem) {
	if (inputfield.value=='0') {
		inputfield.parentNode.parentNode.parentNode.parentNode.setAttribute('class','disabled'); 
		inputfield.parentNode.parentNode.parentNode.parentNode.previousSibling.setAttribute('class','disabled'); 
	}
//	inputfield.removeAttribute('onchange');
//	inputfield.removeAttribute('value');
//	inputfield.setAttribute('type','checkbox');
//	alert(elem.nodeValue);
//	if (elem.nodeValue=='true') {inputfield.setAttribute('checked',true);} else {
//		if (elem.nodeValue=='false') {inputfield.setAttribute('checked',false);} else
//			inputfield.setAttribute('checked',true);
//		} 
//	inputfield.setAttribute('onchange','javascript:change_checkbox(this);');
	}
//function change_checkbox(box) {
//	alert('checkbox changed');
//	if (box.checked=='true') {newval='true';} else {newval='false';} 
//	box.setAttribute('type','text');
//	box.setAttribute('onChange','update_code2(this,"'+box.name+'");');
//	box.setAttribute('value',newval);
//	update_code2(box,box.name);
//	}
function elem_inputepilogue_TResolution(inputfield,elem) {
	if (elem.parentNode.nodeName=='Interval') {
		editlink=inputfield.parentNode.appendChild(document.createElement('img'));
		editlink.setAttribute('src','images/seqicon_list.png');
		editlink.setAttribute('height','15px');
		editlink.setAttribute('title','Convert to Tabulated Update Times');
		editlink.setAttribute('onclick','javascript:convert_tag("'+inputfield.name+'","Times");');
		}
	}
function elem_inputepilogue_Times(inputfield,elem) {
	if (elem.parentNode.nodeName=='Interval') {
		editlink=inputfield.parentNode.appendChild(document.createElement('img'));
		editlink.setAttribute('src','images/seqicon_function.png');
		editlink.setAttribute('height','20px');
		editlink.setAttribute('title','Convert to Time Resolution Declaration');
		editlink.setAttribute('onclick','javascript:convert_tag("'+inputfield.name+'","TResolution");');
		}
	}
function elem_inputepilogue_Value(inputfield,elem) {
	inputfield.setAttribute('size','10');
//	elem=get_elem_to_modify_from_idname(inputfield.name);
	if (elem.parentNode.nodeName=='Edge') {return;}
	if (elem.parentNode.nodeName=='Interval') {
		editlink=inputfield.parentNode.appendChild(document.createElement('img'));
		editlink.setAttribute('src','images/seqicon_list.png');
		editlink.setAttribute('height','15px');
		editlink.setAttribute('title','Convert to Tabulated Values');
		editlink.setAttribute('onclick','javascript:convert_tag("'+inputfield.name+'","Values");');
		}
	}	
function elem_inputepilogue_Values(inputfield,elem) {
	inputfield.setAttribute('size','10');
	if (elem.parentNode.nodeName=='Interval') {
		editlink=inputfield.parentNode.appendChild(document.createElement('img'));
		editlink.setAttribute('src','images/seqicon_function.png');
		editlink.setAttribute('height','20px');
		editlink.setAttribute('title','Convert to Formulaic Value');
		editlink.setAttribute('onclick','javascript:convert_tag("'+inputfield.name+'","Value");');
		}
	}	

function convert_tag(name,convertto) {
// this function converts a named node from one tag type to another
	elem=get_elem_to_modify_from_idname(xmlDoc,name);
	parentname=generate_name_for_element(elem.parentNode);
	var pos=0; var child=elem; while ((child=child.previousSibling)!= null) {pos++};
	item_delete(name,'false');
	item_add(parentname,convertto,pos);

	}

function elem_inputepilogue_OnChannel(inputfield,elem) {
	//autocomplete the entries from channel list
	actb(inputfield,channel_list);
	//provide link to expand and jump to the channel in the head data:
	jumplink=inputfield.parentNode.appendChild(document.createElement('img'));
	jumplink.setAttribute('src','images/seqicon_go.png');
	jumplink.setAttribute('height','20px');
	jumplink.setAttribute('title','Jump to Channel Definition');
	jumplink.setAttribute('onclick','javascript:jump_to_channel("'+inputfield.value+'");');
	}
function elem_inputepilogue_TimingGroup(inputfield,elem) {
	//autocomplete the entries from channel list
	//actb(inputfield,channel_list);
	//provide link to expand and jump to the channel in the head data:
	jumplink=inputfield.parentNode.appendChild(document.createElement('img'));
	jumplink.setAttribute('src','images/seqicon_go.png');
	jumplink.setAttribute('height','20px');
	jumplink.setAttribute('title','Jump to TimingGroupData Definition');
	jumplink.setAttribute('onclick','javascript:jump_to_element("'+inputfield.value+'","TimingGroupData","GroupNumber");');
	}

function jump_to_channel(name) {
	// this function expands the xml tree path down into a named channel, then highlights the channel node on the tree 
	var channel_elem=find_element_by_name(name,'Channel','ChannelName');
	channel_elem.setAttribute('expanded',"1");
	var nextelem=channel_elem;
	while (nextelem.nodeName != 'XTSM') {
		nextelem.setAttribute('expanded',"1");
		nextelem=nextelem.parentNode;
		}
	response=update_code_fromtree(nextelem);
	var channeldivid='divtree__'+generate_name_for_element(channel_elem);
	document.getElementById(channeldivid).setAttribute('class',"highlighted");
	document.getElementById(channeldivid).previousSibling.setAttribute('class',"highlighted");
	document.getElementById(channeldivid).previousSibling.scrollIntoView();
	}
	
	
function jump_to_element(name,type,namefield) {
	// this function expands the xml tree path down into a named element, then highlights the channel node on the tree 
	var channel_elem=find_element_by_name(name,type,namefield);
	channel_elem.setAttribute('expanded',"1");
	var nextelem=channel_elem;
	while (nextelem.nodeName != 'XTSM') {
		nextelem.setAttribute('expanded',"1");
		nextelem=nextelem.parentNode;
		}
	response=update_code_fromtree(nextelem);
	var channeldivid='divtree__'+generate_name_for_element(channel_elem);
	document.getElementById(channeldivid).setAttribute('class',"highlighted");
	document.getElementById(channeldivid).previousSibling.setAttribute('class',"highlighted");
	document.getElementById(channeldivid).previousSibling.scrollIntoView();
	}

function find_element_by_name(name,tagtype,nametype) {
// this locates and returns a node in the XML data by tag type and name (name is searched in fields of nametype).  the returned object is a dom node in the dom-parsed xml tree
		if (window.DOMParser){
			  	var parser2=new DOMParser();
    			var xmlDoc2=parser2.parseFromString(editor.getValue(),"text/xml");
			  }
		else // Internet Explorer
			  {
			  	var xmlDoc2=new ActiveXObject("Microsoft.XMLDOM");
	   		  	xmlDoc2.async=false;
				xmlDoc2.loadXML(editor.getValue()); 
			  }
		var dom=xmlDoc2.childNodes[0];
		var oftype=dom.getElementsByTagName(tagtype);
		for (var j=0; j< oftype.length; j++) {
			var namehere=oftype[j].getElementsByTagName(nametype);
			//alert(namehere[0].firstChild.nodeValue);
			if (namehere[0].firstChild.nodeValue==name) {
				return oftype[j];
				}
			}
		return null;
	}

function collect_edges_below(node,edge_list) {
	if (edge_list==null) var edge_list= new Array();
	//alert(node.nodeName);
	if (node.nodeName != '#text') {
		var edges_at_this_level=node.getElementsByTagName('Edge');
		//alert('l');
		for (var j=0;j<edges_at_this_level.length;j++) {edge_list.push(edges_at_this_level.item(j));}
		var eit=node.firstChild;
		//alert('hi');
		while (eit != null) {
			edge_list=collect_edges_below(eit,edge_list);
			eit=eit.nextSibling;
			}
		//alert('hiagain');
		}
	return edge_list;
	}

// the following function is dreamweaver's implementation of a jump menu - it is used for selecting files
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}

//the following translates a string into xml-friendly format in a way the javascript dom textnode write call will not alter
function escape_xml(string){
	oldstring=string;
	string=string.replace(/&/g,"#amp;");
	string=string.replace(/>/g,"#gt;");
	string=string.replace(/</g,"#lt;");
	string=string.replace(/"/g,"#quot;");
	string=string.replace(/'/g,"#apos;");
	//alert(oldstring+"->"+string);
	return string;
}
//the following translates a string from xml-friendly format to text in a way the javascript dom textnode write call will not alter
function unescape_xml(string){
	oldstring=string;
	string=string.replace(/#gt;/g,">");
	string=string.replace(/#lt;/g,"<");
	string=string.replace(/#quot;/g,"\"");
	string=string.replace(/#apos;/g,"'");
	string=string.replace(/#amp;/g,"&");
	//alert(oldstring+"->"+string);
	return string;
}

// the following function generates a name for an element in the DOM tree which is fully descriptive of its position in the tree, in the form TAGTYPE_position__TAGTYPE_position__... which describes the element's position in the tree - the name begins with the eldest parent, and ends with the element itself.  For example, body_1__Sequence_2__Subsequence_1 would be the name of the first subsequence inside the second sequence of the first body.
function generate_name_for_element(elem){
	name='';
	it=elem;
	while (it.parentNode != null) {
		siblingcount=1;
		sibit=it;
		while (sibit.previousSibling != null) {if (sibit.previousSibling.nodeName==it.nodeName) siblingcount=siblingcount+1;sibit=sibit.previousSibling;}
		name=it.nodeName+'_'+siblingcount+'__'+name;
		it=it.parentNode;
		}
	return name.substring(0,name.length-2);
}
// the following function refreshes the editable XML tree display, and records information about how long it took to do so 
function refreshtree(){
		//record start time
		var starttime=(new Date).getTime();
		//if the body has a list, remove it?!@#  -   this one line cost me so much time i'm leaving it in as a reminder how one bad line can tick like a bomb.
		//if (document.body.getElementsByTagName('ul')[0] != null) document.body.removeChild(document.body.getElementsByTagName('ul')[0]);
		//build the tree
		tree_output_dom(null,null);	
		//get the new time
		var endtime=(new Date).getTime();
		//rewrite the parser timing info 
		if (document.getElementById('tree_build_div').childNodes.length == 1) document.getElementById('tree_build_div').removeChild(document.getElementById('tree_build_div').childNodes[0]);
		document.getElementById('tree_build_div').appendChild(document.createTextNode('tree build time: '+(endtime-starttime)/1000.+'s'));		
}
function getStyle(oElm, strCssRule){
    var strValue = "";
    if(document.defaultView && document.defaultView.getComputedStyle){
        strValue = document.defaultView.getComputedStyle(oElm, "").getPropertyValue(strCssRule);
    }
    else if(oElm.currentStyle){
        strCssRule = strCssRule.replace(/\-(\w)/g, function (strMatch, p1){
            return p1.toUpperCase();
        });
        strValue = oElm.currentStyle[strCssRule];
    }
    return strValue;
}
function darken_elem(delem){
	col=getStyle(delem,"Color").split('(')[1].split(')')[0].split(',');
//	alert(getStyle(delem,"Background-Color"));
	ocolor=rgbToHsv(col[0],col[1],col[2]);
	ncolor=hsvToRgb(ocolor[0],ocolor[1],70);
	//delem.removeAttribute("class");
	delem.style.color="rgb("+ncolor.join(',')+')';
//	alert("rgb("+ncolor.join(',')+')');
}
function toggle_xtsm_disable(update_elem,cbox){
	//alert(cbox.parentNode);
	//These lines record the window position at the time the input field was typed in
		var vscroll = (document.all ? document.scrollTop : window.pageYOffset);
		var hscroll = (document.all ? document.scrollLeft : window.pageXOffset);
    //This parses the existing XML code into a DOM object
		if (window.DOMParser){
			  parser=new DOMParser();
    			xmlDoc=parser.parseFromString(editor.getValue(),"text/xml");
			  }	else {// Internet Explorer
			  xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
			  xmlDoc.async=false;
    			xmlDoc.loadXML(editor.getValue()); 
			  }
//This parses the update_elem string input to find the appropriate field in the DOM object to change
		update_nodelist=update_elem.split('__');
		origxmllines=editor.getValue().split('\n');
//This loop builds the reference to the appropriate DOM object
		elem_to_modify=xmlDoc;
		for (j=0;j<update_nodelist.length;j++)
			{
			if (elem_to_modify.getElementsByTagName(update_nodelist[j].split('_')[0])[parseInt(update_nodelist[j].split('_')[1])-1] != 0) {
				elem_to_modify=elem_to_modify.getElementsByTagName(update_nodelist[j].split('_')[0])[parseInt(update_nodelist[j].split('_')[1])-1];
				} 
			}
		if (elem_to_modify.getAttribute('disabled') == "disabled") {elem_to_modify.removeAttribute('disabled');} else {elem_to_modify.setAttribute('disabled','disabled');}
		
		//...and recreates the XML as text
		newxmlstring=XMLtoString(xmlDoc);
//This helps us identify the changed line in the XML code to highlight it in the editor window
		newxmlstringlines=newxmlstring.split('\n');
//This loop identifies the first changed line in the XML code
		for(j=0;j<origxmllines.length; j++ ) {
			if (origxmllines[j] != newxmlstringlines[j]) {
				break;
				}
			}
//This replaces the code in the editor window
		editor.setValue(newxmlstring);
//this next element was added when the editor event was changed to onBlur()
		refreshtree();
//The following lines highlight the changed code in the editor window
		linemarker=editor.setMarker(j,'-->');
		pos= new Object();
		pos.line=j;
		pos.ch=0;
		pos2= new Object();
		pos2.line=j;
		pos2.ch=0;
		editor.setCursor(j,0);
//This line pinks-out the background of line in editor
		editor.markText(pos,pos2,"CodeMirror-matchhighlight2");
//And this pinks-out the input element background in the XML tree - this however is overwritten by editor's refresh of tree onChanges
		//calling_element.style.backgroundColor="#ffcccc";
		//This returns the window location to where it was when the value was originally typed into the input field
		window.scrollTo(hscroll, vscroll);
}

function topline_data(elem,divitem){
//This function outputs the top line of all elements in the XML tree
	//topline_fields="Sequence;Name,text,Name;;"+
	//"SubSequence;Name,text,Name;StartTime,text,Start Time;;"+
	//"Edge;";
//The following object contains information about what child elements to display on the topline for each parent element type
var display_fields = new Object;
display_fields.SubSequence="Name,text,Name;StartTime,text,Start Time";
display_fields.Sequence="Name,text,Name";
display_fields.Edge="OnChannel,text,Channel;Value,text,Value;Time,text,Time";
display_fields.Interval="Name,text,Name;OnChannel,text,Channel;StartTime,text,Start;EndTime,text,End";
display_fields.Channel="ChannelName,text,Name;TimingSysChannel,text,Timing Channel";
display_fields.TimingGroupData="Name,text,Name;GroupNumber,text,GroupNumber";
display_fields.Parameter="Name,text,Name";
display_fields.BitSequence="OnChannel,text,Channel;StartTime,text,Start Time";

//The following object contains information about what controls (clickable icons) to display on the right-hand side of the topline for each parent element type.  each is a list of the form control_function,pop-up text,parameter_for_control_function;control_function,pop-up text,parameter_for_control_function
var display_controls = new Object;
display_controls.all=";item_move,Move Up,1;item_move,Move Down,-1;item_clone,Clone,Clone";
display_controls.body="item_add,Add Sequence,Sequence";
display_controls.SubSequence="item_delete,Delete This,;item_add,Add Edge,Edge;item_add,Add Interval,Interval;item_add,Add Parameter,Parameter";
display_controls.Sequence="item_delete,Delete This,;item_add,Add SubSequence,SubSequence;item_add,Add Parameter,Parameter";
display_controls.Edge="item_delete,Delete This,";
display_controls.Interval="item_delete,Delete This,";
display_controls.Channel="item_delete,Delete This,";
display_controls.TimingGroupData="item_delete,Delete This,";
display_controls.ChannelMap="item_add,Add Channel,Channel;item_add,Add TimingGroupData,TimingGroupData";
display_controls.Parameter="item_delete,Delete This,";
display_controls.TimingData="item_delete,Delete This,;item_add,Add Graph,Graph";
display_controls.BitSequence="item_delete,Delete This,";

//writes a disable checkbox
if (elem.nodeName != 'Description') {
	var disablebox=divitem.appendChild(document.createElement('input'));
	disablebox.setAttribute('type','checkbox');
	disablebox.setAttribute('onclick','toggle_xtsm_disable("'+generate_name_for_element(elem)+'",this);')
	if (elem.getAttribute("disabled")!="disabled") {
		disablebox.setAttribute('checked','checked');
		} else {darken_elem(divitem);}
}

//Writes a label for the node
		  if (document.getElementsByName('show_help').item(0).checked && jQuery.inArray(elem.nodeName,help_fields.available_fields)!= -1) {
			  linkitem=divitem.appendChild(document.createElement('a'));
			  linkitem.appendChild(document.createTextNode(elem.nodeName));
			  linkitem.setAttribute('href',help_fields.links[jQuery.inArray(elem.nodeName,help_fields.available_fields)]);
  			  linkitem.setAttribute('title',help_fields.quick_descript[jQuery.inArray(elem.nodeName,help_fields.available_fields)]);

			  //divitem.style.backgroundColor='#ff1111';
		  } else {divitem.appendChild(document.createTextNode(elem.nodeName));}


//	divitem.appendChild(document.createTextNode(elem.nodeName));
	divitem.appendChild(document.createTextNode(": "));
	var supresschildren=[];

//Writes topline fields
	if (display_fields[elem.nodeName] != null) {
		showelements=display_fields[elem.nodeName].split(';');
		for (s=0;s<(showelements.length);s++){
			//the following displays the node type name, linking to help where applicable
			if (document.getElementsByName('show_help').item(0).checked && jQuery.inArray((showelements[s].split(','))[2],help_fields.available_fields)!= -1) {
				linkitem=divitem.appendChild(document.createElement('a'));
				linkitem.appendChild(document.createTextNode((showelements[s].split(','))[2]));
				linkitem.setAttribute('href',help_fields.links[jQuery.inArray((showelements[s].split(','))[2],help_fields.available_fields)]);
 				linkitem.setAttribute('title',help_fields.quick_descript[jQuery.inArray((showelements[s].split(','))[2],help_fields.available_fields)]);
				//divitem.style.backgroundColor='#ff1111';
			} else {divitem.appendChild(document.createTextNode((showelements[s].split(','))[2]));}
			if ((showelements[s].split(','))[1]!='textarea'){ 
				var subinputelem=divitem.appendChild(document.createElement("input"));
				subinputelem.setAttribute('type',(showelements[s].split(','))[1]);
				} else {
				var subinputelem=divitem.appendChild(document.createElement("textarea"));
//				subinputelem.setAttribute('type',(showelements[s].split(','))[1]);
				}
			//sistyle=subinputelem.appendChild(document.createElement("style"));
			var elemtobeinput=(showelements[s].split(','))[0];
			var subelem=elem.getElementsByTagName(elemtobeinput)[0];

			//If a topline element is not present in data structure, create it
			if (subelem == null) { 
				p=elem.parentNode;
				while (p.parentNode != null) p=p.parentNode;
				subelem=elem.appendChild(p.createElement(elemtobeinput));
				}
			

			if (subelem != null) {
				subinputelem.setAttribute('name',generate_name_for_element(subelem));
				//Below is an attempt to tab-align fields - is not very robust method.
				//subinputelem.style.position='fixed';
				//subinputelem.style.left=(350+250*s).toString()+'px';
				subinputelem.setAttribute('onchange','update_code2(this,"'+generate_name_for_element(subelem)+'");');
				if (subelem.childNodes[0] != null) {
					if ((showelements[s].split(','))[1]!='textarea') {subinputelem.setAttribute('value',subelem.childNodes[0].nodeValue);} else {subinputelem.appendChild(document.createTextNode(subelem.childNodes[0].nodeValue));}
					}
				if (typeof(window['elem_inputepilogue_'+(showelements[s].split(','))[0]]) === "function") {
					// function for child division exists, so we can now call it
					window['elem_inputepilogue_'+(showelements[s].split(','))[0]](subinputelem,subelem);  //this subelem pass is not necessary
					}

				}
			//the next line inserts the parsed value, which is contained in the currentvalue attribute of the tag.
			if ((subelem.getAttribute("currentvalue")!= null) && (subelem.getAttribute("currentvalue")!=subelem.childNodes[0].nodeValue)) {
				//divitem.appendChild(document.createTextNode("=>"+subelem.getAttribute("currentvalue")+"\u00a0\u00a0"));
				subinputelem.setAttribute('title','parsed to => '+subelem.getAttribute("currentvalue"));
				}
			//the next line flags a parser error
			if (subelem.getAttribute("parseerror")!= null) {subinputelem.style.backgroundColor="#FFFF00";subinputelem.title=subelem.getAttribute("parseerror");}
			//the next line adds to a list of elements which should be supressed in the child division because they appear in top line
			supresschildren.push((showelements[s].split(','))[0]);
			}
		}
	//the next block inserts the controls that appear on the far right of the topline as icons.
	if (display_controls[elem.nodeName] != null) {
		var showcontrols=(display_controls[elem.nodeName]+display_controls['all']).split(';').reverse();
		for (q=0;q<(showcontrols.length);q++){
			var controltoadd=showcontrols[q].split(',')[0];
			var controlname=controltoadd+"_"+generate_name_for_element(elem);
			var controlimg=divitem.appendChild(document.createElement("img"));
			controlimg.setAttribute('height','18px');
			controlimg.setAttribute('align','right');
			controlimg.setAttribute('title',showcontrols[q].split(',')[1]);
			controlimg.setAttribute('onclick',controltoadd+'(\''+generate_name_for_element(elem)+'\',\''+showcontrols[q].split(',')[2]+'\');');
			controlimg.setAttribute('src','images/seqicon_'+controltoadd+showcontrols[q].split(',')[2]+'.png');
			}
		}
//Returns a list of fields not to duplicate in the collarpsible division
			return supresschildren;
	}
	
//The following adds a method for arrays to check if they contain a value
Array.prototype.contains = function(obj) {
    var i = this.length;
    while (i--) {
        if (this[i] === obj) {
            return true;
        }
    }
    return false;
}

function common_substring(data) {
  var i, ch, memo, idx = 0
  do {
    memo = null
    for (i=0; i < data.length; i++) {
      ch = data[i].charAt(idx)
      if (!ch) break
      if (!memo) memo = ch
      else if (ch != memo) break
    }
  } while (i == data.length && idx < data.length && ++idx)

  return (data[0] || '').slice(0, idx)
}
function select_code(elem_id){
	var findseq=elem_id.split('__');
//	alert(findseq);
	xmltree=get_xml_from_editor();
	//advance to the corresponding element  - LEFT OFF HERE
	selected_elem=get_elem_to_modify_from_idname(xmltree, elem_id);
	
	
}
selected_divs= new Array();
function div_select(div_fired, elem_id){
	//alert(xml_elem);
	if (selected_divs.length == 0) {
		selected_divs.push(generate_name_for_element(div_fired));
		div_fired.style.backgroundColor="DarkTurquoise";
		select_code(elem_id);
	} else if (selected_divs.length == 1) {
		selected_divs.push(generate_name_for_element(div_fired));
		//alert(common_substring(selected_divs));
		div_fired.style.backgroundColor="DarkTurquoise";
		} else {selected_divs= new Array();}
}

//This function is used recursively to output the XML tree with input fields linked to the code editor	
//It takes an element elem as input, and a tree to build on
function tree_output_dom(elem,tree) {
var then=new Date().getTime();
// if we are at the top of the tree (signified by elem=null), parse the editor content from xml into a DOM object
	if (elem==null) {  
			
			if (window.DOMParser){
			  	parser=new DOMParser();
    			xmlDoc=parser.parseFromString(editor.getValue(),"text/xml");
			  }
			else // Internet Explorer
			  {
			  	xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	   		  	xmlDoc.async=false;
				xmlDoc.loadXML(editor.getValue()); 
			  }
		elem=xmlDoc.childNodes[0];
	}
	//Return immediately if this is a text node
	if (elem.nodeName=='#text') return 0;
	//if the tree is null, append a new element to tree division
	if (tree==null) {
		var tree_div=document.getElementById('tree_div');
		while (tree_div.hasChildNodes()) {
		    tree_div.removeChild(tree_div.lastChild);
			}
		tree=tree_div.appendChild(document.createElement('ul'));
		//tree=document.body.appendChild(tree);
		tree.setAttribute('name','XMLtree');
		}
	//add a list item for the current DOM element to the current tree element 
	//print a list entry for this node with its node name and important properties, use an "xtsm_XXX" class for its node type
	listitemnode=tree.appendChild(document.createElement("li"));
	divitem=listitemnode.appendChild(document.createElement("div"));
	//was trying to create a selection method by clicking li marker:  "useful", as it fires up the ul ladder
	//listitemnode.setAttribute('onclick','alert("'+generate_name_for_element(elem)+'");');

	divitem.setAttribute('class','xtsm_'+elem.nodeName+'_head');
	divitem.setAttribute('oncontextmenu','div_select(this,"'+generate_name_for_element(elem)+'");	return false;');

// make a collapsible subdivision if this node has children
	if (elem.childNodes.length > 1) {
		collapse_icon=divitem.appendChild(document.createElement("img"));
		if (elem.getAttribute('expanded')==1)  collapse_icon.setAttribute('src','images/DownTriangleIcon.png'); else collapse_icon.setAttribute('src','images/RightFillTriangleIcon.png');
		collapse_icon.setAttribute('height','12px');
		collapse_icon.setAttribute('title','Expand/Collapse');
		childdivname='divtree__'+generate_name_for_element(elem);
		collapse_icon.setAttribute('onclick','toggleDiv_update_editor(\''+childdivname+'\')');	
		collapse_icon.setAttribute('oncontextmenu','togglechildDiv_update_editor(\''+childdivname+'\')');	
		}	
	//Create the topline of the element, get which children should be suppressed in the subsequent calls	
	var suppresschildren=topline_data(elem,divitem);

	//Create a textfield if this is a terminal element of the tree
	//alert(elem.nodeName+(!(suppresschildren.contains(elem.nodeName))));
	if ((elem.childNodes.length < 2))  {
		//suppresschildren.join(';').split(elem.nodeName).length < 2
		if (!(suppresschildren.contains(elem.nodeName))) {
			//divitem.appendChild(document.createTextNode('sup'+suppresschildren.contains(elem.nodeName)));
			inputelem=divitem.appendChild(document.createElement("input"));
			inputelem.setAttribute('type','text');
			name=generate_name_for_element(elem);
			inputelem.setAttribute('name',name);
			inputelem.setAttribute('onchange','update_code2(this,"'+name+'");');
			if (elem.childNodes[0] != null) inputelem.setAttribute('value',unescape_xml(elem.childNodes[0].nodeValue));
			//the next line inserts the parsed value, which is contained in the currentvalue attribute of the tag.
			if ((elem.getAttribute("currentvalue")!= null) && (elem.getAttribute("currentvalue")!=elem.childNodes[0].nodeValue)) {divitem.appendChild(document.createTextNode("=>"+elem.getAttribute("currentvalue")+"\u00a0\u00a0"));}
			//the next line flags a parser error
			if (elem.getAttribute("parseerror")!= null) {inputelem.style.backgroundColor="#FFFF00";inputelem.title=elem.getAttribute("parseerror");}
			if( typeof(window['elem_inputepilogue_'+elem.nodeName]) === "function") {
			// function for child division exists, so we can now call it
				window['elem_inputepilogue_'+elem.nodeName](inputelem,elem);
				}
		}
	}



	//Create a child division 
	if (elem.childNodes.length > 1) {
		var childdiv=listitemnode.appendChild(document.createElement("div"));
		childdiv.setAttribute('id',childdivname);
		childdiv.setAttribute('class','xtsm_'+elem.nodeName+'_body');
		if (elem.getAttribute('expanded')==1) style='display:block'; else style='display:none';
		childdiv.setAttribute('style',style);
		var subtree=childdiv.appendChild(document.createElement("ul"));
		//lit=subtree.appendChild(document.createElement("li"));
		//lit.appendChild(document.createTextNode('supressing:'+suppresschildren));
		var it=elem.firstChild;
// //!suppresschildren.contains(it.nodeName)
		while (it != null) {if (!(suppresschildren.contains(it.nodeName))) tree_output_dom(it,subtree);it=it.nextSibling;}
		if( typeof(window['elem_childepilogue_'+elem.nodeName]) === "function") {
			// function for child division exists, so we can now call it
				window['elem_childepilogue_'+elem.nodeName](childdiv,elem);
				}
		}
	var now=new Date().getTime();
	return xmlDoc;
	
}

function orig_tree_output_dom(elem,tree) {
	if (elem==null) {  
			
			if (window.DOMParser){
			  	parser=new DOMParser();
    			xmlDoc=parser.parseFromString(editor.getValue(),"text/xml");
			  }
			else // Internet Explorer
			  {
			  	xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	   		  	xmlDoc.async=false;
				xmlDoc.loadXML(editor.getValue()); 
			  }
		elem=xmlDoc.childNodes[0];
	}
	//Return immediately if this is a text node
	if (elem.nodeName=='#text') return 0;
	//if the tree is null, append a new element at end of document
	if (tree==null) {
		tree=document.createElement('ul');
		tree=document.body.appendChild(tree);
		tree.setAttribute('name','XMLtree');
		}
	//add a list item for the current DOM element to the current tree element 
	//print a list entry for this node with its node name and important properties, use an "xtsm_XXX" class for its node type
	listitem=document.createElement("li");
	listitemnode=tree.appendChild(listitem);
	divitem=listitemnode.appendChild(document.createElement("div"));

	divitem.setAttribute('class','xtsm_'+elem.nodeName+'_head');

// make a collapsible subdivision if this node has children
	if (elem.childNodes.length > 1) {
		collapse_icon=divitem.appendChild(document.createElement("img"));
		if (elem.getAttribute('expanded')==1)  collapse_icon.setAttribute('src','images/DownTriangleIcon.png'); else collapse_icon.setAttribute('src','images/RightFillTriangleIcon.png');
		collapse_icon.setAttribute('height','12px');
		collapse_icon.setAttribute('title','Expand/Collapse');
		childdivname='divtree__'+generate_name_for_element(elem);
		collapse_icon.setAttribute('onclick','toggleDiv_update_editor(\''+childdivname+'\')');	
		}	
	//Create the topline of the element, get which children should be suppressed in the subsequent calls	
	var suppresschildren=topline_data(elem,divitem);

	//Create a textfield if this is a terminal element of the tree
	//alert(elem.nodeName+(!(suppresschildren.contains(elem.nodeName))));
	if ((elem.childNodes.length < 2))  {
		//suppresschildren.join(';').split(elem.nodeName).length < 2
		if (!(suppresschildren.contains(elem.nodeName))) {
			//divitem.appendChild(document.createTextNode('sup'+suppresschildren.contains(elem.nodeName)));
			inputelem=divitem.appendChild(document.createElement("input"));
			inputelem.setAttribute('type','text');
			name=generate_name_for_element(elem);
			inputelem.setAttribute('name',name);
			inputelem.setAttribute('onchange','update_code2(this,"'+name+'");');
			if (elem.childNodes[0] != null) inputelem.setAttribute('value',unescape_xml(elem.childNodes[0].nodeValue));
			//the next line inserts the parsed value, which is contained in the currentvalue attribute of the tag.
			if ((elem.getAttribute("currentvalue")!= null) && (elem.getAttribute("currentvalue")!=elem.childNodes[0].nodeValue)) {divitem.appendChild(document.createTextNode("=>"+elem.getAttribute("currentvalue")+"\u00a0\u00a0"));}
			//the next line flags a parser error
			if (elem.getAttribute("parseerror")!= null) {inputelem.style.backgroundColor="#FFFF00";inputelem.title=elem.getAttribute("parseerror");}
			if( typeof(window['elem_inputepilogue_'+elem.nodeName]) === "function") {
			// function for child division exists, so we can now call it
				window['elem_inputepilogue_'+elem.nodeName](inputelem,elem);
				}
		}
	}



	//Create a child division 
	if (elem.childNodes.length > 1) {
		var childdiv=listitemnode.appendChild(document.createElement("div"));
		childdiv.setAttribute('id',childdivname);
		childdiv.setAttribute('class','xtsm_'+elem.nodeName+'_body');
		if (elem.getAttribute('expanded')==1) style='display:block'; else style='display:none';
		childdiv.setAttribute('style',style);
		var subtree=childdiv.appendChild(document.createElement("ul"));
		//lit=subtree.appendChild(document.createElement("li"));
		//lit.appendChild(document.createTextNode('supressing:'+suppresschildren));
		var it=elem.firstChild;
// //!suppresschildren.contains(it.nodeName)
		while (it != null) {if (!(suppresschildren.contains(it.nodeName))) tree_output_dom(it,subtree);it=it.nextSibling;}
		if( typeof(window['elem_childepilogue_'+elem.nodeName]) === "function") {
			// function for child division exists, so we can now call it
				window['elem_childepilogue_'+elem.nodeName](childdiv,elem);
				}
		}
	
	return xmlDoc;
	
}

//BELOW ARE UTILITY FUNCTIONS FOR ACTIONS IN THE XML TREE
// this function returns the xml text currently stored in the editor
function get_xml_from_editor(){
		if (window.DOMParser){
		  	parser=new DOMParser();
   			xmlDoc=parser.parseFromString(editor.getValue(),"text/xml");
		  }
		else // the following is necessary if using Internet Explorer
		  {
		  	xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
   		  	xmlDoc.async=false;
			xmlDoc.loadXML(editor.getValue()); 
		  }
	return xmlDoc;
	}
	
function get_elem_to_modify_from_idname(xmlDoc,elem){
	//this function retrieves an element by id string to modify for controls and fields
	//This parses the elem string input to find the appropriate field in the DOM object to change
		update_nodelist=elem.split('__');
	//This loop builds the reference to the appropriate DOM object
		elem_to_modify=xmlDoc;
		for (j=0;j<update_nodelist.length;j++)
			{
			elem_to_modify=elem_to_modify.getElementsByTagName(update_nodelist[j].split('_')[0])[parseInt(update_nodelist[j].split('_')[1])-1];
			}
	return elem_to_modify;
	}
	
// this deletes an item from the XML code stored in the editor.  
function item_delete(elem,userconfirm){
	//This parses the existing XML code into a DOM object
		xmlDoc=get_xml_from_editor();
	//This parses the elem string input to find the appropriate field in the DOM object to change
		elem_to_modify=get_elem_to_modify_from_idname(xmlDoc,elem);
	//This deletes the element upon confirmation by user
		if ((userconfirm != null)&&(userconfirm == 'false')) {elem_to_modify.parentNode.removeChild(elem_to_modify);} else	if (confirm('Deleting '+elem+'  OK?')) elem_to_modify.parentNode.removeChild(elem_to_modify); else return;
	//...and recreates the XML as text
		newxmlstring=XMLtoString(xmlDoc);
	//This replaces the code in the editor window
		editor.setValue(newxmlstring);
		refreshtree();

}	

// the following object stores templates for newly added XML elements, including necessary children with blank values; the tabbing and newlines are aesthetic to help a user see the heirachy better
template = new Object;
template.Sequence="<Sequence>\n\t<Name></Name>\n</Sequence>\n";
template.SubSequence="\n\t<SubSequence>\n\t\t<Name></Name>\n\t\t<Description></Description>\n\t\t<Caption></Caption>\n\t</SubSequence>\n";
template.Edge="\n\t\t<Edge>\n\t\t\t<Name></Name>\n\t\t\t<OnChannel></OnChannel>\n\t\t\t<Time></Time>\n\t\t\t<Value></Value>\n\t\t</Edge>\n";
template.Interval="\n\t\t<Interval>\n\t\t\t<Name></Name>\n\t\t\t<OnChannel></OnChannel>\n\t\t\t<StartTime></StartTime>\n\t\t\t<EndTime></EndTime>\n\t\t\t<Value></Value>\n\t\t\t<Burst>\n\t\t\t\t<Pulses></Pulses>\n\t\t\t\t<Period></Period>\n\t\t\t\t<DutyHigh></DutyHigh>\n\t\t\t\t<Amplitude></Amplitude></Burst>\n\t\t\t<TResolution></TResolution>\n\t\t\t<VResolution></VResolution>\n\t\t\t<Description></Description>\n\t\t\t<Comments></Comments>\n\t\t</Interval>\n";
template.Graph="\n\t\t\t\t<Graph>\n\t\t\t\t<Name />\n\t\t\t\t</Graph>";
template.Channel="\n\t\t\t<Channel>\n\t\t\t\t<Name />\n\t\t\t\t<ChannelName />\n\t\t\t\t<Comments />\n\t\t\t\t<TimingSysChannel />\n\t\t\t\t<TimingGroup />\n\t\t\t\t<TimingGroupIndex />\n\t\t\t\t<ConnectsTo />\n\t\t\t\t<Group />\n\t\t\t\t<HoldingValue />\n\t\t\t\t<Calibration />\n\t\t\t\t<MinValue />\n\t\t\t\t<Maxvalue />\n\t\t\t\t<MaxDuty />\n\t\t\t\t<MaxDuration />\n\t\t\t\t<MaxTriggers />\n\t\t\t</Channel>";
template.TimingGroupData="\n\t\t\t<TimingGroupData expanded='1'>\n\t\t\t\t<Name />\n\t\t\t\t<Description />\n\t\t\t\t<ClockedBy />\n\t\t\t\t<GroupNumber />\n\t\t\t\t<ChannelCount />\n\t\t\t\t<Scale />\n\t\t\t\t<ResolutionBits />\n\t\t\t</TimingGroupData>";
template.Times="\n\t\t\t<Times />";
template.Time="\n\t\t\t<Time />";
template.Value="\n\t\t\t<Value />";
template.Values="\n\t\t\t<Values />";


//the following function adds new items to the XML code stored in the editor; 
function item_add(elem,addtype,atpos){
	//This parses the existing XML code into a DOM object
		xmlDoc=get_xml_from_editor();
	//This parses the elem string input to find the appropriate field in the DOM object to change
		elem_to_modify=get_elem_to_modify_from_idname(xmlDoc,elem);
		//alert('Adding a '+addtype+' node.');
		if (window.DOMParser){
		  	parser=new DOMParser();
   			xmlTemplate=parser.parseFromString(template[addtype],"text/xml");
		  }
		else // Internet Explorer
		  {
		  	xmlTemplate=new ActiveXObject("Microsoft.XMLDOM");
   		  	xmlTemplate.async=false;
			xmlTemplate.loadXML(template); 
		  }
		elem_to_modify.insertBefore(xmlTemplate.getElementsByTagName(addtype)[0],elem_to_modify.childNodes[atpos]);
	//...and recreates the XML as text
		newxmlstring=XMLtoString(xmlDoc);
	//This replaces the code in the editor window
		editor.setValue(newxmlstring);
		refreshtree();

}	

// the following is a utility function to add a node 'node' after the node 'referenceNode' as a child of the element 'parent'
function insertAfter(parent, node, referenceNode) {
  return parent.insertBefore(node, referenceNode.nextSibling);
}


function item_move(elem,distance){
	//This parses the existing XML code into a DOM object
		var xmlDoc=get_xml_from_editor();
	//This parses the elem string input to find the appropriate field in the DOM object to change
		var elem_to_modify=get_elem_to_modify_from_idname(xmlDoc,elem);
	//This moves the element 
	distance=parseInt(distance);
	for(jj=0;jj<Math.abs(distance);jj++){
		var c=elem_to_modify.cloneNode(true);
		if (distance > 0) {if (elem_to_modify.previousSibling == null) break; newNode=elem_to_modify.parentNode.insertBefore(c,elem_to_modify.previousSibling);}
		if (distance < 0) {if (elem_to_modify.nextSibling == null) break;  newNode=elem_to_modify.parentNode.insertBefore(c,elem_to_modify.nextSibling.nextSibling);}
		elem_to_modify.parentNode.removeChild(elem_to_modify);
		}
//		alert(newNode.nodeName);
	//...and recreates the XML as text
		newxmlstring=XMLtoString(xmlDoc);
	//This replaces the code in the editor window
		editor.setValue(newxmlstring);
		refreshtree();
}

//the following function clones a node in the XML code stored in the editor
function item_clone(elem,arg2){
	//This parses the existing XML code into a DOM object
		var xmlDoc=get_xml_from_editor();
	//This parses the elem string input to find the appropriate field in the DOM object to change
		var elem_to_modify=get_elem_to_modify_from_idname(xmlDoc,elem);
	//This clones the element 
		var c=elem_to_modify.cloneNode(true)
		newNode=elem_to_modify.parentNode.insertBefore(c,elem_to_modify);
	//...and recreates the XML as text
		newxmlstring=XMLtoString(xmlDoc);
	//This replaces the code in the editor window
		editor.setValue(newxmlstring);
		refreshtree();
}

function populate_folders(){
	$("#file_operations_p").load("lookup_sequences.php?folder=none",function(data){
		if (document.getElementById("folder_select") != null) document.getElementById("folder_select").parentNode.removeChild(document.getElementById("folder_select"));
		var folders=data.split(',');
		var folderselect=document.getElementById("file_operations").insertBefore(document.createElement('select'),document.getElementById("file_operations").firstChild);
		folderselect.setAttribute('id','folder_select');
		folderselect.options[folderselect.options.length] = new Option('Select Folder:  ', 'none');
		folderselect.options[folderselect.options.length] = new Option('Transforms', '/../transforms');
		for (var k=0; k<folders.length;k++) {folderselect.options[folderselect.options.length] = new Option(folders[k], '/'+folders[k]);}
		folderselect.setAttribute('multiple','multiple');
		folderselect.setAttribute('size','8');
		folderselect.setAttribute('onchange','javascript:populate_files(this);');
		});
}
function populate_files(folder_select){
	$("#file_operations_p").load("lookup_sequences.php?folder="+folder_select.value,function(data){
		if (document.getElementById("file_select") != null) document.getElementById("file_select").parentNode.removeChild(document.getElementById("file_select"));
		var files=data.split(',');
		var fileselect=document.getElementById("file_operations").insertBefore(document.createElement('select'),document.getElementById("file_operations").firstChild.nextSibling);
		fileselect.setAttribute('id','file_select');
		fileselect.options[fileselect.options.length] = new Option('Select File:  ', 'none');
		for (var k=0; k<files.length;k++) {fileselect.options[fileselect.options.length] = new Option(files[k], files[k]);}
		fileselect.setAttribute('multiple','multiple');
		fileselect.setAttribute('size','8');
		fileselect.setAttribute('onchange','javascript:populate_file_info();');
		//		fileselect.setAttribute('disabled','false');
//		filenamelabel=fileselect.parentNode.insertBefore(document.createTextNode('Or Enter New Filename:'),fileselect.nextSibling);
		if (document.getElementById("newfile") != null) document.getElementById("newfile").parentNode.removeChild(document.getElementById("newfile"));
		newfileinput=fileselect.parentNode.insertBefore(document.createElement('input'),fileselect.nextSibling);
		newfileinput.setAttribute('id','newfile');
		newfileinput.setAttribute('value','or type new filename here');
		newfileinput.setAttribute('onfocus','javascript:this.value="";this.previousSibling.setAttribute("disabled","true");');
		newfileinput.setAttribute('onblur','javascript:change_newfileinput(this);');
		});
	if (document.getElementById("folder_select").selectedIndex==1) {document.getElementById("file_op_target_input").selectedIndex=1;};
	if (document.getElementById("folder_select").selectedIndex!=1) {document.getElementById("file_op_target_input").selectedIndex=0;};

}
function change_newfileinput(newfileinput) {
	if (newfileinput.value=='') {document.getElementById('file_select').removeAttribute('disabled'); newfileinput.value='or type new filename here';} else {
		document.getElementById('save_file').value=("c:/wamp/www/sequences/"+document.getElementById('folder_select').value+"/"+newfileinput.value).replace('//','/');
	}
	
}
function populate_file_info(){
	document.getElementById('file_info_div').innerHTML='File:&nbsp;<b>'+document.getElementById('folder_select').value+'/'+document.getElementById('file_select').value+'</b><div id="file_desc">Description:</div><br />Last Saved: <br />Last Saved By: ';
	document.getElementById('load_file').value=("c:/wamp/www/sequences/"+document.getElementById('folder_select').value+"/"+document.getElementById('file_select').value).replace('//','/');
	//$.get(document.getElementById('load_file').value.split('c:/wamp/www').pop(),function(data){document.getElementById('file_desc').innerHTML=data.match(/<![ \r\n\t]*(--([^\-]|[\r\n]|-[^\-])*--[ \r\n\t]*)\>/ ); }); 

}
function xtsm_load(){
	if (file_op_target_input.value=='Experiment XML') {	$("#code").load(document.getElementById('load_file').value.split('c:/wamp/www').pop(),function(data){editor.setValue(data);refreshtree();});}
	if (file_op_target_input.value=='Transform XSLT') {	
		//$("#xslt_transform_textarea").load(document.getElementById('load_file').value.split('c:/wamp/www').pop(),function(data){});
		$.get(document.getElementById('load_file').value.split('c:/wamp/www').pop(),function(data){document.getElementById('xslt_transform_textarea').value=XMLtoString(data);});
		document.getElementById("xslt_transform_textarea").removeAttribute("onfocus");
	}
}
function xtsm_save(){
//	document.write(editor.getValue());
	t0=Date.now();
	$.post("save_xtsm.php",{filename: $.base64.encode(document.getElementById('save_file').value.split('c:/wamp/www/').pop()), filedata: $.base64.encode(editor.getValue())},function(data){$("#file_message").html("<span style='color: red;'>"+data+"in "+(Date.now()-t0)+"ms</span>");});
}
function save_something(){
	var target=document.getElementById("file_op_target_input").options[document.getElementById("file_op_target_input").selectedIndex].text;
	if (target=="Experiment XML") {xtsm_save();}
	if (target=="Transform XSLT") {
		t0=Date.now();
		$.post("save_xtsm.php",{filename: $.base64.encode(document.getElementById('save_file').value.split('c:/wamp/www/').pop()), filedata: $.base64.encode(document.getElementById('xslt_transform_textarea').value)},function(data){$("#file_message").html("<span style='color: red;'>"+data+"in "+(Date.now()-t0)+"ms</span>");});
	}
}

function post_xtsm(){
	var tboundary='--asJKbb173';
	var transferdata = new Array();
	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+'set_global_variable_from_socket'+'\n\r--'+tboundary+'--\n\r';

//transform editor contents to disable xml where warranted.
	if ($("#disables_on_post_button").is(':checked')) {
		//get appropriate xsl transform
		$.ajax({
			  url: 'transforms/disable_att_to_prefix.xsl',
			  success: function(data){xslstring=data;}, 
			  dataType: 'text',
			  async: false
			});
		var test=editor.getValue();
		var XMLcont=xslt_transform_string(test,xslstring);
		} else {var XMLcont=editor.getValue();}

// remove whitespace if option is selected, build content string
	if ($("#compress_on_post_button").is(':checked')) {transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="ACTIVE_XTSM"\n\r\n\r'+XMLcont.replace(/[>]\s*[<]/g,"><")+'\n\r--'+tboundary+'--\n\r';} else {transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="ACTIVE_XTSM"\n\r\n\r'+XMLcont+'\n\r--'+tboundary+'--\n\r';}

	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
	transferdata=transferdata.join("");
	//alert(transferdata);
	$.ajax({
		url: 'http://127.0.0.1:8083',
		type: 'POST',
		contentType: 'multipart/form-data; boundary='+tboundary,
		processData: false,
		data: transferdata,
		success: function (result) {
			//alert(result);
		}
		});
}

function retrieve_xtsm(){
	var tboundary='--asJKbb173';
	var transferdata = new Array();
	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+'get_global_variable_from_socket'+'\n\r--'+tboundary+'--\n\r';
	transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="variablename"\n\r\n\r'+'ACTIVE_XTSM'+'\n\r--'+tboundary+'--\n\r';
	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
	transferdata=transferdata.join("");
	$.ajax({
		url: 'http://localhost:8083',
		type: 'POST',
		contentType: 'multipart/form-data; boundary='+tboundary,
		processData: false,
		data: transferdata,
		success: function (result) {
			result=result.substring(16);
			result=result.substring(result.indexOf('<XTSM'),result.lastIndexOf('</XTSM')+7);
			if($("#disables_on_post_button").is(':checked'))  {
				$.ajax({
				  url: 'transforms/disable_prefix_to_att.xsl',
				  success: function(data){xslstring=data;}, 
				  dataType: 'text',
				  async: false
				});
				var test=editor.getValue();
				var procresult=xslt_transform_string(result,xslstring);
				} else {var procresult=result;}
			if ($("#compress_on_post_button").is(':checked')) {
				//CodeMirror.commands["selectAll"](editor);
				editor.setValue(procresult.replace(/[>][<]/g,">\n<"));
				refreshtree();
				//editor.autoFormatRange(3, 100);
				} else {editor.setValue(procresult);refreshtree();}
		}
		});
}
//function define_idl_global(name,value,successfunction) {
//	var tboundary='--asJKbb173';
//	var transferdata = new Array();
//	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+'set_global_variable_from_socket'+'\n\r--'+tboundary+'--\n\r';
//	transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="'+name+'"\n\r\n\r'+value+'\n\r--'+tboundary+'--\n\r';
//	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
//	transferdata=transferdata.join("");
//	$.ajax({
//		url: 'http://localhost:8083',
//		type: 'POST',
//		contentType: 'multipart/form-data; boundary='+tboundary,
//		processData: false,
//		data: transferdata,
//		success: function(result) {}
//	});	
//}
//
function test_idltransferspeed(data_length,results) {
	var tboundary='--asJKbb173';
	if (data_length == null) data_length=10;
	if (data_length > 10000000.) {
		// this is the exiting process
		document.getElementById("IDLspeed_result").innerHTML=results+"</table>";
		//var newdiv=document.getElementById("IDLspeed_result").appendChild(document.createElement('div'));
		//make_simple_graph(newdiv,results.substring(2)+'___'+results.substring(2),['time','timeagain']);
		return 1;
		}
	if (results == null ) results='<br /><table border="1"><tr><td><b>GUI<>IDL SpeedTest</b></td><td colspan="4">Time (ms)</td></tr><tr><td align="right">Size (Bytes)</td><td>IDL read</td><td>IDL write</td><td>IDL init</td><td>Ajax Roundtrip</td></tr>';
	var transferdata = new Array();
	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+'set_global_variable_from_socket'+'\n\r--'+tboundary+'--\n\r';
	transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSPEEDTEST"\n\r\n\r'+randomString(data_length)+'\n\r--'+tboundary+'--\n\r';
	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
	transferdata=transferdata.join("");
	var ajaxtime=new Date().getTime();
	$.ajax({
		url: 'http://127.0.0.1:8083',
		type: 'POST',
		contentType: 'multipart/form-data; boundary='+tboundary,
		processData: false,
		data: transferdata,
		success: function (result) {
			result=result.substring(16);
			result=result.substring(data_length+39);
			var now=new Date().getTime();
			setTimeout(function(){
				results+='<tr><td align="right">'+data_length.toExponential(2)+'&nbsp;</td><td>'+((result.split(',')).slice(0,3)).join('</td><td>')+'</td><td>'+(now-ajaxtime)+'</td></tr>';
				//document.getElementById("IDLspeed_result").setAttribute('value',document.getElementById("IDLspeed_result").getAttribute('value')+'__'+data_length+'_'+result);
				data_length*=10;
				test_idltransferspeed(data_length,results);
			},.3);			
		},
//		trycount: 0,
//		retrylimit: 10,
//		timeout: 200,
//		error: function(jqXHR, textStatus)  { 
//			if (textStatus == 'timeout') {
//				this.trycount++;
//				if (this.trycount <= this.retrylimit) {$.ajax(this);return;}
//				} else alert("too many tries");
//			}

	});
}

function randomString(string_length) {
	
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var randomstring = '';
	for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		randomstring += chars.substring(rnum,rnum+1);
	}
	return randomstring;	
}
function disable_idl_socket(){
	$.get('http://localhost:8083?stop_listening=1',function(data){
		$("#parser_span").html('IDL Socket Deactivated');
		$("#parse_post_button").attr('disabled','disabled');
		$("#post_xtsm_button").attr('disabled','disabled');
		$("#retrieve_xtsm_button").attr('disabled','disabled');
		$("#disable_idl_socket_button").attr('disabled','disabled');
		$("#test_idltransferspeed_button").attr('disabled','disabled');
		//for (var j=0; j<100; j++) {setTimeout(function(){document.getElementById('code_dest_file').value+='.';},10000);}
		ping_count=0;
		idl_pinger=setInterval(ping_idl,1000);
		});	
}


function ping_idl(){
	document.getElementById('ping_counter').value=ping_count.toString();
	ping_count++;

	var tboundary='--asJKbb173';
	var transferdata = new Array();
	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+'ping_idl_from_socket'+'\n\r--'+tboundary+'--\n\r';
	transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="variablename"\n\r\n\r'+'nullvar'+'\n\r--'+tboundary+'--\n\r';
	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
	transferdata=transferdata.join("");
	//alert(transferdata);
	$.ajax({
		url: 'http://127.0.0.1:8083',
		type: 'POST',
		contentType: 'multipart/form-data; boundary='+tboundary,
		processData: false,
		data: transferdata,
		success: function (result) {
			window.clearInterval(idl_pinger);
			$("#parser_span").html('');
			$("#parse_post_button").removeAttr('disabled');
			$("#post_xtsm_button").removeAttr('disabled');
			$("#retrieve_xtsm_button").removeAttr('disabled');
			$("#disable_idl_socket_button").removeAttr('disabled');
			$("#test_idltransferspeed_button").removeAttr('disabled');
			document.getElementById('ping_counter').value='connected';
		}
		});
}

function background_ping_idl(){
	var tboundary='--asJKbb173';
	var transferdata = new Array();
	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+'ping_idl_from_socket'+'\n\r--'+tboundary+'--\n\r';
	transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="variablename"\n\r\n\r'+'nullvar'+'\n\r--'+tboundary+'--\n\r';
	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
	transferdata=transferdata.join("");
	//alert(transferdata);
	$.ajax({
		url: 'http://127.0.0.1:8083',
		type: 'POST',
		contentType: 'multipart/form-data; boundary='+tboundary,
		processData: false,
		data: transferdata,
		success: function (result) {
			window.clearInterval(background_idl_pinger);
			$("#parser_span").html('');
			$("#parse_post_button").removeAttr('disabled');
			$("#post_xtsm_button").removeAttr('disabled');
			$("#retrieve_xtsm_button").removeAttr('disabled');
			$("#disable_idl_socket_button").removeAttr('disabled');
			$("#test_idltransferspeed_button").removeAttr('disabled');
			document.getElementById('ping_counter').value='connected';
		},
		error: function () {
			$("#parser_span").html('IDL Socket Inactive');
			$("#parse_post_button").attr('disabled','disabled');
			$("#post_xtsm_button").attr('disabled','disabled');
			$("#retrieve_xtsm_button").attr('disabled','disabled');
			$("#disable_idl_socket_button").attr('disabled','disabled');
			$("#test_idltransferspeed_button").attr('disabled','disabled');
			document.getElementById('ping_counter').value='';		
		}
		});
}

function launch_idl(){
	$.get('launch_idl.php',function(data){
		$("#parser_span").html('');
		$("#parse_post_button").removeAttr('disabled');
		$("#post_xtsm_button").removeAttr('disabled');
		$("#retrieve_xtsm_button").removeAttr('disabled');
		$("#disable_idl_socket_button").removeAttr('disabled');
		$("#test_idltransferspeed_button").removeAttr('disabled');
		});	
}
</script>
<body><table width="100%"><tr><td width="2%" align="center" onclick="javascript:alert('rewind!');" bgcolor="#CC0000"><div style="color:white" title="Advance to next shot">&lt;<br /> S<br />H<br/>O<br/>T<br/><br/>#<br/>&lt;<br /></div></td><td width="96%">

<div style="border:1px solid;"><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('file_operations'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">File Operations<img src="images/seqicon_refresh.png" height="20px" onclick="javascript:populate_folders();" onload="javascript:populate_folders();" title="Refresh" />&nbsp;&nbsp;Target:<select id="file_op_target_input"><option>Experiment XML</option><option>Transform XSLT</option></select>
<table border="1"><tr><td valign="top"><div id="file_operations" style="display:none"><p id="file_operations_p" style="display:none"/></div></td><td valign="top">File to Load:<input type="text" id="load_file" size="50"/><img src="images/seqicon_load.png" onclick="javascript:xtsm_load();" height="20px" title="Load Now"/><br /> <img src="images/DownTriangleIcon.png" height="18" onclick="document.getElementById('save_file').value=document.getElementById('load_file').value"><img src="images/UpTriangleIcon.png" height="18" onclick="document.getElementById('load_file').value=document.getElementById('save_file').value"><br />File to Save:<input type="text" id="save_file" size="50"/><img src="images/seqicon_save.png" onclick="javascript:save_something();" height="20px" title="Save Now" /><div id='file_message' ></div></td><td valign="top"><div id='file_info_div'></div></td></tr></table></div>


<?php 
//// This is the front-end of the script that shows the user files to choose 
//	print("Show sequences contained in folder: ");
//	IF (isset($_REQUEST['folder'])) {$folder=$_REQUEST['folder'];} ELSE {$folder='';}
//	$folders=scandir("c:/wamp/www/sequences");
//	$folders=array_reverse($folders);
//	// Reorder folders by date
//	for ($j=0;$j<count($folders);$j++){
//		IF ($folders[$j]=='.'){continue;}
//		IF ($folders[$j]=='..'){continue;}
//		$dateparts=explode('-',$folders[$j]);
//		IF (count($dateparts)==3){
//			$folderdates[$j]=strtotime($dateparts[2].'/'.$dateparts[0].'/'.$dateparts[1]);
//			$folderskeyedbydate[$folderdates[$j]]=$folders[$j];
//		}
//	}
//	ksort($folderskeyedbydate);
//	$folders=array_reverse($folderskeyedbydate);
//	print('<form name="form" id="form"><select name="jumpMenu" id="jumpMenu" onchange="MM_jumpMenu(\'self\',this,0)"><option value='.$_SERVER['PHP_SELF'].'> Choose folder from below </option>');
//	for ($j=0;$j<count($folders);$j++){
//		IF ($folders[$j]=='.'){continue;}
//		IF ($folders[$j]=='..'){continue;}
//		IF (is_dir("c:/wamp/www/sequences/".$folders[$j])) {
//			$queryarray=$_REQUEST;
//			$queryarray['folder']=$folders[$j];
//			$qstring=http_build_query($queryarray);
//			//print('<option value="viewlabphotos.php?folder='.$folders[$j].'" ');
//			print('<option value="Sequence_Viewer3.php?'.$qstring.'" ');
//			IF ($folders[$j]==$folder) {print("selected=\"selected\" ");}
//			print('>'.$folders[$j]);
//			IF($folders[$j]==date('n-j-Y')) {print(" (Today) ");}
//			IF($folders[$j]==date('n-j-Y',mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")))) {print(" (Yesterday) ");}
//			print('</option>');
//		}
//	}
//	print(" </select></form>");
//IF (!(isset($_REQUEST['file']))) {$seq='';} ELSE {$seq=$_REQUEST['file'];}
//IF (isset($_REQUEST['folder']) ){	
//	$seqs=scandir("c:/wamp/www/sequences/".$folder);
//	print('<form name="form2" id="form2"><select name="jumpMenu" id="jumpMenu" onchange="MM_jumpMenu(\'self\',this,0)"><option value='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'> Choose folder from below </option>');
//	for ($j=0;$j<count($seqs);$j++){
//		IF ($seqs[$j]=='.'){continue;}
//		IF ($seqs[$j]=='..'){continue;}
//			$queryarray=$_REQUEST;
//			$queryarray['file']=$seqs[$j];
//			unset($queryarray['code']);
//			$qstring=http_build_query($queryarray);
//			//print('<option value="viewlabphotos.php?folder='.$folders[$j].'" ');
//			print('<option value="Sequence_Viewer3.php?'.$qstring.'" ');
//			IF ($seqs[$j]==$seq) {print("selected=\"selected\" ");}
//			print('>'.$seqs[$j]);
//			print('</option>');		
//		}
//	print('</select></form>');
//	}
//IF (isset($_REQUEST['code'])) {$code=$_REQUEST['code'];} ELSE IF (isset($_REQUEST['file'])) {$code=file_get_contents("c:/wamp/www/sequences/".$folder."/".$_REQUEST['file']);} ELSE {$code='';}
//
?>

<!-- These lines create the XML code editor window; the code editor is a generic way of making any change needed to the XML file, and to show the user what is there. -->
<p><p><div style="border:1px solid"><img src="images/DownTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('editor_div'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">
<b><span style="color:red">Source Data Editor:</span></b><p><div id="editor_div">
<form name="codeform" id="codeform" action="<?php //print('Sequence_Save.php?'.$qstring);?>" method="post">

	<script src="codemirror/lib/codemirror.js"></script>
    <link rel="stylesheet" href="codemirror/lib/codemirror.css">
	    <script src="codemirror/lib/util/closetag.js"></script>
	<script src="codemirror/mode/xml/xml.js"></script>
	<script src="codemirror/mode/javascript/javascript.js"></script>
	<script src="codemirror/mode/css/css.js"></script>
    <script src="codemirror/mode/htmlmixed/htmlmixed.js"></script>

    <script src="codemirror/mode/xmlpure/xmlpure.js"></script>

<!-- The following textarea is where the XML code is stored -->
    <textarea name="code" id="code">
    <?php //IF (substr($code,0,2)=="<?") {$codelines=explode("\n",$code);unset($codelines[0]);$code=(implode("\n",$codelines));} print($code); ?>
    </textarea>

    <script> 
// the following lines create a codeMirror object and link it to the textarea just created 	
// the onBlur event was originally an onChange - if we are missing reflection of updates in the tree, this is why.
	     var editor = CodeMirror.fromTextArea(document.getElementById("code"), {mode: "text/html", gutter:"True",  lineNumbers:"True", onBlur: function(){refreshtree();}, linewrapping:"True" });
    </script></div></div>
<!-- The following lines create a text field for entry of a file name to save at.  By default the folder is updated to today's date -->
<!--    Destination Filename: 
    <input type="text" id="code_dest_file" name="code_dest_file" size="70" value="<?php print("sequences/".date("n-j-Y")."/");IF (isset($_REQUEST['file'])) print($_REQUEST['file']);?>"/><input type="text" hidden="hidden" name="returnto" value="<?php print($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']); ?>">
    <input type="submit" value="Save Now (Alt-s)" id="save_editor_content_button" />-->
</form>
<script language="javascript">

	function XMLtoString(elem){
		var serialized;
		try {
			// XMLSerializer exists in current Mozilla browsers
			serializer = new XMLSerializer();
			serialized = serializer.serializeToString(elem);
		} 
		catch (e) {
			// Internet Explorer has a different approach to serializing XML
			serialized = elem.xml;
		}
		return serialized;
	}

	function update_code2(calling_element,update_elem){
//THIS FUNCTION UPDATES THE XML STRING BASED AFTER INPUT FIELDS ARE CHANGED IN THE EDITABLE XML TREE
//IT MUST BE PASSED THE OBJECT CORRESPONDING TO THE CHANGED FIELD, AND A STRING REFERRING TO THE ELEMENT IN THE XML TO BE CHANGED
//THE LATTER SHOULD BE PARSED AS NODENAME_NUMBER__NODENAME_NUMBER__...

//These lines record the window position at the time the input field was typed in
		var vscroll = (document.all ? document.scrollTop : window.pageYOffset);
		var hscroll = (document.all ? document.scrollLeft : window.pageXOffset);
		
//This brings in the changed field text
		newvalue=calling_element.value;
				//alert(update_elem);

//This parses the existing XML code into a DOM object
		if (window.DOMParser){
			  parser=new DOMParser();
    			xmlDoc=parser.parseFromString(editor.getValue(),"text/xml");

			  }
			else // Internet Explorer
			  {
			  xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
			  xmlDoc.async=false;
    			xmlDoc.loadXML(editor.getValue()); 

			  }

//This parses the update_elem string input to find the appropriate field in the DOM object to change
		update_nodelist=update_elem.split('__');
		origxmllines=editor.getValue().split('\n');

//This loop builds the reference to the appropriate DOM object
		elem_to_modify=xmlDoc;
		for (j=0;j<update_nodelist.length;j++)
			{
			if (elem_to_modify.getElementsByTagName(update_nodelist[j].split('_')[0])[parseInt(update_nodelist[j].split('_')[1])-1] != 0) {
				elem_to_modify=elem_to_modify.getElementsByTagName(update_nodelist[j].split('_')[0])[parseInt(update_nodelist[j].split('_')[1])-1];
				} 
			}	
		
//This changes the value of the right node in the DOM object...
		if (elem_to_modify.hasChildNodes())  {elem_to_modify.childNodes[0].nodeValue=escape_xml(calling_element.value); } 
		if (!elem_to_modify.hasChildNodes())  {
			x=xmlDoc.createTextNode(escape_xml(calling_element.value));
			elem_to_modify.appendChild(x);
			//alert(elem_to_modify.childNodes[0].nodeValue);
			} 

//...and recreates the XML as text
		newxmlstring=XMLtoString(xmlDoc);
		
//This helps us identify the changed line in the XML code to highlight it in the editor window
		newxmlstringlines=newxmlstring.split('\n');
		
//This loop identifies the first changed line in the XML code
		for(j=0;j<origxmllines.length; j++ ) {
			if (origxmllines[j] != newxmlstringlines[j]) {
				break;
				}
			}

//This replaces the code in the editor window
		editor.setValue(newxmlstring);
//this next element was added when the editor event was changed to onBlur()
		refreshtree();
		
//The following lines highlight the changed code in the editor window
		linemarker=editor.setMarker(j,'-->');
		pos= new Object();
		pos.line=j;
		pos.ch=0;
		pos2= new Object();
		pos2.line=j;
		pos2.ch=0;
		editor.setCursor(j,0);


//This line pinks-out the background of line in editor
		editor.markText(pos,pos2,"CodeMirror-matchhighlight2");

//And this pinks-out the input element background in the XML tree - this however is overwritten by editor's refresh of tree onChanges
		calling_element.style.backgroundColor="#ffcccc";
		//This returns the window location to where it was when the value was originally typed into the input field
		window.scrollTo(hscroll, vscroll);
	}
function update_code_fromtree(xmlDocin){
	var newxmlstring=XMLtoString(xmlDocin);
	// this following line is problematic - if an html element is added to the page above the xml tree editor, and below code mirror, this next line never returns
	editor.setValue(newxmlstring);
	refreshtree();

	return 1;
}
function parse_variables(){
	var d= new Date();
	document.parseform.parseStartTime.value=d.getTime()
	document.parseform.xmlstring.value=editor.getValue();
}
function displayparsetime(){
	var d= new Date();
	document.parsetimediv=d.getTime()
}

function getOffset( el ) {
    var _x = 0;
    var _y = 0;
    while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) ) {
        _x += el.offsetLeft - el.scrollLeft;
        _y += el.offsetTop - el.scrollTop;
        el = el.offsetParent;
    }
    return { top: _y, left: _x };
}

	function search_dom(termfield) {
		//This parses the existing XML code into a DOM object
		if (window.DOMParser){
			  	var parser2=new DOMParser();
    			var xmlDoc2=parser2.parseFromString(editor.getValue(),"text/xml");
			  }
			else // Internet Explorer
			  {
			  	var xmlDoc2=new ActiveXObject("Microsoft.XMLDOM");
			  	xmlDoc2.async=false;
    			xmlDoc2.loadXML(editor.getValue()); 
			  }
		var searchresult=xmlDoc2.evaluate("//*[text()[contains(.,'"+termfield.value+"')]]" ,xmlDoc2,null, XPathResult.ORDERED_NODE_ITERATOR_TYPE,null);
		resultslistsuperdiv=document.getElementById('search_results_div');//document.body.appendChild(document.createElement('div'));
		resultslistdiv=resultslistsuperdiv.appendChild(document.createElement('div'));
		var delicon=resultslistdiv.appendChild(document.createElement("img"));
		delicon.setAttribute('src','images/seqicon_item_delete.png');
		delicon.setAttribute('height','12px');
		delicon.setAttribute('title','Delete results');
		delicon.setAttribute('onclick','javascript:this.parentNode.parentNode.removeChild(this.parentNode);');
		resultslistdiv.appendChild(document.createTextNode("  Results for '"));
		var ital=resultslistdiv.appendChild(document.createElement("i"));
		ital.appendChild(document.createTextNode(termfield.value));
		resultslistdiv.appendChild(document.createTextNode("':"));
		resultslist=resultslistdiv.appendChild(document.createElement('ul'));
		resultslist.setAttribute("style","list-style-type:none");
		var result=searchresult.iterateNext();
		while (result){
			var listitem=resultslist.appendChild(document.createElement('li'));
			var jumpicon=listitem.appendChild(document.createElement('img'));
			jumpicon.setAttribute('src','images/seqicon_go.png');
			jumpicon.setAttribute('height','18px');
			jumpicon.setAttribute('title','Jump to this element');
			jumpicon.setAttribute('onclick','javascript:searchjump(\''+generate_name_for_element(result.parentNode)+'\');this.setAttribute("src","images/seqicon_gone.png")');
			var matchtext=result.childNodes[0].nodeValue.split(termfield.value);
			listitem.appendChild(document.createTextNode(result.parentNode.nodeName+" : "+result.nodeName+"='"+matchtext[0]));
			var underscored=listitem.appendChild(document.createElement('u'));
			underscored.appendChild(document.createTextNode(termfield.value));
			listitem.appendChild(document.createTextNode(matchtext[1]+"'"));
			result=searchresult.iterateNext();
			}
		}

	function populate_search_fields() {
		alert('populating fields');
		}

	function collect_tag_types(xmlDocin) {
		
		}

	function searchjump(name){
		//This parses the existing XML code into a DOM object
		if (window.DOMParser){
			  	var parser2=new DOMParser();
    			var xmlDoc2=parser2.parseFromString(editor.getValue(),"text/xml");
			  }
			else // Internet Explorer
			  {
			  	var xmlDoc2=new ActiveXObject("Microsoft.XMLDOM");
			  	xmlDoc2.async=false;
    			xmlDoc2.loadXML(editor.getValue()); 
			  }
		var elem=get_elem_to_modify_from_idname(xmlDoc2,name);
		highlight_expand(elem);
		}

	function highlight_expand(elem) {
		var nextelem=elem;
		while (nextelem.nodeName != 'XTSM') {
			nextelem.setAttribute('expanded',"1");
			nextelem=nextelem.parentNode;
			}
		update_code_fromtree(nextelem);
		var divid='divtree__'+generate_name_for_element(elem);
		document.getElementById(divid).setAttribute('class',"highlighted");
		document.getElementById(divid).previousSibling.setAttribute('class',"highlighted");
		document.getElementById(divid).previousSibling.scrollIntoView();
		}
	function change_font(fontname){
		fonts=document.getElementById('stylesheet0').innerHTML.split('font-family:');
		for (var j=1;j<fonts.length;j++) {
			fonthere=fonts[j].split(';');
			fonthere[0]=fontname;
			fonts[j]=fonthere.join(';');
		}
		document.getElementById('stylesheet0').innerHTML=fonts.join('font-family:');
	}
	function darken_all(button) {
		colors=document.getElementById('stylesheet0').innerHTML.split('input')
		colors=colors[0].split('#');
		newHTML=colors[0];
		for (var j=1;j<colors.length;j++) {
			thiscolor='#'+colors[j].substring(0,6);
			R = hexToR(thiscolor);
			G = hexToG(thiscolor);
			B = hexToB(thiscolor);
			ocolor=rgbToHsv(R,G,B);
			if (button.value=='Dark Room Color') ncolor=hsvToRgb(ocolor[0],ocolor[1],100-0.6*ocolor[2]); else ncolor=hsvToRgb(ocolor[0],ocolor[1],(166.7-1.667*ocolor[2]));
			newHTML+='#'+rgbToHex(ncolor[0],ncolor[1],ncolor[2])+colors[j].substring(6);
			}
		if (button.value=='Dark Room Color') newHTML+="\n input{background:#333333;color:#ffffff;border:#333333} \n body{background:#666666; color:#ffffff} a{color:#ffffff}"; else newHTML+="\n input{background:#ffffff;color:#000000;} \n body{background:#ffffff; color:#000000}";
		document.getElementById('stylesheet0').innerHTML=newHTML;
		if (button.value=='Dark Room Color') button.value='Bright Room Color'; else button.value='Dark Room Color';		
		}
		
function loadXMLDoc(dname){
	if (window.XMLHttpRequest)
	  {
	  xhttp=new XMLHttpRequest();
	  }
	else
	  {
	  xhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	xhttp.open("GET",dname,false);
	xhttp.send("");
	return xhttp.responseXML;
}

function xslt_transform() {

	var xsltext=document.getElementById("xslt_transform_textarea").value;
	var xslparser=new DOMParser();
	var xsl=xslparser.parseFromString(xsltext,"text/xml");

	var docparser=new DOMParser();
	var xml=docparser.parseFromString(editor.getValue(),"text/xml");
	
	var xsltProcessor=new XSLTProcessor();
	xsltProcessor.importStylesheet(xsl);
  	ex = xsltProcessor.transformToFragment(xml,document);
	exs=XMLtoString(ex);
	
	if (document.getElementById("transform_target_input").value=='Experiment XML') {
		editor.setValue(exs);
		refreshtree();
	}
	if (document.getElementById("transform_target_input").value=='New Tab') {
		var newwin=window.open();
		newwin.document.write(exs);
		newwin.document.close();
	}
}
function xslt_transform_string(instring,xsltext) {

	var xslparser=new DOMParser();
	var xsl=xslparser.parseFromString(xsltext,"text/xml");

	var docparser=new DOMParser();
	var xml=docparser.parseFromString(instring,"text/xml");
	
	var xsltProcessor=new XSLTProcessor();
	xsltProcessor.importStylesheet(xsl);
  	ex = xsltProcessor.transformToFragment(xml,document);
	exs=XMLtoString(ex);
	return exs;	
}

function execute_idl_direct(command,returnfunction){
	var tboundary='--asJKbb173';
	var transferdata = new Array();
	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+'execute_from_socket'+'\n\r--'+tboundary+'--\n\r';
	transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="command"\n\r\n\r'+command+'\n\r--'+tboundary+'--\n\r';
	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
	transferdata=transferdata.join("");
	$.ajax({
		url: 'http://127.0.0.1:8083',
		type: 'POST',
		contentType: 'multipart/form-data; boundary='+tboundary,
		processData: false,
		data: transferdata,
		success: function(result){returnfunction(result);}
	});
}

function execute_idl(){
	var tboundary='--asJKbb173';
	var transferdata = new Array();
	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+'execute_from_socket'+'\n\r--'+tboundary+'--\n\r';
	transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="command"\n\r\n\r'+document.getElementById("idl_console_send_textarea").value+'\n\r--'+tboundary+'--\n\r';
	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
	transferdata=transferdata.join("");
	if (idlcommandhistory[idlcommandhistory.length-1] != document.getElementById("idl_console_send_textarea").value) {
		idlcommandhistory.push(document.getElementById("idl_console_send_textarea").value);
		idlcommandindex=idlcommandhistory.length ;
		}
	//alert(transferdata);
	$.ajax({
		url: 'http://127.0.0.1:8083',
		type: 'POST',
		contentType: 'multipart/form-data; boundary='+tboundary,
		processData: false,
		data: transferdata,
		success: function (result) {
			var consoleresult=((result.substring(16).split('--MainLevelVariables--'))[0]).replace(/\n\r/g,"\n");
			if (document.getElementById("idl_console_return_textarea").value == 'IDL responses will appear here.') {document.getElementById("idl_console_return_textarea").value='';}
			document.getElementById("idl_console_return_textarea").value+=consoleresult;
			document.getElementById("idl_console_return_textarea").scrollTop = document.getElementById("idl_console_return_textarea").scrollHeight
			//document.getElementById("idl_console_send_textarea").setAttribute("onfocus","this.value='';")
			var varresult=((result.split('--MainLevelVariables--'))[1]).split(';');
			var ol=document.getElementById("idl_variable_textarea").options.length;
			for (var j=1; j<=ol;j++){document.getElementById("idl_variable_textarea").options.remove(1);}
			for (var j=0; j<varresult.length;j++){
				newopt=document.createElement('option');
				newopt.text=varresult[j];
				document.getElementById("idl_variable_textarea").add(newopt,null);
			}
			//alert(result);
		}
		});

}
function IDLvarChange(selectelem){
	if (selectelem.selectedIndex != 0) {
		document.getElementById("idl_console_send_textarea").value+="scope_varfetch('"+selectelem.options[selectelem.selectedIndex].text+"',level=1)";
	}

}
function idl_send_keydown(tevent){
	if (tevent.keyCode == 38) {
		//up arrow pressed, go to previous command in history
		if (document.getElementById("idl_console_send_textarea").selectionStart < document.getElementById("idl_console_send_textarea").cols) {
			if (idlcommandindex > 0) {idlcommandindex--;} 
			document.getElementById("idl_console_send_textarea").value=idlcommandhistory[idlcommandindex];
			}
	}
}
function idl_send_keyup(tevent){
		if (tevent.keyCode == 40) {
		//down arrow pressed, go to next command in history
		if (idlcommandindex < (idlcommandhistory.length-1)) {idlcommandindex++;} 
		document.getElementById("idl_console_send_textarea").value=idlcommandhistory[idlcommandindex];
		} else if (tevent.keyCode == 13) {
		//enter key pressed, submit
		curs=document.getElementById("idl_console_send_textarea").selectionStart;
		document.getElementById("idl_console_send_textarea").value=document.getElementById("idl_console_send_textarea").value.substring(0,curs-1)+ document.getElementById("idl_console_send_textarea").value.substring(curs,document.getElementById("idl_console_send_textarea").value.length);
		execute_idl();
		document.getElementById("idl_console_send_textarea").value='';
		} else 	if (tevent.keyCode == 27) {
		//escape key pressed, empty field
		document.getElementById("idl_console_send_textarea").value='';
	} else if (tevent.keyCode == 36) {
		//home key pressed, jump to first command
		idlcommandindex = 0 ;
		document.getElementById("idl_console_send_textarea").value=idlcommandhistory[idlcommandindex];
	} else if (tevent.keyCode == 35) {
		//end key pressed, jump to last command
		idlcommandindex = idlcommandhistory.length -1 ;
		document.getElementById("idl_console_send_textarea").value=idlcommandhistory[idlcommandindex];
	} else if ((tevent.keyCode == 39) && (tevent.ctrlKey)) {
		//autocomplete by right arrow
		matchlen=document.getElementById("idl_console_send_textarea").value.length;
		for (var j=0; j<(idlcommandhistory.length); j++) {
			matchthis=(idlcommandhistory[idlcommandhistory.length-1-j].substring(0,matchlen));
			if (matchthis==document.getElementById("idl_console_send_textarea").value) {
				document.getElementById("idl_console_send_textarea").value=idlcommandhistory[idlcommandhistory.length-1-j];
				return;
				}
		}
	}

}

var idlcommandhistory=new Array();
var idlcommandindex=0;

function idlsendfocus(){
	if ((document.getElementById('idl_console_send_textarea').value == 'Enter IDL Commands Here.')||(document.getElementById('idl_console_send_textarea').value == idlcommandhistory[idlcommandhistory.length-1])) {document.getElementById('idl_console_send_textarea').value='';}
	return 1;
}
function execute_idl_command(commandname, sucfunct ){
	var tboundary='--asJKbb173';
	var transferdata = new Array();
	transferdata[0]='--'+tboundary+'\n\rContent-Disposition: form-data; name="IDLSocket_ResponseFunction"\n\r\n\r'+commandname+'\n\r--'+tboundary+'--\n\r';
	transferdata[1]='--'+tboundary+'\n\rContent-Disposition: form-data; name="command"\n\r\n\r'+commandname+'\n\r--'+tboundary+'--\n\r';
	transferdata[2]='--'+tboundary+'\n\rContent-Disposition: form-data; name="terminator"\n\r\n\r'+'die'+'\n\r--'+tboundary+'--\n\r';
	transferdata=transferdata.join("");
	$.ajax({
		url: 'http://127.0.0.1:8083',
		type: 'POST',
		contentType: 'multipart/form-data; boundary='+tboundary,
		processData: false,
		data: transferdata,
		success: function (result) {sucfunct(result)}
	});
}

function populate_plots(){
	//ask idl to transfer active plot windows to jpegs in IDL/imagestack directory
   	execute_idl_command('post_plots', function (result){
	//read files in imagestack directory on server
	$("#idl_analysis_plot_files").load("lookup_files.php?folder=IDL/imagestack",function(data){
		var plotfiles=data.split(',');
		var imax=document.getElementById("idl_analysis_plot_select").length;
		for (var i=0;i<imax;i++){document.getElementById("idl_analysis_plot_select").remove(0);}
		for (var j=0;j<plotfiles.length;j++) {
			var op=document.createElement("option");
			op.text=plotfiles[j];
			document.getElementById("idl_analysis_plot_select").add(op);
		}
		//update the plots
		update_analysis_plots();
		})
	} );
}
function update_analysis_plots(){
        var colors=["red","blue","green","yellow"];
		//step through each plot window on page, updating contents
		var numplots=document.getElementById('analysis_plot_table').getElementsByTagName("tr")[0].getElementsByTagName("td").length-1;		
		var nextselectedplot=0;	
		for (var k=0;k<numplots;k++){		
			while ((document.getElementById('idl_analysis_plot_select').options[nextselectedplot].selected == false) && (nextselectedplot < (document.getElementById('idl_analysis_plot_select').options.length-1))) {nextselectedplot++;}
			if ((nextselectedplot == (document.getElementById('idl_analysis_plot_select').options.length-1)) && (document.getElementById('idl_analysis_plot_select').options[nextselectedplot].selected == false)) {$("#idl_analysis_plotviewer_"+k).iviewer('loadImage','../images/seqicon_null.jpg');continue;}
			
			$("#idl_analysis_plotviewer_"+k).iviewer('loadImage','IDL/imagestack/'+document.getElementById("idl_analysis_plot_select").options[nextselectedplot].text+'?time='+Math.round(+new Date()/1000));

//			document.getElementById("idl_analysis_plot_select").options[nextselectedplot].style.selected=colors[k];
			document.getElementById("idl_analysis_plottitle_"+k).innerHTML="Plot "+k+": "+document.getElementById("idl_analysis_plot_select").options[nextselectedplot].text;
//			document.getElementById("idl_analysis_plottitle_"+k).parentNode.style.background=colors[k];
			nextselectedplot++;
		}
}

function crement_value(amount,id){
	// increases the value in element 'id' by the amount given
	document.getElementById(id).value=parseInt(document.getElementById(id).value)+parseInt(amount);
}
function build_analysis_plots(num_analysis_plots){
	var startind=document.getElementById('analysis_plot_table').getElementsByTagName("tr")[0].getElementsByTagName("td").length-1;
	for(var jj=startind;jj<(startind+num_analysis_plots);jj++){
		toprow=document.getElementById('analysis_plot_table').getElementsByTagName("tr")[0].appendChild(document.createElement('td'));
		toprow.setAttribute("colspan","3");
		toprowspan=toprow.appendChild(document.createElement('span'));
		toprowspan.setAttribute("id","idl_analysis_plottitle_"+jj);
		midrow=document.getElementById('analysis_plot_table').getElementsByTagName("tr")[1]//.appendChild(document.createElement('td'));
		leftarrtd=midrow.appendChild(document.createElement('td'));
		leftarr=leftarrtd.appendChild(document.createElement('img'));
		leftarr.setAttribute("src","../images/LeftFillTriangleIcon.png");
		leftarr.setAttribute("height","15px");
		leftarr.setAttribute("onclick","javascript:crement_value(-1,'idl_analysis_plotindex_"+jj+"');update_analysis_plots();");
		viewerdivtd=midrow.appendChild(document.createElement('td'));
		viewerdiv=viewerdivtd.appendChild(document.createElement('div'));
		viewerdiv.setAttribute("id","idl_analysis_plotviewer_"+jj);
		viewerdiv.setAttribute("class","viewer");
		indin=viewerdivtd.appendChild(document.createElement('input'));
		indin.setAttribute("type","text");
		indin.setAttribute("hidden","hidden");
		indin.setAttribute("id","idl_analysis_plotindex_"+jj);		
		indin.value=jj;		
		rightarrtd=midrow.appendChild(document.createElement('td'));
		rightarr=rightarrtd.appendChild(document.createElement('img'));
		rightarr.setAttribute("src","../images/RightFillTriangleIcon.png");
		rightarr.setAttribute("height","15px");
		rightarr.setAttribute("onclick","javascript:crement_value(1,'idl_analysis_plotindex_"+jj+"');update_analysis_plots();");
		
		botrow=document.getElementById('analysis_plot_table').getElementsByTagName("tr")[2].appendChild(document.createElement('td'));
		botrow.setAttribute("colspan","3");
		//divfordiv=botrow.appendChild(document.createElement('div'));
		//buttdiv=divfordiv.appendChild(document.createElement('div'));
		//buttdiv.setAttribute("id","idl_analysis_plotbuttons_"+jj);
		statspan=botrow.appendChild(document.createElement('span'));
		statspan.setAttribute("id","idl_analysis_plotviewerstatus_"+jj);
		statspan.setAttribute("align","right");
		jumpbutt=botrow.appendChild(document.createElement('img'));
		jumpbutt.setAttribute("src","../images/seqicon_tonewtab.png");
		jumpbutt.setAttribute("height","15px");
		jumpbutt.setAttribute("align","right");
		jumpbutt.setAttribute("onclick","window.open($('#idl_analysis_plotviewer_"+jj+"').iviewer('info', 'src',''));");
		if (0) {srcimg='../IDL/imagestack/imgcap0.jpg'} else {srcimg='../images/seqicon_null.jpg';}
		viewer=$("#idl_analysis_plotviewer_"+jj).iviewer({
                       	src: srcimg, 
	 					onMouseMove: function(ev, coords) {
						  document.getElementById('idl_analysis_plots_coords').innerHTML='('+coords.x.toFixed(1)+', '+coords.y.toFixed(1)+')';
						  },
                  });	
	}
}

function togglehelp(){
	if (document.getElementById("idl_console_help_div").style.display=="block") {document.getElementById("idl_console_help_div").style.display="none";} else {document.getElementById("idl_console_help_div").style.display="block";document.getElementById("idl_console_help_div").innerHTML=idl_console_help_text;}	
}

function destroy_analysis_plots(num_to_destroy){
		toprow=document.getElementById('analysis_plot_table').getElementsByTagName("tr")[0].getElementsByTagName("td");
		midrow=document.getElementById('analysis_plot_table').getElementsByTagName("tr")[1].getElementsByTagName("td");
		botrow=document.getElementById('analysis_plot_table').getElementsByTagName("tr")[2].getElementsByTagName("td");
	if (toprow.length <= 2) {return;}
	for(var j=0;j<num_to_destroy;j++){
		
		element=toprow[toprow.length-1];
		element.parentNode.removeChild(element);
		element=midrow[midrow.length-1];
		element.parentNode.removeChild(element);
		element=midrow[midrow.length-1];
		element.parentNode.removeChild(element);
		element=midrow[midrow.length-1];
		element.parentNode.removeChild(element);
		element=botrow[botrow.length-1];
		element.parentNode.removeChild(element);
	}

}

function find_starters(key, array) {
  var results = [];
  for (var i = 0; i < array.length; i++) {
    if (array[i].indexOf(key) == 0) {
      results.push(array[i]);
    }
  }
  return results;
}

function update_idl_func(elem){
	if (elem.value=='') {return;}
	matchselect=document.getElementById("idl_function_search_select");
	var maxx=matchselect.options.length;
	for (var i = 0; i < maxx; i++) {matchselect.remove(0);}
	var matches=find_starters(elem.value.toUpperCase(),idl_command_list);
	for (var i = 0; i < (matches.length-1); i++) {
		op=document.createElement("option");
		op.text=matches[i];
		matchselect.add(op);
	}
}

function idl_function_defn_lookup(name){
	alert(name);

}

var idl_command_list=new Array();

</script>



<div name='tree_options_div'></div>

<div style="border:1px solid;"><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('idl_analysis'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">Analysis Console<div id="idl_analysis" style="display:none"><table border="1"><tr><td colspan="2" ><div style="display:none" id="idl_console_help_div"></div></td><td colspan="2" ><div style="display:none" id="idl_command_help_div"></div></td></tr><tr valign="bottom"><td><textarea rows="10" cols="70" id="idl_console_return_textarea" onfocus="">IDL responses will appear here.</textarea></td><td><textarea rows="3" cols="70" id="idl_console_send_textarea" onfocus="idlsendfocus();" onkeyup="idl_send_keyup(event);" onkeydown="idl_send_keydown(event);">Enter IDL Commands Here.</textarea><input type="button" value="Execute Now" onclick="execute_idl();"></td><td><select size="10" id="idl_variable_textarea" onchange="IDLvarChange(this);"><option>IDL Variables:</option></select></td><td><input type="text" id="idl_func_search_field" onkeyup="update_idl_func(this);" value="search idl functions" onfocus='this.value="";if (idl_command_list.length ==0) {execute_idl_direct("print, routine_info(/system)",function(result){idl_command_list=result.replaceAll(">IDL>"," ").split(/\s+/g).slice(4);});}' /><br /><select multiple="multiple" size="8" id="idl_function_search_select" onchange="idl_function_defn_lookup(this.options[this.selectedIndex].text);"><option>IDL Functions:</option></select></td></tr></table></div></div>

<!--this needs to be replaced with expandable array demo'd in jquery/testiviewer.html-->
<div style="border:1px solid;"><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('idl_analysis_plots'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">Analysis Plots <img src="images/seqicon_refresh.png" height="15px" onclick="populate_plots();"><div id="idl_analysis_plots" style="display:none">

<!--<table border=1><tr><td>Current Plots:</td><td colspan="3"><span id="idl_analysis_plottitle_0">Window 0:</span></td></tr><tr><td><select size="18" id="idl_analysis_plot_select" onchange=""></select></td><td><img src="images/LeftFillTriangleIcon.png" height="15px" onclick="javascript:crement_value(-1,'idl_analysis_plotindex_0');update_analysis_plots();"  /></td><td><img id="idl_analysis_plot_0" height="300px" width="300px"/><input type="text" hidden="hidden" id="idl_analysis_plotindex_0" value="0" /></td><td><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:crement_value(1,'idl_analysis_plotindex_0');update_analysis_plots();" /></td></tr><tr><td /><td colspan="3" align="right"><img src="images/seqicon_tonewtab.png" height="15px" onclick="window.open(document.getElementById('idl_analysis_plot_0').src);" /></td></tr></table>
-->
<table id="analysis_plot_table" border=1><tr><td><img src="jquery/img/iviewer.zoom_out.gif" onclick="destroy_analysis_plots(1);" height="15px">Current Plots:<img src="jquery/img/iviewer.zoom_in.gif" onclick="build_analysis_plots(1);" height="15px"></td></tr><tr><td><select size="18" id="idl_analysis_plot_select" onchange="update_analysis_plots();" multiple="multiple"></select></td></tr><tr><td ><span id="idl_analysis_plots_coords" >(000.0,000.0)</span></td></tr></table>

</div><div style="display:none" id="idl_analysis_plot_files"></div></div>




<div style="border:1px solid;"><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('xslt_transforms'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">XML Transforms<div id="xslt_transforms" style="display:none"><textarea rows="10" cols="75" id="xslt_transform_textarea" onfocus="javascript:this.value='';this.removeAttribute('onfocus');">Enter XSLT here, or load from file.</textarea>Target:<select id="transform_target_input"><option>Experiment XML</option><option>New Tab</option></select><input type="button" value="Transform Now" onclick="xslt_transform();"><br /></div></div>

<div style="border:1px solid;"><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('display_options'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">Display and Help Options<div id="display_options" style="display:none"><input type="button" value="Dark Room Color" onclick="darken_all(this);"><br />Show Help:<input name='show_help' type='checkbox' onchange='togglehelp();refreshtree();'><br />Font<input type="text" onchange="change_font(this.value);"><div name='tree_build_div' id='tree_build_div' /></div></div>

<div style="border:1px solid;"><form name="parseform" action="Sequence_parse_variables.php" method="post"><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('parser_data_div'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">Parser<input type="text" name="xmlstring" hidden="hidden" /><input type="text" name="parseStartTime" hidden="hidden" />
<input type="submit" value="Parse / Post" onclick="javascript:parse_variables();" id="parse_post_button"><span id="parser_span"></span></form><input type="submit" value="Post as Active Experiment" onclick="javascript:post_xtsm();" id="post_xtsm_button"  /><input type="submit" value="Retrieve Active Experiment" onclick="javascript:retrieve_xtsm();" id="retrieve_xtsm_button"  /><div id="parser_data_div" style="display:none"><input type="submit" value="Disable IDL Socket" onclick="javascript:disable_idl_socket();" id="disable_idl_socket_button" ><input type="submit" value="Launch IDL" onclick="javascript:launch_idl();" id="launch_idl_button" ><input type="submit" value="Test Transfer Speed to IDL" onclick="javascript:test_idltransferspeed();" id="test_idltransferspeed_button" /><div /><input type="checkbox" id="compress_on_post_button" >Compress on Post/Retrieve<input type="checkbox" id="disables_on_post_button" checked="checked">Respect Disabled XML &nbsp; IDL Reconnect Attempts:<input type="text" id="ping_counter"/><span id="IDLspeed_result" value=""></span><?php IF (isset($_REQUEST['parseStartTime'])) print("Roundtrip Parse Time: <div name='parsetimediv' onload=''>".sprintf('%01.3f',microtime(true)-$_REQUEST['parseStartTime']/1000.0)."s (".$_REQUEST['parsetype'].")</div>");?></div></div>

<div style="border:1px solid;"><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('history_div'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">History / Undo &nbsp;<img src="images/seqicon_undo.png" height="15px">&nbsp;<img src="images/seqicon_redo_inactive.png" height="15px"><div id="history_div" style="display:none"><div id="history_subdiv">Undo Levels:<input id="undo_levels" value="4" type="text" /></div></div></div>

<div style="border:1px solid;"><img src="images/RightFillTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('search_div'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')">Search Tools
<div id="search_div" style="display:none"><br />Search XML: <input type="text" value="enter search term" onfocus="javascript:this.value=''" style="font-style:italic" onchange="javascript:search_dom(this);" /><div id="search_results_div"></div></div></div>

<div style="border:1px solid;"><img src="images/DownTriangleIcon.png" height="15px" onclick="javascript:toggleDiv_byElem_flipIcon(document.getElementById('tree_div'),this,'images/DownTriangleIcon.png','images/RightFillTriangleIcon.png')"><b><span style="color:red">Tree Editor:</span></b><img src='images/seqicon_refresh.png' onclick='javascript:refreshtree();' onload="javascript:update_code_fromtree(tree_output_dom(null,null));" height='20px' title="Refresh XML Tree"/><div id="tree_div"></div></div>

</td><td width="2%" bgcolor="#336633" align="center" onclick="alert('advance!');" ><div style="color:white" title="Rewind to previous shot"> &gt;<br />S<br />H<br/>O<br/>T<br/><br/>#<br/>&gt;<br /></div></td></tr></table></body>
</html>
</body>


</html>


