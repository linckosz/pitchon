submenu_list['statistics'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 102, 'html'), //Statistics
	},

	"loading": {
		"style": "info",
		"title": "<span class='fa fa-spinner fa-spin'></span>",
		"class": "submenu_app_statistics_loading",
		"now": function(Elem, subm){
			//Center the loading
			//Elem.find("[find=submenu_info_title]").css("text-align", "center");
			var pitch = Bruno.storage.get("pitch", subm.param);
			var layer = subm.layer;
			if(pitch){
				wrapper_sendAction(
					{
						pitch_id: pitch['id'],
						pitch_md5: pitch['md5'],
					},
					'post',
					'api/stats/session',
					function(msg, error, status, extra){
						if(submenu_Getnext() == layer+1){
							if(typeof extra.data != "undefined"){
								var data = extra.data;
								submenu_Build_return('statistics_data', layer, true, data, false);
							} else {
								submenu_Build_return('statistics_data', layer, true, null, false);
							}
						}
					},
					function(){
						if(submenu_Getnext() == layer+1){
							submenu_Build_return('statistics_data', layer, true, null, false);
						}
					}
				);
			}
		},
	},

};

submenu_list['statistics_data'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 102, 'html'), //Statistics
	},

	//Change the language
	"no_data": {
		"style": "info",
		"title": Bruno.Translation.get('app', 301, 'html'), //No data found
		"class": "submenu_app_statistics_nodata",
	},

	"pre_action": {
		"style": "preAction",
		"action": function(Elem, subm){
			submenu_app_statistics_list(subm.param);
		},
	},
	
};

var submenu_app_statistics_list = function(sessions){

	//Clear the list to rebuild it then
	for(var i in submenu_list['statistics_data']){
		if(
			   i != "_title"
			&& i != "no_data"
			&& i != "pre_action"
		){
			delete submenu_list['statistics_data'][i];
		}
	}

	if(typeof sessions == "object"){
		for(var i in sessions){
			delete submenu_list['statistics_data']['no_data'];
			var title = sessions[i]['title'];
			if(typeof submenu_list['statistics_data']['statistics_'+i] == 'undefined'){
				submenu_list['statistics_data']['statistics_'+i] = {
					"style": "statistics_button",
					"title": "",
					"value": {
						time: function(){
							return new wrapper_date(i).display('date_full');
						},
						participants: sessions[i]['participants'],
					},
					"action_param": {
						id: 123,
						md5: '123abc',
					},
					"action": function(Elem, subm, data){
						console.log(data);
					},
				};
			}
		}
	}
	

};


Submenu.prototype.style['statistics_button'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_app_statistics').clone();
	Elem.prop("id", '');
	Elem.find("[find=submenu_app_statistics_session]").html(attribute.value.time());
	Elem.find("[find=submenu_app_statistics_participants]").html(attribute.value.participants);
	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.click(attribute.action_param, function(event){
			attribute.action(Elem, that, event.data);
		});
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};
