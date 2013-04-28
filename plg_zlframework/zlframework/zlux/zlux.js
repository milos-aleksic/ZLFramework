/* ===================================================
 * ZLUX Main Plugin v0.1
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
	Plugin.prototype = $.extend(Plugin.prototype, {
		name: 'zluxMain',
		options: {},
		// var for internal events, must be reseted when expanding
		events: {},
		// save the Joomla Root url
		JRoot: location.href.match(/^(.+)administrator\/index\.php.*/i)[1],
		initialize: function(target, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;
		},
		/**
		 * Dispatches the specified event name and it's arguments to all listeners.
		 *
		 * @method trigger
		 * @param {String} name Event name to fire.
		 * @param {Object..} Multiple arguments to pass along to the listener functions.
		 */
		trigger : function(name) {
			var list = this.events[name.toLowerCase()], i, args;

			// console.log(name, arguments);

			if (list) {
				// Replace name with sender in args
				args = Array.prototype.slice.call(arguments);
				args[0] = this;

				// Dispatch event to all listeners
				for (i = 0; i < list.length; i++) {
					// Fire event, break chain if false is returned
					if (list[i].func.apply(list[i].scope, args) === false) {
						return false;
					}
				}
			}

			return true;
		},
		/**
		 * Adds an event listener by name.
		 *
		 * @method bind
		 * @param {String} name Event name to listen for.
		 * @param {function} func Function to call ones the event gets fired.
		 * @param {Object} scope Optional scope to execute the specified function in.
		 */
		bind: function(name, func, scope) {
			var list;

			name = name.toLowerCase();
			list = this.events[name] || [];
			list.push({func : func, scope : scope || this});
			this.events[name] = list;
		},
		/**
		 * Removes the specified event listener.
		 *
		 * @method unbind
		 * @param {String} name Name of event to remove.
		 * @param {function} func Function to remove from listener.
		 */
		unbind : function(name) {
			name = name.toLowerCase();

			var list = this.events[name], i, func = arguments[1];

			if (list) {
				if (func !== undef) {
					for (i = list.length - 1; i >= 0; i--) {
						if (list[i].func === func) {
							list.splice(i, 1);
								break;
						}
					}
				} else {
					list = [];
				}

				// delete event list if it has become empty
				if (!list.length) {
					delete this.events[name];
				}
			}
		},
		/**
		 * Removes all event listeners.
		 *
		 * @method unbindAll
		 */
		unbindAll : function() {
			var $this = this;
			
			plupload.each(events, function(list, name) {
				$this.unbind(name);
			});
		},
		/**
		 * Check whether uploader has any listeners to the specified event.
		 *
		 * @method hasEventListener
		 * @param {String} name Event name to check for.
		 */
		hasEventListener : function(name) {
			return !!this.events[name.toLowerCase()];
		},
		/**
		 * Pseudo sprintf implementation - simple way to replace tokens with specified values.
		 *
		 * @param {String} str String with tokens
		 * @return {String} String with replaced tokens
		 */
		sprintf: function(str) {
			var args = [].slice.call(arguments, 1), reStr = '';

			str.split(/%[sdf]/).forEach(function(part) {
				reStr += part;
				if (args.length) {
					 reStr += args.shift();
				}
			});
			return reStr;
		}
	});
	// save the plugin for global use
	$.zluxMain = Plugin;
})(jQuery);


