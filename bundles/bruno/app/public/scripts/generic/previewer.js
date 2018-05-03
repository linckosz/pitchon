var previewer = function(file_id){
	$("#previewer_update").off('click');
	$("#previewer_delete").off('click');
	app_application_bruno.clean("previewer_picture");
	if(file_id){
		var item = Bruno.storage.get('file', file_id);
		if(item){
			var url = Bruno.storage.getFile(file_id);
			if(url){
				var parent = Bruno.storage.getParentClone('file', file_id);
				if(item['category']=="image"){
					
					$("#previewer_picture")
						.css("background-image", "url('"+url+"')")
						.removeClass('display_none');
					if(parent){
						app_application_bruno.add("previewer_picture", parent['_type']+"_"+parent['id'], function(){
							var type = this.action_param[0];
							var id = this.action_param[1];
							var new_file_id = Bruno.storage.get(type, id, 'file_id');
							previewer(new_file_id);
						}, [parent['_type'], parent['id']]);
					}

					$("#previewer_update").on('click', [parent['_type'], parent['id']], function(event){
						event.stopPropagation();
						var type = event.data[0];
						var id = event.data[1];
						var item = Bruno.storage.getClone(type, id);
						var temp_id = app_upload_open_photo_single(item['_type'], item['id'], item['md5'], 'file_id', null, false, true);
						previewer_upload_status(temp_id);
					});
					
					$("#previewer_delete").on('click', file_id, function(event){
						previewer_upload_abort(event);
						event.stopPropagation();
						if(confirm(Bruno.Translation.get('app', 26, 'js'))){ //Are you sure you want to delete this item?
							var data = {};
							//Delete the file
							data.delete = {};
							data.delete.file = {};
							var item = Bruno.storage.get('file', event.data);
							data.delete.file[item['id']] = {
								id: item['id'],
								md5: item['md5'],
							};
							if(parent){
								//Unset the parent linked
								data.set = {};
								data.set[parent['_type']] = {};
								data.set[parent['_type']][parent['id']] = {
									id: parent['id'],
									md5: parent['md5'],
									file_id: null,
								};
							}
							var Elem_bis = $(this);
							var action_cb_success = function(msg, error, status, extra){
								storage_cb_success(msg, error, status, extra);
							}
							var action_cb_complete = function(){
								storage_cb_complete();
								base_hideProgress(Elem_bis);
								app_application_bruno.prepare("file_"+file_id, true);
							};
							if(storage_offline(data)){
								base_showProgress(Elem);
								wrapper_sendAction(data, 'post', 'api/data/set', action_cb_success, storage_cb_error, storage_cb_begin, action_cb_complete);
								app_application_bruno.prepare("file_"+file_id, true);
							}
							previewer(false);
						}
					});
					
					app_generic_state.change({
						previewer: true,
					}, null, 1);
					return true;
				}
				//toto => still need to make the video, even if we don't display them
			}
		}
	}
	$("#previewer_picture")
		.css("background-image", "url('"+wrapper_neutral.src+"')")
		.addClass('display_none');

	app_generic_state.change({
		previewer: false,
	}, null, -1);

	return false;
};

var previewer_upload_garbage = {};
var previewer_upload_progress = false;
var previewer_upload_temp_id = false;
var previewer_upload_status = function(temp_id){
	previewer_upload_temp_id = temp_id;
	previewer_upload_progress = false;
	previewer_upload_garbage[temp_id] = app_application_garbage.add(temp_id);
	app_application_bruno.add(previewer_upload_garbage[temp_id], 'upload', function() {
		var Elem = $('#previewer_update');
		var data = app_upload_files.getData(this.action_param);
		if(previewer_upload_progress && (!data || Elem.length<=0)){
			Elem.css('background', '');
			previewer_upload_progress = false;
			previewer_upload_temp_id = false;
			delete previewer_upload_garbage[this.action_param];
			app_application_garbage.remove(this.id);
		} else if(data){
			var progress = Math.floor(data.bruno_progress);
			var color_start = 'rgba(143, 143, 143, 0.6)';
			var color_end = 'rgba(143, 143, 143, 0.2)';
			if($.inArray(data.bruno_status, ['abort', 'failed', 'error', 'deleted']) >= 0){
				color_start = 'rgba(185, 124, 103, 0.6)';
			} else {
				if(progress<100 && data.bruno_status!='done'){
					previewer_upload_progress = Math.floor(data.bruno_progress);
				}
				if(data.bruno_status=='done'){
					previewer_upload_progress = 100;
				}
			}
			progress = previewer_upload_progress;
			Elem
				.css('background', '-moz-linear-gradient(left, '+color_start+' '+(progress-2)+'%, '+color_end+' '+(progress+2)+'%)')
				.css('background', '-webkit-linear-gradient(left, '+color_start+' '+(progress-2)+'%, '+color_end+' '+(progress+2)+'%)')
				.css('background', 'linear-gradient(to right, '+color_start+' '+(progress-2)+'%, '+color_end+' '+(progress+2)+'%)');
		}
	}, temp_id);
};

var previewer_upload_abort = function(event){
	if(previewer_upload_temp_id){
		var data = app_upload_files.getData(previewer_upload_temp_id);
		if(data){
			data.abort();
		}
	}
}

JSfiles.finish(function(){
	$("#previewer_picture, #previewer_close").on('click', function(event){
		previewer(false);
		event.stopPropagation();
	});
});
