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
			Bruno.statistics.pitch = pitch;
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
		"class": "submenu_app_statistics_top_charts display_none",
		"title": "",
		"now": function(Elem, subm){
			Elem.addClass('display_none');
			if(typeof subm.param == "object" && typeof subm.param.session == "object"){
				for(var i in subm.param.session){
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

var submenu_app_statistics_session_csv = function(){
	if(Bruno.statistics.pitch){
		device_download('https://app.pitchon.net/api/stats/session/'+Bruno.statistics.pitch['md5']+'/'+Bruno.statistics.pitch['id']+'/session.csv', '_blank', 'session.csv');
		device_download('https://app.pitchon.net/api/stats/session/'+Bruno.statistics.pitch['md5']+'/'+Bruno.statistics.pitch['id']+'/statistics.csv', '_blank', 'statistics.csv');
		device_download('https://app.pitchon.net/api/stats/session/'+Bruno.statistics.pitch['md5']+'/'+Bruno.statistics.pitch['id']+'/answered.csv', '_blank', 'answered.csv');
	}
};

var submenu_app_statistics_quiz_chart_gauge;
var submenu_app_statistics_quiz_chart_donut;

var submenu_app_statistics_session_list = function(){
	//Clear the list to rebuild it then
	for(var i in submenu_list['statistics_quiz']){
		if(
			   i != "_title"
			&& i != "no_data"
			&& i != "csv"
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

	submenu_list['statistics_quiz']['space'] = {
		"style": "space",
		"title": "space",
		"class": "visibility_hidden",
		"value": 40,
	};
	
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
	Elem.find("[find=gauge_clicks]").prop("id", 'gauge_clicks_'+subm.id);
	Elem.find("[find=pie_devices]").prop("id", 'pie_devices_'+subm.id);
	var that_id = subm.id;

	//[plan]
	if(Bruno.storage.get('user', wrapper_localstorage.user_id, 'plan') >= 3){
		Elem.find("[find=submenu_app_statistics_charts_csv]").removeClass("display_none").on("click", submenu_app_statistics_session_csv);
	}

	Elem.find("[find=submenu_app_statistics_charts_date]").addClass("display_none");
	Elem.find("[find=submenu_app_statistics_charts_title]").addClass("display_none");
	Elem.find("[find=submenu_app_statistics_charts_sessions]").html(Bruno.Translation.get('app', 110, 'js', {sessions: Bruno.statistics.getNbrSessions()})); //[{sessions}] sessions
	Elem.find("[find=submenu_app_statistics_charts_participants]").html(Bruno.Translation.get('app', 105, 'js', {participants: Bruno.statistics.getNbrParticipants()})); //[{participants}] participants
	Elem.find("[find=submenu_app_statistics_charts_views]").html(Bruno.Translation.get('app', 106, 'js', {views: Bruno.statistics.getNbrViews()})); //[{views}] ad views
	Elem.find("[find=submenu_app_statistics_charts_clicks]").html(Bruno.Translation.get('app', 107, 'js', {clicks: Bruno.statistics.getNbrClicks()})); //[{clicks}] ad clicks

	app_application_bruno.add("submenu_app_statistics_charts_"+subm.id, "submenu_show_"+subm.id, function(){
		var subm_id = this.action_param;

		//Gauge
		submenu_app_statistics_quiz_chart_gauge = c3.generate({
			bindto: '#gauge_clicks_'+subm_id,
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

		setTimeout(function(subm_id){
			if(Bruno.statistics.getNbrClicks() > 0){
				var Elem_bis = $("#"+subm_id);
				if(Elem_bis.length > 0){
					$("#gauge_clicks_"+subm_id).removeClass("display_none");
					Elem_bis.find("[find=submenu_app_statistics_charts_clicks_div]").removeClass("display_none");
					Elem_bis.find(".submenu_app_statistics_session_clicks").removeClass("display_none");
					submenu_app_statistics_quiz_chart_gauge.load({
						columns: [
							[Bruno.Translation.get('app', 104, 'js'), Bruno.statistics.getClicksRate()], //Ad clicks
						]
					});
				}
			}
		}, 0, subm_id);

		var clicks_devices = Bruno.statistics.getDevices();
		var data_columns = [];
		var color_pattern = [];
		if(clicks_devices['ios'] > 0){
			data_columns.push(['iOS', clicks_devices['ios']]);
			color_pattern.push('#33B5E5');
		}
		if(clicks_devices['android'] > 0){
			data_columns.push(['Android', clicks_devices['android']]);
			color_pattern.push('#FFBB33');
		}
		if(clicks_devices['windows'] > 0){
			data_columns.push(['Windows', clicks_devices['windows']]);
			color_pattern.push('#71DA6E');
		}
		if(clicks_devices['others'] > 0){
			data_columns.push(['others', clicks_devices['others']]);
			color_pattern.push('#FF4444');
		}

		if(data_columns.length <= 0){
			$("#pie_devices_"+subm_id).addClass("display_none");
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
		"class": "submenu_app_statistics_top_charts",
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
						id: statistics[i]['id'],
						title: (new wrapper_date(parseInt(statistics[i]['c_at'], 10)).display('date_full')),
						style: statistics[i]['style'],
						question_id: statistics[i]['question_id'],
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
						submenu_Build('statistics_question', true, true, this.value);
					},
				};
			}
		}
	}

	submenu_list['statistics_session']['space'] = {
		"style": "space",
		"title": "space",
		"class": "visibility_hidden",
		"value": 40,
	};
	
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
	Elem.find("[find=gauge_clicks]").prop("id", 'gauge_clicks_'+subm.id);
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

	var date = "<i>" + Bruno.Translation.get('app', 109, 'js') + " #" + subm.param.count + ": </i>" + (new wrapper_date(parseInt(session['c_at'], 10)).display('date_full')); //Session

	Elem.find("[find=submenu_app_statistics_charts_date]").html(date);
	Elem.find("[find=submenu_app_statistics_charts_title]").addClass("display_none");
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
			bindto: '#gauge_clicks_'+subm_id,
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

		setTimeout(function(statistics_list, subm_id, clicks){
			if(clicks > 0){
				var Elem_bis = $("#"+subm_id);
				if(Elem_bis.length > 0){
					$("#gauge_clicks_"+subm_id).removeClass("display_none");
					Elem_bis.find("[find=submenu_app_statistics_charts_clicks_div]").removeClass("display_none");
					Elem_bis.find(".submenu_app_statistics_statistics_clicks").removeClass("display_none");
					submenu_app_statistics_session_chart_gauge.load({
						columns: [
							[Bruno.Translation.get('app', 104, 'js'), Bruno.statistics.getClicksRate(statistics_list)], //Ad clicks
						]
					});
				}
			}
		}, 0, statistics_list, subm_id, session['clicks']);

		var clicks_devices = Bruno.statistics.getDevices(statistics_list);
		var data_columns = [];
		var color_pattern = [];
		if(clicks_devices['ios'] > 0){
			data_columns.push(['iOS', clicks_devices['ios']]);
			color_pattern.push('#33B5E5');
		}
		if(clicks_devices['android'] > 0){
			data_columns.push(['Android', clicks_devices['android']]);
			color_pattern.push('#FFBB33');
		}
		if(clicks_devices['windows'] > 0){
			data_columns.push(['Windows', clicks_devices['windows']]);
			color_pattern.push('#71DA6E');
		}
		if(clicks_devices['others'] > 0){
			data_columns.push(['others', clicks_devices['others']]);
			color_pattern.push('#FF4444');
		}

		if(data_columns.length <= 0){
			$("#pie_devices_"+subm_id).addClass("display_none");
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













/******************************************************

 QUESTION

******************************************************/
submenu_list['statistics_question'] = {
	//Set the title of the top
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 113, 'html'), //Anwsers Statistics
	},

	"charts": {
		"style": "statistics_question_charts",
		"class": "submenu_app_statistics_top_charts",
		"title": "",
	},

	"pre_action": {
		"style": "preAction",
		"action": function(Elem, subm){
			submenu_app_statistics_answers_list(subm.param);
		},
	},
	
};

var submenu_app_statistics_answers_chart_gauge;
var submenu_app_statistics_answers_chart_donut;

var submenu_app_statistics_answers_list = function(param){
	//Clear the list to rebuild it then
	for(var i in submenu_list['statistics_question']){
		if(
			   i != "_title"
			&& i != "charts"
			&& i != "pre_action"
		){
			delete submenu_list['statistics_question'][i];
		}
	}

	var statistics = false;
	for(var i in Bruno.statistics.data.statistics){
		if(Bruno.statistics.data.statistics[i]["id"] == param.id){
			statistics = Bruno.statistics.data.statistics[i];
		}
	}

	var answers = Bruno.storage.list('answer', 6, null, 'question', param.question_id);
	answers = Bruno.storage.sort_items(answers, 'number');
	if(answers){
		for(var i in answers){
			var letter = Bruno.statistics.NumberToLetter(answers[i]["number"]);
			if(!statistics[letter]){ //We can skip if there was 0 answers or never (false)
				if(param.style == 2 && !answers[i]["file_id"]){
					continue; //Skip if no picture
				} else if(!answers[i]["title"] && !answers[i]["file_id"]){
					continue; //Skip if no title and no picture
				}
			}
			if(typeof submenu_list['statistics_question']['answer_'+answers[i]["number"]] == 'undefined'){
				submenu_list['statistics_question']['answer_'+answers[i]["number"]] = {
					"style": "statistics_question_answer",
					"title": "",
					"value": answers[i],
				};
			}
		}
	}

	submenu_list['statistics_question']['space'] = {
		"style": "space",
		"title": "space",
		"class": "visibility_hidden",
		"value": 40,
	};
	
};

Submenu.prototype.style['statistics_question_charts'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_app_statistics_charts').clone();
	Elem.prop("id", "submenu_app_statistics_charts_"+subm.id);
	Elem.find("[find=gauge_clicks]").prop("id", 'gauge_clicks_'+subm.id);
	Elem.find("[find=pie_devices]").prop("id", 'pie_devices_'+subm.id);
	var that_id = subm.id;

	var trophy = false;
	if(subm.param.style == 1){
		trophy = subm.param.trophy;
		Elem.find("[find=submenu_app_statistics_charts_style]").removeClass("display_none").attr("src", app_layers_img_answers_grey.src);
	} else if(subm.param.style == 2){
		trophy = subm.param.trophy;
		Elem.find("[find=submenu_app_statistics_charts_style]").removeClass("display_none").attr("src", app_layers_img_pictures_grey.src);
	} else if(subm.param.style == 3){
		Elem.find("[find=submenu_app_statistics_charts_style]").removeClass("display_none").attr("src", app_layers_img_statistics_grey.src);
	} else if(subm.param.style == 4){
		Elem.find("[find=submenu_app_statistics_charts_style]").removeClass("display_none").attr("src", app_layers_img_survey_grey.src);
	}

	Elem.find("[find=submenu_app_statistics_charts_date]").html("&nbsp;" + subm.param.title);
	Elem.find("[find=submenu_app_statistics_charts_title]").html(subm.param.question);
	Elem.find("[find=submenu_app_statistics_charts_sessions_div]").addClass("display_none");
	Elem.find("[find=submenu_app_statistics_charts_participants]").html(Bruno.Translation.get('app', 105, 'js', {participants: subm.param.participants})); //[{participants}] participants
	Elem.find("[find=submenu_app_statistics_charts_views]").html(Bruno.Translation.get('app', 106, 'js', {views: subm.param.views})); //[{views}] ad views
	Elem.find("[find=submenu_app_statistics_charts_clicks]").html(Bruno.Translation.get('app', 107, 'js', {clicks: subm.param.clicks})); //[{clicks}] ad clicks
	if(trophy){
		Elem.find("[find=submenu_app_statistics_charts_trophy_div]").removeClass("display_none")
		Elem.find("[find=submenu_app_statistics_charts_trophy]").html(trophy);
	}

	

	app_application_bruno.add("submenu_app_statistics_charts_"+subm.id, "submenu_show_"+subm.id, function(){
		var subm_id = this.action_param;

		//Gauge
		submenu_app_statistics_answers_chart_gauge = c3.generate({
			bindto: '#gauge_clicks_'+subm_id,
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

		setTimeout(function(subm_id, param){
			if(param.clicks > 0){
				var Elem_bis = $("#"+subm_id);
				if(Elem_bis.length > 0){
					$("#gauge_clicks_"+subm_id).removeClass("display_none");
					Elem_bis.find("[find=submenu_app_statistics_charts_clicks_div]").removeClass("display_none");
					Elem_bis.find(".submenu_app_statistics_session_clicks").removeClass("display_none");
					var clicks_rate = 0;
					if(param.views > 0){
						clicks_rate = Math.round(1000 * param.clicks / param.views) / 10; //With one decimal 32.5
					}
					submenu_app_statistics_answers_chart_gauge.load({
						columns: [
							[Bruno.Translation.get('app', 104, 'js'), clicks_rate], //Ad clicks
						]
					});
				}
			}
		}, 0, subm_id, subm.param);

		var statistics_list = {};
		statistics_list[subm.param.id] = subm.param.id;
		var clicks_devices = Bruno.statistics.getDevices(statistics_list);
		var data_columns = [];
		var color_pattern = [];
		if(clicks_devices['ios'] > 0){
			data_columns.push(['iOS', clicks_devices['ios']]);
			color_pattern.push('#33B5E5');
		}
		if(clicks_devices['android'] > 0){
			data_columns.push(['Android', clicks_devices['android']]);
			color_pattern.push('#FFBB33');
		}
		if(clicks_devices['windows'] > 0){
			data_columns.push(['Windows', clicks_devices['windows']]);
			color_pattern.push('#71DA6E');
		}
		if(clicks_devices['others'] > 0){
			data_columns.push(['others', clicks_devices['others']]);
			color_pattern.push('#FF4444');
		}

		if(data_columns.length <= 0){
			$("#pie_devices_"+subm_id).addClass("display_none");
		}
		
		//Devices
		submenu_app_statistics_answers_chart_donut = c3.generate({
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



Submenu.prototype.style['statistics_question_answer'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_app_statistics_answer').clone();
	Elem.prop("id", '');

	//Index
	var index = attribute.value.number; //Style 1(answers) & 2(pictures): we keep the number
	var letter = Bruno.statistics.NumberToLetter(attribute.value.number);
	if(subm.param.style == 3 || subm.param.style == 4){ //3(statistiques) 4(survey)
		index = letter.toUpperCase();
	}
	if(subm.param.style == 1 || subm.param.style == 3){
		index += ".";
	} else {
		index += ")";
	}
	Elem.find("[find=index]").html(index);

	//% of answers
	var statistics = false;
	for(var i in Bruno.statistics.data.statistics){
		if(Bruno.statistics.data.statistics[i]["id"] == subm.param.id){
			statistics = Bruno.statistics.data.statistics[i];
		}
	}
	if(typeof statistics == "object" && statistics.answers > 0){
		var info = "";
		if(subm.param.style == 4){ //Survey
			if(subm.param.score[letter] !== false){
				info = subm.param.score[letter];
			}
		} else {
			info = Math.round(100 * statistics[letter] / statistics.answers) + "%";
		}
		Elem.find("[find=info]").html(info);
	}

	//Check
	if(subm.param.style == 1 || subm.param.style == 2){
		Elem.find("[find=correct]").removeClass("display_none");
		if(typeof statistics == "object" && attribute.value.number == statistics.number){
			Elem.find("[find=correct]").addClass("fa fa-check");
		}
	}

	//Imgae
	if(attribute.value.file_id){
		var thumbnail = Bruno.storage.getFile(attribute.value.file_id, 'thumbnail');
		if(thumbnail){
			Elem.find("[find=img]").removeClass("display_none").css("background-image", "url('"+thumbnail+"')");
		}
	}

	//Title
	Elem.find("[find=title]").html(attribute.value.title);
	
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};
