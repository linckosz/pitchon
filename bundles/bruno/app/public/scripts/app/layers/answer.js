
function app_layers_answer_launchPage(param){
	if(typeof param === 'undefined'){ param = null; }
	app_layers_answer_feedPage(param);

	$('#app_content_top_title_pitches, #app_content_top_title_questions, #app_content_top_title_answers').removeClass('display_none');

	$('#app_content_top_title_menu').find('.app_content_top_title_select_opened').removeClass('app_content_top_title_select_opened');
	$('#app_content_top_title_select_answers').addClass('app_content_top_title_select_opened');
}

var app_layers_answer_refresh_timer;
var app_layers_answer_refresh = function(timer){
	if(typeof timer == 'undefined'){
		timer = wrapper_timeout_timer;
	}
	clearTimeout(app_layers_answer_refresh_timer);
	app_layers_answer_refresh_timer = setTimeout(function(){
		//Delete the last border in Mobile mode
		var layer = $('#app_layers_content');
		layer.find('.models_answer_standard_last').removeClass('models_answer_standard_last');
		layer.find('.models_answer_standard').last().addClass('models_answer_standard_last');
		wrapper_IScroll();
	}, timer);
}
$(window).resize(app_layers_answer_refresh);

var app_layers_answer_clipboard_pitch = false;
var app_layers_answer_question = null;

