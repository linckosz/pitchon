//app_upload_all
Submenu.prototype.style['app_upload_all'] = function(submenu_wrapper, subm){
	if(typeof app_application_bruno !== 'undefined'){
		if(app_application_bruno.add(subm.id, 'upload', function(){ //We cannot simplify because Elem is not the HTML object, it's a JS Submenu object
			subm.Add_MenuAppUploadAllForm();
			subm.Add_MenuAppUploadAllFile();
		})){
			subm.Add_MenuAppUploadAllForm();
			subm.Add_MenuAppUploadAllFile();
			app_application_bruno.prepare('upload', true);
		}
	}
};

//app_upload_single
Submenu.prototype.style['app_upload_sub'] = function(submenu_wrapper, subm){
	if(typeof app_application_bruno !== 'undefined' && typeof app_upload_files !== 'undefined' && typeof app_upload_files.bruno_files[this.attribute.value] !== 'undefined'){
		if(app_application_bruno.add(subm.id, 'upload', function(){
			subm.Add_MenuAppUploadSubFile();
		})){
			subm.Add_MenuAppUploadSubFile();
			app_application_bruno.prepare('upload', true);
		}
	} else {
		subm.display = false;
	}
};

Submenu.prototype.Add_MenuAppUploadAllForm = function() {
	var submenu_wrapper = this.Wrapper();
	var Elem = null;
	var Elem_bt = null;
	var Elem_ct = null;
	var that = this;

	//Upload function buttons
	if($('#'+this.id+'_submenu_app_upload_function').length <= 0){
		Elem = $('#-submenu_app_upload_function').clone();

		Elem.prop("id", this.id+'_submenu_app_upload_function');

		submenu_wrapper.find("[find=submenu_wrapper_bottom]").addClass('submenu_bottom base_optionTab');
		submenu_wrapper.find("[find=submenu_wrapper_content]").css('bottom', submenu_wrapper.find("[find=submenu_wrapper_bottom]").height());

		Elem_bt = $('#-submenu_app_upload_function_button').clone();
		Elem_bt.prop("id", this.id+'_submenu_app_upload_function_button'+'_start');
		Elem_bt.html(Bruno.Translation.get('app', 5, 'html')); //Start
		Elem_bt.click(function(event){
			$('#app_upload_fileupload').fileupload('option')._cancelHandler(event); //Force to reinitialize before any start
			$('#app_upload_fileupload').fileupload('option')._startHandler(event);
		});
		Elem.append(Elem_bt);

		Elem_bt = $('#-submenu_app_upload_function_button').clone();
		Elem_bt.prop("id", this.id+'_submenu_app_upload_function_button'+'_stop');
		Elem_bt.html(Bruno.Translation.get('app', 12, 'html')); //Stop
		Elem_bt.click(function(event){
			$('#app_upload_fileupload').fileupload('option')._cancelHandler(event);
		});
		Elem_bt.hide();
		Elem.append(Elem_bt);

		Elem_bt = $('#-submenu_app_upload_function_button').clone();
		Elem_bt.prop("id", this.id+'_submenu_app_upload_function_button'+'_cancel');
		Elem_bt.html(Bruno.Translation.get('app', 22, 'html')); //Delete
		Elem_bt.click(function(event){
			if(app_upload_files.bruno_numberOfFiles>0){
				$('#app_upload_fileupload').fileupload('option')._cancelHandler(event); //Force to reinitialize before any start
				$('#app_upload_fileupload').fileupload('option')._deleteHandler(event);
			} else {
				$('#'+that.id).find("[find=submenu_wrapper_back]").click();
			}
		});
		Elem.append(Elem_bt);

		Elem_bt = $('#-submenu_app_upload_add_corner').clone();
		Elem_bt.prop('id', this.id+'_submenu_app_upload_add_corner');
		Elem_bt.click(function(event){
			app_upload_open_files();
		});
		Elem_bt.appendTo(submenu_wrapper);

		Elem_bt = $('#-submenu_app_upload_add_top').clone();
		Elem_bt.prop('id', this.id+'submenu_app_upload_add_top');
		Elem_bt.click(function(event){
			submenu_app_upload_display($(this));
		});
		Elem_bt.appendTo(submenu_wrapper.find("[find=submenu_wrapper_side_right]"));

		submenu_wrapper.find("[find=submenu_wrapper_bottom]").append(Elem);
	}

	if($('#'+this.id+'_submenu_app_upload_title').length <= 0){
		Elem = $('#-submenu_app_upload_title').clone();
		Elem.prop("id", this.id+'_submenu_app_upload_title');
		submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
	} else {
		Elem = $('#'+this.id+'_submenu_app_upload_title');
	}

	Elem.find("[find=submenu_app_upload_all_progress_pc]").css('width',
		Math.floor(app_upload_files.bruno_progressall) + '%'
	);
	Elem.find("[find=submenu_app_upload_all_progress_pc_text]").html(
		function(){
			if(app_upload_files.bruno_progressall>=100 && app_upload_files.bruno_numberOfFiles<=0){
				return Bruno.Translation.get('app', 8, 'html'); //Complete
			} else {
				return Math.floor(app_upload_files.bruno_progressall) + '%';
			}
		}
	);
	Elem.find("[find=submenu_app_upload_all_size]").html(
		app_upload_files.bruno_size
	);
	Elem.find("[find=submenu_app_upload_all_speed]").html(
		app_upload_files.bruno_britate
	);
	Elem.find("[find=submenu_app_upload_all_time]").html(
		app_upload_files.bruno_time
	);
	Elem.find("[find=submenu_app_upload_all_files]").html(
		function(){
			if(app_upload_files.bruno_numberOfFiles<=1){
				return app_upload_files.bruno_numberOfFiles + ' ' + Bruno.Translation.get('app', 19, 'html'); //file
			} else {
				return app_upload_files.bruno_numberOfFiles + ' ' + Bruno.Translation.get('app', 20, 'html'); //files
			}
		}
	);
	//Free memory
	delete submenu_wrapper;
	return true;
};

