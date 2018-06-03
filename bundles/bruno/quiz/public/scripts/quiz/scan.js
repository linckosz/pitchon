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
};

$(function() {
	
	if(base_is_mobile()){ //wrapper_show_error is for dev
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
						$("#quiz_scan_qrcode_video_flip").addClass("display_none")
						$("#quiz_scan_qrcode_video_done").removeClass("visibility_hidden"); //We must work with hidden, if not the clock does not load
						quiz_scan_scanner.stop();
						$("#quiz_scan_qrcode_video_done").css("background-color", "rgba(255, 255, 255, 0.80)");
						setTimeout(function(){
							$("#quiz_scan_qrcode_video_done").css("background-color", "rgba(255, 255, 255, 0)");
						}, 200);
						window.location.href = content;
					}
				});
				quiz_scan_scanner_index = quiz_scan_scanner_camera.length - 1; //There is more probabilty that the back camera is the last one registered, so we default on it
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
				quiz_scan_scanner.start(quiz_scan_scanner_camera[quiz_scan_scanner_index]).then(function(){
					$("#quiz_scan_qrcode_video_bar").removeClass("display_none");
					if(quiz_scan_scanner_camera.length > 1) {
						$("#quiz_scan_qrcode_video_flip")
							.removeClass("display_none")
							.on("click", quiz_scan_scanner_change);
					}
				}).catch(function (err) {
					$("#quiz_scan_qrcode").addClass("display_none");
					$("#quiz_scan_code_or").addClass("display_none");
					$(window).resize();
					//We force to focus on input
					$("#quiz_scan_code_text").focus().blur();
					$("#quiz_scan_code_text").get(0).scrollIntoView(true);
				});
			}
		});
	} else {
		//We force to focus on input
		$("#quiz_scan_code_text").focus().blur();
		$("#quiz_scan_code_text").get(0).scrollIntoView(true);
	}

	$("#quiz_scan").removeClass("visibility_hidden");

	$("#quiz_scan_code_text")
		.on("focus", function(){
			$("#quiz_scan_code_text").get(0).scrollIntoView(true);
			setTimeout(function(){
				$("#quiz_scan_code_text").get(0).scrollIntoView(true);
			}, 400);
		})
		.on("keypress", function(event){
			if(event.which==13){
				$("#quiz_scan_code_btn").click();
			}
		})

	$("#quiz_scan_code_btn").on("click", function(){
		var code = parseInt($("#quiz_scan_code_text").val(), 10);
		if(code && code > 0){
			window.location.href = '/c/'+code;
		} else {
			$("#quiz_scan_code_text").focus();
			$("#quiz_scan_code_text").get(0).scrollIntoView(true);
			setTimeout(function(){
				$("#quiz_scan_code_text").get(0).scrollIntoView(true);
			}, 400);
		}
	});



	$(window).resize();

});
