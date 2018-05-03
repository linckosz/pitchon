submenu_list['app_upload_all'] = {
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 4, 'html'), //Uploads
	},
	"app_upload_all": {
		"style": "app_upload_all",
		"title": "Upload all", //This title will be not used
	},
};

submenu_list['app_upload_sub'] = {
	"_title": {
		"style": "title",
		"title": Bruno.Translation.get('app', 21, 'html'), //Unknown
	},
	"app_upload_sub": {
		"style": "app_upload_sub",
		"title": "Upload single", //This title will be not used
		"value": -1, //This must be previously updated before opening the menu
	},
};

//This function is called only at the file submit moment because it can be different per file
function app_upload_prepare_log(parent_type, parent_id, parent_md5, parent_file_id, parent_data, temp_id, precompress, real_orientation){
	if($.isNumeric(parent_id)){
		parent_id = parseInt(parent_id, 10)
	}
	$('#app_upload_parent_type').val(parent_type);
	$('#app_upload_parent_id').val(parent_id);
	$('#app_upload_parent_md5').val(parent_md5);
	$('#app_upload_parent_file_id').val(parent_file_id);
	$('#app_upload_parent_data').val(parent_data);
	$('#app_upload_temp_id').val(temp_id);
	$('#app_upload_precompress').val(precompress);
	$('#app_upload_real_orientation').val(real_orientation);
}

function app_upload_set_launcher(parent_type, parent_id, parent_md5, parent_file_id, parent_data, show_submenu, start, temp_id, precompress){
	if(typeof show_submenu == 'undefined'){ show_submenu = false; }
	if(typeof start == 'undefined'){ start = false; }
	if(typeof precompress == 'undefined'){ precompress = true; }
	if(typeof real_orientation == 'undefined'){ real_orientation = true; }
	if(typeof parent_file_id == 'boollean' && parent_file_id===true){ parent_file_id = 'file_id'; } //We default to file_id attribute
	app_upload_auto_launcher.parent_type = parent_type;
	app_upload_auto_launcher.parent_id = parent_id;
	app_upload_auto_launcher.parent_md5 = parent_md5;
	app_upload_auto_launcher.parent_file_id = parent_file_id;
	app_upload_auto_launcher.parent_data = JSON.stringify(parent_data);
	app_upload_auto_launcher.show_submenu = show_submenu;
	app_upload_auto_launcher.start = start;
	app_upload_auto_launcher.temp_id = temp_id;
	app_upload_auto_launcher.precompress = precompress;

	if(precompress && isIOS){
		app_upload_auto_launcher.real_orientation = false;
	} else {
		app_upload_auto_launcher.real_orientation = true;
	}

	if(precompress){
		//If we cannot compress on front, it will be on backend
		$('#app_upload_fileupload').fileupload('option', {disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),});
	} else {
		$('#app_upload_fileupload').fileupload('option', {disableImageResize: true,});
	}
}

function app_upload_open_photo_single(parent_type, parent_id, parent_md5, parent_file_id, parent_data, show_submenu, start, temp_id){
	if(typeof temp_id == "undefined"){
		temp_id = md5(Math.random());
	}
	app_upload_set_launcher(parent_type, parent_id, parent_md5, parent_file_id, parent_data, show_submenu, start, temp_id, true);
	$('#app_upload_form_photo_single').click();
	return temp_id;
}

function app_upload_open_video_single(parent_type, parent_id, parent_md5, parent_file_id, parent_data, show_submenu, start, temp_id){
	if(typeof temp_id == "undefined"){
		temp_id = md5(Math.random());
	}
	app_upload_set_launcher(parent_type, parent_id, parent_md5, parent_file_id, parent_data, show_submenu, start, temp_id, true);
	$('#app_upload_form_video_single').click();
	return temp_id;
}

var app_upload_auto_launcher_timeout;
var app_upload_auto_launcher = {
	parent_type: false,
	parent_id: false,
	parent_md5: false,
	parent_file_id: false,
	parent_data: {},
	show_submenu: false,
	start: true,
	temp_id: false,
	real_orientation:true,
	init: function(){
		this.parent_type = false;
		this.parent_id = false;
		this.parent_md5 = false;
		this.parent_file_id = false;
		this.parent_data = {};
		this.show_submenu = false;
		this.start = true;
		this.temp_id = false;
		this.real_orientation = true;
	},
};

