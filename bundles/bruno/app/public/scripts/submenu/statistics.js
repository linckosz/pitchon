/******************************************************

 QUIZ

******************************************************/
submenu_list['statistics_quiz_open'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 108, 'html'), //Quiz Global Statistics
	},

	"loading": {
		"style": "info",
		"title": "<span class='fa fa-spinner fa-spin'></span>",
		"class": "submenu_app_statistics_session_loading",
		"now": function(Elem, subm){
			Bruno.statistics.data = {};
			var pitch = Bruno.storage.get("pitch", subm.param);
			var layer = subm.layer;
			if(pitch){
				wrapper_sendAction(
					{
						id: pitch['id'],
						md5: pitch['md5'],
					},
					'post',
					'api/stats/session',
					function(msg, error, status, extra){
						if(submenu_Getnext() == layer+1){
							if(typeof extra.data != "undefined"){
								var data = extra.data;
								Bruno.statistics.data = extra.data;
								submenu_Build_return('statistics_quiz', layer, true, data, false);
							} else {
								submenu_Build_return('statistics_quiz', layer, true, null, false);
							}
						}
					},
					function(){
						if(submenu_Getnext() == layer+1){
							submenu_Build_return('statistics_quiz', layer, true, null, false);
						}
					}
				);
			}
		},
	},

};

submenu_list['statistics_quiz'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 108, 'html'), //Quiz Global Statistics
	},

	//Change the language
	"no_data": {
		"style": "info",
		"title": Bruno.Translation.get('app', 301, 'html'), //No data found
		"class": "submenu_app_statistics_session_nodata",
	},

	"charts": {
		"style": "statistics_quiz_charts",
		"class": "submenu_app_statistics_session_charts display_none",
		"title": "",
		"now": function(Elem, subm){
			Elem.addClass('display_none');
			if(typeof subm.param == "object"){
				for(var i in subm.param){
					Elem.removeClass('display_none');
					break;
				}
			}
		},
	},

	"pre_action": {
		"style": "preAction",
		"action": function(Elem, subm){
			submenu_app_statistics_session_list();
		},
	},
	
};

var submenu_app_statistics_quiz_chart_gauge;
var submenu_app_statistics_quiz_chart_donut;

var submenu_app_statistics_session_list = function(){
	//Clear the list to rebuild it then
	for(var i in submenu_list['statistics_quiz']){
		if(
			   i != "_title"
			&& i != "no_data"
			&& i != "charts"
			&& i != "pre_action"
		){
			delete submenu_list['statistics_quiz'][i];
		}
	}

	var sessions = Bruno.statistics.getSessions();
	if(typeof sessions == "object"){
		var count = 0;
		for(var i in sessions){
			count++;
		}
		var count_length = (""+count).length;
		var count_text = count;
		for(var i in sessions){
			delete submenu_list['statistics_quiz']['no_data'];
			if(typeof submenu_list['statistics_quiz']['statistics_'+i] == 'undefined'){
				count_text = ""+count;
				while(count_text.length < count_length){
					count_text = "0"+count_text;
				}
				count--;
				submenu_list['statistics_quiz']['statistics_'+i] = {
					"style": "statistics_session_button",
					"title": "",
					"value": {
						title: "<i>" + Bruno.Translation.get('app', 109, 'js') + " #" + count_text + ": </i>" + (new wrapper_date(parseInt(i, 10)).display('date_full')),
						participants: sessions[i]['participants'],
						views: sessions[i]['views'],
						clicks: sessions[i]['clicks'],
					},
					"action_param": {
						id: sessions[i]['id'],
						count: count_text,
					},
					"action": function(Elem, subm, data){
						submenu_Build('statistics_session', true, true, data);
					},
				};
			}
		}
	}
	
};


