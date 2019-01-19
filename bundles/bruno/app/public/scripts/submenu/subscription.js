submenu_list['subscription'] = {
	
	"_title": {
		"style": "title",
		"title": function(){
			if(responsive.test("maxMobile")){
				return Bruno.Translation.get('app', 158, 'html'); //Subscription plan
			}
			return Bruno.Translation.get('app', 155, 'html'); //Upgrade your subscription plan
		},
	},

	"selection": {
		"style": "subscription_selection",
		"title": "",
		"now": function(Elem, subm){
			
			//Subscription plan selection
			Elem.find(".submenu_subscription_option").on("click", Elem, function(event){
				if(!$(this).hasClass("disabled")){
					event.data.find(".submenu_subscription_option").removeClass("selected");
					$(this).addClass("selected");
					submenu_subscription_selection_plan = parseInt($(this).attr("plan"), 10);
					submenu_subscription_pricing_fn(event.data, submenu_subscription_prices[submenu_subscription_selection_plan]);
					submenu_subscription_features(event.data.find("[find=features]"));
				}
			});

			//Preselection the current user plan
			var plan = Bruno.storage.get('user', wrapper_localstorage.user_id, 'plan'); //Must get the real plan value regardless it's read_only (don't use getPlan() which can return null)
			submenu_subscription_plan_duration = parseInt(Bruno.storage.get('user', wrapper_localstorage.user_id, 'plan_duration'), 10);
			if(plan){
				var Elem_plan = Elem.find("[plan="+plan+"]:first");
				Elem_plan.addClass("selected");
				submenu_subscription_selection_plan = parseInt(plan, 10);
				submenu_subscription_pricing_fn(Elem, submenu_subscription_prices[submenu_subscription_selection_plan]);
				submenu_subscription_features(Elem.find("[find=features]"));
			}
			Elem.find(".submenu_subscription_option").each(function(){
				if($(this).hasClass("selected")){
					return false; //=break
				}
			});

			if(submenu_subscription_discount_value > 0){
				//Elem.find("[find=discount]").removeClass("display_none");
			}

			submenu_subscription_slider_cursor_focus = false;

			var Elem_bar = Elem.find("[find=slider_bar]");
			var Elem_index = Elem.find("[find=slider_index]");
			var submenu_wrapper = subm.Wrapper();

			Elem_bar.data('duration', submenu_subscription_plan_duration);
			Elem_bar.data('reset', submenu_subscription_plan_duration);
			Elem_index.css('left', (25 * (submenu_subscription_plan_duration-1))+"%" );

			Elem.find("[find=slider_cursor]").on("mousedown touchdown touchstart", [Elem_index, Elem_bar], function(event){
				var Elem_index = event.data[0];
				var Elem_bar = event.data[1];
				submenu_subscription_slider_cursor_focus = true;
				Elem_index.addClass("active");
				Elem_bar.data('reset', Elem_bar.data('duration'));
			});

			submenu_wrapper.on('mousemove touchmove', [Elem_bar, Elem_index, Elem], function(event){
				if(submenu_subscription_slider_cursor_focus){
					var Elem_bar = event.data[0];
					var Elem_index = event.data[1];
					var Elem = event.data[2];
					wrapper_mouse.set(event);
					var offset = Elem_bar.offset();
					var left = 100*(wrapper_mouse.x - Elem_bar.offset().left) / Elem_bar.width();
					if(left < 0){
						left = 0;
					} else if(left > 100){
						left = 100;
					}
					var plan_duration = 1;
					if(left<12){
						plan_duration = 1;
					} else if(left<37){
						plan_duration = 2;
					} else if(left<62){
						plan_duration = 3;
					} else if(left<87){
						plan_duration = 4;
					} else {
						plan_duration = 5;
					}
					Elem_index.css('left', left+"%" );

					submenu_subscription_pricing_fn(Elem, submenu_subscription_price, plan_duration);
					// toto => Display live value

				}
			});
			submenu_wrapper.on('mouseup touchup touchend', [Elem_bar, Elem_index, Elem], function(event){
				if(submenu_subscription_slider_cursor_focus){
					submenu_subscription_slider_cursor_focus = false;
					var Elem_bar = event.data[0];
					var Elem_index = event.data[1];
					var Elem = event.data[2];
					Elem_index.removeClass("active");
					//Move to closer
					var left = 100 * parseInt(Elem_index.css('left'), 10) / Elem_bar.width();
					if(left<12){
						submenu_subscription_plan_duration = 1;
					} else if(left<37){
						submenu_subscription_plan_duration = 2;
					} else if(left<62){
						submenu_subscription_plan_duration = 3;
					} else if(left<87){
						submenu_subscription_plan_duration = 4;
					} else {
						submenu_subscription_plan_duration = 5;
					}
					Elem_index.css('left', (25 * (submenu_subscription_plan_duration-1))+"%" );
					
					submenu_subscription_pricing_fn(Elem, submenu_subscription_price);
					// toto => Display live value
				}
			});
			submenu_wrapper.on('mouseleave', [Elem_bar, Elem_index], function(event){
				if(submenu_subscription_slider_cursor_focus){
					submenu_subscription_slider_cursor_focus = false;
					var Elem_bar = event.data[0];
					var Elem_index = event.data[1];
					Elem_index.removeClass("active");
					//Move to initial
					Elem_bar.data('duration', Elem_bar.data('reset'));
					Elem_index.css('left', (25 * (parseInt(Elem_bar.data('reset'), 10)-1))+"%" );
				}
			});
			
			Elem.find(".submenu_subscription_slider_dot").on("click", Elem_index, function(event){
				if(!submenu_subscription_slider_cursor_focus){
					Elem_index = event.data;
					submenu_subscription_plan_duration = parseInt($(this).attr('value'), 10);
					Elem_index.css('left', (25 * (submenu_subscription_plan_duration-1))+"%" );
					submenu_subscription_pricing_fn(Elem, submenu_subscription_price);
				}
			});
			
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

var submenu_subscription_slider_cursor_focus = false;

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

var submenu_subscription_pricing_fn = function(Elem, price, fake_plan_duration){
	if(typeof fake_plan_duration == "undefined"){ fake_plan_duration = false; }
	var plan_duration = submenu_subscription_plan_duration;
	var fake = false;
	if(typeof price == "number" && price >= 0){
		if(typeof fake_plan_duration == "number" && fake_plan_duration >= 0){
			fake = true;
			plan_duration = fake_plan_duration;
		} else {
			submenu_subscription_price = price;
		}
	} else {
		return false;
	}
	if(typeof submenu_subscription_plans[plan_duration] == "undefined"){
		plan_duration = submenu_subscription_plans.length - 1;
	}
	//This operation must be the same as the Backend
	var total_price = price * submenu_subscription_plans[plan_duration][0]; //Price * Months

	//Promocode
	var discount = submenu_subscription_plans[plan_duration][1] * ((100-submenu_subscription_discount_value)/100);
	
	if(discount<1){
		Elem.find("[find=pricing_total_discount]").removeClass("display_none");
		Elem.find("[find=pricing_total_discount_value]").html(Math.round(100*(1-discount)));
		Elem.find("[find=pricing_total_instead]").removeClass("display_none").html(Math.floor(total_price)+"€");
		total_price = discount * total_price;
	} else {
		Elem.find("[find=pricing_total_discount]").addClass("display_none");
		Elem.find("[find=pricing_total_discount_value]").html(0);
		Elem.find("[find=pricing_total_instead]").addClass("display_none").html("");
	}
	total_price = Math.floor(total_price);
	Elem.find("[find=pricing_total_value]").html(total_price+"€");
	Elem.find("[find=slider_duration]").html(submenu_subscription_plans[plan_duration][2]);

	//PROMOCODE (PHP)
	//$plan_price = floor((100-intval($promocode->discount))/100 * $plan_price);


	//EXPIRATION DATE CALULATION
	//It takes in consideration the remaining time and its convertion if the user change the plan
	var plan = Bruno.storage.getPlan();
	var plan_expire = Bruno.storage.getPlanAt();
	var now = new wrapper_date().getTimestamp();
	var expiration = new wrapper_date();
	if(plan_expire > now && Bruno.storage.getPlan()){
		//If currently active we postpone the plan
		var new_plan_expire = now;
		if(plan != submenu_subscription_selection_plan){
			//If the plan is different, we need to extrapolate an updated expiration date based on remaining days and plan price
			var ms_diff = plan_expire - now; //Time difference in ms
			if(submenu_subscription_prices[plan] > 0){
				//Ratio of diff
				ms_diff = Math.floor(ms_diff * submenu_subscription_prices[plan] / submenu_subscription_prices[submenu_subscription_selection_plan]);
				new_plan_expire = now + ms_diff;
			}
		} else {
			new_plan_expire = plan_expire;
		}
		expiration = new wrapper_date(new_plan_expire);
	}
	//We add the duration of the plan selected
	expiration.addMonths(submenu_subscription_plans[plan_duration][0]);
	
	Elem.find("[find=pricing_expiration]").html(Bruno.Translation.get('app', 157, 'html', {expiration: expiration.display('date_medium_slim')})); //Expiration date: [{expiration}]
	

	if(!fake){
		submenu_subscription_total_price = total_price;
	}
};