var app_layers_answer_new_animation = false;
var app_layers_answer_feedPage = function(param){
	if(typeof param == 'undefined'){ param = null; }
	var sub_layer = $('#app_layers_answer');
	sub_layer.addClass('overthrow');
	sub_layer.recursiveEmpty();
	$('<div find="wrapper" class="app_layers_answer_wrapper"></div>').appendTo(sub_layer);
	var position_wrapper = sub_layer.find("[find=wrapper]");

	app_content_top_title._set('question', param);
	app_layers_answer_question = Bruno.storage.get('question', param);
	var pitch = Bruno.storage.getParent('question', app_layers_answer_question['id']);

	var pitch_timer = {};
	if(pitch){
		var item = pitch;
		app_content_top_title._set('pitch', item['id']);
		Elem = $('#-models_pitch_top').clone();
		Elem.prop('id', 'models_pitch_top_'+item['id']);

		Elem.find("[find=preview]").click(
			item['id'],
			function(event){
				event.stopPropagation();
				var pitch_id = Bruno.storage.get("pitch", event.data, "id");
				var question_id = app_layers_answer_question['id'];
				showppt_launch(pitch_id, question_id);
			}
		);
		
		Elem.find("[find=input_textarea]").val(item['title']);
		//Create new item
		pitch_timer[item['id']] = null;
		Elem.find("[find=input_textarea]").on('blur keyup input', item['id'], function(event){
			var timer = 2000;
			if(event.type=="blur"){
				timer = 0;
			}
			var str = $(this).val();
			var pitch_id = event.data;
			var pitch = Bruno.storage.get('pitch', pitch_id);
			if(pitch){
				title = str.replace(/(\r\n|\n|\r)/gm, " ");
				var data = {};
				data.set = {};
				data.set.pitch = {};
				data.set.pitch[pitch['id']] = {
					id: pitch['id'],
					md5: pitch['md5'],
					title: title,
				};
			}
			clearTimeout(pitch_timer[pitch_id]);
			pitch_timer[pitch_id] = setTimeout(function(pitch_id, data){
				if(storage_offline(data)){
					wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
				}
			}, timer, pitch_id, data);
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
			if(event.which == 13 || event.which == 27){
				$(this).blur();
			}
			var rows_prev = parseInt($(this).attr('rows'), 10);
			$(this).textareaRows();
			if(rows_prev != parseInt($(this).attr('rows'), 10)){
				wrapper_IScroll();
			}
		});

		Elem.appendTo(position_wrapper);
		app_application_bruno.add('models_pitch_top_'+pitch['id'], "pitch_"+pitch['id'], function(){
			var item = Bruno.storage.get('pitch', this.action_param);
			if(item){
				$("#"+this.id).find("[find=title]").val( item['title'] );
			}
		}, pitch['id']);
	} else {
		app_content_top_title._set('pitch', null);
		app_content_top_title._set('question', null);
		app_content_top_title._set('answer', null);
		app_content_menu.selection("pitch");
		return false;
	}

	var question_timer = {};
	if(app_layers_answer_question){
		app_content_top_title._set('question', app_layers_answer_question['id']);
		Elem = $('#-models_question_top').clone();
		Elem.prop('id', 'models_question_top_'+app_layers_answer_question['id']);

		Elem.find("[find=input_textarea]").val(app_layers_answer_question['title']);
		//Create new item
		question_timer[app_layers_answer_question['id']] = null;
		Elem.find("[find=input_textarea]").on('blur keyup input', app_layers_answer_question['id'], function(event){
			var timer = 2000;
			if(event.type=="blur"){
				timer = 0;
			}
			var str = $(this).val();
			var question_id = event.data;
			var question = Bruno.storage.get('question', question_id);
			if(question){
				title = str.replace(/(\r\n|\n|\r)/gm, " ");
				var data = {};
				data.set = {};
				data.set.question = {};
				data.set.question[app_layers_answer_question['id']] = {
					id: app_layers_answer_question['id'],
					md5: app_layers_answer_question['md5'],
					title: title,
				};
			}
			clearTimeout(question_timer[question_id]);
			question_timer[question_id] = setTimeout(function(question_id, data){
				if(storage_offline(data)){
					wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
				}
			}, timer, question_id, data);
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
			if(event.which == 13 || event.which == 27){
				$(this).blur();
			}
			var rows_prev = parseInt($(this).attr('rows'), 10);
			$(this).textareaRows();
			if(rows_prev != parseInt($(this).attr('rows'), 10)){
				wrapper_IScroll();
			}
		});

		Elem.appendTo(position_wrapper);
		app_application_bruno.add('models_question_top_'+app_layers_answer_question['id'], "question_"+app_layers_answer_question['id'], function(){
			app_layers_answer_question = Bruno.storage.get('question', app_layers_answer_question['id']); //Refresh the values
			$("#"+this.id).find("[find=title]").val( app_layers_answer_question['title'] );
		});
	} else {
		app_content_top_title._set('pitch', null);
		app_content_top_title._set('question', null);
		app_content_top_title._set('answer', null);
		app_content_menu.selection("pitch");
		return false;
	}

	Elem = $('#-app_layers_answer_style').clone();
	Elem.prop('id', '');
	Elem.find("[find=select_style]").on('change', function(event){
		var data = {};
		data.set = {};
		data.set.question = {};
		if(app_layers_answer_question){
			data.set.question[app_layers_answer_question['id']] = {
				id: app_layers_answer_question['id'],
				md5: app_layers_answer_question['md5'],
				style: this.value,
			};
			if(storage_offline(data)){
				app_layers_answer_answers_style(this.value);
				app_layers_answer_answers_correct(false, true);
				wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
			}
		}
		$(this).css("background-image", "url('"+app_layers_icon_source30(this.value)+"')");
	});
	Elem.find("[find=select_style]").css("background-image", "url('"+app_layers_icon_source30(app_layers_answer_question['style'])+"')");
	//Preselect
	Elem.find("option").each(function(){
		if($(this).val() == app_layers_answer_question['style']){
			this.selected = true;
		}
	});
	Elem.appendTo(position_wrapper);
	app_application_bruno.add("app_layers_answer", "question_"+app_layers_answer_question['id']+"_style", function(){
		app_layers_answer_question = Bruno.storage.get('question', app_layers_answer_question['id']); //Refresh the values
		var style = app_layers_answer_question["style"];
		var current = $("#app_layers_answer").find("[find=select_style]").val();
		if(style && style!=current){
			$("#app_layers_answer").find("[find=select_style]").val(style).change();
			app_layers_answer_answers_style(style);
			app_layers_answer_answers_correct(false, true);
		}
	});

	//Get my Presentation button
	Elem = $('#-app_layers_answer_settings').clone();
	Elem.prop('id', 'app_layers_answer_settings');
	Elem.on('click', app_layers_answer_question['id'], function(event){
		var pitch_id = Bruno.storage.getParent("question", event.data, "id");
		submenu_Build('app_answer_get_presentation', true, true, pitch_id);
	});
	Elem.appendTo(position_wrapper);

	//Question image
	Elem = $('#-app_layers_answer_question').clone();
	Elem.prop('id', 'app_layers_answer_question');
	Elem.find("[find=add]").on('click', app_layers_answer_question['id'], function(event){
		var item = Bruno.storage.get('question', event.data);
		var temp_id = app_upload_open_photo_single('question', item['id'], item['md5'], 'file_id', null, false, true);	
		app_layers_answer_upload_status($(this), temp_id, true);
	});
	Elem.find("[find=image]").on('click', app_layers_answer_question['id'], function(event){
		var item = Bruno.storage.get('question', event.data);
		previewer(item['file_id']);
	});
	Elem.appendTo(position_wrapper);
	app_layers_answer_answers_question_image();
	app_application_bruno.add("app_layers_answer_question", "question_"+app_layers_answer_question['id'], function(){
		app_layers_answer_answers_question_image();
	});

	//Tips sentence
	Elem = $('#-app_layers_answer_tips').clone();
	Elem.prop('id', 'app_layers_answer_tips');
	Elem.appendTo(position_wrapper);

	//Add 6 answers (4 last are optional)
	var answer_timer = {};
	var answer_creating = {};
	var answers_wrapper = $('<div find="answers_wrapper" class="app_layers_answer_answers_table app_layers_answer_answers_wrapper"></div>');
	var items = Bruno.storage.list('answer', 6, null, 'question', app_layers_answer_question['id']); //We have a maximum of 6 answers
	items = Bruno.storage.sort_items(items, 'number');
	for (var i = 1; i <= 6; i++) {
		var item = false;
		if(typeof items[i-1] != "undefined"){
			item = items[i-1];
		}
		Elem = $('#-app_layers_answer_answers').clone();
		Elem.prop('id', 'app_layers_answer_answers_'+i);
		Elem.data('number', i);
		Elem.data('unavailable', false);
		Elem.addClass('number_'+i);
		Elem.find("[find=number]").html(i);
		//Change correct answer
		Elem.find("[find=answer_select]").on('click', i, function(event){
			event.stopPropagation();
			var number = event.data;
			app_layers_answer_answers_correct(number, true);
		});
		Elem.find("[find=input_textarea]").val('');
		if(item){
			Elem.find("[find=input_textarea]").val(item['title']);
		}
		var letter = String.fromCharCode(64 + parseInt(i), 10);
		letter = letter.replace(/(\r\n|\n|\r)/gm, "");
		Elem.find("[find=answer_letter]").text(letter);
		//Create new item
		answer_timer[i] = null;
		answer_creating[i] = false;
		Elem.find("[find=input_textarea]").on('blur keyup input', i, function(event){
			var timer = 2000;
			if(event.type=="blur"){
				timer = 0;
			}
			var str = $(this).val();

			var number = event.data;
			var answer = false;
			var answers = Bruno.storage.list('answer', 1, { number: number, }, 'question', app_layers_answer_question['id']);
			if(answers.length>0){
				answer = answers[0];
				answer_creating[number] = false;
			}

			if(answer){
				title = str.replace(/(\r\n|\n|\r)/gm, " ");
				var data = {};
				data.set = {};
				data.set.answer = {};
				data.set.answer[answer['id']] = {
					id: answer['id'],
					md5: answer['md5'],
					title: title,
				};
				if(storage_offline(data)){
					app_layers_answer_answers_correct(false, true);
				}
				clearTimeout(answer_timer[number]);
				answer_timer[number] = setTimeout(function(data, Elem, title){
					if($(Elem).length>0){
						title = $(Elem).val();
					}
					for(var i in data.set.answer){
						data.set.answer[i].title = title;
						break; //Because there is only one record, we can break
					}
					if(storage_offline(data)){
						app_layers_answer_answers_correct(false, true);
					}
					wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
				}, timer, data, this, title);
			} else if(!answer_creating[number]){
				title = str.replace(/(\r\n|\n|\r)/gm, " ");
				var md5id = Bruno.storage.getMD5("answer");
				var data = {};
				data.set = {};
				data.set.answer = {};
				data.set.answer[md5id] = {
					md5: md5id,
					parent_id: app_layers_answer_question['id'],
					parent_md5: app_layers_answer_question['md5'],
					number: number,
					title: title,
				};
				if(storage_offline(data)){
					app_layers_answer_answers_correct(false, true);
				}
				clearTimeout(answer_timer[number]);
				clearTimeout(answer_creating[number]);
				answer_creating[number] = setTimeout(function(data, Elem, title, number){
					if($(Elem).length>0){
						title = $(Elem).val();
					}
					for(var i in data.set.answer){
						data.set.answer[i].title = title;
						break; //Because there is only one record, we can break
					}
					if(storage_offline(data)){
						app_layers_answer_answers_correct(false, true);
					}
					wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, function(){
						storage_cb_complete();
						answer_creating[number] = false;
					});
				}, timer, data, this, title, number);
			} else {
				var Elem = $(this);
				clearTimeout(answer_timer[number]);
				answer_timer[number] = setTimeout(function(Elem){
					if($(Elem).length>0){
						$(Elem).trigger("keyup"); //Loop until the object exists
					}
				}, timer, this);
			}
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
			if(event.which == 13 || event.which == 27){
				$(this).blur();
			}
			var rows_prev = parseInt($(this).attr('rows'), 10);
			$(this).textareaRows();
			if(rows_prev != parseInt($(this).attr('rows'), 10)){
				wrapper_IScroll();
			}
		});

		Elem.find("[find=answer_add]").on('click', i, function(event){
			event.stopPropagation();
			var answer = false;
			var answers = Bruno.storage.list('answer', 1, { number: event.data, }, 'question', app_layers_answer_question['id']);
			if(answers.length>0){
				answer = answers[0];
				var temp_id = app_upload_open_photo_single('answer', answer['id'], answer['md5'], 'file_id', null, false, true);
				app_layers_answer_upload_status($(this), temp_id, true);
			} else {
				var answer_data = {
					parent_id: app_layers_answer_question['id'],
					parent_md5: app_layers_answer_question['md5'],
					number: event.data,
					title: '',
				}
				var temp_id = app_upload_open_photo_single('answer', false, false, false, answer_data, false, true);
				app_layers_answer_upload_status($(this), temp_id, true);
			}
		});
		Elem.find("[find=answer_image]")
			.on('click', i, function(event){
				event.stopPropagation();
				var answer = false;
				var answers = Bruno.storage.list('answer', 1, { number: event.data, }, 'question', app_layers_answer_question['id']);
				if(answers.length>0){
					answer = answers[0];
					previewer(answer['file_id']);
				}
			})
			.on('error', function(){
				$(this).addClass("app_layers_answer_answers_picture_image");
			})
			.on('load', function(){
				if(this.complete){
					$(this).addClass("app_layers_answer_answers_picture_image");
				} else {
					$(this).addClass("app_layers_answer_answers_picture_image");
				}
			});

		Elem.on('click', [app_layers_answer_question['id'], i], function(event){
			if(Bruno.storage.get('question', event.data[0], 'style')==2){ //Only only for picture style
				event.stopPropagation();
				var answer = false;
				var answers = Bruno.storage.list('answer', 1, { number: event.data[1], }, 'question', app_layers_answer_question['id']);
				if(answers.length>0){
					answer = answers[0];
					if(answer['file_id'] && Bruno.storage.get('file', answer['file_id'])){
						previewer(answer['file_id']);
					} else {
						var temp_id = app_upload_open_photo_single('answer', answer['id'], answer['md5'], 'file_id', null, false, true);
						app_layers_answer_upload_status($(this), temp_id, true);
					}
				} else {
					var answer_data = {
						parent_id: app_layers_answer_question['id'],
						parent_md5: app_layers_answer_question['md5'],
						number: event.data[1],
						title: '',
					}
					var temp_id = app_upload_open_photo_single('answer', false, false, false, answer_data, false, true);
					app_layers_answer_upload_status($(this), temp_id, true);
				}
			}
		});

		Elem.appendTo(answers_wrapper);
	}
	answers_wrapper.appendTo(position_wrapper);

	//Draw to DOM

	position_wrapper.find("[find=input_textarea]").textareaRows();

	for (var i = 1; i <= 6; i++) {
		$('#app_layers_answer_answers_'+i).find("[find=input_textarea]").textareaRows();
		if(typeof items[i] != "undefined"){
			app_layers_answer_answers_picture_image(i);
		}
		app_application_bruno.add('app_layers_answer_answers_'+i, "answer", function(){
			$('#app_layers_answer_answers_'+this.action_param).find("[find=input_textarea]").textareaRows();
			app_layers_answer_answers_picture_image(this.action_param);
			app_layers_answer_answers_correct(false, true);
		}, i);
	}

	app_layers_answer_answers_style(app_layers_answer_question['style']);
	
	app_layers_answer_answers_correct(app_layers_answer_question['number'], true);

	app_layers_answer_refresh(0);

	app_application_bruno.add("app_layers_answer", ["question_"+app_layers_answer_question['id']+"_answer_id", "answer"], function(){
		app_layers_answer_question = Bruno.storage.get('question', app_layers_answer_question['id']); //Refresh the values
		app_layers_answer_answers_correct(app_layers_answer_question['number'], true);
	});

	app_application_bruno.add("app_layers_answer", "answer_delete", function(){
		$('#app_layers_answer').find("[answer_id]").filter(function() {
			var id = parseInt($(this).attr("answer_id"), 10);
			var item = Bruno.storage.get('answer', id);
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
								app_layers_answer_refresh();
							},
						}
					);
			}
			return false;
		});
	});

	app_layers_answer_new_animation = false;
	app_application_bruno.add("app_layers_answer", "answer", function(){
		var items = Bruno.storage.list('answer', 6, null, 'question', app_layers_answer_question['id']);
		var item;
		var Elem;
		var position;
		var time_limit = Bruno.now.getTimeServer()-(10*1000);
		for(var i in items){
			item = items[i];
			if($('#models_answer_'+i).length==1){
				Elem = $('#models_answer_'+i);
				Elem.find("[find=title]").html( wrapper_to_html(item['title']) );
			}
		}
		app_layers_answer_new_animation = true;
		app_layers_answer_refresh();
	});

	app_application_bruno.prepare("answer", true);

	//Launch onboarding
	if(!Bruno.storage.onboarding_stop){
		var tuto = Bruno.storage.get('user', wrapper_localstorage.user_id, 'tuto');
		if(tuto){
			Bruno.storage.onboarding_stop = true;
			setTimeout(function(){
				app_layers_answer_grumble_1();
				app_layers_answer_grumble_2();
			}, 10);
		}
	}
};

