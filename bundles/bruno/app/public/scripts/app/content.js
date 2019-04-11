var app_content_dynamic_position = function() {
	$('#app_content_dynamic, #app_content_dynamic_sub').height(function(){
		return $(window).height() - $('#app_content_top').height();
	});
	$('#app_content_dynamic_sub').width($('#app_content_dynamic').width());
}
app_content_dynamic_position();
var app_content_dynamic_position_timer;
$(window).resize(function(){
	clearTimeout(app_content_dynamic_position_timer);
	app_content_dynamic_position_timer = setTimeout(app_content_dynamic_position, wrapper_timeout_timer);
});

var app_content_menu = {

	menu: null,

	param: null,

	param_md5: null,

	selection: function(menu, param){
		if(typeof menu == 'undefined'){ menu = 'pitch'; }
		if(typeof param == 'undefined'){ param = null; }

		if(app_content_menu.menu == menu && app_content_menu.param_md5 == md5(param)){
			return true;
		}

		//Clean all submenu before opening a new project (even if the same as current one)
		submenu_Hideall(true);

		var index_state = 0;
		var order_state = {
			pitch: 0,
			question: 1,
			answer: 2,
		};
		if(typeof order_state[menu] != 'undefined'){
			index_state = order_state[menu];
		}
		app_generic_state.change(
			{
				menu: index_state,
			},
			param,
			1
		);

		app_content_menu.menu = menu;
		app_content_menu.param = param;

		app_layers_changePage(menu, param);

		return true;
	},
};

$('#app_content_top_home').click(function(){
	submenu_Build('settings');
});

var app_content_resize_timer;
var app_content_resize = function(){
	clearTimeout(app_content_resize_timer);
	app_content_resize_timer = setTimeout(function(){
		if($("#app_content_dynamic_sub").length > 0){
			if($("#app_content_dynamic_sub").width() <= 900){
				$("#app_content_dynamic_sub").addClass("max-width-900");
			} else {
				$("#app_content_dynamic_sub").removeClass("max-width-900");
			}
		}
		app_application_bruno.prepare("resize", true);
	}, 400);
};

$(window).resize(function(){
	app_content_resize();
});

var app_content_top_title = {
	pitch: null,
	question: null,
	answer: null,
	_set: function(type, id){
		if(type in app_content_top_title){
			app_content_top_title[type] = id;
			app_application_save_page(type, id);
		}
	},
};

$("#app_content_top_title_select_pitches").on('click', function(){
	app_content_top_title._set('pitch', null);
	app_content_top_title._set('question', null);
	app_content_top_title._set('answer', null);
	app_content_menu.selection("pitch");
});
$("#app_content_top_title_select_questions").on('click', function(){
	app_content_top_title._set('question', null);
	app_content_top_title._set('answer', null);
	app_content_menu.selection("question", app_content_top_title.pitch);
});
$("#app_content_top_title_select_answers").on('click', function(){
	app_content_top_title._set('answer', null);
	app_content_menu.selection("answer",app_content_top_title.question);
});

