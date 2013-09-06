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


/* ===================================================
 * ZLUX Field Items
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
		name: 'zluxFieldItems',
		options: {
			controlName: ''
		},
		events: {},
		initialize: function(target, options) {
			this.options = $.extend({}, $.fn.zluxMain.prototype.options, this.options, options);
			var $this = this;

			// init vars
			$this.aRelated = [];

			// save relations list
			$this.list = $('<ul />').addClass('zlux-field-items').appendTo(target)

			// init sortable feature
			.sortable({
				handle: '.zlux-x-sort',
				placeholder: 'dragging',
				axis: 'y',
				opacity: 1,
				delay: 100,
				tolerance: 'pointer',
				containment: 'parent',
				forcePlaceholderSize: !0,
				scroll: !1,
				start: function (event, ui) {
					ui.helper.addClass('ghost')
				},
				stop: function (event, ui) {
					ui.item.removeClass('ghost')
				}
			})

			// delete event
			.on('click', '.zlux-x-delete', function () {
				$(this).closest('li').fadeOut(200, function () {
					var $object = {};
					$object.id = $(this).data('id');

					$this.removeRelation($object);
				})
			});

			// append current values to list
			$('input', target).each(function(i, input){
				var $object = $(input).data('info');
				$this.appendRelation($object);

				// remove the original input
				$(input).remove();
			});
			
			// set the styling class
			target.addClass('zl-bootstrap');

			// set the trigger button
			$this.dialogTrigger = $('<button type="button" class="btn btn-mini"><i class="icon-plus-sign"></i> Add item </button>')

			// add it to dom
			.appendTo(target);

			// init the dialog manager
			$this.dialogTrigger.zlux("DialogItemsManager", {
				position: {
					of: target,
					my: 'left top',
					at: 'right top'
				},
				apps: '1'
			})

			// on object select event
			.on("zlux.ObjectSelected", function(e, manager, $object){

				// check if already related
				if($.inArray($object.id, $this.aRelated) != '-1') {

					// if so, unrelate
					$this.removeRelation($object);

				} else { // relate

					// set the object checkbox
					$('.column-icon i', $object.dom).removeClass('icon-file-alt').addClass('icon-check');

					// append the object to the relations list dom
					$this.appendRelation($object);
				}
			})

			.on("zlux.TableDrawCallback", function(e, manager){
				
				$('tbody tr', manager.oTable).each(function(i, object_dom){
					if($.inArray(object_dom.getAttribute('data-id'), $this.aRelated) != '-1') {
						$('.column-icon i', object_dom).removeClass('icon-file-alt').addClass('icon-check');
					}
				})

				// save the manager
				$this.manager = manager;
			})
		},
		appendRelation: function ($object) {
			var $this = this;
			
			// prepare object dom
			$('<li data-id="' + $object.id + '"><div>'
				+'<div class="zlux-x-name">' + $object.name + '</div>'
				+'<span class="zlux-x-tools">'
					+'<i class="zlux-x-delete icon-remove-circle" title="Delete"></i>'
					+'<i class="zlux-x-sort icon-move" title="Sort"></i>'
				+'</span>'
				+'<div class="zlux-x-info">'
					+'<div>' + $object.type.name + ' / ' + $object.application.name + '</div>'
				+'</div>'
				+'<input type="hidden" name="' + $this.options.controlName + '" value="' + $object.id + '"/>'
			+'</div></li>')

			// add to dom
			.appendTo($this.list);

			// add to related list
			$this.aRelated.push($object.id);
		},
		removeRelation: function ($object) {
			var $this = this;
			
			// remove from dom
			$('li[data-id="' + $object.id + '"]', $this.list).remove();

			// and from related array
			$this.aRelated.splice($.inArray($object.id, $this.aRelated), 1);

			// remove the checkbox from the manager
			if ($this.manager) {
				$('tbody tr[data-id="' + $object.id + '"] .column-icon i', $this.manager.oTable).removeClass('icon-check').addClass('icon-file-alt');
			}
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