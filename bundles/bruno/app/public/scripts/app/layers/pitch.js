
function app_layers_pitch_launchPage(param){
	if(typeof param === 'undefined'){ param = null; }
	app_layers_pitch_feedPage(param);

	$('#app_content_top_title_pitches').removeClass('display_none');
	$('#app_content_top_title_questions, #app_content_top_title_answers').addClass('display_none');
	$('#app_content_top_title_menu').find('.app_content_top_title_select_opened').removeClass('app_content_top_title_select_opened');
	$('#app_content_top_title_select_pitches').addClass('app_content_top_title_select_opened');
}

var app_layers_pitch_refresh_timer;
var app_layers_pitch_refresh = function(timer){
	if(typeof timer == 'undefined'){
		timer = wrapper_timeout_timer;
	}
	clearTimeout(app_layers_pitch_refresh_timer);
	app_layers_pitch_refresh_timer = setTimeout(function(){
		//Delete the last border in Mobile mode
		var layer = $('#app_layers_content');
		layer.find('.models_pitch_standard_last').removeClass('models_pitch_standard_last');
		layer.find('.models_pitch_standard').last().addClass('models_pitch_standard_last');
		wrapper_IScroll();
		if(typeof myIScrollList['app_layers_pitch'] == 'object' && (myIScrollList['app_layers_pitch'].hasHorizontalScroll || myIScrollList['app_layers_pitch'].hasVerticalScroll)){
			$('#app_layers_pitch_add_corner').removeClass('display_none');
		} else {
			$('#app_layers_pitch_add_corner').addClass('display_none');
		}
	}, timer);
};
$(window).resize(app_layers_pitch_refresh);