var app_layers_content_move = {

	moving: false,

	wrapper: false,

	clone: false,

	elem: false,

	timer: false,

	touchcancel_timer: false,

	cb_begin: false,

	cb_success: false,

	cb_progress: false,

	mouse_status_leave: false,

	//Check where the mouse is when start to avoid (use % because of rotation and zoom)
	offsetTop: false,
	offsetLeft: false,

	//Check the mouse offset after timeout
	currentTop: 0,
	currentLeft: 0,

	init: function(){
		clearTimeout(app_layers_content_move.timer);
		clearTimeout(app_layers_content_move.touchcancel_timer);
		wrapper_IScroll_switch();
		app_layers_content_move.moving = false;
		app_layers_content_move.wrapper = false;
		if(app_layers_content_move.clone && app_layers_content_move.clone.length>0){
			app_layers_content_move.clone.recursiveRemove();
		}
		app_layers_content_move.clone = false;
		if(app_layers_content_move.elem && app_layers_content_move.elem.length>0){
			app_layers_content_move.elem.removeClass('shadow');
			app_layers_content_move.elem.find("[find=changeposition]").recursiveRemove();
		}
		app_layers_content_move.elem = false;
		app_layers_content_move.timer = false;
		app_layers_content_move.cb_begin = false;
		app_layers_content_move.cb_success = false;
		app_layers_content_move.cb_progress = false;
		app_layers_content_move.offsetTop = false;
		app_layers_content_move.offsetLeft = false;
	},

	mousedown: function(event, Elem, cb_begin, cb_success, cb_progress){
		if(!wrapper_IScroll_scrolling && !app_layers_content_move.moving && Elem.length>0){
			app_layers_content_move.init();
			app_layers_content_move.elem = Elem;
			wrapper_mouse.set(event);
			app_layers_content_move.currentTop = wrapper_mouse.y;
			app_layers_content_move.currentLeft = wrapper_mouse.x;
			clearTimeout(app_layers_content_move.timer);
			clearTimeout(app_layers_content_move.touchcancel_timer);
			app_layers_content_move.timer = setTimeout(function(){
				if(
					   wrapper_IScroll_scrolling
					|| Math.abs(app_layers_content_move.currentTop - wrapper_mouse.y) > 5
					|| Math.abs(app_layers_content_move.currentLeft - wrapper_mouse.x) > 5
				){
					app_layers_content_move.init();
					return false;
				}
				app_layers_content_move.moving = true;
				wrapper_IScroll_switch(false);
				var position_wrapper = $('#app_layers_content').find("[find=wrapper]");
				if(app_layers_content_move.elem && app_layers_content_move.elem.length>0 && position_wrapper.length>0){
					var clone = $('#'+app_layers_content_move.elem.prop('id')+'_clone');
					if(clone.length<=0){
						clone = $('#'+app_layers_content_move.elem.prop('id')).clone();
						clone.prop('id', app_layers_content_move.elem.prop('id')+'_clone');
						clone.appendTo(position_wrapper);
						
						var frame_clone = $('<div find="changeposition" class="app_content_clone_frame_clone">');
						frame_clone.width(clone.outerWidth());
						frame_clone.height(clone.outerHeight());
						var posTop = 0;
						var margin = parseInt(clone.css('border-top'), 10);
						if(margin){
							posTop = posTop - margin;
						}
						var posLeft = 0;
						var margin = parseInt(clone.css('border-left'), 10);
						if(margin){
							posLeft = posLeft - margin;
						}
						frame_clone.css({
							top: posTop,
							left: posLeft,
						});
						frame_clone.appendTo(clone);
						
						var frame_elem = $('<div find="changeposition" class="app_content_clone_frame_elem">');
						frame_elem.width(app_layers_content_move.elem.outerWidth());
						frame_elem.height(app_layers_content_move.elem.outerHeight());
						var posTop = 0;
						var margin = parseInt(app_layers_content_move.elem.css('border-top'), 10);
						if(margin){
							posTop = posTop - margin;
						}
						var posLeft = 0;
						var margin = parseInt(app_layers_content_move.elem.css('border-left'), 10);
						if(margin){
							posLeft = posLeft - margin;
						}
						frame_elem.css({
							top: posTop,
							left: posLeft,
						});
						frame_elem.appendTo(app_layers_content_move.elem);
					}
					app_layers_content_move.clone = $('#'+clone.prop('id'));
					app_layers_content_move.wrapper = $('#app_layers_content').find('[find=wrapper]');
					if(typeof cb_success == 'function'){
						app_layers_content_move.cb_begin = cb_begin;
					}
					if(typeof cb_success == 'function'){
						app_layers_content_move.cb_success = cb_success;
					}
					if(typeof cb_progress == 'function'){
						app_layers_content_move.cb_progress = cb_progress;
					}
					var rotate = -0.60;
					var scale = 1.01;
					if(responsive.test("maxMobileL")){
						rotate = -0.70;
						scale = 1.02;
					}
					clone
					.css('visibility', 'hidden')
					.addClass('move')
					.recursiveOff()
					.velocity(
						{
							rotateZ: rotate,
							scale: scale,
							opacity: 0.90,
						},
						{
							mobileHA: hasGood3Dsupport,
							duration: 150,
							delay: 10,
							begin: function(){
								if(app_layers_content_move.elem && app_layers_content_move.elem.length>0){
									app_layers_content_move.offsetLeft = (wrapper_mouse.x - app_layers_content_move.elem.offset().left) / app_layers_content_move.elem.outerWidth();
									app_layers_content_move.offsetTop = (wrapper_mouse.y - app_layers_content_move.elem.offset().top) / app_layers_content_move.elem.outerHeight();
									app_layers_content_move.move();
									app_layers_content_move.elem.addClass('shadow');
								}
								if(typeof app_layers_content_move.cb_begin == 'function'){
									app_layers_content_move.cb_begin();
								}
								if(app_layers_content_move.clone && app_layers_content_move.clone.length>0){
									app_layers_content_move.clone.css('visibility', '');
								}
							},
							complete: function(){
								app_layers_content_move.move();
							},
						}
					);
				}
			}, 400);
		}
	},

	reset: function(animation){
		if(typeof animation == 'undefined'){ animation = true; }
		clearTimeout(app_layers_content_move.timer);
		clearTimeout(app_layers_content_move.touchcancel_timer);
		if(app_layers_content_move.moving){
			if(typeof app_layers_content_move.cb_progress == 'function'){
				app_layers_content_move.cb_progress();
			}
			if(
				   animation
				&& app_layers_content_move.clone
				&& app_layers_content_move.elem
				&& app_layers_content_move.clone.length>0
				&& app_layers_content_move.elem.length>0
				){
					var posTop = app_layers_content_move.elem.offset().top - app_layers_content_move.wrapper.offset().top;
					var margin = parseInt(app_layers_content_move.elem.css('margin-top'), 10);
					if(margin){
						posTop = posTop - margin;
					}
					var posLeft = app_layers_content_move.elem.offset().left - app_layers_content_move.wrapper.offset().left;
					var margin = parseInt(app_layers_content_move.elem.css('margin-left'), 10);
					if(margin){
						posLeft = posLeft - margin;
					}
					app_layers_content_move.clone
					.velocity(
						{
							rotateZ: -0,
							scale: 1,
							opacity: 1,
							top: posTop,
							left: posLeft,
						},
						{
							mobileHA: hasGood3Dsupport,
							duration: 200,
							delay: 10,
							begin: function(){
								if(typeof app_layers_content_move.cb_success == 'function'){
									app_layers_content_move.cb_success();
								}
							},
							complete: function(){
								app_layers_content_move.init();
							},
						}
					);
			} else {
				if(typeof app_layers_content_move.cb_success == 'function'){
					app_layers_content_move.cb_success();
				}
				app_layers_content_move.init();
			}	
		} else {
			app_layers_content_move.init();
		}
	},

	move: function(event){
		if(
			   app_layers_content_move.elem
			&& app_layers_content_move.wrapper
			&& app_layers_content_move.clone
			&& app_layers_content_move.elem.length>0
			&& app_layers_content_move.wrapper.length>0
			&& app_layers_content_move.clone.length>0
		){
			var Boundaries = app_layers_content_move.elem.get()[0].getBoundingClientRect();

			//Top
			var rel_top = wrapper_mouse.y - app_layers_content_move.wrapper.offset().top;
			if(app_layers_content_move.offsetTop!==false && app_layers_content_move.offsetTop>=0 && app_layers_content_move.offsetTop<=1){
				rel_top = rel_top - Math.round(Boundaries.height  * app_layers_content_move.offsetTop);
			} else {
				rel_top = rel_top - Math.round(Boundaries.height / 2);
			}
			var margin = parseInt(app_layers_content_move.elem.css('margin-top'), 10);
			if(margin){
				rel_top = rel_top - margin;
			}
		
			//Left
			var rel_left = wrapper_mouse.x - app_layers_content_move.wrapper.offset().left;
			if(app_layers_content_move.offsetLeft!==false && app_layers_content_move.offsetLeft>=0 && app_layers_content_move.offsetLeft<=1){
				rel_left = rel_left - Math.round(Boundaries.width * app_layers_content_move.offsetLeft);
			} else {
				rel_left = rel_left - Math.round(Boundaries.width / 2);
			}
			var margin = parseInt(app_layers_content_move.elem.css('margin-left'), 10);
			if(margin){
				rel_left = rel_left - margin;
			}

			app_layers_content_move.clone.css({ top: rel_top, left: rel_left });
			if(typeof app_layers_content_move.cb_progress == 'function'){
				app_layers_content_move.cb_progress();
			}
			app_layers_content_move.scroll();
		}
	},

	scroll: function(){
		var content = $("#app_layers_content");
		var overthrow = content.find(".overthrow").first();
		if(overthrow && typeof myIScrollList[overthrow.prop("id")] != 'undefined'){
			var speed = 0;
			var mouse_ratio = (wrapper_mouse.y - content.offset().top) / content.height();
			if(mouse_ratio < 0.10){
				speed = 4;
			} else if(mouse_ratio < 0.20){
				speed = 2;
			} else if(mouse_ratio > 0.90){
				speed = -4;
			} else if(mouse_ratio > 0.80){
				speed = -2;
			}
			if(speed == 0){
				return false;
			} else if(speed > 0){ //move up
				if(myIScrollList[overthrow.prop("id")].y >= 0){
					myIScrollList[overthrow.prop("id")].scrollTo(0, 0, 0, wrapper_IScroll_easing_linear);
					return false;
				}
			} else if(speed < 0){
				if(myIScrollList[overthrow.prop("id")].y <= myIScrollList[overthrow.prop("id")].maxScrollY){
					myIScrollList[overthrow.prop("id")].scrollTo(0, myIScrollList[overthrow.prop("id")].maxScrollY, 0, wrapper_IScroll_easing_linear);
					return false;
				}
			}
			myIScrollList[overthrow.prop("id")].scrollBy(0, speed, 0, wrapper_IScroll_easing_linear);
		}
	},

};


