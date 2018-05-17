<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


/**
 * License Methods
 *
 */
class Pngx__Admin__EDD_License {

	protected $update_url  = '';
	protected $options_id  = Pngx__Main::OPTIONS_ID;
	protected $license     = '';
	protected $plugin_name = '';

	public function __construct( $shop_url, $options_id, $license, $plugin_name = null ) {

		$this->update_url = trailingslashit( $shop_url );

		$this->options_id = $options_id;

		$this->license = $license;

		$this->plugin_name = $plugin_name;

		//Ajax Save License
		add_action( 'wp_ajax_pngx_license_update', array( $this, 'license_update' ) );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

	}

	/**
	 * Get the update API endpoint url
	 *
	 * @return string
	 */
	public function get_update_url() {
		return apply_filters( 'pngx_update_url', $this->update_url );
	}

	/*
	* Register and Enqueue Style and Scripts on Options Screens
	*
	*/
	public function activate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST[ 'pngx_license_activate_' . $this->license ] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'pngx_license_nonce_' . $this->license, 'pngx_license_nonce_' . $this->license ) ) {
				return false; // get out if we didn't click the Activate button
			}

			if ( $_POST[ 'pngx_license_key_' . $this->license ] != $this->license ) {
				return false; //not this plugins license
			}

			//Set WordPress Option Name
			$license_option_name = esc_attr( $_POST[ 'pngx_license_key_' . $this->license ] );

			// retrieve the license from the database
			$license_info = get_option( $license_option_name );

			//Check if the License has changed and deactivate
			if ( $_POST[ $this->options_id ][ $license_option_name ] != $license_info['key'] ) {

				$license_info['key'] = esc_attr( trim( $_POST[ $this->options_id ][ $license_option_name ] ) );

				delete_option( $license_option_name );

				update_option( $license_option_name, $license_info );

			}

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => esc_attr( trim( $license_info['key'] ) ),
				'item_name'  => urlencode( esc_attr( $_POST[ 'pngx_license_name_' . $this->license ] ) ), // the name of our product in EDD
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, self::get_update_url() ) ), array(
				'timeout'   => 15,
				'sslverify' => false,
			) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			//Remove Current Expiration
			$license_info['status'] = 'nostatus';

			//Get Status of Key
			$license_info['status'] = esc_attr( $license_data->license );

			//Remove Current Expiration
			unset( $license_info['expires'] );

			//Set Expiration Date  for This License
			$license_info['expires'] = esc_attr( $license_data->expires );

			//if Expired Add that to the option.
			if ( isset( $license_data->error ) && 'expired' == $license_data->error ) {
				$license_info['expired'] = esc_attr( $license_data->error );
			}

			//if Expired Add that to the option.
			if ( isset( $license_data->error ) && 'missing' == $license_data->error ) {
				unset( $license_info['expires'] );
				unset( $license_info['expired'] );
				$license_info['status'] = esc_attr( $license_data->error );
			}

			//Update License Object
			update_option( $license_option_name, $license_info );

		}

		return true;
	}

	/*
	* Deactivate a license key.
	*
	*/
	public function deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST[ 'pngx_license_deactivate_' . $this->license ] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'pngx_license_nonce_' . $this->license, 'pngx_license_nonce_' . $this->license ) ) {
				return false; // get out if we didn't click the Activate button
			}

			if ( $_POST[ 'pngx_license_key_' . $this->license ] != $this->license ) {
				return false; //not this plugins license
			}

			$license_option_name = esc_attr( $_POST[ 'pngx_license_key_' . $this->license ] );

			// retrieve the license from the database
			$license_info = get_option( $license_option_name );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => esc_attr( trim( $license_info['key'] ) ),
				'item_name'  => urlencode( esc_attr( $_POST[ 'pngx_license_name_' . $this->license ] ) ),
				// the name of our product in EDD
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, self::get_update_url() ) ), array(
				'timeout'   => 15,
				'sslverify' => false,
			) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				unset( $license_info['status'] );
				unset( $license_info['expires'] );

				//Update License Object
				update_option( $license_option_name, $license_info );

				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( 'deactivated' == $license_data->license || 'failed' == $license_data->license ) {

				unset( $license_info['status'] );
				unset( $license_info['expires'] );

				//Update License Object
				update_option( $license_option_name, $license_info );
			}

		}

		return true;
	}


	/**
	 * Update License by Ajax Call
	 */
	public function license_update() {

		// check nonce
		if ( ! wp_verify_nonce( $_POST['pngx_license_nonce'], 'pngx_license_updates' ) ) {
			wp_send_json_error( array( 'message' => __( 'Incorrect Permissions!', 'plugin-engine' ) ) );
		}

		if ( ! isset( $_POST['license_inputs'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No Data!', 'plugin-engine' ) ) );
		}

		$license_fields = wp_parse_args( $_POST['license_inputs'] );

		if ( ! $license_fields['pngx_license_key'] || ! $license_fields['pngx_license_name'] || ! $license_fields['pngx_license_action'] || ! $license_fields['pngx_shop_url'] ) {
			wp_send_json_error( array( 'message' => __( 'Missing Required Fields', 'plugin-engine' ) ) );
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Incorrect Capabilities!', 'plugin-engine' ) ) );
		}

		// update local license key
		$license_info = $this->update_local_license( $license_fields );

		// Update status locally on deactivate
		// Erroring on caution to do it here if for some reason there is a
		// Server error at least it will enable plugin deactivation
		if ( 'deactivate_license' == $license_fields['pngx_license_action'] ) {
			$this->deactivate_local( $license_fields, $license_info );
		}

		// decode the license data
		$license_data = $this->get_license_status( $license_fields, $license_info );

		//If not success get the correct error message
		if ( false === $license_data->success ) {

			$this->invalid( $license_data, $license_fields );

		}

		// Deactived License
		if ( 'deactivated' == $license_data->license || 'failed' == $license_data->license ) {

			$this->deactivate();

		}

		//Activated License
		if ( 'valid' === $license_data->license ) {

			$this->activate( $license_fields, $license_data, $license_info );
		}
	}

	/*
	* Update Local License
	*
	*/
	public function update_local_license( $license_fields ) {

		//Send Input to Sanitize Class, will return sanitized input or no input if no sanitization method
		$sanitize = new Pngx__Sanitize( 'license', $license_fields[ $license_fields['pngx_license_key'] ], array() );

		$license_info = array();

		//License Key
		$license_info['key'] = $sanitize->result;

		//Get Existing Option
		$existing_license = get_option( $license_fields['pngx_license_key'] );

		if ( ! $existing_license['key'] ) {

			update_option( $license_fields['pngx_license_key'], $license_info );

		} elseif ( $existing_license['key'] && $existing_license['key'] != $license_info['key'] ) {

			delete_option( $license_fields['pngx_license_key'] );

			update_option( $license_fields['pngx_license_key'], $license_info );

		}

		return $license_info;
	}

	/*
	* License Status
	*
	*/
	public function get_license_status( $license_fields, $license_info ) {

		// data to send in our API request
		$api_params = array(
			'edd_action' => esc_html( $license_fields['pngx_license_action'] ),
			'license'    => esc_attr( trim( $license_info['key'] ) ),
			'item_name'  => urlencode( esc_attr( $license_fields['pngx_license_name'] ) ), // the name of our product in EDD
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $license_fields['pngx_shop_url'] ) ), array(
			'timeout'   => 15,
			'sslverify' => false,
		) );

		$body_response = json_decode( wp_remote_retrieve_body( $response ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || ! $body_response || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$data = array(
				'message'        => $message,
				'license_status' => esc_html( __( 'License status has not changed with the license server.', 'plugin-engine' ) ),
				'action'         => $license_fields['pngx_license_action'],
			);

			// On Error and Deactivating make sure to deactivate to prevent lock out
			if ( 'deactivate_license' == $license_fields['pngx_license_action'] ) {
				$this->deactivate( $data );
			}

			wp_send_json_error( $data );

		}

		// decode the license data
		return json_decode( wp_remote_retrieve_body( $response ) );

	}

	/*
	* Activate
	*
	*/
	public function activate( $license_fields, $license_data, $license_info ) {

		$expiration_msg = $expiration_date = '';

		//Remove Current Expiration
		$license_info['status'] = 'nostatus';

		//Get Status of Key
		$license_info['status'] = esc_html( $license_data->license );

		//Remove Current Expiration
		unset( $license_info['expires'] );

		//Set Expiration Date  for This License
		$license_info['expires'] = esc_html( $license_data->expires );

		//if Expired Add that to the option.
		if ( isset( $license_data->error ) && 'expired' === $license_data->error ) {
			$license_info['expired'] = esc_html( $license_data->error );
		}

		//if Expired Add that to the option.
		if ( isset( $license_data->error ) && 'missing' === $license_data->error ) {
			unset( $license_info['expires'] );
			unset( $license_info['expired'] );
			$license_info['status'] = esc_html( $license_data->error );
		}

		//Update License Object
		update_option( $license_fields['pngx_license_key'], $license_info );

		if ( isset( $license_info['expires'] ) ) { // Only Display Expiration if Date
			$expiration_date = strtotime( $license_info['expires'] );
			$expiration_date = date( get_option( 'date_format' ), $expiration_date );
			$expiration_msg  = sprintf( __( ' and Expires on %s', 'plugin-engine' ), esc_attr( $expiration_date ) );
		}

		$data = array(
			'message'        => esc_html__( 'License Saved and Valid', 'plugin-engine' ),
			'status'         => $license_info['status'],
			'expires'        => $expiration_date,
			'license_status' => esc_html( __( 'License is Active', 'plugin-engine' ) . $expiration_msg ),
			'button'         => 'Deactivate License',
			'action'         => 'deactivate_license',
		);

		wp_send_json_success( $data );

	}

	/*
	* Deactivate Local
	*
	*/
	public function deactivate_local( $license_fields, $license_info ) {

		unset( $license_info['status'] );
		unset( $license_info['expires'] );

		//Update License Object
		update_option( $license_fields['pngx_license_key'], $license_info );

		return;
	}

	/*
	* Deactivate
	*
	*/
	public function deactivate( $error = array() ) {

		$data = array(
			'message'        => esc_html__( 'License Deactivated', 'plugin-engine' ),
			'status'         => '',
			'license_status' => esc_html( __( 'Click Activate License to enable automatic updates.', 'plugin-engine' ) ),
			'button'         => 'Activate License',
			'action'         => 'activate_license',
		);

		if ( isset( $error['message'] ) && isset( $error['license_status'] ) ) {
			$data['message']        = esc_html__( 'License Deactivated locally, it could not be deactivated with the server due to:', 'plugin-engine' ) . ' ' . $error['message'];
			$data['license_status'] = $error['license_status'];
			$data['remote_error']   = true;
		}

		wp_send_json_success( $data );

	}

	/*
	* Invalid
	*
	*/
	public function invalid( $license_data, $license_fields ) {

		$message = __( 'An error occurred, please try again.', 'plugin-engine' );

		if ( isset( $license_data->error ) ) {

			switch ( $license_data->error ) {

				case 'expired' :

					$expiration_date = strtotime( $license_data->expires );
					$expiration_date = date( get_option( 'date_format' ), $expiration_date );
					$message         = sprintf( __( 'License Expired on %s', 'plugin-engine' ), esc_attr( $expiration_date ) );
					break;

				case 'revoked' :

					$message = __( 'Your license key has been disabled.', 'plugin-engine' );
					break;

				case 'missing' :

					$message = __( 'Invalid license.', 'plugin-engine' );
					break;

				case 'invalid' :

					$message = __( 'Your license is invalid.', 'plugin-engine' );
					break;

				case 'site_inactive' :

					$message = __( 'Your license is not active for this URL.', 'plugin-engine' );
					break;

				case 'item_name_mismatch' :

					$message = sprintf( __( 'This appears to be for another plugin and invalid license key for %s.', 'plugin-engine' ), $license_fields['pngx_license_name'] );
					break;

				case 'no_activations_left':

					$message = __( 'Your license key has reached its activation limit.', 'plugin-engine' );
					break;

				case 'license_not_activable':

					$message = __( 'Your license cannot be activated or deactivated, please confirm in your accoint it is active.', 'plugin-engine' );
					break;
			}

		}

		$status = 'expired' === $license_data->error ? $license_data->error : '';

		$data = array(
			'message'        => esc_html__( 'License is Not Active', 'plugin-engine' ),
			'status'         => $status,
			'license_status' => esc_html( $message ),
			'button'         => 'Activate License',
			'action'         => 'activate_license',
		);

		wp_send_json_error( $data );

	}

	/**
	 * Check Plugin Engine Plugin License and Update Status and Expiration Date
	 *
	 * @param $_transient_data
	 *
	 * @return mixed
	 */
	public function check_update( $_transient_data ) {

		if ( $this->plugin_name && false === ( $license = get_transient( 'pngx_plugin_check_' . $this->license ) ) ) {

			$license_info = get_option( $this->license );

			if ( empty( $license_info ) || empty( $license_info['key'] ) || empty( $license_info['status'] ) || empty( $license_info['expires'] ) ) {
				return $_transient_data;
			}


			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => esc_attr( trim( $license_info['key'] ) ),
				'item_name'  => urlencode( esc_attr( $this->plugin_name ) ), // the name of our product in EDD
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->update_url ) ), array(
				'timeout'   => 15,
				'sslverify' => false,
			) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return $_transient_data;
			}

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( empty( $license_data->license ) || empty( $license_data->expires ) ) {
				return $_transient_data;
			}
			//Get Status of Key
			$license_info['status'] = esc_html( $license_data->license );

			//Remove Current Expiration
			unset( $license_info['expires'] );

			//Set Expiration Date  for This License
			$license_info['expires'] = esc_attr( $license_data->expires );

			//if Expired Add that to the option.
			if ( 'expired' == $license_data->license ) {
				$license_info['expired'] = esc_attr( $license_data->license );
			}

			//if Expired Add that to the option.
			if ( 'missing' == $license_data->license ) {
				unset( $license_info['expires'] );
				unset( $license_info['expired'] );
				$license_info['status'] = esc_attr( $license_data->license );
			}

			//Update License Object
			update_option( $this->license, $license_info );

			set_transient( 'pngx_plugin_check_' . $this->license, $license_data, 24 * HOUR_IN_SECONDS );

		}


		return $_transient_data;
	}

}