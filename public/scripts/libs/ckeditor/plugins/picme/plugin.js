CKEDITOR.plugins.add( 'picme', {
    icons: 'picme',
    init: function( editor ) {
        editor.addCommand( 'insertpicme', {
            exec: function( editor ) {
                console.log('picme');
                /*
                console.log(typeof Lincko);
                var picID  = Lincko.storage.get("users", wrapper_localstorage.uid, 'profile_pic');
                var thumb_url = app_application_icon_single_user.src;
                console.log('picID');
                if(picID){
                    thumb_url = Lincko.storage.getLinkThumbnail(picID);
                }
                var elem_img = $('<img>').css('background-image','url("'+thumb_url+'")');
                console.log(elem_img[0]);
                //editor.insertHtml( elem_img[0] );
                */
            }
        });
        editor.ui.addButton( 'picme', {
            label: 'Insert picme',
            command: 'insertpicme',
            toolbar: 'insert'
        });
    }
});