Submenu.prototype.Add_MenuAppUploadAllFile = function(event) {
	var that = this;
	var Elem = null;
	var pause = true;
	var finish = true;
	var retry = true;
	//Each
	if(typeof app_upload_files !== 'undefined'){
		$.each(app_upload_files.bruno_files, function(index, data){
			if($.type(data) === 'object'){
				if(typeof data.bruno_type !== 'undefined' && data.bruno_type === 'file'){
					if($('#'+that.id+'_submenu_app_upload_single_'+index).length <= 0){
						Elem = $('#-submenu_app_upload_single').clone();
						Elem.prop("id", that.id+'_submenu_app_upload_single_'+index);
						Elem.find("[find=submenu_app_upload_name]").html(
							data.bruno_name
						);
						Elem.find("[find=submenu_app_upload_name]").prop('title',
							data.bruno_name
						);
						Elem.find("[find=submenu_app_upload_size]").html(
							$('#app_upload_fileupload').fileupload('option')._formatFileSize(data.bruno_size)
						);
						destination = "";
						if(data.bruno_parent_type=="projects" && data.bruno_parent_id==Bruno.storage.getMyPlaceholder()['_id']){
							destination = Bruno.Translation.get('app', 2502, 'html'); //Personal Space
							destination = destination.ucfirst();
						} else {
							parent = Bruno.storage.get(data.bruno_parent_type, data.bruno_parent_id);
							if(parent){
								for (var i in parent){
									if(i.indexOf('+')===0){
										destination = parent[i].ucfirst();
									} else if(destination=="" && i=="-username"){
										destination = parent[i].ucfirst();
									}
								}
							}
						}
						Elem.find("[find=submenu_app_upload_where]").html(destination);
						Elem.find("[find=submenu_app_upload_single_cancel]").click(function(event){
							event.stopPropagation();
							if(typeof data.bruno_type !== 'undefined' && data.bruno_type === 'file' && data.bruno_status !== 'deleted'){
								data.bruno_status = 'deleted';
								$('#app_upload_fileupload').fileupload('option').destroy(event, data);
							}
						});
						Elem.click(function(){
							if(typeof app_upload_files.bruno_files[index] !== 'undefined' && data.bruno_status !== 'deleted' && data.bruno_status !== 'done'){
								submenu_list['app_upload_sub'].app_upload_sub.value = index;
								$.each(that.Wrapper().find('.submenu_deco_next'), function() {
									$(this).removeClass('submenu_deco_next');
								});
								if(submenu_Build("app_upload_sub", that.layer+1)){
									$(this).addClass('submenu_deco_next');
								}
							}
						});
						that.Wrapper().find("[find=submenu_wrapper_content]").append(Elem);
					} else {
						Elem = $('#'+that.id+'_submenu_app_upload_single_'+index);
					}

					if(data.files[0].preview && $.trim(Elem.find("[find=submenu_app_upload_preview_image]").html()) === ''){ //[toto] File staying in cache, memory not cleared?
						if(typeof data.files[0].preview.tagName !== 'undefined' && data.files[0].preview.tagName.toLowerCase() === 'canvas'){
							Elem.find("[find=submenu_app_upload_preview_image]").html(
								'<img src="'+data.files[0].preview.toDataURL()+'" style="width: auto; height: auto;">'
							);
						} else {
							Elem.find("[find=submenu_app_upload_preview_image]").html(
								data.files[0].preview.outerHTML
							);
						}
					}

					Elem.find("[find=submenu_app_upload_progress_pc]").css('width',
						Math.floor(data.bruno_progress) + '%'
					);

					if(data.bruno_progress>=100 && data.bruno_status === 'done'){
						Elem.find("[find=submenu_app_upload_single_cancel]").hide();
						Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
							Bruno.Translation.get('app', 8, 'html') //Complete
						);
					} else {
						Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
							Math.floor(data.bruno_progress) + '%'
						);
					}

					if(data.bruno_status === 'restart'){
						Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_info');
						Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
							data.bruno_error
						);
					} else if(data.bruno_status === 'abort'){
						Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_abort');
						Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
							Bruno.Translation.get('app', 11, 'html') //Stopped
						);
					} else if(data.bruno_status === 'failed'){
						Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_failed');
						Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
							data.bruno_error
						);
					} else if(data.bruno_status === 'error'){
						Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_failed');
						Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
							data.bruno_error
						);
					} else if(data.bruno_status === 'deleted'){
						Elem.find("[find=submenu_app_upload_single_cancel]").hide();
						Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_failed');
						Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
							Bruno.Translation.get('app', 23, 'html') //Canceled
						);
					} else {
						Elem.find("[find=submenu_app_upload_progress_full]").removeClass('submenu_app_upload_progress_full_info');
						Elem.find("[find=submenu_app_upload_progress_full]").removeClass('submenu_app_upload_progress_full_abort');
						Elem.find("[find=submenu_app_upload_progress_full]").removeClass('submenu_app_upload_progress_full_failed');
					}

					if(data.bruno_status === 'uploading' || data.bruno_status === 'restart' ){
						pause = false;
					}

					if(data.bruno_status !== 'done' && data.bruno_status !== 'deleted'){
						finish = false;
					}

					if(data.bruno_status !== 'abort' && data.bruno_status !== 'failed' && data.bruno_status !== 'error'  && data.bruno_status !== 'deleted'){
						retry = false;
					}

					if(data.bruno_status === 'done' || data.bruno_status === 'deleted'){
						var delay = 1000;
						if(data.bruno_status === 'deleted'){
							delay = 1500;
						}
						var Sequence = [
							{ e: Elem, p: 'slideUp', o: { delay: delay } },
							{ e: Elem.children(), p: 'transition.fadeOut', o: { delay: delay, sequenceQueue: false } },
						];
						$.Velocity.RunSequence(Sequence);
					}
				}
			}
		});
			
		if(pause){
			$('#'+that.id+'_submenu_app_upload_function_button_stop').hide();
			$('#'+that.id+'_submenu_app_upload_function_button_start').show();
		} else {
			$('#'+that.id+'_submenu_app_upload_function_button_start').hide();
			$('#'+that.id+'_submenu_app_upload_function_button_stop').show();
		}

		if(retry){
			$('#'+that.id+'_submenu_app_upload_function_button_start').html(
				Bruno.Translation.get('app', 24, 'html') //Retry
			);
		} else {
			$('#'+that.id+'_submenu_app_upload_function_button_start').html(
				Bruno.Translation.get('app', 5, 'html') //Start
			);
		}

		if(finish){
			$('#'+that.id+'_submenu_app_upload_function_button_cancel').css('visibility', 'hidden');
			$('#'+that.id+'_submenu_app_upload_function_button_start').css('visibility', 'hidden');
		} else {
			$('#'+that.id+'_submenu_app_upload_function_button_cancel').css('visibility', 'visible');
			$('#'+that.id+'_submenu_app_upload_function_button_start').css('visibility', 'visible');
		}

	}
	return true;
};

