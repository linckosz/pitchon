function mailchimp_ajax(email){
	if(typeof email!="string"){ return false; }

	var param = {
		"email_address": email.toLowerCase(),
	};

	$.ajax({
		url: top.location.protocol+'//'+document.linckoFront+document.linckoBack+document.domain+'/mailchimp',
		method: 'POST',
		data: JSON.stringify(param),
		contentType: 'application/json; charset=UTF-8',
		dataType: 'json',
		timeout: 10000,
		beforeSend: function(jqXHR, settings){
			$(document.body).css('cursor', 'progress');
			$('#home_news_submit_progress').css("display", "block");
			$('#home_news_submit_progress').removeClass('display_none');
			base_format_form_single($('#home_news_submit_progress'));
		},
		success: function(data){
			if(data.status=="subscribed"){
				base_show_error(Lincko.Translation.get('web', 11, 'js'), false);
			} else if(data.title=="Member Exists"){
				base_show_error(Lincko.Translation.get('web', 10, 'js'), false);
			} else {
				base_show_error(Lincko.Translation.get('web', 9, 'js'), true);
			}
		},
		error: function(xhr_err, ajaxOptions, thrownError){
			base_show_error(Lincko.Translation.get('web', 9, 'js'), true);
		},
		complete: function(){
			$(document.body).css('cursor', '');
			$('#home_news_submit_progress').addClass('display_none');
		},
	});
}
