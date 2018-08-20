submenu_list['subscription'] = {
	
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 155, 'html'), //Upgrade your subscription plan
	},

	"selection": {
		"style": "subscription_selection",
		"title": "",
		"now": function(Elem, subm){
			Elem.find(".submenu_subscription_option").on("click", Elem, function(event){
				if(!$(this).hasClass("disabled")){
					event.data.find(".submenu_subscription_option").removeClass("selected");
					$(this).addClass("selected");
					submenu_subscription_selection_plan = parseInt($(this).attr("plan"), 10);
					submenu_subscription_pricing_fn(event.data, parseInt($(this).find("[find=amount]").html(), 10));
					submenu_subscription_features(event.data.find("[find=features]"));
				}
			});
			var plan = Bruno.storage.get('user', wrapper_localstorage.user_id, 'plan'); //Must get the real plan value regardless it's read_only
			if(plan){
				var Elem_plan = Elem.find("[plan="+plan+"]:first");
				Elem_plan.addClass("selected");
				submenu_subscription_selection_plan = parseInt(plan, 10);
				submenu_subscription_pricing_fn(Elem, parseInt(Elem_plan.find("[find=amount]").html(), 10));
				submenu_subscription_features(Elem.find("[find=features]"));
			}
			Elem.find(".submenu_subscription_option").each(function(){
				if($(this).hasClass("selected")){
					return false; //=break
				}
				//$(this).addClass("disabled");
			});

			if(submenu_subscription_pricing == 1){
				Elem.find("[find=pricing_month]").addClass("selected");
			} else {
				Elem.find("[find=pricing_annual]").addClass("selected");
			}

			Elem.find(".submenu_subscription_billing").on("click", Elem, function(event){
				event.data.find(".submenu_subscription_billing").removeClass("selected");
				$(this).addClass("selected");
				submenu_subscription_pricing = parseInt($(this).attr("pricing"), 10); //1:monthly / 2:annual
			});

			if(submenu_subscription_discount_value > 0){
				Elem.find("[find=discount]").removeClass("display_none");
			}
			
			//We need to delay the display of paypal
			app_application_bruno.add("submenu_subscription_"+subm.id, "submenu_show_"+subm.id, function(){
				var Elem_paypal = this.action_param[0];
				var subm_id = this.action_param[1];
				if(Elem_paypal.length == 1){
					submenu_subscription_paypal(Elem_paypal);
					submenu_resize_content();
				}
			}, [Elem.find("[find=paypal_buy]"), subm.id] );

		},
	},

	"space1": {
		"style": "space",
		"title": "space",
		"value": 60,
	},

};

Submenu.prototype.style['subscription_selection'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_subscription').clone();
	Elem.prop("id", 'submenu_subscription_'+subm.id);
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};

var submenu_subscription_features = function(Elem){
	Elem.find("[find=feature]").each(function(){
		if(parseInt($(this).attr("plan"), 10) <= submenu_subscription_selection_plan){
			$(this)
				.removeClass("fa-circle fa-minus-circle submenu_subscription_feature_disabled")
				.addClass("fa-check-circle submenu_subscription_feature_enabled");
		} else {
			$(this)
				.removeClass("fa-check-circle fa-minus-circle submenu_subscription_feature_enabled")
				.addClass("fa-circle submenu_subscription_feature_disabled");
		}
	});
};

var submenu_subscription_pricing_fn = function(Elem, price){
	if(typeof price == "number" && price >= 0){
		submenu_subscription_price = price;
	}
	var pricing_month_value = submenu_subscription_price;
	var pricing_annual_value = ((10*submenu_subscription_price) - 1);
	var discount = ((100-submenu_subscription_discount_value)/100);
	if(discount<1){
		Elem.find("[find=pricing_month_value]").html(Math.floor(discount * pricing_month_value)+"€");
		Elem.find("[find=pricing_month_instead]").removeClass("display_none").html(Bruno.Translation.get('app', 156, 'html', {amount: pricing_month_value,})); //Instead of [{amount}]€
		Elem.find("[find=pricing_annual_value]").html(Math.floor(discount * pricing_annual_value)+"€");
		Elem.find("[find=pricing_annual_instead]").removeClass("display_none").html(Bruno.Translation.get('app', 156, 'html', {amount: pricing_annual_value,})); //Instead of [{amount}]€
	} else {
		Elem.find("[find=pricing_month_value]").html(pricing_month_value+"€");
		Elem.find("[find=pricing_month_instead]").addClass("display_none").html(Bruno.Translation.get('app', 156, 'html', {amount: pricing_month_value,})); //Instead of [{amount}]€
		Elem.find("[find=pricing_annual_value]").html(pricing_annual_value+"€");
		Elem.find("[find=pricing_annual_instead]").addClass("display_none").html(Bruno.Translation.get('app', 156, 'html', {amount: pricing_annual_value,})); //Instead of [{amount}]€
	}
};
