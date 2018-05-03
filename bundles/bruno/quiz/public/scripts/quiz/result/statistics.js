var quiz_result_statistics_statistics_timer = false;
var quiz_result_statistics_statistics = function(){
	clearInterval(quiz_result_statistics_statistics_timer);
	quiz_result_statistics_statistics_timer = setInterval(function(){
		//Check if the statistics page is using new question
	}, 3000);
};

$(function() {
	$('#quiz_result_statistics_scan_picture').on('click', function(){
		base_scanner();
	});
});
