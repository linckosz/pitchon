
var account_integration_code = false;

var account_submit_running = false;
var account_joinus_cb_begin = function(){
	account_submit_running = true;
	account_hide_error(true);
	$(document.body).css('cursor', 'progress');
	$('#account_joinus_submit_progress').css("display", "block");
	$('#account_joinus_submit_progress').removeClass('display_none');
	base_format_form_single($('#account_joinus_submit_progress'));
	$('#account_joinus_submit').addClass('account_joinus_submit_running');
};

var account_signin_cb_begin = function(){
	account_submit_running = true;
	account_hide_error(true);
	$(document.body).css('cursor', 'progress');
	$('#account_signin_submit_progress').css("display", "block");
	$('#account_signin_submit_progress').removeClass('display_none');
	base_format_form_single($('#account_signin_submit_progress'));
	$('#account_signin_submit').addClass('account_signin_submit_running');
};

var account_forgot_cb_begin = function(){
	account_submit_running = true;
	account_hide_error(true);
	$(document.body).css('cursor', 'progress');
	$('#account_forgot_submit_progress').css("display", "block");
	$('#account_forgot_submit_progress').removeClass('display_none');
	base_format_form_single($('#account_forgot_submit_progress'));
	$('#account_forgot_submit').addClass('account_forgot_submit_running');
};

var account_reset_cb_begin = function(jqXHR, settings){
	account_submit_running = true;
	account_hide_error(true);
	$(document.body).css('cursor', 'progress');
	$('#account_reset_submit_progress').css("display", "block");
	$('#account_reset_submit_progress').removeClass('display_none');
	base_format_form_single($('#account_reset_submit_progress'));
	$('#account_reset_submit').addClass('account_reset_submit_running');
	//Initialize credential
	account_credential = {};
	if(settings.data){
		var data = JSON.parse(settings.data);
		if(typeof data == 'object'){
			for(var i in data){
				if(typeof data[i].name == 'string' && typeof data[i].value == 'string'){
					if(data[i].name == 'email'){
						account_credential.email = data[i].value;
					}
					if(data[i].name == 'password'){
						account_credential.password = data[i].value;
					}
				}
			}
		}
	}
};


var account_error_timing;
var account_error_hide_timer = function(){
	clearTimeout(account_error_timing);
	account_error_timing = setTimeout(function(){ account_hide_error(); }, 2500);
}

var account_joinus_cb_success = account_signin_cb_success = function(msg, err, status, data){
	var field = 'undefined';
	if(typeof data.field !== 'undefined') { field = data.field; }
	if(err){
		base_hide_error(true);
		$('#account_error, #account_error_mobile').html(wrapper_to_html(msg));
		$("#account_error, #account_error_mobile").velocity("transition.slideDownIn", { duration: 500, delay: 100, });
		$("#account_joinus_form input[name="+field+"]").addClass('base_input_text_error').focus();
		$("#account_captcha").prop("src", $("#account_captcha").prop("src"));
		account_error_hide_timer();
	} else {
		window.location.href = wrapper_link['root'];
	}
};

var account_forgot_cb_success = function(msg, err, status, data){
	var field = 'undefined';
	var email = "";
	var reset = false;
	if(typeof data.field != 'undefined') { field = data.field; }
	if(typeof data.email != 'undefined') { email = data.email; }
	if(typeof data.reset != 'undefined') { reset = data.reset; }
	if(err){
		base_hide_error(true);
		$('#account_error, #account_error_mobile').html(wrapper_to_html(msg));
		$("#account_error, #account_error_mobile").velocity("transition.slideDownIn", { duration: 500, delay: 100, });
		$("#account_signin_form input[name="+field+"]").addClass('base_input_text_error').focus();
		account_error_hide_timer();
		if(reset){
			account_reset_time_left(0);
		}
	} else {
		account_reset_time_left_init(email);
		account_show('reset');
	}
};

