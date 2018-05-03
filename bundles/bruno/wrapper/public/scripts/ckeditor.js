/*
	Must run before DOM load, but after ckeditor.js library load
	in wrapper.twig, load it write after "jquery-ui.min.js" :
	<script src="{{ _filelatest('/scripts/libs/ckeditor/ckeditor.js') }}" type="text/javascript"></script>
	<script src="{{ _filelatest('/bruno/wrapper/scripts/ckeditor.js') }}" type="text/javascript"></script> => can regroup it
*/

if(CKEDITOR){
	CKEDITOR.disableAutoInline = true;
	CKEDITOR.config.allowedContent = true;
	CKEDITOR.config.title = false;
	CKEDITOR.config.linkShowTargetTab = false;
	CKEDITOR.config.fillEmptyBlocks = false;
	CKEDITOR.dtd.$removeEmpty['span'] = false;
}
