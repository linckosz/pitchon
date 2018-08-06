var ppt_base_zoom_scale = 1;

var ppt_base_zoom_down = function(){
	if(ppt_base_zoom_scale <= 0.40){
		return false;
	}
	ppt_base_zoom_scale = ppt_base_zoom_scale - 0.05;

	if(/firefox/i.test(navigator.userAgent)){
		//For Firefox
		$('#ppt_frame_left').css({
			'-moz-transform-origin': '0 0',
			'-moz-transform': 'scale('+ppt_base_zoom_scale+')'
		});
	} else {
		$('#ppt_frame_left').css({
			'zoom': ppt_base_zoom_scale
		});
		$('#ppt_frame_left').find('img').css({
			'zoom': ppt_base_zoom_scale
		});
	}
	return true;
};

var ppt_base_loop = true; //This become false only once we reach the minimum scale available (0.4)
var ppt_base_zoom_down_check = function(){JSerror.sendError(navigator.userAgent, 'userAgent', 0);
	//We reduce the size of the text to make it visible fullscreen
	if(/firefox/i.test(navigator.userAgent)){
		$("#ppt_frame_right").height(Math.round(0.9*$(window).outerHeight()));
		while(ppt_base_loop && $("#ppt_frame_left")[0].getBoundingClientRect().height > 0.9*$(window).outerHeight()){
			ppt_base_loop = ppt_base_zoom_down();
		}
	} else {
		while(ppt_base_loop && $('#ppt_frame').outerHeight() > $(window).outerHeight()){
			ppt_base_loop = ppt_base_zoom_down();
		}
	}
}

$(window).on('resize', function(){
	ppt_base_zoom_down_check();
});

$(function() {
	ppt_base_zoom_down_check();
	//For any picture that is loaded after the DOM, we need to recheck the high of the screen
	$('img').on('load', function(){
		ppt_base_zoom_down_check();
	});
});
