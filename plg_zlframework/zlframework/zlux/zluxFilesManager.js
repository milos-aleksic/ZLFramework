/* ===================================================
 * ZLUX Files Manager v0.2
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function(options){
		this.options = $.extend({}, this.options, options);
		this.events = {};
	};
	Plugin.prototype = $.extend(Plugin.prototype, $.zluxMain.prototype, {
		name: 'zluxFilesManager',
		options: {
			"root": 'images', // relative path to the root
			"extensions": '', // comma separated values
			"ajax_url": ''
		},
		events: {},
		initialize: function(target, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;

			// http://srobbin.com/jquery-plugins/approach/
			// if placeholder set the trigger button
			// $('<a class="btn btn-mini" href="#"><i class="icon-plus-sign"></i>Add Item</a>')

			// save target
			$this.target = target;

			// init filesmanager
			$this.filesmanager = $('<div class="zl-bootstrap zlux-filesmanager" />').appendTo(target);
			$this.initDataTable($this.filesmanager);
		},
		initDataTable: function(wrapper) {
			var $this = this,
				source = $this.options.ajax_url+'&task=FilesManager&root='+$this.options.root;

			// set table
			$('<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" />')
			.appendTo(wrapper);

			// init dataTable
			$this.oTable = $('table', wrapper).dataTable({
				"sDom": "f<'row-fluid'<'span12'B>><'row-fluid'<'span12't>><'row-fluid row-footer'<'span6'i><'span6'p>>",
				"oLanguage": {
					"sSearch": "_INPUT_",
					"sEmptyTable": "No Files found",
					"sInfoEmpty": "",
					"sInfo": "Showing _END_ of _TOTAL_ Files"
				},
				"sAjaxUrl": $this.options.ajax_url,
				"sAjaxSource": source,
				"sServerMethod": "POST",
				"sStartRoot": $this.options.root,
				// "bServerSide": true,
				"iDisplayLength": 9,
				"aoColumns": [
					{ 
						"sTitle": "", "mData": "type", "bSearchable": false, "sWidth": "14px", "sClass": "column-icon",
						"mRender": function ( data, type, full ) {
							if (type == 'display') {
								return '<i class="icon-'+(data == 'folder' ? 'folder-close' : 'file-alt')+'"></i>';
							} else {
								return data;
							}
						}
					},
					{ 
						"sTitle": "Name", "mData": "name", "sClass": "column-name",
						"mRender": function ( data, type, full ) {
							if (type == 'display') {
								return '<a href="#">'+data+'</a>';
							} else {
								return data;
							}
						},
						"fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
							$(nTd).parent('tr').attr('data-path', oData.path)
						}
					}
					// {
					// 	"sTitle": "Size", "mData": "size", "bSearchable": false, "sClass": "right", "sWidth": "100px",
					// 	"mRender": function ( data, type, full ) {
					// 		return type == 'display' ? (full.type == 'folder' ? '* ' : '')+data.display : data.value;
					// 	}
					// }
				],
				"aoColumnDefs": {
					"bVisible": false, "aTargets": [ 2 ]
				},
				"aaSorting": [ [0,'desc'], [1,'asc'] ], // init sort
				"fnServerData": function ( sUrl, aoData, fnCallback, oSettings ) {
					$this._fnServerData(sUrl, aoData, fnCallback, oSettings);
				},
				"fnServerParams": function (aoData) {
					aoData.push({ "name": "extensions", "value": $this.options.extensions });
				},
				"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
					$(nRow).attr('data-type', aData.type);
				},
				"fnInitComplete": function(oSettings, data) {
					var input_filter = $('.dataTables_filter', wrapper)
					
					.append(
						// set search icon
						$('<i class="icon-search" />'),
						// and the cancel button
						$('<i class="icon-remove zlux-ui-dropdown-unselect" />').hide().on('click', function(){
							$('input', input_filter).val('');
							$(this).hide();
							// reset the filter
							$this.oTable.fnFilter('');
						})
					);

					$('input', input_filter).on('keyup', function(){
						if ($(this).val() == '') {
							$('.zlux-ui-dropdown-unselect', input_filter).hide();
						} else {
							$('.zlux-ui-dropdown-unselect', input_filter).show();
						}
					})

					// trigger table init event
					$this.trigger("InitComplete");
				},
				"fnPreDrawCallback": function(oSettings) {
					// show processing
					$this.zluxdialog.spinner('show');
				},
				"fnDrawCallback": function(oSettings) {
					// pagination hide/show
					var oPaging = oSettings.oInstance.fnPagingInfo(),
						pagination = $('.dataTables_paginate', $(oSettings.nTableWrapper)).closest('.row-fluid');
					(oPaging.iTotalPages <= 1) && pagination.hide() || pagination.show();

					// update dialog scrollbar
					$this.zluxdialog.scrollbar('refresh');

					// hide processing if root available
					oSettings.sCurrentRoot &&
						$this.zluxdialog.spinner('hide');
				}
			})

			/* Add a click handler to the rows */
			.on('click', 'tbody [data-type=folder] a', function(e) {
				var row = $(this).closest('tr'),
					oSettings = $this.oTable.fnSettings(),
					sUrl = oSettings.sAjaxSource.replace(/\&root=.+/, '');
				$this.oTable.fnReloadAjax(sUrl+'&root='+oSettings.sCurrentRoot+'/'+row.data('path'));
				return false;
			})

			// select file event
			.on('click', 'tbody [data-type=file] a', function(){

				var row = $(this).closest('tr').attr('data-checked', 'true'),
					col = $(this).closest('td'),
					oSettings = $this.oTable.fnSettings();
					
				// if input
				// if ($this.target[0].tagName == 'INPUT'){
				// 	row.siblings('[data-checked]').removeAttr('data-checked');
				// 	var value = oSettings.sCurrentRoot+'/'+row.data('path')
				// 	$this.target.val(value).trigger('change');
				// } else {}
				
				return false;
			})

			// remove file event
			.on('click', 'tbody [data-type=file] .icon-remove', function(){

				var row = $(this).closest('tr').removeAttr('data-checked'),
					col = $(this).closest('td');
					
				// if input
				// if ($this.target[0].tagName == 'INPUT'){
				// 	$this.target.val('').trigger('change');
				// } else {
				// 	// $this._addReleatedItem($this.oTable, wrapper.children('div'), col);
				// }
				
				return false;
			})
		},
		/**
		 * Reloads the Table content
		 */
		reload: function() {
			$this.oTable.fnSettings().bReloading = true;
			$this.oTable.fnReloadAjax($this.oTable.fnSettings().sAjaxSource);
		},
		_fnServerData: function( sUrl, aoData, fnCallback, oSettings ) {
			$this = this;

			// create cache object
			oSettings.aAjaxDataCache = oSettings.aAjaxDataCache ? oSettings.aAjaxDataCache : [];
			
			oSettings.jqXHR = $.ajax({
				"url": sUrl,
				"data": aoData,
				"beforeSend": function(jqXHR, settings){
					// check if the data is cached
					var cached = false,
						root = sUrl.match(/\&root=.+/),
						root = root ? root.pop().replace(/\&root=/, '').replace(/\/$/, '') : '';

					if (!oSettings.bReloading){
						$.each(oSettings.aAjaxDataCache, function(i, v){
							if (v.root == root){
								var json = v.data;

								// save root
								oSettings.sCurrentRoot = v.root;

								// trigger events
								$(oSettings.oInstance).trigger('xhr', [oSettings, json]);
								fnCallback( json );

								// avoid ajax call
								cached = true;
							}
						})
					}

					if (cached) return false; else {
						// show processing
						$this.zluxdialog.spinner('show');
					}
				},
				"success": function (json) {
					if ( json.sError ) {
						oSettings.oApi._fnLog( oSettings, 0, json.sError );
					}

					// save directory root
					oSettings.sCurrentRoot = json.root;

					// reset cache to 0 if reloading
					if (oSettings.bReloading) oSettings.aAjaxDataCache = [];

					// upload cache data or save new cache
					var cached = false;
					$.each(oSettings.aAjaxDataCache, function(i, v){
						if (v.root == json.root){
							oSettings.aAjaxDataCache[i] = {'root':json.root, 'data':json};
							cached = true; return false;
						}
					})
					if (!cached) oSettings.aAjaxDataCache.push({'root':json.root, 'data':json});

					// set reloading to false
					oSettings.bReloading = false;

					// trigger events
					$(oSettings.oInstance).trigger('xhr', [oSettings, json]);
					fnCallback( json );
				},
				"dataType": "json",
				"cache": false,
				"type": oSettings.sServerMethod,
				"error": function (xhr, error, thrown) {
					if ( error == "parsererror" ) {
						oSettings.oApi._fnLog( oSettings, 0, "DataTables warning: JSON data from "+
							"server could not be parsed. This is caused by a JSON formatting error." );
					}
				}
			});
		}
	});
	// Don't touch
	$.fn[Plugin.prototype.name] = function() {
		if (this.data(Plugin.prototype.name)) return; // standart check to avoid duplicate inits
		var args   = arguments;
		var method = args[0] ? args[0] : null;
		return this.each(function() {
			var element = $(this);
			if (Plugin.prototype[method] && element.data(Plugin.prototype.name) && method != 'initialize') {
				element.data(Plugin.prototype.name)[method].apply(element.data(Plugin.prototype.name), Array.prototype.slice.call(args, 1));
			} else if (!method || $.isPlainObject(method)) {
				var plugin = new Plugin();
				if (Plugin.prototype['initialize']) {
					plugin.initialize.apply(plugin, $.merge([element], args));
				}
				element.data(Plugin.prototype.name, plugin);
			} else {
				$.error('Method ' +  method + ' does not exist on jQuery.' + Plugin.name);
			}
		});
	};
	// save the plugin for global use
	$.zluxFilesManager = Plugin;
})(jQuery);