Submenu.prototype.style['statistics_session_button'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_app_statistics_session').clone();
	Elem.prop("id", '');
	Elem.find("[find=submenu_app_statistics_session_title]").html(attribute.value.title);
	Elem.find("[find=submenu_app_statistics_session_participants]").html(attribute.value.participants);
	Elem.find("[find=submenu_app_statistics_session_views]").html(attribute.value.views);
	Elem.find("[find=submenu_app_statistics_session_clicks]").html(attribute.value.clicks);
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

Submenu.prototype.style['statistics_quiz_charts'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_app_statistics_charts').clone();
	Elem.prop("id", "submenu_app_statistics_charts_"+subm.id);
	Elem.find("[find=gauge_cliks]").prop("id", 'gauge_cliks_'+subm.id);
	Elem.find("[find=pie_devices]").prop("id", 'pie_devices_'+subm.id);
	var that_id = subm.id;

	Elem.find("[find=submenu_app_statistics_charts_date]").addClass("display_none");
	Elem.find("[find=submenu_app_statistics_charts_sessions]").html(Bruno.Translation.get('app', 110, 'js', {sessions: Bruno.statistics.getNbrSessions()})); //[{sessions}] sessions
	Elem.find("[find=submenu_app_statistics_charts_participants]").html(Bruno.Translation.get('app', 105, 'js', {participants: Bruno.statistics.getNbrParticipants()})); //[{participants}] participants
	Elem.find("[find=submenu_app_statistics_charts_views]").html(Bruno.Translation.get('app', 106, 'js', {views: Bruno.statistics.getNbrViews()})); //[{views}] ad views
	Elem.find("[find=submenu_app_statistics_charts_clicks]").html(Bruno.Translation.get('app', 107, 'js', {clicks: Bruno.statistics.getNbrClicks()})); //[{clicks}] ad clicks

	app_application_bruno.add("submenu_app_statistics_charts_"+subm.id, "submenu_show_"+subm.id, function(){
		var subm_id = this.action_param;

		//Gauge
		submenu_app_statistics_quiz_chart_gauge = c3.generate({
			bindto: '#gauge_cliks_'+subm_id,
			size: {
				width: 250,
			},
			gauge: {
				expand: false,
				label: {
					show: false,
					format: function (value, ratio, id) {
						return (Math.round(1000*ratio) / 10)+"%";
					},
				},
			},
			color: {
				pattern: ['#71DA6E'],
			},
			legend: {
				item: {
					onclick: function(id){
						return false;
					},
				},
			},
			transition: {
				duration: 800,
			},
			data: {
				type: 'gauge',
				columns: [
					[Bruno.Translation.get('app', 104, 'js'), 0], //Ad clicks
				],
			},
		});

		setTimeout(function(){
			submenu_app_statistics_quiz_chart_gauge.load({
				columns: [
					[Bruno.Translation.get('app', 104, 'js'), Bruno.statistics.getClicksRate()], //Ad clicks
				]
			});
		}, 0);

		var clicks_rate = Bruno.statistics.getDevices();
		var data_columns = [];
		var color_pattern = [];
		if(clicks_rate['ios'] > 0){
			data_columns.push(['iOS', clicks_rate['ios']]);
			color_pattern.push('#33B5E5');
		}
		if(clicks_rate['android'] > 0){
			data_columns.push(['Android', clicks_rate['android']]);
			color_pattern.push('#FFBB33');
		}
		if(clicks_rate['windows'] > 0){
			data_columns.push(['Windows', clicks_rate['windows']]);
			color_pattern.push('#71DA6E');
		}
		if(clicks_rate['others'] > 0){
			data_columns.push(['others', clicks_rate['others']]);
			color_pattern.push('#FF4444');
		}

		//Devices
		submenu_app_statistics_quiz_chart_donut = c3.generate({
			bindto: '#pie_devices_'+subm_id,
			size: {
				height: 250,
				width: 250,
			},
			donut: {
				title: Bruno.Translation.get('app', 111, 'js'), //devices
				label: {
					format: function (value, ratio, id) {
						return Math.round(100*ratio)+"%";
					},
				},
			},
			color: {
				pattern: ['#33B5E5', '#FFBB33', '#71DA6E', '#FF4444'],
			},
			legend: {
				item: {
					onclick: function(id){
						return false;
					},
				},
			},
			data: {
				type: 'donut',
				columns: data_columns,
			},
		});

	}, subm.id );

	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};










/******************************************************

 SESSION

******************************************************/
submenu_list['statistics_session'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 112, 'html'), //Session Statistics
	},

	"charts": {
		"style": "statistics_session_charts",
		"class": "submenu_app_statistics_session_charts",
		"title": "",
	},

	"pre_action": {
		"style": "preAction",
		"action": function(Elem, subm){
			submenu_app_statistics_statistics_list(subm.param.id);
		},
	},
	
};

