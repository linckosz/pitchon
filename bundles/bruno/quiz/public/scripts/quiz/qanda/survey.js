var score = {};

$("[find=quiz_star]").on("click", function(){
	var number = $(this).attr('number');
	var value = parseInt($(this).attr('score'));
	if(typeof score[number] == "number" && score[number] == value){
		score[number] = null;
		delete score[number];
		var Elem = $("[find=quiz_number_"+number+"] > [find=quiz_star]");
		Elem.addClass('fa-star-o').removeClass('fa-star show');
	} else {
		score[number] = value;
		var Elem = $("[find=quiz_number_"+number+"] > [find=quiz_star]");
		Elem.addClass('fa-star-o').removeClass('fa-star show');
		Elem.filter(function() {
			return parseInt($(this).attr("score")) <= value;
		}).addClass('fa-star show').removeClass('fa-star-o');
	}
});

$("#quiz_confirm").on("click", function(event){
	event.stopPropagation();
	var total = 0;
	for(var i in score){
		total++;
	}
	if(total < score_total){
		if(!confirm(trans_unfinshed)){ //You haven't scored all, those not scored will be ignored. Do you want to send your result?
			return false;
		}
	}
	var url = base_answer_url+'?score='+JSON.stringify(score);
	if(wrapper_html_zoom){
		url = url+"&zoom="+wrapper_html_zoom;
	}
	window.location.href = url;
});
