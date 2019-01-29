//Initiiaze fields with same name
function base_format_input(field){
	if(field in base_input_field){
		var Elem = $("input[name="+field+"]");
		if($.type(base_input_field[field].tags) === 'object') {
			for(tag in base_input_field[field].tags){
				Elem.prop(tag, base_input_field[field].tags[tag]);
			}
		}
	}
}

//This function is only for IE which gives the wrong width when the element is hidden
function base_format_form_single(Elem){
	Elem.width(function(){
		return $(this).prev().outerWidth() - 8;
	});
}

//Initialize a bunch of forms' inputs
function base_format_form(prefix){
	if(typeof prefix !== 'string'){ prefix = ''; }
	var Elem = null;
	for(field in base_input_field){
		if(field.indexOf(prefix) === 0){
			base_format_input(field);
		}
	}
	base_format_form_single($('.submit_progress_bar'));
}
base_format_form();

var base_error_timing;

function base_show_error(msg, error, time) {
	if(typeof error == 'undefined'){ error = false; }
	if(typeof time == 'undefined'){ time = 4000; }
	if(error && $('#base_error').hasClass('base_message')){
		$('#base_error').removeClass('base_message');
	} else if(!error && !$('#base_error').hasClass('base_message')){
		$('#base_error').addClass('base_message');
	}
	clearTimeout(base_error_timing);
	//This avoid a double call
	msg = wrapper_to_html(msg); //Escape the whole string for HTML displaying
	if(typeof msg == "string" && $('#base_error').length > 0 && php_nl2br(php_br2nl(msg)) != php_nl2br(php_br2nl($('#base_error').html()))){
		$('#base_error').html(msg);
		if($('#base_error').is(':hidden')){
			$("#base_error").velocity("transition.slideRightBigIn", {
				duration: 260,
				delay: 120,
			});
		} else {
			$("#base_error").fadeTo( 80 , 0.8).fadeTo( 150 , 1);
		}
	} else if(typeof msg != "string"){
		JSerror.sendError(msg, 'base_show_error', 0);
	}
	base_error_timing = setTimeout(function(){ base_hide_error(); }, time);
}

function base_hide_error(now) {
	if(typeof now == 'undefined'){ now = false; }
	$('#base_error').velocity("stop");
	if(now){
		clearTimeout(base_error_timing);
		$('#base_error').hide().empty();
	} else if($('#base_error').is(':visible')){
		$("#base_error").velocity("transition.slideRightBigOut", {
			duration: 160,
			delay: 80,
			complete: function(){
				clearTimeout(base_error_timing);
				$('#base_error').hide().empty();
			},
		});
	}
}

$('#base_error').click(function(){
	base_hide_error();
});

var IMGcaptcha = new Image();
IMGcaptcha.src = "/captcha/4/320/120";

$("img[name=captcha]").prop("src", IMGcaptcha.src);

$("img[name=captcha]").click(function(){
	IMGcaptcha.src = IMGcaptcha.src;
	$("img[name=captcha]").prop("src", IMGcaptcha.src);
});

//If Safari iOS we make the object bigger and indeed allow the scroll of the main page (not inside the iFrame)
if(isSafariIOS){
	$('#base_wrapper').addClass('base_wrapper_scroll_y');
}
webworker_operation.launch_base_iframe = function(child_body_height){
	if(isIOS){
		if(child_body_height > 0){
			var wrapper_height = $('#base_iframe_message').offset()['top'] + child_body_height;
			$('#base_iframe_message').css('height', child_body_height);
			$('#base_wrapper').css('height', wrapper_height);
		}
	}
}
webworker_operation.launch_base_iframe($(window).height());

var base_standalone = function(){
	if(isMobileApp(true)){
		$('#account_close').recursiveRemove();
		$('#base_wrapper').addClass('display_none');
		$('#account_language').removeClass('display_none');
		account_show(true);
	} else {
		$('#base_wrapper').removeClass('display_none');
		$('#account_language').addClass('display_none');
	}
}

JSfiles.finish(function(){
	base_standalone();
});
