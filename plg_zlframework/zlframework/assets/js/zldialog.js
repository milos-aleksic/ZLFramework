/* Copyright (C) JOOlanders SL http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only */

(function ($) {
    var a = function () {};
    $.extend(a.prototype, {
        name: "ZLdialog",
        initialize: function (a, d, callback) {
            this.options = $.extend({
                width: '300',
                height: '200',
                title: 'Dialog'
            }, d);
                // dialog content
                this.wrapper = $('<div><span class="zl-loaderhoriz" /></div>').insertAfter(a);
                // create dialog
                var e = this,
                    h = e.wrapper.dialog($.extend({
                    autoOpen: !1,
                    resizable: !1,
                    width: e.options.width,
                    height: e.options.height,
                    dialogClass: 'zldialog',
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
                    }
                }, e.options)).dialog('widget'),
                
                f = $('<span title="' + e.options.title + '" class="zl-btn-dialog" />').insertAfter(a).bind("click", function () {
                    e.wrapper.dialog(e.wrapper.dialog("isOpen") ? "close" : "open")
                    if (!$(this).data('initialized')){
                        callback(e);
                    } $(this).data('initialized', !0);
                });
            
            $('html').bind("mousedown", function (a) {
                // close if target is not the trigger, the dialog it self or a child of any qtip
                e.wrapper.dialog("isOpen") && !f.is(a.target) && !h.find(a.target).length && !$(a.target).closest('.qtip').length && e.wrapper.dialog("close")
            });
        },
        loaded: function () {
            this.wrapper.find('.zl-loaderhoriz').remove();
        },
        close: function () {
            this.wrapper.dialog('close');
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