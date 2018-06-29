submenu_list['app_pitch_new'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 2111, 'html'), //Pitch
	},
	"pitch_title": {
		"style": "input_text",
		"title": Bruno.Translation.get('app', 28, 'html'), //Title
		"name": "title",
		"value": function(){
			var items = Bruno.storage.list('pitch');
			var param = {
				number: items.length+1,
			};
			return Bruno.Translation.get('app', 2103, 'html', param); //Pitch #[{number]}
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
			data.set.pitch = {};
			var md5id = Bruno.storage.getMD5("pitch");
			data.set.pitch[md5id] = {
				md5: md5id,
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
				app_application_bruno.prepare("pitch", true);
				var item = Bruno.storage.findMD5(md5id, "pitch");
				if(item){
					setTimeout(function(parent_id){
						app_content_menu.selection("question", parent_id);
					}, 400, item['id']);
				}
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

submenu_list['app_pitch_edit'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 2111, 'html'), //Pitch
	},
	"pitch_title": {
		"style": "input_text",
		"title": Bruno.Translation.get('app', 28, 'html'), //Title
		"name": "title",
		"value": function(Elem, subm){
			return Bruno.storage.get('pitch', subm.param, 'title');
		},
		"class": "submenu_input_text needsclick",
		"now": function(Elem, subm){
			Elem.on('keypress', subm, function(event) {
				event.stopPropagation(); 
				if (event.which == 13) {
					event.data.Wrapper().find("[form=submit]").click();
				}
			});
		},

	},
	"deletion": {
		"style": "button_delete",
		"title": Bruno.Translation.get('app', 22, 'html'), //Delete
		"name": "deletion",
		"class": "submenu_bottom_deletion",
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
	"edit": {
		"style": "bottom_button",
		"title": Bruno.Translation.get('app', 43, 'html'), //Edit
		"action": function(Elem, subm){
			var data = {};
			data.set = {};
			data.set.pitch = {};
			var item = Bruno.storage.get('pitch', subm.param);
			data.set.pitch[item['id']] = {
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
				app_application_bruno.prepare("pitch", true);
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


var submenu_app_pitch_share_timeout = null;
var submenu_app_pitch_share_current_email = false;

submenu_list['app_pitch_share'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 138, 'html'), //Share your Quiz
	},
	"search": {
		"style": "pitch_search",
		"title": Bruno.Translation.get('app', 139, 'html'), //search by email address
		"action": function(Elem, subm, now){
			if(typeof now == "undefined"){ now = false; }
			var email = Elem.find("[find=input]").val();
			email = email.toLowerCase();
			if(email == submenu_app_pitch_share_current_email){
				return false;
			}
			Elem.find("[find=add]").off("click").addClass("disabled");
			base_hideProgress(Elem.find("[find=add]"));
			submenu_app_pitch_share_current_email = email;
			Elem.find("[find=info]").html("");
			clearTimeout(submenu_app_pitch_share_timeout);
			if(base_input_field.email.valid(email)){
				var timer = 1000;
				if(now){
					timer = 0;
				}
				base_showProgress(Elem.find("[find=add]"));
				submenu_app_pitch_share_timeout = setTimeout(function(email, Elem){
					var Elem_bis = Elem;
					wrapper_sendAction(
						{email: email, },
						'post',
						'api/user/search',
						function(msg, err, status, data){
							Elem_bis.find("[find=add]").addClass("disabled");
							if(data && typeof data.id != "undefined"){
								if(data.id == wrapper_localstorage.user_id){
									Elem_bis.find("[find=info]").html(Bruno.Translation.get('app', 142, 'html')); //This is your email address.
								} else {
									var users = Bruno.storage.get("pitch", Elem_bis.find("[find=add]").data("pitch_id"), "_user");
									if(users[data.id]){
										Elem_bis.find("[find=info]").html(Bruno.Translation.get('app', 141, 'html')); //You already shared it with this person.
									} else {
										Elem_bis.find("[find=add]")
											.removeClass("disabled")
											.on("click", [Elem_bis, data], function(event){
												event.stopPropagation();
												event.data[0].find("[find=input]")
													.val("")
													.trigger("change");
												var pitch = Bruno.storage.get("pitch", Elem_bis.find("[find=add]").data("pitch_id"));
												submenu_app_pitch_share_add_user(Elem_bis.find("[find=add]").data("pitch_id"), event.data[1]["id"], true);
												submenu_app_pitch_share_add_tab(event.data[1], $(this).data("pitch_id"), $(this).data("subm_id"));
											});
									}
								}
							} else {
								Elem_bis.find("[find=info]").html(Bruno.Translation.get('app', 140, 'html')); //This person does not have yet an account.
							}
							base_hideProgress(Elem_bis.find("[find=add]"));
						},
						null,
						null,
						function(){
							base_hideProgress(Elem_bis.find("[find=add]"));
						}
					);
				}, timer, email, Elem);
			}
		},
		"now": function(Elem, subm){
			Elem.find("[find=add]")
				.data("subm_id", subm.id)
				.data("pitch_id", subm.param);
		},
	},
	"pre_action": {
		"style": "preAction",
		"action": function(Elem, subm){
			//Clear the list to rebuild it then
			for(var i in submenu_list['app_pitch_share']){
				if(
					   i != "_title"
					&& i != "search"
					&& i != "pre_action"
					&& i != "post_action"
				){
					delete submenu_list['app_pitch_share'][i];
				}
			}
		},
	},
	"post_action": {
		"style": "postAction",
		"action": function(Elem, subm){
			var users = Bruno.storage.get("pitch", subm.param, "_user");
			if(users){
				for(var i in users){
					submenu_app_pitch_share_add_tab(users[i], subm.param, subm.id);
				}
			}
		},
	},
};

Submenu.prototype.style['pitch_search'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_pitch_search').clone();
	Elem.prop("id", that.id+"_submenu_pitch_search");
	Elem.find("[find=input]").attr('placeholder', attribute.title);
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("value" in attribute) {
		if(typeof attribute.value == "function"){
			var value = attribute.value(Elem, that);
			Elem.find("[find=input]").val(value);
		} else {
			Elem.find("[find=input]").val(attribute.value);
		}
	}

	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.find("[find=input]").on({
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
					attribute.action(Elem, that, true); //Press Enter
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



var submenu_app_pitch_share_add_user = function(pitch_id, user_id, access){
	var pitch = Bruno.storage.get("pitch", pitch_id);
	if(pitch){
		var data = {};
		data.set = {};
		data.set.pitch = {};
		data.set.pitch[pitch['id']] = {
			id: pitch['id'],
			md5: pitch['md5'],
			"user>access": {},
		};
		data.set.pitch[pitch['id']]["user>access"][user_id] = access;
		if(storage_offline(data)){
			wrapper_sendAction(data, 'post', 'api/data/set', storage_cb_success, storage_cb_error, storage_cb_begin, storage_cb_complete);
		}
	}
};


//Add individual user tab line
var submenu_app_pitch_share_add_tab = function(user, pitch_id, subm_id){
	if($("#"+subm_id+"_submenu_pitch_user_"+user["id"]).length>=1){
		$("#"+subm_id+"_submenu_pitch_user_"+user["id"]).data("check", true).removeClass("uncheck");
		$("#"+subm_id+"_submenu_pitch_user_"+user["id"]).find("[find=check]").removeClass("visibility_hidden");
		return false;
	}
	var Elem = $('#-submenu_pitch_user').clone();
	Elem.prop("id", subm_id+"_submenu_pitch_user_"+user["id"]);
	Elem.find("[find=username]").html(user["username"]);
	Elem.find("[find=email]").html(user["email"]);

	var pitch_c_by = Bruno.storage.get("pitch", pitch_id, "c_by");
	if(user["id"] == pitch_c_by){ //Lock because it's the creator
		Elem.addClass("submenu_pitch_user_read");
		Elem.find("[find=check]")
			.removeClass("fa-check")
			.addClass("fa-lock");
	} else {
		Elem.find("[find=check]").addClass("check");
		Elem
			.data("check", true)
			.on("click", [pitch_id, user["id"]], function(event){
				event.stopPropagation();
				var pitch_id = event.data[0];
				var user_id = event.data[1];
				var check = $(this).data("check");
				if(check){
					var accept = false;
					if(user["id"] == wrapper_localstorage.user_id){
						if(confirm(Bruno.Translation.get('app', 144, 'js'))){ //Are you sure you want to block your access to this item? If you confirm, you won't be able to see it then.
							accept = true;
						}
					} else {
						accept = true;
					}
					if(accept){
						$(this).data("check", false).addClass("uncheck");
						$(this).find("[find=check]").addClass("visibility_hidden");
						submenu_app_pitch_share_add_user(pitch_id, user_id, false);
					}
				} else {
					$(this).data("check", true).removeClass("uncheck");
					$(this).find("[find=check]").removeClass("visibility_hidden");
					submenu_app_pitch_share_add_user(pitch_id, user_id, true);
				}
			});
	}

	$("#"+subm_id+"_submenu_pitch_search").after(Elem);
	return Elem;

};