var app_layers_pitch_new_animation = false;
var app_layers_pitch_feedPage = function(param){
	if(typeof param == 'undefined'){ param = null; }
	var sub_layer = $('#app_layers_pitch');
	sub_layer.addClass('overthrow');
	sub_layer.recursiveEmpty();
	$('<div find="wrapper" class="app_layers_pitch_wrapper"></div>').appendTo(sub_layer);
	var position_wrapper = sub_layer.find("[find=wrapper]");

	//Preview icon
	$('#app_content_top_right')
		.addClass('display_none')
		.off('click');

	Elem = $('#-app_layers_pitch_add_icon').clone();
	Elem.prop('id', 'app_layers_pitch_add_icon');
	Elem.click(param, function(event){
		event.stopPropagation();
		if(!$(this).find("[find=add]").hasClass("display_none")){
			$(this).addClass("app_layers_pitch_add_icon_fixed");
			$(this).find("[find=add]").addClass("display_none");
			$(this).find("[find=input]").removeClass("display_none");
			$(this).css('cursor', 'default');
			$('#app_layers_pitch_add_corner').addClass('app_layers_pitch_add_corner_display_none');
			var items = Bruno.storage.list('pitch');
			var param = {
				number: items.length+1,
			};
			$(this).find("[find=input_textarea]")
				.val(Bruno.Translation.get('app', 2103, 'html', param)) //Pitch #[{number]}
				.focus()
				.select()
				.textareaRows();
			wrapper_IScroll();
		}
	});
	//Create new item
	Elem.find("[find=input_textarea]").on('keyup', function(event){
		if(event.which == 13){
			app_layers_pitch_icon_create();
			event.preventDefault();
		}
		return false;
	});
	//Disable New Line
	Elem.find("[find=input_textarea]").on('keydown keypress change copy paste cut input', function(event){
		if(event.type=="copy" || event.type=="paste" || event.type=="cut"){
			setTimeout(function(that){
				var str = that.val();
				if(str.match(/(\r\n|\n|\r)/gm)){
					str = str.replace(/(\r\n|\n|\r)/gm, " ");
					that.val(str);
				}
				var rows_prev = parseInt(that.attr('rows'), 10);
				that.textareaRows();
				if(rows_prev != parseInt(that.attr('rows'), 10)){
					wrapper_IScroll();
				}
			}, 0, $(this));
		} else {
			var str = $(this).val();
			if(str.match(/(\r\n|\n|\r)/gm)){
				str = str.replace(/(\r\n|\n|\r)/gm, " ");
				$(this).val(str);
			}
		}
		if(event.which == 13){
			event.preventDefault();
		} else if(event.which == 27){
			app_layers_pitch_icon_back();
		}
		var rows_prev = parseInt($(this).attr('rows'), 10);
		$(this).textareaRows();
		if(rows_prev != parseInt($(this).attr('rows'), 10)){
			wrapper_IScroll();
		}
	});

	//All the focus/blur trick is to solve an issue than when the focus is on the textarea we have to click twice on OK (or CANCEL) because the first click does only a blur on textarea
	//This still be a qdirty fix because the problem still exits with any other area of the screen, but it's less obvious and annoying for the user
	Elem.find("[find=input_textarea]").data('focus', false);
	Elem.find("[find=input_textarea]").on('focus', function(event){
		$(this).textarea.data('focus', true);
	});
	Elem.find("[find=input_textarea]").on('blur', function(event){
		$(this).textarea.data('focus', false);
	});
	Elem.find("[find=input_create], [find=input_cancel]").hover(
		//MouseIn
		function(){
			var textarea = $("#app_layers_pitch_add_icon").find("[find=input_textarea]");
			if(textarea.is( ":focus" )){
				textarea.data('focus', true);
				textarea.blur();
			} else {
				textarea.data('focus', false);
			}
		},
		//MouseOut
		function(){
			var textarea = $("#app_layers_pitch_add_icon").find("[find=input_textarea]");
			if(textarea.data('focus')){
				textarea.focus();
			}
		}
	);

	Elem.find("[find=input_cancel]").on('click', function(event){
		app_layers_pitch_icon_back();
		event.preventDefault();
		return false;
	});
	Elem.find("[find=input_create]").on('click', function(event){
		app_layers_pitch_icon_create();
		event.preventDefault();
		return false;
	});
	Elem.appendTo(position_wrapper);

	var layer = $('#app_layers_content');
	Elem = $('#-app_layers_pitch_add_corner').clone();
	Elem.prop('id', 'app_layers_pitch_add_corner');
	Elem.click(param, function(event){
		event.stopPropagation();
		$('#app_layers_pitch_add_icon').click();
	});
	Elem.appendTo(layer);

	app_layers_pitch_refresh(0);

	app_application_bruno.add("app_layers_pitch", "pitch_delete", function(){
		$('#app_layers_pitch').find("[pitch_id]").filter(function() {
			var id = parseInt($(this).attr("pitch_id"), 10);
			var item = Bruno.storage.get('pitch', id);
			if(!item){
				$(this)
					.find("[find=delete]")
					.recursiveOff()
					.css("cursor", "default")
				$(this)
					.recursiveOff()
					.css("cursor", "default")
					.velocity(
						'transition.expandOut',
						{
							mobileHA: hasGood3Dsupport,
							duration: 500,
							delay: 10,
							complete: function(){
								$(this).recursiveRemove();
								app_layers_pitch_refresh();
							},
						}
					);
			}
			return false;
		});
	});

	app_layers_pitch_new_animation = false;
	app_application_bruno.add("app_layers_pitch", "pitch", function(){
		var items = Bruno.storage.list('pitch');
		items = Bruno.storage.sort_items(items, 'id', 0, -1, true);
		items = Bruno.storage.sort_items(items, 'sort', 0, -1, false);
		var item;
		var Elem;
		var position;
		var time_limit = Bruno.now.getTimeServer()-(10*1000);
		for(var i in items){
			item = items[i];
			if($('#models_pitch_'+item['id']).length==0){
				Elem = $('#-models_pitch').clone();
				Elem.prop('id', 'models_pitch_'+item['id']);
				Elem.attr('c_at', item['c_at']);
				Elem.attr('pitch_id', item['id']);
				Elem.find("[find=title]").html( wrapper_to_html(item['title']) );
				Elem.find("[find=delete]").click(
					item['id'],
					function(event){
						event.stopPropagation();
						app_layers_content_move.reset();
						if(confirm(Bruno.Translation.get('app', 26, 'js'))){ //Are you sure you want to delete this item?
							var data = {};
							data.delete = {};
							data.delete.pitch = {};
							var item = Bruno.storage.get('pitch', event.data);
							data.delete.pitch[item['id']] = {
								id: item['id'],
								md5: item['md5'],
							};
							var action_cb_success = function(msg, error, status, extra){
								storage_cb_success(msg, error, status, extra);
								app_content_menu.selection("pitch");
							}
							var action_cb_complete = function(){
								storage_cb_complete();
								app_application_bruno.prepare("pitch", true);
							};
							if(storage_offline(data)){
								wrapper_sendAction(data, 'post', 'api/data/set', action_cb_success, storage_cb_error, storage_cb_begin, action_cb_complete);
							}
						}
					}
				);
				Elem.find("[find=preview]").click(
					item['id'],
					function(event){
						event.stopPropagation();
						app_layers_content_move.reset();
						var pitch_id = Bruno.storage.get("pitch", event.data, "id");
						showppt_launch(pitch_id);
					}
				);
				Elem.find("[find=settings]").click(
					item['id'],
					function(event){
						event.stopPropagation();
						app_layers_content_move.reset();
						var pitch_id = Bruno.storage.get("pitch", event.data, "id");
						submenu_Build('app_answer_get_presentation', true, true, pitch_id);
					}
				);
				Elem.click(
					item['id'],
					function(event){
						event.stopPropagation();
						app_layers_content_move.reset();
						app_content_menu.selection("question", event.data);
					}
				);
				Elem.on('mousedown touchdown touchstart', function(event){
					app_layers_content_move.mousedown(
						event,
						$(this),
						function(){ //cb_begin
							app_layers_pitch_list_position.build();
						},
						function(){ //cb_success
							if(!app_layers_pitch_list_position.list){
								return false;
							}
							//Order from big to small
							var order_id = [];
							var keysY = Object.keys(app_layers_pitch_list_position.list).sort(function(a, b) { return b - a; });
							for(var y in keysY){
								var keyY = keysY[y];
								if(typeof app_layers_pitch_list_position.list[keyY] == 'undefined'){
									continue;
								}
								var keysX = Object.keys(app_layers_pitch_list_position.list[keyY]).sort(function(a, b) { return b - a; });
								for(var x in keysX){
									var keyX = keysX[x];
									if(typeof app_layers_pitch_list_position.list[keyY][keyX] == 'undefined'){
										continue;
									}
									if($("#"+app_layers_pitch_list_position.list[keyY][keyX]).length>0){
										order_id.push(parseInt($("#"+app_layers_pitch_list_position.list[keyY][keyX]).attr("pitch_id"), 10));
									}
								}
							}
							var data = {};
							data.set = {};
							data.set.pitch = {};
							for(var i in order_id){
								var order = parseInt(i, 10)+1;
								var item = Bruno.storage.get("pitch", order_id[i]);
								if(item){
									data.set.pitch[item['id']] = {
										id: item['id'],
										md5: item['md5'],
										sort: order,
									};
								}
							}
							app_layers_pitch_list_position.list = false;
							app_layers_pitch_list_position.current.x = false;
							app_layers_pitch_list_position.current.y = false;
							var action_cb_complete = function(){
								storage_cb_complete();
								app_application_bruno.prepare("pitch", true);
							};
							if(storage_offline(data)){
								app_application_bruno.prepare("pitch", true);
								wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, action_cb_complete);
							}
						},
						function(){ //cb_progress
							if(app_layers_pitch_list_position.list && app_layers_content_move.clone && app_layers_content_move.clone.length>0){
								var posY = false;
								var keysY = Object.keys(app_layers_pitch_list_position.list).sort(function(a, b) { return a - b; });
								for(var i in keysY){
									var y = keysY[i];
									if(!posY){
										posY = y;
									}
									if(wrapper_mouse.y > y){
										posY = y;
									}
								}
								if(posY){
									if(typeof app_layers_pitch_list_position.list[posY] == 'undefined'){
										return false;
									}
									var posX = false;
									var keysX = Object.keys(app_layers_pitch_list_position.list[posY]).sort(function(a, b) { return a - b; });
									for(var i in keysX){
										var x = keysX[i];
										if(!posX){
											posX = x;
										}
										if(wrapper_mouse.x > x){
											posX = x;
										}
									}
								}
								if(posY && posX){
									if(
										   (posY != app_layers_pitch_list_position.current.y
										|| posX != app_layers_pitch_list_position.current.x)
										&& app_layers_content_move.elem
										&& app_layers_content_move.elem.length>0
										&& typeof app_layers_pitch_list_position.list[posY] != 'undefined'
										&& typeof app_layers_pitch_list_position.list[posY][posX] != 'undefined'
										&& $('#'+app_layers_pitch_list_position.list[posY][posX]).length>0
									){
										var direction = 'before';
										if(posY > app_layers_pitch_list_position.current.y){
											direction = 'after';
										} else if(posY == app_layers_pitch_list_position.current.y){
											if(posX > app_layers_pitch_list_position.current.x){
												direction = 'after';
											}
										}
										if(direction=='before'){
											app_layers_content_move.elem.insertBefore($('#'+app_layers_pitch_list_position.list[posY][posX]));
										} else {
											app_layers_content_move.elem.insertAfter($('#'+app_layers_pitch_list_position.list[posY][posX]));
										}
										app_layers_pitch_list_position.build();
										app_layers_pitch_refresh();
									}
									
								}
							}
						}
					);
				});
					
				if($("#app_layers_pitch_add_icon").length>0){
					Elem.insertBefore($("#app_layers_pitch_add_icon"));
				} else {
					position = $('#app_layers_pitch').find("[find=wrapper]");
					Elem.appendTo(position);
				}
				if(app_layers_pitch_new_animation && 1000*item['c_at'] > time_limit){
					Elem
					.velocity(
						'transition.expandIn',
						{
							mobileHA: hasGood3Dsupport,
							duration: 500,
							delay: 10,
							display: 'inline-table',
							complete: function(){
								app_layers_pitch_refresh();
							},
						}
					);
				}
			} else {
				Elem = $('#models_pitch_'+item['id']);
				Elem.find("[find=title]").html( wrapper_to_html(item['title']) );
			}
		}
		app_layers_pitch_new_animation = true;
		app_layers_pitch_refresh();
	}, param);

	app_application_bruno.prepare("pitch", true);

};