/* ===================================================
 * ZLUX Dialog Files Manager v0.1
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function(options){
		this.options = $.extend({}, this.options, options);
		this.events = {};
	};
	Plugin.prototype = $.extend(Plugin.prototype, $.zluxFilesManager.prototype, {
		name: 'zluxDialogFilesManager',
		options: {
			"title": 'Files Manager',
			"full_mode": 0
		},
		events: {},
		initialize: function(input, options) {
			this.options = $.extend({}, $.zluxFilesManager.prototype.options, this.options, options);
			var $this = this;

			// set main wrapper arount the input
			$this.wrapper = input.wrap($('<div class="zl-bootstrap" />'));

			// set the trigger button after the input
			$this.dialogTrigger = $('<a title="'+$this.options.title+'" class="btn btn-mini zlux-btn-edit" href="#"><i class="icon-edit"></i></a>')
			.insertAfter(input)

			// button events
			.on('click', function(){
				
				if ($this.zluxdialog.inited) {
					// toggle the dialog
					$this.zluxdialog.toggle();
				} else {
					// init the dialog
					$this.zluxdialog.init();
				}

				// avoid default
				return false;
			})


			// set the dialog options
			$this.zluxdialog = new $.zluxDialog({
				title: $this.options.title,
				width: $this.options.full_mode ? '75%' : 300,
				dialogClass: 'zl-bootstrap zlux-filesmanager ' + ($this.options.full_mode ? 'zlux-dialog-full' : 'zlux-dialog-mini'),
				position: ($this.options.full_mode == false ? {
					of: $this.dialogTrigger,
					my: 'left top',
					at: 'right bottom'
				} : null)
			});

			// on dialog load
			$this.zluxdialog.bind("InitComplete", function(dialog) {
				$this.eventDialogLoaded();
			});


			// on manager init
			$this.bind("InitComplete", function(manager) {

				// init dialog scrollbar
				$this.zluxdialog.scrollbar('refresh');

				// get subtoolbar
				var subtoolbar = $('.zlux-dialog-subtoolbar-filter', $this.zluxdialog.toolbar.wrapper);

				// move the search field to the toolbar
				$('.dataTables_filter', $this.oTable.fnSettings().nTableWrapper).appendTo(subtoolbar);

				// show the content
				$this.zluxdialog.initContent();
			});
		},
		eventDialogLoaded: function() {
			var $this = this;

			// init filesmanager
			$this.filesmanager = $('<div class="zlux-filesmanager" />').appendTo($this.zluxdialog.content);
			$this.initDataTable($this.filesmanager);

			// set global close event
			$('html').on('mousedown', function(event) {
				// close if target is not the trigger or the dialog it self
				$this.zluxdialog.dialog('isOpen') && !$this.dialogTrigger.is(event.target) && !$this.dialogTrigger.find(event.target).length && !$this.zluxdialog.widget.find(event.target).length && $this.zluxdialog.dialog('close')
			});

			// init toolbar
			$this.zluxdialog.setMainToolbar(
				[{
					title : "Apply Filters",
					icon : "filter",
					click : function(tool){
						// toggle the subtoolbar visibility
						$('.zlux-dialog-subtoolbar-filter', $this.zluxdialog.toolbar.wrapper).slideToggle('fast');

						tool.toggleClass('zlux-ui-tool-enabled');
					}
				},{
					title : "New folder",
					icon : "folder-close",
					subicon : "plus-sign",
					click : function(){
						console.log(1);
						// $.ajax({
						// 	"url": oDTSettings.oInit.sAjaxUrl+"&task=newFolder&path=images",
						// 	"dataType": "json",
						// 	"cache": false,
						// 	"success": function (json) {
								
						// 	}
						// })
					}
				},
				{
					title : "Upload files to current folder",
					icon : "cloud-upload",
					click : function(){
						// show the associated toolbar
						$this.zluxdialog.showToolbar(2);

						// disable dialog scroll
						$this.zluxdialog.scrollbar('hide');

						$('.zlux-filesmanager', $this.zluxdialog.content).fadeOut('400', function(){

							// init ZLUX Upload
							$this.zluxupload.inited || $this.zluxupload.init();
							
							// show the upload view
							$('.zlux-upload', $this.zluxdialog.content).fadeIn('400');
						})
					}
				},
				{
					title : "Refresh",
					icon : "refresh",
					click : function(){
						// reload the table data
						$this.reload();
					}
				}]
			);

			// init subtoolbar
			$this.zluxdialog.newSubToolbar('filter');

			// set upload engine
			$this.zluxupload = new $.zluxUpload({
				url: $this.options.ajax_url+'&task=upload',
				path: 'images',
				wrapper: $this.zluxdialog.content,
				signature: $this.options.signature,
				policy: $this.options.policy
			});

			// when queue files changes
			$this.zluxupload.bind('QueueChanged', function(up){

				// refresh scroll
				$this.zluxdialog.scrollbar('refresh');

				// enable/disable upload button
				if ($this.zluxupload.getQueuedFiles().length) {
					$this.zluxdialog.toolbarBtnState(2, 'upload', 'enabled');
				} else {
					$this.zluxdialog.toolbarBtnState(2, 'upload', 'disabled');
				}
			});

			// when uploader ready
			$this.zluxupload.bind('Init', function(){

				// toogle the buttons on upload events
				$this.zluxupload.uploader.bind('BeforeUpload', function(up){
					$this.zluxdialog.toolbarBtnState(2, 'ban-circle', 'enabled');
					$this.zluxdialog.toolbarBtnState(2, 'upload', 'disabled');
					$this.zluxdialog.toolbarBtnState(2, 'plus-sign', 'disabled');
				})
				$this.zluxupload.uploader.bind('UploadComplete', function(up){
					$this.zluxdialog.toolbarBtnState(2, 'ban-circle', 'enabled');
					$this.zluxdialog.toolbarBtnState(2, 'plus-sign', 'enabled');
					$this.zluxdialog.toolbarBtnState(2, 'ban-circle', 'disabled');
				})
			})

			// set Upload toolbar
			$this.zluxdialog.newToolbar(
				[{
					title : "Add new files to upload",
					icon : "plus-sign",
					click : function(){
						// find the upload browse input and trigger it
						$('.zlux-upload-browse', $this.upload).siblings('.moxie-shim').children('input').trigger('click');
					}
				},
				{
					title : "Start uploading",
					icon : "upload disabled",
					click : function(){
						$this.zluxupload.uploader.start();
						return false;
					}
				},
				{
					title : "Cancel current upload",
					icon : "ban-circle disabled",
					click : function(){
						// cancel current queue upload
						$this.zluxupload.uploader.stop();

						// disable the btn
						$this.zluxdialog.toolbarBtnState(2, 'ban-circle', 'disabled');
						$this.zluxdialog.toolbarBtnState(2, 'upload', 'enabled');
						$this.zluxdialog.toolbarBtnState(2, 'plus-sign', 'enabled');
					}
				}],
				2,
				// back to main function
				function(){
					$('.zlux-upload', $this.zluxdialog.content).fadeOut('400', function(){

						// empty possible upload queue
						$this.zluxupload.emptyQueue();

						// show the filesmanager view
						$('.zlux-filesmanager', $this.zluxdialog.content).fadeIn('400');

						// refresh dialog scroll
						$this.zluxdialog.scrollbar('refresh');
					})
				}
			)
		}
	});
	// Don't touch
	$.fn[Plugin.prototype.name] = function() {
		var args   = arguments;
		var method = args[0] ? args[0] : null;
		return this.each(function() {
			var element = $(this);
			if (Plugin.prototype[method] && element.data(Plugin.prototype.name) && method != 'initialize') {
				element.data(Plugin.prototype.name)[method].apply(element.data(Plugin.prototype.name), Array.prototype.slice.call(args, 1));
			} else if (!method || $.isPlainObject(method)) {
				var plugin = new Plugin();
				if (Plugin.prototype['initialize']) {
					plugin.initialize.apply(plugin, $.merge([element], args));
				}
				element.data(Plugin.prototype.name, plugin);
			} else {
				$.error('Method ' +  method + ' does not exist on jQuery.' + Plugin.name);
			}
		});
	};
})(jQuery);


/* ===================================================
 * ZLUX Upload v0.2
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function(options){
		this.options = $.extend({}, this.options, options);
		this.events = {};
	};
	Plugin.prototype = $.extend(Plugin.prototype, $.zluxMain.prototype, {
		name: 'zluxUpload',
		options: {
			url: null,
			extensions: null,
			path: null,
			fileMode: 'files',
			max_file_size: '1024kb',
			wrapper: null
		},
		init: function() {
			var $this = this;

			// append the upload to the wrapper
			$this.upload = $('<div class="zlux-upload" />').attr('data-zlux-upload-status', '').appendTo($this.options.wrapper)

			// start hiden
			.hide();

			// set the dropzone
			$this.dropzone = $('<div class="zlux-upload-dropzone" />').appendTo($this.upload).append(
				$('<span class="zlux-upload-dropzone-msg">Drop files here<br />or ' +
					'<a class="zlux-upload-browse" href="javascript:;">browse & choose</a> them' +
				'</span>')
			)

			// init DnD events
			$this.initDnDevents();

			// bind DnD events
			$this.bind("WindowDragHoverStart", function(up, target) {
				// set draghover attr
				$this.dropzone.attr('data-zlux-draghover', true);
			});

			$this.bind("WindowDragHoverEnd", function(up, target) {
				// set draghover attr
				$this.dropzone.attr('data-zlux-draghover', false);
			})

			// set the file list
			$this.filelist = $('<ul class="unstyled zlux-upload-filelist" />').appendTo($this.upload)

				// set file details sliding
				.on('click', '.zlux-upload-file-btn-details', function(){
					var icon = $(this),
						file = icon.closest('.zlux-upload-file'),
						details = icon.siblings('.zlux-upload-file-details');

					if (file.hasClass('zlux-ui-open')) {
						icon.addClass('icon-plus-sign').removeClass('icon-minus-sign');
						details.slideUp('fast', function(){
							file.removeClass('zlux-ui-open');
							// update dialog scrollbar
							// $this.zluxSelector.scrollbar('refresh');
						});

					} else {
						file.addClass('zlux-ui-open');
						icon.removeClass('icon-plus-sign').addClass('icon-minus-sign');
						details.slideDown('fast', function(){
							// $this.zluxSelector.scrollbar('refresh');
						});
					}
				})

				// remove file from files function
				.on('click', '.zlux-upload-file-btn-remove', function(){
					var $file = $(this).closest('.zlux-upload-file'),
						file = $this.uploader.getFile($file[0].id);

					// proceede only if file is not being uploaded currently
					if (file.zlux_status != plupload.STARTED && file.status != plupload.UPLOADING) {
						$file.fadeOut(function(){
							$(this).remove();
							$this.uploader.removeFile(file);
						});
					}
				})

			// listen to File Errors event
			$this.bind("FileError", function(up, file, message) {
				var $file = $('#' + file.id, $this.filelist);
				
				// set status
				$file.attr('data-zlux-upload-status', 'error');

				// set message
				$('.zlux-upload-file-details-error', $file).html(message);

				// change icon
				$('.zlux-upload-file-btn-details', $file).removeClass('icon-minus-sign').addClass('icon-warning-sign');
			})

			// init plupload
			$this.initPlupload();

			// set init state
			$this.inited = true;
		},
		/*
		 * Init the Plupload plugin
		 */
		initPlupload: function() {
			var $this = this;

			// init the Plupload uploader
			$this.uploader = new plupload.Uploader({
				runtimes: 'html5',
				browse_button: $('.zlux-upload-browse', $this.upload)[0],
				drop_element: $this.dropzone[0], 
				max_file_size: '1mb',
				// url: $this.options.url,
				url: 'http://milcom.testing.s3.amazonaws.com/',

				// S3
				multipart: true,
				multipart_params: {
					'key': '${filename}', // use filename as a key
					'Filename': '${filename}', // adding this to keep consistency across the runtimes
					'acl': 'public-read',
					'Content-Type': 'image/jpeg',
					'success_action_status': '201',
					'AWSAccessKeyId' : 'AKIAJBGAQYDO6Z76KIGQ',		
					'policy': $this.options.policy,
					'signature': $this.options.signature
				},
				file_data_name: 'file', // optional, but better be specified directly

				// Flash settings
				flash_swf_url : $this.JRoot + 'media/zoo/applications/docs/elements/contentarea/assets/plupload/Moxie.swf',

				// Post init events, bound after the internal events
				init : {
					FilesAdded: function(up, files) {
						$this.eventFilesAdded(files);
					},
					FileUploaded: function(up, file, info) {
						$this.eventFileUploaded(file, info);
					},
					UploadProgress: function(up, file) {
						$this.eventUploadProgress(file);
					},
					BeforeUpload: function(up, file) {
						$this.eventBeforeUpload(file);
					},
					QueueChanged: function(up) {
						$this.eventQueueChanged();
					},
					StateChanged: function() {
						$this.eventStateChanged();
					},
					Error: function(up, err) {
						$this.eventError(err);
					}
				}
			});

			// trigger the init event
			$this.uploader.bind('Init', function(){
				$this.trigger('Init');
			})

			// init the uploader
			$this.uploader.init();
		},
		/*
		 * Translates the specified string.
		 */
		_: function(str) {
			return plupload.translate(str) || str;
		},
		/*
		 * Fires when a error occurs.
		 */
		eventError: function(err) {
			var $this = this,
				file = err.file,
				message,
				details;

			// file related errors
			if (file) {
				message = '<strong>' + err.message + '</strong>';
				details = err.details;
				
				if (details) {
					message += " <br /><i>" + err.details + "</i>";
				} else {
					
					switch (err.code) {
						case plupload.FILE_EXTENSION_ERROR:
							details = $this._("File: %s").replace('%s', file.name);
							break;
						
						case plupload.FILE_SIZE_ERROR:
							details = $this.sprintf($this._("File: %s, size: %d, max file size: %d"), file.name, file.size, plupload.parseSize($this.options.max_file_size));
							break;

						case plupload.FILE_DUPLICATE_ERROR:
							details = $this._("%s already present in the queue.").replace(/%s/, file.name);
							break;
							
						case plupload.FILE_COUNT_ERROR:
							details = $this._("Upload element accepts only %d file(s) at a time. Extra files were stripped.").replace('%d', $this.options.max_file_count);
							break;
						
						case plupload.IMAGE_FORMAT_ERROR :
							details = $this._("Image format either wrong or not supported.");
							break;
						
						case plupload.IMAGE_MEMORY_ERROR :
							details = $this._("Runtime ran out of available memory.");
							break;
													
						case plupload.HTTP_ERROR:
							details = $this._("Upload URL might be wrong or doesn't exist.");
							break;
					}
					message += " <br /><i>" + details + "</i>";
				}
				
				// trigger file error event
				$this.trigger('FileError', file, message);
			}

			// console.log(message);
		},
		/*
		 * Fires when the overall state is being changed for the upload queue.
		 */
		eventStateChanged: function() {
			var $this = this;

			// update the zlux upload status
			if ($this.uploader.state === plupload.UPLOADING) {
				$this.upload.attr('data-zlux-upload-status', 'uploading');
			}

			if ($this.uploader.state === plupload.STOPPED) {
				$this.upload.attr('data-zlux-upload-status', 'stopped');

				// reset the progress bar
				$('.zlux-upload-toolbar-progress .bar', $this.upload).width('0%');

				// refresh the file list
				$this._updateFilelist();
			}
		},
		/*
		 * Fires when just before a file is uploaded.
		 */
		eventBeforeUpload: function(file) {
			var $this = this,
				$file = $('#' + file.id, $this.filelist);
			
			// set the upload path
			// $this.uploader.settings.url = $this.uploader.settings.url+'&path='+$this.options.path;

			// set progress to 0
			$('.zlux-upload-file-progress', $file).html('0%');

			// set the started status
			file.zlux_status = 2;

			// update status
			$this._handleFileStatus(file);
		},
		/*
		 * Fires while a file is being uploaded.
		 */
		eventUploadProgress: function(file) {
			var $this = this,

			// avoid the NaN value
			percentage = isNaN(file.percent) ? 0 : file.percent;

			// upload the progress info
			$('#' + file.id + ' .zlux-upload-file-progress', $this.filelist).html(percentage + '%');

			// update status
			$this._handleFileStatus(file);
			// $this._updateTotalProgress();
		},
		/*
		 * Fires when a file is successfully uploaded.
		 */
		eventFileUploaded: function(file, info) {
			var $this = this,
				response = $.parseJSON(info.response);

			// upload the name
			$('#' + file.id + ' .zlux-upload-file-name', $this.filelist).html(response.name);

			// upload the progress
			$('#' + file.id + ' .zlux-upload-file-progress', $this.filelist).html('100%').fadeOut();

			// change the buttons/icons
			$('#' + file.id + ' .zlux-upload-file-btn-remove').removeClass('icon-remove').addClass('icon-ok');

			// update file status
			$this._handleFileStatus(file);
		},
		/*
		 * Fires while when the user selects files to upload.
		 */
		eventFilesAdded: function(files) {
			var $this = this;
			
			// add the file preview
			$.each(files, function(index, file) {
				$this._renderFilePreview(file);
			})
		},
		/*
		 * Fires when the file queue is changed.
		 */
		eventQueueChanged: function() {
			var $this = this;

			// refresh the filelist
			$this._updateFilelist();
		},
		/*
		 * Get files yet to be uploaded
		 */
		getQueuedFiles: function() {
			var $this = this,
				files = [];

			// add the file preview
			$.each($this.uploader.files, function(index, file) {
				file.status != plupload.DONE && files.push(file);
			})

			return files;
		},
		/*
		 * Empty the file queue and dom
		 */
		emptyQueue: function() {
			var $this = this;

			// removes all file froms queue and dom
			$this.uploader.splice();
			$this.filelist.empty();
		},
		_updateFilelist: function() {
			var $this = this,
				queued = false;

			// foreach file
			$.each($this.uploader.files, function(index, file) {
				
				// check if there are files left to upload
				if (file.status != plupload.DONE) {
					queued = true;
				}

				// check for stopped files
				if (file.status == plupload.STOPPED) {
					// refresh file statut
					$this._handleFileStatus(file);
				}
			})

			// if files left
			if ($this.uploader.files.length) {
				// hide the dropzone msg
				$('.zlux-upload-dropzone-msg', $this.upload).hide();
			}

			// if no files left
			if (!$this.uploader.files.length) {

				// update the upload status
				$this.upload.attr('data-zlux-upload-status', '');

				// disable the upload btn
				$('.zlux-upload-toolbar-start', $this.upload).addClass('disabled');

				// show the dropzone message
				$('.zlux-upload-dropzone-msg', $this.upload).fadeIn();

			// if queued files left
			} else if (queued) {

				// update the upload status
				$this.upload.attr('data-zlux-upload-status', 'queued');

				// enable the upload btn
				$('.zlux-upload-toolbar-start', $this.upload).removeClass('disabled');

			// if uploaded files left
			} else {

				// update the upload status
				$this.upload.attr('data-zlux-upload-status', 'stopped');

				// disable the upload btn
				$('.zlux-upload-toolbar-start', $this.upload).addClass('disabled');
			}

			// fire queue event
			$this.trigger('QueueChanged');
		},
		_renderFilePreview: function(file) {
			var $this = this;
			
			$('<li id="' + file.id + '" class="zlux-upload-file" />').append(

				// buttons / icons
				$('<i class="zlux-upload-file-btn-remove icon-remove" />'),
				$('<i class="zlux-upload-file-btn-details icon-plus-sign" />'),

				// upload progress
				$('<span class="zlux-upload-file-progress"/>'),

				// name
				$('<div class="zlux-upload-file-name">' + file.name + '</div>'),

				// details
				$('<div class="zlux-upload-file-details" />').append(
					'<div class="zlux-upload-file-details-error" />' +
					'<ul class="unstyled zlux-upload-file-details-list">' +
						'<li><strong>Name</strong>: ' + file.name.replace(/(\.[a-z0-9]+)$/, '') + '</li>' +
						'<li><strong>Type</strong>: ' + file.type + '</li>' +
						'<li><strong>Size</strong>: ' + plupload.formatSize(file.size) + '</li>' +
					'</ul>'
				)
				.hide()
			)

			// append to the file list
			.appendTo($this.filelist);
		},
		_handleFileStatus: function(file) {
			var $this = this,
				$file = $('#' + file.id, $this.filelist),
				status = '';

			// check custom status
			if (file.zlux_status == plupload.STARTED) {
				status = 'started';

				// unset the status to avoid further conflicts
				file.zlux_status = '';

			// else check default status
			} else {

				if (file.status == plupload.DONE) {
					status = 'done';
				}

				if (file.status == plupload.FAILED) {
					status = 'failed';
				}

				if (file.status == plupload.QUEUED) {
					status = 'queued';
				}

				if (file.status == plupload.UPLOADING) {
					status = 'uploading';
				}

				if (file.status == plupload.STOPPED) {
					// reset the file upload progress
					$('.zlux-upload-file-progress', $file).html('');
				}
			}

			// set the file status
			$file.attr('data-zlux-upload-status', status);
		},
		_updateTotalProgress: function() {
			var $this = this;

			// upload the bar percentage
			$('.progress .bar', $this.toolbar).css('width', $this.uploader.total.percent + '%');
		},
		/**
			Init the Drag and Drop events

			In order to normalize the window in/out for File dragging a jQuery collection $() is used to keep track of what events were fired on what elements. The event.target is added the collection whenever dragenter was fired and removed whenever dragleave happened. The idea is if the collection is empty it means we have actually left the original element because if we were entering a child element at least one element (the child) would still be in the jQuery collection. This workaround doesn't work with Plupload DnD declared element, additional events must be used instead.

			Original idea - http://stackoverflow.com/a/10310815/698289
		*/
		initDnDevents: function(element) {
			var $this = this,
			collection = $(),
			dz_collection = $(),
			inWindow = false;
			inDZ = false;

			// Make sure if we drop something on the page we don't navigate away
     		$(window).on("drop", function(e) {
				e.preventDefault();
				return false;
			})

     		// when enter the window draging a file, fire the event
			.on('dragenter', function(e) {

				if (collection.size() === 0) {
					$this.trigger('WindowDragHoverStart');
				
					// update zones
					inWindow = true;
					inDZ = false;
				}

				collection = collection.add(e.target);
			})

			// when leave the window or drop a file on it, fire the event
			.on('dragleave drop', function(e) {

				// timeout is needed because Firefox 3.6 fires the dragleave event on
				// the previous element before firing dragenter on the next one
				setTimeout(function() {
					var isChild = false;

					// FF workaround, in order to avoid permission errors dragging outside the body, use the try-catch
					// to check if the relatedTarget is a child of the body
					try {
						isChild = $('body').find(e.relatedTarget).length ? true : isChild;
					}
					catch(err){} // do nothing

					// remove target from collection
					collection = collection.not(e.target);

					
					if (collection.size() === 0 && !isChild) {
						inWindow = false;
					}
				}, 1);

				// check a while later if both zones are left
				setTimeout(function() {
					if(!inWindow && !inDZ){
						$this.trigger('WindowDragHoverEnd');
						dz_collection = $();
						collection = $();
					} 
				}, 2);
			});


			// because of plupload events on the dropzone, it's considered like new window, so must be checked separatly
			$this.dropzone.on('dragenter', function(e) {

				if (dz_collection.size() === 0) {
					$this.trigger('WindowDragHoverStart');
					
					// update zones
					inWindow = false;
					inDZ = true;
				}

				dz_collection = dz_collection.add(e.target);
			});

			$this.dropzone.on('dragleave drop', function(e) {

				setTimeout(function() {
					var isChild = false;

					// FF workaround, in order to avoid permission errors dragging outside the body, use the try-catch
					// to check if the relatedTarget is a child of the body
					try {
						isChild = $('body').find(e.relatedTarget).length ? true : isChild;
					}
					catch(err){} // do nothing

					// remove target from collection
					dz_collection = dz_collection.not(e.target);

					// this event could be prevented, once each time
					if (dz_collection.size() === 0 && !isChild) {
						inDZ = false;
					}

				}, 1);

				// check a while later if both zones are left
				setTimeout(function() {
					if(!inWindow && !inDZ){
						$this.trigger('WindowDragHoverEnd');
						dz_collection = $();
						collection = $();
					}
				}, 2);
			});
		}
	});
	// save the plugin for global use
	$.zluxUpload = Plugin;
})(jQuery);


/* ===================================================
 * ZLUX Files Preview v0.1
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function(){};
	Plugin.prototype = $.extend(Plugin.prototype, {
		name: 'zluxFilesPreview',
		options: {
		},
		initialize: function(target, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;
		}
	});
	// Don't touch
	$.fn[Plugin.prototype.name] = function() {
		if (this.data(Plugin.prototype.name)) return; // standart check to avoid duplicate inits
		var args   = arguments;
		var method = args[0] ? args[0] : null;
		return this.each(function() {
			var element = $(this);
			if (Plugin.prototype[method] && element.data(Plugin.prototype.name) && method != 'initialize') {
				element.data(Plugin.prototype.name)[method].apply(element.data(Plugin.prototype.name), Array.prototype.slice.call(args, 1));
			} else if (!method || $.isPlainObject(method)) {
				var plugin = new Plugin();
				if (Plugin.prototype['initialize']) {
					plugin.initialize.apply(plugin, $.merge([element], args));
				}
				element.data(Plugin.prototype.name, plugin);
			} else {
				$.error('Method ' +  method + ' does not exist on jQuery.' + Plugin.name);
			}
		});
	};
})(jQuery);