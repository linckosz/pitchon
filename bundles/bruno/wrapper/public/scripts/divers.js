var wrapper_browser = function(ua) {
	if(typeof ua==="undefined"){
		return false;
	}
	return navigator.userAgent.toUpperCase().indexOf(ua.toUpperCase())>=0;
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

var supportsTouch = 'ontouchstart' in window || navigator.msMaxTouchPoints;

$.fn.hasScrollBar = function() {
	return this.get(0).scrollHeight > this.height();
};

//http://artsy.github.io/blog/2012/10/18/so-you-want-to-do-a-css3-3d-transform/
var hasGood3Dsupport =
	   'WebkitPerspective' in document.body.style
	|| 'MozPerspective' in document.body.style
	|| 'msPerspective' in document.body.style
	|| 'OPerspective' in document.body.style
	|| 'perspective' in document.body.style
;

var isIOS = false;
if(navigator.userAgent.match(/iPhone|iPad|iPod/i)){
	isIOS = true;
	hasGood3Dsupport = false;
}

//Safari is
var isSafariIOS = false;
if(navigator.userAgent.match(/iPhone|iPad|iPod/i) && navigator.userAgent.match(/Safari/i)){
	isSafariIOS = true;
}

var wrapper_is_mobile = function(){
	return /webOS|iPhone|iPad|BlackBerry|Windows Phone|Opera Mini|IEMobile|Mobile/i.test(navigator.userAgent);
}

/*
	This commands help to track time spent in some functions
	wrapper_time_checkpoint(false, true);
	wrapper_time_checkpoint('01');
	wrapper_time_checkpoint('02');
*/
var	wrapper_time_checkpoint_time = false;
var wrapper_time_checkpoint = function(msg, reset, show){
	if(typeof msg != 'undefined' && msg){
		msg = '['+msg+'] ';
	} else {
		msg = '';
	}
	if(typeof reset == 'boolean' && reset){ 
		wrapper_time_checkpoint_time = false;
	} else {
		reset = false;
	}
	if(typeof show == 'undefined'){
		show = true;
		if(reset){
			show = false;
		}
	}
	var now = Math.round(performance.now()); //round ms
	var delay = false;
	if(wrapper_time_checkpoint_time){
		if(show){
			var delay = now - wrapper_time_checkpoint_time;
			console.log(msg+'time: '+delay);
		}
	} else {
		if(show){
			console.log(msg+'start')
		}
	}
	wrapper_time_checkpoint_time = now;
	return delay;
}

//Adjust the high of a textarea according to the line
//Limit to 5 by default
$.fn.textareaRows = function(){
	var maxrows = parseInt(this.attr('maxrows'), 10);
	if(typeof maxrows == 'undefined' || !maxrows || maxrows<=0){
		maxrows = 5;
	}
	var tolerance = 2; //Give 2px tolerance for chinese character display issue (android as a small offset)
	var scrollbar = this.get(0).scrollHeight - this.outerHeight(); //Negative means no scrollbar
	var rows = parseInt(this.attr('rows'), 10);
	while(rows>maxrows && rows>1){
		rows--;
		this.attr('rows', rows);
		scrollbar = this.get(0).scrollHeight - this.outerHeight();
	}
	if(scrollbar > tolerance){
		while(scrollbar > tolerance && rows<maxrows){
			rows++;
			this.attr('rows', rows);
			scrollbar = this.get(0).scrollHeight - this.outerHeight();
		}
	} else {
		while(scrollbar <= tolerance && rows>1){
			rows--;
			this.attr('rows', rows);
			scrollbar = this.get(0).scrollHeight - this.outerHeight();
		}
		if(scrollbar > tolerance){
			rows++;
			this.attr('rows', rows);
		}
	}
	return this;
};

var setCookie = function(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
};
