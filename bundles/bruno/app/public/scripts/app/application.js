//This variable must be loaded before because other JS files will record their IDs inside
var app_application_bruno = {
	_elements: {},
	_functions: { all:[], fields:{}, }, //Only register functions before loading is done, after it will be prohibited
	_fields: {}, //This must be setup previously somewhere, for instance storage.js

	/*
		Different ways to register an item (HTML element or function):
		app_application_bruno.add( (function)action, *(string|array)range ); => it will repeat the action periodically (it's a setTimeout)
		app_application_bruno.add( (string)id, (string|array)range, (function)action, *(function)deletion );
		app_application_bruno.add( (string)id, (string|array)range, (object)action{  (function)action, *(function)deletion	} );

		(string|array)range: It helps to launch action() only if the update function calls one of it's name.

		IMPORTANT => Be aware that the same element can cumulate multiple declaration, it can only overwrite
	*/

	clean: function(id){
		this._elements[id] = null;
		delete this._elements[id];
	},

	add: function(id, range, action, action_param, exists, exists_param, deletion, deletion_param){
		var object = false;
		//Assign id
		var item = {
			id: null,							//Inside a callback we can get the id by doing this.id
			range: {},							//React to which category (all for empty)
			action: function(){},				//If HTML exists and its related objects, we take action
				/*
					Access parameters like:
					this.action_param
					this.action_param[0]
				*/
			action_param: null,					//optional parameters =>
				/*
					item_id
					[item_id, item_type]
				*/
			exists: function(){ return true; },	//Check exitistance of any related object
			exists_param: null,					//optional parameters
			deletion: function(){},				//If HTML element or related objects are missing, we take a deletion action
			deletion_param: null,				//optional parameters
			timer: false,						//Avoid to run 2 actions at the same time
		};

		if(typeof action_param !== 'undefined'){ item.action_param = action_param; }
		if(typeof exists_param !== 'undefined'){ item.exists_param = exists_param; }
		if(typeof deletion_param !== 'undefined'){ item.deletion_param = deletion_param; }

		//Assign Exists, Action and Deletion methods
		if(typeof id === 'string' || typeof id === 'number'){
			item.id = id;
			if(typeof action === 'function'){
				item.action = action;
				object = 'element';
			}
			if(typeof exists === 'function'){
				item.exists = exists;
				object = 'element';
			}
			if(typeof deletion === 'function'){
				item.deletion = deletion;
				object = 'element';
			}
		} else if(typeof id === 'function' && !wrapper_load_progress.done){
			//We do not record functions after DOM load to avoid to multipliate a lot some functions because each function is anonymous and we cannot classified them.
			//It's something we migth work on to also avoid the same function registered in 2 differents fields
			object = 'function';
		}

		if(object){
			//Assign range scope
			if(typeof range === 'string'){
				range = range.toLowerCase();
				range = [range];
			}
			if($.type(range) === 'object' || $.type(range) === 'array'){
				for(var i in range){
					range[i] = range[i].toLowerCase();
					item.range[range[i]] = true;
				}
			}
			
			if(object === 'element'){
				var range_key = md5(range); //It helps to instance multiple listener with different range on a same HTML element
				if(typeof this._elements[''+id] == 'undefined'){
					this._elements[''+id] = {};
				}
				this._elements[''+id][range_key] = item;
			} else if(object === 'function'){
				//Has to register the function as anonyme, if not the value of "this" inside the function(especially for objects) can be different,
				//using "that = id" helps to keep the orginal value of this as it should be
				//NOTE: Be careful the use of this inside methods, it tends to be "window" object
				var that = id;
				if($.isEmptyObject(item.range)){
					this._functions.all.push(function(){ that(); });
				} else {
					for(var field in item.range){
						if(typeof this._functions.fields[field] === 'undefined'){
							this._functions.fields[field] = [];
						}
						this._functions.fields[field].push(function(){ that(); });
					}
				}
			}
			return true;
		}
		return false;
	},

	/*
		It updates all elements and functions that are linked to any _fields
	*/
	update: function(procedural){
		if(typeof procedural != 'boolean'){ procedural = false; }
		var Elem;

		//Have to scan object, not fields to insure we do not launch many times the same function
		if(!$.isEmptyObject(this._fields)){

			//First we scan all HTML elements
			for(var Elem_id in this._elements){
				Elem = $('#'+Elem_id);
				for(var range_key in this._elements[Elem_id]){
					if(Elem.length <= 0 || !this._elements[Elem_id][range_key].exists()){ //delete the element if it doesn't exist on DOM
						this._elements[Elem_id][range_key].deletion();
						delete this._elements[Elem_id][range_key];
					} else {
						if(typeof this._elements[Elem_id][range_key].range == 'object'){
							for(var field in this._fields){
								if(
									   typeof this._elements[Elem_id][range_key].range[field] != 'undefined'
									|| typeof this._elements[Elem_id][range_key].range[field.replace(/_\d+$/, '')] != 'undefined'
								){
									try {
										clearTimeout(this._elements[Elem_id][range_key].timer);
										if(procedural){ //Run immediatly (but can see screen little freeze)
											this._elements[Elem_id][range_key].action();
										} else { //It will wait until the parent scope script is finished (less screen freeze)
											this._elements[Elem_id][range_key].timer = setTimeout(function(Elem_id, range_key){
												if(app_application_bruno._elements[Elem_id] && app_application_bruno._elements[Elem_id][range_key]){
													app_application_bruno._elements[Elem_id][range_key].action();
												} else {
													//console.log("application => "+Elem_id);
													//JSerror.sendError(Elem_id, 'app_application_bruno._elements[Elem_id][range_key] does not exists', 0);
												}
											}, 0, Elem_id, range_key);
										}
									} catch(event) {
										var instance = "Other";
										if (event instanceof TypeError) {
											instance = "TypeError";
										} else if (event instanceof RangeError) {
											instance = "RangeError";
										} else if (event instanceof EvalError) {
											instance = "EvalError";
										} else if (event instanceof ReferenceError) {
											instance = "ReferenceError";
										}
										var message = "";
										if(event.message){ message = event.message; }
										var name = "";
										if(event.name){ name = event.name; }
										var fileName = "";
										if(event.fileName){ fileName = event.fileName; }
										var lineNumber = 0;
										if(event.lineNumber){ lineNumber = event.lineNumber; }
										var columnNumber = 0;
										if(event.columnNumber){ columnNumber = event.columnNumber; }
										var stack = "";
										if(event.stack){
											stack = event.stack;
										}
										JSerror.sendError(this._elements[Elem_id][range_key].action, 'app_application_bruno.update => this._elements["'+Elem_id+'"]["'+range_key+'"].action() => '+field, 0);
										JSerror.sendError(stack, fileName+" "+message, lineNumber, columnNumber, instance+" "+name);
									}
									break; //Do not launch more than one time if ever launched
								}
							}
						}
					}
				}
				if(Elem.length <= 0){
					delete this._elements[Elem_id];
				}
			}

			Elem = null;
			delete Elem;

			//Scan all functions
			for(var field in this._fields){
				if(typeof this._functions.fields[field] === 'object'){
					for(var i in this._functions.fields[field]){
						try {
							this._functions.fields[field][i]();
						} catch(event) {
							var instance = "Other";
							if (event instanceof TypeError) {
								instance = "TypeError";
							} else if (event instanceof RangeError) {
								instance = "RangeError";
							} else if (event instanceof EvalError) {
								instance = "EvalError";
							} else if (event instanceof ReferenceError) {
								instance = "ReferenceError";
							}
							var message = "";
							if(event.message){ message = event.message; }
							var name = "";
							if(event.name){ name = event.name; }
							var fileName = "";
							if(event.fileName){ fileName = event.fileName; }
							var lineNumber = 0;
							if(event.lineNumber){ lineNumber = event.lineNumber; }
							var columnNumber = 0;
							if(event.columnNumber){ columnNumber = event.columnNumber; }
							var stack = "";
							if(event.stack){
								stack = event.stack;
							}
							JSerror.sendError(this._functions.fields[field][i], 'app_application_bruno.update => this._functions.fields["'+field+'"]['+i+']()', 0);
							JSerror.sendError(stack, fileName+" "+message, lineNumber, columnNumber, instance+" "+name);
						}
					}
				}
			}

			//Only then we launch all functions registered in all
			//But do it only if the local database has been updated, if not there is no use to launch those functions
			for(var i in this._functions.all){
				try {
					this._functions.all[i]();
				} catch(event) {
					var instance = "Other";
					if (event instanceof TypeError) {
						instance = "TypeError";
					} else if (event instanceof RangeError) {
						instance = "RangeError";
					} else if (event instanceof EvalError) {
						instance = "EvalError";
					} else if (event instanceof ReferenceError) {
						instance = "ReferenceError";
					}
					var message = "";
					if(event.message){ message = event.message; }
					var name = "";
					if(event.name){ name = event.name; }
					var fileName = "";
					if(event.fileName){ fileName = event.fileName; }
					var lineNumber = 0;
					if(event.lineNumber){ lineNumber = event.lineNumber; }
					var columnNumber = 0;
					if(event.columnNumber){ columnNumber = event.columnNumber; }
					var stack = "";
					if(event.stack){
						stack = event.stack;
					}
					JSerror.sendError(this._functions.all[i], 'app_application_bruno.update => this._functions.all['+i+']()', 0);
					JSerror.sendError(stack, fileName+" "+message, lineNumber, columnNumber, instance+" "+name);
				}
			}
		}

		for(var field in this._fields){
			delete this._fields[field];
		}
		
		return true;
	},

	/*
		Prepare a list of element to be triggered by a timer (every 15s)
		fields: [defaults: false]
			- false: Do not add any type of element to be triggered
			- true: Trigger all elements
			- 'tasks': Trigger all elements that listen to the type tasks, or a single tasks ID (do not involve parents and children)
			- 'tasks_20': Trigger all elements listening to tasks 20 only (IMPORTANT: using ID will trigger parents and children)
			-  ['projects', 'tasks_20']: Use an array to combine
		update: [default: false]
			- false: Do nothing, just wait for the timer (every 15s) to launch an update
			- true: Force update

		NOTE: Because JS is not ready yet (obverse() is too new) to observe any object change, we have to add it manually.
	*/
	prepare: function(fields, update, procedural){
		if(typeof fields == 'undefined'){ fields = false; }
		if(typeof update != 'boolean'){ update = false; }
		if(typeof procedural != 'boolean'){ procedural = false; }
		var field;
		if(typeof fields == 'string' || typeof fields == 'number'){
			if(typeof this._fields[fields] != 'object'){
				this._fields[fields] = true;
			}
		} else if(typeof fields == 'object'){
			for(var i in fields){
				if(typeof fields[i] == 'string' || typeof fields[i] == 'number'){
					if(typeof this._fields[fields[i]] != 'object'){
						this._fields[fields[i]] = true;
					}
				}
			}
		} else if(fields === true){
			//Prepare all to be updated
			for(var id in this._elements){
				for(var range_key in this._elements[id]){
					for(var field in this._elements[id][range_key].range){
						if(typeof this._fields[fields] != 'object'){
							this._fields[field] = true;
						}
					}
				}
			}
			for(var field in this._functions.fields){
				if(typeof this._fields[fields] != 'object'){
					this._fields[field] = true;
				}
			}
		}

		//Force to update if update at true
		if(update){
			this.update(procedural);
		}
	},

};

