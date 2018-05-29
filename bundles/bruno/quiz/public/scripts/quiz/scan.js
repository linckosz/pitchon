var quiz_scan_scanner = false;
var quiz_scan_scanner_camera = false;
var quiz_scan_scanner_index = 0;

var quiz_scan_scanner_change = function(){
	if(quiz_scan_scanner_camera && quiz_scan_scanner_camera.length > 1){
		quiz_scan_scanner_index++;
		if(quiz_scan_scanner_index >= quiz_scan_scanner_camera.length){
			quiz_scan_scanner_index = 0;
		}
		quiz_scan_scanner.start(quiz_scan_scanner_camera[quiz_scan_scanner_index]);
	}
	//alert(quiz_scan_scanner_camera[quiz_scan_scanner_index].name);
};

$(function() {
	
	if(true || wrapper_is_mobile()){
		Instascan.Camera.getCameras().then(function(cameras){
			quiz_scan_scanner_camera = cameras;
			if(quiz_scan_scanner_camera.length > 0) {
				$("#quiz_scan_qrcode").removeClass("display_none");
				$("#quiz_scan_code_or").removeClass("display_none");
				quiz_scan_scanner = new Instascan.Scanner({
					video: document.getElementById('quiz_scan_qrcode_scan'),
					mirror: false,
					backgroundScan: false,
					scanPeriod: 5,
				});
				quiz_scan_scanner.addListener('scan', function (content) {
					if(/^(https?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/gm.exec(content)){
						$("#quiz_scan_qrcode_video_bar").addClass("display_none");
						$("#quiz_scan_qrcode_video_done").removeClass("visibility_hidden"); //We must work with hidden, if not the clock does not load
						quiz_scan_scanner.stop();
						$("#quiz_scan_qrcode_video_done").css("background-color", "rgba(255, 255, 255, 0.80)");
						setTimeout(function(){
							$("#quiz_scan_qrcode_video_done").css("background-color", "rgba(255, 255, 255, 0)");
						}, 200);
						window.location.href = content;
					}
				});
				for (var i = 0; i < quiz_scan_scanner_camera.length; i++) {
					if(quiz_scan_scanner_camera[i].name && quiz_scan_scanner_camera[i].name.toLowerCase().indexOf('back') >= 0 ){
						quiz_scan_scanner_index = i;
					}
				}
				$(window).resize(function(){
					$(window).height()
					var height = $("#quiz_scan_qrcode_video").width();
					if(height > 0.60 * $(window).height()){
						height = Math.floor(0.60 * $(window).height());
					}
					$("#quiz_scan_qrcode_video").height(height);
				});
				$(window).resize();
				quiz_scan_scanner.start(quiz_scan_scanner_camera[0]).then(function(){
					$(window).resize();
					$("#quiz_scan_qrcode_video_bar").removeClass("display_none");
					if(quiz_scan_scanner_camera.length >= 1) {
						$("#quiz_scan_qrcode_video_flip")
							.removeClass("display_none")
							.on("click", quiz_scan_scanner_change);
					}
				});
			}
		});
	}

	$("#quiz_scan").removeClass("visibility_hidden");

	$(window).resize();

});