//This help to clear all intervals
var app_upload_start_interval = {};

$(function() {
	//Do not use 'use strict', it makes the code heavier, even if it's better for conventional coding

	$('#app_upload_fileupload').fileupload({
		dataType: 'json',
		url: location.protocol+'//'+document.domain+'/api/file/upload', //Bruno update
		url_result: location.protocol+'//'+document.domain+'/api/file/result?%s',
		disableImageResize: true, //Bruno update
		imageMaxWidth: 1024,
		imageMaxHeight: 1024,
		imageQuality: 0.75,

		imageOrientation: true, //Bruno update
		singleFileUploads: true, //Bruno update
		minFileSize: 0, //Bruno update
		autoUpload: false, //Bruno update
		maxFileSize: 1000000000, //Bruno update (limit to 1GB)
		bitrateInterval: 1000, //Bruno update (display every second, which is more readable)
		loadImageMaxFileSize: 100000000, //Bruno update (limit to 100Mb)
		loadImageFileTypes: /^image\/.*$/, //Bruno update
		previewMaxWidth: 256,
		previewMaxHeight: 256,
		//maxChunkSize: 10000, //100KB [toto] Chunk will help to manage the Pause function but need a modification on back side
		messages: {
			maxNumberOfFiles: Bruno.Translation.get('app', 13, 'html'), //Maximum number of files exceeded
			acceptFileTypes: Bruno.Translation.get('app', 14, 'html'), //File type not allowed
			maxFileSize: Bruno.Translation.get('app', 15, 'html'), //File is too large
			minFileSize: Bruno.Translation.get('app', 16, 'html'), //File is too small
			uploadedBytes: Bruno.Translation.get('app', 17, 'html'), //Uploaded bytes exceed file size
		},

		//Array used to smooth the uploading speed and time remaining display
		bruno_bitrate: [],
		bruno_time: [],

		/*
		Info: This data is to access some variable and functions outside a callback
		$('#app_upload_fileupload').data().blueimpFileupload
		*/

		//data => File object
		always: function(event, data) {
			$('#app_upload_fileupload').fileupload('option').progressall(event, this);
		},

		//data => File object
		add: function(event, data) {
			if (typeof event != 'undefined' && event && event.isDefaultPrevented()) { return false; }
			var that = $('#app_upload_fileupload').fileupload('option');

			that.reindex(event, this);

			app_upload_files.bruno_files[app_upload_files.bruno_files_index] = data;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_type = 'file';
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_files_index = app_upload_files.bruno_files_index;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_status = 'pause';
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_error = null;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_progress = 0;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_name = data.files[0].name;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_size = data.files[0].size;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_parent_id = app_upload_auto_launcher.parent_id;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_parent_type = app_upload_auto_launcher.parent_type;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_parent_md5 = app_upload_auto_launcher.parent_md5;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_parent_file_id = app_upload_auto_launcher.parent_file_id;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_parent_data = app_upload_auto_launcher.parent_data;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_precompress = app_upload_auto_launcher.precompress;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_real_orientation = app_upload_auto_launcher.real_orientation;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_start = app_upload_auto_launcher.start;
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_try = 2;
			//bruno_temp_id is setup later

			//Note: This function help to avoid a trouble issue that would completely stop the file upoading system if it submits while the staus is pending
			app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_submit = function(){
				var temp_index = this.bruno_files_index;
				var nbr_uploading = 0;
				for(var i in app_upload_files.bruno_files){
					if(i!=temp_index && app_upload_files.bruno_files[i].bruno_status == 'uploading'){
						nbr_uploading++;
					}
					if(nbr_uploading>=app_upload_files.bruno_limit){
						break;
					}
				}
				if(nbr_uploading>=app_upload_files.bruno_limit || this.state() == 'pending'){
					clearInterval(app_upload_start_interval[temp_index]);
					app_upload_start_interval[temp_index] = setInterval(function(temp_index){
						var nbr_uploading = 0;
						for(var i in app_upload_files.bruno_files){
							if(i!=temp_index && app_upload_files.bruno_files[i].bruno_status == 'uploading'){
								nbr_uploading++;
							}
							if(nbr_uploading>=app_upload_files.bruno_limit){
								break;
							}
						}
						if(nbr_uploading<app_upload_files.bruno_limit){
							if(typeof app_upload_files.bruno_files[temp_index] == "undefined"){
								clearInterval(app_upload_start_interval[temp_index]);
							} else if(app_upload_files.bruno_files[temp_index].state()!='pending'){
								clearInterval(app_upload_start_interval[temp_index]);
								app_upload_files.bruno_files[temp_index].submit();
							}
						}
					}, 300, temp_index);
				} else {
					app_upload_files.bruno_files[temp_index].submit();
				}
			};

			if(app_upload_auto_launcher.temp_id){
				app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_temp_id = app_upload_auto_launcher.temp_id;
				app_upload_auto_launcher.temp_id = md5(Math.random());
			} else {
				app_upload_files.bruno_files[app_upload_files.bruno_files_index].bruno_temp_id = md5(Math.random());
			}

			//Reinitialise display information
			that.bruno_bitrate.length = [];
			that.bruno_time.length = [];

			if('process' in data){
				data.process(function() {
					var result = {};
					try {
						//Bug if the canvas is not loaded => 'parseMetaData' of undefined
						result = $('#app_upload_fileupload').fileupload('process', data);
					} catch(event){

					}
					return result;
				})
				.fail(function() {
					if (data.files[0].error) {
						data.bruno_status = 'failed';
						data.errorThrown = 'failed';
						data.bruno_error = data.files[0].error;
						data.abort();
					}
				});
			}

			app_upload_files.bruno_files_index++;
			that.progressall(event, this);

			//Open submenu by default
			if(app_upload_auto_launcher.show_submenu){
				submenu_Build("app_upload_all", -1, false);
			}
			//Do not auto start by default
			if(app_upload_auto_launcher.start){
				var temp_index = app_upload_files.bruno_files_index - 1;
				app_upload_files.bruno_files[temp_index].bruno_submit();
			}

			clearTimeout(app_upload_auto_launcher_timeout);
			app_upload_auto_launcher_timeout = setTimeout(function() {
				app_upload_auto_launcher.init();
			}, 50);
			

			//This is used to force the preview to appear because the preview variable is not available at once right after the object creation
			setTimeout(function(data) {
				$('#app_upload_fileupload').fileupload('option').progressall(event, this);
			}, 60, data);
			//The second timeout is just in case the first one didn't worked
			setTimeout(function(data) {
				$('#app_upload_fileupload').fileupload('option').progressall(event, this);
			}, 2000, data);

		},

		//It will decrement the file index to rewrite over previously allocated memory space.
		//data => File object
		reindex: function(event, data) {
			if (typeof event != 'undefined' && event && event.isDefaultPrevented()) { return false; }
			var that = $('#app_upload_fileupload').fileupload('option');
			while(app_upload_files.bruno_files_index > 0 && typeof app_upload_files.bruno_files[app_upload_files.bruno_files_index-1] == 'undefined'){
				app_upload_files.bruno_files_index--;
			}
		},

		//data => File object
		submit: function(event, data) {
			clearInterval(app_upload_start_interval[data.bruno_files_index]);
			app_upload_files.bruno_files[data.bruno_files_index].bruno_status = 'uploading';
			var parent_type = app_upload_files.bruno_files[data.bruno_files_index].bruno_parent_type;
			var parent_id = app_upload_files.bruno_files[data.bruno_files_index].bruno_parent_id;
			var parent_md5 = app_upload_files.bruno_files[data.bruno_files_index].bruno_parent_md5;
			var parent_file_id = app_upload_files.bruno_files[data.bruno_files_index].bruno_parent_file_id;
			var parent_data = app_upload_files.bruno_files[data.bruno_files_index].bruno_parent_data;
			var temp_id = app_upload_files.bruno_files[data.bruno_files_index].bruno_temp_id;
			var precompress = app_upload_files.bruno_files[data.bruno_files_index].bruno_precompress;
			var real_orientation = app_upload_files.bruno_files[data.bruno_files_index].bruno_real_orientation;
			app_upload_prepare_log(parent_type, parent_id, parent_md5, parent_file_id, parent_data, temp_id, precompress, real_orientation);
			app_application_bruno.prepare('upload', true);
		},
		
		//data => File object
		done: function(event, data) {
			if (typeof event != 'undefined' && event && event.isDefaultPrevented()) { return false; }
			var that = $('#app_upload_fileupload').fileupload('option');
			if(data.result && data.result.error){
				app_upload_files.bruno_files[data.bruno_files_index].bruno_status = 'error';
				app_upload_files.bruno_files[data.bruno_files_index].bruno_error = Bruno.Translation.get('app', 18, 'html'); //Server error
				if(typeof data.result.msg == 'string'){
					app_upload_files.bruno_files[data.bruno_files_index].bruno_error = data.result.msg;
				}
				if(app_upload_files.bruno_files[data.bruno_files_index].bruno_try>0){ //For any kind of error, we try at least once
					if(data.result.flash && data.result.flash.resignin){ //Only try once if it's pure file error, more if it's about resigning
						app_upload_files.bruno_files[data.bruno_files_index].bruno_try--;
					} else {
						app_upload_files.bruno_files[data.bruno_files_index].bruno_try = 0;
					}
					app_upload_files.bruno_files[data.bruno_files_index].bruno_status = 'restart';
					app_upload_files.bruno_files[data.bruno_files_index].bruno_error = Bruno.Translation.get('app', 59, 'html'); //Your file upload is restarting
				} else if(app_upload_files.bruno_files[data.bruno_files_index].bruno_try<=0){
					app_upload_files.bruno_files[data.bruno_files_index].bruno_try = 2;
				}
			} else {
				app_upload_files.bruno_files[data.bruno_files_index].bruno_status = 'done';
				$('#app_upload_fileupload').fileupload('option').progressall(event, this);
				app_application_bruno.prepare('upload', true, true); //procedural launch
				delete app_upload_files.bruno_files[data.bruno_files_index];
				clearInterval(app_upload_start_interval[data.bruno_files_index]);
				that.reindex(event, this);
				app_application_bruno.prepare('file');
				//Force to update elements if the function is available
				if(data.result && data.result.extra && typeof storage_cb_success == 'function'){
					var msg = '';
					var show = false;
					var status = 200;
					var extra = null;
					if(typeof data.result.msg != 'undefined'){ msg = data.result.msg; }
					if(typeof data.result.show != 'undefined'){ show = data.result.show; }
					if(typeof data.result.status != 'undefined'){ status = data.result.status; }
					if(typeof data.result.extra != 'undefined'){ extra = data.result.extra; }
					storage_cb_success(msg, show, status, extra);
				}
			}
			app_application_bruno.prepare('upload', true);
			Bruno.storage.getLatest();
		},

		//data => File object
		progress: function(event, data) {
			if (typeof event != 'undefined' && event && event.isDefaultPrevented()) { return false; }
			var that = $('#app_upload_fileupload').fileupload('option');
			var progress = Math.floor( 100 * data.loaded/data.total );
			if(progress<0){
				progress=0;
			} else if(progress>100){
				progress=100;
			}
			app_upload_files.bruno_files[data.bruno_files_index].bruno_progress = progress;
		},

		//data => Main object
		progressall: function(event, data) {
			if (typeof event != 'undefined' && event && event.isDefaultPrevented()) { return false; }
			var that = $('#app_upload_fileupload').fileupload('option');
			app_upload_files.bruno_numberOfFiles = that._numberOfFiles();
			if($.type(data) == 'object' && data.loaded && data.total && data.bitrate){
				var progress = Math.floor( 100 * data.loaded/data.total );
				if(app_upload_files.bruno_numberOfFiles<=0){
					progress = 100;
					//Reinitialise display information
					that.bruno_bitrate.length = [];
					that.bruno_time.length = [];
				} else if(progress<0){
					progress = 0;
				} else if(progress>100){
					progress = 100;
				}
				app_upload_files.bruno_progressall = progress;
				app_upload_files.bruno_britate = that._formatBitrate(data.bitrate);
				if(data.bitrate>0){
					app_upload_files.bruno_time = that._formatTime((data.total - data.loaded) * 8 / data.bitrate);
				}
				app_upload_files.bruno_loaded = that._formatFileSize(data.loaded);
				app_upload_files.bruno_total = that._formatFileSize(data.total);
				app_upload_files.bruno_size = that._formatComplete(data.loaded, data.total);
			} else {
				if(app_upload_files.bruno_numberOfFiles<=0){
					app_upload_files.bruno_progressall = 100;
					//Reinitialise display information
					that.bruno_bitrate.length = [];
					that.bruno_time.length = [];
				} else {
					var bruno_progress = false;
					$.each(app_upload_files.bruno_files, function(index, data){
						if($.type(data) == 'object'){
							if(data.bruno_progress > 0){
								return bruno_progress = true;
							}
						}
					});
					if(!bruno_progress){
						app_upload_files.bruno_progressall = 0;
					}
				}
				app_upload_files.bruno_britate = that._formatBitrate(0);
				app_upload_files.bruno_time = that._formatTime(0);
				app_upload_files.bruno_loaded = that._formatFileSize(0);
				app_upload_files.bruno_total = that._formatFileSize(0);
				app_upload_files.bruno_size = that._formatComplete(0, 0);
			}
			app_application_bruno.prepare('upload', true);
		},

		//data => File object
		fail: function(event, data) {
			if (typeof event != 'undefined' && event && event.isDefaultPrevented()) { return false; }
			if (data.bruno_status == 'failed') {
				data.bruno_error = data.files[0].error || data.errorThrown || Bruno.Translation.get('app', 9, 'html'); //Unknown error
			} else if (data.bruno_status != 'error') {
				app_upload_files.bruno_files[data.bruno_files_index].bruno_status = 'error';
				app_upload_files.bruno_files[data.bruno_files_index].bruno_error = Bruno.Translation.get('app', 18, 'html'); //Server error
			}
			clearInterval(app_upload_start_interval[data.bruno_files_index]);
			$('#app_upload_fileupload').fileupload('option').progressall(event, this);
		},

		//data => File object
		destroy: function(event, data) {
			if (typeof event != 'undefined' && event && event.isDefaultPrevented()) { return false; }
			var that = $('#app_upload_fileupload').fileupload('option');
			app_upload_files.bruno_files[data.bruno_files_index].abort();
			app_upload_files.bruno_files[data.bruno_files_index].bruno_status = 'deleted';
			app_upload_files.bruno_files[data.bruno_files_index].bruno_progress = 0;
			app_application_bruno.prepare('upload', true, true); //procedural launch
			$('#app_upload_fileupload').fileupload('option').progressall(event, this);
			delete app_upload_files.bruno_files[data.bruno_files_index];
			clearInterval(app_upload_start_interval[data.bruno_files_index]);
			that.reindex(event, this);
			$('#app_upload_fileupload').fileupload('option').progressall(event, this);
		},


		_startHandler: function(event) {
			if(typeof event != 'undefined' && event){ event.preventDefault(); }
			var that = this;
			$.each(app_upload_files.bruno_files, function(index, data){
				if($.type(data) == 'object'){
					if(data.bruno_status == 'failed'){
						data.bruno_status = 'deleted';
						that.destroy(event, data);
					} else if(typeof data.bruno_type != 'undefined' && data.bruno_type == 'file'){
						data.bruno_status = 'pause';
						data.bruno_submit();
					}
				}
			});
			app_application_bruno.prepare('upload', true);
		},

		_cancelHandler: function(event) {
			if(typeof event != 'undefined'){ event.preventDefault(); }
			var that = this;
			$.each(app_upload_files.bruno_files, function(index, data){
				if($.type(data) == 'object'){
					if(typeof data.bruno_type != 'undefined' && data.bruno_type == 'file' && data.bruno_status != 'abort'){
						data.bruno_status = 'abort';
						data.abort();
					}
				}
			});
			app_application_bruno.prepare('upload', true);
		},

		_deleteHandler: function(event) {
			if(typeof event != 'undefined'){ event.preventDefault(); }
			var that = this;
			$.each(app_upload_files.bruno_files, function(index, data){
				if($.type(data) == 'object'){
					if(typeof data.bruno_type != 'undefined' && data.bruno_type == 'file' && data.bruno_status != 'deleted'){
						data.bruno_status = 'deleted';
						that.destroy(event, data);
					}
				}
			});
			app_application_bruno.prepare('upload', true, true); //procedural launch
		},

		_numberOfFiles: function(){
			var num = 0;
			$.each(app_upload_files.bruno_files, function(index, data){
				if($.type(data) == 'object'){
					if(typeof data.bruno_type != 'undefined' && data.bruno_type == 'file'){
						num++;
					}
				}
			});
			return num;
		},

		_formatFileSize: function(bytes) {
			if (typeof bytes != 'number') {
				return '?';
			} else if (bytes >= 1073741824) {
				return (bytes / 1073741824).toFixed(2) + ' GB';
			} else if (bytes >= 1048576) {
				return (bytes / 1048576).toFixed(2) + ' MB';
			} else {
				return (bytes / 1024).toFixed(0) + ' KB';
			}
		},

		_formatBitrate: function(bits) {
			if (typeof bits != 'number') {
				return '?';
			}

			var that = $('#app_upload_fileupload').fileupload('option');
			that.bruno_bitrate.unshift(bits);
			if(that.bruno_bitrate.length > 4){
				that.bruno_bitrate.length = 4;
			}

			var length = that.bruno_bitrate.length;
			var divide = 0;
			var sum = 0;
			$.each(that.bruno_bitrate, function(index, data){
				sum = sum + data;
				divide = divide + 1;
			});

			if (divide<=0) {
				bits = 0;
			} else {
				bits = sum / divide;
			}

			if (bits >= 1073741824) {
				return (bits / 1073741824).toFixed(1) + ' Gbit/s';
			} else if (bits >= 1048576) {
				return (bits / 1048576).toFixed(1) + ' Mbit/s';
			} else if (bits >= 1024) {
				return (bits / 1024).toFixed(0) + ' kbit/s';
			} else {
				return bits.toFixed(0) + ' bit/s';
			}
		},

		_formatComplete: function(loaded, total) {
			if (typeof loaded != 'number' || typeof total != 'number') {
				return '?';
			} else if (total >= 1073741824) {
				return (loaded / 1073741824).toFixed(2) + ' GB / ' + (total / 1073741824).toFixed(2) + ' GB';
			} else if (total >= 1048576) {
				return (loaded / 1048576).toFixed(2) + ' MB / ' + (total / 1048576).toFixed(2) + ' MB';
			} else {
				return (loaded / 1024).toFixed(0) + ' KB / ' + (total / 1024).toFixed(0) + ' KB';
			}
		},

		_formatPercentage: function(value) {
			return Math.floor(value) + ' %';
		},

		_formatTime: function(seconds) {
			if (typeof seconds != 'number') {
				return '?';
			}

			var that = $('#app_upload_fileupload').fileupload('option');
			that.bruno_time.unshift(seconds);
			if(that.bruno_time.length > 4){
				that.bruno_time.length = 4;
			}

			var length = that.bruno_time.length;
			var divide = 0;
			var sum = 0;
			$.each(that.bruno_time, function(index, data){
				sum = sum + data;
				divide = divide + 1;
			});

			if (divide<=0) {
				seconds = 0;
			} else {
				seconds = sum / divide;
			}

			var date = new Date(seconds * 1000),
				days = Math.floor(seconds / 86400);
			days = days ? days + 'd ' : '';
			return days +
				('0' + date.getUTCHours()).slice(-2) + ':' +
				('0' + date.getUTCMinutes()).slice(-2) + ':' +
				('0' + date.getUTCSeconds()).slice(-2);
		},
	});

	$("#app_upload_fileupload").on('submit', function(event) {
		 if(typeof event != 'undefined'){ event.preventDefault(); } else { event = null; } //Disable submit action by click
		 return false; //Disable by JS action
	});

});
