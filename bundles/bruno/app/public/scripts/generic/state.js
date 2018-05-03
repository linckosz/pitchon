
$(window).on('popstate', function(event){
	//If it's triggered by hash only
	if(document.location.hash){
		app_generic_state.openItem(false, document.location.hash);
		return true;
	}
	if(app_generic_state.manual){ //Only trigger manual user action (button)
		var action = false;
		var change = false;
		var data = history.state;
		if(data){
			for(var i=0; i<app_generic_state.priority.length; i++){
				var key = app_generic_state.priority[i];
				if(app_generic_state.type[key]=='boolean'){
					if(typeof data[key] != 'undefined' && app_generic_state.current[key]!=app_generic_state.default[key]){
						data[key] = app_generic_state.default[key];
						change = app_generic_state.downKey(key, data[key]);
						if(change){
							if(typeof app_generic_state.action[key] == 'function'){
								app_generic_state.action[key](data);
							}
							action = true;
							break; //We only modify one element at a time
						}
						action = true;
					}
				} else if(app_generic_state.type[key]=='increase'){
					if(typeof data[key] != 'undefined' && data[key]!=app_generic_state.current[key]){
						if(data[key]<0){
							data[key] = 0;
						}
						change = app_generic_state.downKey(key, data[key]);
						if(change){
							if(typeof app_generic_state.action[key] == 'function'){
								app_generic_state.action[key](data);
							}
							action = true;
							break; //We only modify one element at a time
						}
						action = true;
					}
				}
			}
		}
		if(!action){
			if(supportsTouch){
				base_show_error(Bruno.Translation.get('app', 61, 'js')); //Tap again to exit the application
				clearTimeout(app_generic_state.close_timer);
				app_generic_state.close_timer = setTimeout(function(){
					base_hide_error();
					app_generic_state.reset();
					app_generic_state.openItem();
					window.history.pushState(app_generic_state.default, app_generic_state.getTitle(), document.location.pathname);
					app_generic_state.close_timer = false;
				}, 3000);
			} else {
				app_generic_state.reset();
				app_generic_state.openItem();
				window.history.replaceState(app_generic_state.default, app_generic_state.getTitle(), document.location.pathname);
			}
		} else if(!change){
			app_generic_state.manual = true;
			window.history.go(-1);
		}
	}
	app_generic_state.manual = true;
});

