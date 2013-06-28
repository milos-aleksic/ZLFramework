/* ===================================================
 * ZLUX Items Manager
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
	Plugin.prototype = $.extend(Plugin.prototype, $.fn.zluxManager.prototype, {
		name: 'zluxItemsManager',
		options: {
			apps: '', // comma separated values
			types: '', // comma separated values
			categories: '', // comma separated values
			tags: '' // comma separated values
		},
		initialize: function(target, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;

			// set the filter param
			$this.filter = {};

			// run the initial check
			$this.initCheck();

			// save target
			$this.target = target;

			// init filesmanager
			$this.filesmanager = $('<div class="zl-bootstrap zlux-itemsmanager" />').appendTo(target);
			$this.initDataTable($this.filesmanager);
		},
		/**
		 * Performs initial tasks
		 */
		initCheck: function() {
			var $this = this;

			// set ID
			$.fn.zluxItemsManager.iNextUnique++;
			$this.ID = $.fn.zluxItemsManager.iNextUnique;
		},
		initDataTable: function(wrapper) {
			var $this = this;

			// set table
			$('<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" />')
			.appendTo(wrapper);

			// init dataTable
			$this.oTable = $('table', wrapper).dataTable({
				"sDom": "F<'row-fluid'<'span12't>><'row-fluid'<'span12'p>><'row-fluid zlux-x-info'<'span12'i>>",
				"bServerSide": true,
				"iDisplayLength": 20,
				"sAjaxSource": $this.AjaxURL() + '&task=ItemsManager',
				"fnServerParams": function (aoData) {
					// determine what filter values to use
					var apps = $this.filter.apps ? $this.filter.apps : $this.options.apps,
						types = $this.filter.types ? $this.filter.types : $this.options.types,
						cats = $this.filter.cats ? $this.filter.cats : $this.options.categories;
						tags = $this.filter.tags ? $this.filter.tags : $this.options.tags;

					// push the values
					aoData.push({ "name": "apps", "value": $this.options.apps }); // allways send the original app selection
					aoData.push({ "name": "types", "value": $this.options.types }); // allways send the original type selection
					aoData.push({ "name": "categories", "value": $this.options.categories }); // allways send the original cat selection
					aoData.push({ "name": "tags", "value": $this.options.tags }); // allways send the original tag selection
					aoData.push({ "name": "filter_apps", "value": apps });
					aoData.push({ "name": "filter_types", "value": types });
					aoData.push({ "name": "filter_cats", "value": cats });
					aoData.push({ "name": "filter_tags", "value": tags });
				},
				"oLanguage": {
					"sEmptyTable": $this._('IM_NO_ITEMS_FOUND'),
					"sInfoEmpty": "",
					"sInfo": $this._('IM_PAGINATION_INFO')
				},
				"aoColumns":
				[
					{
						"sTitle": $this._('NAME'), "mData": "_itemname", "sClass":"column-name",
						"mRender": function ( data, type, full ) {
							return type == 'display' ? '' : data;
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
					{
						"sTitle": "ID", "mData": "id", "bSearchable": false, "bSortable": false
					},
					{
						"sTitle": "", "mData": "type", "bSortable": false, "bSearchable": false, "sWidth": "14px", "sClass": "column-icon",
						"mRender": function ( data, type, full ) {
							if (type == 'display') {
								return '<i class="icon-file-alt"></i>';
							} else {
								return data;
							}
						}
					}
				],
				"aoColumnDefs": [
					{ "bVisible": false, "aTargets": [ 1, 2, 3, 4, 5 ] }
				],
				"fnInitComplete": function(oSettings, data) {
					var input_filter = $('.zlux-x-filter-input_wrapper', wrapper)
					
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

					// set search events
					$('input', input_filter).on('keyup', function(){
						if ($(this).val() == '') {
							$('.zlux-ui-dropdown-unselect', input_filter).hide();
						} else {
							$('.zlux-ui-dropdown-unselect', input_filter).show();
						}
					})

					// fix the header column order
					$('thead tr th:last', $this.oTable).prependTo($('thead tr', $this.oTable));

					// trigger table init event
					$this.trigger("InitComplete");
				},
				"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
					var $object = aData;
					
					// save object dom
					$object.dom = $(nRow);

					// set object dom properties
					$object.dom.addClass('zlux-object');

					// fix the column order
					$('td:last', $object.dom).prependTo($object.dom);

					// reset and append the object data
					$('.column-name', $object.dom).html('').removeClass('zlux-ui-open').append(

						// render the object content
						$this.renderObjectDOM($object)
					)
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
					$this.trigger("TableDrawCallback", oSettings);
				}
			})

			// Trigger Object Selected event
			.on('click', '.zlux-object .zlux-x-name a', function(){
				var object_dom = $(this).closest('tr.zlux-object'),
					$object = $this.oTable.fnGetData( object_dom[0] );

				// set the zlux object
				$object.dom = object_dom;

				// trigger event
				$this.trigger("ObjectSelected", $object);
				
				// prevent default
				return false;
			})
		},
		/**
		 * Render the Object content
		 */
		renderObjectDOM: function($object) {
			var $this = this,
				sName,
				aDetails;
			
			// prepare the details
			aDetails = [
				{name: $this._('ROUTE'), value: $object.application.name + ' / ' + $object.type.name + ' / ID ' + $object.id},
				{name: $this._('ACCESS'), value: $object.access},
				{name: $this._('CREATED'), value: $object.created}
			]

			// add Author if known
			$object.author.name && aDetails.push({name: $this._('AUTHOR'), value: $object.author.name});
			
			var sDetails = '';
			$.each(aDetails, function(i, detail){
				sDetails += '<li><strong>' + detail.name + '</strong>: <span>' + detail.value + '</span></li>';
			})

			// set object dom
			var content = $(
				// btns
				'<div class="zlux-x-tools">' +
					'<i class="zlux-x-details-btn icon-angle-down" />' +
					// '<i class="zlux-x-remove icon-minus-sign" />' + // remove feature - TODO
				'</div>' +

				// name
				'<div class="zlux-x-name"><a href="#" class="zlux-x-name-link">' + $object.name + '</a></div>' +

				// details
				'<div class="zlux-x-details">' +
					'<div class="zlux-x-messages" />' +
					'<div class="zlux-x-details-content">' +
						'<ul class="unstyled">' + sDetails + '</ul>' +
					'</div>' +
				'</div>'
			)

			return content;
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
	$.fn[Plugin.prototype.name] = Plugin;
	$.fn[Plugin.prototype.name].iNextUnique = 0;
})(jQuery);


