var base_error_timing;
var base_show_error_running = false;
var base_show_error = function(msg, error) {
	if(typeof error === 'undefined'){ error = false; }
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
		if(!base_show_error_running && $('#base_error').is(':hidden')){
			$("#base_error").velocity("transition.slideRightBigIn", {
				mobileHA: hasGood3Dsupport,
				duration: 260,
				delay: 120,
			});
		} else {
			$("#base_error").fadeTo( 60 , 0.9).fadeTo( 120 , 1);
		}
		base_show_error_running = true;
	} else if(typeof msg != "string"){
		JSerror.sendError(msg, 'base_show_error', 0);
	}
	base_error_timing = setTimeout(function(){ base_hide_error(); }, 4000);
}

var base_hide_error = function(now) {
	if(typeof now == 'undefined'){ now = false; }
	$('#base_error').velocity("stop");
	base_show_error_running = false;
	if(now){
		clearTimeout(base_error_timing);
		$('#base_error').hide().recursiveEmpty();
	} else if($('#base_error').is(':visible')){
		$("#base_error").velocity("transition.slideRightBigOut", {
			mobileHA: hasGood3Dsupport,
			duration: 160,
			delay: 80,
			complete: function(){
				clearTimeout(base_error_timing);
				$('#base_error').hide().recursiveEmpty();
			},
		});
	}
}

$('#base_error').click(function(){
	base_hide_error();
});
