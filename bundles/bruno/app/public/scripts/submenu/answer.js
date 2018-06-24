var submenu_advertising_link_timeout = null;
var submenu_brand_name_timeout = null;

submenu_list['app_answer_new'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 2113, 'html'), //Answer
	},
	"answertitle": {
		"style": "input_text",
		"title": Bruno.Translation.get('app', 28, 'html'), //Title
		"name": "title",
		"value": function(Elem, subm){
			var items = Bruno.storage.list('answer', -1, null, 'question', subm.param);
			var param = {
				number: items.length+1,
			};
			return Bruno.Translation.get('app', 2109, 'html', param); //Answer #[{number]}
		},
		"class": "submenu_input_text",
		"now": function(Elem, subm){
			Elem.on('keypress', subm, function(event) {
				event.stopPropagation(); 
				if (event.which == 13) {
					event.data.Wrapper().find("[form=submit]").click();
				}
			});
		},

	},
	"create": {
		"style": "bottom_button",
		"title": Bruno.Translation.get('app', 41, 'html'), //Create
		"action": function(Elem, subm){
			var data = {};
			data.set = {};
			data.set.answer = {};
			var md5id = Bruno.storage.getMD5("answer");
			var parent = Bruno.storage.get("question", subm.param);
			data.set.answer[md5id] = {
				md5: md5id,
				parent_id: parent['id'],
				parent_md5: parent['md5'],
				title: subm.Wrapper().find("[name=title]").val(),
			};
			var Elem_bis = Elem;
			var subm_bis = subm;
			var action_cb_success = function(msg, error, status, extra){
				storage_cb_success(msg, error, status, extra);
				if(!error){
					subm_bis.Hide();
				}
			}
			var action_cb_complete = function(){
				storage_cb_complete();
				base_hideProgress(Elem_bis);
				app_application_bruno.prepare("answer", true);
			};
			if(storage_offline(data)){
				base_showProgress(Elem);
				wrapper_sendAction(data, 'post', 'api/data/set', action_cb_success, storage_cb_error, storage_cb_begin, action_cb_complete);
			}
		},
		"now": function(Elem, subm){
			Elem.find("[find=submenu_bottom_button]").attr('form', 'submit');
		},
	},
	"cancel": {
		"style": "bottom_button",
		"title": Bruno.Translation.get('app', 7, 'html'), //Cancel
		"action": function(Elem, subm){
			subm.Hide();
		},
	},
};

submenu_list['app_answer_edit'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 2113, 'html'), //Answer
	},
	"answer_title": {
		"style": "input_text",
		"title": Bruno.Translation.get('app', 28, 'html'), //Title
		"name": "title",
		"value": function(Elem, subm){
			return Bruno.storage.get('answer', subm.param, 'title');
		},
		"class": "submenu_input_text",
		"now": function(Elem, subm){
			Elem.on('keypress', subm, function(event) {
				event.stopPropagation(); 
				if (event.which == 13) {
					event.data.Wrapper().find("[form=submit]").click();
				}
			});
		},

	},
	"edit": {
		"style": "bottom_button",
		"title": Bruno.Translation.get('app', 43, 'html'), //Edit
		"action": function(Elem, subm){
			var data = {};
			data.set = {};
			data.set.answer = {};
			var item = Bruno.storage.get('answer', subm.param);
			data.set.answer[item['id']] = {
				id: item['id'],
				md5: item['md5'],
				title: subm.Wrapper().find("[name=title]").val(),
			};
			var Elem_bis = Elem;
			var subm_bis = subm;
			var action_cb_success = function(msg, error, status, extra){
				storage_cb_success(msg, error, status, extra);
			}
			var action_cb_complete = function(){
				storage_cb_complete();
				base_hideProgress(Elem_bis);
				app_application_bruno.prepare("answer", true);
			};
			if(storage_offline(data)){
				base_showProgress(Elem);
				wrapper_sendAction(data, 'post', 'api/data/set', action_cb_success, storage_cb_error, storage_cb_begin, action_cb_complete);
			}
			subm_bis.Hide();
		},
		"now": function(Elem, subm){
			Elem.find("[find=submenu_bottom_button]").attr('form', 'submit');
		},
	},
	"cancel": {
		"style": "bottom_button",
		"title": Bruno.Translation.get('app', 7, 'html'), //Cancel
		"action": function(Elem, subm){
			subm.Hide();
		},
	},
};


