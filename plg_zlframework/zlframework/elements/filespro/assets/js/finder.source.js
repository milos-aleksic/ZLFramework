/* Copyright (C) ZOOlanders.com - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only */

(function ($) {
    var a = function () {};
    $.extend(a.prototype, {
        name: "FinderPro",
        initialize: function (a, d) {
            function e(d) {
                d.preventDefault();
                var f = $(this).closest(".finderpro li", a),
					zf = a; // finderpro
                f.length || (f = a);
				// retrieve data
				!f.hasClass('file') && (f.hasClass(b.options.open) && !f.hasClass('reload') ? f.removeClass(b.options.open).children("ul").slideUp() : (f.addClass('loading'), $.post(b.options.url+'&method=files', {
                    path: 	  f.data("path"),
					req_type: (f.data('type') || 'init') // if no value, send init
                },
				function (folders) // If succesfull, populate
				{
					f.find('span.zl-loaderhoriz').remove();
                    f.removeClass('loading').addClass(b.options.open);
					if(folders.msg)
					{
						// if msg present show it instead
						f.children().remove("ul");
						f.append("<ul>").children("ul").append($('<li>' + folders.msg + '</li>'));
					}
					else 
					{
						// populate with data tree
						(!f.hasClass('reload') || !f.children('ul').length) ? b.tree(folders, f, e) : f.children('ul').slideUp(400, function(){
							f.removeClass('reload');
							b.tree(folders, f, e)
						});

						// add root file manager options
						if(b.options.filemanager) {
							( a.data("toolbar-initialized") || (
								
								// append toolbar for the main folder
								$('<span class="root-folder tools" />')
									.append(
										// refresh feature
										$('<span class="zl-btn-small refresh action" title="Refresh" />').bind('click', function()
										{
											f.addClass('reload').find('li').addClass('loading');
											a.trigger("retrieve:finderpro");
										})
									).append(
										// upload feature
										$('<span class="zl-btn-small plupload action" title="'+filesPro.translate('Upload files into the main folder')+'" />').bind('click', function()
										{
											b.plupload($(this), '', function(){
												f.addClass('reload').find('li').addClass('loading');
												a.trigger("retrieve:finderpro");
											})
										})
									).append(
										// new folder feature
										$('<span class="zl-btn-small add action" title="'+filesPro.translate('Create a new folder into the main folder')+'" />').bind('click', function()
										{
											b.Prompt(filesPro.translate('Input a name for the new folder'), filesPro.translate('MyFolder'), $(this), function(response){
												// if yes create new folder
												
												response && f.find('li').addClass('loading') && $.post(b.options.url+'&method=newfolder', {path: '', newfolder: response}, function () {
													f.addClass('reload');
													a.trigger("retrieve:finderpro");
												}, "json")
											});
										})
										
									).prependTo(zf.closest('.ui-dialog').find('.ui-dialog-titlebar'))
								
							, a.data("toolbar-initialized", !0)) );
							
							// attach qTip Events
							$('.ui-dialog').find('.tools span').qtip({
								position: {
									my: 'bottom left',
									at: 'top center'
								},
								show: {
									delay: 700
								},
								style: 'ui-tooltip-custom ui-tooltip-light ui-tooltip-rounded ui-tooltip-dialogue'
							});
						}
					}
                }, "json")))
            }
            var b = this;
            this.options = $.extend({
                url: "",
                path: "",
                open: "open",
                loading: "loading"
            }, d);
            a.data("path", this.options.path).bind("retrieve:finderpro", e).trigger("retrieve:finderpro")
        },
		
		// create dom tree
		tree: function (folders, f, e)
		{
			var b = this;
			folders.length && (f.children().remove("ul"), f.append("<ul>").children("ul").hide(), $.each(folders, function (h, g) 
			{
				f.children("ul").append(
					$('<li>').append(
						$('<div class="btns"><a href="#">' + g.name + '</a></div>').append(
						
							// add file manager options
							b.options.filemanager && $('<span class="tools" />')
							.append(
								// upload feature if folder
								(g.type == 'folder') && $('<span class="zl-btn-small plupload action" title="'+filesPro.translate('Upload files into this folder')+'" />').bind('click', function()
								{
									var clicked = $(this);
									b.plupload(clicked.closest('li'), g.path, function(){
										clicked.closest('li').addClass('reload');
										clicked.closest('.finderpro .btns').find('a').trigger('click');
									})
								})
							).append(
								// new folder feature
								(g.type == 'folder') && $('<span class="zl-btn-small add action" title="'+filesPro.translate('Create a new subfolder')+'" />').bind('click', function()
								{
									var clicked = $(this);
									b.Prompt(filesPro.translate('Input a name for the new folder'), filesPro.translate('MyFolder'), clicked.closest('li'), function(response){
										// if yes create new folder
										response && clicked.closest('li').addClass('reload loading') && $.post(b.options.url+'&method=newfolder', {path: g.path, newfolder: response}, function () {
											// add dom
											clicked.closest('.btns').find('a').trigger('click');
										}, "json")
									});
								})
							).append(
								// delete feature
								$('<span class="zl-btn-small delete action" title="'+filesPro.translate('Delete')+'" />').click(function()
								{
									var clicked = $(this);
									b.Confirm(filesPro.translate('You are about to delete')+' "'+g.name+'"', clicked.closest('li'), function(response){
										// if yes, delete
										response && clicked.closest('li').addClass('loading') && $.post(b.options.url+'&method=delete', {path: g.path}, function () {
											clicked.closest('li').fadeOut(400, function(){
												clicked.closest('li').remove()
											})
										}, "json")
									});
									
								})
							)
						)
					).addClass(g.type).data("path", g.path).data("type", g.type).data("val", g.val)
				)
			}), f.find("ul a").bind("click", e), f.children("ul").slideDown());
		},
		
		// Plupload
		plupload: function (target, path, callback)
		{
			var b = this,
				p = $('<div />').appendTo('body').Plupload({
					url: b.options.url,
					path: (path === undefined ? '' : path),
					extensions:  b.options.extensions,
					fileMode: 'files',
					max_file_size: b.options.max_file_size,
					callback: callback
				});
			
			// append to plupload the cancel button
			$('<a class="plupload_button plupload_cancel ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" />')
				.append($('<span class="ui-button-icon-primary ui-icon ui-icon-circle-close"></span>'))
				.append($('<span class="ui-button-text">'+filesPro.translate('Cancel')+'</span>'))
				.bind('hover', function(){
					$(this).toggleClass('ui-state-hover')
				})
				.bind('click', function(){
					$(this).closest('.qtip').qtip('hide')
					b.reset();
				})
				.appendTo(p.find('.plupload_buttons'));
			
			this.dialogue(p, target, 'plupload');
		},
		
		// qTip Dialogue
		dialogue: function (content, target, style)
		{
			this.reset();
			target.find('.btns').first().addClass('action'); // add action class
			$(target).qtip({
				content: {
					text: content
				},
				position: {
					my: 'left bottom',
					at: 'top right',
					viewport: $(window)
				},
				show: {
					ready: true, // Show it straight away
					solo: true,
					delay: 0
				},
				hide: false, // We'll hide it maunally so disable hide events
				style: style+' ui-tooltip-custom ui-tooltip-filespro ui-tooltip-light ui-tooltip-rounded ui-tooltip-dialogue', // Add a few styles
				events: {
					// Hide the tooltip when any buttons in the dialogue are clicked
					render: function(event, api) {
						$('button', api.elements.content).click(api.hide);
					},
					// Destroy the tooltip once it's hidden as we no longer need it!
					hide: function(event, api) { api.destroy(); }
				}
			});
		},
		
		// Confirm method
		Confirm: function (question, target, callback)
		{
			// Content will consist of the question and ok/cancel buttons
			var message = $('<p />', { text: question }),
				ok = $('<button />', { 
					text: filesPro.translate('Confirm'),
					click: function() { 
						callback(true);
						target.closest('.finderpro').find('.btns').removeClass('action');
					}
				}),
				cancel = $('<button />', { 
					text: filesPro.translate('Cancel'),
					click: function() { 
						callback(false); 
						target.closest('.finderpro').find('.btns').removeClass('action');
					}
				});

			this.dialogue(message.add(ok).add(cancel), target);
		},
		
		// Prompt method
		Prompt: function (question, placeholder, target, callback)
		{
			// Content will consist of a question elem and input, with ok/cancel buttons
			var message = $('<p />', { text: question }),
				input = $('<input />', { val: placeholder }),
				ok = $('<button />', { 
					text: filesPro.translate('Confirm'),
					click: function() { 
						callback(input.val());
						target.closest('.finderpro').find('.btns').removeClass('action');
					}
				}),
				cancel = $('<button />', {
					text: filesPro.translate('Cancel'),
					click: function() { 
						callback(null);
						target.closest('.finderpro').find('.btns').removeClass('action');
					}
				});

			this.dialogue(message.add(input).add(ok).add(cancel), target);
		},
		
		// reset
		reset: function ()
		{
			$('.finderpro').find('.btns').removeClass('action'); // remove from all
			$('.qtip').qtip('hide');
		}
    });
    $.fn[a.prototype.name] = function () {
        var g = arguments,
            d = g[0] ? g[0] : null;
        return this.each(function () {
            var e = $(this);
            if (a.prototype[d] && e.data(a.prototype.name) && d != "initialize") e.data(a.prototype.name)[d].apply(e.data(a.prototype.name), Array.prototype.slice.call(g, 1));
            else if (!d || $.isPlainObject(d)) {
                var b = new a;
                a.prototype.initialize && b.initialize.apply(b, $.merge([e], g));
                e.data(a.prototype.name, b)
            } else $.error("Method " + d + " does not exist on jQuery." + a.name)
        })
    }
})(jQuery);
(function ($) {
    var a = function () {};
    $.extend(a.prototype, {
        name: "DirectoriesPro",
        initialize: function (a, d) {
            this.options = $.extend({
                url: "",
                title: "Folders",
				extensions: null,
                mode: "folder", // files, folder, both
				filemanager: false
            }, d);
            var e = this,
				// dialog content
                b = $('<div class="finderpro"><span class="zl-loaderhoriz" /></div>').insertAfter(a).delegate("a", "click", function () {
                    b.find("div").removeClass("selected");
                    var d = $(this).parent().addClass("selected").parent();
					(e.options.mode == "files" && d.hasClass("file")) && a.val(d.data("val")) && a.trigger('change');
					(e.options.mode == "folders" && d.hasClass("folder")) && a.val(d.data("val")) && a.trigger('change');
					e.options.mode == "both" && a.val(d.data("val")) && a.trigger('change');
					b.FinderPro("reset");
                }),
				// create dialog
                h = b.dialog($.extend({
                    autoOpen: !1,
                    resizable: !1,
                    open: function () {
                        h.position({
                            of: f,
                            my: "left top",
                            at: "right bottom"
                        })
                    },
					dragStop: function(event, ui) {
						$('.qtip').qtip('reposition');
					},
					close: function(event, ui) {
						$('.qtip').qtip('hide');
						$('.finderpro .btns').removeClass('action');
					}
                }, e.options)).dialog("widget"),
                
				f = $('<span title="' + e.options.title + '" class="files" />').insertAfter(a).bind("click", function () {
                    b.dialog(b.dialog("isOpen") ? "close" : "open")
					if (!$(this).data('initialized')){
						b.FinderPro(e.options)
					} $(this).data('initialized', !0);
                });
            a.data('icon', f);
            a.data('dialog', h);
            $('html').bind("mousedown", function (a) {
				// close if target is not the trigger, the dialog it self or a child of any qtip
                b.dialog("isOpen") && !f.is(a.target) && !h.find(a.target).length && !$(a.target).closest('.qtip').length && b.dialog("close")
            })
        }
    });
    $.fn[a.prototype.name] = function () {
        var g = arguments,
            d = g[0] ? g[0] : null;
        return this.each(function () {
            var e = $(this);
            if (a.prototype[d] && e.data(a.prototype.name) && d != "initialize") e.data(a.prototype.name)[d].apply(e.data(a.prototype.name), Array.prototype.slice.call(g, 1));
            else if (!d || $.isPlainObject(d)) {
                var b = new a;
                a.prototype.initialize && b.initialize.apply(b, $.merge([e], g));
                e.data(a.prototype.name, b)
            } else $.error("Method " + d + " does not exist on jQuery." + a.name)
        })
    }
})(jQuery);