/* ===================================================
 * ZLUX Dialog v0.1
 * https://zoolanders.com/extensions/zl-framework
 * Eg: new $.zluxDialog();
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
		name: 'zluxDialog',
		options: {
			width: '300',
			height: '150',
			title: 'Dialog'
		},
		events: {},
		init: function() {
			var $this = this;

			if ($this.inited) return;

			// set dialog class
			$this.options.dialogClass = 'zlux-dialog ' + $this.options.dialogClass;

			// init dialog
			$this.main = $('<div />').appendTo($('body'))
			.dialog($.extend({
				autoOpen: 1, // auto open on first init
				resizable: !1,
				height: 'auto'
			}, $this.options));

			// set the dialog content wrapper
			$this.content = $('<div class="zlux-dialog-content" />').appendTo($this.main);

			// save a reference to the widget, the wrapper of the entire dialog
			$this.widget = $this.main.dialog('widget');

			// on dialog open
			$this.main.on("dialogopen", function(event, ui){
				$this.widget.data('dialog-dragged') || // once dragged keep the position
				$this.widget.position($.extend({
					my: 'center',
					at: 'center',
					of: window
				}, $this.options.position)).data('dialog-dragged', 1);
			});
			
			// set the spinner
			$this._spinner = $('<span class="zlux-loader-circle-big" />').appendTo($this.widget);
			$this._spinner_area = $this.main;

			// init scrollbar
			$this.main.perfectScrollbar();

			// set toolbar
			$this.toolbar = {};
			$this.toolbar.wrapper = $('<div class="zlux-dialog-toolbar" />').hide().insertAfter($this.widget.children()[0]);

			// trigget init event
			$this.trigger("InitComplete");

			// set init state
			$this.inited = true;
		},
		toggle: function() {
			this.dialog(this.dialog("isOpen") ? "close" : "open");
		},
		dialog: function(method) {
			return this.main.dialog(method);
		},
		scrollbar: function(action) {
			var $this = this;

			if (action == 'refresh') {
				$('.ps-scrollbar-y', $this.main).css('top', 0);
				$('.ps-scrollbar-x', $this.main).css('left', 0);
				$this.scrollbar('show'); // let's be sure is visible
				$this.scrollbar('update');

				// if no scroll necesary, hide it
				var margin = $this.content.width() - $('.ps-scrollbar-x', $this.main).width();
				if (margin >= 0 && margin <= 5) {
					$this.scrollbar('hide');
				}

			} else if (action == 'hide') {
				$this.widget.addClass('noscroll');
			} else if (action == 'show') {
				$this.widget.removeClass('noscroll');
			} else {
				$this.main.perfectScrollbar(action);
			}
		},
		/**
		 * The processing display behaviour
		 */
		spinner: function(action) {
			var $this = this;

			if (action == 'show') {
				$this.inited && $this.main.add().fadeTo(0, 0.5);
				$this._spinner.show();
			} else {
				$this._spinner.hide();
				$this.main.add($this.toolbar.wrapper).fadeTo('slow', 1);

				// move the spinner once when first time hidden
				if (!$this.spinner_inited) {
					$this._spinner.appendTo($this.content);
					$this.spinner_inited = true;
				}
			}
		},
		/**
		 * Set a Dialog Toolbar
		 *
		 * @param tools Array An array of tool objects
		 * eg: [{title : "New folder", icon : "folder-close", subicon : "plus-sign", click : function(){}}]
		 */
		newToolbar: function(tools, alias, backtomain) {
			var $this = this;

			// set the wrapper
			var toolbar = $('<ul class="zlux-dialog-toolbar-' + alias + ' inline" />').appendTo($this.toolbar.wrapper);

			// append the tools
			$.each(tools, function(i, tool) {
				toolbar.append(
					$('<li />').append(
						$('<i class="icon-' + tool.icon + ' icon-2x icon-border" title="' + tool.title + '">' +
								(tool.subicon ? '<i class="icon-' + tool.subicon + '"></i>' : '') +
						'</i>')
						.on('click', function(){
							// don't proceede if tool is disabled
							if ($(this).hasClass('disabled')) return false;

							// execute custom functions
							tool.click($(this));
						})
					)
				);
			})

			// set the action when back to main button is clicked
			$('.zlux-dialog-toolbar-backtomain', $this.toolbar.wrapper).on('click', function(){
				backtomain();
			})
		},
		/**
		 * Set a Dialog Sub Toolbar
		 */
		newSubToolbar: function(alias) {
			var $this = this;

			// set the wrapper
			var subtoolbar = $('<div class="zlux-dialog-subtoolbar-' + alias + '" />').hide().appendTo($this.toolbar.wrapper);
		},
		setMainToolbar: function(tools) {
			var $this = this;
			
			// set toolbar
			$this.newToolbar(tools, 'main');

			// set the back to main button
			$this.toolbar.wrapper.prepend(
				$('<i class="zlux-dialog-toolbar-backtomain icon-reply icon-border" title="Back to Main view" />').on('click', function(){
					
					$('[class^="zlux-dialog-toolbar-"]', $this.toolbar.wrapper).hide()
					$('.zlux-dialog-toolbar-main', $this.toolbar.wrapper).show();
					
				})
			);
		},
		/*
		 * Shows the indicated toolbar hiding the others
		 */
		showToolbar: function(alias) {
			var $this = this;

			// show the desired
			$('.zlux-dialog-toolbar-' + alias, $this.toolbar.wrapper).show();

			// hide the main
			$('.zlux-dialog-toolbar-main', $this.toolbar.wrapper).hide();

			// show the back button
			$('.zlux-dialog-toolbar-backtomain', $this.toolbar.wrapper).show();
		},
		/*
		 * Toggle Toolbar button
		 */
		toolbarBtnState: function(toolbar_alias, btn, state) {
			var $this = this,
				tool = $('.zlux-dialog-toolbar-' + toolbar_alias + ' i.icon-' + btn, $this.toolbar.wrapper);

			if (state == 'disabled') {
				tool.animate({
					color: '#C0C0C0'
					}, 200, function() {
					tool.addClass('disabled')
				});
			} else {
				tool.animate({
					color: '#444'
					}, 200, function() {
					tool.removeClass('disabled')
				});
			}
		}
	});
	// save the plugin for global use
	$.zluxDialog = Plugin;
})(jQuery);


