var submenu_obj = {};
var submenu_mouseenter = false;
var submenu_show = {};

function Submenu(menu, next, param, animate) {
	this.obj = submenu_list[menu];
	this.menu = menu;
	this.layer = 1;
	if (typeof next == 'number') {
		if (next === 0) {
			this.layer = submenu_Getfull();
		} else {
			this.layer = next;
		}
	} else if (typeof next == 'boolean' && next === true) {
		this.layer = submenu_Getposition(menu);
	}
	if (typeof param === 'undefined') {
		this.param = null;
	} else {
		this.param = param;
	}
	if (typeof animate === 'undefined') { animate = true; }

	this.id = this.layer + "_submenu_wrapper_" + menu;

	this.zIndex = 2000 + this.layer;
	this.display = true;
	this.attribute = null;
	this.focuson = true;

	function Constructor(subm) {
		//First we have to empty the element if it exists
		submenu_Clean(subm.layer, false);

		var submenu_wrapper = $('#-submenu_wrapper').clone();
		if($('#'+subm.id).length>0){
			$('#'+subm.id).recursiveRemove();
		}
		submenu_wrapper.prop("id", subm.id);

		submenu_wrapper.css('z-index', subm.zIndex);
		//Do not not add "overthrow" in twig template, if not the scrollbar will not work
		submenu_wrapper.find("[find=submenu_wrapper_content]").addClass('overthrow').prop("id", "overthrow_"+subm.id);

		submenu_wrapper.appendTo('#app_application_submenu_block');
		submenu_wrapper.on('mouseenter', function(){
			submenu_mouseenter['submenu'] = true;
		});
		submenu_wrapper.on('mouseleave', function(){
			submenu_mouseenter['submenu'] = false;
		});

		//This is because we can only place 3 menus on Desktop mode, so after 3 layers we switch to full width mode
		if (subm.layer > 3) { submenu_wrapper.addClass('submenu_wrapper_important'); }

		//Launch Pre action
		for (var att in subm.obj) {
			subm.attribute = subm.obj[att];
			if ("style" in subm.attribute && subm.attribute.style == "preAction" && "action" in subm.attribute) {
				if (typeof subm.attribute.action == "function") {
					subm.attribute.action(submenu_wrapper, subm);
				}
			}
		}
		//Build the page
		for (var att in subm.obj) {
			subm.attribute = subm.obj[att];
			if ("style" in subm.attribute && "title" in subm.attribute) {
				if (typeof subm.style[subm.attribute.style] == "function") {
					subm.style[subm.attribute.style](submenu_wrapper, subm);
				}
			}
		}

		for (var att in subm.obj) {
			subm.attribute = subm.obj[att];
			if ("style" in subm.attribute && "items" in subm.attribute) {
				if (typeof subm.style[subm.attribute.style] == "function") {
					subm.style[subm.attribute.style](submenu_wrapper, subm);
				}
			}
		}

		//Launch Post action
		for (var att in subm.obj) {
			subm.attribute = subm.obj[att];
			if ("style" in subm.attribute && subm.attribute.style == "postAction" && "action" in subm.attribute) {
				if (typeof subm.attribute.action == "function") {
					subm.attribute.action(submenu_wrapper, subm);
				}
			}
		}

		if (subm.display) {
			subm.Show(animate); //animate at false (default is true) force to open the submenu without animation to be able to access to DOM directly
		} else {
			subm.Hide();
		}
		wrapper_IScroll_refresh();
		wrapper_IScroll();
		//Free memory
		delete submenu_wrapper;

		return subm;
	}
	Constructor(this);
}

Submenu.prototype.style = {};

Submenu.prototype.FocusForm = function() {
	var that = this;
	if (!supportsTouch && this.focuson) {
		setTimeout(function() {
			submenu_wrapper = that.Wrapper();
			var ElemFocus = submenu_wrapper.find("input:enabled:visible:first:not(.no_focus)");
			if (ElemFocus.length >= 1) {
				ElemFocus.focus();
				return true;
			}
			ElemFocus = submenu_wrapper.find("textarea:enabled:visible:first:not(.no_focus)");
			if (ElemFocus.length >= 1) {
				ElemFocus.focus();
				return true;
			}
			ElemFocus = submenu_wrapper.find("select:enabled:visible:first:not(.no_focus)");
			if (ElemFocus.length >= 1) {
				ElemFocus.focus();
				return true;
			}
		}, 1);
	}
};

