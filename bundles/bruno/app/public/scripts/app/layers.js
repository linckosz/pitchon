var app_layers = true;

var app_layers_changePage = function(menu, param){
	if(typeof param === 'undefined'){ param = null; }
	if(typeof now === 'undefined'){ now = false; }
	var appear_time = 50;
	var timing = 200;
	var delay = 60;
	if(responsive.test("maxMobileL")){
		appear_time = 0;
	}
	var layer = $('#app_layers_content');
	if(!layer.html()){ timing = 0; }
	var Sequence = [
		{ e: layer, p: { opacity: 0, }, o: { duration: timing, delay: delay, } },
		{ e: layer, p: { opacity: 1, }, o: { duration: appear_time, sequenceQueue: true,
			begin: function(){
				app_layers_launchMenu(menu, param);
				wrapper_textarea();
				wrapper_IScroll();
			},
		} },
	];
	$.Velocity.RunSequence(Sequence);
	layer = null;
	delete layer;
}

var app_layers_menu = null;

var app_layers_launchMenu = function(menu, param){
	if(typeof param === 'undefined'){ param = null; }
	var layer = $('#app_layers_content');

	if(typeof window['app_layers_'+app_layers_menu+'_closePage'] === 'function'){
		window['app_layers_'+app_layers_menu+'_closePage']();
	}

	layer.recursiveEmpty();
	menu = menu.toLowerCase();

	app_layers_menu = menu;
	if($('#-app_layers_'+menu).length>0){
		var Elem = $('#-app_layers_'+menu).clone();
		Elem.prop('id', 'app_layers_'+menu);
		Elem.appendTo(layer);
		if(typeof window['app_layers_'+menu+'_launchPage'] === 'function'){
			window['app_layers_'+menu+'_launchPage'](param);
		}
		return true;
	} else {
		layer.html(Bruno.Translation.get('app', 42, 'html')); //Page not found
		return false;
	}

	layer = null;
	delete layer;

};

var app_layers_icon_source = function(style){
	if(style==2){
		return app_layers_img_pictures.src;
	} else if(style==3){
		return app_layers_img_statistics.src;
	} else if(style==4){
		return app_layers_img_survey.src;
	}
	return app_layers_img_answers.src;
};

var app_layers_icon_source30 = function(style){
	if(style==2){
		return app_layers_img_pictures30.src;
	} else if(style==3){
		return app_layers_img_statistics30.src;
	} else if(style==4){
		return app_layers_img_survey30.src;
	}
	return app_layers_img_answers30.src;
};