var account_reset_cb_success = function(msg, err, status, data){
	var field = 'undefined';
	if(typeof data.field !== 'undefined') { field = data.field; }
	if(err){
		base_hide_error(true);
		$('#account_error, #account_error_mobile').html(wrapper_to_html(msg));
		$("#account_error, #account_error_mobile").velocity("transition.slideDownIn", { duration: 500, delay: 100, });
		$("#account_joinus_form input[name="+field+"]").addClass('base_input_text_error').focus();
		account_error_hide_timer();
	} else {
		//The server will setup credential session information
		window.location.href = wrapper_link['root'];
	}
};



var account_joinus_cb_error = account_signin_cb_error = account_forgot_cb_error = account_reset_cb_error = function(xhr_err, ajaxOptions, thrownError){
	var msgtp = Lincko.Translation.get('wrapper', 1, 'html'); //Communication error
	$('#account_error, #account_error_mobile').html(msgtp);
	if($('#account_error, #account_error_mobile').is(':hidden')){
		base_hide_error(true);
		$("#account_error, #account_error_mobile").velocity("transition.slideDownIn", { duration: 500, delay: 100, });
		account_error_hide_timer();
	}
};

var account_credential = {};

var account_joinus_cb_complete = function(){
	account_submit_running = false;
	$(document.body).css('cursor', '');
	$('#account_joinus_submit_progress').addClass('display_none');
	$('#account_joinus_submit').removeClass('account_joinus_submit_running');
};

var account_signin_cb_complete = function(){
	account_submit_running = false;
	$(document.body).css('cursor', '');
	$('#account_signin_submit_progress').addClass('display_none');
	$('#account_signin_submit').removeClass('account_signin_submit_running');
};

var account_forgot_cb_complete = function(){
	account_submit_running = false;
	$(document.body).css('cursor', '');
	$('#account_forgot_submit_progress').addClass('display_none');
	$('#account_forgot_submit').removeClass('account_forgot_submit_running');
};

var account_reset_cb_complete = function(){
	account_submit_running = false;
	$(document.body).css('cursor', '');
	$('#account_reset_submit_progress').addClass('display_none');
	$('#account_reset_submit').removeClass('account_reset_submit_running');
	account_credential = {};
};

var global_select = false; //'joinus', 'signin', 'forgot', 'reset', 'wechat'

function account_show(select) {
	if(select == global_select){
		return false;
	}
	if(typeof select=="boolean"){
		if(select && account_youjian){
			select = 'signin';
		} else {
			select = 'joinus';
		}
	}
	if(select=='signin' || select=='joinus'){
		$('#account_tab_lincko_back').addClass('display_none');
	} else {
		$('#account_tab_lincko_back').removeClass('display_none');
	}
	if(typeof select == "undefined"){ select = 'joinus'; }
	$('#account_wrapper').css('z-index', 1500).css("display", "table");
	$('#base_wrapper').addClass('blur');
	account_select(select);
}

function account_hide() {
	global_select = false;
	if(!isMobileApp(true)){
		$('#account_wrapper').css('z-index', -1).hide();
		$('#base_wrapper').removeClass('blur');
	}
}

