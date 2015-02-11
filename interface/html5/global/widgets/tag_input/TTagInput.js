(function( $ ) {

	$.fn.TTagInput = function( options ) {
		var opts = $.extend( {}, $.fn.TTagInput.defaults, options );
		var $this = this;
		var field;

		var add_tag_input = null;

		var tag_span_dic = [];

		var close_btn = null;

		var mass_edit_mode = false;

		var check_box = null;

		var enabled = true;

		var error_string = '';

		var error_tip_box;

		var api_tag;

		var object_type_id = -1;

		this.showErrorTip = function( sec ) {

			if ( !Global.isSet( sec ) ) {
				sec = 2
			}

			if ( !error_tip_box ) {
				error_tip_box = Global.loadWidgetByName( WidgetNamesDic.ERROR_TOOLTIP );
				error_tip_box = error_tip_box.ErrorTipBox()
			}
			error_tip_box.show( this, error_string, sec )
		};

		this.hideErrorTip = function() {

			if ( Global.isSet( error_tip_box ) ) {
				error_tip_box.remove();
			}

		};

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.addClass( 't-tag-input-readonly' );
				add_tag_input.addClass( 'add-tag-input-readonly' );
				add_tag_input.attr( 'disabled', true );
			} else {
				$this.removeClass( 't-tag-input-readonly' );
				add_tag_input.removeClass( 'add-tag-input-readonly' );
				add_tag_input.removeAttr( 'disabled' );
			}
//			  $this.children().attr( 'disabled', 'true' );

		};

		this.setCheckBox = function( val ) {
			check_box.attr( 'checked', val )
		};

		this.isChecked = function() {
			if ( check_box ) {
				if ( check_box.attr( 'checked' ) ) {
					return true
				}
			}
			return false;
		};

		this.setMassEditMode = function( val ) {
			mass_edit_mode = val;

			if ( mass_edit_mode ) {
				check_box = $( " <input type='checkbox' class='ta-mass-edit-checkbox' />" );
				check_box.insertBefore( $( this ) );

				check_box.change( function() {
					$this.trigger( 'formItemChange', [$this] );
				} );

			} else {
				if ( check_box ) {
					check_box.remove();
					check_box = null;
				}
			}

		};

		this.clearErrorStyle = function() {

		};

		this.getField = function() {
			return field;
		};

		Global.addCss( 'global/widgets/tag_input/TTagInput.css' );

		this.getValue = function() {

			var value = '';

			for ( var key in tag_span_dic ) {
				value = value + tag_span_dic[key].find( '.tag-span' ).text() + ',';
			}

			value = value.substring( 0, value.length - 1 );

			return value;
		};

		this.setValue = function( val ) {

			$this.buildTagSpan( val );
		};

		this.buildTagSpan = function( val ) {

			if ( !val ) {
				val = '';
			}

			for ( var key in tag_span_dic ) {
				tag_span_dic[key].remove();
			}

			tag_span_dic = {};

			var tagArray = val.split( ',' );

			if ( tagArray && tagArray.length === 1 && tagArray[0] === '' ) {
				return;
			}

			$.each( tagArray, function( index, content ) {

				var delete_mode = false;
				if ( content.indexOf( '[search-deleted]' ) > 0 || content.indexOf( '-' ) === 0 ) {
					content = content.split( '[search-deleted]' )[0];
					delete_mode = true;
				}

				var tagSpanDiv = $( "<div class='tag-span-div'></div>" );
				var close_btn = $( "<span  class='close-btn'>X</span>" );
				var tagSpan = $( "<span class='tag-span'></span>" );

				if ( delete_mode ) {
					tagSpanDiv.addClass( 'removed' );
				}

				close_btn.attr( 'value', content );
				tagSpan.text( content );

				var doing_dblclick = false;
				close_btn.dblclick( function( e ) {
					doing_dblclick = true;
					if ( !enabled ) {
						return;
					}

					var value = $( this ).attr( 'value' );

					if ( Global.isSet( tag_span_dic[value] ) ) {
						tag_span_dic[value].remove()

					}

					delete tag_span_dic[value];

					if ( check_box ) {
						check_box.attr( 'checked', 'true' )
					}

					$this.trigger( 'formItemChange', [$this] );

					setTimeout( function() {
						doing_dblclick = false;
					}, 200 );

				} );

				close_btn.click( function( e ) {

					setTimeout( function() {
						if ( !doing_dblclick ) {
							doNext();
						}
					}, 200 );

					var $$this = this;

					function doNext() {
						if ( !enabled ) {
							return;
						}

						var value = $( $$this ).attr( 'value' );
						var current_div = tag_span_dic[value];
						var new_value = '';

						//Error: Unable to get property 'removeClass' of undefined or null reference in https://ondemand1.timetrex.com/interface/html5/global/widgets/tag_input/TTagInput.js?v=8.0.0-20150126-192326 line 214
						if ( !current_div ) {
							return;
						}

						if ( value.indexOf( '-' ) === 0 ) {
							new_value = value.substr( 1 );
							current_div.removeClass( 'removed' );
						} else {
							new_value = '-' + value;
							current_div.addClass( 'removed' );
						}

						tag_span_dic[value].find( '.tag-span' ).text( new_value );

						delete tag_span_dic[value];
						$( $$this ).attr( 'value', new_value );

						tag_span_dic[new_value] = current_div;

						if ( check_box ) {
							check_box.attr( 'checked', 'true' )
						}

						$this.trigger( 'formItemChange', [$this] );
					}

				} );

				tagSpanDiv.append( tagSpan );
				tagSpanDiv.append( close_btn );
				$( $this ).prepend( tagSpanDiv );

				tag_span_dic[close_btn.attr( 'value' )] = tagSpanDiv;

			} );
		}

		this.createTag = function( val ) {

			var tagSpanDiv = $( "<div class='tag-span-div new'></div>" );
			var close_btn = $( "<span class='close-btn'>X</span>" );
			var tagSpan = $( "<span class='tag-span'></span>" );

			close_btn.attr( 'value', val );
			tagSpan.text( val );

			var doing_dblclick = false;

			close_btn.dblclick( function( e ) {
				doing_dblclick = true;
				if ( !enabled ) {
					return;
				}

				var value = $( this ).attr( 'value' );

				if ( Global.isSet( tag_span_dic[value] ) ) {
					tag_span_dic[value].remove()

				}

				delete tag_span_dic[value];

				if ( check_box ) {
					check_box.attr( 'checked', 'true' )
				}

				$this.trigger( 'formItemChange', [$this] );

				setTimeout( function() {
					doing_dblclick = false;
				}, 200 );

			} );

			close_btn.click( function( e ) {

				setTimeout( function() {
					if ( !doing_dblclick ) {
						doNext();
					}
				}, 200 );

				var $$this = this;

				function doNext() {
					if ( !enabled ) {
						return;
					}

					var value = $( $$this ).attr( 'value' );
					var current_div = tag_span_dic[value];
					var new_value = '';

					if ( value.indexOf( '-' ) === 0 ) {
						new_value = value.substr( 1 );
						current_div.removeClass( 'removed' );
					} else {
						new_value = '-' + value;
						current_div.addClass( 'removed' );
					}

					tag_span_dic[value].find( '.tag-span' ).text( new_value );

					delete tag_span_dic[value];
					$( $$this ).attr( 'value', new_value );

					tag_span_dic[new_value] = current_div;

					$this.trigger( 'formItemChange', [$this] );
				}

			} );

			if ( Global.isSet( tag_span_dic[close_btn.attr( 'value' )] ) ) {
				return;
			}

			tagSpanDiv.append( tagSpan );
			tagSpanDiv.append( close_btn );
			tagSpanDiv.insertBefore( add_tag_input );

			tag_span_dic[close_btn.attr( 'value' )] = tagSpanDiv;
		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

			if ( o.object_type_id ) {
				object_type_id = o.object_type_id;
			}

			add_tag_input = $( "<input class='add-tag-input' />" );

			api_tag = new (APIFactory.getAPIClass( 'APICompanyGenericTag' ))();

			add_tag_input.autocomplete( {
				source: []
			} );

			$( this ).append( add_tag_input );

			$( this ).click( function() {
				add_tag_input.focus();
				if ( !enabled ) {
					if ( !check_box ) {
						if ( LocalCacheData.current_open_sub_controller &&
							LocalCacheData.current_open_sub_controller.edit_view &&
							LocalCacheData.current_open_sub_controller.is_viewing ) {
							error_string = Global.view_mode_message;
							$this.showErrorTip( 10 );
						} else if ( LocalCacheData.current_open_primary_controller &&
							LocalCacheData.current_open_primary_controller.edit_view &&
							LocalCacheData.current_open_primary_controller.is_viewing ) {
							error_string = Global.view_mode_message;
							$this.showErrorTip( 10 );
						}
					}
				}
			} );

			$( this ).contextmenu( function( e ) {
				e.preventDefault();
				e.stopImmediatePropagation();

			} );

			$( this ).mouseout( function() {
				$this.hideErrorTip();
			} );

			add_tag_input.bind( 'focusout', function() {
				if ( add_tag_input.val().length > 0 ) {

					if ( !enabled ) {
						return;
					}

					$this.createTag( add_tag_input.val() );
					add_tag_input.val( '' );

					if ( check_box ) {
						check_box.attr( 'checked', 'true' );
					}

					$this.trigger( 'formItemChange', [$this] );

				}
			} );

			add_tag_input.bind( 'keyup', function( e ) {

				if ( e.which === 40 || e.which === 38 ) {
					e.preventDefault();
					return false;
				}

				e.preventDefault();
				var args = {};
				args.filter_data = {};
				args.filter_data.object_type_id = [object_type_id];
				args.filter_data.name = add_tag_input.val();
				api_tag.getCompanyGenericTag( args, {
					onResult: function( result ) {

						var result_data = result.getResult();
						var final_result = [];

						for ( var i = 0; i < result_data.length; i++ ) {
							final_result.push( result_data[i].name );
						}

						add_tag_input.autocomplete( "option", "source", final_result );
						add_tag_input.autocomplete( 'search', args.filter_data.name );

					}
				} );

				return false;

			} );

			add_tag_input.bind( 'keydown', function( e ) {

				if ( e.which === 40 || e.which === 38 ) {
					e.preventDefault();
					return false;
				}

				if ( (e.which === 13 || e.which === 44 || e.which === 32 || e.which === 9 || e.which === 188) &&
					add_tag_input.val().length > 0 ) {

					e.preventDefault();

					if ( !enabled ) {
						return false;
					}

					$this.createTag( add_tag_input.val() );
					add_tag_input.val( '' );

					if ( check_box ) {
						check_box.attr( 'checked', 'true' );
					}
					add_tag_input.autocomplete( "option", "source", [] );
					$this.trigger( 'formItemChange', [$this] );

					return false;
				} else {
					add_tag_input.autocomplete( "option", "source", [] );
				}

			} );

		} );

		return this;

	};

	$.fn.TTagInput.defaults = {};

})( jQuery );