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
