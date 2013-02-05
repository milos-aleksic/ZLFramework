/* Copyright (C) ZOOlanders.com - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only */
(function ($) {

	/** ADD NEW WIDGET FOR BUTTON BECAUSE BOOTSTRAP IN 3.0 OVERRIDE $.button of jquery ui **/
	var lastActive, startXPos, startYPos, clickDragged,
	baseClasses = "ui-button ui-widget ui-state-default ui-corner-all",
	stateClasses = "ui-state-hover ui-state-active ",
	typeClasses = "ui-button-icons-only ui-button-icon-only ui-button-text-icons ui-button-text-icon-primary ui-button-text-icon-secondary ui-button-text-only",
	formResetHandler = function() {
		var buttons = $( this ).find( ":ui-button" );
		setTimeout(function() {
			buttons.button( "refresh" );
		}, 1 );
	},
	radioGroup = function( radio ) {
		var name = radio.name,
			form = radio.form,
			radios = $( [] );
		if ( name ) {
			if ( form ) {
				radios = $( form ).find( "[name='" + name + "']" );
			} else {
				radios = $( "[name='" + name + "']", radio.ownerDocument )
					.filter(function() {
						return !this.form;
					});
			}
		}
		return radios;
	};

	/** ZL BUTTON WIDGET **/
	$.widget( "ui.buttonzl", {
		version: "1.9.1",
		defaultElement: "<button>",
		options: {
			disabled: null,
			text: true,
			label: null,
			icons: {
				primary: null,
				secondary: null
			}
		},
		_create: function() {
			this.element.closest( "form" )
				.unbind( "reset" + this.eventNamespace )
				.bind( "reset" + this.eventNamespace, formResetHandler );

			if ( typeof this.options.disabled !== "boolean" ) {
				this.options.disabled = !!this.element.prop( "disabled" );
			} else {
				this.element.prop( "disabled", this.options.disabled );
			}

			this._determineButtonType();
			this.hasTitle = !!this.buttonElement.attr( "title" );

			var that = this,
				options = this.options,
				toggleButton = this.type === "checkbox" || this.type === "radio",
				hoverClass = "ui-state-hover" + ( !toggleButton ? " ui-state-active" : "" ),
				focusClass = "ui-state-focus";

			if ( options.label === null ) {
				options.label = (this.type === "input" ? this.buttonElement.val() : this.buttonElement.html());
			}

			this.buttonElement
				.addClass( baseClasses )
				.attr( "role", "button" )
				.bind( "mouseenter" + this.eventNamespace, function() {
					if ( options.disabled ) {
						return;
					}
					$( this ).addClass( "ui-state-hover" );
					if ( this === lastActive ) {
						$( this ).addClass( "ui-state-active" );
					}
				})
				.bind( "mouseleave" + this.eventNamespace, function() {
					if ( options.disabled ) {
						return;
					}
					$( this ).removeClass( hoverClass );
				})
				.bind( "click" + this.eventNamespace, function( event ) {
					if ( options.disabled ) {
						event.preventDefault();
						event.stopImmediatePropagation();
					}
				});

			this.element
				.bind( "focus" + this.eventNamespace, function() {
					// no need to check disabled, focus won't be triggered anyway
					that.buttonElement.addClass( focusClass );
				})
				.bind( "blur" + this.eventNamespace, function() {
					that.buttonElement.removeClass( focusClass );
				});

			if ( toggleButton ) {
				this.element.bind( "change" + this.eventNamespace, function() {
					if ( clickDragged ) {
						return;
					}
					that.refresh();
				});
				// if mouse moves between mousedown and mouseup (drag) set clickDragged flag
				// prevents issue where button state changes but checkbox/radio checked state
				// does not in Firefox (see ticket #6970)
				this.buttonElement
					.bind( "mousedown" + this.eventNamespace, function( event ) {
						if ( options.disabled ) {
							return;
						}
						clickDragged = false;
						startXPos = event.pageX;
						startYPos = event.pageY;
					})
					.bind( "mouseup" + this.eventNamespace, function( event ) {
						if ( options.disabled ) {
							return;
						}
						if ( startXPos !== event.pageX || startYPos !== event.pageY ) {
							clickDragged = true;
						}
				});
			}

			if ( this.type === "checkbox" ) {
				this.buttonElement.bind( "click" + this.eventNamespace, function() {
					if ( options.disabled || clickDragged ) {
						return false;
					}
					$( this ).toggleClass( "ui-state-active" );
					that.buttonElement.attr( "aria-pressed", that.element[0].checked );
				});
			} else if ( this.type === "radio" ) {
				this.buttonElement.bind( "click" + this.eventNamespace, function() {
					if ( options.disabled || clickDragged ) {
						return false;
					}
					$( this ).addClass( "ui-state-active" );
					that.buttonElement.attr( "aria-pressed", "true" );

					var radio = that.element[ 0 ];
					radioGroup( radio )
						.not( radio )
						.map(function() {
							return $( this ).button( "widget" )[ 0 ];
						})
						.removeClass( "ui-state-active" )
						.attr( "aria-pressed", "false" );
				});
			} else {
				this.buttonElement
					.bind( "mousedown" + this.eventNamespace, function() {
						if ( options.disabled ) {
							return false;
						}
						$( this ).addClass( "ui-state-active" );
						lastActive = this;
						that.document.one( "mouseup", function() {
							lastActive = null;
						});
					})
					.bind( "mouseup" + this.eventNamespace, function() {
						if ( options.disabled ) {
							return false;
						}
						$( this ).removeClass( "ui-state-active" );
					})
					.bind( "keydown" + this.eventNamespace, function(event) {
						if ( options.disabled ) {
							return false;
						}
						if ( event.keyCode === $.ui.keyCode.SPACE || event.keyCode === $.ui.keyCode.ENTER ) {
							$( this ).addClass( "ui-state-active" );
						}
					})
					.bind( "keyup" + this.eventNamespace, function() {
						$( this ).removeClass( "ui-state-active" );
					});

				if ( this.buttonElement.is("a") ) {
					this.buttonElement.keyup(function(event) {
						if ( event.keyCode === $.ui.keyCode.SPACE ) {
							// TODO pass through original event correctly (just as 2nd argument doesn't work)
							$( this ).click();
						}
					});
				}
			}

			// TODO: pull out $.Widget's handling for the disabled option into
			// $.Widget.prototype._setOptionDisabled so it's easy to proxy and can
			// be overridden by individual plugins
			this._setOption( "disabled", options.disabled );
			this._resetButton();
		},

		_determineButtonType: function() {
			var ancestor, labelSelector, checked;

			if ( this.element.is("[type=checkbox]") ) {
				this.type = "checkbox";
			} else if ( this.element.is("[type=radio]") ) {
				this.type = "radio";
			} else if ( this.element.is("input") ) {
				this.type = "input";
			} else {
				this.type = "button";
			}

			if ( this.type === "checkbox" || this.type === "radio" ) {
				// we don't search against the document in case the element
				// is disconnected from the DOM
				ancestor = this.element.parents().last();
				labelSelector = "label[for='" + this.element.attr("id") + "']";
				this.buttonElement = ancestor.find( labelSelector );
				if ( !this.buttonElement.length ) {
					ancestor = ancestor.length ? ancestor.siblings() : this.element.siblings();
					this.buttonElement = ancestor.filter( labelSelector );
					if ( !this.buttonElement.length ) {
						this.buttonElement = ancestor.find( labelSelector );
					}
				}
				this.element.addClass( "ui-helper-hidden-accessible" );

				checked = this.element.is( ":checked" );
				if ( checked ) {
					this.buttonElement.addClass( "ui-state-active" );
				}
				this.buttonElement.prop( "aria-pressed", checked );
			} else {
				this.buttonElement = this.element;
			}
		},

		widget: function() {
			return this.buttonElement;
		},

		_destroy: function() {
			this.element
				.removeClass( "ui-helper-hidden-accessible" );
			this.buttonElement
				.removeClass( baseClasses + " " + stateClasses + " " + typeClasses )
				.removeAttr( "role" )
				.removeAttr( "aria-pressed" )
				.html( this.buttonElement.find(".ui-button-text").html() );

			if ( !this.hasTitle ) {
				this.buttonElement.removeAttr( "title" );
			}
		},

		_setOption: function( key, value ) {
			this._super( key, value );
			if ( key === "disabled" ) {
				if ( value ) {
					this.element.prop( "disabled", true );
				} else {
					this.element.prop( "disabled", false );
				}
				return;
			}
			this._resetButton();
		},

		refresh: function() {
			var isDisabled = this.element.is( ":disabled" ) || this.element.hasClass( "ui-button-disabled" );
			if ( isDisabled !== this.options.disabled ) {
				this._setOption( "disabled", isDisabled );
			}
			if ( this.type === "radio" ) {
				radioGroup( this.element[0] ).each(function() {
					if ( $( this ).is( ":checked" ) ) {
						$( this ).button( "widget" )
							.addClass( "ui-state-active" )
							.attr( "aria-pressed", "true" );
					} else {
						$( this ).button( "widget" )
							.removeClass( "ui-state-active" )
							.attr( "aria-pressed", "false" );
					}
				});
			} else if ( this.type === "checkbox" ) {
				if ( this.element.is( ":checked" ) ) {
					this.buttonElement
						.addClass( "ui-state-active" )
						.attr( "aria-pressed", "true" );
				} else {
					this.buttonElement
						.removeClass( "ui-state-active" )
						.attr( "aria-pressed", "false" );
				}
			}
		},

		_resetButton: function() {
			if ( this.type === "input" ) {
				if ( this.options.label ) {
					this.element.val( this.options.label );
				}
				return;
			}
			var buttonElement = this.buttonElement.removeClass( typeClasses ),
				buttonText = $( "<span></span>", this.document[0] )
					.addClass( "ui-button-text" )
					.html( this.options.label )
					.appendTo( buttonElement.empty() )
					.text(),
				icons = this.options.icons,
				multipleIcons = icons.primary && icons.secondary,
				buttonClasses = [];

			if ( icons.primary || icons.secondary ) {
				if ( this.options.text ) {
					buttonClasses.push( "ui-button-text-icon" + ( multipleIcons ? "s" : ( icons.primary ? "-primary" : "-secondary" ) ) );
				}

				if ( icons.primary ) {
					buttonElement.prepend( "<span class='ui-button-icon-primary ui-icon " + icons.primary + "'></span>" );
				}

				if ( icons.secondary ) {
					buttonElement.append( "<span class='ui-button-icon-secondary ui-icon " + icons.secondary + "'></span>" );
				}

				if ( !this.options.text ) {
					buttonClasses.push( multipleIcons ? "ui-button-icons-only" : "ui-button-icon-only" );

					if ( !this.hasTitle ) {
						buttonElement.attr( "title", $.trim( buttonText ) );
					}
				}
			} else {
				buttonClasses.push( "ui-button-text-only" );
			}
			buttonElement.addClass( buttonClasses.join( " " ) );
		}
	});



	/** REAL PLUPLOAD CODE **/

    var b = function () {};
    $.extend(b.prototype, {
        name: 'Plupload',
        options: {
			url: null,
			title: null,
			extensions: null,
			path: null,
			fileMode: 'files',
			max_file_size: '1024kb',
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
					max_file_size : this.options.max_file_size,
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

			/** BOOTSTRAP IN 3.0 OVERRIDE $.button of jquery ui **/
			var browse_button = $('.plupload_add', h);
			var start_button = $('.plupload_start', h);
			var stop_button = $('.plupload_stop', h);

			browse_button.buttonzl({
				icons: { primary: 'ui-icon-circle-plus' }
			});
			
			start_button.buttonzl({
				icons: { primary: 'ui-icon-circle-arrow-e' },
				disabled: true
			});
			
			stop_button.buttonzl({
				icons: { primary: 'ui-icon-circle-close' }
			});

			var up = h.data('plupload').uploader;
			up.bind('QueueChanged', function() {
				if (up.files.length === (up.total.uploaded + up.total.failed)) {
					start_button.buttonzl('disable');
				} else {
					start_button.buttonzl('enable');
				}	
			});

			// Replace Max File Size in Error Message
			var MaxFileSize = this.options.max_file_size;
			up.bind('Error', function(up, err){
				if (err.code=='-600') {
					var MsgBox 	= $('.plupload_message'),
						Msg 	= MsgBox.html().replace("max file size: 1024", "max file size: " + MaxFileSize);
					MsgBox.html(MsgBox.html().replace("max file size: 1024", "max file size: " +  MaxFileSize))

				}
				up.refresh(); // Reposition Flash/Silverlight
		    });


			start_button.click(function(e) {
				if (!$(this).buttonzl('option', 'disabled')) {
					up.start();
				}
				e.preventDefault();
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