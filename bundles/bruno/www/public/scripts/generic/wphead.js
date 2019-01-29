/*
var IMGpagefirst = new Image();
IMGpagefirst.src = '';

// Faire un array true/false => faire un a = !a puis recupere la source dans l'array,
// cela est pour preloader les images background au passage de la souris ou du doigt pour eviter le flash du temps de chargement
*/


$('#wphead_signout').click(function(){
	wrapper_sendAction('','post','user/signout', null, null, wrapper_signout_cb_begin, wrapper_signout_cb_complete);
});

$('#wphead_signin').click(function(){
	if(typeof account_show != 'undefined') { account_show('signin'); }
});

$('#wphead_joinus').click(function(){
	if(typeof account_show != 'undefined') { account_show('joinus'); }
});

$('#wphead_account').click(function(){
	window.location.href = wphead_link['root'];
});

$('#wphead_logo_img, #wphead_lincko').click(function(){
	$('#base_iframe_message')
		.prop('src', wphead_link['blog_prefix']+'/product'+wphead_link['blog_suffix']+'/')
		.prop('data', wphead_link['blog_prefix']+'/product'+wphead_link['blog_suffix']+'/');
	wphead_active_menu('overview');
});

$('#wphead_menu').click(function(){
	dropmenu_Build("settings", $('#wphead_dropmenu'));
});

$('#wphead_bar_blog').click(function(){
	$('#base_iframe_message')
		.prop('src', wphead_link['blog_prefix']+'/')
		.prop('data', wphead_link['blog_prefix']+'/');
	wphead_active_menu('blog');
});

$('#wphead_bar_features').click(function(){
	if(!isMobileApp(true)){
		$('#base_iframe_message')
			.prop('src', wphead_link['blog_prefix']+'/features'+wphead_link['blog_suffix']+'/')
			.prop('data', wphead_link['blog_prefix']+'/features'+wphead_link['blog_suffix']+'/');
		wphead_active_menu('features');
	}
});

$('#wphead_bar_overview').click(function(){
	if(!isMobileApp(true)){
		$('#base_iframe_message')
			.prop('src', wphead_link['blog_prefix']+'/product'+wphead_link['blog_suffix']+'/')
			.prop('data', wphead_link['blog_prefix']+'/product'+wphead_link['blog_suffix']+'/');
		wphead_active_menu('overview');
	}
});

var wphead_active_current_menu = 'overview';

var wphead_active_menu = function(menu){
	if(!isMobileApp(true)){
		if(typeof menu == 'undefined'){
			menu = wphead_active_current_menu;
		} else {
			if(wphead_active_current_menu != menu){
				$('body').scrollTop(0);
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
	} else {
		$('#base_iframe_message')
			.prop('src', '')
			.prop('data', '');
	}
}

$(window).resize(function(){
	var body_height = $(window).innerHeight();
	$('#wphead_dropmenu').css('max-height', body_height - $('#wphead_dropmenu').offset()['top']);
	//We can work with Scrolled iFrame for all browsaers, expect Safari on iOS
	//For Safari iOS we don't need to resize since we force the object to be the full size
	if(!isSafariIOS){
		$('#base_iframe_message').css('height', body_height - $('#base_iframe_message').offset()['top']);
	}
	if(typeof wrapper_IScroll == 'function'){
		wrapper_IScroll(); //Need it for dropmenu
	}
});

dropmenu_list['settings'] = $.extend(
	{
		"overview": {
			"style": "button",
			"title": Lincko.Translation.get('web', 2001, 'html'), //Overview
			"action": function(url){
				if(!isMobileApp(true) && url != $('#base_iframe_message').prop('data')){
					$('#base_iframe_message')
						.prop('src', url)
						.prop('data', url);
					wphead_active_menu('overview');
				}
			},
			"action_param": wphead_link['blog_prefix']+'/product'+wphead_link['blog_suffix']+'/',
			"class": "wphead_dropmenu_first",
		},

		"features": {
			"style": "button",
			"title": Lincko.Translation.get('web', 2002, 'html'), //Features
			"action": function(url){
				if(!isMobileApp(true) && url != $('#base_iframe_message').prop('data')){
					$('#base_iframe_message')
						.prop('src', url)
						.prop('data', url);
					wphead_active_menu('features');
				}
			},
			"action_param": wphead_link['blog_prefix']+'/features'+wphead_link['blog_suffix']+'/',
			"class": "wphead_dropmenu_first",
		},

		"blog": {
			"style": "button",
			"title": Lincko.Translation.get('web', 2003, 'html'), //Blog
			"action": function(url){
				if(!isMobileApp(true) && url != $('#base_iframe_message').prop('data')){
					$('#base_iframe_message')
						.prop('src', url)
						.prop('data', url);
					wphead_active_menu('blog');
				}
			},
			"action_param": wphead_link['blog_prefix']+'/',
			"class": "wphead_dropmenu_first",
		},

		"language": {
			"style": "next",
			"title": Lincko.Translation.get('app', 1, 'html'), //Language
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

function dropmenu_Build(menu, container) {
	if(container.length>0 && typeof dropmenu_list[menu] == "object"){
		var subm = dropmenu_list[menu];

		subm.opened = false;
		if(container.is(':visible')){
			subm.opened = true;
		}

		if(subm.opened){
			container.velocity('transition.slideLeftBigOut', 300);
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

webworker_operation.launch_account_iframe = function(lincko_signin_bg){
	$('#wphead_signin > span').velocity({backgroundColorAlpha: +lincko_signin_bg}, {delay: +lincko_signin_bg*100, duration: 200,});
};

//Google analytics
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-78242020-1', 'auto');
ga('send', 'pageview');
