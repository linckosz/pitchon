var answerid = false;

$("[find=quiz_answer]").on("click", function(){
	$(".selected").removeClass("selected");
	$("[find=check]").addClass("display_none");
	$(this).addClass("selected");
	$(this).find("[find=check]").removeClass("display_none");
	answerid = $(this).attr('answerid');
	$("#quiz_confirm").removeClass("display_none");
});

$("#quiz_confirm").on("click", function(event){
	event.stopPropagation();
	if(answerid){
		var url = base_answer_url+answerid;
		if(wrapper_html_zoom){
			url = url+"?zoom="+wrapper_html_zoom;
		}
		window.location.href = url;

	}
});
