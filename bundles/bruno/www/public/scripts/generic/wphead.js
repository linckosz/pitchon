/*
var IMGpagefirst = new Image();
IMGpagefirst.src = '';

// Faire un array true/false => faire un a = !a puis recupere la source dans l'array,
// cela est pour preloader les images background au passage de la souris ou du doigt pour eviter le flash du temps de chargement
*/

$('#wphead_signin').click(function(){
	window.location.href = location.protocol+'//app.'+document.domainRoot;
});

$('#wphead_account').click(function(){
	window.location.href = location.protocol+'//app.'+document.domainRoot;
});

$('#wphead_logo_img, #wphead_title').click(function(){
	wphead_active_menu('overview', true);
});

$('#wphead_menu').click(function(){
	dropmenu_Build("settings", $('#wphead_dropmenu'));
});

$('#wphead_bar_overview').click(function(){
	wphead_active_menu('overview', true);
});

$('#wphead_bar_features').click(function(){
	wphead_active_menu('features', true);
});

$('#wphead_bar_pricing').click(function(){
	wphead_active_menu('pricing', true);
});

$('#wphead_bar_about').click(function(){
	wphead_active_menu('about', true);
});

$('#base_content').click(function(){
	dropmenu_Build("settings", $('#wphead_dropmenu'), true); //Only close
});


var wphead_active_menu = function(menu, redirect){
	if(typeof redirect == 'undefined'){
		redirect = false;
	}
	if(typeof menu == 'undefined'){
		menu = wphead_active_current_menu;
	} else {
		if(wphead_active_current_menu == menu){
			redirect = false;
		}
		wphead_active_current_menu = menu;
	}
	
	//Bar menu
	$('.table_cell_center_link_active').removeClass('table_cell_center_link_active');
	if($('#wphead_bar_'+menu).length>0){
		$('#wphead_bar_'+menu).addClass('table_cell_center_link_active');
	}
	
	//Dropdown menu
	$('.wphead_dropmenu_first_active').removeClass('wphead_dropmenu_first_active');
	if($('#dropmenu_settings_'+menu).length>0){
		$('#dropmenu_settings_'+menu).addClass('wphead_dropmenu_first_active');
	}

	if(redirect){
		if(!menu){
			menu = 'overview';
		}
		window.location.href = '/'+menu;
	}
}
wphead_active_menu();

$(window).resize(function(){
	var body_height = $(window).innerHeight();
	$('#wphead_dropmenu').css('max-height', body_height - $('#wphead_dropmenu').offset()['top']);
	if(typeof wrapper_IScroll == 'function'){
		wrapper_IScroll(); //Need it for dropmenu
	}
});

dropmenu_list['settings'] = $.extend(
	{
		"overview": {
			"style": "button",
			"title": Bruno.Translation.get('www', 1, 'html'), //Overview
			"action": function(url){
				wphead_active_menu('overview', true);
			},
			"class": "wphead_dropmenu_first",
		},

		"features": {
			"style": "button",
			"title": Bruno.Translation.get('www', 2, 'html'), //Features
			"action": function(url){
				wphead_active_menu('features', true);
			},
			"class": "display_none wphead_dropmenu_first", //toto => remove display_none
		},

		"pricing": {
			"style": "button",
			"title": Bruno.Translation.get('www', 3, 'html'), //Pricing
			"action": function(url){
				wphead_active_menu('pricing', true);
			},
			"class": "display_none wphead_dropmenu_first", //toto => remove display_none
		},

		"about": {
			"style": "button",
			"title": Bruno.Translation.get('www', 5, 'html'), //About
			"action": function(url){
				wphead_active_menu('about', true);
			},
			"class": "display_none wphead_dropmenu_first", //toto => remove display_none
		},

		"language": {
			"style": "next",
			"title": Bruno.Translation.get('www', 4, 'html'), //Language
			"next": "language",
			"value": dropmenu_language_full,
			"class": "wphead_dropmenu_first",
		},

		"hide": {
			"style": "hide",
			"action": function(){
				//Help to high light the menu
				wphead_active_menu();
			},
		},
	},
	dropmenu_list['settings']
);

