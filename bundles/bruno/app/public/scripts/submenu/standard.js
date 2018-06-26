Submenu.prototype.style['title'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var title;
	var className;
	var Elem = $('#-submenu_top').clone();
	Elem.prop("id", '');
	submenu_wrapper.prepend(Elem);
	if( typeof attribute.title == "function" ){
		title = attribute.title(subm);
	} else {
		title = attribute.title;
	}
	if ("class" in attribute && typeof attribute.class == "function") {
		submenu_wrapper.find('.submenu_top').addClass(attribute.class(subm));
	} else if( "class" in attribute ){
		submenu_wrapper.find('.submenu_top').addClass(attribute['class']);
	}
	submenu_wrapper.find("[find=submenu_wrapper_title]").html(title);
	submenu_wrapper.find("[find=submenu_wrapper_back]").click(function(){
		submenu_Clean(that.layer, true);
	});
	return Elem;
};

Submenu.prototype.style['title_small'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_title_small').clone();
	Elem.prop("id", '');
	Elem.find("[find=submenu_title]").html(attribute.title);
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};

Submenu.prototype.style['button'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_button').clone();
	Elem.prop("id", '');
	Elem.find("[find=submenu_button_title]").html(attribute.title);
	var value = false;
	if ("value" in attribute) {
		if(typeof attribute.value == "function"){
			value = attribute.value(Elem, that);
		} else if(attribute.value){
			value = attribute.value;
		}
	}
	if(value){
		Elem.find("[find=submenu_button_value]").removeClass("display_none").html(value);
	} else {
		Elem.find("[find=submenu_button_value]").addClass("display_none");
	}
	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.click(attribute.action_param, function(event){
			attribute.action(Elem, that, event.data);
		});
	}
	if ("hide" in attribute) {
		if (attribute.hide) {
			Elem.click(function() {
				submenu_Clean(that.layer, true);
			});
		}
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

Submenu.prototype.style['button_delete'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_button').clone();
	Elem.prop("id", '');
	Elem.find("[find=submenu_button_title]").addClass("submenu_bottom_deletion_button").html(attribute.title);
	Elem.find("[find=submenu_button_value]").recursiveRemove();
	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.find("[find=submenu_button_title]").click(attribute.action_param, function(event){
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

Submenu.prototype.style['info'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_info').clone();
	Elem.prop("id", '');
	Elem.find("[find=submenu_info_title]").html(attribute.title);
	if ("value" in attribute) {
		Elem.find("[find=submenu_info_value]").html(attribute.value);
	} else {
		Elem.find("[find=submenu_info_value]").addClass("display_none");
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

Submenu.prototype.style['small_button'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_small_button').clone();
	Elem.prop("id", '');
	Elem.find("[find=submenu_small_button_title]").html(attribute.title);
	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.click(attribute.action_param, function(event){
			attribute.action(Elem, that, event.data);
		});
	}
	if ("hide" in attribute) {
		if (attribute.hide) {
			Elem.click(function() {
				submenu_Clean(that.layer, true);
			});
		}
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

Submenu.prototype.style['space'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_space').clone();
	Elem.prop("id", '');
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	if ("value" in attribute) {
		Elem.css('height', attribute.value);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};

Submenu.prototype.style['next'] = function(submenu_wrapper, subm) {
	var attribute = subm.attribute;
	var Elem = $('#-submenu_next').clone();
	var that = subm;
	Elem.prop("id", '');
	if ("value" in attribute) {
		if(typeof attribute.value == "function"){
			var value = attribute.value(Elem, that);
			Elem.find("[find=submenu_next_value]").html(value);
		} else {
			Elem.find("[find=submenu_next_value]").html(attribute.value);
		}
	}
	if (!("action_param" in attribute)) {
		attribute.action_param = null;
	}
	if ("next" in attribute) {
		if (attribute.next in submenu_list) {
			if (typeof attribute.keep_title != "boolean" || !attribute.keep_title) {
				for (var att in submenu_list[attribute.next]) {
					next_attribute = submenu_list[attribute.next][att];
					if ("style" in next_attribute && "title" in next_attribute) {
						if (next_attribute.style == "title") {
							attribute.title = next_attribute.title;
						}
					}
				}
			}
			var action_param = attribute.action_param;
			if(typeof attribute.action_param == "function"){
				action_param = attribute.action_param(Elem, that);
			}
			Elem.click(action_param, function(event) {
				$.each(that.Wrapper().find('.submenu_deco_next'), function() {
					$(this).removeClass('submenu_deco_next');
				});
				if(submenu_Build(attribute.next, that.layer + 1, true, event.data)) {
					$(this).addClass('submenu_deco_next');
				}

			});
		}
	}
	Elem.find("[find=submenu_next_title]").html(attribute.title);
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};

Submenu.prototype.style['input_text_flat'] = function(submenu_wrapper, subm) {
	var attribute = subm.attribute;
	var Elem = $('#-submenu_input_flat').clone();
	var that = subm;
	Elem.prop("id", '');
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	Elem.find("[find=submenu_title]").html(attribute.title);
	if ("value" in attribute) {
		if(typeof attribute.value == "function"){
			var value = attribute.value(Elem, that);
			Elem.find("[find=submenu_value]").val(value);
		} else {
			Elem.find("[find=submenu_value]").val(attribute.value);
		}
	}

	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.find("[find=submenu_value]").on({
			focusin: function(){ attribute.action(Elem, that) },
			focusout: function(){ attribute.action(Elem, that, true) },
			change: function(){ attribute.action(Elem, that) },
			copy: function(){ attribute.action(Elem, that) },
			paste: function(){ attribute.action(Elem, that, true) },
			cut: function(){ attribute.action(Elem, that, true) },
			keyup: function(event) {
				if (event.which != 13) {
					attribute.action(Elem, that);
				} else {
					attribute.action(Elem, that, true);
				}
			},
		});
	}
	Elem.on('click', function(){
		Elem.find("[find=submenu_value]").focus();
	});
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};

Submenu.prototype.style['picture'] = function(submenu_wrapper, subm) {
	var attribute = subm.attribute;
	var that = subm;
	var Elem = $('#-submenu_picture').clone();
	Elem.prop("id", '');
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.click(attribute.action_param, function(event){
			attribute.action(Elem, that, event.data);
		});
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	Elem.find("[find=submenu_title]").html(attribute.title);
	//Value must be an URL of a picture
	Elem.find("[find=submenu_value]").attr('src', wrapper_neutral.src);
	if ("value" in attribute) {
		if(typeof attribute.value == "function"){
			var value = attribute.value(Elem, that);
			Elem.find("[find=submenu_value]").attr('src', value);
		} else {
			Elem.find("[find=submenu_value]").attr('src', attribute.value);
		}
	}
	
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
}

Submenu.prototype.style['radio'] = function(submenu_wrapper, subm) {
	var attribute = subm.attribute;
	var that = subm;
	var Elem = $('#-submenu_radio').clone();
	var selected = false;
	Elem.prop("id", '');
	Elem.find("[find=submenu_radio_title]").html(attribute.title);
	if ("selected" in attribute) {
		if (attribute.selected) {
			Elem.find("[find=submenu_radio_check]").removeClass('visibility_hidden');
		} else {
			Elem.find("[find=submenu_radio_check]").addClass('visibility_hidden');
		}
	}

	var select_id = subm.id+"_"+md5(Math.random());
	var select_elem = Elem.find("[find=submenu_radio_value]");
	select_elem.prop("id", select_id);
	app_application_bruno.add(select_id, "form_radio", function(){
		var Elem = subm.action_param[0];
		var attribute = subm.action_param[1];
		if (attribute.selected) {
			Elem.find("[find=submenu_radio_check]").removeClass('visibility_hidden');
		} else {
			Elem.find("[find=submenu_radio_check]").addClass('visibility_hidden');
		}
	}, [Elem, attribute] );

	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.click(attribute.action_param, function(event){
			attribute.action(Elem, that, event.data);
		});
	}
	if ("hide" in attribute) {
		if (attribute.hide) {
			Elem.click(function() {
				submenu_Clean(that.layer, true);
			});
		}
	}
	if ("value" in attribute) {
		if(typeof attribute.value == 'function'){
			Elem.find("[find=submenu_radio_text]").html(attribute.value());
		} else { 
			Elem.find("[find=submenu_radio_text]").html(attribute.value);
		}
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

Submenu.prototype.style['input_hidden'] = function(submenu_wrapper, subm) {
	var attribute = subm.attribute;
	var that = subm;
	var Elem = $('#-submenu_input').clone();
	var Input = $('<input type="hidden" find="submenu_input" />');
	Elem.prop("id", '');
	Elem.find("[find=submenu_title]").html(attribute.title);
	Elem.prop('for', attribute.name);
	Input.prop('name', attribute.name);
	Elem.append(Input);
	if ("value" in attribute) {
		Elem.find("[find=submenu_input]").prop('value', attribute.value);
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

Submenu.prototype.style['input_text'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_input').clone();
	var Input = $('<input type="text" find="submenu_input" class="selectable" />');
	Elem.prop("id", '');
	Elem.find("[find=submenu_title]").html(attribute.title);
	Elem.prop('for', attribute.name);
	Input.prop('name', attribute.name);
	Elem.append(Input);
	if ("value" in attribute) {
		if(typeof attribute.value == "function"){
			var value = attribute.value(Elem, that);
			Elem.find("[find=submenu_input]").prop('value', value);
		} else {
			Elem.find("[find=submenu_input]").prop('value', attribute.value);
		}
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

Submenu.prototype.style['input_textarea'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_input').clone();
	Elem.prop("id", '');
	var Value = '';
	if ("value" in attribute) {
		Value = attribute.value;
	}
	var Input = $('<textarea find="submenu_input_textarea" class="selectable"></textarea>'); //toto
	Input.html(Value); //toto
	Elem.find("[find=submenu_title]").html(attribute.title);
	Elem.prop('for', attribute.name);
	Input.prop('name', attribute.name);
	Elem.append(Input);
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	return Elem;
};

Submenu.prototype.style['select_multiple'] = function(submenu_wrapper, subm) {
	var attribute = subm.attribute;
	var Elem = $('#-submenu_select').clone();
	var that = subm;
	Elem.prop("id", '');
	if ("value" in attribute) {
		Elem.find("[find=submenu_select_value]").html(attribute.value);
	}
	if (!attribute["param"]) {
		attribute.param = null;
	}
	if ("next" in attribute) {
		if (attribute.next in submenu_list) {
			var next_id = subm.id+"_"+md5(Math.random());
			var next_elem = Elem.find("[find=submenu_select_value]");
			next_elem.prop("id", next_id);
			
			app_application_bruno.add(next_id, "select_multiple", function(){
				var Num = 0;
				for (var att in this.action_param) {
					var next_attribute = this.action_param[att];
					if ("style" in next_attribute && "title" in next_attribute) {
						if (next_attribute.style == "title") {
							attribute.title = next_attribute.title;
						}
					}
					if ("selected" in next_attribute) {
						if (next_attribute.selected) {
							Num++;
						}
					}
				}
				var next_id = $('#'+this.id);
				next_id.html(Num);
			}, submenu_list[attribute.next]);
			Elem.click(function() {
				$.each(that.Wrapper().find('.submenu_deco_next'), function() {
					$(this).removeClass('submenu_deco_next');
				});
				if (submenu_Build(attribute.next, that.layer + 1, true, attribute.param)) {
					$(this).addClass('submenu_deco_next');
				}
			});
		}
	}
	Elem.find("[find=submenu_select_title]").html(attribute.title);
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	app_application_bruno.prepare("select_multiple", true);
	return Elem;
};

Submenu.prototype.style['bottom_button'] = function(submenu_wrapper, subm) {
	var that = subm;
	var attribute = subm.attribute;
	var Elem = $('#-submenu_bottom').clone();
	Elem.prop("id", '');
	submenu_wrapper.find("[find=submenu_wrapper_bottom]").addClass('submenu_bottom');
	if ("action" in attribute) {
		if (!("action_param" in attribute)) {
			attribute.action_param = null;
		}
		Elem.find("[find=submenu_bottom_button]").click(attribute.action_param, function(event){
			attribute.action(Elem, that, event.data);
		});
	}
	if ("hide" in attribute) {
		if (attribute.hide) {
			Elem.find("[find=submenu_bottom_button]").click(function() {
				submenu_Clean(that.layer, true);
			});
		}
	}
	Elem.find("[find=submenu_bottom_title]").html(attribute.title);
	if ("class" in attribute) {
		Elem.addClass(attribute['class']);
	}
	if ("now" in attribute && typeof attribute.now == "function") {
		attribute.now(Elem, that);
	}
	if (submenu_wrapper.find("[find=submenu_wrapper_bottom]").find(".submenu_bottom_cell").length == 0) {
		submenu_wrapper.find("[find=submenu_wrapper_bottom]").append(Elem);
	} else {
		submenu_wrapper.find("[find=submenu_wrapper_bottom]").find(".submenu_bottom_cell").append(Elem.children());
	}
	return Elem;
};