var app_layers_answer_grumble_mask = function(){
	Elem = $('#-app_layers_answer_mask').clone();
	Elem.prop('id', 'app_layers_answer_mask');
	Elem.on('click', function(event){
		$(document.body).trigger('click.bubble');
		event.stopPropagation();
		return false;
	});
	$('#app_layers_answer').find("[find=wrapper]").append(Elem);
};

var app_layers_answer_grumble_mask_hide = function(){
	if($("#app_layers_answer_mask").length>0){
		$("#app_layers_answer_mask").recursiveRemove();
	}
};

var app_layers_answer_grumble_action = function(){
	var data = {};
	data.set = {};
	data.set.user = {};
	var item = Bruno.storage.get('user', wrapper_localstorage.user_id);
	data.set.user[item['id']] = {
		id: item['id'],
		md5: item['md5'],
		tuto: null,
	};
	
	if(storage_offline(data)){
		wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
	}
};

var app_layers_answer_grumble_1 = function(){
	//http://jamescryer.github.io/grumble.js/
	$('#app_layers_answer_settings').find("[find=eye]").grumble(
		{
			text: Bruno.Translation.get('app', 125, 'html'), //See how cool it will be!
			size: 150,
			sizeRange: [150],
			angle: 200,
			distance: 8,
			showAfter: 400,
			hideOnClick: true,
			type: 'alt-',
			useRelativePositioning: true,
			onBeginHide: function(){
				app_layers_answer_grumble_mask_hide();
				app_layers_answer_grumble_action();
			},
		}
	);
};

