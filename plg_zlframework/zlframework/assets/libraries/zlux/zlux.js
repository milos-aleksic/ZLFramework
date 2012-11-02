/* ===================================================
 * ZLUX SaveElement v0.1
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
    var Plugin = function(){};
    Plugin.prototype = $.extend(Plugin.prototype, {
        name: 'zluxSaveElement',
        options: {
            url: '',
            msgSaveElement: 'Save Element'
        },
        initialize: function(element, options) {
            this.options = $.extend({}, this.options, options);
            var $this = this;

            // append the button
            $('<a class="btn btn-small save" href="javascript:void(0);"><i class="icon-ok-sign"></i> '+$this.options.msgSaveElement+'</a>')
            .on('click', function()
            {
                var button = $(this).addClass('btn-working'),
                    postData = button.closest('.element').find('input, textarea').serializeArray();

                $.post($this.options.url+'&task=saveelement', postData, function(data) {
                    button.removeClass('btn-working');
                });
            }
            ).appendTo(element.find('.btn-toolbar'));
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


/* ===================================================
 * ZLUX BrowseFile v0.1
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
    var Plugin = function(){};
    Plugin.prototype = $.extend(Plugin.prototype, {
        name: 'zluxBrowseFile',
        options: {
            url: '',
            path: 'images'
        },
        initialize: function(input, options) {
            this.options = $.extend({}, this.options, options);
            var $this = this,
                JRoot = location.href.match(/^(.+)administrator\/index\.php.*/i)[1];

            // wrap
            input.wrap('<div class="zlux-finder"><div /></div>')

            // call dialog
            .zluxDialog({
                title: 'Dialog title',
                width: 600,
                height: 400,
                classes: 'zlux-finder'
            }, function(dialog){

                // load content on dialog
                dialog.content.zluxFinder({
                    dialog: dialog,
                    url: $this.options.url,
                    path: $this.options.path
                });

                // create events
                dialog.content.on('click', '.item.file a', function() {
                    input.val($(this).closest('.item').data('path')) && input.trigger('change');
                });
            });

            // prepare preview
            var preview = $('<div class="file-preview zl-bootstrap" />').append( $('<img class="img-polaroid" />') ).appendTo(input.parent());

             // '<div class="file-preview">'
             //        +'<div class="zlux-fp-found">'
             //            +'<div class="file-preview"></div>'
             //            +'<div class="file-info">'
             //                +'<div class="file-name"><span></span></div>'
             //                +'<div class="file-properties"></div>'
             //            +'</div>'
             //        +'</div>'
             //        +'<div class="fp-missing"></div>'
             //    +'</div>');


            // listen for new values
            input.on('change', function(){
                preview.find('img').attr('src', JRoot+input.val());
            })

            // init
            .trigger('change');

           

            // cancel button
            $('<span>').addClass('input-cancel').insertAfter(input).click(function () {
                input.val('');
                // $this.resetFileDetails(details);
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



/* ===================================================
 * ZLUX Finder v0.1
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
    var Plugin = function(){};
    Plugin.prototype = $.extend(Plugin.prototype, {
        name: 'zluxFinder',
        options: {
            dialog: {},
            url: '',
            path: 'images',
            open: 'open',
            filemanager: true
        },
        initialize: function(parent, options) {
            this.options = $.extend({}, this.options, options);
            var $this = this,
                $open = $this.options.open;

            parent.data('path', this.options.path).bind('zluxFinder.retrieve', retrieve).trigger('zluxFinder.retrieve');

            function retrieve(e) {
                e.preventDefault();
                var item = $(this).closest('.zlux-dialog li', parent);

                if (!item.length) {
                    item = parent;
                }

                if(!item.hasClass('file')) // if folder
                {
                    if(item.hasClass($open) && !item.hasClass('reload')){
                        item.removeClass($open).children('ul').slideUp() ;
                    } else {
                        item.addClass('loading');

                        $.post($this.options.url+'&controller=zlframework&task=JSONfiles', {path: item.data('path')},
                        function (itemsData) // AJAX response
                        {
                            item.removeClass('loading').addClass($open);

                            // item.html(''); // clean scenario
                            $this.options.dialog.loaded();
                            item.children().remove('ul.items');

                            if(!itemsData || itemsData.msg) // fail
                            {
                                // if msg present show it instead
                                // item.append('<ul>').children('ul').append($('<li>' + itemsData.msg + '</li>'));
                            }
                            else // succesfull
                            {
                                $this.buildItemsTree(itemsData, item);
                                item.find('ul a').on('click', retrieve).on('click', function(){
                                    $(this).closest('.zlux-dialog').find('.selected').removeClass('selected');
                                    $(this).closest('.item').addClass('selected');
                                })
                                
                                item.children('ul').slideDown();

                                // populate with data tree
                            
                                if(!item.hasClass('reload') || !item.children('ul').length) // first time
                                {
                                    // $this.buildItemsTree(itemsData, item);
                                    // item.find('ul a').bind('click', retrieve);
                                    // item.children('ul').slideDown();
                                }
                                else
                                {
                                    // item.children('ul').slideUp(400, function()
                                    // {
                                    //     item.removeClass('reload');
                                    //     $this.buildItemsTree(itemsData, item);
                                    //     item.find('ul a').bind('click', retrieve);
                                    //     item.children('ul').slideDown();
                                    // })
                                }
                                

                                // add root file manager options
                                if($this.options.filemanager) {
                                    ( parent.data('toolbar-initialized') || (
                                        
                                        // append toolbar for the main folder
                                        $('<span class="root-folder tools" />')
                                            .append(
                                                // refresh feature
                                                $('<span class="zl-btn-small refresh action" title="Refresh" />').bind('click', function()
                                                {
                                                    item.addClass('reload').find('li').addClass('loading');
                                                    parent.trigger('zluxFinder.retrieve');
                                                })
                                            ).append(
                                                // upload feature
                                                $('<span class="zl-btn-small plupload action" title="'+filesPro.translate('Upload files into the main folder')+'" />').bind('click', function()
                                                {
                                                    $this.plupload($(this), '', function(){
                                                        item.addClass('reload').find('li').addClass('loading');
                                                        parent.trigger('zluxFinder.retrieve');
                                                    })
                                                })
                                            ).append(
                                                // new folder feature
                                                $('<span class="zl-btn-small add action" title="'+filesPro.translate('Create a new folder into the main folder')+'" />').bind('click', function()
                                                {
                                                    $this.Prompt(filesPro.translate('Input a name for the new folder'), filesPro.translate('MyFolder'), $(this), function(response){
                                                        // if yes create new folder
                                                        
                                                        response && item.find('li').addClass('loading') && $.post($this.options.url+'&method=newfolder', {path: '', newfolder: response}, function () {
                                                            item.addClass('reload');
                                                            a.trigger('zluxFinder.retrieve');
                                                        }, 'json')
                                                    });
                                                })
                                                
                                            ).prependTo(parent.closest('.ui-dialog').find('.ui-dialog-titlebar'))
                                        
                                    , parent.data('toolbar-initialized', !0)) );
                                    
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
                        }, 'json');
                    }
                }
            }
        },
        // create dom tree
        buildItemsTree: function (itemsData, item)
        {
            var $this = this;
            item.append('<ul class="items" />').children('ul').hide();

            $.each(itemsData, function(h, g) 
            {
                var newItem = $('<li class="item" />').addClass(g.type).data('path', g.path).data('size', g.size);

                // add file manager options
                if($this.options.filemanager)
                {
                    $('<div class="btns"><a href="#">' + g.name + '</a></div>').append
                    (
                        $('<span class="tools" />')
                        .append(
                            // upload feature if folder
                            (g.type == 'folder') && $('<span class="zl-btn-small plupload action" title="'+filesPro.translate('Upload files into this folder')+'" />').bind('click', function()
                            {
                                var clicked = $(this);
                                $this.plupload(clicked.closest('li'), g.path, function(){
                                    clicked.closest('li').addClass('reload');
                                    clicked.closest('.finderpro .btns').find('a').trigger('click');
                                })
                            })
                        ).append(
                            // new folder feature
                            (g.type == 'folder') && $('<span class="zl-btn-small add action" title="'+filesPro.translate('Create a new subfolder')+'" />').bind('click', function()
                            {
                                var clicked = $(this);
                                $this.Prompt(filesPro.translate('Input a name for the new folder'), filesPro.translate('MyFolder'), clicked.closest('li'), function(response){
                                    // if yes create new folder
                                    response && clicked.closest('li').addClass('reload loading') && $.post($this.options.url+'&method=newfolder', {path: g.path, newfolder: response}, function () {
                                        // add dom
                                        clicked.closest('.btns').find('a').trigger('click');
                                    }, 'json')
                                });
                            })
                        ).append(
                            // delete feature
                            $('<span class="zl-btn-small delete action" title="'+filesPro.translate('Delete')+'" />').click(function()
                            {
                                var clicked = $(this);
                                $this.Confirm(filesPro.translate('You are about to delete')+' "'+g.name+'"', clicked.closest('li'), function(response){
                                    // if yes, delete
                                    response && clicked.closest('li').addClass('loading') && $.post($this.options.url+'&method=delete', {path: g.path}, function () {
                                        clicked.closest('li').fadeOut(400, function(){
                                            clicked.closest('li').remove()
                                        })
                                    }, 'json')
                                });
                                
                            })
                        )
                    )
                    
                    // append
                    .appendTo(newItem);
                };

                // set and append
                newItem.appendTo(item.children('ul'));     
            });
            
        },
        
        // Plupload
        plupload: function (target, path, callback)
        {
            var $this = this,
                p = $('<div />').appendTo('body').Plupload({
                    url: $this.options.url,
                    path: (path === undefined ? '' : path),
                    extensions:  $this.options.extensions,
                    fileMode: 'files',
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
                    $this.reset();
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




/* ===================================================
 * ZLUX Dialog v0.1
 * https://zoolanders.com/extensions/zl-framework
 * ===================================================
 * Copyright (C) JOOlanders SL 
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * ========================================================== */
(function ($) {
    var Plugin = function(){};
    Plugin.prototype = $.extend(Plugin.prototype, {
        name: 'zluxDialog',
        options: {
            width: '300',
            height: '150',
            title: 'Dialog',
            classes: ''
        },
        initialize: function(input, options, callback) {
            this.options = $.extend({}, this.options, options);
            var $this = this;

            // dialog content
            this.content = $('<div><span class="zlux-loader-horiz" /></div>').insertAfter(input);
            
            // create dialog
            var $this = this,
                h = $this.content.dialog($.extend({
                autoOpen: !1,
                resizable: !1,
                width: $this.options.width,
                height: $this.options.height,
                dialogClass: 'zlux-dialog zl-bootstrap'+($this.options.classes ? ' '+$this.options.classes : ''),
                open: function () {
                    h.position({
                        of: f,
                        my: 'left top',
                        at: 'right bottom'
                    })
                },
                dragStop: function(event, ui) {
                    window.qtip && $('.qtip').qtip('reposition');
                },
                close: function(event, ui) {
                    window.qtip && $('.qtip').qtip('hide');
                }
            }, $this.options)).dialog('widget'),
            
            // open dialog icon
            f = $('<span title="' + $this.options.title + '" class="files" />').insertAfter(input).bind("click", function () {
                $this.content.dialog($this.content.dialog("isOpen") ? "close" : "open")
                if (!$(this).data('initialized')){
                    callback($this);
                } $(this).data('initialized', !0);
            });
            
            $('html').bind('mousedown', function(event) {
                // close if target is not the trigger, the dialog it self or a child of any qtip
                $this.content.dialog('isOpen') && !f.is(event.target) && !h.find(event.target).length && !$(event.target).closest('.qtip').length && $this.content.dialog('close')
            });
        },
        loaded: function () {
            this.content.find('.zlux-loader-horiz').remove();
        },
        close: function () {
            this.content.dialog('close');
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