var app_layers_pitch_list_position = {

	current: {
		x: false,
		y: false,
	},

	list: false,

	build: function(){
		if(app_layers_content_move.elem && app_layers_content_move.elem.length>0){
			var elem_id = app_layers_content_move.elem.prop('id');
			app_layers_pitch_list_position.list = {};
			$('#app_layers_content').find("[pitch_id]").each(function(){
				var that = $(this);
				if(that.prop('id') == app_layers_content_move.clone.prop('id')){
					return false;
				}
				var offset = that.offset();
				if(typeof app_layers_pitch_list_position.list[offset.top] == 'undefined'){
					app_layers_pitch_list_position.list[offset.top] = {};
				}
				app_layers_pitch_list_position.list[offset.top][offset.left] = that.prop('id');
				if(app_layers_pitch_list_position.list[offset.top][offset.left] == elem_id){
					app_layers_pitch_list_position.current.x = offset.left;
					app_layers_pitch_list_position.current.y = offset.top;
				}
			});
		}
	},
};

var app_layers_pitch_icon_back = function(){
	if($("#app_layers_pitch_add_icon").length>0){
		$("#app_layers_pitch_add_icon").removeClass("app_layers_pitch_add_icon_fixed");
		$("#app_layers_pitch_add_icon").find("[find=add]").removeClass("display_none");
		$("#app_layers_pitch_add_icon").find("[find=input]").addClass("display_none");
		$("#app_layers_pitch_add_icon").css('cursor', '');
		$("#app_layers_pitch_add_icon").find("[find=input_textarea]").val("");
		$("#app_layers_pitch_add_icon").blur();
		$('#app_layers_pitch_add_corner').removeClass('app_layers_pitch_add_corner_display_none');
		wrapper_IScroll();
		app_layers_pitch_refresh();
	}
};

