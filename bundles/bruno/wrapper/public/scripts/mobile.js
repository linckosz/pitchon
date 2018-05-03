function setMobileAlias(){
	var sha = wrapper_localstorage.sha;
	if(typeof sha == 'undefined') {
		sha = '';
	}

	if(typeof android != 'undefined' ) {
		android.setAlias('android', sha);
	} else if(typeof window !== 'undefined' &&  typeof window.webkit !== 'undefined' && typeof window.webkit.messageHandlers !== 'undefined' && typeof window.webkit.messageHandlers.iOS !== 'undefined') {
		var obj = {
			sha: sha,
		}
		window.webkit.messageHandlers.iOS.postMessage(obj);
	}
	if(typeof winPhone != 'undefined' ) {
		winPhone.setAlias(sha);
	}
}
setMobileAlias();

function isMobileApp(){
	var device = device_type();
	return device=="android" || device=="ios" || device=="winphone";
}

function useMobileNotification(){
	var notif = false;
	if(isMobileApp()){
		var device = device_type();
		if(device=='android'){
			if(typeof android.notification != 'function'){ //android versionCode 4 and up has js notification function
				notif = true;
			}
		}
		else if(device=='ios' && Bruno.storage.iosHideNotif.data){
			notif = true;
		}
	}
	return notif;
}

function device_download(url, target, name){
	if(typeof target == 'undefined'){ target = '_system'; }
	if(typeof name == 'undefined'){ name = 'file'; }
	var device = device_type();

	if(device=="android"){
		if(name == 'files' && typeof android.download == 'function'){
			android.download(url, document.cookie);
		} 
		else if(name != 'files' && typeof android.open_external_url == 'function'){
			android.open_external_url(url);
		}
	}
	else if(device=="ios") {
		if(name == 'file'){
			var download_obj = {
				url: url,
				cookie: document.cookie,
			};
			window.webkit.messageHandlers.iOS.postMessage(download_obj);
		} else {
			ios_open_link(url);
		}		
	}
	else if(device=="winphone" && typeof winPhone.download == 'function') {
		winPhone.download(url);
	}
	else if(/MicroMessenger|firefox|opera/i.test(navigator.userAgent)){
		window.open(url, target);
	}
	else {
		//Another method if some browser (safari?) do not work
		var anchor = document.createElement('a');
		anchor.href = url;
		if(target){ anchor.target = target; }
		if(name){ anchor.download = name; }
		anchor.click();
	}
}

var device_type_record = false;
function device_type(){
	if(!device_type_record){
		if(typeof android != 'undefined') {
			device_type_record = "android";
		}
		else if(typeof window.webkit != 'undefined' && typeof window.webkit.messageHandlers != 'undefined' && typeof window.webkit.messageHandlers.iOS != 'undefined') {
			device_type_record = "ios";
		}
		else if(typeof winPhone != 'undefined') {
			device_type_record = "winphone";
		}
		else if(/webOS|iPhone|iPad|BlackBerry|Windows Phone|Opera Mini|IEMobile|Mobile/i.test(navigator.userAgent)) {
			device_type_record = "mobilebrowser";
		} else {
			device_type_record = "computer";
		}
	}
	return device_type_record;
}
