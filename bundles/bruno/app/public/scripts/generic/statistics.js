Bruno.statistics = {};
Bruno.statistics.data = {};


//Get total number of Sessions
Bruno.statistics.getNbrSessions = function(){
	var result = 0;
	if(typeof Bruno.statistics.data['session'] == 'object'){
		for(var i in Bruno.statistics.data['session']){
			result++;
		}
	}
	return result;
};

//Get total number of Participants
Bruno.statistics.getNbrParticipants = function(statistics_list){
	var result = 0;
	var users = {};
	if(typeof Bruno.statistics.data['answered'] == 'object'){
		for(var i in Bruno.statistics.data['answered']){
			if(typeof statistics_list == 'object' && typeof statistics_list[Bruno.statistics.data['answered'][i]['statistics_id']] == 'undefined'){
				//We skip if we want to limit to a certain scope
				continue;
			}
			users[Bruno.statistics.data['answered'][i]['guest_id']] = true;
		}
	}
	for(var i in users){
		result++;
	}
	return result;
};

//Get total number of Views
Bruno.statistics.getNbrViews = function(statistics_list){
	var result = 0;
	if(typeof Bruno.statistics.data['answered'] == 'object'){
		for(var i in Bruno.statistics.data['answered']){
			if(typeof statistics_list == 'object' && typeof statistics_list[Bruno.statistics.data['answered'][i]['statistics_id']] == 'undefined'){
				//We skip if we want to limit to a certain scope
				continue;
			}
			result++;
		}
	}
	return result;
};

//Get total number of Views
Bruno.statistics.getNbrClicks = function(statistics_list){
	var result = 0;
	if(typeof Bruno.statistics.data['answered'] == 'object'){
		for(var i in Bruno.statistics.data['answered']){
			if(typeof statistics_list == 'object' && typeof statistics_list[Bruno.statistics.data['answered'][i]['statistics_id']] == 'undefined'){
				//We skip if we want to limit to a certain scope
				continue;
			}
			if(Bruno.statistics.data['answered'][i]['ad_clicks']){ //We don't care here how many clicks per user, we just considerate one click
				result++;
			}
		}
	}
	return result;
};

//Get % of click per view
Bruno.statistics.getClicksRate = function(statistics_list){
	var views = Bruno.statistics.getNbrViews(statistics_list);
	if(views > 0){
		return Math.round(1000 * Bruno.statistics.getNbrClicks(statistics_list) / Bruno.statistics.getNbrViews(statistics_list)) / 10; //With one decimal 32.5
	}
	return 0;
};

//Get Devices numbers
Bruno.statistics.getDevices = function(statistics_list){
	var result = {
		ios: 0,
		android: 0,
		windows: 0,
		others: 0,
	};
	if(typeof Bruno.statistics.data['answered'] == 'object'){
		for(var i in Bruno.statistics.data['answered']){
			if(typeof statistics_list == 'object' && typeof statistics_list[Bruno.statistics.data['answered'][i]['statistics_id']] == 'undefined'){
				//We skip if we want to limit to a certain scope
				continue;
			}
			if(Bruno.statistics.data['answered'][i]['info_0'].toLowerCase() == 'macintosh'){
				result['ios']++;
			} else if(Bruno.statistics.data['answered'][i]['info_0'].toLowerCase() == 'android'){
				result['android']++;
			} else if(Bruno.statistics.data['answered'][i]['info_0'].toLowerCase() == 'windows'){
				result['windows']++;
			} else {
				result['others']++;
			}
		}
	}
	return result;
};


//Get Sessions
Bruno.statistics.getSessions = function(session_id){
	var result = {};
	var result_temp = {};
	var key = [];
	var statistics_list = {};
	if(typeof Bruno.statistics.data['session'] == 'object'){
		for(var i in Bruno.statistics.data['session']){
			if(typeof session_id != 'undefined' && session_id != Bruno.statistics.data['session'][i]['id']){
				//We skip if we want to limit to a certain scope
				continue;
			}
			statistics_list = Bruno.statistics.getStatisticsIDs(Bruno.statistics.data['session'][i]['id']);
			key.push(Bruno.statistics.data['session'][i]['c_at']);
			result_temp[Bruno.statistics.data['session'][i]['c_at']] = {
				id: Bruno.statistics.data['session'][i]['id'],
				c_at: Bruno.statistics.data['session'][i]['c_at'],
				participants: Bruno.statistics.getNbrParticipants(statistics_list),
				views: Bruno.statistics.getNbrViews(statistics_list),
				clicks: Bruno.statistics.getNbrClicks(statistics_list),
			};
		}
		//Order result from newest to oldest
		key.sort().reverse();
		for(var i in key){
			result[key[i]] = result_temp[key[i]];
		}
	}
	return result;
};

//Get ID list of statistics from session
Bruno.statistics.getStatisticsIDs = function(session_id){
	var result = {};
	if(typeof session_id == 'undefined'){
		return result;
	}
	if(typeof Bruno.statistics.data['statistics'] == 'object'){
		for(var j in Bruno.statistics.data['statistics']){
			if(Bruno.statistics.data['statistics'][j]['session_id'] == session_id){
				result[Bruno.statistics.data['statistics'][j]['id']] = Bruno.statistics.data['statistics'][j]['id'];
			}
		}
	}

	return result;
};

