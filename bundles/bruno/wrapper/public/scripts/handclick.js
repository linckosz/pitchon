var wrapper_handclick_index = 0;

//pos_x and pos_y are position in % from top-left of the element, by default we start slithly above the center
var wrapper_handclick_create = function(Elem, loop, pos_x, pos_y, delay){
	if(typeof loop == "undefined"){ loop = 1; }
	if(typeof pos_x == "undefined" || pos_x < 0 || pos_x > 100){ pos_x = 40; }
	if(typeof pos_y == "undefined" || pos_y < 0 || pos_y > 100){ pos_y = 40; }
	if(typeof delay == "undefined"){ delay = 1200; }
	loop = parseInt(loop, 10);
	if(loop > 0 && Elem.length==1){
		wrapper_handclick_loop = loop;
		Elem_click = $('#-wrapper_handclick').clone();
		wrapper_handclick_index++;
		Elem_click.prop('id', 'wrapper_handclick_'+wrapper_handclick_index);
		Elem_click.insertAfter(Elem);
		var top = Elem.position().top + Math.floor(pos_y * Elem.outerHeight() / 100);
		var left = Elem.position().left + Math.floor(pos_x * Elem.outerWidth() / 100);
		Elem_click.css("top", top);
		Elem_click.css("left", left);
		wrapper_handclick_animate(wrapper_handclick_index, loop, delay);
		return Elem_click;
	}
	return false;
};

var wrapper_handclick_remove = function(index){
	if($("#wrapper_handclick_"+index).length >= 1){
		$("#wrapper_handclick_"+index).recursiveRemove();
	}
	return true;
};

var wrapper_handclick_animate = function(index, loop, delay){
	var Elem = $("#wrapper_handclick_"+index);
	if(loop > 0 && Elem.length == 1 ){
		$("#wrapper_handclick_"+index).find("[find=click]").addClass("display_none");
		$("#wrapper_handclick_"+index)
			.removeClass("display_none")
			.css({ marginTop: 10, marginLeft: 0 })
			.animate(
				{ marginTop: 0, marginLeft: 0 },
				{
					duration: 400,
					complete: function(){
						$(this).find("[find=click]").removeClass("display_none").fadeIn(100, function(){
							loop--;
							setTimeout(function(index, loop, delay){
								wrapper_handclick_animate(index, loop, delay);
							}, delay, index, loop, delay)
						});
					}
				}
			);
	} else {
		wrapper_handclick_remove(index);
	}
};
