var ppt_frame_iframe_show_done = false;
var ppt_frame_iframe_show = function(){

	//Only run once
	if(ppt_frame_iframe_show_done){
		return true;
	}
	ppt_frame_iframe_show_done = true;

	$('#ppt_frame_hand').velocity(
		{
			opacity: 0,
		},
		{
			duration: 1000,
			complete: function(){
				$('#ppt_frame_hand').addClass("display_none");
			},
		}
	);

	$('#ppt_frame_right_bottom').removeClass("display_none");
	$('#ppt_frame_right_bottom').velocity(
		{
			opacity: 1,
		},
		{
			duration: 1000,
		}
	);

	$('#ppt_frame_style').removeClass("display_none");
	$('#ppt_frame_style').velocity(
		{
			opacity: 0.3,
		},
		{
			duration: 1000,
		}
	);
}

var ppt_frame_hand_move = function(){
	$('#ppt_frame_hand').velocity(
		{
			marginBottom: "1%",
			marginRight: "0.5%",
		},
		{
			delay: 500,
			duration: 2000,
		}
	);
};

$(function() {
	$('#ppt_frame_hand').on("click", ppt_frame_iframe_show);
});