Submenu.prototype.Add_MenuAppUploadSubFile = function() {
	var attribute = this.attribute;
	var submenu_wrapper = this.Wrapper();
	if(typeof attribute === 'undefined'){ attribute = {}; }
	var Elem = null;
	var Elem_bt = null;
	var that = this;
	var pause = true;
	var retry = true;
	var finish = true;
	var bruno_files_index = -1;
	var data;

	if($.type(app_upload_files) === 'object'){
			
		//Upload function buttons
		if($('#'+this.id+'_submenu_app_upload_function').length <= 0){
				
			if(typeof attribute.value === 'undefined'){
				return true;
			}
			bruno_files_index = attribute.value;
			if(typeof app_upload_files.bruno_files[bruno_files_index] === 'undefined'){
				return true;
			}

			Elem = $('#-submenu_app_upload_function').clone();

			Elem.prop("id", this.id+'_submenu_app_upload_function');

			submenu_wrapper.find("[find=submenu_title]").html(
				app_upload_files.bruno_files[bruno_files_index].bruno_name
			);
			submenu_wrapper.find("[find=submenu_wrapper_bottom]").addClass('submenu_bottom base_optionTab');
			submenu_wrapper.find("[find=submenu_wrapper_content]").css('bottom', submenu_wrapper.find("[find=submenu_wrapper_bottom]").height());

			Elem_bt = $('#-submenu_app_upload_function_button').clone();
			Elem_bt.prop("id", this.id+'_submenu_app_upload_function_button'+'_start');
			Elem_bt.html(Bruno.Translation.get('app', 5, 'html')); //Start
			Elem_bt.click(function(event){
				if(app_upload_files.bruno_files[bruno_files_index]){
					app_upload_files.bruno_files[bruno_files_index].bruno_status = 'abort';
					app_upload_files.bruno_files[bruno_files_index].abort(); //Force to reinitialize before any start
					app_upload_files.bruno_files[bruno_files_index].bruno_submit();
				}
			});
			Elem.append(Elem_bt);

			Elem_bt = $('#-submenu_app_upload_function_button').clone();
			Elem_bt.prop("id", this.id+'_submenu_app_upload_function_button'+'_stop');
			Elem_bt.html(Bruno.Translation.get('app', 12, 'html')); //Stop
			Elem_bt.click(function(event){
				if(app_upload_files.bruno_files[bruno_files_index]){
					app_upload_files.bruno_files[bruno_files_index].bruno_status = 'abort';
					app_upload_files.bruno_files[bruno_files_index].abort();
				}
				app_application_bruno.prepare('upload', true);
			});
			Elem_bt.hide();
			Elem.append(Elem_bt);

			Elem_bt = $('#-submenu_app_upload_function_button').clone();
			Elem_bt.prop("id", this.id+'_submenu_app_upload_function_button'+'_cancel');
			Elem_bt.html(Bruno.Translation.get('app', 22, 'html')); //Delete
			Elem_bt.click(function(event){
				if(app_upload_files.bruno_files[bruno_files_index]){
					app_upload_files.bruno_files[bruno_files_index].bruno_status = 'abort';
					app_upload_files.bruno_files[bruno_files_index].abort();//Force to reinitialize before any start
					$('#app_upload_fileupload').fileupload('option').destroy(event, app_upload_files.bruno_files[bruno_files_index]);
				} else {
					$('#'+that.id).find("[find=submenu_wrapper_back]").click();
				}
			});
			Elem.append(Elem_bt);

			submenu_wrapper.find("[find=submenu_wrapper_bottom]").append(Elem);
		} else {
			bruno_files_index = submenu_wrapper.find("[find=submenu_app_upload_sub_index]").val();
		}

		if($.type(app_upload_files.bruno_files[bruno_files_index]) === 'object'){
			data = app_upload_files.bruno_files[bruno_files_index];
			if($('#'+that.id+'_submenu_app_upload_sub').length <= 0){
				Elem = $('#-submenu_app_upload_sub').clone();
				Elem.prop("id", that.id+'_submenu_app_upload_sub');
				Elem.find("[find=submenu_app_upload_size]").html(
					$('#app_upload_fileupload').fileupload('option')._formatFileSize(data.bruno_size)
				);
				Elem.find("[find=submenu_app_upload_sub_index]").val(
					bruno_files_index
				);
				submenu_wrapper.find("[find=submenu_wrapper_content]").append(Elem);
			} else {
				Elem = $('#'+that.id+'_submenu_app_upload_sub');
			}

			if(data.files[0].preview && $.trim(Elem.find("[find=submenu_app_upload_preview_image]").html()) === ''){
				if(typeof data.files[0].preview.tagName !== 'undefined' && data.files[0].preview.tagName.toLowerCase() === 'canvas'){
					Elem.find("[find=submenu_app_upload_preview_image]").addClass('submenu_app_upload_sub_preview_canvas');
					Elem.find("[find=submenu_app_upload_preview_image]").html(
						'<img src="'+data.files[0].preview.toDataURL()+'" style="width: auto; height: auto;">'
					);
				} else {
					Elem.find("[find=submenu_app_upload_preview_image]").html(
						data.files[0].preview
					);
					if(typeof data.files[0].preview.tagName !== 'undefined' && data.files[0].preview.tagName.toLowerCase() === 'video'){
						Elem.find("[find=submenu_app_upload_preview_image]").addClass('submenu_app_upload_sub_preview_player');
					}
				}
			}

			Elem.find("[find=submenu_app_upload_progress_pc]").css('width',
				Math.floor(data.bruno_progress) + '%'
			);

			if(data.bruno_progress>=100 && data.bruno_status === 'done'){
				Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
					Bruno.Translation.get('app', 8, 'html') //Complete
				);
			} else {
				Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
					Math.floor(data.bruno_progress) + '%'
				);
			}

			if(data.bruno_status === 'abort'){
				Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_abort');
				Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
					Bruno.Translation.get('app', 11, 'html') //Stopped
				);
			} else if(data.bruno_status === 'failed'){
				Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_failed');
				Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
					data.bruno_error
				);
			} else if(data.bruno_status === 'error'){
				Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_failed');
				Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
					data.bruno_error
				);
			} else if(data.bruno_status === 'deleted'){
				Elem.find("[find=submenu_app_upload_progress_full]").addClass('submenu_app_upload_progress_full_failed');
				Elem.find("[find=submenu_app_upload_progress_pc_text]").html(
					Bruno.Translation.get('app', 23, 'html') //Canceled
				);
			} else {
				Elem.find("[find=submenu_app_upload_progress_full]").removeClass('submenu_app_upload_progress_full_abort');
				Elem.find("[find=submenu_app_upload_progress_full]").removeClass('submenu_app_upload_progress_full_failed');
			}

			if(data.bruno_status === 'uploading'){
				pause = false;
			}

			if(data.bruno_status !== 'done' && data.bruno_status !== 'deleted'){
				finish = false;
			}

			if(data.bruno_status !== 'abort' && data.bruno_status !== 'failed' && data.bruno_status !== 'error'  && data.bruno_status !== 'deleted'){
				retry = false;
			}

			if(pause){
				$('#'+that.id+'_submenu_app_upload_function_button_stop').hide();
				$('#'+that.id+'_submenu_app_upload_function_button_start').show();
			} else {
				$('#'+that.id+'_submenu_app_upload_function_button_start').hide();
				$('#'+that.id+'_submenu_app_upload_function_button_stop').show();
			}

			if(retry){
				$('#'+that.id+'_submenu_app_upload_function_button_start').html(
					Bruno.Translation.get('app', 24, 'html') //Retry
				);
			} else {
				$('#'+that.id+'_submenu_app_upload_function_button_start').html(
					Bruno.Translation.get('app', 5, 'html') //Start
				);
			}

			if(finish){
				$('#'+that.id+'_submenu_app_upload_function_button_cancel').css('visibility', 'hidden');
				$('#'+that.id+'_submenu_app_upload_function_button_start').css('visibility', 'hidden');
			} else {
				$('#'+that.id+'_submenu_app_upload_function_button_cancel').css('visibility', 'visible');
				$('#'+that.id+'_submenu_app_upload_function_button_start').css('visibility', 'visible');
			}
		}
	}
	//Free memory
	delete submenu_wrapper;
	return true;
};

app_upload_files.getData = function(temp_id){
	for(var i in app_upload_files.bruno_files){
		if(app_upload_files.bruno_files[i].bruno_temp_id == temp_id){
			return app_upload_files.bruno_files[i];
		}
	}
	return false;
};
