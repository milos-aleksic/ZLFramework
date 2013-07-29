/* ===================================================
 * ZLUX Dates Manager
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
		name: 'zluxDatesManager',
		options: {

		},
		initialize: function(target, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;

			// run the initial check
			$this.initCheck();

			// save target
			$this.target = target;

			// set wrapper
			$this.date = $('<div class="zl-bootstrap zlux-datesmanager" />').appendTo(target);
		},
		/**
		 * Performs initial tasks
		 */
		initCheck: function() {
			var $this = this;

			// set ID
			$.fn.zluxDatesManager.iNextUnique++;
			$this.ID = $.fn.zluxDatesManager.iNextUnique;
		}
	});
	// save the plugin for global use
	$.fn[Plugin.prototype.name] = Plugin;
	$.fn[Plugin.prototype.name].iNextUnique = 0;
})(jQuery);


/* ===================================================
 * ZLUX Dialog Dates Manager
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function(options){
		this.options = $.extend({}, $.fn.zluxDatesManager.prototype.options, this.options, options);
		this.events = {};
	};
	Plugin.prototype = $.extend(Plugin.prototype, $.fn.zluxDatesManager.prototype, {
		name: 'zluxDialogDatesManager',
		options: {
			title: 'Dates Manager',
			position: {}, // override the Dialog position
			full_mode: 0,
			dialogClass: '',
			mode: 'date', // date, time or datetime
			firstDay: 0 // week start day
		},
		events: {},
		initialize: function(dialogTrigger, options) {
			this.options = $.extend({}, this.options, options);
			var $this = this;

			// run initial check
			$this.initCheck();

			// save target
			$this.target = dialogTrigger;

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

			// prepare the dialog class
			$this.options.dialogClass = 'zl-bootstrap zlux-datesmanager' 
				+ ' zlux-datesmanager-' + $this.options.mode
				+ ($this.options.full_mode ? ' zlux-dialog-full ' : '') 
				+ ($this.options.dialogClass ? ' ' + $this.options.dialogClass : '');

			// set the dialog options
			$.fn.zlux("Dialog", {
				title: $this.options.title,
				width: $this.options.full_mode ? '75%' : 300,
				dialogClass: $this.options.dialogClass,
				scrollbar: false,
				position: $.extend({
					of: $this.dialogTrigger,
					my: 'left top',
					at: 'right bottom'
				}, $this.options.position)
			})

			// when plugin ready, save reference
			.done(function(plugin){

				// save dialog reference
				$this.zluxdialog = plugin;

				// set events
				$this.zluxdialog.bind("InitComplete", function() {

					// set the dialog unique ID
					$this.zluxdialog.widget.attr('id', 'zluxDatesManager_' + $this.ID);

					// init dialog related functions
					$this.eventDialogLoaded();
				});
			});
		},
		/*
		 * Fires when the dialog has finished it's initial tasks
		 */
		eventDialogLoaded: function() {
			var $this = this;

			// init dates manager
			$this.datesmanager = $('<div class="zlux-datesmanager" />').appendTo($this.zluxdialog.content);

			// set global close event
			$('html').on('mousedown', function(event) {
				// close if target is not the trigger or the dialog it self
				$this.zluxdialog.dialog('isOpen') && !$this.dialogTrigger.is(event.target) && !$this.dialogTrigger.find(event.target).length && !$this.zluxdialog.widget.find(event.target).length && !$this.zluxdialog.widget.is(event.target) && $this.zluxdialog.dialog('close')
			});

			// set the settings options
			var settings = {
				dateFormat: $.datepicker.ISO_8601,
				constrainInput: false,
				prevText: '', // important as icons have been modified
				nextText: '', // idem
				firstDay: $this.options.firstDay,
				onSelect: function(dateText, ins){
					// trigger event
					$this.trigger("DateSelected", dateText);
				}
			};

			// load date mode
			if ($this.options.mode == 'date') {

				// init
				$this.datesmanager.datepicker(settings)

				// show it
				.datepicker('show');

			// load time/datetime mode
			} else {

				$this.datesmanager.datetimepicker($.extend(settings, {
					timeFormat: 'hh:mm:ss',
					showSecond: false,
					timeOnly: $this.options.mode == 'time' ? true : false // will hide date if time mode
				}))

				// show it
				.datetimepicker('show');
			}

			// trigger event
			$this.trigger("InitComplete");
		},
		/**
		 * Init the Main Events
		 */
		initMainEvents: function() {
			var $this = this;

			// on manager init
			$this.bind("InitComplete", function(manager) {

				// show the content
				$this.zluxdialog.initContent();
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