Submenu.prototype.showSubmenu = function(time, delay, animate) {
	if(typeof time == 'undefined'){ time = 160; }
	if(typeof delay == 'undefined'){ delay = 60; }
	if(typeof animate == 'undefined'){ animate = false; }
	//In case of animation, give enough time for the picture to be draw into the memory
	if(animate){
		delay = delay + wrapper_performance.delay;
	}
	var submenu_wrapper = this.Wrapper();
	var that = this;
	$('#' + that.id).css('width', '33.33%');
	$('#' + that.id).css('visibility', 'hidden').hide();
	if(!animate){
		$('#' + that.id).css('visibility', 'visible').show(0);
		app_application_submenu_position();
		submenu_resize_content();
		submenu_wrapper_width()
		that.FocusForm();
		$(window).resize();
		that.Wrapper().find("[find=submenu_wrapper_content]").focus();
		setTimeout(function(sub_that){
			sub_that.Wrapper().find("[find=submenu_wrapper_content]").focus();
			app_application_bruno.prepare(["submenu_show", "submenu_show_"+sub_that.id], true);
		}, 1, that);
	} else if (responsive.test("minDesktop")) {
		if (that.layer <= 3) { submenu_wrapper.css('z-index', 2000); } //This insure for the 1/3 version to go below the previous one
		submenu_wrapper.velocity(
			"transition.slideLeftBigIn", {
				mobileHA: hasGood3Dsupport,
				duration: time,
				delay: delay,
				easing: [.38, .1, .13, .9],
				begin: function() {
					$('#' + that.id).css('visibility', 'visible').show(0);
					that.Wrapper().find("[find=submenu_wrapper_content]").focus();
				},
				complete: function() {
					//The line below avoid a bug in Chrome that could make the scroll unavailable in some areas
					//submenu_wrapper.hide().show(0);
					submenu_wrapper.css('z-index', that.zIndex);
					app_application_submenu_position();
					submenu_resize_content();
					submenu_wrapper_width();
					that.FocusForm();
					$(window).resize();
					setTimeout(function(sub_that){
						sub_that.Wrapper().find("[find=submenu_wrapper_content]").focus();
						app_application_bruno.prepare(["submenu_show", "submenu_show_"+sub_that.id], true);
					}, 1, that);
				}
			}
		);
	} else {
		//var animation = "bruno.slideRightBigIn";
		var animation = "bruno.slideLeftBigIn";
		setTimeout(function(){
			submenu_wrapper.velocity(
				animation, {
					mobileHA: hasGood3Dsupport,
					duration: Math.floor(1.5 * time),
					delay: delay,
					easing: [.38, .1, .13, .9],
					begin: function() {
						$('#' + that.id).css('visibility', 'visible').show(0);
						that.Wrapper().find("[find=submenu_wrapper_content]").focus();
					},
					complete: function() {
						app_application_submenu_position();
						submenu_resize_content();
						submenu_wrapper_width()
						that.FocusForm();
						$(window).resize();
						setTimeout(function(sub_that){
							sub_that.Wrapper().find("[find=submenu_wrapper_content]").focus();
							app_application_bruno.prepare(["submenu_show", "submenu_show_"+sub_that.id], true);
						}, 1, that);
					}
				}
			);
		}, 10);
	}
	delete submenu_wrapper;
}

Submenu.prototype.Show = function(animate) {
	if(typeof animate == 'undefined'){ animate = false; }
	if(!wrapper_performance.powerfull && responsive.test("maxTablet")){ animate = false; } //toto => the animation seems slow on mobile, disallow velocity
	var that = this;
	var time = 200;
	var delay = 60;
	if (typeof submenu_show[this.id] !== 'boolean' || !submenu_show[this.id]) {
		submenu_show[this.id] = true;
		var state_layer = that.layer;
		app_generic_state.change(
			{
				submenu: state_layer,
			},
			that.param,
			1
		);
		this.showSubmenu(time, delay, animate);
	}
};

