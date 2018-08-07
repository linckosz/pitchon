var ppt_base_zoom_scale = 1;

var ppt_base_zoom_down = function(){
	if(ppt_base_zoom_scale <= 0.40){
		return false;
	}
	ppt_base_zoom_scale = ppt_base_zoom_scale - 0.05;

	$('#ppt_frame_left').css({
		'font-size': (Math.round(30 * ppt_base_zoom_scale)/10)+'vw',
		'font-size': (Math.round(30 * ppt_base_zoom_scale)/10)+'vmin',
	});
	//Reduce picture fater than text
	$('#ppt_frame_left .ppt_qanda_questions_picture_img').css({
		'max-height': Math.round(20 * ppt_base_zoom_scale * ppt_base_zoom_scale)+'vw',
		'max-height': Math.round(20 * ppt_base_zoom_scale * ppt_base_zoom_scale)+'vmin',
	});
	$('#ppt_question_picture').css({
		'max-height': Math.round(256 * ppt_base_zoom_scale * ppt_base_zoom_scale)+'px',
		'max-height': Math.round(26 * ppt_base_zoom_scale * ppt_base_zoom_scale)+'vh',
	});
	/*
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
	*/
	return true;
};

var ppt_base_loop = true; //This become false only once we reach the minimum scale available (0.4)
var ppt_base_zoom_down_check = function(){
	if(!ppt_base_doscale || $('#ppt_frame_left_top').length==0 || $('#ppt_frame_left_bottom').length==0){
		return false;
	}
	//We reduce the size of the text to make it visible fullscreen
	$("#ppt_frame_right").height(Math.round(0.90*$(window).outerHeight()));
	while(ppt_base_loop && ($('#ppt_frame_left_top')[0].getBoundingClientRect().height + $('#ppt_frame_left_bottom')[0].getBoundingClientRect().height) > 0.90*$(window).outerHeight()){
		ppt_base_loop = ppt_base_zoom_down();
	}
	/*
	if(/firefox/i.test(navigator.userAgent)){
		$("#ppt_frame_right").height(Math.round(0.9*$(window).outerHeight()));
		//while(ppt_base_loop && $("#ppt_frame_left")[0].getBoundingClientRect().height > 0.9*$(window).outerHeight()){
		//while(ppt_base_loop && $(document).outerHeight() > $(window).outerHeight()){
		//while(ppt_base_loop && $('#ppt_frame_left').outerHeight() > $(window).outerHeight()){
		while(ppt_base_loop && ($('#ppt_frame_left_top')[0].getBoundingClientRect().height + $('#ppt_frame_left_bottom')[0].getBoundingClientRect().height) > 0.95*$(window).outerHeight()){
			ppt_base_loop = ppt_base_zoom_down();
		}
	} else {
		$("#ppt_frame_right").height(Math.round(0.9*$(window).outerHeight()));
		//while(ppt_base_loop && $('#ppt_frame').outerHeight() > $(window).outerHeight()){
		while(ppt_base_loop && ($('#ppt_frame_left_top')[0].getBoundingClientRect().height + $('#ppt_frame_left_bottom')[0].getBoundingClientRect().height) > 0.95*$(window).outerHeight()){
			ppt_base_loop = ppt_base_zoom_down();
		}
	}
	*/
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
