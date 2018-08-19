var wrapper_xhr;
var wrapper_totalxhr = 0;
var wrapper_offline = false;
var wrapper_offline_checker = false;
var wrapper_offline_timer;
var wrapper_offline_check_timer;

//Track which key is down for sub-actions
var wrapper_keydown = false;
$(window).on('keydown', function(event){
	wrapper_keydown = event.which;
});
$(window).on('keyup blur', function(event){
	wrapper_keydown = false;
});

setCookie('ip', wrapper_user_ip, 8/24);
//Keep track of IP in cookies every 4H
setInterval(function(){
	setCookie('ip', wrapper_user_ip, 8/24);
}, 4*60*60*1000);

var wrapper_sendAction = function(param, method, action, cb_success, cb_error, cb_begin, cb_complete){
	if(typeof cb_success==="undefined" || cb_success===null){ cb_success = function(){}; }
	if(typeof cb_error==="undefined" || cb_error===null){ cb_error = function(){}; }
	if(typeof cb_begin==="undefined" || cb_begin===null){ cb_begin = function(){}; }
	if(typeof cb_complete==="undefined" || cb_complete===null){ cb_complete = function(){}; }
	
	wrapper_totalxhr++;
	method = method.toUpperCase();
	action = action.toLowerCase();
	param = ArrayToObject(param);

	//Ajax calls will queue GET request only, that can timeout if the url is the same, but the PHP code still processing in background
	//We add a random md5 code to insure we avoid getting in queue for the same ajax call
	if(method=="GET"){
		var unique_md5 = md5(Math.random());
		action = action+'?'+unique_md5;
	}

	var timeout = 30000; //30s

	wrapper_xhr = $.ajax({
		url: location.protocol+'//'+document.domain+'/'+action,
		type: method,
		data: JSON.stringify(param),
		contentType: 'application/json; charset=UTF-8',
		dataType: 'json',
		timeout: timeout,

		beforeSend: function(jqXHR, settings){
			cb_begin(jqXHR, settings);
			wrapper_offline_checker = true;
			clearTimeout(wrapper_offline_timer);
			wrapper_offline_timer = setTimeout(function(){
				wrapper_offline_checker = false;
			}, 200);
		},

		success: function(data){
			wrapper_offline_hide();
			var msg = '';
			var extra = null;
			var error = false;
			var status = 200;

			if(typeof data.extra != 'undefined'){
				extra = data.extra;
				if(typeof data.extra == 'object' && typeof data.extra.msg == 'string'){
					msg = data.extra.msg;
				}
			}

			if(typeof data.error != 'undefined' && data.error){
				error = true;
				//JSerror.sendError(JSON.stringify(data, null, 4), '/wrapper.js/wrapper_ajax().success()', 0);
				console.log(data);
			}
			
			if(data.show && typeof base_show_error == 'function'){
				if(typeof data.show == 'string'){
					base_show_error(data.show, error);
				} else {
					base_show_error(msg, error);
				}
			}

			if(typeof data.status != 'undefined'){
				status = data.status;
			}

			// Below is the production information with "dataType: 'json'"
			cb_success(msg, error, status, extra);
		},
		
		error: function(xhr_err, ajaxOptions, thrownError){
			if(xhr_err.status==0 && wrapper_offline_checker){
				wrapper_offline_show();
			} else {
				wrapper_offline_hide();
				var msg = wrapper_totalxhr+') '+'xhr.status => '+xhr_err.status
					+'\n'
					+'ajaxOptions => '+ajaxOptions
					+'\n'
					+'thrownError => '+thrownError;

				if(ajaxOptions!='abort'){
					console.log(msg);
				}
			}

			cb_error(xhr_err, ajaxOptions, thrownError);
		},

		complete: function(){
			cb_complete();
		},
	});

	return wrapper_xhr;
};

var wrapper_signout_cb_begin = function(){
	$(document.body).css('cursor', 'progress');
};
var wrapper_signout_cb_complete = function(){
	$(document.body).css('cursor', '');
	wrapper_localstorage.emptyStorage();
	window.location.href = wrapper_href+'?refresh='+(new Date()).getTime();
};

var wrapper_offline_show_timer;
var wrapper_offline_show = function(){
	if(!wrapper_offline){
		wrapper_offline = true;
		//Giving 2 seconds can help to cover if only one call failed
		wrapper_offline_show_timer = setTimeout(function(){
			$("#wrapper_offline").removeClass("display_none");
		}, 2000);
		clearInterval(wrapper_offline_check_timer);
		wrapper_offline_check_timer = setInterval(function(){
			wrapper_sendAction(null, 'post', 'wrapper/info/online');
		}, 1000);
	}
};