/* ===================================================
 * ZLUX Items Manager v0.2
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
		name: 'zluxItemsManager',
		options: {
			"apps": '', // comma separated values
			"types": '', // comma separated values
			"categories": '', // comma separated values
			"tags": '', // comma separated values
			"ajax_url": ''
		},
		initialize: function(target, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;

			// set the filter param
			$this.filter = {};

			// save targer
			$this.target = target;

			// set items manager
			$this.itemsmanager = $('<div class="zl-bootstrap zlux-itemsmanager" />'),
			button = null;

			// if input, create dialog and return Item ID
			if (target[0].tagName == 'INPUT')
			{
				// wrapp the input
				target.wrap($this.itemsmanager);

				// set the trigger button
				button = $('<a title="'+$this.options.title+'" class="btn btn-mini zlux-btn-edit" href="#"><i class="icon-edit"></i></a>')
				.insertAfter(target);
			} 
			else // if placeholder, create dialog and return Item Rendered DOM
			{
				// append the Items Rendering wrapper
				$('<div />').appendTo($this.itemsmanager);
				
				// set the trigger button
				button = $('<a class="btn btn-mini" href="#"><i class="icon-plus-sign"></i>Add Item</a>')
				.appendTo($this.itemsmanager);

				// append wrapper
				$this.itemsmanager.appendTo(target);
			}

			// init Item Selector
			button.zluxSelector({
				title: $this.options.title,
				width: $this.options.full_mode ? '75%' : 300,
				height: 'auto',
				dialogClass: 'zl-bootstrap zlux-items-manager-dialog '+($this.options.full_mode ? 'zlux-dialog-full' : 'zlux-dialog-mini'),
				position: ($this.options.full_mode ? {
					my: 'center',
					at: 'center',
					of: window
				} : null),
				button: button
			}, function(zluxSelector) {

				// save zluxSelector
				$this.zluxSelector = zluxSelector;

				// when dialog ready init table
				$this._dataTable(zluxSelector.content, function(){

					// init dialog scrollbar
					$this.zluxSelector.scrollbar();
				});
			});

			// bind event
			$this.bind("ItemSelected", function(manager, id){
				$this._addReleatedItem(wrapper.children('div'), col);
			});
		},
		_addReleatedItem: function(row) {
			var $this = this,
				Data = $this.oTable.fnGetData(row[0]),

			// set row content
			row = $('<div class="row-fluid" />').append(
				$('<span class="span12"><h4>'+Data._itemname+'</h4><div>'+Data.details+'</div></span>')
			);

			$this.wrapper.append(row);
		},
		initDataTable: function(wrapper) {
			var $this = this;

			// set table
			$('<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" />')
			.appendTo(wrapper);

			// init dataTable
			$this.oTable = $('table', wrapper).dataTable({
				"sDom": "f<'row-fluid'<'span12't>><'row-fluid'<'span12'p>><'row-fluid'<'span12'i>>",
				"bServerSide": true,
				"iDisplayLength": 5,
				"sAjaxSource": $this.options.ajax_url+'&task=ItemsManager',
				"fnServerParams": function (aoData) {
					// determine what filter values to use
					var apps = $this.filter.apps ? $this.filter.apps : $this.options.apps,
						types = $this.filter.types ? $this.filter.types : $this.options.types,
						cats = $this.filter.cats ? $this.filter.cats : $this.options.categories;
						tags = $this.filter.tags ? $this.filter.tags : $this.options.tags;

					// push the values
					aoData.push({ "name": "apps", "value": $this.options.apps }); // allways send the original app selection
					aoData.push({ "name": "types", "value": $this.options.types }); // allways send the original type selection
					aoData.push({ "name": "filter_apps", "value": apps });
					aoData.push({ "name": "filter_types", "value": types });
					aoData.push({ "name": "filter_cats", "value": cats });
					aoData.push({ "name": "filter_tags", "value": tags });
				},
				"oLanguage": {
					"sSearch": "_INPUT_",
					"sEmptyTable": "No Items found",
					"sInfoEmpty": "",
					"sInfo": "_END_ of _TOTAL_"
				},
				"aoColumns":
				[
					{
						"sTitle": "Name", "mData": "_itemname", "sWidth": "38%", "sClass":"zlux-global-entry",
						"mRender": function ( data, type, full ) {
							return type == 'display' ? '<span class="zlux-global-entry-name"><a href="#">' + data + '</a></span>' : data;
						},
						"fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
							$(nTd).parent('tr').attr('data-id', oData.id)
						}
					},
					{ 
						"sTitle": "App", "mData": "application", "bSortable": false,
						"mRender": function ( data, type, full ) {
							return type == 'display' ? data.name : data.id;
						}
					},
					{ 
						"sTitle": "Type", "mData": "type", "bSortable": false,
						"mRender": function ( data, type, full ) {
							return type == 'display' ? data.name : data.id;
						}
					},
					{ "sTitle": "Access", "mData": "access", "bSearchable": false, "bSortable": false },
					{ "sTitle": "Author", "mData": "author", "bSearchable": false, "bSortable": false,
						"mRender": function ( data, type, full ) {
							return type == 'display' ? data.name : data.id;
						}
					},
					{ "sTitle": "ID", "mData": "id", "bSearchable": false, "bSortable": false }
				],
				"aoColumnDefs": [
					{ "bVisible": false, "aTargets": [ 1, 2, 3, 4, 5 ] }
				],
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
				"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
					// prepare the details
					var details = '';
					$.each(aData.details, function(name, val){
						details += '<li><strong>' + name + '</strong>: ' + val + '</li>';
					})

					// set item details
					$('.zlux-global-entry', $(nRow))
					.prepend('<i class="zlux-global-entry-toggle icon-check-empty" /><i class="zlux-global-entry-details-btn icon-plus-sign" />')

					.append(
						'<div class="zlux-global-entry-details">' +
							'<ul class="unstyled">' + details + '</ul>' +
						'</div>'
					);
				},
				"fnPreDrawCallback": function(oSettings) {
					// show processing
					$this.zluxdialog.spinner('show');

					// trigger event
					$this.trigger("DTPreDrawCallback", oSettings);
				},
				"fnDrawCallback": function(oSettings) {
					// pagination hide/show
					var oPaging = oSettings.oInstance.fnPagingInfo(),
						pagination = $('.dataTables_paginate', $(oSettings.nTableWrapper)).closest('.row-fluid');
					(oPaging.iTotalPages <= 1) && pagination.hide() || pagination.show();

					// update dialog scrollbar
					$this.zluxdialog.scrollbar('refresh');

					// hide processing
					$this.zluxdialog.spinner('hide');

					// trigger event
					$this.trigger("DTDrawCallback", oSettings);
				}
			})

			// select item event
			.on('click', '.zlux-global-entry-name a, .zlux-global-entry-toggle', function(e){
				var row = $(this).closest('tr');

				if (row.attr('data-zlux-global-entry-status') != 'true') {
					row.attr('data-zlux-global-entry-status', 'true');

					// change the toggle icon
					$('.zlux-global-entry-toggle', row).removeClass('icon-check-empty').addClass('icon-check');
					
					// trigger event
					$this.trigger("ItemSelected", row.data('id'), row);

				// perfome deselection only if toggle button clicked
				} else if ($('.zlux-global-entry-toggle').is(e.target)){

					// set unselect state
					row.removeAttr('data-zlux-global-entry-status');

					// change the toggle icon
					$('.zlux-global-entry-toggle', row).removeClass('icon-check').addClass('icon-check-empty');
					
					// trigger event
					$this.trigger("ItemUnselected", row.data('id'), row);
				}
				
				return false;
			})

			// init item details features
			.on('click', '.zlux-global-entry-details-btn', function(){
				var icon = $(this),
					TD = icon.closest('td'),
					details = icon.siblings('.zlux-global-entry-details');

				if (!TD.hasClass('zlux-ui-open')) {
					TD.addClass('zlux-ui-open');
					details.slideDown('fast', function(){
						icon.removeClass('icon-plus-sign').addClass('icon-minus-sign');
						$this.zluxdialog.scrollbar('refresh');
					});
				} else {
					details.slideUp('fast', function(){
						TD.removeClass('zlux-ui-open');
						icon.addClass('icon-plus-sign').removeClass('icon-minus-sign');
						// update dialog scrollbar
						$this.zluxdialog.scrollbar('refresh');
					});
				}
			})
		},
		/**
		 * Reloads the Table content
		 */
		reload: function() {
			this.oTable.fnReloadAjax();
		}
	});
	// Don't touch
	$.fn[Plugin.prototype.name] = function() {
		// if (this.data(Plugin.prototype.name)) return; // standart check to avoid duplicate inits
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
	$.zluxItemsManager = Plugin;
})(jQuery);


