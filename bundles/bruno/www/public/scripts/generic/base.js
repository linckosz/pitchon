var base_content_timer;
$(window).resize(function(){
	clearTimeout(base_content_timer);
	base_content_timer = setTimeout(function(){
		$("#base_content").height(function(){
			return $(window).height() - $("#base_menu").height();
		});
		wrapper_IScroll();
	}, 50);
});
