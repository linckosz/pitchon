ppt_event_slide.change = function(timer){
	if(typeof timer == 'undefined'){ timer = 50; }
	clearTimeout(ppt_event_slide.timer);
	ppt_event_slide.timer = setTimeout(function(){
		if(ppt_event_slide.direction=='next' && ppt_event_slide.next && ppt_event_slide.next != wrapper_href){
			window.location.href = ppt_event_slide.next;
		} else if(ppt_event_slide.direction=='prev' && ppt_event_slide.prev && ppt_event_slide.prev != wrapper_href){
			window.location.href = ppt_event_slide.prev;
		}
	}, timer);
};

if(ppt_event_slide.listenevent){
	$('#ppt_event_slide').removeClass('display_none');
	if(ppt_event_slide.prev){
		$('#ppt_event_slide_prev')
			.on('click', function(event){
				ppt_event_slide.direction = 'prev';
				ppt_event_slide.change(0);
				event.stopPropagation();
			});
	} else {
		$('#ppt_event_slide_prev')
			.addClass('disabled')
			.on('click', function(event){
				event.stopPropagation();
			});
	}
	if(ppt_event_slide.next){
		$('#ppt_event_slide_next')
			.on('click', function(event){
				ppt_event_slide.direction = 'next';
				ppt_event_slide.change(0);
				event.stopPropagation();
			});
	} else {
		$('#ppt_event_slide_next')
			.addClass('disabled')
			.on('click', function(event){
				event.stopPropagation();
			});
	}
	$('#ppt_event_slide').addClass('display_none');
	$(document).on('mousemove', function(event){
		$('#ppt_event_slide').removeClass('display_none');
	});
	$(document).on('mouseleave', function(event){
		$('#ppt_event_slide').addClass('display_none');
	});
	$(document).on('mouseenter', function(event){
		$('#ppt_event_slide').removeClass('display_none');
	});
	
	// We don't need Mousewheel, key up and down are used for remote ppt controller mainly
	//$(document).on('wheel mousewheel DOMMouseScroll keypress keydown keyup', function(event){
	$(document).on('keypress keydown keyup', function(event){
		var timer = 50;
		if(event.type=='keydown'){
			timer = 400;
		} else if(event.type=='keypress'){
			timer = 100;
		}
		ppt_event_slide.direction = 'next';
		if(event.type=='wheel' || event.type=='mousewheel'){
			if(
				   typeof event.originalEvent != 'undefined'
				&& typeof event.originalEvent.wheelDelta != 'undefined'
				&& event.originalEvent.wheelDelta > 0
			) {
				ppt_event_slide.direction = 'prev'; //wheelup
			}
		} else if(event.type=='DOMMouseScroll'){
			if(
				   typeof event.originalEvent != 'undefined'
				&& typeof event.originalEvent.detail != 'undefined'
				&& event.originalEvent.detail < 0
			) {
				ppt_event_slide.direction = 'prev'; //wheelup
			}
		} else if(event.type=='keypress' || event.type=='keydown' || event.type=='keyup'){
			if(event.which==8 || event.which==33 || event.which==36 || event.which==37 || event.which==38){ //backspace(8) / pageup(33) / home(36) / left(37) / up(38)
				ppt_event_slide.direction = 'prev';
			} else if(event.which==13 || event.which==32 || event.which==34 || event.which==35 || event.which==39 || event.which==40){ //enter(13) / space(32) / pagedown(34) / end(35) / right(39) / down(40)
				ppt_event_slide.direction = 'next';
			} else {
				return false;
			}
		}
		ppt_event_slide.change(timer);
	});
}