var app_layers_answer_grumble_2 = function(){
	var grumble_2_distance = 2 + $('#app_layers_answer_url').find("[find=ppt]").outerHeight();
	$('#app_layers_answer_url').find("[find=ppt_wrapper]").grumble(
		{
			text: Bruno.Translation.get('app', 127, 'html'), //Get your public presentation here
			size: 150,
			sizeRange: [150],
			angle: 340,
			distance: grumble_2_distance,
			showAfter: 600,
			hideOnClick: true,
			type: 'alt-',
			useRelativePositioning: true,
			onBeginHide: function(){
				app_layers_answer_grumble_mask_hide();
			},
		}
	);
};

app_application_bruno.add(function(){
	$('#app_layers_answer').find("[find=input_textarea]").each(function(){
		$(this).textareaRows();
	});
}, "resize");

var app_layers_answer_answers_correct = function(number, force_update){
	app_layers_answer_question = Bruno.storage.get('question', app_layers_answer_question['id']); //Refresh the values
	if(app_layers_answer_question['style']==1 || app_layers_answer_question['style']==2){ //Change only for answers and pictures
		if($('#app_layers_answer_answers_'+number).data('unavailable')){
			//Skip the click if the selection is unavailable
			return false;
		}
		if(typeof number == 'undefined' || number === false){ number = app_layers_answer_question['number']; }
		if(typeof force_update == 'undefined'){ force_update = false; }
		var answer = false;
		var answers = Bruno.storage.list('answer', 1, { number: number, }, 'question', app_layers_answer_question['id']);
		if(answers.length>0){
			answer = answers[0];
		}
		
		if(app_layers_answer_question['style']==2){ //Pictures
			if(!answer || !answer['file_id']){ //Exclude no file attach
				//Keep current selection
				number = app_layers_answer_question['number'];
				answer = false;
				answers = Bruno.storage.list('answer', 1, { number: app_layers_answer_question['number'], }, 'question', app_layers_answer_question['id']);
				if(answers.length>0){
					answer = answers[0];
				}
				if(!answer || !answer['file_id']){ //Exclude no file attach
					var items = Bruno.storage.list('answer', 6, { file_id: ['!=', null], }, 'question', app_layers_answer_question['id']);
					items = Bruno.storage.sort_items(items, 'number');
					for(var i in items){
						if(Bruno.storage.get('file', items[i]['file_id'])){
							answer = items[i];
							number = answer['number'];
							break;
						}
					}
				}
			}
		} else {
			var title = "";
			var file_id = false;
			if(!answer){
				var title = $("#app_layers_answer_answers_"+number).find("[find=input_textarea]").val();
				title = title.replace(/(\r\n|\n|\r)/gm, " ");
			} else {
				title = answer['title'];
				file_id = answer['file_id'];
			}
			if(title.length<=0 && !file_id){ //Exclude empty responses and no file attach
				//Keep current selection
				number = app_layers_answer_question['number'];
				answer = false;
				answers = Bruno.storage.list('answer', 1, { number: app_layers_answer_question['number'], }, 'question', app_layers_answer_question['id']);
				if(answers.length>0){
					answer = answers[0];
				}
				if(!answer || (answer['title']=="" && !answer['file_id'])){ //Exclude empty responses and no file attach
					var items = Bruno.storage.list('answer', 6, [{ title: ['!=', ''], }, { file_id: ['!=', null], }], 'question', app_layers_answer_question['id']);
					items = Bruno.storage.sort_items(items, 'number');
					for(var i in items){
						if(Bruno.storage.get('file', items[i]['file_id'])){
							answer = items[i];
							number = answer['number'];
							break;
						}
					}
				}
			}
		}

		if(answer){
			number = answer['number'];
		}

		if(number != app_layers_answer_question['number'] ){
			force_update = true;
			var data = {};
			data.set = {};
			data.set.question = {};
			data.set.question[app_layers_answer_question['id']] = {
				id: app_layers_answer_question['id'],
				md5: app_layers_answer_question['md5'],
				number: number,
			};
			if(storage_offline(data)){
				wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
			}
		}
		if(force_update){
			$('#app_layers_answer').find(".ok").removeClass('ok');
			$('#app_layers_answer_answers_'+number).find("[find=answer_select], [find=row]").addClass('ok');
		}
	}
	if(force_update){
		//Disable the empty answers
		$('#app_layers_answer').find("[find=answer]").each(function(){
			if(typeof $(this).data('number') == 'undefined'){
				return false;
			}
			var unavailable = true;
			var answer = false;
			var answers = Bruno.storage.list('answer', 1, { number: $(this).data('number'), }, 'question', app_layers_answer_question['id']);
			if(answers.length>0){
				answer = answers[0];
			}
			if(app_layers_answer_question['style']==2){ //Pictures
				if(answer && answer['file_id'] && Bruno.storage.get('file', answer['file_id'])){
					unavailable = false;
				}
			} else {
				if(answer && (answer['title']!='' || answer['file_id'] && Bruno.storage.get('file', answer['file_id']))){
					unavailable = false;
				} else if(!answer){
					var str = $(this).find("[find=input_textarea]").val();
					str = str.replace(/(\r\n|\n|\r)/gm, " ");
					if(str.length>0){
						unavailable = false;
					}
				}
			}
			if(unavailable){
				$(this).data('unavailable', true);
				$(this).find("[find=answer_tickbox], [find=answer_select]").addClass('unavailable');
			} else {
				$(this).data('unavailable', false);
				$(this).find("[find=answer_tickbox], [find=answer_select]").removeClass('unavailable');
			}
		});
	}
	return true;
}