var app_application_garbage = {
	add: function(id){ //Better to be unique
		if(typeof id == 'undefined'){ id = md5(Math.random()); }
		if($("#app_application_garbage_"+id).length > 0){
			return false;
		}
		var span = $('<span/>');
		span.prop("id", "app_application_garbage_"+id);
		span.appendTo($("#app_application_garbage"));
		return "app_application_garbage_"+id;
	},

	remove: function(garbage_id){
		//Check that it's a garbage before to remove
		if(garbage_id.indexOf('app_application_garbage_')===0){
			$("#"+garbage_id).recursiveRemove();
		}
	},
}


var app_application_action = function(action, info){
	if(action > 0){
		action = -action; //Negate records from Front
	}
	if(typeof info != 'undefined'){
		wrapper_sendAction({action: action, info: info}, 'post', 'api/info/action');
	} else {
		wrapper_sendAction({action: action}, 'post', 'api/info/action');
	}
}
/*
	iscroll is disabled between mousedown and mouseup to prevent scrolling during highlighting
	if options.click is set to true, it is set to false during this time to prevent unwanted click on mouseup
*/
var iscroll_disabled = {};
var application_iscroll_text_selection = function(event){
	//This helps to save a little CPU becuse parents() is very heavy
	if(typeof $(this).data('iscroll_id') == 'undefined'){
		var iscroll_id = $(this).parents(".overthrow").prop("id");//find iScroll id
		$(this).data('iscroll_id', iscroll_id);
	} else {
		var iscroll_id = $(this).data('iscroll_id');
	}
	var scroll = myIScrollList[iscroll_id];
	if(scroll){
		if(event.type=='focus' || event.type=='focusin'){
			iscroll_disabled[iscroll_id] = true;
			scroll.disable();//disables the iScroll
			if(device_type()!='computer' || supportsTouch){
				if(event.type=='focusin'){
					//Some timing are necessary while the keyboard is showing on mobile, usually < 500ms
					var elem = $(this).get(0);
					setTimeout(function(scroll, elem){
						scroll.scrollToElement(elem, 100);
					}, 100, scroll, elem);
					setTimeout(function(scroll, elem){
						scroll.scrollToElement(elem, 100);
					}, 400, scroll, elem);
					setTimeout(function(scroll, elem){
						scroll.scrollToElement(elem, 100);
					}, 600, scroll, elem);
				}
			}
		} else {
			iscroll_disabled[iscroll_id] = false;
			myIScrollList[iscroll_id].enable();//enables the iScroll
		}
	}
};
$("body").on("focus focusin blur focusout", ".selectable", application_iscroll_text_selection);
/*
	This re-enable the scrolling by the mouse wheel.
	The disavantgae is that text is not seletctable anymore, must focusout and focusin again to re-enable it.
*/
$("body").on("mousewheel", function(event) {
	for(var iscroll_id in iscroll_disabled){
		if(myIScrollList[iscroll_id]){
			iscroll_disabled[iscroll_id] = false;
			myIScrollList[iscroll_id].enable();//enables the iScroll
		}
	}
});

