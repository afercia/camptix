<?php

/**
 * Payment Method Class for CampTix
 *
 * @since 1.2
 */

class CampTix_Payment_Method extends CampTix_Addon {

	public $id = false;
	public $name = false;
	public $description = false;
	public $supported_currencies = false;
	public $supported_features = array(
		'refund-single' => false,
		'refund-all' => false,
	);

	/**
	 * Constructor
	 */
	function __construct() {
		/** @var $camptix CampTix_Plugin */
		global $camptix;

		parent::__construct();

		add_filter( 'camptix_available_payment_methods', array( $this, '_camptix_available_payment_methods' ) );
		add_filter( 'camptix_validate_options', array( $this, '_camptix_validate_options' ) );
		add_filter( 'camptix_get_payment_method_by_id', array( $this, '_camptix_get_payment_method_by_id' ), 10, 2 );

		if ( ! $this->id )
			die( 'id not specified in a payment method' );

		if ( ! $this->name )
			die( 'name not specified in a payment method' );

		if ( ! $this->description )
			die( 'description not specified in a payment method' );

		if ( ! is_array( $this->supported_currencies ) || count( $this->supported_currencies ) < 1 )
			die( 'supported currencies not specified in a payment method' );

		$this->camptix_options = $camptix->get_options();
	}

	function supports_currency( $currency ) {
		return in_array( $currency, $this->supported_currencies );
	}

	/**
	 * Check if the payment method supports the given feature
	 *
	 * @param string $feature
	 *
	 * @return bool
	 */
	function supports_feature( $feature ) {
		return array_key_exists( $feature, $this->supported_features ) ? $this->supported_features[ $feature ] : false;
	}

	/**
	 * Get the payment gateway object for the given ID
	 *
	 * @param CampTix_Payment_Method $payment_method
	 * @param string                 $id
	 *
	 * @return CampTix_Payment_Method
	 */
	function _camptix_get_payment_method_by_id( $payment_method, $id ) {
		if ( $this->id == $id ) {
			$payment_method = $this;
		}

		return $payment_method;
	}

	/**
	 * Render the section header markup on the Payment screen
	 */
	function _camptix_settings_section_callback() {
		echo '<p>' . $this->description . '</p>';
		printf( '<p>' . __( 'Supported currencies: <code>%s</code>.', 'camptix' ) . '</p>', implode( '</code>, <code>', $this->supported_currencies ) );
	}