var submenu_app_statistics_session_chart_gauge;
var submenu_app_statistics_session_chart_donut;

var submenu_app_statistics_statistics_list = function(session_id){
	//Clear the list to rebuild it then
	for(var i in submenu_list['statistics_session']){
		if(
			   i != "_title"
			&& i != "charts"
			&& i != "pre_action"
		){
			delete submenu_list['statistics_session'][i];
		}
	}

	var statistics = Bruno.statistics.getStatistics(session_id);
	if(typeof statistics == "object"){
		for(var i in statistics){
			if(typeof submenu_list['statistics_session']['statistics_'+i] == 'undefined'){
				submenu_list['statistics_session']['statistics_'+i] = {
					"style": "statistics_statistics_button",
					"title": "",
					"value": {
						title: (new wrapper_date(parseInt(statistics[i]['c_at'], 10)).display('date_full')),
						style: statistics[i]['style'],
						question: statistics[i]['question'],
						participants: statistics[i]['participants'],
						views: statistics[i]['views'],
						clicks: statistics[i]['clicks'],
						trophy: statistics[i]['trophy'],
						score: statistics[i]['score'],
					},
					"action_param": {
						id: statistics[i]['id'],
					},
					"action": function(Elem, subm, data){
						console.log(this.value);
						//submenu_Build('statistics_session_open', true, true, data);
					},
				};
			}
		}
	}
	
};