var wrapper_offline_hide = function(){
	if(wrapper_offline){
		wrapper_offline = false;
		wrapper_offline_checker = false;
		$("#wrapper_offline").addClass("display_none").removeClass('wrapper_offline_top');
		clearTimeout(wrapper_offline_show_timer);
		clearTimeout(wrapper_offline_timer);
		clearInterval(wrapper_offline_check_timer);
	}
};

$("#wrapper_offline").on('click', function(){
	if($(this).hasClass('wrapper_offline_top')){
		$(this).removeClass('wrapper_offline_top');
	} else {
		$(this).addClass('wrapper_offline_top');
	}
});

var wrapper_time_server = function(){
	wrapper_sendAction(null, 'post', 'wrapper/info/timems', function(msg, error, status, extra){
		if(typeof extra.timems != 'undefined' && typeof Bruno.now != 'undefined'){
			Bruno.now.setServerOffset(extra.timems);
		}
	});
};

wrapper_localstorage.encrypt_ok = true;
wrapper_localstorage.encrypt = function(link, data){
	if(!link || !wrapper_localstorage.encrypt_ok){
		return false;
	}
	var result = false;
	try {
		if(data!=null){
			var store_data = JSON.stringify(data);
			var time = 1000*3600*24*90; //Keep the value for 3 months
			//var time = 1000*3600*24*1; //Keep the value for 1 day
			result = amplify.store(link, JSON.stringify(data), { expires: time });
		} else {
			result = amplify.store(link, null);
		}
	} catch(event) {
		wrapper_localstorage.emptyStorage();
		console.log(event);
	}
	return result;
};

wrapper_localstorage.decrypt = function(link){
	//If we cannot decrypt, the data might be conrupted, so we delete it
	try {
		var data = amplify.store(link);
		return JSON.parse(data); //Best
	} catch(event) {
		amplify.store(link, null);
	}
	return false;
};

//Force to delete all data that are not linked to the workspace to release some space
wrapper_localstorage.emptyStorage = function(){
	wrapper_localstorage.encrypt_ok = false;
	var result = false;
	$.each(amplify.store(), function(storeKey) {
		result = true;
		amplify.store(storeKey, null);
	});
	return result;
};

//Default is Mobile
var wrapper_IScroll_options = {
	click: false, //At true, on mobile it works but but the element doesn't flash
	keyBindings: false, //by default disable iscroll reacting to keyboard keys (arrows, pageup/pagedown etc)
	mouseWheel: true,
	scrollbars: true,
	scrollX: false,
	scrollY: true,
	fadeScrollbars: true,
	interactiveScrollbars: false,
	shrinkScrollbars: 'clip',
	scrollbars: 'custom',
	preventDefaultException: {tagName:/.*/},
	HWCompositing: hasGood3Dsupport,
	disablePointer: true, // important to disable the pointer events that causes the issues
	disableTouch: false, // false if you want the slider to be usable with touch devices
	disableMouse: false, // false if you want the slider to be usable with a mouse (desktop)
};

var wrapper_IScroll_easing_linear = {
	style: 'cubic-bezier(0,0,1,1)',
	fn: function(k) { return k; }
};

//For Desktop support
if(!supportsTouch){
	wrapper_IScroll_options.fadeScrollbars = false;
	wrapper_IScroll_options.click = true; //true avoid ghost click for Desktop (Migth be a problem for hybrid computer like Surface Pro)
	wrapper_IScroll_options.interactiveScrollbars = true;
	wrapper_IScroll_options.shrinkScrollbars = 'scale'; //CPU hunger, not suitable for mobiles
	/*
		NOTE: turn disableMouse to false if you want to be enable to scroll by pointing
		 - true: Enable text selection, but it disables scrolling with the mousedown, and (IMPORTANT!) disables "click" event!
		 - false: Enable scrolling with mousedown, but make text selection more difficult to handle 
				  (can be tweaked with some logic, can turn it true after mousedown 200ms for instance)
	*/
   wrapper_IScroll_options.disableMouse = false;
}

var wrapper_IScroll_options_new = {};
var wrapper_IScroll_cb_creation = {};

