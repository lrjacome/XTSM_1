/*jslint browser: true*/
/*global $, jQuery, DOMParser, XMLSerializer, XSLTProcessor, XPathResult, CodeMirror */

function Hdiode_code_tree(html_div, sources) {

// This object implements a linked-pair of text/code-editor (using codeMirror) 
// and HTML xml tree-editor. The HTML representation of the tree is built using
// an XSL transform which can be dynamically loaded/reloaded.  
// Optionally, an XSD schema can also be loaded (not yet used for anything).
// Both editor and tree are inserted into the HTML DOM at the provided html_div.

    function create_container(html_div) {
    // creates child divisions to house title (topline), codemirror, and tree. 
    // Creates textarea to later be converted into codemirror editor.
        this.html_div_title = html_div.appendChild(document.createElement('div'));
        this.html_div_title.setAttribute("class", "hdiode_xml_tree_titlediv");
        this.html_div_title.appendChild(document.
            createElement('span')).appendChild(document.createTextNode('XML Editor'));
        this.html_div_cm = html_div.appendChild(document.createElement('div'));
        this.html_div_cm.setAttribute("class", "hdiode_xml_tree_cmdiv");
        this.html_div_tree = html_div.appendChild(document.createElement('div'));
        this.html_div_tree.setAttribute("class", "hdiode_xml_tree_treediv");
        this.textarea = this.html_div_cm.appendChild(document.createElement('textarea'));
        this.textarea.value = this.xml_string;
    }
    this.create_container = create_container;

    function xmltoString(elem) {
        var serialized, serializer;
        try {
            serializer = new XMLSerializer();
            serialized = serializer.serializeToString(elem);
        } catch (e) { serialized = elem.xml; }
        return serialized;
    }

    function refresh_tree() {
        //builds HTML tree by applying XSL to XML.
        var xslparser, docparser, xml, xsltProcessor, ex, exs;
        if (!this.xml_string) { return; }
        if (!this.xsl_string) { return; }
        xslparser = new DOMParser();
        // -> would be good to avoid reparsing the xsl everytime.
        if (!(typeof this.xslDoc === 'object')) {
            this.xslDoc = xslparser.parseFromString(this.xsl_string, "text/xml");
        }
        docparser = new DOMParser();
        xml = docparser.parseFromString(this.xml_string, "text/xml");
        xsltProcessor = new XSLTProcessor();
        xsltProcessor.importStylesheet(this.xslDoc);
        ex = xsltProcessor.transformToFragment(xml, document);
        exs = xmltoString(ex);
        this.html_div_tree.appendChild(ex);
        this.html_div_tree.innerHTML = exs;
        // -> need to bind update methods here - 
        // must require xsl routine to tag inputs for binding.
        this.bind_events();
    }
    this.refresh_tree = refresh_tree;

    function toggleProp_update_editor(event) {
        // toggles an element between expanded and collapsed view by rewriting XML, 
        // and re-generating entire tree.  Retrieve XPATH to generating XML element 
        // from first parent division's gen_id property
        var change_prop, elmpath, docparser, xml, target, newval, temp, targets, i;
        change_prop = event.data.args[0].replace(/["']{1}/gi, "");
        docparser = new DOMParser();
        xml = docparser.parseFromString(event.data.container.xml_string, "text/xml");
        target = event.data.container.event_get_elm(event, xml);
        newval = ($(target).attr(change_prop) === "1") ? '0' : '1';
        if ($(target).attr(change_prop) === "1") {
            $(target).attr(change_prop, "0");
        } else {
            (temp = $(target).attr(change_prop, "1"));
        }
        if (event.ctrlKey) {
            //ctrl-toggle applies to children
            elmpath = event.data.container.event_get_elmpath(event);
            targets = xml.evaluate(elmpath + "/*", xml, null,
                XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null);
            for (i = 0; i < targets.snapshotLength; i += 1) {
                $(targets.snapshotItem(i)).attr(change_prop, newval);
            }
        }
        if (event.altKey) {
            //alt-toggle applies to all decendants
            elmpath = event.data.container.event_get_elmpath(event);
            targets = xml.evaluate(elmpath + "//*", xml, null,
                XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null);
            for (i = 0; i < targets.snapshotLength; i += 1) {
                $(targets.snapshotItem(i)).attr(change_prop, newval);
            }
        }
        event.data.container.xml_string = xmltoString(xml);
        event.data.container.update_editor();
        // (tree is automatically refreshed by onchange event of codemirror editor)
    }
    this.toggleProp_update_editor = toggleProp_update_editor;

    function event_get_elmpath(event) {
    // given an event, this returns the xpath to the corresponding xml element that generated the control
        var elmpath;
        elmpath = $(event.target).parents("div:first").get(0).
            getAttribute('gen_id');
        if (elmpath.substr(elmpath.length - 2, 2) === '__') {
            if (event.target.name) {
                elmpath += event.target.name;
            } else {
                elmpath = elmpath.substr(0, elmpath.length - 2);
            }
        }
        elmpath = elmpath.split('divtree__')[1].replace(/__/g, "]/").
            replace(/_/g, "[") + "]";
        return elmpath;
    }
    this.event_get_elmpath = event_get_elmpath;

    function event_get_elm(event, xml) {
        // given an event, this returns the xml object that generated the control;
        // (it safely assumes there is only one such element)
        var elmpath, target;
        elmpath = event.data.container.event_get_elmpath(event);
        target = xml.evaluate(elmpath, xml, null, XPathResult.
            UNORDERED_NODE_ITERATOR_TYPE, null).iterateNext();
        return target;
    }
    this.event_get_elm = event_get_elm;


    function updateElement_update_editor(event) {
        // toggles an element between expanded and collapsed view by 
        // rewriting XML, and re-generating entire tree
        // retrieve XPATH to generating XML element from 
        // first parent division's gen_id property
        //var elmpath, docparser, xml, target;
    	var elmpath, docparser, xml, target;
        elmpath = $(event.target).parents("div:first").get(0).
            getAttribute('gen_id');
        if (elmpath.substr(elmpath.length - 2, 2) === '__') {
            elmpath += event.target.name;
        }
        elmpath = elmpath.split('divtree__')[1].replace(/__/g, "]/").
            replace(/_/g, "[") + "]";
        docparser = new DOMParser();
        xml = docparser.parseFromString(event.data.container.xml_string, "text/xml");
        target = xml.evaluate(elmpath, xml, null, XPathResult.
            UNORDERED_NODE_ITERATOR_TYPE, null).iterateNext();
        if (target.firstChild) { target.firstChild.data = event.target.value; } else { target.appendChild(xml.createTextNode(event.target.value)); }
        event.data.container.xml_string = xmltoString(xml);
        event.data.container.update_editor();
        // (tree is automatically refreshed by onchange event of codemirror editor)
    }
    this.updateElement_update_editor = updateElement_update_editor;

    function autocomplete(event) {
        var docparser, tevent, xml, res;//  res, xml;
        //first exit if the keypress is not ctrl-right- or ctrl-left-arrow
        tevent = event;
        if (!(event.ctrlKey)) { return; }
        if ((event.keyCode !== 39) && (event.keyCode !== 37)) { return; }
        // this event handler autocompletes by looking up values 
        // from xml dom when uparrow is pressed.
        // check if this input was the last pressed 
        // autocomplete, if not, reset index to zero
        if (event.data.container.autocomplete_lastfield !==
                $(event.target).attr('name')) {
            event.data.container.autocomplete_lastfield = $(event.target).attr('name');
            event.data.container.autocomplete_root = $(event.target).attr('value');
            event.data.container.autocomplete_index = 0;
        } else {
            if (event.keyCode === 39) { event.data.container.autocomplete_index += 1; }
            if (event.keyCode === 37) { event.data.container.autocomplete_index -= 1; }
        }
        docparser = new DOMParser();
        xml = docparser.parseFromString(event.data.container.xml_string, "text/xml");
        res = xml.evaluate(event.data.args[0].split("'")[1].replace('$', '"' + event.data.container.autocomplete_root + '"'), xml, null,
            XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null);
        // take modulus of index to reference hits, insert them into field
        if (res.snapshotLength > 1) {
            $(event.target).attr('value', $(res.snapshotItem(
                event.data.container.autocomplete_index - Math.floor(
                    event.data.container.autocomplete_index / res.snapshotLength
                ) * res.snapshotLength
            )).text());
        } else { $(event.target).attr('value', $(res.snapshotItem(0)).text()); }
    }
    this.autocomplete = autocomplete;

    function modifyElement_update_editor(event) {
		var docparser, xml, elm;
        docparser = new DOMParser();
        xml = docparser.parseFromString(event.data.container.xml_string, "text/xml");
        elm = event.data.container.event_get_elm(event, xml);
        if (event.data.args[0] === "'delete'") {
            elm.parentElement.removeChild(elm);
        } else if (event.data.args[0] === "'move'") {
        } else if (event.data.args[0] === "'clone'") {
            //THIS DOESN'T WORK YET
			var alert;
            alert('cloning');
            elm.parentElement.insertBefore(elm, elm);
        }
        event.data.container.xml_string = xmltoString(xml);
        event.data.container.update_editor();
    }
    this.modifyElement_update_editor = modifyElement_update_editor;

    function bind_events() {
        // this searches the html tree looking for xtsm_viewer_event attributes 
        // their value should be of the form 
        // eventType:handlerFunctionName(arg1,arg2...)
        // it then attaches the handler (should be a method of this object) 
        // to the HTML event
        var bind_targets, next_target, eventtype, handler_name, handler_args,
            that, thisevent, allevents, j;
        bind_targets = document.evaluate('//*[@xtsm_viewer_event]', document, null,
            XPathResult.UNORDERED_NODE_ITERATOR_TYPE, null);
        next_target = bind_targets.iterateNext();
        while (next_target) {
            // this parses the event type and handler function 
            // from the xtsm_viewer_event attribute
            // multiple events should be split by semicolons
            //var thisevent=next_target.getAttribute('xtsm_viewer_event').split(';')[0];
            allevents = next_target.getAttribute('xtsm_viewer_event').split(';');
            for (j = 0; j < allevents.length - 1; j += 1) {
                thisevent = allevents[j];
                eventtype = thisevent.split(':')[0];
                if (eventtype.substr(0, 2) === 'on') {
                    eventtype = eventtype.substr(2);
                }
                handler_name = thisevent.
                    split(':')[1].split('(')[0];
                handler_args = thisevent.
                    split(':')[1];
                handler_args = handler_args.substring(handler_args.indexOf("(") + 1);
                handler_args = handler_args.substring(0, handler_args.lastIndexOf(")")).match(/(?!;| |$)([^";]*"[^"]*")*([^";]*[^ ";])?/g); //split(',');
//                if (handler_name === "autocomplete") {alert(handler_args ); }
                if (typeof this[handler_name] === 'function') {
                    //this[handler_name].apply(this, handler_args);
                    //this line does the event-binding
                    that = this;
                    $(next_target).on(eventtype, null,
                        { container: this, args: handler_args }, this[handler_name]);
                }
            }
            next_target = bind_targets.iterateNext();
        }
    }
    this.bind_events = bind_events;

    function update_editor() { this.editor.setValue(this.xml_string); }
    this.update_editor = update_editor;

    function load_file(filename, target) {
        var thatt = this;
        $.get(filename, function (source) {
            if (target === 'xml_string') {
                thatt.xml_string = source;
                thatt.update_editor();
                return source;
            }
            if (target === 'xsl_string') {
                thatt.xsl_string = source;
                return source;
            }
            if (filename.split(/\.xml|\.xsd|\.xtsm/).length > 1) {
                thatt.xml_string = source;
                thatt.update_editor();
                return source;
            }
            if (filename.split(/\.xs|\.xsl/).length > 1) {
                thatt.xsl_string = source;
                return source;
            }
        }, 'text');
    }
    this.load_file = load_file;

    this.xml_string = sources.xml_string;
    this.xsl_string = sources.xsl_string;
    this.xsd_string = sources.xsd_string;
    if (html_div) { this.create_container(html_div); }
    var that = this;
    if (this.textarea) {
        this.editor = CodeMirror.fromTextArea(this.textarea,
            { mode: "text/html", gutter: "True", lineNumbers: "True",
                gutters: ["note-gutter", "CodeMirror-linenumbers"],
                linewrapping: "True", autoCloseTags: true });
        this.editor.on("change", function () {
            that.xml_string = that.editor.getValue();
            that.refresh_tree();
            return;
        });
    }
    this.editor.setGutterMarker(0, "note-gutter", document.createTextNode("start>"));

    return this;


}

function main() {
	"use strict";

	var arg, file_counter, parser_counter;

	//Creates new hdiode tree
	arg = new Hdiode_code_tree(document.getElementById('Create_Tree'), {xml_string: '<none>', xsl_string: '<none>', xsd_string: 'nausea'});
	arg.load_file("sofartlight.xslt", 'xsl_string');
	arg.load_file("../MetaViewer/sequences/default.xtsm", 'xml_string');

	file_counter = 0;
	function file_operations() {
	//Opens file operations, including load/save files options.
		if (file_counter === 0) {
			document.getElementById('file_menu').src = "/images/DownTriangleIcon.png";
			document.getElementById('file_operations').style.display = "block";
			file_counter = 1;
		} else if (file_counter === 1) {
			document.getElementById('file_menu').src = "/images/RightFillTriangleIcon.png";
			document.getElementById('file_operations').style.display = "none";
			file_counter = 0;
		}
	}

	parser_counter = 0;
	function parser_operations() {
	//Opens parser operations, including parse file, post/retreive experiment data, etc.
		if (parser_counter === 0) {
			document.getElementById('parser_menu').src = "/images/DownTriangleIcon.png";
			document.getElementById('parser_operations').style.display = "block";
			parser_counter = 1;
		} else if (parser_counter === 1) {
			document.getElementById('parser_menu').src = "/images/RightFillTriangleIcon.png";
			document.getElementById('parser_operations').style.display = "none";
			parser_counter = 0;
		}
	}

	function default_save_name() {
		var x, year, month, day, hour, minute, second, datetime, save_name;

		x = new Date();
		year = x.getFullYear();
		month = x.getMonth() + 1;
		day = x.getDate() + 1;
		hour = x.getHours();
		minute = x.getMinutes();
		second = x.getSeconds();
		datetime = month + "-" + day + "-" + year + "/" + hour + "h_" + minute + "m_" + second + "s";
		save_name = "c:/wamp/www/MetaViewer/sequences/" + datetime + ".xtsm";

		return save_name;
	}

	function load_new_file() {
		var filename;
		filename = document.getElementById("load_file").value.split('c:/wamp/www').pop();
		arg.load_file(filename, 'xml_string');
	}

	function save_file() {
		var save_name, test_name, code;
		save_name = document.getElementById('save_file').value;
		code = arg.xml_string;
		test_name = save_name.substring(2);
		if (test_name.indexOf(':') !== -1) {alert('File name cannot contain ":", besides "c:/..."'); } else {
			$.post("save_file.php", {filename: save_name, filedata: code}, function () {alert('File Saved'); });
		}
	}

	function refresh() {
	//Refreshes code tree, resets load file and save file text boxes.
		arg.refresh_tree();
		document.getElementById('load_file').value = "";
		document.getElementById('save_file').value = default_save_name();
		counter = 1;
		file_operations();
	}

	//Controls File Operations, Load, Save, and Refresh Buttons
	document.getElementById("file_menu").onclick = function () {file_operations() };
	document.getElementById("parser_menu").onclick = function () {parser_operations() };
	document.getElementById("load").onclick = function () {load_new_file(); };
	document.getElementById("save_file").defaultValue = default_save_name();
	document.getElementById("save").onclick = function () {save_file(); };
	document.getElementById("refresh").onclick = function () {refresh(); };
}
