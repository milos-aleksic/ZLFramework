/* ===================================================
 * ZLUX Fields Manager
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	"use strict";
	var Plugin = function(options){
		this.options = $.extend({}, this.options, options);
		this.events = {};

		// init Options Field
		$('.zlux-field-option').zluxFieldOptions();
	};
	Plugin.prototype = $.extend(Plugin.prototype, $.fn.zluxManager.prototype, {
		name: 'zluxFieldsManager',
		options: {}
	});
	// Don't touch
	$.fn[Plugin.prototype.name] = Plugin;
})(jQuery);


/* ===================================================
 * ZLUX Field Options
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	"use strict";
	var Plugin = function(options){
		this.options = $.extend({}, this.options, options);
		this.events = {};
	};
	Plugin.prototype = $.extend(Plugin.prototype, $.fn.zluxMain.prototype, {
		name: 'zluxFieldOptions',
		options: {},
		events: {},
		initialize: function(target, options) {
			this.options = $.extend({}, $.fn.zluxMain.prototype.options, this.options, options);
			var $this = this;

			// init vars
			$this.list = target.children('ul');
			$this.hidden = $('li.hidden', $this.list).detach();


			target.on('click', '.delete', function() {
				$(this).closest('li').slideUp(400, function() {
					$(this).remove();
					$this.orderOptions();
				});

				return false;
			})

			.on('click', '.add', function() {
				$this.hidden.clone().removeClass('hidden').appendTo($this.list).slideDown(200).effect('highlight', {}, 1000).find('input:first').focus();
				$this.orderOptions();
			})


			.on('blur', '.name-input input', function() {
				var option = $(this).closest('li');
				var text = option.find('.panel input:text');

				if ($(this).val() !== '' && text.val() === '') {
					var alias = '';
					$this.getAlias($(this).val(), function(data){
						alias = data ? data : '42';
						text.val(alias);
						option.find('a.trigger').text(alias);
					});
				}
			})

			.on('keydown', '.panel input:text', function(event) {
				event.stopPropagation();
				if (event.which === 13) { $this.setOptionValue($(this).closest('li')); }
				if (event.which === 27) { $this.removeOptionPanel($(this).closest('li')); }
			})

			.on('click', 'input.accept', function () { 
				$this.setOptionValue($(this).closest('li'));

				return false;
			})

			.on('click', 'a.cancel', function () {
				$this.removeOptionPanel($(this).closest('li'));

				return false;
			})

			.on('click', 'a.trigger', function() {
				$(this).hide().closest('li').find('div.panel').addClass('active').find('input:text').focus();

				return false;
			});


			this.list.sortable({
				handle: 'div.sort-handle',
				containment: this.list.parent().parent(),
				placeholder: 'dragging',
				axis: 'y',
				opacity: 1,
				revert: 75,
				delay: 100,
				tolerance: 'pointer',
				zIndex: 99,
				start: function(event, ui) {
					ui.placeholder.height(ui.helper.height());
					$this.list.sortable('refreshPositions');
				},
				stop: function() {
					$this.orderOptions();
				}
			});
		},
		setOptionValue: function(option) {
			var $this = this;
			var text  = option.find('div.panel input:text');

			var alias = text.val();
			if (alias === '') {
				alias = option.find('div.name-input input').val();
			}

			this.getAlias(alias, function(data) {
				alias = data ? data : '42';
				text.val(alias);
				option.find('a.trigger').text(alias);
				$this.removeOptionPanel(option);
			});
		},
		orderOptions: function() {
			var $this = this;

			var pattern = /(elements\[\S+])\[(-?\d+)\]/g;
			$this.list.children('li').each(function(i) {
				$(this).find('input').each(function() {
					if ($(this).attr('name')) {
						$(this).attr('name', $(this).attr('name').replace(pattern, "$1["+i+"]"));
					}
				});
			});
		},
		getAlias: function(name, callback) {
			var url = $.fn.zluxMain.prototype.JBase + 'index.php?option=com_zoo&controller=manager&format=raw&task=getalias&force_safe=1';
			
			$.getJSON(url, { name: name }, 
				function(data) {
					callback(data);
				}
			);
		},
		removeOptionPanel: function(option){
			option.find('div.panel input:text').val(option.find('a.trigger').show().text());
			option.find('div.panel').removeClass('active');
		}
	});
	// Don't touch
	$.fn[Plugin.prototype.name] = function() {
		var args   = arguments;
		var method = args[0] ? args[0] : null;
		return this.each(function() {
			var element = $(this);
			if (Plugin.prototype[method] && element.data(Plugin.prototype.name) && method !== 'initialize') {
				element.data(Plugin.prototype.name)[method].apply(element.data(Plugin.prototype.name), Array.prototype.slice.call(args, 1));
			} else if (!method || $.isPlainObject(method)) {
				var plugin = new Plugin();
				if (Plugin.prototype.initialize) {
					plugin.initialize.apply(plugin, $.merge([element], args));
				}
				element.data(Plugin.prototype.name, plugin);
			} else {
				$.error('Method ' +  method + ' does not exist on jQuery.' + Plugin.name);
			}
		});
	};
})(jQuery);