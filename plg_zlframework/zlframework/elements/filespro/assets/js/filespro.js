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
	Plugin.prototype = $.extend(Plugin.prototype, $.fn.zluxMain.prototype, {
		name: 'ElementFilespro',
		options: {
			root: '',
			extensions: '',
			storage: '',
			fileMode: '',
			max_file_size: '',
			title: ''
		},
		events: {},
		initialize: function(element, options) {
			this.options = $.extend({}, $.fn.zluxMain.prototype.options, this.options, options);
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
					var id = 'filespro-element-' + index,

						// set main wrapper arount the input
						$wrapper = $input.closest('.row').addClass('zl-bootstrap'),

						// should the preview render a mini file preview?
						filePreview = $input.hasClass('image-element') ? true : false;

					// set input id
					$input.attr('id', id);

					// init preview engine
					$this.zluxpreview = $.fn.zluxPreview();

					var storage = $('span.zlux-x-filedata', $wrapper).data('zlux-storage');

					// init the file manager
					$input.zluxDialogFilesManager({
						root: $this.options.images,
						extensions: $this.options.extensions,
						storage: 'local',
						max_file_size: '1024kb',
						title: $this.options.title,
						storage: storage.engine,
						storage_params: storage,
						root: storage.root
					})

					// on object select event
					.data('zluxDialogFilesManager').bind("ObjectSelected", function(manager, $object){

						// abort if file mode incompatible
						if ($this.options.fileMode == 'files' && $object.type != 'file') return;
						if ($this.options.fileMode == 'folders' && $object.type != 'folder') return;

						// prepare the value
						var value = $input.data('zluxDialogFilesManager')._getFullPath($object.name);

						// save new value in input
						$input.val(value).trigger('change');

						// update preview
						$('.zlux-preview', $wrapper).remove();
						$wrapper.append($this.zluxpreview.renderPreviewDOM($object, filePreview));
					});

					// set the initial preview
					var oData = $('span.zlux-x-filedata', $wrapper).data('zlux-data');
					if (!$.isEmptyObject(oData) && $input.val()) {
						$wrapper.append($this.zluxpreview.renderPreviewDOM(oData, filePreview));	
					}

					// create cancel button
					$('<i class="icon-remove zlux-x-cancel-btn" />').insertAfter($input).on('click', function () {
						$input.val('');
						$('.zlux-preview', $wrapper).remove();
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