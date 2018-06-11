var base_xhr;
var base_totalxhr = 0;
var base_sendAction = function(param, method, action, cb_success, cb_error, cb_begin, cb_complete){
	if(typeof cb_success==="undefined" || cb_success===null){ cb_success = function(){}; }
	if(typeof cb_error==="undefined" || cb_error===null){ cb_error = function(){}; }
	if(typeof cb_begin==="undefined" || cb_begin===null){ cb_begin = function(){}; }
	if(typeof cb_complete==="undefined" || cb_complete===null){ cb_complete = function(){}; }
	
	base_totalxhr++;
	method = method.toUpperCase();
	action = action.toLowerCase();
	param = ArrayToObject(param);

	//Ajax calls will queue GET request only, that can timeout if the url is the same, but the PHP code still processing in background
	//We add a random md5 code to insure we avoid getting in queue for the same ajax call
	if(method=="GET"){
		var unique_md5 = md5(Math.random());
		action = action+'?'+unique_md5;
	}

	var timeout = 30000; //30s

	base_xhr = $.ajax({
		url: location.protocol+'//'+document.domain+'/'+action,
		type: method,
		data: JSON.stringify(param),
		contentType: 'application/json; charset=UTF-8',
		dataType: 'json',
		timeout: timeout,

		beforeSend: function(jqXHR, settings){
			cb_begin(jqXHR, settings);
		},

		success: function(data){
			var msg = '';
			var extra = null;
			var error = false;
			var status = 200;
			if(typeof data.extra != 'undefined'){
				extra = data.extra;
				if(typeof data.extra == 'object' && typeof data.extra.msg == 'string'){
					msg = data.extra.msg;
				}
			}
			if(typeof data.error != 'undefined' && data.error){
				error = true;
				console.log(data);
			}
			if(typeof data.status != 'undefined'){
				status = data.status;
			}
			// Below is the production information with "dataType: 'json'"
			cb_success(msg, error, status, extra);
		},
		
		error: function(xhr_err, ajaxOptions, thrownError){
			var msg = base_totalxhr+') '+'xhr.status => '+xhr_err.status
				+'\n'
				+'ajaxOptions => '+ajaxOptions
				+'\n'
				+'thrownError => '+thrownError;

			if(ajaxOptions!='abort'){
				console.log(msg);
			}
			cb_error(xhr_err, ajaxOptions, thrownError);
		},

		complete: function(){
			cb_complete();
		},
	});

	return base_xhr;
};

var base_is_mobile = function(){
	return /webOS|iPhone|iPad|BlackBerry|Windows Phone|Opera Mini|IEMobile|Mobile/i.test(navigator.userAgent);
}

$(function() {
	$("#base_website").on("click", function(){
		window.open(location.protocol+'//'+document.domainRoot, '_blank');
	});
});