function account_select(select) {
	global_select = select;
	$('#account_signin_box, #account_joinus_box, #account_forgot_box, #account_reset_box, #account_integration_box').addClass('display_none');
	$('#account_tab_joinus, #account_tab_signin').removeClass('account_trans').addClass('display_none');
	$('#account_tab_joinus > div, #account_tab_signin > div').removeClass('account_tab_joinus').removeClass('account_tab_signin');
	$('#account_wrapper').find('.account_integration_icon').removeClass('account_integration_icon_active account_integration_icon_blur');
	account_hide_error();
	account_integration_account.stop();
	if(integration_wechat(true)){
		return true;
	}
	if(select == 'forgot'){
		$('#account_forgot_box').removeClass('display_none');
		$('#account_forgot_email').val($('#account_signin_email').val());
		$('#account_forgot_email').focus();
	} else if(select == 'reset'){
		$('#account_reset_box').removeClass('display_none');
		$('#account_reset_code').val("");
		$('#account_reset_password').val("");
		$('#account_reset_password').focus(); //Helps to reset the text behind
		$('#account_reset_code').focus();
	} else if(select == 'wechat'){
		$('#account_integration_top_info').recursiveEmpty();
		$('#account_integration_box').removeClass('display_none');
			$('#account_integration_top_text').html(
				isMobileApp() ? 
				wrapper_to_html(Lincko.Translation.get('web', 15, 'html')) //waiting for response from WeChat...
			:	wrapper_to_html(Lincko.Translation.get('web', 14, 'html'))); //Scan the QR code to sign in using your Wechat account
		$('#account_wrapper').find('.account_integration_icon').addClass('account_integration_icon_blur');
		$('#account_integration_wechat').addClass('account_integration_icon_active').removeClass('account_integration_icon_blur');
	} else if(select == 'signin'){
		$('#account_signin_box').removeClass('display_none');
		$('#account_tab_joinus, #account_tab_signin').removeClass('display_none');
		$('#account_tab_joinus').addClass('account_trans');
		$('#account_tab_joinus > div').addClass('account_tab_joinus');
		if($('#account_joinus_email').val() != ''){
			$('#account_signin_email').val($('#account_joinus_email').val());
		}
		if($('#account_signin_email').val() != ''){
			account_display_label($('#account_signin_email'), false);
			$('#account_signin_password').focus();
		} else {
			$('#account_signin_email').focus();
		}
	} else { // 'joinus'
		$('#account_joinus_box').removeClass('display_none');
		$('#account_tab_joinus, #account_tab_signin').removeClass('display_none');
		$('#account_tab_signin').addClass('account_trans');
		$('#account_tab_signin > div').addClass('account_tab_signin');
		//This helps to refresh the captcha image to avoid it appear unlinked
		$("#account_captcha").prop("src", $("#account_captcha").prop("src"));
		if($('#account_signin_email').val() != account_youjian){
			$('#account_joinus_email').val($('#account_signin_email').val());
		}
		if($('#account_joinus_email').val() != ''){
			account_display_label($('#account_joinus_email'), false);
			$('#account_joinus_password').focus();
		} else {
			$('#account_joinus_email').focus();
		}
	}
}

function account_hide_error(now) {
	if(typeof now == 'undefined'){ now = false; }
	if(now){
		$("#account_error, #account_error_mobile").hide();
	} else {
		if($('#account_error, #account_error_mobile').is(':visible')){
			$("#account_error, #account_error_mobile").velocity("transition.fadeOut", { duration: 500, delay: 100, });
		}
	}
}

function account_display_label(input, hide_error) {
	if(hide_error){
		account_hide_error();
		$(input).removeClass('base_input_text_error');
	}
	if(typeof input == 'undefined' || !$(input) || !$(input).val() || $(input).val().length<=0){
		//$(input).prev().css('visibility', 'visible').css('z-index', 1);
		if($(input).prev().is(':hidden')){
			$(input).prev().velocity("transition.fadeIn", { duration: 300, delay: 100, });
		}
	} else {
		//$(input).prev().css('visibility', 'hidden').css('z-index', -1);
		if($(input).prev().is(':visible')){
			$(input).prev().velocity("transition.fadeOut", { duration: 300, delay: 100, });
		}
	}
}

var account_reset_time_left_timer;
var account_reset_time_left_seconds = 0;
var account_reset_time_left = function(timeout){
	account_reset_time_left_seconds = timeout;
	if(account_reset_time_left_seconds<=0){
		account_reset_time_left_expired();
	} else {
		$('#account_reset_limit_seconds').removeClass('display_none');
		$('#account_reset_limit_time').html(account_reset_time_left_seconds);
		window.clearInterval(account_reset_time_left_timer);
		account_reset_time_left_timer = window.setInterval(function(){
			if(account_reset_time_left_seconds<=0){
				account_reset_time_left_expired();
			} else {
				$('#account_reset_limit_time').html(account_reset_time_left_seconds);
			}
			account_reset_time_left_seconds--;
			if(account_reset_time_left_seconds<0){
				account_reset_time_left_seconds = 0;
				window.clearInterval(account_reset_time_left_timer);
			}
		}, 1000);
	}
}

