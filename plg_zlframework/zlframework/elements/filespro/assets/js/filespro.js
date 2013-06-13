/* ===================================================
 * Files Pro edit script
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
	Plugin.prototype = $.extend(Plugin.prototype, $.fn['zluxMain'].prototype, {
		name: 'ElementFilespro',
		options: {
			root: '',
			extensions: '',
			storage: '',
			fileMode: ''
		},
		events: {},
		initialize: function(element, options) {
			this.options = $.extend({}, $.fn['zluxMain'].prototype.options, this.options, options);
			var $this = this;

			// save the element reference
			$this.element = element;

			// apply on each new instances
			element.on('click', 'p.add a', function () {
				$this.apply($('input.filespro', element));
			});
			
			// first time init
			$this.apply($('input.filespro', element));
		},
		apply: function (inputs){
			var $this = this;
				
			inputs.each(function(index, input)
			{
				var $input = $(input);

				if (!$(input).data('initialized'))
				{
					var id = 'filespro-element-' + index;
					
					// set input id
					$input.attr('id', id);

					// set main wrapper arount the input
					$this.wrapper = $input.closest('.row').addClass('zl-bootstrap');

					// init preview engine
					$this.zluxpreview = $.fn.zluxPreview();

					// init the file manager
					$input.zluxDialogFilesManager({
						root: $this.options.images,
						extensions: $this.options.extensions,
						storage: $this.options.storage
					})

					// on object select event
					.data('zluxDialogFilesManager').bind("ObjectSelected", function(manager, $object){

						// abort if file mode incompatible
						if ($this.options.fileMode == 'files' && $object.data.type != 'file') return;
						if ($this.options.fileMode == 'folders' && $object.data.type != 'folder') return;

						// prepare the value
						var value = manager.oTable.fnSettings().sCurrentPath + '/' + $object.data.path;

						// save new value in input
						$input.val(value).trigger('change');

						// update preview
						$('.zlux-preview', $this.wrapper).remove();
						$this.wrapper.append($this.zluxpreview.renderPreviewDOM($object.data));
					});

					// set the initial preview
					var oData = $('span.zlux-x-filedata', $this.wrapper).data('zlux-data');
					$this.wrapper.append($this.zluxpreview.renderPreviewDOM(oData));

					// create cancel button
					$('<i class="icon-remove zlux-x-cancel-btn" />').insertAfter($input).on('click', function () {
						$input.val('');
						$('.zlux-preview', $this.wrapper).remove();
					});
				
				} $input.data('initialized', !0);
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