var app_layers_answer_answers_style = function(style){
	//Change the display
	if(style==1){ //Questions
		$("#app_layers_answer").find("*").removeClass('pictures statistics survey').addClass('answers');
	} else if(style==2){ //Pictures
		$("#app_layers_answer").find("*").removeClass('answers statistics survey').addClass('pictures');
	} else if(style==3){ //Statistics
		$("#app_layers_answer").find("*").removeClass('answers pictures survey').addClass('statistics');
	} else if(style==4){ //Statistics
		$("#app_layers_answer").find("*").removeClass('answers pictures statistics').addClass('survey');
	}
};

var app_layers_answer_answers_question_image = function(){
	if(app_layers_answer_question){
		app_layers_answer_question = Bruno.storage.get('question', app_layers_answer_question['id']); //Refresh the values
		var file = Bruno.storage.get('file', app_layers_answer_question['file_id']);
		if(file){
			var thumbnail = Bruno.storage.getFile(file['id'], 'thumbnail');
			if(!thumbnail){
				thumbnail = Bruno.storage.getFile(file['id']);
			}
			$('#app_layers_answer_question').find("[find=add]").addClass('display_none');
			$('#app_layers_answer_question').find("[find=image]")
				.removeClass('display_none')
				.attr('src', thumbnail);
			return true;
		}
	}
	$('#app_layers_answer_question').find("[find=add]").removeClass('display_none');
	$('#app_layers_answer_question').find("[find=image]").addClass('display_none');
};