app_application_submenu_block_mousedown = false;
$('#app_application_submenu_block')
	.mousedown(function(event){
		if($(event.target).prop('id') == 'app_application_submenu_block'){
			app_application_submenu_block_mousedown = true;
		}
	})
	.mouseup(function(event){
		if(app_application_submenu_block_mousedown && $(event.target).prop('id') == 'app_application_submenu_block'){
			submenu_Hideall();
		}
		app_application_submenu_block_mousedown = false;
	});

function app_application_submenu_position() {
	var Elem = $('#app_application_submenu_block');
	var submenu_top = 0;
	var submenu_left = 0;
	var hidden = false;
	if(responsive.test("minDesktop")){
		submenu_top = 48;
	}
	if(responsive.test("minMobileL")){
		Elem
		.height(function(){
			return $(window).height() - submenu_top;
		})
		.width(function(){
			return $(window).width();
		});
	} else {
		Elem.css({height: '100%', width: '100%'});
	}
	if(Elem.css('display') === 'none'){
		hidden = true;
		Elem.show();
	}
	Elem.offset({ top: submenu_top });
	if(hidden){
		Elem.hide();
	}
}
app_application_submenu_position();
var app_application_submenu_position_timer;
$(window).resize(function(){
	clearTimeout(app_application_submenu_position_timer);
	app_application_submenu_position_timer = setTimeout(app_application_submenu_position, wrapper_timeout_timer);
});

