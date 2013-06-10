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
			"apps": '', // comma separated values
			"types": '', // comma separated values
			"categories": '', // comma separated values
			"tags": '' // comma separated values
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
				"iDisplayLength": 20,
				"sAjaxSource": $this.AjaxUrl + '&task=ItemsManager',
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
						"sTitle": "Name", "mData": "_itemname", "sClass":"column-name",
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

					// add class for CSS perfomance
					$('label', input_filter).addClass('zlux-x-label');

					// set search events
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
					var $object = $(nRow).addClass('zlux-object');

					//('<i class="zlux-object-toggle icon-check-empty" /><i class="zlux-object-details-btn icon-plus-sign" />')

					// prepare the details
					var aDetails = [];
					$.each(aData.details, function(name, val){
						aDetails.push({'name': name, 'value': val});
					})

					// reset and append the object data
					$('.column-name', $object).html('').removeClass('zlux-ui-open').append(

						// render the object content
						$this.renderObjectDOM('<a href="#">' + aData._itemname + '</a>', aDetails)
					);

					// the remove feature is not ready yet, must be hiden
					$('.zlux-x-remove', $object).remove();
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
			.on('click', '.zlux-object-name a, .zlux-object-toggle', function(e){
				var row = $(this).closest('tr');

				if (row.attr('data-zlux-object-status') != 'true') {
					row.attr('data-zlux-object-status', 'true');

					// change the toggle icon
					$('.zlux-object-toggle', row).removeClass('icon-check-empty').addClass('icon-check');
					
					// trigger event
					$this.trigger("ItemSelected", row.data('id'), row);

				// perfome deselection only if toggle button clicked
				} else if ($('.zlux-object-toggle').is(e.target)){

					// set unselect state
					row.removeAttr('data-zlux-object-status');

					// change the toggle icon
					$('.zlux-object-toggle', row).removeClass('icon-check').addClass('icon-check-empty');
					
					// trigger event
					$this.trigger("ItemUnselected", row.data('id'), row);
				}
				
				return false;
			})

			// init item details features
			.on('click', '.zlux-object-details-btn', function(){
				var icon = $(this),
					TD = icon.closest('td'),
					details = icon.siblings('.zlux-object-details');

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
	$.fn[Plugin.prototype.name] = Plugin;
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
			$this.dialogTrigger = $('<a title="'+$this.options.title+'" class="btn btn-mini zlux-btn-edit" href="#"><i class="icon-edit"></i></a>')
			.insertAfter(input)

			// button events
			.on('click', function(){
				
				// toggle the dialog
				$this.zluxdialog.toggle();

				// avoid default
				return false;
			})


			// set the dialog options
			$this.zluxdialog = new $.fn.zluxDialog({
				title: $this.options.title,
				width: $this.options.full_mode ? '75%' : 300,
				dialogClass: 'zl-bootstrap zlux-itemsmanager ' + ($this.options.full_mode ? 'zlux-dialog-full' : 'zlux-dialog-mini'),
				position: ($this.options.full_mode == false ? {
					of: $this.dialogTrigger,
					my: 'left top',
					at: 'right bottom'
				} : null)
			})

			.done(function(){
				$this.eventDialogLoaded();
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
			$this.zluxdialog.newSubToolbar('filter', 'main');
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