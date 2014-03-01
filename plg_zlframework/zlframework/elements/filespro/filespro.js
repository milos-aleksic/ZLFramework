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
	Plugin.prototype = $.extend(Plugin.prototype, $.zlux.Main.prototype, {
		name: 'ElementFilespro',
		options: {
			root: '',
			extensions: '',
			storage: '',
			fileMode: '',
			max_file_size: '',
			title: '',
			resize: {}
		},
		events: {},
		initialize: function(element, options) {
			this.options = $.extend({}, $.zlux.Main.prototype.options, this.options, options);
			var $this = this;

			// save the element reference
			$this.element = element;

			// apply on each new instances
			element.on('click', 'p.add a', function () {
				$this.apply(
					$('input.filespro', element)

					// fix for media pro
					.add('input.mediapro-element', element)
				);
			});
			
			// first time init
			$this.apply(
				$('input.filespro', element)
				
				// fix for media pro
				.add('input.mediapro-element', element)
			);
		},
		apply: function (inputs){
			var $this = this;

			$.zlux.assets.load('files').done(function(){

				// load preview engine
				var filesPreview = $.zlux.filesPreview();
				
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

						var storage = $('span.zlux-x-filedata', $wrapper).data('zlux-storage');

						// set the trigger button
						$this.dialogTrigger = $('<a title="' + $this.options.title + '" class="btn btn-mini zlux-btn-edit" href="#"><i class="icon-edit"></i></a>')

						// add it to dom
						.appendTo($wrapper);

						// init the file manager
						$this.dialogTrigger.zlux("filesDialogManager", {
							extensions: $this.options.extensions,
							max_file_size: $this.options.max_file_size,
							title: $this.options.title,
							storage: storage.engine,
							storage_params: storage,
							root: storage.root,
							resize: $this.options.resize
						})

						// on object select event
						.on("zlux.ObjectSelected", function(e, manager, $object){

							// abort if file mode incompatible
							if ($this.options.fileMode == 'files' && $object.type != 'file') return;
							if ($this.options.fileMode == 'folders' && $object.type != 'folder') return;

							// prepare the value
							var value = manager.getFullPath($object.name);

							// save new value in input
							$input.val(value).trigger('change');

							// store the path for the preview
							$object.path = value;

							// update preview
							$('.zlux-preview', $wrapper).remove();
							$wrapper.append(filesPreview.renderPreviewDOM($object, filePreview));
						});

						// set the initial preview
						var oData = $('span.zlux-x-filedata', $wrapper).data('zlux-data');
						if (!$.isEmptyObject(oData) && $input.val()) {
							// store the path for the preview
							oData.path = $input.val();
							$wrapper.append(filesPreview.renderPreviewDOM(oData, filePreview));	
						}

						// create cancel button
						$('<i class="icon-remove zlux-x-cancel-btn" />').insertAfter($input).on('click', function () {
							$input.val('');
							$('.zlux-preview', $wrapper).remove();
						});
					
					} $input.data('initialized', !0);
				});
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