submenu_list['app_answer_get_presentation'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 2114, 'html'), //Get the Presentation
	},

	"preview": {
		"style": "button",
		"title": "<span class='fa fa-eye'>&nbsp;&nbsp;&nbsp;</span>"+Bruno.Translation.get('app', 2116, 'html'), //Preview the Presentation
		"action": function(Elem, subm){
			var question_id = app_layers_answer_question['id'];
			var pitch_id = Bruno.storage.get("question", question_id, "parent_id");
			showppt_launch(pitch_id, question_id);
		},
	},

	"url": {
		"style": "button",
		"class": "submenu_app_answer_get_presentation_methods",
		"title": "<span class='fa fa-link'>&nbsp;&nbsp;&nbsp;</span>"+Bruno.Translation.get('app', 2115, 'html'), //Copy presentation URL
		"value": function(Elem, subm){
			if(responsive.test("minMobileL")){
				return Bruno.storage.getPitchURL(subm.param);
			}
			return false;
		},
		"now": function(Elem, subm){
			Elem.attr('data-clipboard-text', Bruno.storage.getPitchURL(subm.param));
			if(app_layers_answer_clipboard_pitch && destroy in app_layers_answer_clipboard_pitch){
				app_layers_answer_clipboard_pitch.destroy();
			}
			var app_layers_answer_clipboard_pitch = new Clipboard(Elem[0]);
			app_layers_answer_clipboard_pitch.on('success', function(event) {
				var msg = Bruno.Translation.get('app', 70, 'html')+"\n"+event.text; //URL copied to the clipboard
				base_show_error(msg, false); 
				event.clearSelection();
			});
			app_layers_answer_clipboard_pitch.on('error', function(event) {
				var msg = Bruno.Translation.get('app', 71, 'html'); //Your system does not allow to copy to the clipboard
				base_show_error(msg, true);
				event.clearSelection();
			});
		},
	},

	"space1": {
		"style": "space",
		"title": "space",
		"value": 20,
	},

	"statistics": {
		"style": "next",
		"keep_title": true,
		"title": Bruno.Translation.get('app', 102, 'html'), //Statistics
		"next": "statistics_quiz_open",
		"class": "",
		"action_param": function(Elem, subm){
			return subm.param; //Pitch ID
		},
	},

	"space2": {
		"style": "space",
		"title": "space",
		"value": 20,
	},

	"brand_name": {
		"style": "input_text_flat",
		"title": Bruno.Translation.get('app', 98, 'html'), //Brand name or Link
		"now": function(Elem, subm){
			Elem.find("[find=submenu_value]").addClass("no_focus");
		},
		"value": function(Elem, subm){
			var value = Bruno.storage.get("pitch", subm.param, "brand");
			if(typeof value == "string" && value.length > 0){
				return value;
			}
			return document.domainRoot;
		},
		"action": function(Elem, subm, now){
			if(typeof now == "undefined"){ now = false; }
			var item = Bruno.storage.get('pitch', subm.param);
			var value = Elem.find("[find=submenu_value]").val();
			//We don't save if the vaue is the same
			if(value == item['brand']){
				return true;
			}
			var data = {};
			data.set = {};
			data.set.pitch = {};
			data.set.pitch[item['id']] = {
				id: item['id'],
				md5: item['md5'],
				brand: value,
			};
			var timer = 1000;
			if(now){
				timer = 0;
			}
			if(storage_offline(data)){
				clearTimeout(submenu_settings_profile_picture_timeout);
				submenu_settings_profile_picture_timeout = setTimeout(function(data_bis){
					wrapper_sendAction(data_bis, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
				}, timer, data);
			}
		},
	},

	"brand_logo": {
		"style": "picture",
		"title": Bruno.Translation.get('app', 99, 'html'), //Brand logo
		"value": function(Elem, subm){
			var thumbnail = app_application_logo_bruno.src;
			var brand_pic = Bruno.storage.get("pitch", subm.param, "brand_pic");
			if(typeof brand_pic == "number"){
				var pic = Bruno.storage.getFile(brand_pic, "thumbnail");
				if(pic){
					thumbnail = pic;
				}
			}
			Elem.find("[find=submenu_value]").css("background-image", "url('"+thumbnail+"')");
			return wrapper_neutral.src;
		},
		"now": function(Elem, subm){
			var item = Bruno.storage.get("pitch", subm.param);
			Elem.find("[find=submenu_value]")
				.prop('id', subm.id+'_brand_pic')
				.addClass('submenu_app_answer_brand_logo')
				.on('click', subm.param, function(event){
					var item = Bruno.storage.get("pitch", event.data);
					app_upload_open_photo_single('pitch', item['id'], item['md5'], 'brand_pic', null, false, true);	
				});
			app_application_bruno.add(subm.id+'_brand_pic', "pitch_"+subm.param+"_brand_pic", function(){
				var brand_pic = Bruno.storage.get("pitch", this.action_param, 'brand_pic');
				if(brand_pic){
					var thumbnail = Bruno.storage.getFile(brand_pic, "thumbnail");
					if(thumbnail){
						//$("#"+this.id).attr('src', thumbnail);
						$("#"+this.id).css("background-image", "url('"+thumbnail+"')");
					}
				}
			}, subm.param);
		},
	},

	"advertising_link": {
		"style": "input_text_flat",
		"title": Bruno.Translation.get('app', 100, 'html'), //Advertising name or link
		"now": function(Elem, subm){
			Elem.find("[find=submenu_value]").addClass("no_focus");
		},
		"value": function(Elem, subm){
			var value = Bruno.storage.get("pitch", subm.param, "ad");
			if(typeof value == "string" && value.length > 0){
				return value;
			}
			return document.domainRoot;
		},
		"action": function(Elem, subm, now){
			if(typeof now == "undefined"){ now = false; }
			var item = Bruno.storage.get('pitch', subm.param);
			var value = Elem.find("[find=submenu_value]").val();
			//We don't save if the vaue is the same
			if(value == item['ad']){
				return true;
			}
			var data = {};
			data.set = {};
			data.set.pitch = {};
			data.set.pitch[item['id']] = {
				id: item['id'],
				md5: item['md5'],
				ad: value,
			};
			var timer = 1000;
			if(now){
				timer = 0;
			}
			if(storage_offline(data)){
				clearTimeout(submenu_settings_profile_picture_timeout);
				submenu_settings_profile_picture_timeout = setTimeout(function(data_bis){
					wrapper_sendAction(data_bis, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
				}, timer, data);
			}
		},
	},

	"advertising_picture": {
		"style": "picture",
		"title": Bruno.Translation.get('app', 101, 'html'), //Advertising picture
		"value": function(Elem, subm){
			var value = Bruno.storage.get("pitch", subm.param, "ad_pic");
			if(typeof value == "number"){
				var thumbnail = Bruno.storage.getFile(value, "thumbnail");
				if(thumbnail){
					return thumbnail;
				}
			}
			return app_application_logo_bruno.src;
		},
		"now": function(Elem, subm){
			var item = Bruno.storage.get("pitch", subm.param);
			Elem.find("[find=submenu_value]")
				.prop('id', subm.id+'_ad_pic')
				.on('click', subm.param, function(event){
					var item = Bruno.storage.get("pitch", event.data);
					app_upload_open_photo_single('pitch', item['id'], item['md5'], 'ad_pic', null, false, true);	
				});
			app_application_bruno.add(subm.id+'_ad_pic', "pitch_"+subm.param+"_ad_pic", function(){
				var ad_pic = Bruno.storage.get("pitch", this.action_param, 'ad_pic');
				if(ad_pic){
					var thumbnail = Bruno.storage.getFile(ad_pic, "thumbnail");
					if(thumbnail){
						$("#"+this.id).attr('src', thumbnail);
					}
				}
			}, subm.param);
		},
	},

	"space3": {
		"style": "space",
		"title": "space",
		"value": 40,
	},

	"deletion": {
		"style": "button",
		"title": "<span class='fa fa-trash-o'>&nbsp;&nbsp;&nbsp;</span> "+Bruno.Translation.get('app', 22, 'html'), //Delete
		"name": "deletion",
		"class": "submenu_button_deletion",
		"action": function(Elem, subm){
			if(confirm(Bruno.Translation.get('app', 26, 'js'))){ //Are you sure you want to delete this item?
				var data = {};
				data.delete = {};
				data.delete.pitch = {};
				var item = Bruno.storage.get('pitch', subm.param);
				data.delete.pitch[item['id']] = {
					id: item['id'],
					md5: item['md5'],
				};
				var Elem_bis = Elem;
				var subm_bis = subm;
				var action_cb_success = function(msg, error, status, extra){
					storage_cb_success(msg, error, status, extra);
					app_content_menu.selection("pitch");
				}
				var action_cb_complete = function(){
					storage_cb_complete();
					base_hideProgress(Elem_bis);
					app_application_bruno.prepare("pitch", true);
				};
				if(storage_offline(data)){
					base_showProgress(Elem);
					wrapper_sendAction(data, 'post', 'api/data/set', action_cb_success, storage_cb_error, storage_cb_begin, action_cb_complete);
				}
				subm_bis.Hide();
			}
		},
	},

	"space4": {
		"style": "space",
		"title": "space",
		"class": "submenu_space_noborder",
		"value": 20,
	},

};