	/**
	 * Render the markup for the Enabled button
	 *
	 * @param array $args
	 */
	function _camptix_settings_enabled_callback( $args = array() ) {
		if ( in_array( $this->camptix_options['currency'], $this->supported_currencies ) )
			return $this->field_yesno( $args );

			<p class="description">
				<?php printf(
					__( '%s is not supported by this payment method.', 'camptix' ),
					'<code>' . $this->camptix_options['currency'] . '</code>'
				); ?>
			</p>

			<?php
		}
	}

	/**
	 * Validate options if they were submitted for this payment method
	 *
	 * @param array $camptix_options
	 *
	 * @return array
	 */
	function _camptix_validate_options( $camptix_options ) {
		$post_key = "camptix_payment_options_{$this->id}";
		$option_key = "payment_options_{$this->id}";

		if ( ! isset( $_POST[ $post_key ] ) ) {
			return $camptix_options;
		}

		$input = $_POST[ $post_key ];
		$output = $this->validate_options( $input );
		$camptix_options[ $option_key ] = $output;

		return $camptix_options;
	}

	/**
	 * Validate new option values before saving
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	function validate_options( $input ) {
		return array();
	}

	function payment_checkout( $payment_token ) {
		die( __FUNCTION__ . ' not implemented' );
	}

	/**
	 * Handle the refund process
	 *
	 * @param string $payment_token
	 *
	 * @return int A payment status, e.g., PAYMENT_STATUS_CANCELLED, PAYMENT_STATUS_COMPLETED, etc
	 */
	function payment_refund( $payment_token ) {
		/** @var $camptix Camptix_Plugin  */
		global $camptix;

		$refund_data = array();
		$camptix->log( __FUNCTION__ . ' not implemented in payment module.', 0, null, 'refund' );

		return $this->payment_result( $payment_token, CampTix_Plugin::PAYMENT_STATUS_REFUND_FAILED, $refund_data );
	}

	/**
	 * Send a request for a refund to the payment gateway API
	 *
	 * @param string $payment_token
	 *
	 * @return array
	 */
	function send_refund_request( $payment_token ) {
		/** @var $camptix Camptix_Plugin  */
		global $camptix;

		$result = array(
			'token' => $payment_token,
			'status' => CampTix_Plugin::PAYMENT_STATUS_REFUND_FAILED,
			'refund_transaction_id' => null,
			'refund_transaction_details' => array()
		);

		$camptix->log( __FUNCTION__ . ' not implemented in payment module.', 0, null, 'refund' );
		return $result;
	}

	/**
	 * Register settings for the Payment screen
	 */
	function payment_settings_fields() {
		return;
	}

	/**
	 * Add the current payment method to the list of available methods
	 *
	 * @param array $payment_methods
	 *
	 * @return array
	 */
	function _camptix_available_payment_methods( $payment_methods ) {
		if ( $this->id && $this->name && $this->description ) {
			$payment_methods[ $this->id ] = array(
				'name' => $this->name,
				'description' => $this->description,
			);
		}

		return $payment_methods;
	}

	function payment_result( $payment_token, $result, $data = array() ) {
		global $camptix;
		return $camptix->payment_result( $payment_token, $result, $data );
	}

	function redirect_with_error_flags( $query_args = array() ) {
		global $camptix;
		$camptix->redirect_with_error_flags( $query_args );
	}

	function error_flag( $flag ) {
		global $camptix;
		$camptix->error_flag( $flag );
	}

	function get_tickets_url() {
		global $camptix;
		return $camptix->get_tickets_url();
	}

	function log( $message, $post_id = 0, $data = null, $module = 'payment' ) {
		global $camptix;
		return $camptix->log( $message, $post_id, $data, $module );
	}

	function get_order( $payment_token = false ) {
		if ( ! $payment_token ) {
			return array();
		}

		$attendees = get_posts( array(
			'posts_per_page' => 1,
			'post_type'      => 'tix_attendee',
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => 'tix_payment_token',
					'compare' => '=',
					'value'   => $payment_token,
					'type'    => 'CHAR',
				),
			),
		) );

		if ( ! $attendees ) {
			return array();
		}

		return $this->get_order_by_attendee_id( $attendees[0]->ID );
	}

	/**
	 * Get the order for the given attendee
	 *
	 * @param int $attendee_id
	 *
	 * @return array
	 */
	function get_order_by_attendee_id( $attendee_id ) {
		$order = (array) get_post_meta( $attendee_id, 'tix_order', true );

		if ( $order ) {
			$order['attendee_id'] = $attendee_id;
		}
		return $order;

	}

	/**
	 * A text input for the Settings API, name and value attributes
	 * should be specified in $args. Same goes for the rest.
	 */
	function field_text( $args ) {
		?>
		<input type="text" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * A checkbox field for the Settings API.
	 */
	function field_checkbox( $args ) {
		?>
		<input type="checkbox" name="<?php echo esc_attr( $args['name'] ); ?>" value="1" <?php checked( $args['value'] ); ?> />
		<?php
	}

	/**
	 * Get an escaped field name for a setting
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	function field_yesno( $args ) {
		?>
		<label class="tix-yes-no description"><input type="radio" name="<?php echo esc_attr( $args['name'] ); ?>" value="1" <?php checked( $args['value'], true ); ?>> <?php _e( 'Yes', 'camptix' ); ?></label>
		<label class="tix-yes-no description"><input type="radio" name="<?php echo esc_attr( $args['name'] ); ?>" value="0" <?php checked( $args['value'], false ); ?>> <?php _e( 'No', 'camptix' ); ?></label>

		<?php if ( isset( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
		<?php endif; ?>
		<?php
	}

	function settings_field_name_attr( $name ) {
		return esc_attr( "camptix_payment_options_{$this->id}[{$name}]" );
	}

	/**
	 * Add a setting field
	 *
	 * @param string $option_name
	 * @param string $title
	 * @param string $callback
	 * @param string $description
	 */
	function add_settings_field_helper( $option_name, $title, $callback, $description = '' ) {
		add_settings_field(
			'camptix_payment_' . $this->id . '_' . $option_name,
			$title,
			$callback,
			'camptix_options',
			'payment_' . $this->id,
			array(
				'name' => $this->settings_field_name_attr( $option_name ),
				'value' => $this->options[ $option_name ],
				'description' => $description,
		) );
	}

	/**
	 * Get this payment method's options
	 *
	 * @return array
	 */
	function get_payment_options() {
		$payment_options = array();
		$option_key = "payment_options_{$this->id}";

		if ( isset( $this->camptix_options[ $option_key ] ) ) {
			$payment_options = (array) $this->camptix_options[ $option_key ];
		}

		return $payment_options;
	}
}
