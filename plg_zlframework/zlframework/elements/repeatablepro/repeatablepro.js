/* ===================================================
 * ElementRepeatablePro
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
	var Plugin = function () {};
	$.extend(Plugin.prototype, {
		name: "ElementRepeatablePro",
		options: {
			msgDeleteElement: 'Delete Element',
			msgSortElement: 'Sort Element',
			msgLimitReached: 'Limit reached',
			instanceLimit: '',
			url: ''
		},
		initialize: function (element, options) {
			this.options = $.extend({}, this.options, options);

			var $this = this,
				list = $('ul.repeatable-list', element),
				hidden = $('li.hidden', list).remove(),
				count = list.children('li.repeatable-element').length;

			// save some references
			$this.element = element;
			$this.list = list;
			$this.count = count;

			// save Add Instance current text
			$this.options.msgAddInstance = $('p.add a', element).html();

			// set buttons
			list.children('li.repeatable-element').each(function () {
				// wrap content
				$(this).children().wrapAll($('<div>').addClass('repeatable-content'));

				// attach btns
				$this.attachButtons($(this))
			});

			// init functions
			list.on('mousedown', '.zlux-x-sort', function () {
				$('.more-options.show-advanced', list).removeClass('show-advanced');
				list.height(list.height()); // set height so the layout is not altered on sorting
				$(this).closest('li.repeatable-element')
					.find('.more-options').hide().end()
					.find('.file-details').hide()

			}).on('mouseup', '.zlux-x-sort', function () {
				$(this).closest('li.repeatable-element')
					.find('.more-options').show().end()
					.find('.file-details').show()

			}).on('click', '.zlux-x-delete', function () {
				var instance = $(this).closest('li.repeatable-element');
				instance.fadeOut(200, function () {

					// remove from DOM
					$(this).remove();

					// trigger event
					element.trigger('instance.deleted', [instance]);

					// show back new instance button if limit on
					if($this.options.instanceLimit)
						$('p.add a', element).removeClass('disabled').html($this.options.msgAddInstance);

				})
			}).sortable({
				handle: '.zlux-x-sort',
				placeholder: 'repeatable-element dragging',
				axis: 'y',
				opacity: 1,
				delay: 100,
				cursorAt: {
					top: 16
				},
				tolerance: 'pointer',
				containment: 'parent',
				scroll: !1,
				start: function (event, ui) {
					ui.item.addClass('ghost');
					ui.placeholder.height(ui.item.height() - 2);
					ui.placeholder.width($('div.repeatable-content', ui.item).width() - 2);
				},
				stop: function (event, ui) {
					ui.item.removeClass('ghost');
					$('.more-options', ui.item).show();
					$('.file-details', ui.item).show();
					list.height(''); // reset height to default

					// update field indexes
					$this.updateIndexes($(event.target));
				}
			});

			// ADD INSTANCE default way
			$('p.add a', element).on('click', function()
			{
				// if limit reached abort instance creation
				if ($this.options.instanceLimit && $this.options.instanceLimit <= list.children().length) 
					return false;

				$this.addElementInstance(hidden.html());

				// if limit reached change button state
				if ($this.options.instanceLimit && $this.options.instanceLimit <= list.children().length)
					$('p.add a', element).addClass('disabled').html($this.options.msgLimitReached);
			});
		},
		/**
			Load by Ajax an Element instance

			@layout string The instance layout
			@ref string Reference or name of the layout
		*/
		loadElementInstance: function(layout, ref) {
			var $this = this;

			$.ajax({
				url: $this.options.ajax_url + '&task=callelement',
				type: 'POST',
				data: {
					method: 'getemptylayout',
					layout: layout
				},
				success : function(instance) {
					$this.addElementInstance(instance);
					$this.element.trigger('instance.added', [instance, ref]); // custom event for notifyng that the new instance is ready
				}
			})
		},
		/**
			Add the Element instance
		*/
		addElementInstance: function(instance) {
			var $this = this;

			// if level2 instances
			if($this.list.hasClass('repeatable-list-level2')) {

				// set level1 index taking the level1 index from first instance
				var patt = new RegExp(/(?:elements\[\w{8}-\w{4}-\w{4}-\w{4}-\w{12}\])\[(-?\d+)\]/),
					index = patt.exec($('[name^="elements"]', $this.list).first().attr('name')).pop();

				instance = instance.replace(/(elements\[\w{8}-\w{4}-\w{4}-\w{4}-\w{12}\])(\[-?\d+\])/g, '$1[' + index + ']');
				
				// increase level2 index
				instance = instance.replace(/(elements\[\S+])\[(-?\d+)\]/g, '$1[' + $this.count++ + ']');

			// if level1, increase index
			} else {
				instance = instance.replace(/(elements\[\w{8}-\w{4}-\w{4}-\w{4}-\w{12}\])(\[-?\d+\])/g, '$1[' + $this.count++ + ']');
			}

			// increase the [zluxvar-1] var
			instance = instance.replace(/\[zluxvar-1\]/g, $this.count);

			// set wrappers
			instance = $('<li class="repeatable-element"><div class="repeatable-content">' + instance + '</div></li>')

			// attach btns
			$this.attachButtons(instance);

			// empty values from all unhidden form inputs
			$('input, textarea', instance).filter(function(){return $(this).attr('type') != 'hidden'}).each(function () {
				$(this).val('').html('')
			});
			instance.appendTo($this.list);
			instance.children('div.repeatable-content').effect('highlight', {}, 1E3)
		},
		/**
			Attach tools btns to instance
		*/
		attachButtons: function(instance) {
			
			// if btns already present, abort
			if ($('.zlux-x-sort, .zlux-x-delete', instance)[0]) return;

			// otherwise add default buttons
			$('<span>').addClass('zlux-x-sort sort').attr('title', this.options.msgSortElement).appendTo(instance);
			$('<span>').addClass('zlux-x-delete delete').attr('title', this.options.msgDeleteElement).appendTo(instance)
		},
		/**
			Update fields indexes
		*/
		updateIndexes: function($element) {
			var $this = this;

			// get regex for name attribute
			if($this.list.hasClass('repeatable-list-level2')) {
				// if sub list, seek the final element reference
				var name_regex = new RegExp(/(elements\[\S+])\[(-?\d+)\]/);
			} else {
				// by default seek the first element reference
				var name_regex = new RegExp(/(elements\[\w{8}-\w{4}-\w{4}-\w{4}-\w{12}\])(\[-?\d+\])/);
			}

			// iterate over first level instances
			$this.list.children('li.repeatable-element').each(function(index){
				
				$('[name^="elements"]', $(this)).each(function(){
					var name = $(this).attr('name').replace(name_regex, '$1[' + index + ']');
					$(this).attr('name', name);
				})
			})
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