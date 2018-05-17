/*
 *	JavaScript Wordpress Editor Forked for Plugin Engine
 *	Author: 		Ante Primorac / Brian Jessee (Forked)
 *	Author URI: 	http://anteprimorac.from.hr
 *	Version: 		1.2
 *	License:
 *		Copyright (c) 2013 Ante Primorac
 *		Permission is hereby granted, free of charge, to any person obtaining a copy
 *		of this software and associated documentation files (the "Software"), to deal
 *		in the Software without restriction, including without limitation the rights
 *		to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *		copies of the Software, and to permit persons to whom the Software is
 *		furnished to do so, subject to the following conditions:
 *
 *		The above copyright notice and this permission notice shall be included in
 *		all copies or substantial portions of the Software.
 *
 *		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *		AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *		OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *		THE SOFTWARE.
 *	Usage:
 *		server side(WP):
 *			js_wp_editor( $settings );
 *		client side(jQuery):
 *			$('textarea').wp_editor( options );
 *
 *	Repo: https://github.com/anteprimorac/js-wp-editor
 */

(function ( $, window ) {

	$.fn.wp_editor = function ( options, uni_key, reduced ) {

		if ( !$( this ).is( 'textarea' ) ) {
			console.warn( 'Element must be a textarea' );
		}

		if ( typeof tinyMCEPreInit == 'undefined' || typeof QTags == 'undefined' || typeof pngx_editor_vars == 'undefined' ) {
			console.warn( 'js_wp_editor( $settings ); must be loaded' );
		}

		if ( !$( this ).is( 'textarea' ) || typeof tinyMCEPreInit == 'undefined' || typeof QTags == 'undefined' || typeof pngx_editor_vars == 'undefined' ) {
			return this;
		}

		// Set Variables
		var default_options = {};
		var pngx_options = get_defaults( pngx_editor_vars.includes_url, uni_key, pngx_editor_vars.visual_editor_buttons, reduced );

		id_regexp = new RegExp( uni_key, 'g' );

		if ( tinyMCEPreInit.mceInit[uni_key] ) {
			default_options.mceInit = tinyMCEPreInit.mceInit[uni_key];
		}

		pngx_options = $.extend( true, default_options, pngx_options );

		options = $.extend( true, pngx_options, options );

		return this.each( function () {

				if ( !$( this ).is( 'textarea' ) ) {

					console.warn( 'Element must be a textarea' );

				} else if ( 'false' === pngx_editor_vars.rich_editing ) {

					set_code_editor( uni_key );

				}
				if ( 'true' === pngx_editor_vars.rich_editing ) {

					var current_id = $( this ).attr( 'id' );

					$.each( options.mceInit, function ( key, value ) {
						if ( $.type( value ) == 'string' )
							options.mceInit[key] = value.replace( id_regexp, current_id );
					} );
					options.mode = options.mode == 'tmce' ? 'tmce' : 'html';

					//if tiny mce exists for id, remove it to reinit
					if ( typeof tinyMCEPreInit.mceInit[current_id] !== 'undefined' ) {
						tinyMCE.remove( tinymce.editors[current_id] );
					}

					tinyMCEPreInit.mceInit[current_id] = options.mceInit;

					$( this ).addClass( 'wp-editor-area' ).show();
					var self = this;
					if ( $( this ).closest( '.wp-editor-wrap' ).length ) {
						var parent_el = $( this ).closest( '.wp-editor-wrap' ).parent();
						$( this ).closest( '.wp-editor-wrap' ).before( $( this ).clone() );
						$( this ).closest( '.wp-editor-wrap' ).remove();
						self = parent_el.find( 'textarea[id="' + current_id + '"]' );
					}

					var wrap = $( '<div id="wp-' + current_id + '-wrap" class="wp-core-ui wp-editor-wrap ' + options.mode + '-active" />' ),
						editor_tools = $( '<div id="wp-' + current_id + '-editor-tools" class="wp-editor-tools hide-if-no-js" />' ),
						editor_tabs = $( '<div class="wp-editor-tabs" />' ),
						switch_editor_tmce = $( '<a id="' + current_id + '-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="' + current_id + '">Visual</a>' ),
						switch_editor_html = $( '<a id="' + current_id + '-html" class="wp-switch-editor switch-html" data-wp-editor-id="' + current_id + '">Text</a>' ),
						media_buttons = $( '<div id="wp-' + current_id + '-media-buttons" class="wp-media-buttons" />' ),
						insert_media_button = $( '<a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="' + current_id + '" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a>' ),
						editor_container = $( '<div id="wp-' + current_id + '-editor-container" class="wp-editor-container" />' ),
						content_css = /*Object.prototype.hasOwnProperty.call(tinyMCEPreInit.mceInit[current_id], 'content_css') ? tinyMCEPreInit.mceInit[current_id]['content_css'].split(',') :*/ false;

					insert_media_button.appendTo( media_buttons );
					media_buttons.appendTo( editor_tools );

					switch_editor_tmce.appendTo( editor_tabs );
					switch_editor_html.appendTo( editor_tabs );
					editor_tabs.appendTo( editor_tools );
					if ( !reduced ) {
						editor_tools.appendTo( wrap );
					}
					editor_container.appendTo( wrap );

					editor_container.append( $( self ).clone().addClass( 'wp-editor-area' ) );

					if ( content_css != false )
						$.each( content_css, function () {
							if ( !$( 'link[href="' + this + '"]' ).length )
								$( self ).before( '<link rel="stylesheet" type="text/css" href="' + this + '">' );
						} );

					$( self ).before( '<link rel="stylesheet" id="editor-buttons-css" href="' + pngx_editor_vars.includes_url + 'css/editor.css" type="text/css" media="all">' );

					$( self ).before( wrap );
					$( self ).remove();


					set_code_editor( uni_key );

					switchEditors.go( current_id, options.mode );

					$( wrap ).on( 'click', '.insert-media', function ( event ) {
						var elem = $( event.currentTarget ),
							editor = elem.data( 'editor' ),
							options = {
								frame: 'post',
								state: 'insert',
								title: wp.media.view.l10n.addMedia,
								multiple: true
							};

						event.preventDefault();

						elem.blur();

						if ( elem.hasClass( 'gallery' ) ) {
							options.state = 'gallery';
							options.title = wp.media.view.l10n.createGalleryTitle;
						}

						wp.media.editor.open( editor, options );
					} );
				}
			}
		);
	};

	function set_code_editor( uni_key ) {

		new QTags( uni_key );
		QTags._buttonsInit();

		var $html_buttons = $( '#' + uni_key ).data( 'toggleHtml_buttons' );

		if ( $html_buttons && pngx_editor_vars.html_editor_buttons ) {

			$html_buttons = $html_buttons.split( ',' );

			// Get Existing Buttons to Detect if to add or not to prevent duplicates
			var existing_buttons = [];
			if ( "undefined" != typeof edButtons ) {

				edButtons.forEach( function ( element ) {
					existing_buttons.push( element.id );
				} );

			}

			// Setup HTML Buttons and do not add if already added
			for ( i = 0; i < pngx_editor_vars.html_editor_buttons.length; i++ ) {

				if ( jQuery.inArray( pngx_editor_vars.html_editor_buttons[i]['id'], existing_buttons ) >= 0 ) {
					continue;
				}

				if ( jQuery.inArray( pngx_editor_vars.html_editor_buttons[i]['id'], $html_buttons ) >= 0 ) {

					QTags.addButton(
						pngx_editor_vars.html_editor_buttons[i]['id'],
						pngx_editor_vars.html_editor_buttons[i]['display'],
						pngx_editor_vars.html_editor_buttons[i]['arg1'],
						pngx_editor_vars.html_editor_buttons[i]['arg2'],
						pngx_editor_vars.html_editor_buttons[i]['access_key'],
						pngx_editor_vars.html_editor_buttons[i]['title'],
						pngx_editor_vars.html_editor_buttons[i]['priority'],
						uni_key
					);
				}
			}

		}

	}

	function get_toolbar_1( uni_key, reduced ) {

		var $tool_bar_1 = $( '#' + uni_key ).data( 'toggleToolbar_1' );
		if ( $tool_bar_1 ) {
			return $tool_bar_1;
		} else if ( reduced ) {
			return 'forecolor,bold,italic,underline,strikethrough,hr,charmap,alignleft,aligncenter,alignright,link,unlink,spellchecker,pastetext,removeformat';
		}
		return 'formatselect,forecolor,bold,italic,underline,strikethrough,hr,bullist,numlist,alignleft,aligncenter,alignright,link,unlink,wp_adv';

	}

	function get_toolbar_2( uni_key, reduced ) {

		var $tool_bar_2 = $( '#' + uni_key ).data( 'toggleToolbar_2' );
		if ( $tool_bar_2 ) {
			return $tool_bar_2;
		} else if ( reduced ) {
			return '';
		}
		return 'fontselect,fontsizeselect,blockquote,pastetext,removeformat,charmap,outdent,indent,undo,redo,spellchecker,fullscreen,wp_help';

	}

	function get_toolbar_3( uni_key ) {

		var $tool_bar_3 = $( '#' + uni_key ).data( 'toggleToolbar_3' );

		if ( $tool_bar_3 ) {
			return $tool_bar_3;
		}

		return '';

	}

	function get_defaults( resource_url, uni_key, editor_buttons, reduced ) {

		var $data = $( '#' + uni_key ).data();

		var $content_css = $data.toggleContent_css;

		var $wpautop_disable = $data.toggleWpautop_disable;

		return {
			'mode': 'tmce',
			'mceInit': {
				"theme": "modern",
				"skin": "lightgray",
				"language": "en",
				"fontsize_formats": "6px 8px 10px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 36px 40px",
				"formats": {
					"alignleft": [
						{
							"selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
							"styles": {"textAlign": "left"},
							"deep": false,
							"remove": "none"
						},
						{
							"selector": "img,table,dl.wp-caption",
							"classes": ["alignleft"],
							"deep": false,
							"remove": "none"
						}
					],
					"aligncenter": [
						{
							"selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
							"styles": {"textAlign": "center"},
							"deep": false,
							"remove": "none"
						},
						{
							"selector": "img,table,dl.wp-caption",
							"classes": ["aligncenter"],
							"deep": false,
							"remove": "none"
						}
					],
					"alignright": [
						{
							"selector": "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
							"styles": {"textAlign": "right"},
							"deep": false,
							"remove": "none"
						},
						{
							"selector": "img,table,dl.wp-caption",
							"classes": ["alignright"],
							"deep": false,
							"remove": "none"
						}
					],
					"strikethrough": {"inline": "del", "deep": true, "split": true}
				},
				"relative_urls": false,
				"remove_script_host": false,
				"convert_urls": false,
				"browser_spellcheck": true,
				"fix_list_elements": true,
				"entities": "38,amp,60,lt,62,gt",
				"entity_encoding": "raw",
				"keep_styles": false,
				"paste_webkit_styles": "font-weight font-style color",
				"preview_styles": "font-family font-size font-weight font-style text-decoration text-transform",
				"wpeditimage_disable_captions": false,
				"wpeditimage_html5_captions": false,
				"plugins": "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,image",
				"content_css": resource_url + "css/dashicons.css?ver=4.7," +
				resource_url + "js/mediaelement/mediaelementplayer.min.css?ver=4.7," +
				resource_url + "js/mediaelement/wp-mediaelement.css?ver=4.7," +
				resource_url + "js/tinymce/skins/wordpress/wp-content.css?ver=4.7, " +
				$content_css,
				"selector": "#" + uni_key,
				"resize": "vertical",
				"menubar": false,
				"wpautop": $wpautop_disable ? false : true,
				"indent": false,
				"toolbar1": get_toolbar_1( uni_key, reduced ),
				"toolbar2": get_toolbar_2( uni_key, reduced ),
				"toolbar3": get_toolbar_3( uni_key ),
				"toolbar4": '',
				"tabfocus_elements": ":prev,:next",
				"body_class": uni_key,
				setup: function ( editor ) {

					// Setup Buttons
					for ( i = 0; i < editor_buttons.length; i++ ) {
						// Simple Wrap Button
						if ( 'wrap' === editor_buttons[i]['type'] ) {
							editor.addButton( editor_buttons[i]['addButton'], {
								title: editor_buttons[i]['title'],
								text: editor_buttons[i]['text'],
								icon: editor_buttons[i]['icon'],
								onclick: (function ( i, buttons ) {
									return function () {
										var selected_text = editor.selection.getContent();
										var return_text = '';
										return_text = buttons['wrapopentag'] + selected_text + buttons['wrapclosetag'];
										editor.execCommand( 'mceInsertContent', 0, return_text );
									};
								})( i, editor_buttons[i] )
							} );
						}
					}
				}
			}
		};

	}

})( jQuery, window );