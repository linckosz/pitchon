submenu_list['reward'] = {
	
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 145, 'html'), //Rewards
	},

	"users": {
		"style": "info",
		"class": "submenu_deco_info_wrap submenu_app_reward_info",
		"title": function(){
			var text = "";
			var hosts = Bruno.storage.get("user", wrapper_localstorage.user_id, "_host");
			if(hosts && hosts > 1){
				text = Bruno.Translation.get('app', 147, 'html', {hosts: hosts,}); //[{number}] users created an account after playing your Quizzes. If any of them upgrades to a paid subscription, you will be rewarded!
			} else if(hosts && hosts == 1){
				text = Bruno.Translation.get('app', 146, 'html'); //A user created an account after playing one of your Quizzes. If he upgrades to a paid subscription, you will be rewarded!
			} else {
				text = Bruno.Translation.get('app', 148, 'html'); //Play your Quizzes with your audience, if someone pays for any subscription, you will be rewarded!
			}

			var user = Bruno.storage.get("user", wrapper_localstorage.user_id);

			var paid_amount = 0;
			var paid_users = 0;
			var unpaid_amount = 0;
			var unpaid_users = 0;
			var records = user["_bank"];
			if(typeof records == "object"){
				for(var i in records){
					if(records[i]["used_at"]){
						paid_amount += parseInt(records[i]["eur"], 10);
						paid_users++;
					} else {
						unpaid_amount += parseInt(records[i]["eur"], 10);
						unpaid_users++;
					}
				}
			}
			if(paid_users > 0 && paid_amount > 0){
				text += "<br /><br />";
				text += Bruno.Translation.get('app', 149, 'js', {amount: paid_amount, users: paid_users,}); //You already received [{amount}] € thanks to [{users}] user(s).
			}
			if(unpaid_users > 0 && unpaid_amount > 0){
				text += "<br /><br />";
				if(paid_users > 0 && paid_amount > 0){
					text += Bruno.Translation.get('app', 150, 'js', {amount: unpaid_amount, users: unpaid_users,}); //Thanks to [{users}] another user(s), you still can ask for <b>[{amount}] €</b>.
				} else {
					text += Bruno.Translation.get('app', 151, 'js', {amount: unpaid_amount, users: unpaid_users,}); //Thanks to [{users}] another user(s), you can ask for <b>[{amount}] €</b>.
				}
			}
			return text;
		},
	},
	
	"reward": {
		"style": "reward_button",
		"title": function(){
			var unpaid_amount = 0;
			var records = Bruno.storage.get("user", wrapper_localstorage.user_id, "_bank");
			if(typeof records == "object"){
				for(var i in records){
					if(!records[i]["used_at"]){
						unpaid_amount += parseInt(records[i]["eur"], 10);
					}
				}
			}
			return Bruno.Translation.get('app', 152, 'html', {amount: unpaid_amount,}); //Ask for [{amount}] €
		},
		"action": function(Elem, subm, data){
			var list = "";
			var unpaid_amount = 0;
			var user = Bruno.storage.get("user", wrapper_localstorage.user_id);
			var records = user["_bank"];
			if(typeof records == "object"){
				for(var i in records){
					if(!records[i]["used_at"]){
						unpaid_amount += parseInt(records[i]["eur"], 10);
						list += "@"+records[i]["id"] + "#" + records[i]["md5"]+";";
					}
				}
			}

			if(unpaid_amount > 0){
				var subject = Bruno.Translation.get('app', 401, 'js', { title: wrapper_main_title, }); //[ [{title}] ] Rewards
				var body = Bruno.Translation.get('app', 402, 'brut', { amount: unpaid_amount, list: list, username: user["username"]}); //Hello, I have been rewarded...
				var link = "mailto:"
					+ wrapper_link['reward']
					+ "?subject=" + escape(subject)
					+ "&body=" + encodeURIComponent(body);
				window.location.href = link;
			}
		},
		"now": function(Elem, subm){
			Elem.addClass("display_none");
			var records = Bruno.storage.get("user", wrapper_localstorage.user_id, "_bank");
			if(typeof records == "object"){
				for(var i in records){
					if(!records[i]["used_at"]){
						Elem.removeClass("display_none");
						return true;
					}
				}
			}
		},
	},

	"space": {
		"style": "space",
		"title": "space",
		"class": "submenu_space_noborder",
		"value": 40,
	},

};

Submenu.prototype.style['reward_button'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_reward_button').clone();
	Elem.prop("id", '');
	Elem.find("[find=ask]").html(attribute.title);
	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.find("[find=ask]").click(attribute.action_param, function(event){
			attribute.action(Elem, that, event.data);
		});
	}
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};