/* ===================================================
 * ZLUX Dialog Items Manager v0.1
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function(options){
		this.options = $.extend({}, $.zluxItemsManager.prototype.options, this.options, options);
		this.events = {};
	};
	Plugin.prototype = $.extend(Plugin.prototype, $.zluxItemsManager.prototype, {
		name: 'zluxDialogItemsManager',
		options: {
			"title": 'Items Manager',
			"full_mode": 0
		},
		events: {},
		initialize: function(input, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;

			// set the filter param
			$this.filter = {};

			// set main wrapper arount the input
			$this.wrapper = input.wrap($('<div class="zl-bootstrap" />'));

			// set the trigger button after the input
			var button = $('<a title="'+$this.options.title+'" class="btn btn-mini zlux-btn-edit" href="#"><i class="icon-edit"></i></a>')
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
				dialogClass: 'zl-bootstrap zlux-itemsmanager-dialog ' + ($this.options.full_mode ? 'zlux-dialog-full' : 'zlux-dialog-mini'),
				position: ($this.options.full_mode == false ? {
					of: button,
					my: 'left top',
					at: 'right bottom'
				} : null)
			});

			// on dialog load
			$this.zluxdialog.bind("InitComplete", function(dialog) {

				// init itemsmanager
				$this.itemsmanager = $('<div class="zlux-itemsmanager" />').appendTo($this.zluxdialog.content);
				$this.initDataTable($this.itemsmanager);

				// set global close event
				$('html').on('mousedown', function(event) {
					// close if target is not the trigger or the dialog it self
					$this.zluxdialog.dialog('isOpen') && !button.is(event.target) && !button.find(event.target).length && !$this.zluxdialog.widget.find(event.target).length && !$this.zluxdialog.widget.is(event.target) && $this.zluxdialog.dialog('close')
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
			});


			// on manager init
			$this.bind("InitComplete", function(manager) {

				// init dialog scrollbar
				$this.zluxdialog.scrollbar('refresh');

				// get subtoolbar
				var subtoolbar = $('.zlux-dialog-subtoolbar-filter', $this.zluxdialog.toolbar.wrapper);

				// set filter selects
				subtoolbar.append(
					$this.getSelect('aaApps', 'Filter by App', function(value){
						// apply the filter
						$this.filter.apps = value;

						// restart type filter
						$this.filter.types = '';
						$('.btn-group[data-id=2]', subtoolbar).attr('data-update', true);
					}, function(){
						// reset the filter
						$this.filter.apps = '';
					}),

					$this.getSelect('aaTypes', 'Filter by Type', function(value){
						// apply the filter
						$this.filter.types = value;
						$('.btn-group[data-id=2]', subtoolbar).attr('data-update', false);
					}, function(){
						// reset the filter
						$this.filter.types = '';
					}),

					$this.getSelect('aaCategories', 'Filter by Category', function(value){
						// apply the filter
						$this.filter.cats = value;
					}, function(){
						// reset the filter
						$this.filter.cats = '';
					}),

					$this.getSelect('aaTags', 'Filter by Tag', function(value){
						// apply the filter
						$this.filter.tags = value;
					}, function(){
						// reset the filter
						$this.filter.tags = '';
					})
				);

				// move the search field to the toolbar
				$('.dataTables_filter', $this.oTable.fnSettings().nTableWrapper).appendTo(subtoolbar);

				// hide the spinner
				$this.zluxdialog.spinner('hide');
			});

			// on item select
			$this.bind("ItemSelected", function(manager, id, row){
				// if multiple selection allowed - TODO

				// else unset siblings check
				row.siblings('[data-checked]').removeAttr('data-checked')

				// set item value
				input.val(id).trigger('change');
			});

			// on item select
			$this.bind("ItemUnselected", function(manager, id, row){
				// unset item value
				input.val('').trigger('change');
			});
		},
		getSelect: function(dataName, text, onChangeCallback, onUnselectCallback, updateCallback) {
			var $this = this,

			// get initial options
			options = $.parseJSON($this.oTable.fnSettings().jqXHR.responseText)[ dataName ],

			// set the toggle btn
			toggle = $('<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">' + text + '<span class="caret" /></a>'),

			// set the dropdown
			dropdown = $('<ul class="dropdown-menu">' + $this._getSelectOptions(options) + '</ul>'),

			// set the button group
			btnGroup = $('<div class="btn-group" data-text="' + text + '" />').append(toggle, dropdown),

			// set the unselect option
			removeBtn = $('<i class="icon-remove zlux-ui-dropdown-unselect" />').on('click', function(){
				// reset the label
				toggle.html(text + '<span class="caret"></span>');

				// hide it self
				removeBtn.hide();

				// execute on unselect callback
				$.isFunction(onUnselectCallback) && onUnselectCallback();

				// reload
				$this.reload();
			})

			// hide and attach to btn group
			.hide().prependTo(btnGroup);

			// if no options disable it
			if (!options.length) {
				toggle.addClass('disabled');
			}

			// set tooggle event
			toggle.on('click', function(e) {
				// if disabled, do nothing
				if (toggle.hasClass('disabled')) return false;
			});

			// set options event
			dropdown.on('click', 'a', function(e) {
				var value = $(this).data('value') ? $(this).data('value') : '',
					label = text;

				// set the option as label
				label = $(this).text().replace(/^[^a-zA-Z]+/g, '').replace(/[^a-zA-Z]+$/g, '');

				// show the unselect option
				removeBtn.show();

				// update the label
				toggle.html(label+'<span class="caret"></span>')

				// save the selected value
				.data('selected-value', value);

				// execute on change callback
				$.isFunction(onChangeCallback) && onChangeCallback(value);

				// reload
				$this.reload();
				e.preventDefault();
			});

			// set update event
			$this.bind("DTDrawCallback", function(manager, oSettings) {
				// update options
				var options = $.parseJSON($this.oTable.fnSettings().jqXHR.responseText)[ dataName ];
				dropdown.empty().append($this._getSelectOptions(options));

				// if no options disable it
				if (!options.length) {
					btnGroup.addClass('disabled');
				}

				// check if the new options have on it the current selected value
				var match = false;
				$.each(options, function(index, value){
					if (value.id == toggle.data('selected-value')) {
						match = true;
					}
				});

				// if they have not, the current selection must be deselected
				if (!match) {
					removeBtn.hide();
					toggle.data('selected-value', '').html(text + '<span class="caret"></span>');
				}
			});

			// return the dropdown
			return btnGroup;
		},
		_getSelectOptions: function(options) {
			var $this = this,
				options, i,
				iLen = options.length,
				asResultData = new Array();

			// set options
			for ( i=0 ; i<iLen ; i++ )
			{
				var sValue = '<li><a href="#" data-value="'+options[i].id+'">'+options[i].name+'</a></li>';
				if (jQuery.inArray(sValue, asResultData) > -1) continue;

				// else push the value onto the result data array
				else asResultData.push(sValue);
			}

			// join and return the options
			return asResultData.join('');
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
				"sDom": "<'row-fluid'<'span12'B>><'row-fluid'<'span12'f>><'row-fluid'<'span12't>><'row-fluid row-footer'<'span6'i><'span6'p>>",
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
			var button = $('<a title="'+$this.options.title+'" class="btn btn-mini zlux-btn-edit" href="#"><i class="icon-edit"></i></a>')
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
				dialogClass: 'zl-bootstrap zlux-filesmanager-dialog ' + ($this.options.full_mode ? 'zlux-dialog-full' : 'zlux-dialog-mini'),
				position: ($this.options.full_mode == false ? {
					of: button,
					my: 'left top',
					at: 'right bottom'
				} : null)
			});

			// on dialog load
			$this.zluxdialog.bind("InitComplete", function(dialog) {

				// init filesmanager
				$this.filesmanager = $('<div class="zlux-filesmanager" />').appendTo($this.zluxdialog.content);
				$this.initDataTable($this.filesmanager);

				// set global close event
				$('html').on('mousedown', function(event) {
					// close if target is not the trigger or the dialog it self
					$this.zluxdialog.dialog('isOpen') && !button.is(event.target) && !button.find(event.target).length && !$this.zluxdialog.widget.find(event.target).length && $this.zluxdialog.dialog('close')
				});

				// init toolbar
				$this.zluxdialog.setMainToolbar(
					[{
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
			});


			// on manager init
			$this.bind("InitComplete", function(manager) {

				// stop dialog processing ui
				$this.zluxdialog.spinner('hide');

				// init dialog scrollbar
				$this.zluxdialog.scrollbar('refresh');
			});
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


/* ===================================================
 * ZLUX SaveElement v0.2
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function(){};
	Plugin.prototype = $.extend(Plugin.prototype, {
		name: 'zluxSaveElement',
		options: {
			msgSaveElement: 'Save Element',
			item_id: 0,
			elm_id: '',
			ajax_url: ''
		},
		initialize: function(element, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;

			// append the button
			$('<a class="btn btn-small save" href="javascript:void(0);" />')
			.append('<i class="icon-ok-sign"></i> '+$this.options.msgSaveElement)
			.on('click', function()
			{
				var button = $(this).addClass('btn-working'),
					postData = button.closest('.element').find('input, textarea').serializeArray();

				$.post(
					$this.options.ajax_url+'&task=saveelement&item_id='+$this.options.item_id+'&elm_id='+$this.options.elm_id, 
					postData, function(data) {
					button.removeClass('btn-working');
				});
			}
			).appendTo(element.find('.btn-toolbar'));
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
 * ZLUX Items Filter v0.1
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
		name: 'zluxItemsFilter',
		options: {
			wrapper: null
		},
		init: function() {
			var $this = this;

			// append the upload to the wrapper
			$this.wrapper = $('<div class="zlux-itemsfilter" />').appendTo($this.options.wrapper)

			// start hiden
			.hide();

			// set the filter list
			$this.filterlist = $('<ul class="unstyled zlux-itemsfilter-filterlist" />').appendTo($this.wrapper);


			$this.filterlist.append('<form class="form-horizontal"><fieldset>'+
					'<div class="control-group"><label class="control-label">Multiple Checkboxes</label><div class="controls">' +
					    '<label class="checkbox"><input type="checkbox" name="checkboxes" value="Option one">' +
					      'Option one</label><label class="checkbox"><input type="checkbox" name="checkboxes" value="Option two">' +
					      'Option two</label></div></div></fieldset></form>');

			// set init state
			$this.inited = true;
		}
	});
	// save the plugin for global use
	$.zluxItemsFilter = Plugin;
})(jQuery);