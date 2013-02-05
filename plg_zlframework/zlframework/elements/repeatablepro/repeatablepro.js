/* Copyright (C) YOOtheme GmbH, Copyright (C) JOOlanders SL for any modification http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only */

(function ($) {
	var a = function () {};
	$.extend(a.prototype, {
		name: "ElementRepeatablePro",
		options: {
			msgDeleteElement: 'Delete Element',
			msgSortElement: 'Sort Element',
			msgLimitReached: 'Limit reached',
			instanceLimit: '',
			url: ''
		},
		initialize: function (e, a) {
			this.options = $.extend({}, this.options, a);
			var d = this,
				c = e.find("ul.repeatable-list"),
				g = c.find("li.hidden").remove(),
				h = c.find("li.repeatable-element").length;

			// save Add Instance current text
			d.options.msgAddInstance = e.find("p.add a").html();

			// set buttons
			c.find("li.repeatable-element").each(function () {
				d.attachButtons($(this))
			});

			// init functions
			c.delegate("span.sort", "mousedown", function () {
				c.find(".more-options.show-advanced").removeClass("show-advanced");
				c.height(c.height()); // set height so the layout is not altered on sorting
				$(this).closest("li.repeatable-element")
					.find(".more-options").hide().end()
					.find(".file-details").hide()
			}).delegate("span.sort", "mouseup", function () {
				$(this).closest("li.repeatable-element")
					.find(".more-options").show().end()
					.find(".file-details").show()
			}).delegate("span.delete", "click", function () {
				$(this).closest("li.repeatable-element").fadeOut(200, function () {
					$(this).remove();

					// show back new instance button if limit on
					if(d.options.instanceLimit){
						e.find("p.add a").removeClass('disabled').html(d.options.msgAddInstance);
					}
				})
			}).sortable({
				handle: "span.sort",
				placeholder: "repeatable-element dragging",
				axis: "y",
				opacity: 1,
				delay: 100,
				cursorAt: {
					top: 16
				},
				tolerance: "pointer",
				containment: "parent",
				scroll: !1,
				start: function (b, a) {
					a.item.addClass("ghost");
					a.placeholder.height(a.item.height() - 2);
					a.placeholder.width(a.item.find("div.repeatable-content").width() - 2);
				},
				stop: function (a, b) {
					b.item.removeClass("ghost");
					b.item.find('.more-options').show();
					b.item.find('.file-details').show();
					c.height(''); // reset height to default
					
					// reset the name index
					$('.repeatable-element', $(a.target)).each(function(index){
						$('input, textarea', $(this)).each(function(){
							
							if($(this).attr('name')) {
								var name = $(this).attr('name').replace(/(elements\[\S+])\[(-?\d+)\]/g, "$1["+index+"]");
								$(this).attr('name', name);	
							}
						})
					})
				}
			});

			// ADD ELEMENT default way
			e.find("p.add a").on("click", function()
			{
				// if limit reached abort instance creation
				if(d.options.instanceLimit && d.options.instanceLimit <= c.children().length){
					return false;
				}

				d.addElement(c, g.html().replace(/(elements\[\S+])\[(-?\d+)\]/g, "$1[" + h+++"]"));

				// if limit reached change button state
				if(d.options.instanceLimit && d.options.instanceLimit <= c.children().length){
					e.find("p.add a").addClass('disabled').html(d.options.msgLimitReached);
				}
				
			});

			// ADD ELEMENT extended way, multilple layouts possible
			e.find(".btn-group.ajax-add-instance .dropdown-menu a").on("click", function()
			{
				var b = $(this),
					btn_main = b.closest('.btn-group').find('.btn.dropdown-toggle').addClass('btn-working'),
					layout = b.data('layout');

				$.ajax({
					url: d.options.url+'&task=callelement',
					type: 'GET',
					data: {
						method: 'loadeditlayout',
						layout: layout
					},
					success : function(data) {
						d.addElement(c, data.replace(/(elements\[\S+])\[(-?\d+)\]/g, '$1[' + h+++']'));
						btn_main.removeClass('btn-working');
						b.trigger('newinstance'); // custom event for noticing the new instance is ready
					}
				})
			})
		},
		addElement: function(c, element)
		{
			var d = this,
				a = $('<li class="repeatable-element" />').html(element);

			d.attachButtons(a);

			// empty values from all unhidden form inputs
			a.find('input, textarea').filter(function(){return $(this).attr('type') != 'hidden'}).each(function () {
				$(this).val('').html('')
			});
			a.appendTo(c);
			a.children('div.repeatable-content').effect('highlight', {}, 1E3)
		},
		attachButtons: function(a) {
			a.children().wrapAll($("<div>").addClass("repeatable-content"));
			$("<span>").addClass("sort").attr("title", this.options.msgSortElement).appendTo(a);
			$("<span>").addClass("delete").attr("title", this.options.msgDeleteElement).appendTo(a)
		}
	});
	$.fn[a.prototype.name] = function () {
		var e = arguments,
			f = e[0] ? e[0] : null;
		return this.each(function () {
			var d = $(this);
			if (a.prototype[f] && d.data(a.prototype.name) && f != "initialize") d.data(a.prototype.name)[f].apply(d.data(a.prototype.name), Array.prototype.slice.call(e, 1));
			else if (!f || $.isPlainObject(f)) {
				var c = new a;
				a.prototype.initialize && c.initialize.apply(c, $.merge([d], e));
				d.data(a.prototype.name, c)
			} else $.error("Method " + f + " does not exist on jQuery." + a.name)
		})
	}
})(jQuery);