/* ===================================================
 * ZLUX Dialog Items Manager
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function(options){
		this.options = $.extend({}, $.fn.zluxItemsManager.prototype.options, this.options, options);
		this.events = {};
	};
	Plugin.prototype = $.extend(Plugin.prototype, $.fn.zluxItemsManager.prototype, {
		name: 'zluxDialogItemsManager',
		options: {
			title: 'Items Manager',
			position: {}, // override the Dialog position
			full_mode: 0
		},
		events: {},
		initialize: function(dialogTrigger, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;

			// run initial check
			$this.initCheck();

			// set the filter param
			$this.filter = {};

			// dialogTrigger example, it should be set by the caller script
			// $('<a title="' + $this.options.title + '" class="btn btn-mini zlux-btn-edit" href="#"><i class="icon-edit"></i></a>')

			// set the trigger button event
			$this.dialogTrigger = dialogTrigger.on('click', function(){
				
				// toggle the dialog
				$this.zluxdialog.toggle();

				// avoid default
				return false;
			})

			$this.initDialog();
			$this.initMainEvents();
		},
		/**
		 * Init the Dialog
		 */
		initDialog: function() {
			var $this = this;

			// set the dialog options
			$this.zluxdialog = new $.fn.zluxDialog({
				title: $this.options.title,
				width: $this.options.full_mode ? '75%' : 300,
				dialogClass: 'zl-bootstrap zlux-itemsmanager ' + ($this.options.full_mode ? 'zlux-dialog-full' : ''),
				position: $.extend({
					of: $this.dialogTrigger,
					my: 'left top',
					at: 'right bottom'
				}, $this.options.position)
			})

			.done(function(){
				// set the dialog unique ID
				$this.zluxdialog.widget.attr('id', 'zluxItemsManager_' + $this.ID);

				// init dialog related functions
				$this.eventDialogLoaded();
			});
		},
		eventDialogLoaded: function() {
			var $this = this;

			// init itemsmanager
			$this.itemsmanager = $('<div class="zlux-itemsmanager" />').appendTo($this.zluxdialog.content);
			$this.initDataTable($this.itemsmanager);


			// set Object details Open event
			$this.zluxdialog.main.on('click', '.zlux-x-details-btn', function(){
				var toggle = $(this),
					$object = toggle.closest('tr.zlux-object'),
					TD = $('td.column-name', $object),
					details = $('.zlux-x-details', $object);

				// open the details
				if (!TD.hasClass('zlux-ui-open')) {
					TD.addClass('zlux-ui-open');
					toggle.removeClass('icon-angle-down').addClass('icon-angle-up');

					// scroll to the Object with animation
					$this.zluxdialog.content.stop().animate({
						'scrollTop': $object.get(0).offsetTop
					}, 900, 'swing')

					// open, when done...
					details.slideDown('fast', function(){
						$this.zluxdialog.scrollbar('refresh');
					});

				// close them
				} else {
					toggle.addClass('icon-angle-down').removeClass('icon-angle-up');
					TD.removeClass('zlux-ui-open');
					details.slideUp('fast', function(){
						$this.zluxdialog.scrollbar('refresh');
					});
				}
			})

			// set global close event
			$('html').on('mousedown', function(event) {
				// close if target is not the trigger or the dialog it self
				$this.zluxdialog.dialog('isOpen') && !$this.dialogTrigger.is(event.target) && !$this.dialogTrigger.find(event.target).length && !$this.zluxdialog.widget.find(event.target).length && !$this.zluxdialog.widget.is(event.target) && $this.zluxdialog.dialog('close')
			});

			// init toolbar
			$this.zluxdialog.setMainToolbar(
				[{
					title: $this._('APPLY_FILTERS'),
					icon: "filter",
					click: function(tool){
						// toggle the subtoolbar visibility
						$('.zlux-dialog-subtoolbar-filter', $this.zluxdialog.toolbar.wrapper).slideToggle('fast');

						tool.toggleClass('zlux-ui-tool-enabled');
					}
				},{
					title:  $this._('REFRESH'),
					icon: "refresh",
					click: function(){
						// reload the table data
						$this.reload();
					}
				}]
			);

			// init subtoolbar
			$this.zluxdialog.newSubToolbar('filter', 'main');
		},
		/**
		 * Init the Main Events
		 */
		initMainEvents: function() {
			var $this = this;

			// on manager init
			$this.bind("InitComplete", function(manager) {

				// init dialog scrollbar
				$this.zluxdialog.scrollbar('refresh');

				// get subtoolbar
				var subtoolbar = $('.zlux-dialog-subtoolbar-filter', $this.zluxdialog.toolbar.wrapper);

				// set filter selects
				subtoolbar.append(
					$this.getSelect('aaApps', $this._('IM_FILTER_BY_APP'), function(value){
						// apply the filter
						$this.filter.apps = value;

						// restart type filter
						$this.filter.types = '';
						$('.btn-group[data-id=2]', subtoolbar).attr('data-update', true);
					}, function(){
						// reset the filter
						$this.filter.apps = '';
					}),

					$this.getSelect('aaTypes', $this._('IM_FILTER_BY_TYPE'), function(value){
						// apply the filter
						$this.filter.types = value;
						$('.btn-group[data-id=2]', subtoolbar).attr('data-update', false);
					}, function(){
						// reset the filter
						$this.filter.types = '';
					}),

					$this.getSelect('aaCategories', $this._('IM_FILTER_BY_CATEGORY'), function(value){
						// apply the filter
						$this.filter.cats = value;
					}, function(){
						// reset the filter
						$this.filter.cats = '';
					}),

					$this.getSelect('aaTags', $this._('IM_FILTER_BY_TAG'), function(value){
						// apply the filter
						$this.filter.tags = value;
					}, function(){
						// reset the filter
						$this.filter.tags = '';
					})
				);

				// move the search field to the toolbar
				$('.zlux-x-filter-input_wrapper', $this.oTable.fnSettings().nTableWrapper).appendTo(subtoolbar);

				// show the content
				$this.zluxdialog.initContent();
			});

			// on item select
			$this.bind("ItemSelected", function(manager, id, row){
				// if multiple selection allowed - TODO

				// else unset siblings check
				row.siblings('[data-checked]').removeAttr('data-checked')

				// set item value
				input.val(id).trigger('change');
			});

			// on item unselect
			$this.bind("ItemUnselected", function(manager, id, row){
				// unset item value
				input.val('').trigger('change');
			});
		},
		getSelect: function(dataName, text, onChangeCallback, onUnselectCallback) {
			var $this = this,

			// get initial options
			options = $.parseJSON($this.oTable.fnSettings().jqXHR.responseText)[ dataName ];

			// if 0 or 1 option, abort rendering
			if (options.length <= 1) return;

			// set the toggle btn
			var toggle = $('<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">' + text + '<span class="caret" /></a>'),

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
			$this.bind("TableDrawCallback", function(manager, oSettings) {
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