var account_reset_time_left_is_expired = false;
var account_reset_time_left_expired = function(){
	account_reset_time_left_is_expired = true;
	var span = $('<span>').addClass('account_reset_limit_expired').html(Lincko.Translation.get('web', 12, 'html')); //time expired
	$('#account_reset_limit_time').html(span);
	$('#account_reset_limit_seconds').addClass('display_none');
	account_reset_time_left_seconds = 0;
	window.clearInterval(account_reset_time_left_timer);
	$("#account_reset_email").val("").prop('disabled', true);
	$("#account_reset_code, #account_reset_password").val("").prop('disabled', true);
	$("#account_reset_code, #account_reset_password").parent().addClass("account_no_cursor");
	$("#account_reset_submit").addClass("account_no_cursor account_reset_submit_disabled").prop('disabled', true);
	$('#account_reset_password').blur(); //Helps to reset the text behind
	$('#account_reset_code').blur();
	account_hide_error();
}
var account_reset_time_left_init = function(email){
	account_reset_time_left_is_expired = false;
	$("#account_reset_email").prop('disabled', false).val(email);
	$("#account_reset_code, #account_reset_password").prop('disabled', false);
	$("#account_reset_code, #account_reset_password").parent().removeClass("account_no_cursor");
	$("#account_reset_submit").removeClass("account_no_cursor account_reset_submit_disabled").prop('disabled', false);
	account_reset_time_left(120); //Set timeout to 2 minute
}

$('#account_close').click(function(){
	account_hide();
});

$('#account_tab_joinus').click(function(){
	account_show('joinus');
});

$('#account_tab_signin').click(function(){
	account_show('signin');
});

$('#account_signin_forgot').click(function(){
	account_show('forgot');
});

var account_integration_account_timer;
var account_integration_account = {
	timer: null,
	time: 1500,
	ready: true,
	status: -1,
	start: function(){
		account_integration_account.stop();
		account_integration_account.time = 1500;
		account_integration_account.timeout();
	},
	timeout: function(){
		account_integration_account.timer = setTimeout(function(){
			if(!account_integration_account.ready){
				return false;
			}
			account_integration_account.ready = false;
			if(account_integration_account.time < 10000){ //Limit at 10s scanner
				account_integration_account.time = account_integration_account.time + 50;
			}
			wrapper_sendAction(null, 'get', 'integration/code', function(msg, err, status, data){
				if(typeof data.status != 'undefined' && data.status != account_integration_account.status){
					account_integration_account.status = data.status;
					if(data.status==0){ //failed
						$('#account_integration_top_info').recursiveEmpty();
						var div = $('<div>').html(wrapper_to_html(msg));
						$('#account_integration_top_info').append(div);
						account_integration_account.stop();
						return true;
					} else if(data.status==2){ //processing
						$('#account_integration_top_info').recursiveEmpty();
						var div = $('<div>').html(wrapper_to_html(msg));
						var loading_bar = $("#-submit_progress_bar").clone();
						loading_bar.prop('id', '').addClass('account_integration_top_progress');
						$('#account_integration_top_info').append(div).append(loading_bar);
						return true;
					} else if(data.status==3){ //done
						$('#account_integration_top_info').recursiveEmpty();
						var div = $('<div>').html(wrapper_to_html(msg));
						$('#account_integration_top_info').append(div);
						account_integration_account.stop();
						window.location.href = wrapper_link['root'];
						return true;
					}
				}
			});
			account_integration_account.ready = true;
			account_integration_account.timeout();
		}, account_integration_account.time);
	},
	stop: function(){
		clearTimeout(account_integration_account.timer);
		account_integration_account.ready = true;
		account_integration_account.status = -1;
	},
};


$('#account_integration_wechat').click(function(){
	account_show('wechat');
	account_integration_wechat_qrcode();
	clearInterval(account_integration_wechat_timer);
	account_integration_wechat_timer = setInterval(function(){
		if($('#account_integration_box').hasClass('display_none')){
			clearInterval(account_integration_wechat_timer);
			return false;
		}
		account_integration_wechat_qrcode();
	}, 120000); //Refresh the QR code every 2min
	if(!isMobileBrowser() && !isMobileApp()){ //allow click only for desktop
		$('#account_integration_top_info').find('img').click(function(){
			account_integration_wechat_qrcode();
		});
	}
});

