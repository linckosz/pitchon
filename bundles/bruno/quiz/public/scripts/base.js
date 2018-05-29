var base_is_mobile = function(){
	return /webOS|iPhone|iPad|BlackBerry|Windows Phone|Opera Mini|IEMobile|Mobile/i.test(navigator.userAgent);
}

$(function() {
	$("#base_website").on("click", function(){
		window.open(location.protocol+'//'+document.domainRoot, '_blank');
	});
});
