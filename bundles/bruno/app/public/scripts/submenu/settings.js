var submenu_settings_profile_picture_timeout = null;

submenu_list['settings'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 2, 'html'), //Settings
	},
	"profile_picture": {
		"style": "profile_photo",
		"title": "",
		"value": function(Elem, subm){
			return Bruno.storage.get('user', wrapper_localstorage.user_id, 'username');
		},
		"now": function(Elem, subm){
			var Elem_pic = Elem.find("[find=submenu_profile_upload_picture]");
			Elem.find("[find=email]").html(Bruno.storage.get('user', wrapper_localstorage.user_id, 'email'));
			Elem_pic
				.removeClass("no_shadow")
				.prop('id', subm.id+'_profile_picture')
				.on('click', subm.param, function(event){
					var item = Bruno.storage.get("user", wrapper_localstorage.user_id);
					app_upload_open_photo_single('user', item['id'], item['md5'], false, null, false, true);	
				});
			app_application_bruno.add(subm.id+'_profile_picture', "file", function(){
				var src = Bruno.storage.getProfile(wrapper_localstorage.user_id);
				if(!src){
					src = app_application_icon_single_user.src;
				}
				$("#"+this.id).css('background-image','url("'+src+'")');
			});
		},
		"action": function(Elem, subm, now){
			if(typeof now == "undefined"){ now = false; }
			var item = Bruno.storage.get('user', wrapper_localstorage.user_id);
			var value = Elem.find("[find=submenu_value]").val();
			//We don't save if the value is the same
			if(value == item['username']){
				return true;
			}
			var data = {};
			data.set = {};
			data.set.user = {};
			data.set.user[item['id']] = {
				id: item['id'],
				md5: item['md5'],
				username: value,
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
	//Change the language
	"language": {
		"style": "next",
		"title": Bruno.Translation.get('app', 1, 'html'), //Language
		"next": "language",
		"value": submenu_language_full,
	},
	"reward": {
		"style": "next",
		"keep_title": true,
		"title": Bruno.Translation.get('app', 145, 'html'), //Rewards
		"next": "reward",
		"value": function(){
			var amount = 0;
			var records = Bruno.storage.get("user", wrapper_localstorage.user_id, "_bank");
			for(var i in records){
				//Only display the amount not paid yet
				if(!records[i]["used_at"] && $.isNumeric(records[i]["eur"])){
					amount +=  parseInt(records[i]["eur"], 10);
				}
			}
			return amount+" €";
		},
	},
	"support": {
		"style": "button",
		"title": Bruno.Translation.get('app', 89, 'html'), //Contact Us
		"value": function(){
			return wrapper_link['support'];
		},
		"action": function(Elem, subm){
			var link = "mailto:"
				+ wrapper_link['support']
				+ "?subject=" + escape("[ID:"+wrapper_localstorage.user_id+"] "+wrapper_main_title)
			;
			window.location.href = link;
		},
	},
	"signout": {
		"style": "button",
		"title": Bruno.Translation.get('app', 38, 'html'), //Sign out
		"action": function(){
			wrapper_sendAction('', 'post', 'api/user/signout', null, null, wrapper_signout_cb_begin, wrapper_signout_cb_complete);
		},
	},
};

Submenu.prototype.style['profile_photo'] = function(submenu_wrapper, subm) {
	var user_id = wrapper_localstorage.user_id;
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_personal_profile').clone();
	var range = 'user_'+user_id;
	Elem.prop("id", '');
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("value" in attribute) {
		if(typeof attribute.value == "function"){
			var value = attribute.value(Elem, that);
			Elem.find("[find=submenu_value]").val(value);
		} else {
			Elem.find("[find=submenu_value]").val(attribute.value);
		}
	}
	var Elem_pic = Elem.find("[find=submenu_profile_upload_picture]");
	Elem_pic
		.attr("preview", "0")
		.prop("id", that.id+"_submenu_profile_upload_picture")
		.css('background-image','url("'+app_application_icon_single_user.src+'")');
	var src = Bruno.storage.getProfile(user_id);
	if(src){
		Elem_pic.css('background-image','url("'+src+'")');
	}
	Elem_pic.addClass("no_shadow");

	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.find("[find=submenu_value]").on({
			focusin: function(){ attribute.action(Elem, that) },
			focusout: function(){ attribute.action(Elem, that, true) },
			change: function(){ attribute.action(Elem, that) },
			copy: function(){ attribute.action(Elem, that) },
			paste: function(){ attribute.action(Elem, that, true) },
			cut: function(){ attribute.action(Elem, that, true) },
			keyup: function(event) {
				if (event.which != 13) {
					attribute.action(Elem, that);
				} else {
					attribute.action(Elem, that, true);
				}
			},
		});
	}

	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return true;
};
