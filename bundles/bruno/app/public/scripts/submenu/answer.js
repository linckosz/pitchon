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
			return Bruno.Translation.get('app', 2109, 'html', param); //Answer #[{number}]
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
		"title": Bruno.Translation.get('app', 2114, 'html'), //Get the Quiz
	},

	"url": {
		"style": "button",
		"class": "submenu_app_answer_get_presentation_methods",
		"title": "<span class='fa fa-link'>&nbsp;&nbsp;&nbsp;</span>"+Bruno.Translation.get('app', 2115, 'html'), //Copy the Presentation URL
		"value": function(Elem, subm){
			if(responsive.test("minMobileL")){
				return Bruno.storage.getPitchURL(subm.param);
			}
			return false;
		},
		"now": function(Elem, subm){
			Elem.prop("id", "app_submenu_answer_get_presentation_url_"+subm.id);
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

	"preview": {
		"style": "button",
		"title": "<span class='fa fa-eye'>&nbsp;&nbsp;&nbsp;</span>"+Bruno.Translation.get('app', 2116, 'html'), //Preview the Presentation
		"now": function(Elem, subm){
			Elem.prop("id", "app_submenu_answer_get_presentation_preview_"+subm.id);
		},
		"action": function(Elem, subm){
			var pitch_id = subm.param;
			var question_id = false;
			if(typeof app_layers_answer_question == "object" && app_layers_answer_question != null && typeof app_layers_answer_question['id'] != "undefined"){
				question_id = app_layers_answer_question['id'];
				if(question_id){
					pitch_id = Bruno.storage.get("question", question_id, "parent_id");
				}
			}
			showppt_launch(pitch_id, question_id);
		},
	},

	"powerpoint": {
		"style": "button",
		"class": "display_none submenu_app_answer_get_presentation_methods",
		"title": "<span class='fa fa-download'>&nbsp;&nbsp;&nbsp;</span>"+Bruno.Translation.get('app', 2118, 'html'), //Download PPT
		"value": function(Elem, subm){
			if(Bruno.storage.getPlan() < 2){
				Elem.find("[find=submenu_button_title]").addClass("submenu_button_paypal_title");
				if(responsive.test("maxMobile")){
					return "<span class='fa fa-lock'></span> "
				} else {
					return "<span class='fa fa-lock'></span> "+Bruno.Translation.get('app', 163, 'html'); //Subscribe to unlock
				}
			}
			return false;
		},
		"now": function(Elem, subm){
			if(Bruno.storage.get('pitch', subm.param)){
				Elem.removeClass('display_none');
			}
		},
		"action": function(Elem, subm){
			if(Bruno.storage.getPlan() < 2){
				submenu_Build_return('subscription', true, true);
			} else if(Bruno.storage.get('pitch', subm.param)){
				Bruno.storage.downloadPPT(subm.param);
				$(window).focus();
				var items = Bruno.storage.list('question', -1, null, 'pitch', subm.param);
				var time = 5 + 5*items.length;
				var msg = Bruno.Translation.get('app', 2119, 'js', { time: time }); //Operation in process The download will start in approximately [{time}] seconds.
				app_application_alert(msg);
			}
		},
	},

	"powerpoint_addin": {
		"style": "button",
		"class": "display_none submenu_app_answer_get_presentation_methods",
		"title": "<span class='fa fa-download'>&nbsp;&nbsp;&nbsp;</span>"+Bruno.Translation.get('app', 2120, 'html'), //Download PowerPoint Add-In
		"value": function(Elem, subm){
			if(Bruno.storage.getPlan() < 2){
				Elem.find("[find=submenu_button_title]").addClass("submenu_button_paypal_title");
				if(responsive.test("maxMobile")){
					return "<span class='fa fa-lock'></span> "
				} else {
					return "<span class='fa fa-lock'></span> "+Bruno.Translation.get('app', 163, 'html'); //Subscribe to unlock
				}
			}
			return false;
		},
		"now": function(Elem, subm){
			if(Bruno.storage.get('pitch', subm.param)){
				Elem.removeClass('display_none');
			}
		},
		"action": function(Elem, subm){
			if(Bruno.storage.getPlan() < 2){
				submenu_Build_return('subscription', true, true);
			} else if(Bruno.storage.get('pitch', subm.param)){
				device_download(app_application_add_in);
			}
		},
	},

	"jpeg": {
		"style": "button",
		"title": "<span class='fa fa-download'>&nbsp;&nbsp;&nbsp;</span>"+Bruno.Translation.get('app', 2121, 'html'), //Download JPEG
		"value": function(Elem, subm){
			if(Bruno.storage.getPlan() < 2){
				Elem.find("[find=submenu_button_title]").addClass("submenu_button_paypal_title");
				if(responsive.test("maxMobile")){
					return "<span class='fa fa-lock'></span> "
				} else {
					return "<span class='fa fa-lock'></span> "+Bruno.Translation.get('app', 163, 'html'); //Subscribe to unlock
				}
			}
			return false;
		},
		"now": function(Elem, subm){
			if(Bruno.storage.get('pitch', subm.param)){
				Elem.removeClass('display_none');
			}
		},
		"action": function(Elem, subm){
			var pitch_id = subm.param;
			var question_id = false;
			if(typeof app_layers_answer_question == "object" && app_layers_answer_question != null && typeof app_layers_answer_question['id'] != "undefined"){
				question_id = app_layers_answer_question['id'];
				if(question_id){
					pitch_id = Bruno.storage.get("question", question_id, "parent_id");
				}
			}
			if(Bruno.storage.getPlan() < 2){
				submenu_Build_return('subscription', true, true);
			} else if(true){ //We download the whole presentation, even if we are in answer page
				var index = 0;
				var pitch_enc = wrapper_integer_map(subm.param);
				var items = Bruno.storage.list('question', -1, null, 'pitch', subm.param);
				for(var i in items){
					index++;
				}
				device_download(location.protocol+'//screen.'+document.domainRoot+'/'+pitch_enc+'.zip', pitch_enc, pitch_enc+'.zip');
				index++;
				var time = 5 + 5*items.length;
				var msg = Bruno.Translation.get('app', 2119, 'js', { time: time }); //Operation in process The download will start in approximately [{time}] seconds.
				app_application_alert(msg);
			} else if(app_layers_menu=="answer" && app_layers_answer_question ){
				var pitch_enc = wrapper_integer_map(subm.param);
				var index = app_layers_answer_question["number"];
				device_download(location.protocol+'//screen.'+document.domainRoot+'/'+pitch_enc+'/'+index+'a.jpg?download=1', index+'a', index+'a.jpg');
				device_download(location.protocol+'//screen.'+document.domainRoot+'/'+pitch_enc+'/'+index+'b.jpg?download=1', index+'b', index+'b.jpg');
				var time = 10;
				var msg = Bruno.Translation.get('app', 2119, 'js', { time: time }); //Operation in process The download will start in approximately [{time}] seconds.
				app_application_alert(msg);
			} else if(Bruno.storage.get('pitch', subm.param)){
				var index = 0;
				var pitch_enc = wrapper_integer_map(subm.param);
				device_download(location.protocol+'//screen.'+document.domainRoot+'/'+pitch_enc+'/0.jpg?download=1', '0', '0.jpg');
				index++;
				var items = Bruno.storage.list('question', -1, null, 'pitch', subm.param);
				items = Bruno.storage.sort_items(items, 'id', 0, -1, true);
				items = Bruno.storage.sort_items(items, 'sort', 0, -1, false);
				for(var i in items){
					device_download(location.protocol+'//screen.'+document.domainRoot+'/'+pitch_enc+'/'+index+'a.jpg?download=1', index+'a', index+'a.jpg');
					device_download(location.protocol+'//screen.'+document.domainRoot+'/'+pitch_enc+'/'+index+'b.jpg?download=1', index+'b', index+'b.jpg');
					index++;
				}
				device_download(location.protocol+'//screen.'+document.domainRoot+'/'+pitch_enc+'/'+index+'a.jpg?download=1', index+'a', index+'a.jpg');
				index++;
				var time = 5 + 5*items.length;
				var msg = Bruno.Translation.get('app', 2119, 'js', { time: time }); //Operation in process The download will start in approximately [{time}] seconds.
				app_application_alert(msg);
			}
		},
	},

	"space1": {
		"style": "space",
		"title": "space",
		"value": 20,
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only || Bruno.storage.getPlan() < 2){
				return true;
			}
			return false;
		},
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
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only || Bruno.storage.getPlan() < 2){
				return true;
			}
			return false;
		},
	},

	"share": {
		"style": "next",
		"keep_title": true,
		"title": Bruno.Translation.get('app', 134, 'html'), //Share with
		"next": "app_pitch_share",
		"action_param": function(Elem, subm){
			return subm.param; //Pitch ID
		},
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only || Bruno.storage.getPlan() < 3){
				return true;
			}
			return false;
		},
		"now": function(Elem, subm){
			var value = this.value;
			Elem.prop('id', subm.id+"_share");
			app_application_bruno.add(subm.id+"_share", "pitch_"+subm.param, function(){
				var value = this.action_param[0]($("#"+this.id), this.action_param[1]);
				Elem.find("[find=submenu_next_value]").html(value);
			}, [value, subm.param]);
		},
		"value": function(Elem, subm){
			var number = 0;
			var users = Bruno.storage.get("pitch", subm.param, "_user");
			if(users){
				for(var i in users){
					number++;
				}
			}
			if(number>1){
				return Bruno.Translation.get('app', 143, 'html', {number: number}); //[{number}] users
			}
			return Bruno.Translation.get('app', 135, 'html'); //no one else
		},
	},

	"space2": {
		"style": "space",
		"title": "space",
		"value": 20,
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only || Bruno.storage.getPlan() < 3){
				return true;
			}
			return false;
		},
	},

	"brand_name": {
		"style": "input_text_flat",
		"title": Bruno.Translation.get('app', 98, 'html'), //Brand name or Link
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only || Bruno.storage.getPlan() < 3){
				return true;
			}
			return false;
		},
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
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only || Bruno.storage.getPlan() < 3){
				return true;
			}
			return false;
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
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only || Bruno.storage.getPlan() < 3){
				return true;
			}
			return false;
		},
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
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only || Bruno.storage.getPlan() < 3){
				return true;
			}
			return false;
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
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only){
				return true;
			}
			return false;
		},
		"now": function(Elem, subm){
			var pitch_c_by = Bruno.storage.get('pitch', subm.param, 'c_by');
			if(pitch_c_by != wrapper_localstorage.user_id){
				Elem.off("click").addClass("display_none");
			}
		},
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
		"skip": function(Elem, subm){
			//[plan]
			if(wrapper_read_only){
				return true;
			}
			return false;
		},
		"now": function(Elem, subm){
			var pitch_c_by = Bruno.storage.get('pitch', subm.param, 'c_by');
			if(pitch_c_by != wrapper_localstorage.user_id){
				Elem.off("click").addClass("display_none");
			}
		},
	},

	"space4": {
		"style": "space",
		"title": "space",
		"class": "submenu_space_noborder",
		"value": 20,
	},

	"post_action": {
		"style": "postAction",
		"action": function(Elem, subm){
			//Launch onboarding
			if(!Bruno.storage.onboarding_stop){
				var tuto = JSON.parse(Bruno.storage.get('user', wrapper_localstorage.user_id, 'tuto'));
				if(typeof tuto == "object" && typeof tuto[2] != "undefined" && tuto[2]){
					setTimeout(function(subm_id){
						app_submenu_answer_grumble_1(subm_id);
					}, 600, subm.id);
				}
			}
		},
	},

};

