var wrapper_compareObjects = function(o1, o2) {
	var k = '';
	for(k in o1) if(o1[k] != o2[k]) return false;
	for(k in o2) if(o1[k] != o2[k]) return false;
	return true;
};

var wrapper_itemExists = function(haystack, needle) {
	for(var i=0; i<haystack.length; i++) if(wrapper_compareObjects(haystack[i], needle)) return true;
	return false;
};

var wrapper_to_html = function(text){
	//text = php_htmlentities(text, true); //Need to enable double encoding
	if(typeof text == 'undefined'){
		text = '';
	}
	text = parseHTML(text);
	text = php_nl2br(text);
	return text;
};

var html_to_wrapper = function(text){
	if(typeof text == 'undefined'){
		text = '';
	}
	text = php_br2nl(text);
	text = restoreHTML(text);
	return text;
};

var wrapper_flat_text = function(text){
	if(typeof text == 'undefined'){
		text = '';
	}
	text = text.replace(/\r\n|\n\r|\r|\n/g, '&nbsp;');
	return text;
};

var wrapper_to_url = function(text){
	// based on the rules here: http://www.mtu.edu/umc/services/web/cms/characters-avoid/
	text = text.replace(/[#%&{}\/\\<>*? $!'":@+`|=_]/g,'-');
	return text;
};

var wrapper_timeoffset = function(){
	//Important: Note that getTimezoneOffset() is return posit value number (-8H for China instead of 8H)
	//Reason is specs: http://stackoverflow.com/questions/21102435/why-does-javascript-date-gettimezoneoffset-consider-0500-as-a-positive-off
	var timeoffset = (new Date()).getTimezoneOffset();
	timeoffset = Math.floor(timeoffset/60);
	if(timeoffset<0){
		timeoffset = 24 + timeoffset; //24H - offset
	}
	if(timeoffset>=24){
		timeoffset = 0;
	}
	return timeoffset;
};

//Help to detach all Nodes
jQuery.prototype.recursiveEmpty = function(delay){
	if(typeof delay == 'undefined'){ delay = 1000; } //By default delay by 1s
	if(delay>0){
		var Children = this.contents();
		setTimeout(function(Children){
			if(Children){
				Children
					.contents().each(function() {
						$(this)
							.recursiveEmpty(0)
							.removeData()
							.remove();
					});
			}
		}, delay, Children);
	} else {
		this
			.contents().each(function() {
				$(this)
					.recursiveEmpty(0)
					.removeData()
					.remove();
			});
	}

	this
		.off()
		.removeAttr()
		.empty();

	return this;
};

//Help to detach all Nodes
jQuery.prototype.recursiveRemove = function(delay){
	if(typeof delay == 'undefined'){ delay = 1000; } //By default delay by 1s
	this
		.recursiveEmpty(delay)
		.removeData()
		.remove();
	return this;
};

//Help to bloc all Nodes event
jQuery.prototype.recursiveOff = function(delay){
	if(typeof delay == 'undefined'){ delay = 0; }
	if(delay>0){
		var Children = this.contents();
		setTimeout(function(Children){
			if(Children){
				Children
					.contents().each(function() {
						$(this)
							.recursiveOff(0)
					});
			}
		}, delay, Children);
	} else {
		this
			.contents().each(function() {
				$(this)
					.recursiveOff(0)
			});
	}

	this
		.off();

	return this;
};

var encode_utf8 = function(s) {
	return unescape(encodeURIComponent(s));
};

var decode_utf8 = function(s) {
	return decodeURIComponent(escape(s));
};

var parseHTML = function(text) {
	text = ''+text;
	return text
		.replaceAll('<', '&lt;')
		.replaceAll('>', '&gt;')
		.replaceAll('"', '&quot;')
		.replaceAll("'", '&#39;')
		.replaceAll('  ', '&nbsp;&nbsp;')
	;
};

var restoreHTML = function(text) {
	text = ''+text;
	return text
		.replaceAll('&lt;', '<')
		.replaceAll('&gt;', '>')
		.replaceAll('&quot;', '"')
		.replaceAll('&#39;', "'")
		.replaceAll('&nbsp;&nbsp;', '  ')
	;
};

String.prototype.ucfirst = function() {
	if(this.length > 0){
		return this.charAt(0).toUpperCase() + this.slice(1);
	} else {
		return this;
	}
};

String.prototype.replaceAll = function(find, replace) {
	find = find.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
	return this.replace(new RegExp(find, 'gi'), replace);
};


var webworker_operation = {

	launch_operation: function(event){
		var object = event.data;
		if(object && object.action){
			var data = null;
			if(typeof object.data != "undefined"){
				data = object.data;
			}
			if(typeof webworker_operation[object.action] == 'function'){
				webworker_operation[object.action](data);
			}
		}
	},

	//webworker.postMessage({action: 'test', data: 123,});
	test: function(obj_data){
		console.log(obj_data);
	},

	indicePerformance: function(indice){
		wrapper_performance.indice = indice;
		if(indice < 300){ //Loop 30 => 300 ms max
			wrapper_performance.powerfull = true;
		} else {
			wrapper_performance.powerfull = false;
		}
		wrapper_performance.setDelay();
	},

};

//Check connection type
//https://developer.mozilla.org/en-US/docs/Web/API/Network_Information_API
var wrapper_connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
var wrapper_connection_type = false;
if(typeof wrapper_connection != "undefined" && typeof wrapper_connection.type != "undefined"){
	wrapper_connection_type = wrapper_connection.type;
	wrapper_connection.addEventListener('typechange', function(){
		wrapper_connection_type = wrapper_connection.type;
	});
}


//https://www.w3schools.com/js/js_cookies.asp
var getCookie = function(cname) {
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
};

var setCookie = function(cname, cvalue, exdays) {
	if(typeof exdays == 'undefined'){ exdays = 30; } //1 month by default
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
};

var ArrayToObject = function(arr){
	var result = false;
	if(typeof arr == 'object'){
		result = {};
		for(var i in arr){
			result[i] = ArrayToObject(arr[i]);
		}
	} else {
		result = arr;
	}
	return result;
};

var wrapper_integer_map = function(text, decode){
	if(typeof decode == 'undefined'){ decode = false; }
	text = ""+text; //convert to text

	var result = "";	
	var map_encode = [
		['m', '5', 'g'], //0
		['9', 'r', 'w'], //1
		['q', 'h', 'c'], //2
		['6', 'j', 'a'], //3
		['z', 'v', '3'], //4
		['b', 'n', '8'], //5
		['y', '7', 'e'], //6
		['t', 'x', 'd'], //7
		['f', '2', '4'], //8
		['s', 'u', 'p'], //9
	];

	if(decode){ //decode
		var map_decode = {};
		for(var i in map_encode){
			for (var j = 0; j <= 2; j++) {
				map_decode[map_encode[i][j]] = i;
			}
		}
		for (var i = 0; i < text.length; i++) {
			if(typeof map_decode[text[i]] != 'undefined'){
				result += ""+map_decode[text[i]];
			}
		}
		result = parseInt(result, 10);
	} else { //encode
		var mod = parseInt(text, 10)%3;
		for (var i = 0; i < text.length; i++) {
			result += ""+map_encode[parseInt(text[i], 10)][mod];
		}
	}

	return result;
};
