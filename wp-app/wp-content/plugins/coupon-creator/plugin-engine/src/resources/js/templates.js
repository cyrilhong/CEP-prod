jQuery( function ( $ ) {

	$( document ).on( 'change', '.pngx-template-chooser select, .pngx-variety-select', function ( e ) {

		e.preventDefault();

		var $option = $( this ).find( 'option:selected' ).val();

		var $ajax_field = $( this ).data( 'toggleAjax_field' );
		var $ajax_field_id = $( this ).data( 'toggleAjax_field_id' );
		var $ajax_action = $( this ).data( 'toggleAjax_action' );

		if ( !$ajax_field && !$ajax_field_id && !$ajax_action ) {
			return;
		}

		$( $ajax_field ).html( '<div class="pngx-loading-svg"></div>' );

		$.ajax( {
			url: pngx_admin_ajax.ajaxurl,
			type: 'post',
			cache: false,
			dataType: 'json',
			data: {
				nonce: pngx_admin_ajax.nonce,
				post_id: pngx_admin_ajax.post_id,
				field: $ajax_field_id,
				option: $option,
				action: $ajax_action
			},
			success: function ( results ) {

				if ( results.success ) {

					$( $ajax_field ).html( JSON.parse( results.data ) );

					// Init Visual Editors
					pngx_admin_fields_init.visual_editor();

					// Init Image Fields
					var image_upload = $( $ajax_field + ' .pngx-upload-image' );
					var selector_img;
					for ( i = 0; i < image_upload.length; i++ ) {
						selector_img = $( image_upload[i] ).attr( 'id' );
						new PNGX__Media( $, selector_img );
					}

					// Init Color Pickers
					$( $ajax_field + ' .pngx-color-picker' ).wpColorPicker();

					//Init Icon Pickers
					$( $ajax_field + ' .pngx-icon-picker' ).iconpicker();

				} else {

					$( $ajax_field ).html( '<h1>' + results.data + '</h1>' );

				}
			}
		} );

	} );

} );