var app_content_user_data = app_application_garbage.add();
app_application_bruno.add(app_content_user_data, 'first_launch', function() {
	if(Bruno.storage.getPlanAt() && $("#app_content_subscribe").length > 0){
		var appear = false;
		if(wrapper_read_only){
			appear = true;
			$("#app_content_subscribe").find("[find=text]").html(Bruno.Translation.get('app', 166, 'brut')); //Your account has expired. <span find="subscribe">Subscribe</span> to unlock features.
		} else if(Bruno.storage.getPlanAt() - (new wrapper_date()).getTimestamp() < 30*24*3600*1000){
			appear = true;
			$("#app_content_subscribe").find("[find=paypal]").addClass(("display_none"));
			var remain_days = Math.abs(Math.floor((Bruno.storage.getPlanAt() - (new wrapper_date()).getTimestamp()) / (24*3600*1000)));
			$("#app_content_subscribe").find("[find=text]").html(Bruno.Translation.get('app', 167, 'brut', {days: remain_days})); //Your account expires in [{days}] days. You can <span find="subscribe">update</span> your subscription plan.
		}
		if(appear){
			if($("#app_content_subscribe").find("[find=subscribe]").length > 0){
				$("#app_content_subscribe").find("[find=subscribe]")
					.addClass("app_content_subscribe_link")
					.on('click', function(){
						submenu_Build_return('subscription', true, true);
					});
			}

			$("#app_content_subscribe").find("[find=paypal]").on('click', function(){
				submenu_Build_return('subscription', true, true);
			});
			
			//Slide from right side
			$("#app_content_subscribe").velocity("transition.slideRightBigIn", {
				mobileHA: hasGood3Dsupport,
				duration: 1300,
				delay: 600,
				begin: function(){
					$("#app_content_subscribe").removeClass("display_none");
				},
			});
		}
		app_application_garbage.remove(app_content_user_data);
	}
});