var app_layers_answer_answers_picture_image = function(number){
	var answer = false;
	var answers = Bruno.storage.list('answer', 1, { number: number, }, 'question', app_layers_answer_question['id']);
	if(answers.length>0){
		answer = answers[0];
	}
	if(answer){
		var file = Bruno.storage.get('file', answer['file_id']);
		if(file){
			var thumbnail = Bruno.storage.getFile(file['id'], 'thumbnail');
			if(!thumbnail){
				thumbnail = Bruno.storage.getFile(file['id']);
			}
			$('#app_layers_answer_answers_'+number).find("[find=answer_add]").addClass('display_none');
			$('#app_layers_answer_answers_'+number).find("[find=answer_image]")
				.removeClass('display_none')
				.attr('src', thumbnail);
			$('#app_layers_answer_answers_'+number).find("[find=row]")
				.css("background-image", "url('"+thumbnail+"')");
			return true;
		}
	}
	$('#app_layers_answer_answers_'+number).find("[find=answer_add]").removeClass('display_none');
	$('#app_layers_answer_answers_'+number).find("[find=answer_image]")
		.addClass('display_none')
		.attr('src', wrapper_neutral.src);
	$('#app_layers_answer_answers_'+number).find("[find=row]")
			.css("background-image", "url('"+wrapper_neutral.src+"')");
};

