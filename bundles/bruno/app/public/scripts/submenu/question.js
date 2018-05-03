submenu_list['app_question_new'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 2112, 'html'), //Question
	},
	"question_title": {
		"style": "input_text",
		"title": Bruno.Translation.get('app', 28, 'html'), //Title
		"name": "title",
		"value": function(Elem, subm){
			var items = Bruno.storage.list('question', -1, null, 'pitch', subm.param);
			var param = {
				number: items.length+1,
			};
			return Bruno.Translation.get('app', 2106, 'html', param); //Question #[{number]}
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
			data.set.question = {};
			var md5id = Bruno.storage.getMD5("question");
			var parent = Bruno.storage.get("pitch", subm.param);
			data.set.question[md5id] = {
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
				app_application_bruno.prepare("question", true);
				var item = Bruno.storage.findMD5(md5id, "question");
				if(item){
					setTimeout(function(parent_id){
						app_content_menu.selection("answer", parent_id);
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

submenu_list['app_question_edit'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 2112, 'html'), //Question
	},
	"question_title": {
		"style": "input_text",
		"title": Bruno.Translation.get('app', 28, 'html'), //Title
		"name": "title",
		"value": function(Elem, subm){
			return Bruno.storage.get('question', subm.param, 'title');
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
	"deletion": {
		"style": "button_delete",
		"title": Bruno.Translation.get('app', 22, 'html'), //Delete
		"name": "deletion",
		"class": "submenu_bottom_deletion",
		"action": function(Elem, subm){
			if(confirm(Bruno.Translation.get('app', 26, 'js'))){ //Are you sure you want to delete this item?
				var data = {};
				data.delete = {};
				data.delete.question = {};
				var item = Bruno.storage.get('question', subm.param);
				data.delete.question[item['id']] = {
					id: item['id'],
					md5: item['md5'],
				};
				var Elem_bis = Elem;
				var subm_bis = subm;
				var action_cb_success = function(msg, error, status, extra){
					storage_cb_success(msg, error, status, extra);
					app_content_menu.selection("question", app_content_top_title.pitch);
				}
				var action_cb_complete = function(){
					storage_cb_complete();
					base_hideProgress(Elem_bis);
					app_application_bruno.prepare("question", true);
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
			data.set.question = {};
			var item = Bruno.storage.get('question', subm.param);
			data.set.question[item['id']] = {
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
				app_application_bruno.prepare("question", true);
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