var app_generic_state = {
	
	allowed: false, //At true pushState and replaceState are available

	close_timer: false,

	started: false,

	quick_item: false,

	manual: true,

	default: {
		showppt: 0,
		previewer: 0,
		submenu: 0,
		menu: 0,
	},

	current: {
		showppt: 0,
		previewer: 0,
		submenu: 0,
		menu: 0,
	},

	param: {
		showppt: {
			0: null,
		},
		previewer: {
			0: null,
		},
		submenu: {
			0: null,
		},
		menu: {
			0: null,
		},
	},

	/*
		boolean: -1/0/1 (depends on default value)
		increase: -x/0/x (depends on incremantal integer value)
	*/
	type: {
		showppt: 'boolean',
		previewer: 'boolean',
		submenu: 'increase',
		menu: 'increase',
	},

	priority: [
		'showppt',
		'previewer',
		'submenu',
		'menu',
	],

	action: {
		showppt: function(data){
			showppt_close();
		},
		previewer: function(data){
			previewer(false);
		},
		submenu: function(data){
			submenu_Clean(data.submenu+1, true, false);
		},
		menu: function(data){
			var order = [
				'pitch',
				'question',
				'answer',
			];
			var index = 0;
			if(typeof order[data.menu] != 'undefined'){
				index = data.menu;
			}
			var param = null;
			if(typeof app_generic_state.param.menu == 'object' && typeof app_generic_state.param.menu[index] != 'undefined'){
				param = app_generic_state.param.menu[index];
			}
			app_content_menu.selection(order[index], param);
		},
	},

	reset: function(){
		if(!this.allowed){ return false; }
		for(var i=0; i<this.priority.length; i++){
			var key = this.priority[i];
			if(typeof this.action[key] == 'function'){
				this.action[key](this.default);
			}
			this.current[key] = this.default[key];
		}
	},

	getIndex: function(){
		if(!this.allowed){ return false; }
		return window.history.length;
	},
	
	getTitle: function(){
		if(!this.allowed){ return false; }
		return wrapper_title+" ["+(this.getIndex()+1)+"]";
	},

	updateKey: function(key, value){
		if(!this.allowed){ return false; }
		if(typeof this.current[key] == 'undefined'){
			return false;
		}
		this.current[key] = value;
		window.history.replaceState(this.current, this.getTitle());
		return true;
	},

	downKey: function(key, value){
		if(!this.allowed){ return false; }
		var result = true;
		if(typeof this.current[key] == 'undefined'){
			return false;
		}
		if(this.type[key] == 'boolean'){
			if(value!=this.default[key]){
				value = this.default[key];
				result = false;
			}
		} else if(this.type[key] == 'increase'){
			if(value>=this.current[key]){
				value = this.current[key]
				result = false;
			}
		}
		this.current[key] = value;
		window.history.replaceState(this.current, this.getTitle());
		return result;
	},

	//data must be an object like default
	change: function(data, param, direction){
		if(!this.allowed){ return false; }
		if(typeof param == 'undefined'){ param = null; }
		if(typeof direction == 'undefined'){ direction = 0; }
		var record = false;
		var position = 0;
		for(var key in data){
			position = 0; //-1:back / 0:replace / 1:forward
			record = false;
			var temp = {};
			if(typeof this.type[key] != 'undefined'){
				if(this.type[key] == 'boolean'){
					if(data[key]!=this.current[key]){
						record = true;
						position = 0; //Replace
						if(this.current[key]==this.default[key]){
							position = 1; //Forward (exit default)
						} else if(data[key]==this.default[key]){
							position = -1; //Back (return default)
						}
					}
				} else if(this.type[key] == 'increase'){
					if(data[key]<0){
						data[key] = 0;
					}
					if(data[key]!=this.current[key]){
						record = true;
						position = data[key] - this.current[key]; //Forward for higher - Back for lower
					}
				}
				//0:all , 1: forceUp(>=0) , 2: forceDown(<=0)
				if((direction<0 && position>0) || (direction>0 && position<0)){
					position = 0;
					record = false;
				}
				if(record){
					if(position<0){ //Back
						this.current[key] = data[key];
						if(this.type[key] == 'boolean'){
							this.current[key] = this.default[key]
						}
						window.history.replaceState(this.current, this.getTitle());
					} else if(position==0){ //Replace
						this.current[key] = data[key];
						window.history.replaceState(this.current, this.getTitle());
					} else if(position>0){ //Forward
						if(this.type[key] == 'boolean'){
							this.current[key] = data[key];
							window.history.pushState(this.current, this.getTitle());
						} else if(this.type[key] == 'increase'){
							for(var j=0; j<position; j++){
								this.current[key] = data[key];
								window.history.pushState(this.current, this.getTitle());
							}
						}
					}
					//Record parameters
					if(typeof this.param[key] != 'object'){ this.param[key] = {}; }
					this.param[key][this.current[key]] = param;
				}
			}
		}
	},

	//Add 2 times currents to avoid exiting to easily
	start: function(){
		if(typeof window.history.pushState != 'function'){
			this.allowed = false;
			return false;
		}
		this.allowed = true;
		if(!this.started && !storage_first_launch){
			this.started = true;
			var pitch_page = getCookie(document.brunoDev+'_pitch_page');
			if(pitch_page){
				this.getItem(pitch_page);
			} else {
				this.getItem();
			}
			window.history.pushState(this.default, this.getTitle(), document.location.pathname); //Make sure we initialse the url at root to clean it
			this.openItem(true);
		}
		return this.started;
	},

	getItem: function(url){
		if(!this.allowed){ return false; }
		if(typeof url == 'undefined'){ url = false; }
		this.quick_item = false;

		if(url){
			var location_hash = url.split("#");
			if(location_hash.length == 2) {
				var hash = location_hash[1].split(/-(.*)/, 2);//url ='https://domain.com/#tasks-base64(56)';
			} else {
				hash = [];
			}
		} else {
			var hash = wrapper_hash.substr(1).split(/-(.*)/, 2);
		}
		wrapper_hash = "";
		if(hash.length==2){
			var type = hash[0];
			var id = parseInt(hash[1], 10);
			this.quick_item = Bruno.storage.getClone(type, id);
			if(!this.quick_item){
				this.quick_item = {};
			}
			this.quick_item._type = type;
		}
		return this.quick_item;
	},
	openItem: function(old, url){
		if(!this.allowed){ return false; }
		if(typeof old == 'undefined'){ old = false; }
		if(typeof url == 'undefined'){ url = false; }
		if(old){
			var item = this.quick_item;
		} else { //get url hash
			var item = this.getItem(url);
		}
		window.history.replaceState(this.current, this.getTitle(), document.location.pathname); //This is just to clean the url
		//console.log(item);
		if(typeof this.model_action[item._type] == 'function'){
			this.model_action[item._type](item);
		}
	},
	model_action: {
		pitch: function(item){
			var item = item;
			var app_generic_state_action = function(){
				if(Bruno.storage.get("pitch", item.id)){
					app_content_menu.selection("question", item.id);
				}
			}
			if(!storage_first_launch && Bruno.storage.get("pitch", item.id)){
				app_generic_state_action();
			} else {
				Bruno.storage.getLatest(false, app_generic_state_action);
			}
		},
		question: function(item){
			var item = item;
			var app_generic_state_action = function(){
				if(Bruno.storage.get("question", item.id)){
					app_content_menu.selection("answer", item.id);
				}
			}
			if(!storage_first_launch && Bruno.storage.get("question", item.id)){
				app_generic_state_action();
			} else {
				Bruno.storage.getLatest(false, app_generic_state_action);
			}
		},
		answer: function(item){
			var item = item;
			var app_generic_state_action = function(){
				var question = Bruno.storage.getParent("answer", item.id);
				if(question){
					app_content_menu.selection("answer", question.id);
				}
			}
			if(!storage_first_launch && Bruno.storage.get("answer", item.id)){
				app_generic_state_action();
			} else {
				Bruno.storage.getLatest(false, app_generic_state_action);
			}
		},
		file: function(item){
			var item = item;
			var app_generic_state_action = function(){
				if(Bruno.storage.get("question", item.id)){
					previewer(item.id);
				}
			}
			if(!storage_first_launch && Bruno.storage.get("file", item.id)){
				app_generic_state_action();
			} else {
				Bruno.storage.getLatest(false, app_generic_state_action);
			}
		},
		submenu: function(item){
			var item = item;
			var app_generic_state_action = function(){
				var arr = item._id.split("%");

				var menu = arr[0];
				var next = 1;
				if(typeof arr[1] != "undefined"){ next=arr[1]; }
				var hide = true;
				if(typeof arr[2] != "undefined"){ hide=arr[2]; }
				var param = null;
				if(typeof arr[3] != "undefined"){ param=arr[3]; }
				var animate = true;
				if(typeof arr[4] != "undefined"){ animate=arr[4]; }

				if(!storage_first_launch){
					var subm = submenu_get(menu);
					if(!subm){
						submenu_Build(menu, next, hide, param, animate);
					}
				} else {
					var model_timer = setInterval(function(menu, next, hide, param, animate){
						if(!storage_first_launch){
							clearInterval(model_timer);
							var subm = submenu_get(menu);
							if(!subm){
								submenu_Build(menu, next, hide, param, animate);
							}
						}
					}, 1000, menu, next, hide, param, animate);
				}
			}
			if(!storage_first_launch){
				app_generic_state_action();
			} else {
				Bruno.storage.getLatest(false, app_generic_state_action);
			}
		}
	},

};

var app_generic_state_garbage = app_application_garbage.add();
app_application_bruno.add(app_generic_state_garbage, 'first_launch', function() {
	if(app_generic_state.start()){
		app_application_garbage.remove(app_generic_state_garbage);
	}
});
