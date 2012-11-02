/* Copyright (C) ZOOlanders.com - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only */

(function ($) {
    var b = function () {};
    $.extend(b.prototype, {
        name: 'ElementFilespro',
        options: {
			url: null,
			flashUrl: null,
			type: null,
			fileMode: 'files',
			title: 'Files',
			extensions: 'jpg,gif,png,zip,pdf',
			file_details: {}
        },
        initialize: function (b, c) {
            this.options = $.extend({}, this.options, c);
			var d = this,
				op = d.options;

			// apply on each new instances
            b.delegate('p.add a', 'click', function () {
				d.apply(b.find('input.'+op.type+'-element'));
            });
			
			d.apply(b.find('input.'+op.type+'-element'));
			
        },
		apply: function (inputs)
		{
			var d = this,
				op = d.options;
				
			inputs.each(function (c)
			{
				if (!$(this).data('initialized'))
				{
					var input = $(this),
						element = input.closest('.repeatable-element'),
						id = op.type+'-element-'+c;
					
					// set input id and options
					input.attr('id', id);
					d.setOptions(element, input);
					
					element.find('input.'+op.type+'-subelement').each(function (c)
					{
						d.setOptions(element, $(this));
					});

					input.val() || d.resetFileDetails(element.find(".file-details")); // clean preview if no file selected
				
				} $(this).data('initialized', !0);
			});
		},
		setOptions: function (element, input)
		{
			var d = this,
				op = d.options,
				
				// get file details dom
				details = input.parent('div.row').find('.file-details');
			
			// set preview now and after
			input.bind('change', function() {
				d.updatedetails(details);
			});

			// create folder/file selector
			input.DirectoriesPro({
				mode: op.fileMode, // files, folder, both
				url: op.url,
				title: op.title,
				extensions: op.extensions,
				filemanager: op.filemanager
			});
			
			// create cancel button
			var cancel_btn = $('<span>').addClass('input-cancel').insertAfter(input).click(function () {
				input.val('');
				d.resetFileDetails(details);
			});
			
		},
		updatedetails: function (details) {
		
			var d = this,
				op 			= d.options,
				input 		= details.prevAll('input'),
				preview 	= details.find('.file-preview'),
				fileinfo 	= details.find('.file-info'),
				found	 	= details.find('.fp-found'),
				missing 	= details.find('.fp-missing');

			
			// set preview
			if (input.val())
			{
				// retrieve and show details
				$.ajax({
					url: op.url,
					data: {
						task: 'callelement',
						method: 'getfiledetails',
						'args[0]': input.val()
					},
					type: 'post',
					datatype: 'json',
					beforeSend: function () {
						d.resetFileDetails(details);
						found.show();
						fileinfo.find('div').hide();
						fileinfo.find('div.file-name span').html('').addClass('zl-loaderhoriz').parent().show();
					},
					success: function (g) {
						g = $.parseJSON(g);
						if(g.name && g.all){
							preview.html(g.preview);
							g.name && fileinfo.find('.file-name span').html(g.name).removeClass('zl-loaderhoriz');
							g.all && fileinfo.find('.file-properties').html(g.all).show();
							found.show();
							missing.hide()
						} else {
							found.hide();
							missing.show()
						}
					}
				});
				details.show()

			} else { details.hide() };

		},
		bigpreview: function (input) {
			var op = this.options;
			
			$('<div>').append( $('<img>').attr('src', op.server + input.val()) ).dialog({
				width: 785,
				height: 450,
				resizable: false,
				title: input.val()
			});
		},
		resetFileDetails: function (details) {
            if(details){
				details.find('.fp-found, .fp-missing').hide();
				details.find('.file-preview, .file-name span, .file-properties').html('');
			}
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