var app_application_save_page = function(type, id){
	var url = Bruno.storage.geHash(type, id);
	if(url){
		setCookie(document.brunoDev+'_pitch_page', url);
	} else {
		setCookie(document.brunoDev+'_pitch_page', false, -1);
	}
}

wrapper_load_progress.add_cb_complete(function(){
	$('body').addClass('base_color_bg_main_gradient customize');
	app_application_bruno.prepare("hide_progress_wall", true);
});

var app_application_mask_timer = null;
var app_application_mask_show = function(){
	$('#app_application_mask').removeClass("display_none visibility_hidden");
	clearTimeout(app_application_mask_timer);
	setTimeout(function(){
		$('#app_application_mask').css("background-image", "url('"+app_application_cloud.src+"')");
	}, 10);
};

var app_application_mask_hide = function(){
	$('#app_application_mask').css("background-image", "url('"+wrapper_neutral.src+"')");
	clearTimeout(app_application_mask_timer);
	app_application_mask_timer = setTimeout(function(){
		$('#app_application_mask').addClass("display_none visibility_hidden");
	}, 500);
};

JSfiles.finish(function(){
	app_application_action(1, wrapper_user_info); //Logged
	//Update every 15s automatically
	app_application_bruno.prepare(true, true);
	setInterval(function(){
		app_application_bruno.prepare(false, true);
	}, 15000); //15s Production

	//Onboarding
	$('#app_application_mask').on('click', function(event){
		$(document.body).trigger('click.bubble');
		event.stopPropagation();
		if(Bruno.storage.onboarding_opened){
			return false;
		}
		clearTimeout(app_application_mask_hide);
		app_application_mask_timer = setTimeout(app_application_mask_hide, 2000);
		return false;
	});
});
