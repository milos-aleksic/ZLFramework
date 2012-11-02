/* Copyright (C) ZOOlanders.com - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only */

(function ($) {
    var b = function () {};
    $.extend(b.prototype, {
        name: 'Plupload',
        options: {
			url: null,
			title: null,
			extensions: null,
			path: null,
			fileMode: 'files',
			callback: null
        },
        initialize: function (b, c) {
            this.options = $.extend({}, this.options, c);
			var d = this,
			
				// init plupload
				h = $('<div class="plupload" />').appendTo(b).plupload({
					// General settings
					runtimes : 'html5,flash',
					url : this.options.url+'&method=uploadFiles',
					flash_swf_url : this.options.flashUrl,
					max_file_size : '1000mb',
					max_file_count: 10, // user can add no more then 20 files at a time
					chunk_size : '1mb',
					// This resize has a bug in chrome / ff with pngs
					resize : false,
					// Rename files by clicking on their titles
					rename: true,
					// Sort files
					sortable: true,
					// Specify what files to browse for
					filters: [
						{title: "Files", extensions: this.options.extensions}
					],
					// Post init events, bound after the internal events
					init: {
					
						// get possible subfolder
						beforeUpload: function(up, file) 
						{
							up.settings.url = up.settings.url+'&path='+d.options.path;
							h.find('.plupload_cancel').hide();
						},
					
						UploadProgress: function(up, file) 
						{
							// Called when queu is 100% and at least 1 file uploaded
							if (up.total.uploaded >= 1 && up.total.percent == 100){
								d.options.callback();
								h.find('.plupload_cancel').show();	
							}
						}		
					}
				});
        }
    });
    $.fn[b.prototype.name] = function () {
        var e = arguments,
            c = e[0] ? e[0] : null;
        return this.each(function () {
            var d = $(this);
            if (b.prototype[c] && d.data(b.prototype.name) && c != "initialize") d.data(b.prototype.name)[c].apply(d.data(b.prototype.name), Array.prototype.slice.call(e, 1));
            else if (!c || $.isPlainObject(c)) {
                var f = new b;
                b.prototype.initialize && f.initialize.apply(f, $.merge([d], e));
                d.data(b.prototype.name, f)
            } else $.error("Method " + c + " does not exist on jQuery." + b.name)
        })
    };
})(jQuery);