var wechat_login_qrcode = function(){
	return top.location.protocol+'//'+document.linckoFront+document.linckoBack+document.domainRoot+'/integration/wechat/wxqrcode?timeoffset='+wrapper_timeoffset()+'&ts='+(new wrapper_date().timestamp);
}

var account_integration_wechat_timer;
var account_integration_wechat_qrcode = function(){
	clearInterval(account_integration_wechat_timer);
	if(isMobileApp()){
		//Call a native function
		if(device_type() == 'android' && typeof android == 'object' && typeof android.wxLogin == 'function'){ //android wechat login
			android.wxLogin(wrapper_timeoffset());
		} else if(device_type() == 'ios'){
			var login = {
				action: 'wxlogin',
				timeoffset: wrapper_timeoffset(),
			};
			window.webkit.messageHandlers.iOS.postMessage(login);
		}
	} else {
		$('#account_integration_top_info').find('img').attr('src', wrapper_neutral.src); //Change to a transarency picture
		//Use Lincko QR code for integration
		//var url_qrcode = top.location.protocol+'//'+document.linckoBack+'file.'+document.domainRoot+':'+document.linckoBackPort+'/integration/qrcode/wechat?'+(new wrapper_date().timestamp);	
		var url_qrcode = wechat_login_qrcode();

		if($('#account_integration_top_info').find('img').length == 1){
			$('#account_integration_top_info').find('img').attr('src', url_qrcode);
		} else {
			$('#account_integration_top_info').recursiveEmpty();
			var image = $('<img>').attr('src', url_qrcode).addClass('account_integration_top_info_qrcode');
			$('#account_integration_top_info').append(image);
		}
		account_integration_account.start();
	}
};

$('#account_error, #account_error_mobile').click(function(){
	account_hide_error();
});

$("#account_joinus_email, #account_joinus_password, #account_joinus_captcha, #account_signin_email, #account_signin_password, #account_forgot_email, #account_reset_code, #account_reset_password").on({
	focus: function(){ account_display_label(this, false); },
	click: function(){ account_display_label(this, false); },
	blur: function(){ account_display_label(this, false); },
	change: function(){ account_display_label(this, false); },
	copy: function(){ account_display_label(this, true); },
	paste: function(){ account_display_label(this, true); },
	cut: function(){ account_display_label(this, true); },
	keyup: function(e) {
		if (e.which != 13) {
			account_display_label(this, true);
		}
	},
	keypress: function(e) {
		if (e.which == 13) {
			$(this.form).submit();
		} else {
			account_display_label(this, true);
		}
	},
});

$("#account_joinus_submit").keypress(function (e) {
	if (e.which == 13) {
		account_reset_autocompletion();
		$("#account_joinus_form").submit();
	}
});
$("#account_signin_submit").keypress(function (e) {
	if (e.which == 13) {
		account_reset_autocompletion();
		$("#account_signin_form").submit();
	}
});
$("#account_forgot_submit").keypress(function (e) {
	if (e.which == 13) {
		account_reset_autocompletion();
		$("#account_forgot_form").submit();
	}
});
$("#account_reset_submit").keypress(function (e) {
	if (e.which == 13) {
		account_reset_autocompletion();
		$("#account_reset_form").submit();
	}
});

$("#account_joinus_submit").click(function(){
	account_reset_autocompletion();
	$("#account_joinus_form").submit();
});
$("#account_signin_submit").click(function(){
	account_reset_autocompletion();
	$("#account_signin_form").submit();
});
$("#account_forgot_submit").click(function(){
	account_reset_autocompletion();
	$("#account_forgot_form").submit();
});
$("#account_reset_submit").click(function(){
	account_reset_autocompletion();
	$("#account_reset_form").submit();
});

//This help to clear the email field if there was an autocompletion issue (sometime chrome does keep empty after autocompletion, the yellow backgroubd effect)
var account_reset_autocompletion = function(){
	var joinus = $('#account_joinus_email').val();
	var signin = $('#account_signin_email').val();
	$('#account_joinus_email').val(joinus+'contact@lincko.com');
	$('#account_signin_email').val(signin+'contact@lincko.com');
	$('#account_joinus_email').val(joinus);
	$('#account_signin_email').val(signin);
}