Submenu.prototype.style['statistics_statistics_button'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_app_statistics_statistics').clone();
	Elem.prop("id", '');
	Elem.find("[find=submenu_app_statistics_statistics_title]").html(attribute.value.title);

	if(attribute.value.style == 1){
		Elem.find("[find=submenu_app_statistics_statistics_style]").attr("src", app_layers_img_answers_grey.src);
	} else if(attribute.value.style == 2){
		Elem.find("[find=submenu_app_statistics_statistics_style]").attr("src", app_layers_img_pictures_grey.src);
	} else if(attribute.value.style == 3){
		Elem.find("[find=submenu_app_statistics_statistics_style]").attr("src", app_layers_img_statistics_grey.src);
	} else if(attribute.value.style == 4){
		Elem.find("[find=submenu_app_statistics_statistics_style]").attr("src", app_layers_img_survey_grey.src);
	}
	
	Elem.find("[find=submenu_app_statistics_statistics_question]").html(attribute.value.question);
	Elem.find("[find=submenu_app_statistics_statistics_participants]").html(attribute.value.participants);
	Elem.find("[find=submenu_app_statistics_statistics_views]").html(attribute.value.views);
	Elem.find("[find=submenu_app_statistics_statistics_clicks]").html(attribute.value.clicks);
	if(attribute.value.style == 1 || attribute.value.style == 2){
		Elem.find("[find=submenu_app_statistics_statistics_trophy_span]").removeClass("display_none");
		Elem.find("[find=submenu_app_statistics_statistics_trophy]").html(attribute.value.trophy);
	} else if(attribute.value.style == 3 || attribute.value.style == 4){
		Elem.find("[find=submenu_app_statistics_statistics_score_span]").removeClass("display_none");
		if(attribute.value.score.a !== false){
			Elem.find("[find=score_a_span]").removeClass("display_none");
			Elem.find("[find=score_a]").html(attribute.value.score.a);
		}
		if(attribute.value.score.b !== false){
			Elem.find("[find=score_b_span]").removeClass("display_none");
			Elem.find("[find=score_b]").html(attribute.value.score.b);
		}
		if(attribute.value.score.c !== false){
			Elem.find("[find=score_c_span]").removeClass("display_none");
			Elem.find("[find=score_c]").html(attribute.value.score.c);
		}
		if(attribute.value.score.d !== false){
			Elem.find("[find=score_d_span]").removeClass("display_none");
			Elem.find("[find=score_d]").html(attribute.value.score.d);
		}
		if(attribute.value.score.e !== false){
			Elem.find("[find=score_e_span]").removeClass("display_none");
			Elem.find("[find=score_e]").html(attribute.value.score.e);
		}
		if(attribute.value.score.f !== false){
			Elem.find("[find=score_f_span]").removeClass("display_none");
			Elem.find("[find=score_f]").html(attribute.value.score.f);
		}
	}
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

Submenu.prototype.style['statistics_session_charts'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_app_statistics_charts').clone();
	Elem.prop("id", "submenu_app_statistics_charts_"+subm.id);
	Elem.find("[find=gauge_cliks]").prop("id", 'gauge_cliks_'+subm.id);
	Elem.find("[find=pie_devices]").prop("id", 'pie_devices_'+subm.id);
	var that_id = subm.id;

	var session = false;
	var temp = Bruno.statistics.getSessions(subm.param.id);
	for(var i in temp){
		session = temp[i];
		break;
	}
	delete temp;
	if(!session){
		delete Elem;
		return false;
	}

	var date = "<i>" + Bruno.Translation.get('app', 109, 'js') + " #" + subm.param.count + ": </i>" + (new wrapper_date(parseInt(session['c_at'], 10)).display('date_full'));

	Elem.find("[find=submenu_app_statistics_charts_date]").html(date);
	Elem.find("[find=submenu_app_statistics_charts_sessions_div]").addClass("display_none");
	Elem.find("[find=submenu_app_statistics_charts_participants]").html(Bruno.Translation.get('app', 105, 'js', {participants: session['participants']})); //[{participants}] participants
	Elem.find("[find=submenu_app_statistics_charts_views]").html(Bruno.Translation.get('app', 106, 'js', {views: session['views']})); //[{views}] ad views
	Elem.find("[find=submenu_app_statistics_charts_clicks]").html(Bruno.Translation.get('app', 107, 'js', {clicks: session['clicks']})); //[{clicks}] ad clicks

	app_application_bruno.add("submenu_app_statistics_charts_"+subm.id, "submenu_show_"+subm.id, function(){
		var subm_id = this.action_param[0];
		var session = this.action_param[1];
		var statistics_list = Bruno.statistics.getStatisticsIDs(session['id']);

		//Gauge
		submenu_app_statistics_session_chart_gauge = c3.generate({
			bindto: '#gauge_cliks_'+subm_id,
			size: {
				width: 250,
			},
			gauge: {
				expand: false,
				label: {
					show: false,
					format: function (value, ratio, id) {
						return (Math.round(1000*ratio) / 10)+"%";
					},
				},
			},
			color: {
				pattern: ['#71DA6E'],
			},
			legend: {
				item: {
					onclick: function(id){
						return false;
					},
				},
			},
			transition: {
				duration: 800,
			},
			data: {
				type: 'gauge',
				columns: [
					[Bruno.Translation.get('app', 104, 'js'), 0], //Ad clicks
				],
			},
		});

		setTimeout(function(statistics_list){
			submenu_app_statistics_session_chart_gauge.load({
				columns: [
					[Bruno.Translation.get('app', 104, 'js'), Bruno.statistics.getClicksRate(statistics_list)], //Ad clicks
				]
			});
		}, 0, statistics_list);

		var clicks_rate = Bruno.statistics.getDevices(statistics_list);
		var data_columns = [];
		var color_pattern = [];
		if(clicks_rate['ios'] > 0){
			data_columns.push(['iOS', clicks_rate['ios']]);
			color_pattern.push('#33B5E5');
		}
		if(clicks_rate['android'] > 0){
			data_columns.push(['Android', clicks_rate['android']]);
			color_pattern.push('#FFBB33');
		}
		if(clicks_rate['windows'] > 0){
			data_columns.push(['Windows', clicks_rate['windows']]);
			color_pattern.push('#71DA6E');
		}
		if(clicks_rate['others'] > 0){
			data_columns.push(['others', clicks_rate['others']]);
			color_pattern.push('#FF4444');
		}

		//Devices
		submenu_app_statistics_session_chart_donut = c3.generate({
			bindto: '#pie_devices_'+subm_id,
			size: {
				height: 250,
				width: 250,
			},
			donut: {
				title: Bruno.Translation.get('app', 111, 'js'), //devices
				label: {
					format: function (value, ratio, id) {
						return Math.round(100*ratio)+"%";
					},
				},
			},
			color: {
				pattern: ['#33B5E5', '#FFBB33', '#71DA6E', '#FF4444'],
			},
			legend: {
				item: {
					onclick: function(id){
						return false;
					},
				},
			},
			data: {
				type: 'donut',
				columns: data_columns,
			},
		});

	}, [subm.id, session] );

	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};