Submenu.prototype.hideSubmenu = function(time, delay, animate) {
	if(typeof time == 'undefined'){ time = 160; }
	if(typeof delay == 'undefined'){ delay = 60; }
	if(typeof animate == 'undefined'){ animate = false; }
	var that = this;
	var submenu_wrapper = this.Wrapper();
	if(!animate){
		that.Remove();
	} else if (responsive.test("minDesktop")) {
		if (that.layer <= 3) { submenu_wrapper.css('z-index', 2000); } //This insure for the 1/3 version to go below the previous one
		submenu_wrapper.velocity(
			"transition.slideLeftBigOut", {
				mobileHA: hasGood3Dsupport,
				duration: time,
				delay: delay,
				easing: [.38, .1, .13, .9],
				complete: function() {
					app_application_bruno.prepare(["submenu_hide", "submenu_hide_"+that.id], true, false, true);
					that.Remove();
					app_application_bruno.prepare(["submenu_hide", "submenu_hide_"+that.id], true);
				}
			}
		);
	} else {
		//var animation = "bruno.slideRightBigOut";
		var animation = "bruno.slideLeftBigOut";
		submenu_wrapper.velocity(
			animation, {
				mobileHA: hasGood3Dsupport,
				duration: Math.floor(1.5 * time),
				delay: delay,
				easing: [.38, .1, .13, .9],
				complete: function() {
					app_application_bruno.prepare(["submenu_hide", "submenu_hide_"+that.id], true, false, true);
					that.Remove();
					app_application_bruno.prepare(["submenu_hide", "submenu_hide_"+that.id], true);
				}
			}
		);
	}
	//Free memory
	delete submenu_wrapper;
}

Submenu.prototype.Hide = function(animate) {
	if(typeof animate == 'undefined'){ animate = false; }
	if(!wrapper_performance.powerfull && responsive.test("maxTablet")){ animate = false; } //toto => the animation seems slow on mobile, disallow velocity
	var that = this;
	var time = 160;
	var delay = 60;
	submenu_show[this.id] = false;
	//Reset menu selection if(menu in submenu_list){
	if ((that.layer - 1) in submenu_obj) {
		submenu_obj[that.layer - 1].Wrapper().find('.submenu_deco_next').removeClass('submenu_deco_next');
	}
	var state_layer = that.layer - 1;
	if(state_layer<=0){
		submenu_content_unblur();
	}
	app_generic_state.change(
		{
			submenu: state_layer,
		},
		that.param,
		-1
	);
	this.hideSubmenu(time, delay, animate);
};

// http://stackoverflow.com/questions/19469881/javascript-remove-all-event-listeners-of-specific-type
Submenu.prototype.Remove = function() {
	$('#' + this.id).hide().recursiveRemove();
	//Free memory
	submenu_obj[this.layer] = null;
	delete submenu_obj[this.layer];
	app_application_submenu_position();
	submenu_resize_content();
	submenu_content_block_hide();
};

Submenu.prototype.Wrapper = function() {
	return $('#' + this.id);
}

function submenu_resize_content() {
	var submenu_wrapper;
	var top;
	var content;
	var bottom;
	var iscroll = false;
	for(var index in submenu_obj){
		submenu_wrapper = $("#"+submenu_obj[index]['id']);
		total = parseInt(submenu_wrapper.height(), 10);
		top = parseInt(submenu_wrapper.find("[find=submenu_wrapper_top]").height(), 10);
		bottom = parseInt(submenu_wrapper.find("[find=submenu_wrapper_bottom]").height(), 10);
		content = total-top-bottom;
		if(content){
			submenu_wrapper.find("[find=submenu_wrapper_content]").height(content);
			iscroll = true;
		}
	}
	if(iscroll){
		wrapper_IScroll_refresh();
		wrapper_IScroll();
	}
	return true;
}

var submenu_resize_content_timer;
$(window).resize(function(){
	clearTimeout(submenu_resize_content_timer);
	submenu_resize_content_timer = setTimeout(submenu_resize_content, wrapper_timeout_timer*2);
});