var app_layers_answer_upload_status_list = {};
var app_layers_answer_upload_status_progress = {};
var app_layers_answer_upload_status = function(Elem, temp_id, fill){
	if(typeof fill != 'boolean'){ fill = false; }
	Elem.find("[find=progress]").addClass('display_none');
	Elem.find("[find=progress]").html('');
	if(!navigator.userAgent.match(/webkit/i)){
		fill = false;
	}
	Elem.find("[find=progress]").html('0%');
	app_layers_answer_upload_status_list[temp_id] = app_application_garbage.add(temp_id);
	app_application_bruno.add(app_layers_answer_upload_status_list[temp_id], 'upload', function() {
		var Elem = this.action_param[0];
		var temp_id = this.action_param[1];
		var data = app_upload_files.getData(temp_id);
		var fill = this.action_param[2];
		if(typeof app_layers_answer_upload_status_progress[temp_id] != 'undefined' && (!data || Elem.length<=0)){
			delete app_layers_answer_upload_status_list[temp_id];
			delete app_layers_answer_upload_status_progress[temp_id];
			Elem
				.css('background', '')
				.removeClass('app_layers_answer_webkit_progress')
				.removeClass('app_layers_answer_upload_error');
			Elem.find("[find=progress]")
				.addClass('display_none')
				.html('');
			app_application_garbage.remove(this.id);
		} else if(data){
			var progress = Math.floor(data.bruno_progress);
			var error = false;
			if($.inArray(data.bruno_status, ['abort', 'failed', 'error', 'deleted']) >= 0){
				error = true;
				Elem.addClass('app_layers_answer_upload_error');
			} else {
				if(progress<100 && data.bruno_status!='done'){
					app_layers_answer_upload_status_progress[temp_id] = Math.floor(data.bruno_progress);
				}
				if(data.bruno_status=='done'){
					app_layers_answer_upload_status_progress[temp_id] = 100;
				}
				Elem.removeClass('app_layers_answer_upload_error');
			}
			progress = app_layers_answer_upload_status_progress[temp_id];
			if(fill){
				Elem.addClass('app_layers_answer_webkit_progress');
				if(error){
					Elem.css('background', '-webkit-linear-gradient(bottom, rgba(185, 124, 103, 0.8) '+(progress-5)+'%, rgba(255, 255, 255, 0.2) '+(progress+5)+'%');
				} else {
					Elem.css('background', '-webkit-linear-gradient(bottom, rgba(255, 255, 255, 0.8) '+(progress-5)+'%, rgba(255, 255, 255, 0.2) '+(progress+5)+'%');
				}
			} else {
				Elem.find("[find=progress]").removeClass('display_none');
			}
			if(error){
				Elem.find("[find=progress]").html('');
			} else {
				Elem.find("[find=progress]").html(progress+'%');
			}
		}
	}, [Elem, temp_id, fill]);
};
