var account_submit_running = false;

var account_error_timing;
var account_error_hide_timer = function(){
	clearTimeout(account_error_timing);
	account_error_timing = setTimeout(function(){ account_hide_error(); }, 2500);
}

var account_hide_error = function(now) {
	if(typeof now == 'undefined'){ now = false; }
	if(now){
		$("#account_error, #account_error_mobile").hide();
	} else {
		if($('#account_error, #account_error_mobile').is(':visible')){
			$("#account_error, #account_error_mobile").velocity("transition.fadeOut", { duration: 500, delay: 100, });
		}
	}
}

var account_display_label = function(input, hide_error) {
	if(hide_error){
		account_hide_error();
		$(input).removeClass('base_input_text_error');
	}
	if(!$(input).val() || $(input).val().length<=0){
		if($(input).prev().is(':hidden')){
			$(input).prev().velocity("transition.fadeIn", { duration: 300, delay: 100, });
		}
	} else {
		if($(input).prev().is(':visible')){
			$(input).prev().velocity("transition.fadeOut", { duration: 300, delay: 100, });
		}
	}
}

var account_signin_cb_begin = function(){
	account_submit_running = true;
	account_hide_error(true);
	$(document.body).css('cursor', 'progress');
	$('#account_signin_submit_progress').css("display", "block");
	$('#account_signin_submit_progress').removeClass('display_none');
	base_format_form_single($('#account_signin_submit_progress'));
	$('#account_signin_submit').addClass('account_signin_submit_running');
};

var account_signin_cb_success = function(msg, err, status, data){
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

var account_signin_cb_error = function(xhr_err, ajaxOptions, thrownError){
	var msgtp = Bruno.Translation.get('wrapper', 1, 'html'); //Communication error
	$('#account_error, #account_error_mobile').html(msgtp);
	if($('#account_error, #account_error_mobile').is(':hidden')){
		base_hide_error(true);
		$("#account_error, #account_error_mobile").velocity("transition.slideDownIn", { duration: 500, delay: 100, });
		account_error_hide_timer();
	}
};

var account_signin_cb_complete = function(){
	account_submit_running = false;
	$(document.body).css('cursor', '');
	$('#account_signin_submit_progress').addClass('display_none');
	$('#account_signin_submit').removeClass('account_signin_submit_running');
};

$("#account_signin_email, #account_signin_password, #account_signin_captcha").on({
	focus: function(){ account_display_label(this, false); },
	click: function(){ account_display_label(this, false); },
	blur: function(){ account_display_label(this, false); },
	change: function(){ account_display_label(this, false); },
	copy: function(){ account_display_label(this, true); },
	paste: function(){ account_display_label(this, true); },
	cut: function(){ account_display_label(this, true); },
	keyup: function(event) {
		if (event.which != 13) {
			account_display_label(this, true);
		}
	},
	keypress: function(event) {
		if (event.which == 13) {
			account_signin();
		} else {
			account_display_label(this, true);
		}
	},
});

$('#account_error, #account_error_mobile').click(function(){
	account_hide_error();
});

$('#account_language_select').on('change', function(){
	wrapper_change_language(this.value);
});

$("#account_signin_submit")
	.keypress(function(event) {
		if (event.which == 13) {
			account_signin();
			$("#account_signin_form").submit();
		}
	})
	.click(function(){
		account_signin();
		$("#account_signin_form").submit();
	})
	.keydown(function(event){
		if (event.which == 13) {
			$('#account_signin_submit').addClass('account_signin_submit_active');
		}
	})
	.keyup(function(){
		$('#account_signin_submit').removeClass('account_signin_submit_active');
	});

//This help to clear the email field if there was an autocompletion issue (sometime chrome does keep empty after autocompletion, the yellow backgroubd effect)
$('#account_signin_email').on('blur', function(){
	var email = $('#account_signin_email').val();
	if(base_input_field.email.valid(email)){
		wrapper_sendAction({email: email, }, 'post', 'wrapper/info/verifyemail', function(msg, err, status, data){
			if(err){
				$('#account_signin_email_format').css('visibility', 'visible');
			}
		});
	}
});

$('#account_signin_email').on('keyup past cut', function(){
	$('#account_signin_email_format').css('visibility', 'hidden');
});

$("#account_signin_password_show").on({
	mousedown: function(){
		$("#account_signin_password").attr('type', 'text');
	},
});

$("#body_bruno").on({
	mouseup: function(){
		$("#account_signin_password").attr('type', 'password');
	},
});

$("#account_signin_password").on({
	blur: function(){
		$("#account_signin_password_tooltip").removeClass('account_input_focus');
	},
	focus: function(){ account_signin_password_tooltip(); },
	change: function(){ account_signin_password_tooltip(); },
	copy: function(){ account_signin_password_tooltip(); },
	paste: function(){ account_signin_password_tooltip(); },
	cut: function(){ account_signin_password_tooltip(); },
	keyup: function() { account_signin_password_tooltip(); },
});

var account_signin_password_tooltip = function(){
	if(base_input_field.password.valid($("#account_signin_password").val())){
		$("#account_signin_password_tooltip").removeClass('account_input_focus');
	} else {
		$("#account_signin_password_tooltip").addClass('account_input_focus');
	}
}

$("#account_forgot_submit")
	.keypress(function(event) {
		if (event.which == 13) {
			account_forgot();
		}
	})
	.click(function(){
		account_forgot();
	});


var account_signin = function(){
	if(account_submit_running){
		return false;
	}
	var email = $('#account_signin_email').val();
	if(!base_input_field.email.valid(email)){
		$('#account_joinus_email_format').css('visibility', 'visible');
	}
	var password = $('#account_signin_password').val();
	if(!base_input_field.password.valid(password)){
		$("#account_signin_password_tooltip").addClass('account_input_focus');
	}
	var remember = $('#account_signin_remember').is(':checked');
	var data = {
		email: email,
		password: password,
		remember: remember,
	};
	wrapper_sendAction(data, 'post', 'api/user/signin', account_signin_cb_success, account_signin_cb_error, account_signin_cb_begin, account_signin_cb_complete);
}

var account_forgot = function(){
	//[toto] Process of password recovery
}

JSfiles.finish(function(){
	account_hide_error();
	account_display_label($("#account_signin_email"), true);
});