var myIScrollList = {};
var wrapper_IScroll_scrolling = false;
function wrapper_IScroll(){
	var overthrow = $('.overthrow');
	overthrow.css('overflow', 'hidden').css('overflow-x', 'hidden').css('overflow-y', 'hidden');
	overthrow.each(function(){
		var Elem = $(this);
		var Child = Elem.children().first();
		if(Child.length>0){
			if(!this.id){
				this.id = "overthrow_"+md5(Math.random());
			}
			if(
				   !myIScrollList[this.id]
				|| Child.hasClass('iscroll_destroyed')
				|| (myIScrollList[this.id] && !Child.hasClass('iscroll_sub_div'))
			){
				Child.removeClass('iscroll_destroyed');
				//Merge with optional options
				var wrapper_IScroll_options_temp = {};
				//We have to loop to recreate the object because of JS memory assignment
				for(key in wrapper_IScroll_options){
					wrapper_IScroll_options_temp[key] = wrapper_IScroll_options[key];
				}
				//We add specific options to the element
				if(typeof wrapper_IScroll_options_new[this.id] == 'object'){
					for(key in wrapper_IScroll_options_new[this.id]){
						wrapper_IScroll_options_temp[key] = wrapper_IScroll_options_new[this.id][key];
					}
				}

				//Enable vertical and horizontal scrolling
				if(!Child.hasClass('iscroll_sub_div')){
					Elem.children().wrapAll('<div class="iscroll_sub_div" />');
					var div_scroll = Elem.children().first();
					if(typeof wrapper_IScroll_options_temp.scrollX != 'undefined' && wrapper_IScroll_options_temp.scrollX){
						div_scroll.addClass('scrollX');
						Elem.width('100%'); //Be sure that it will not stretch up the parent element
					}
					if(typeof wrapper_IScroll_options_temp.scrollY != 'undefined' && wrapper_IScroll_options_temp.scrollY){
						div_scroll.addClass('scrollY');
						Elem.height('100%'); //Be sure that it will not stretch up the parent element
					}
				}
				
				myIScrollList[this.id] = new IScroll(this, wrapper_IScroll_options_temp);
				//We add specific options to the element
				if(typeof wrapper_IScroll_cb_creation[this.id] == 'function'){
					wrapper_IScroll_cb_creation[this.id]();
				}
				myIScrollList[this.id].on('scrollStart', function(){
					wrapper_IScroll_scrolling = true;
				});
				myIScrollList[this.id].on('scrollEnd', function(){
					wrapper_IScroll_scrolling = false;
				});
				Elem.on('scroll', function(){
					$(this).scrollTop(0);
				});
			}
		}
	});

	//Reinitialize or Delete all in a setTimeout to be sure it's loaded after DOM repainting
	setTimeout(wrapper_IScroll_refresh, wrapper_timeout_timer);
};

var wrapper_change_language = function(language){
	wrapper_sendAction({translation_language: language}, 'post', 'wrapper/language', function(){
		window.location.href = wrapper_href+'?refresh'+(new Date()).getTime();
	});
};

var wrapper_IScroll_refresh = function(){
	var Elem = false;
	var Child = null;
	var destroy = false;
	for(var i in myIScrollList){
		Elem = $('#'+i);
		destroy = true;
		if(Elem.length>0){
			if(Elem.hasClass('overthrow')){
				if('refresh' in myIScrollList[i]){
					myIScrollList[i].refresh();
					destroy = false;
					continue;
				}
			} else {
				if('destroy' in myIScrollList[i]){
					Child = Elem.children().first();
					if(Child.length>0 && Child.hasClass('iscroll_sub_div')){
						Child.addClass('iscroll_destroyed');
					}
				}
			}
		}
		if(destroy){
			if('destroy' in myIScrollList[i]){
				myIScrollList[i].destroy();
			}
			myIScrollList[i] = null;
			delete myIScrollList[i];
		}
	}
};

var wrapper_IScroll_switch = function(roll, id){
	if(typeof roll == 'undefined'){ roll = true; }
	if(typeof id == 'undefined'){ id = false; }
	for(var i in myIScrollList){
		if(id && i!=id){
			continue;
		}
		if(roll){
			if('enable' in myIScrollList[i]){
				myIScrollList[i].enable();
			}
		} else {
			if('disable' in myIScrollList[i]){
				myIScrollList[i].initiated = 0;
				myIScrollList[i].disable();
			}
		}
		if('resetPosition' in myIScrollList[i]){
			myIScrollList[i].resetPosition(600);
		}
	}
};

var wrapper_textarea = function(){
	$('body').find("textarea").each(function(){ $(this).textareaRows(); });
};