function dropmenu_Build(menu, container, only_close) {
	if(typeof only_close == "undefined"){ only_close = false; }
	if(container.length>0 && typeof dropmenu_list[menu] == "object"){
		var subm = dropmenu_list[menu];

		subm.opened = false;
		if(container.is(':visible')){
			subm.opened = true;
		}

		if(subm.opened){
			container.velocity('transition.slideLeftBigOut', 300);
		} else if(only_close){
			return false;
		} else {
			//Build the menu
			for (var att in subm) {
				if(
					   typeof subm[att] == "object"
					&& typeof subm[att]['style'] != "undefined"
					&& typeof dropmenu_select[subm[att]['style']] == "function"
				){
					dropmenu_select[subm[att]['style']](subm[att], container, menu, att);
				}
			}
			container.velocity('transition.slideLeftBigIn', 300, function(){
				$(window).resize();
			});
		}

		return !subm.opened;

	}
	return false;
}

var dropmenu_select = {

	button: function(tab, container, menu, att) {
		if($('#dropmenu_'+menu+'_'+att).length>0){
			return $('#dropmenu_'+menu+'_'+att);
		}
		var Elem = $('<div>');
		Elem.prop('id', 'dropmenu_'+menu+'_'+att);
		Elem.off('click');
		if ("action" in tab) {
			if (!("action_param" in tab)) {
				tab.action_param = null;
			}
			Elem.click([tab, container, menu], function(event){
				var tab = event.data[0];
				var container = event.data[1];
				var menu = event.data[2];
				tab.action(tab.action_param);
				container.velocity('slideUp');
			});
		}
		if ("class" in tab) {
			Elem.addClass(tab["class"]);
		}
		if ("title" in tab) {
			Elem.html(tab["title"]);
		}
		container.append(Elem);
		return Elem;
	},
	
	next: function(tab, container, menu, att) {
		if($('#dropmenu_'+menu+'_'+att).length>0){
			return $('#dropmenu_'+menu+'_'+att);
		}
		var Elem = $('<div>');
		Elem.prop('id', 'dropmenu_'+menu+'_'+att);
		var Elem_next = $('<div>');
		Elem_next.hide();
		if ("next" in tab) {
			if (!("action_param" in tab)) {
				tab.action_param = null;
			}
			Elem.click([tab, container, menu, Elem_next], function(event){
				var tab = event.data[0];
				var container = event.data[1];
				var menu = event.data[2];
				var Elem_next = event.data[3];
				var opened = dropmenu_Build(tab.next, Elem_next);
				if(opened){
					$(this).addClass('wphead_dropmenu_first_opened');
				} else {
					$(this).removeClass('wphead_dropmenu_first_opened');
				}
			});
		}
		if ("class" in tab) {
			Elem.addClass(tab["class"]);
		}
		if ("title" in tab) {
			Elem.html(tab["title"]);
		}
		container.append(Elem);
		container.append(Elem_next);
		return Elem;
	},

	radio: function(tab, container, menu, att) {
		if($('#dropmenu_'+menu+'_'+att).length>0){
			return $('#dropmenu_'+menu+'_'+att);
		}
		var Elem = $('<div>');
		Elem.prop('id', 'dropmenu_'+menu+'_'+att);
		Elem.off('click');
		if ("action" in tab) {
			if (!("action_param" in tab)) {
				tab.action_param = null;
			}
			Elem.click([tab, container, menu], function(event){
				var tab = event.data[0];
				var container = event.data[1];
				var menu = event.data[2];
				tab.action(tab.action_param);
				container.velocity('slideUp');
			});
		}
		if ("class" in tab) {
			Elem.addClass(tab["class"]);
		}

		//Select image
		var image = $('<img>');
		image.attr('src', dropmenu_selected_img.src);
		if(!tab.selected){
			image.css('visibility', 'hidden');
		} else {
			Elem.off('click');
		}
		

		if ("title" in tab) {
			Elem.html(tab["title"]);
		}
		Elem.append(image);
		container.append(Elem);
		return Elem;
	},

	hide: function(tab, container, menu, att) {
		if ("action" in tab) {
			tab.action();
		}
		return false;
	}

}