JSfiles.finish(function(){
	
	app_content_menu.selection("pitch");
	if(device_type()!='computer'){
		$('#app_content_pc').removeClass('display_none');
		$('#app_content_copyright').addClass('display_none');
	} else {
		$('#app_content_pc').addClass('display_none');
		$('#app_content_copyright').removeClass('display_none');
	}

	$(window).on('mousemove touchmove', function(event){
		app_layers_content_move.move();
	});
	$(window).on('mouseup touchup touchend', function(event){
		if(app_layers_content_move.moving){
			app_layers_content_move.reset();
		}
	});
	$(window).on('touchcancel', function(event){
		if(app_layers_content_move.moving){
			clearTimeout(app_layers_content_move.touchcancel_timer);
			//Cancel move after 4s
			app_layers_content_move.touchcancel_timer = setTimeout(function(){
				if(app_layers_content_move.moving){
					app_layers_content_move.reset();
				}
			}, 4000);
		}
	});
	$('body').on('mouseleave', function(event){
		if(device_type()!='computer' || supportsTouch){ return false; }
		app_layers_content_move.mouse_status_leave = event.buttons;
	});
	$('body').on('mouseenter', function(event){
		if(device_type()!='computer' || supportsTouch){ return false; }
		if(app_layers_content_move.moving && event.buttons!=1){
			app_layers_content_move.reset(false);
		}
		if(app_layers_content_move.mouse_status_leave != event.buttons){
			wrapper_IScroll_refresh();
		}
		app_layers_content_move.mouse_status_leave = event.buttons;
	});

});