var wrapper_timeout_timer = 200;
var wrapper_IScroll_timer;
$(window).resize(function(){
	clearTimeout(wrapper_IScroll_timer);
	wrapper_IScroll_timer = setTimeout(function(){
		wrapper_textarea();
		wrapper_IScroll();
	}, wrapper_timeout_timer);
});

//http://stackoverflow.com/questions/23885255/how-to-remove-ignore-hover-css-style-on-touch-devices
//This disable some unwanted behavior the double tapping within the 300ms
//This function is slow to run, so use it in another thread
var wrapper_mobile_hover = function(){
	if (supportsTouch && responsive.test("maxMobileL")) { // remove all :hover stylesheets
		try { // prevent crash on browsers not supporting DOM styleSheets properly
			//We first disbale Fastclick on some elements
			$("[contenteditable], textarea, [type=checkbox], [type=password], [type=radio], [type=text]").addClass('needsclick');
			//Remove hover
			for (var si in document.styleSheets) {
				var styleSheet = document.styleSheets[si];
				if (!styleSheet.rules){
					continue;
				}
				for (var ri = styleSheet.rules.length - 1; ri >= 0; ri--) {
					if (!styleSheet.rules[ri].selectorText){
						continue;
					}
					if (styleSheet.rules[ri].selectorText.match(':hover')) {
						styleSheet.rules[ri].selectorText = styleSheet.rules[ri].selectorText.replace(":hover", ":active");
					}
				}
			}
		} catch (ex) {}
	}
};

//Set indice performance to run some javascript (mainly animation) on powerfull devices
var wrapper_performance = {
	indice: false,
	powerfull: false,
	delay: 250, //Additional delay for slow mobile (max 250ms)
	init: function(){
		if(webperf){
			webperf.postMessage({action: 'checkPerformance'});
		}
	},
	setDelay: function(){
		//Based on a 30 loop test
		if(wrapper_performance.indice){
			wrapper_performance.delay = Math.max(0, Math.min(250, 2 * (wrapper_performance.indice - 150)));
		}
	},
};
//By default we consider as powerfull if the width screen is the one of a Tablet Landscape
wrapper_performance.powerfull = true;
wrapper_performance.delay = 50;

//Keep a record of mouse position
var wrapper_mouse = {
	x: 0,
	y: 0,
	dirX: 1, //-1:up 1:down
	dirY: 1, //-1:left 1:right
	set: function(event){
		if(typeof event != 'object'){
			return false;
		}
		var oldX = wrapper_mouse.x;
		var oldY = wrapper_mouse.y;
		if(typeof event.pageX == 'number' && typeof event.pageY == 'number'){
			wrapper_mouse.x = event.pageX;
			wrapper_mouse.y = event.pageY;
		} else if(typeof event.originalEvent == 'object' && typeof event.originalEvent.touches == 'object' && typeof event.originalEvent.touches[0] == 'object' && typeof event.originalEvent.touches[0].pageX == 'number' && typeof event.originalEvent.touches[0].pageY == 'number'){
			wrapper_mouse.x = event.originalEvent.touches[0].pageX;
			wrapper_mouse.y = event.originalEvent.touches[0].pageY;
		} else if(typeof event.originalEvent.touches == 'object' && typeof event.touches[0] == 'object' && typeof event.touches[0].pageX == 'number' && typeof event.touches[0].pageY == 'number'){
			wrapper_mouse.x = event.touches[0].pageX;
			wrapper_mouse.y = event.touches[0].pageY;
		}
		if(wrapper_mouse.x < oldX){
			wrapper_mouse.dirX = -1;
		} else if(wrapper_mouse.x > oldX){
			wrapper_mouse.dirX = 1;
		} else {
			//Do not record 0 because of crenelage effect
		}
		if(wrapper_mouse.y < oldY){
			wrapper_mouse.dirY = -1;
		} else if(wrapper_mouse.y > oldY){
			wrapper_mouse.dirY = 1;
		} else {
			//Do not record 0 because of crenelage effect
		}
	},
}
$(window).on('mousemove touchmove touchdown touchstart', function(event){
	wrapper_mouse.set(event);
});

JSfiles.finish(function(){
	wrapper_time_server();
	wrapper_IScroll();
	if(!isIOS){
		FastClick.attach(document.body);
	}
	wrapper_load_progress.move(100);
	setTimeout(wrapper_mobile_hover, 100); //Load it in a postponed script to not slow down the page loading
});
