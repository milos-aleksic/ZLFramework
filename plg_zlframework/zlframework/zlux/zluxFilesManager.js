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
				source = $this.options.ajax_url + '&task=getFilesManagerData';

			// set table
			$('<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" />')
			.appendTo(wrapper);

			// init dataTable
			$this.oTable = $('table', wrapper).dataTable({
				"sDom": "f<'row-fluid'<'span12'B>><'row-fluid'<'span12't>>",
				"oLanguage": {
					"sSearch": "_INPUT_",
					"sEmptyTable": "No Files found",
					"sInfoEmpty": "",
					"sInfo": "Showing _END_ of _TOTAL_ Files"
				},
				"sAjaxUrl": $this.options.ajax_url,
				"sAjaxSource": source,
				"sServerMethod": "POST",
				"sStartRoot": $this.cleanPath($this.options.root),
				// "bServerSide": true,
				"bPaginate": false,
				"aoColumns": [
					{ 
						"sTitle": "", "mData": "type", "bSearchable": false, "sWidth": "14px", "sClass": "column-icon",
						"mRender": function ( data, type, full ) {
							if (type == 'display') {
								return '<i class="icon-' + (data == 'folder' ? 'folder-close' : 'file-alt') + '"></i>';
							} else {
								return data;
							}
						}
					},
					{ 
						"sTitle": "Name", "mData": "name", "sClass": "column-name zlux-object",
						"mRender": function ( data, type, full ) {
							return type == 'display' ? '<span class="zlux-object-name"><a href="#">' + data + '</a></span>' : data;
						},
						"fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
							// store path in data
							$(nTd).parent('tr').attr('data-path', $this.cleanPath( oData.path ))
						}
					}
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

					// if S3 storage
					if($this.options.storage == 's3') {
						aoData.push({ "name": "storage", "value": "s3" });
						aoData.push({ "name": "accesskey", "value": $this.options.storage_params.accesskey });
						aoData.push({ "name": "key", "value": $this.options.storage_params.secretkey });
						aoData.push({ "name": "bucket", "value": $this.options.storage_params.bucket });
					}
				},
				"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
					// set the entry type, folder or file
					$(nRow).attr('data-type', aData.type)
					// set the status
					.data('zlux-status', 'ready');

					// prepare the details
					var details = '';
					$.each(aData.details, function(name, val){
						details += '<li><strong>' + name + '</strong>: ' + val + '</li>';
					})

					// set entry details
					$('.zlux-object', $(nRow))
					.prepend('<i class="zlux-object-details-btn icon-angle-down" /><i class="zlux-object-remove icon-minus-sign" />')

					.append(
						'<div class="zlux-object-details">' +
							'<div class="zlux-object-messages" />' +
							'<div class="zlux-object-details-content">' +
								'<ul class="unstyled">' + details + '</ul>' +
							'</div>' +
						'</div>'
					);

					// $('.zlux-object-remove', $(nRow)).popover({
					// 	'html':'some',
					// 	'content':'some nig content that should be visible even',
					// 	'placement':'top'
					// });
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

					// hide spinner
					$this.zluxdialog.spinner('hide');
				}
			})

			// when folder clicked
			.on('click', 'tbody [data-type=folder] a', function(e) {
				var row = $(this).closest('tr'),
					oSettings = $this.oTable.fnSettings();

				// update paths
				oSettings.sGoToPath = row.data('path');

				$this.oTable.fnReloadAjax(oSettings.sAjaxSource);
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
				// 	var value = oSettings.sCurrentPath+'/'+row.data('path')
				// 	$this.target.val(value).trigger('change');
				// } else {}
				
				return false;
			})

			// remove file event
			.on('click', '.zlux-object-remove', function(){
				var row = $(this).closest('tr'),
					entry = $(this).closest('td.zlux-object');

				// if open, the remove action will delete the file, with confirmation
				if (entry.hasClass('zlux-ui-open')) {
					$this.trigger('BeforeDeleteFile', row);
				}

				// if closed, will remove the file from selection
				else {
					row.removeAttr('data-checked');
				}
					
				// if input
				// if ($this.target[0].tagName == 'INPUT'){
				// 	$this.target.val('').trigger('change');
				// } else {
				// 	// $this._addReleatedItem($this.oTable, wrapper.children('div'), col);
				// }
				
				return false;
			})

			
		},
		_fnServerData: function( sUrl, aoData, fnCallback, oSettings ) {
			var $this = this,
				root;

			// create cache object
			oSettings.aAjaxDataCache = oSettings.aAjaxDataCache ? oSettings.aAjaxDataCache : [];

			// implelment deferred cache system
			// if ( !$this.cachedScriptPromises[ path ] ) {
			// 	$this.cachedScriptPromises[ path ] = $.Deferred(function( defer ) {
			// 		$.getScript( path ).then( defer.resolve, defer.reject );
			// 	}).promise();
			// }
			// return $this.cachedScriptPromises[ path ].done( callback );

			// if first time, set start root as current path
			if (!oSettings.aAjaxDataCache.length) oSettings.sCurrentPath = oSettings.oInit.sStartRoot;

			// set the root
			root = $this.cleanPath(oSettings.sCurrentPath + '/' + oSettings.sGoToPath);

			// reset vars
			oSettings.sGoToPath = '';

			// send root with post data
			aoData.push({ "name": "root", "value": root });

			// ajax
			oSettings.jqXHR = $.ajax({
				"url": sUrl,
				"data": aoData,
				"beforeSend": function(jqXHR, settings){
					// check if the data is cached
					var cached = false;

					if (!oSettings.bReloading){
						$.each(oSettings.aAjaxDataCache, function(i, v){
							if (v.root == root){
								var json = v.data;

								// save root
								oSettings.sCurrentPath = v.root;

								// emulate the xhr events
								$(oSettings.oInstance).trigger('xhr', [oSettings, json]);
								fnCallback( json );

								// avoid ajax call
								cached = true;
							}
						})
					}

					// if cached abort ajax
					if (cached) return false;

					// else, the ajax proceeds, show the spinner
					$this.zluxdialog.spinner('show');
				},
				"success": function (json) {
					if ( json.sError ) {
						oSettings.oApi._fnLog( oSettings, 0, json.sError );
					}

					// if first time, save real root path, as it can be changed for security reasons by the server
					if (!oSettings.aAjaxDataCache.length) oSettings.oInit.sStartRoot = json.root;

					// save new path
					oSettings.sCurrentPath = json.root;

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
		},
		/**
		 * Returns the full path to the asset
		 */
		_getFullPath: function(path) {
			var sCurrentPath = this.oTable.fnSettings().sCurrentPath;
			return sCurrentPath ? sCurrentPath + '/' + path : path;
		},
		/**
		 * Clean a path from double / and others
		 *
		 * @method cleanPath
		 * @param {String} path The path to be cleaned
		 */
		cleanPath : function(path) {
			// return path and
			return path

			// remove undefined
			.replace(/undefined/g, '')

			// remove double /
			.replace(/\/\//g, '/')

			// remove / from start and begining
			.replace(/(^\/|\/$)/g, '');
		},
		/**
		 * Returns the oTable row related to the provided path
		 */
		_getRowFromPath: function(path) {
			var $this = this;
			return $('tr[data-path="' + path + '"]', $this.oTable);
		},
		/**
		 * Reloads the Table content
		 */
		reload: function() {
			var $this = this,
				oSettings = $this.oTable.fnSettings();

			// set vars
			oSettings.bReloading = true;
			// reload
			$this.oTable.fnReloadAjax(oSettings.sAjaxSource);
		},
		/**
		 * Push a Message specific to the object and manage old ones
		 */
		pushObjectMessage: function(object, message) {
			var $this = this;

			// wrap if message is plain text
			if (typeof(text) == 'string') {
				message = $('<div>' + message + '</div>')
			}

			// if more than one message wrap in separate divs
			if (message.length > 1) {
				$.each(message, function(i, v){
					message[i] = $('<div>' + v + '</div>');
				})
			}

			// get current siblings
			var siblings = $('.zlux-object-msg', object),

			// prepare message wrapper
			msg = $('<div class="zlux-object-details-content zlux-object-msg" />').hide()

			.append(
				// append message content
				message,

				// append remove feature
				$('<i class="zlux-object-msg-remove icon-remove" />').on('click', function(){
					msg.fadeOut();
				})
			)

			// add it to DOM
			.prependTo($('.zlux-object-details', object))

			// show it with effect
			.slideDown('fast', function(){

				// remove any msg sibling
				siblings.fadeOut('slow', function(){
					$(this).remove();
				});
			})
		},
		/**
		 * Delete the file from the server
		 */
		deleteFile: function(path) {
			var $this = this,
				aoData = [];

			// set path
			aoData.push({ "name": "path", "value": $this._getFullPath(path) });

			// if S3 storage
			if($this.options.storage == 's3') {
				aoData.push({ "name": "storage", "value": "s3" });
				aoData.push({ "name": "accesskey", "value": $this.options.storage_params.accesskey });
				aoData.push({ "name": "key", "value": $this.options.storage_params.secretkey });
				aoData.push({ "name": "bucket", "value": $this.options.storage_params.bucket });
			}

			// make the request and return a promise
			return $.Deferred(function( defer )
			{
				$.ajax({
					"url": $this.options.ajax_url + "&task=deletePath",
					"data": aoData,
					"dataType": "json",
					"type": "post"
				})
				
				.done(function(json) {
					if (json.result) {

						defer.resolve();

						// trigger event
						$this.trigger("FileDeleted", path);

					} else {
						// failed with reported error
						defer.reject(json.errors);
					}
				})

				.fail(function(){
					// some unreported error
					defer.reject('Something went wrong, the file was not deleted.');
				})

			}).promise();
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

				// init item details features
				$this.oTable.on('click', '.zlux-object-details-btn', function(){
					var icon = $(this),
						row = icon.closest('tr'),
						TD = icon.closest('td'),
						details = icon.siblings('.zlux-object-details');

					// if row is busy, abort
					if (row.data('zlux-status') == 'busy') return;

					// open/close the details
					if (!TD.hasClass('zlux-ui-open')) {
						TD.addClass('zlux-ui-open');
						icon.removeClass('icon-angle-down').addClass('icon-angle-up');
						details.slideDown('fast', function(){
							// $this.zluxdialog.main.scrollTop(60); // todo
							$this.zluxdialog.scrollbar('refresh');
						});
					} else {
						icon.addClass('icon-angle-down').removeClass('icon-angle-up');
						TD.removeClass('zlux-ui-open');
						details.slideUp('fast', function(){
							$this.zluxdialog.scrollbar('refresh');
						});
					}
				})
			})


			// before Deleting file
			.bind("BeforeDeleteFile", function(manager, object){
				// if allready message displayed, abort
				if ($('.zlux-object-details-message-actions')[0]) return;

				// prepare and display the confirm message
				var msg = $('<div>You are about to delete this file, please <span class="label label-warning label-link">confirm</span></div>')

				// confirm action
				.on('click', '.label-link', function(){

					// only allowed to be submited once
					if ($(this).data('submited')) return; $(this).data('submited', true);

					// set spinner
					$('.column-icon i', object).addClass('icon-spinner icon-spin');

					// start the process							
					$this.deleteFile(object.data('path'))
					
					// if succesfull
					.done(function(){
						object.fadeOut('slow');
					})

					// if fails
					.fail(function(message) {
						$this.pushObjectMessage(object, message);
					})

					// on result
					.always(function(json) {
						// remove spinner
						$('.column-icon i', object).removeClass('icon-spinner icon-spin');
					})
				});

				$this.pushObjectMessage(object, msg);
			})

			// File deleted
			// .bind("FileDeleted", function(manager, path){

			// })
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

							// update upload path
							$this.zluxupload.options.path = $this.oTable.fnSettings().sCurrentPath;

							// show the upload view
							$('.zlux-upload', $this.zluxdialog.content).fadeIn('400');
						})
					}
				},
				{
					title : "Refresh",
					icon : "refresh",
					click : function(){
						$this.reload();
					}
				}]
			);

			// init subtoolbar
			$this.zluxdialog.newSubToolbar('filter');

			// set upload engine
			$this.zluxupload = new $.zluxUpload({
				url: $this.options.ajax_url,
				path: 'images',
				wrapper: $this.zluxdialog.content,
				storage: $this.options.storage,
				storage_params: $this.options.storage_params
			});

			// when queue files changes
			$this.zluxupload
			.bind('QueueChanged', function(up){
				// refresh scroll
				$this.zluxdialog.scrollbar('refresh');
			})

			// toogle the buttons
			.bind('FilesAdded', function(up, file){
				$this.zluxdialog.toolbarBtnState(2, 'upload', 'enabled');
			})

			// toogle the buttons on upload events
			.bind('BeforeUpload', function(up){
				$this.zluxdialog.toolbarBtnState(2, 'ban-circle', 'enabled');
				$this.zluxdialog.toolbarBtnState(2, 'upload', 'disabled');
				$this.zluxdialog.toolbarBtnState(2, 'plus-sign', 'disabled');
			})

			// when file is uploaded
			.bind('FileUploaded', function(up){
				$this.reload();
			})

			// toogle the buttons on upload events
			.bind('UploadComplete', function(up){
				$this.zluxdialog.toolbarBtnState(2, 'ban-circle', 'disabled');
				$this.zluxdialog.toolbarBtnState(2, 'upload', 'disabled');
				$this.zluxdialog.toolbarBtnState(2, 'plus-sign', 'enabled');
			})

			// toogle the buttons on file error event
			.bind('FileError', function(up){
				$this.zluxdialog.toolbarBtnState(2, 'ban-circle', 'disabled');
				$this.zluxdialog.toolbarBtnState(2, 'upload', 'disabled');
				$this.zluxdialog.toolbarBtnState(2, 'plus-sign', 'enabled');
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
			wrapper: null,
			storage: 'local', // local, s3, dropbox
			storage_params: {}
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

					// abort if it's being uploaded
					if ($(this).closest('li.zlux-upload-file').data('zlux-upload-status') == 'uploding') return;

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

				// change icons
				$('.zlux-upload-file-btn-details', $file).removeClass('icon-minus-sign').addClass('icon-warning-sign');
				$('.zlux-upload-file-btn-remove', $file).removeClass('icon-spinner icon-spin').addClass('icon-remove');
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
			var $this = this,
				params;

			// set basics params
			params = {
				runtimes: 'html5',
				browse_button: $('.zlux-upload-browse', $this.upload)[0],
				drop_element: $this.dropzone[0], 
				max_file_size: '1mb',
				url: $this.options.url + '&task=upload',

				// flash runtime settings
				flash_swf_url : $this.JRoot + 'media/zoo/applications/docs/elements/contentarea/assets/plupload/Moxie.swf'
			};


			// if S3 storage
			if($this.options.storage == 's3') {
				params = $.extend(params, {
					url: 'http://' + $this.options.storage_params.bucket + '.s3.amazonaws.com',
					multipart: true,
					multipart_params: {
						'key': '${filename}', // use filename as a key
						'Filename': '${filename}', // adding this to keep consistency across the runtimes
						'acl': 'public-read',
						'Content-Type': 'image/jpeg',
						'success_action_status': '201',
						'AWSAccessKeyId': $this.options.storage_params.accesskey,
						'policy': $this.options.storage_params.policy,
						'signature': $this.options.storage_params.signature
					},
					file_data_name: 'file', // optional, but better be specified directly
				});
			}


			// Post init events, bound after the internal events
			params = $.extend(params, {
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
					UploadComplete: function(up, files) {
						$this.eventUploadComplete(files);
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


			// set the Plupload uploader
			$this.uploader = new plupload.Uploader(params);

			// workaround to trigger the Init event
			// perhaps Plupload bug but it's not working as the others
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

							// if S3 storage
							if($this.options.storage == 's3') {
								
								if ($this.options.storage_params.bucket.match(/\./g)) {
									// When using SLL the bucket names can't have dots
									details = $this._("The bucket name can't contain periods (.).");
								} else {
									details = $this._("There is some missconfiguration with the Bucket. Checkout the CORS permissions. If the bucket is recently created 24h must pass because of Amazon redirections.");
								}
							// if local storage
							} else {
								details = $this._("Upload URL might be wrong or doesn't exist.");
							}
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
			
			// if local storage
			if($this.options.storage == 'local') {
				// update the upload path
				$this.uploader.settings.url = $this.uploader.settings.url + '&path=' + $this.options.path;
			}

			// if S3 storage
			if($this.options.storage == 's3') {
				// update the upload path and file name
				var folder = $this.options.path ? $this.options.path + '/' : '';
				$this.uploader.settings.multipart_params.key = folder + file.name;
				// update the content type
				$this.uploader.settings.multipart_params['Content-Type'] = file.type;
			}

			// set progress to 0
			$('.zlux-upload-file-progress', $file).html('0%');

			// set the started status
			file.zlux_status = 2;

			// update status
			$this._handleFileStatus(file);

			// change the buttons/icons
			$('.zlux-upload-file-btn-remove', $file).removeClass('icon-remove').addClass('icon-spinner icon-spin');

			// trigger event
			$this.trigger('BeforeUpload', file);
		},
		/*
		 * Fires when all files in a queue are uploaded.
		 */
		eventUploadComplete: function(file) {
			var $this = this;

			// trigger event
			$this.trigger('UploadComplete', file);
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
		},
		/*
		 * Fires when a file is successfully uploaded.
		 */
		eventFileUploaded: function(file, info) {
			var $this = this,
				$file = $('#' + file.id, $this.filelist);

			// if local storage
			if($this.options.storage == 'local') {
				var response = $.parseJSON(info.response);

				// update the name
				$('.zlux-upload-file-name', $file).html(response.result);
			}

			// if s3 storage
			else if($this.options.storage == 's3') {
				var response = $(info.response);

				// update the name
				$('.zlux-upload-file-name', $file).html(response.find('Key').html());
			}

			// update progress
			$('.zlux-upload-file-progress', $file).html('100%').fadeOut();

			// change the buttons/icons
			$('.zlux-upload-file-btn-remove', $file).removeClass('icon-remove icon-spinner icon-spin').addClass('icon-ok');

			// update file status
			$this._handleFileStatus(file);

			// trigger event
			$this.trigger('FileUploaded', file);
		},
		/*
		 * Fires while when the user selects files to upload.
		 */
		eventFilesAdded: function(files) {
			var $this = this;

			// add the file preview
			$.each(files, function(index, file) {

				// set initial status
				file.status = 'validating';

				// add file to dom
				var $file = $this._renderFilePreview(file);

				// validate file name
				$.ajax({
					"url": $this.options.url + '&task=validateFileName',
					"type": 'post',
					"data":{
						name: file.name
					},
					"dataType": "json",
					"cache": false,
					"beforeSend": function(jqXHR, settings){
						// add name spinner
						$('.zlux-upload-file-name', $file).html('<i class="icon-spinner icon-spin" />');
					},
					"success": function (json) {
						// update name
						$('.zlux-upload-file-name', $file).html(json.result);

						// update file name
						file.name = json.result;

						// ready to upload, set status
						file.status = 1; 

						// update file status
						$this._handleFileStatus(file);

						// refresh the filelist
						$this._updateFilelist();

						// trigger event
						$this.trigger('FilesAdded', file);
					}
				})
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
				if (file.status != plupload.DONE && file.status != 'validating') files.push(file);
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
				if (file.status != plupload.DONE && file.status != 'validating') {
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
			var $this = this,
			
			$file = $('<li id="' + file.id + '" class="zlux-upload-file" data-zlux-upload-status="validating" />').append(

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
						'<li><strong>File</strong>: ' + file.name.replace(/(\.[a-z0-9]+)$/, '') + '</li>' +
						'<li><strong>Type</strong>: ' + file.type + '</li>' +
						'<li><strong>Size</strong>: ' + plupload.formatSize(file.size) + '</li>' +
					'</ul>'
				)
				.hide()
			)

			// append to the file list
			.appendTo($this.filelist);

			return $file;
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