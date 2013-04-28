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


			// hide main so it's content is not accesible during initial loading
			$this.main.hide();

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
			$this._spinner = $('<i class="zlux-loader-circle-big icon-spinner icon-spin" />').appendTo($this.widget);
			$this._spinner_area = $this.main;

			// init scrollbar
			$this.main.perfectScrollbar();

			// set toolbar
			$this.toolbar = {};
			$this.toolbar.wrapper = $('<div class="zlux-dialog-toolbar" />').hide().insertAfter($this.widget.children()[0]);

			// trigget init event
			$this.trigger("InitComplete");

			// not inited until the content is ready
			$this.inited = false;
		},
		/**
		 * Called from the outside when the content is ready to be shown
		 */
		initContent: function() {
			var $this = this;

			// show the main content
			$this.main.show();
			
			// set the dom status
			$this.widget.attr('data-zlux-status', 'ready');

			// init dialog scrollbar
			$this.scrollbar('refresh');

			// set init state
			$this.inited = true;

			// hide the spinner, must go after the state
			$this.spinner('hide');
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
				var $scroll_y = $('.ps-scrollbar-y', $this.main),
					$scroll_x = $('.ps-scrollbar-x', $this.main).remove();

				$scroll_y.css('top', 0);
				// $x.css('left', 0);
				$this.scrollbar('show'); // let's be sure is visible
				$this.scrollbar('update');

				// if no scroll necesary, hide it
				var margin = Math.round($this.main.height()) - Math.round($scroll_y.height());
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

			if ($this.inited) {
				if (action == 'show') {
					$this.main.fadeTo(0, 0.5);
					$this._spinner.show();

				} else { // hide

					$this._spinner.hide();
					$this.main.add($this.toolbar.wrapper).fadeTo('slow', 1);
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