var app_submenu_answer_grumble_1 = function(subm_id){
	if($("#app_submenu_answer_get_presentation_url_"+subm_id).length > 0){
		$("#app_submenu_answer_get_presentation_url_"+subm_id).grumble(
			{
				text: Bruno.Translation.get('app', 154, 'html'), //Copy the URL for a public presentation
				size: 150,
				sizeRange: [150],
				angle: 160,
				distance: -20,
				showAfter: 400,
				hideOnClick: true,
				type: 'alt-',
				useRelativePositioning: false,
				onShow: function(){
					Bruno.storage.onboarding_opened = true;
					app_application_mask_show();
				},
				onBeginHide: function(){
					Bruno.storage.onboarding_opened = false;
					app_submenu_answer_grumble_2(subm_id);
				},
			}
		);
	} else {
		app_submenu_answer_grumble_2(subm_id);
	}
};

var app_submenu_answer_grumble_2 = function(subm_id){
	if($("#app_submenu_answer_get_presentation_preview_"+subm_id).length > 0){
		$("#app_submenu_answer_get_presentation_preview_"+subm_id).grumble(
			{
				text: Bruno.Translation.get('app', 130, 'html'), //Pr preview it first
				size: 150,
				sizeRange: [150],
				angle: 130,
				distance: -20,
				showAfter: 400,
				hideOnClick: true,
				type: 'alt-',
				useRelativePositioning: false,
				onShow: function(){
					Bruno.storage.onboarding_opened = true;
					app_application_mask_show();
				},
				onBeginHide: function(){
					Bruno.storage.onboarding_opened = false;
					app_application_mask_hide();
					Bruno.storage.clearTuto(2);
				},
			}
		);
	} else {
		Bruno.storage.onboarding_opened = false;
		app_application_mask_hide();
		Bruno.storage.clearTuto(2);
	}
};