var app_layers_pitch_icon_create_running = false; //Avoid double create by multiple clicks
var app_layers_pitch_icon_create = function(){
	if(!app_layers_pitch_icon_create_running && $("#app_layers_pitch_add_icon").length>0){
		var data = {};
		data.set = {};
		data.set.pitch = {};
		var md5id = Bruno.storage.getMD5("pitch");
		var title = $("#app_layers_pitch_add_icon").find("[find=input_textarea]").val();
		title = title.replace(/(\r\n|\n|\r)/gm, " ");
		data.set.pitch[md5id] = {
			md5: md5id,
			title: title,
		};
		var Elem_bis = $("#app_layers_pitch_add_icon").find("[find=input_create]");
		var action_cb_success = function(msg, error, status, extra){
			storage_cb_success(msg, error, status, extra);
			if(!error){
				app_layers_pitch_icon_back();
			}
		}
		var action_cb_complete = function(){
			app_layers_pitch_icon_create_running = false;
			storage_cb_complete();
			base_hideProgress(Elem_bis);
			app_application_bruno.prepare("pitch", true);
			var item = Bruno.storage.findMD5(md5id, "pitch");
			if(item){
				setTimeout(function(parent_id){
					app_content_menu.selection("question", parent_id);
				}, 400, item['id']);
			}
		};
		if(storage_offline(data)){
			base_showProgress(Elem_bis);
			app_layers_pitch_icon_create_running = true;
			wrapper_sendAction(data, 'post', 'api/data/set', action_cb_success, storage_cb_error, storage_cb_begin, action_cb_complete);
		}
	}
};