function submenu_getById(id) {
	for(var index in submenu_obj){
		if(submenu_obj[index]['id']==id && $("#"+id).length>0){
			return submenu_obj[index];
		}
	}
	return false;
}

function submenu_Hideall() {
	for (var index in submenu_obj) {
		submenu_Clean(true, true);
	}
}

//Return the next layer to display full screen
function submenu_Getfull() {
	var next = submenu_Getnext();
	if (next < 4) {
		next = 4;
	}
	return next;
}

// "1" means that there is no submenu displayed
function submenu_Getnext() {
	submenu_layer = 0;
	for (var index in submenu_obj) {
		if (submenu_obj[index].layer > submenu_layer) {
			submenu_layer = submenu_obj[index].layer;
		}
	}
	submenu_layer++;
	return submenu_layer;
}

function submenu_get(menu) {
	var submenu = false;
	for (var index in submenu_obj) {
		if (submenu_obj[index].menu === menu) {
			submenu = submenu_obj[index];
		}
	}
	return submenu;
}

function submenu_Getposition(menu) {
	submenu_position = submenu_Getnext();
	for (var index in submenu_obj) {
		if (submenu_obj[index].menu === menu) {
			submenu_position = submenu_obj[index].layer;
		}
	}
	return submenu_position;
}

function submenu_Clean(layer, animate) {
	if (typeof layer !== 'number' || layer < 1) {
		layer = 1;
	}
	if(typeof animate === 'undefined'){ animate = false; }
	for (var index in submenu_obj) {
		if (submenu_obj[index].layer >= layer) {
			submenu_obj[index].Hide(animate);
		}
	}
}

function submenu_Build(menu, next, hide, param, animate) {
	setTimeout(function(menu, next, hide, param, animate){
		submenu_Build_return(menu, next, hide, param, animate);
	}, 20, menu, next, hide, param, animate);
}

function submenu_Build_return(menu, next, hide, param, animate) {
	if (typeof next === 'undefined') { next = 1; }
	if (typeof hide === 'undefined') { hide = true; }
	if (typeof param === 'undefined') { param = null; }
	if (typeof animate != 'boolean') { animate = true; }
	
	if(next === true){
		next = submenu_Getnext();
	} else if(next === false){
		next = 1;
	} else if(next === -1){
		next = submenu_Getposition(menu);
	}

	//If the tab already exists, just close it if we launch again the action
	
	if (hide) {
		for (var index in submenu_obj) {
			if (submenu_obj[index].menu == menu) {
				submenu_Clean(next, true);
				return false;
			}
		}
	}

	if (menu in submenu_list) {
		var temp = new Submenu(menu, next, param, animate);
		$('#app_application_submenu_block').show();
		if (responsive.test("minDesktop")) {
			$('#app_content_dynamic').addClass('app_application_submenu_blur');
		}
		var layer = temp.layer;
		submenu_obj[layer] = temp;
		temp = null;
		return submenu_obj[layer];
	}
	return true;
}

function submenu_content_block_hide() {
	//submenu_show
	for (var i in submenu_obj) {
		if (submenu_obj[i]) {
			return true;
		}
	}
	$('#app_application_submenu_block').hide();
}

function submenu_wrapper_width() {
	var width = Math.floor($('#app_application_content').width()/3);
	$('#app_application_submenu_block .submenu_wrapper').css('width', width);
}
submenu_wrapper_width();
var submenu_wrapper_width_timer;
$(window).resize(function(){
	clearTimeout(submenu_wrapper_width_timer);
	submenu_wrapper_width_timer = setTimeout(submenu_wrapper_width, wrapper_timeout_timer);
});

function submenu_content_unblur() {
	$('#app_content_dynamic').removeClass('app_application_submenu_blur');
}
submenu_content_unblur();

jQuery.prototype.submenu_getWrapper = function(){
	var wrapper = $(this).closest('.submenu_wrapper');
	if(wrapper.length > 0 && wrapper[0].id){
		var subm = submenu_getById(wrapper[0].id);
		if(subm){
			return [subm, wrapper];
		}
	}
	return false;
};