$("#account_joinus_submit").keydown(function(e){
	if (e.which == 13) {
		$('#account_joinus_submit').addClass('account_joinus_submit_active');
	}
});
$("#account_joinus_submit").keyup(function(){
	$('#account_joinus_submit').removeClass('account_joinus_submit_active');
});

$("#account_signin_submit").keydown(function(e){
	if (e.which == 13) {
		$('#account_signin_submit').addClass('account_signin_submit_active');
	}
});
$("#account_signin_submit").keyup(function(){
	$('#account_signin_submit').removeClass('account_signin_submit_active');
});


//This help to clear the email field if there was an autocompletion issue (sometime chrome does keep empty after autocompletion, the yellow backgroubd effect)
$('#account_joinus_email').on('blur', function(){
	account_reset_autocompletion();
	var email = $('#account_joinus_email').val();
	if(base_input_field.email.valid(email)){
		wrapper_sendAction({email: email, }, 'post', 'email/verify', function(msg, err, status, data){
			if(err){
				$('#account_joinus_email_format').css('visibility', 'visible');
			}
		});
		
	}
});

$('#account_joinus_email').on('keyup past cut', function(){
	$('#account_joinus_email_format').css('visibility', 'hidden');
});


$('#account_signin_email').on('blur', function(){
	account_reset_autocompletion();
});

$("#account_joinus_password_show").click(function(){
	$("#account_joinus_password").attr('type', 'text');
	$("#account_joinus_password_show").addClass('display_none');
});

$("#account_joinus_password").addClass('base_input_text_error').on({
	blur: function(){
		$("#account_joinus_password").attr('type', 'password');
		$("#account_joinus_password_show").removeClass('display_none');
		$("#account_joinus_password_tooltip").removeClass('account_input_focus');
	},
	focus: function(){
		account_joinus_password_tooltip();
	},
	change: function(){ account_joinus_password_tooltip(); },
	copy: function(){ account_joinus_password_tooltip(); },
	paste: function(){ account_joinus_password_tooltip(); },
	cut: function(){ account_joinus_password_tooltip(); },
	keyup: function() { account_joinus_password_tooltip(); },
});

$('#account_tab_lincko_back').click(function(){
	account_show('signin');
})

var account_joinus_password_tooltip = function(){
	if(base_input_field.password.valid($("#account_joinus_password").val())){
		$("#account_joinus_password_tooltip").removeClass('account_input_focus');
	} else {
		$("#account_joinus_password_tooltip").addClass('account_input_focus');
	}
}

webworker_operation.account_show = function(select){
	account_show(select);
}

JSfiles.finish(function(){
	account_display_label($('#account_signin_email'), false);
	$('#account_joinus_timeoffset').val(wrapper_timeoffset());
	if(account_account_force){
		account_show(true);
	} else if(account_user_action){
		account_show(account_user_action);
	}
	if(isMobileApp(true)){
		$('#account_wrapper, .account_trans, .account_tab, #account_tab_lincko, .account_tab_joinus, .account_tab_signin, .account_form').addClass('account_wrapper_mobile_app');
	}

	if(	  navigator.userAgent.match(/MicroMessenger/i)
	   || (typeof android != 'undefined' && typeof android.hasWechat != 'undefined' && !android.hasWechat()) //if android APP and wechat is not installed
	){
		$('#account_integration_wechat').addClass('display_none');
	}

	//For integration login, we request a code
	if(isMobileApp()){
		wrapper_sendAction(null, 'get', 'integration/setcode',
			function(msg, err, status, data){
				if(typeof data.code == 'string' && data.code.length >= 1){
					account_integration_code = data.code;
					var device = device_type();
					if(device=='android'){
						//toto => to be defined
					} else if(device=='ios'){
						var obj = {
							integration_code: account_integration_code,
						}
						window.webkit.messageHandlers.iOS.postMessage(obj);
					} else if(device=='winphone'){
						//toto => to be defined
					}
				}
			}
		);
	}
});