//Get list of statistics from session
Bruno.statistics.getStatistics = function(session_id){
	var result = {};
	if(typeof session_id == 'undefined'){
		return result;
	}
	var result_temp = {};
	var key = [];
	var statistics_list = {};
	var question = false;
	if(typeof Bruno.statistics.data['statistics'] == 'object'){
		for(var i in Bruno.statistics.data['statistics']){
			var stats = Bruno.statistics.data['statistics'][i];
			if(session_id != stats['session_id']){
				continue;
			}
			statistics_list = {};
			statistics_list[stats['id']] = stats['id'];
			key.push(stats['c_at']);
			result_temp[stats['c_at']] = {
				id: stats['id'],
				c_at: stats['c_at'],
				participants: Bruno.statistics.getNbrParticipants(statistics_list),
				views: Bruno.statistics.getNbrViews(statistics_list),
				clicks: Bruno.statistics.getNbrClicks(statistics_list),
				style: stats['style'],
				question_id: false,
				question: "",
				trophy: "0%",
				score: {
					a: false,
					b: false,
					c: false,
					d: false,
					e: false,
					f: false,
				},
			};
			var style = stats['style'];
			if(stats['style'] == 1 || stats['style'] == 2){ //Answers + Pictures
				var total = stats['a']+stats['b']+stats['c']+stats['d']+stats['e']+stats['f'];
				if(total > 0){
					result_temp[stats['c_at']]['trophy'] = Math.round( 100 * stats[Bruno.statistics.NumberToLetter(stats['number'])] / total ) + "%";
				}
			} else if(stats['style'] == 3){ //Statistiques
				var total = stats['a']+stats['b']+stats['c']+stats['d']+stats['e']+stats['f'];
				if(stats['a']!=null){ result_temp[stats['c_at']]['score']['a'] = "0%"; }
				if(stats['b']!=null){ result_temp[stats['c_at']]['score']['b'] = "0%"; }
				if(stats['c']!=null){ result_temp[stats['c_at']]['score']['c'] = "0%"; }
				if(stats['d']!=null){ result_temp[stats['c_at']]['score']['d'] = "0%"; }
				if(stats['e']!=null){ result_temp[stats['c_at']]['score']['e'] = "0%"; }
				if(stats['f']!=null){ result_temp[stats['c_at']]['score']['f'] = "0%"; }
				if(total > 0){
					if(stats['a']){
						result_temp[stats['c_at']]['score']['a'] = Math.round( 100 * stats['a'] / total ) + "%";
					}
					if(stats['b']){
						result_temp[stats['c_at']]['score']['b'] = Math.round( 100 * stats['b'] / total ) + "%";
					}
					if(stats['c']){
						result_temp[stats['c_at']]['score']['c'] = Math.round( 100 * stats['c'] / total ) + "%";
					}
					if(stats['d']){
						result_temp[stats['c_at']]['score']['d'] = Math.round( 100 * stats['d'] / total ) + "%";
					}
					if(stats['e']){
						result_temp[stats['c_at']]['score']['e'] = Math.round( 100 * stats['e'] / total ) + "%";
					}
					if(stats['f']){
						result_temp[stats['c_at']]['score']['f'] = Math.round( 100 * stats['f'] / total ) + "%";
					}
				}
			} else if(stats['style'] == 4){ //Survey
				var total = stats['a']+stats['b']+stats['c']+stats['d']+stats['e']+stats['f'];
				if(stats['t_a']!=null){ result_temp[stats['c_at']]['score']['a'] = "0"; }
				if(stats['t_b']!=null){ result_temp[stats['c_at']]['score']['b'] = "0"; }
				if(stats['t_c']!=null){ result_temp[stats['c_at']]['score']['c'] = "0"; }
				if(stats['t_d']!=null){ result_temp[stats['c_at']]['score']['d'] = "0"; }
				if(stats['t_e']!=null){ result_temp[stats['c_at']]['score']['e'] = "0"; }
				if(stats['t_f']!=null){ result_temp[stats['c_at']]['score']['f'] = "0"; }
				if(total > 0){
					if(stats['a']){
						result_temp[stats['c_at']]['score']['a'] = Math.round( 10 * stats['t_a'] / stats['a'] ) / 10;
					}
					if(stats['b']){
						result_temp[stats['c_at']]['score']['b'] = Math.round( 10 * stats['t_b'] / stats['b'] ) / 10;
					}
					if(stats['c']){
						result_temp[stats['c_at']]['score']['c'] = Math.round( 10 * stats['t_c'] / stats['c'] ) / 10;
					}
					if(stats['d']){
						result_temp[stats['c_at']]['score']['d'] = Math.round( 10 * stats['t_d'] / stats['d'] ) / 10;
					}
					if(stats['e']){
						result_temp[stats['c_at']]['score']['e'] = Math.round( 10 * stats['t_e'] / stats['e'] ) / 10;
					}
					if(stats['f']){
						result_temp[stats['c_at']]['score']['f'] = Math.round( 10 * stats['t_f'] / stats['f'] ) / 10;
					}
				}
			}
			//Attach question values
			question = Bruno.storage.get("question", Bruno.statistics.data['statistics'][i]['question_id']);
			if(question){
				result_temp[stats['c_at']]['question_id'] = question["id"];
				result_temp[stats['c_at']]['question'] = question["title"];
			}
		}
		//Order result from newest to oldest
		key.sort().reverse();
		for(var i in key){
			result[key[i]] = result_temp[key[i]];
		}
	}
	return result;
};

Bruno.statistics.NumberToLetter = function(number){
	if(number==1){
		return 'a';
	} else if(number==2){
		return 'b';
	} else if(number==3){
		return 'c';
	} else if(number==4){
		return 'd';
	} else if(number==5){
		return 'e';
	} else if(number==6){
		return 'f';
	} else {
		return 'a';
	}
};
