/*
var IMGpagefirst = new Image();
IMGpagefirst.src = '';

// Faire un array true/false => faire un a = !a puis recupere la source dans l'array,
// cela est pour preloader les images background au passage de la souris ou du doigt pour eviter le flash du temps de chargement
*/


$('#head_signout').click(function(){
	wrapper_sendAction('','post','user/signout', null, null, wrapper_signout_cb_begin, wrapper_signout_cb_complete);
});

$('#head_signin').click(function(){
	if(typeof account_show != 'undefined') { account_show('signin'); }
});

$('#head_joinus').click(function(){
	if(typeof account_show != 'undefined') { account_show('joinus'); }
});

$('#head_account').click(function(){
	window.location.href = head_link['root'];
});

$('#head_blog').click(function(){
	window.location.href = head_link['blog'];
});

