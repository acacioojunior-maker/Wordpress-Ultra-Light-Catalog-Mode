<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Confiar_Catalog_Order_Handler' ) ) {
	class Confiar_Catalog_Order_Handler {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'wp_ajax_confiar_submit_quote', array( $this, 'handle_quote_submission' ) );
			add_action( 'wp_ajax_nopriv_confiar_submit_quote', array( $this, 'handle_quote_submission' ) );
		}

		public function handle_quote_submission() {
			// Verify nonce
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'confiar_quote_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid security check.', 'confiar-catalog-mode' ) ) );
			}

			// Get and validate form data
			$customer_name  = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
			$customer_email = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';
			$customer_phone = isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '';
			$customer_cnpj  = isset( $_POST['customer_cnpj'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_cnpj'] ) ) : '';
			$customer_cep   = isset( $_POST['customer_cep'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_cep'] ) ) : '';
			$product_id     = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
			$quantity       = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 1;
			$message        = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

			// Validate inputs
			if ( empty( $customer_name ) || strlen( $customer_name ) < 3 ) {
				wp_send_json_error( array( 'message' => __( 'Por favor, informe seu nome (mínimo 3 caracteres).', 'confiar-catalog-mode' ) ) );
			}

			if ( ! is_email( $customer_email ) ) {
				wp_send_json_error( array( 'message' => __( 'Por favor, informe um e-mail válido.', 'confiar-catalog-mode' ) ) );
			}

			if ( strlen( preg_replace( '/\D/', '', $customer_phone ) ) < 8 ) {
				wp_send_json_error( array( 'message' => __( 'Por favor, informe um telefone válido.', 'confiar-catalog-mode' ) ) );
			}

			if ( $product_id <= 0 ) {
				wp_send_json_error( array( 'message' => __( 'Produto inválido.', 'confiar-catalog-mode' ) ) );
			}

			if ( $quantity <= 0 ) {
				wp_send_json_error( array( 'message' => __( 'Por favor, informe uma quantidade válida.', 'confiar-catalog-mode' ) ) );
			}

			// Get or create customer
			$customer_id = $this->get_or_create_customer( $customer_name, $customer_email );

			if ( is_wp_error( $customer_id ) ) {
				wp_send_json_error( array( 'message' => $customer_id->get_error_message() ) );
			}

			// Create order
			$order = $this->create_quote_order( $customer_id, $product_id, $quantity, $message, $customer_email, $customer_phone, $customer_cnpj, $customer_cep );

			if ( is_wp_error( $order ) ) {
				wp_send_json_error( array( 'message' => $order->get_error_message() ) );
			}

			// Send email notifications
			do_action( 'confiar_quote_submitted', $order, $customer_name, $customer_email );

			wp_send_json_success(
				array(
					'message'  => __( 'Quote submitted successfully!', 'confiar-catalog-mode' ),
					'order_id' => $order->get_id(),
				)
			);
		}

		private function get_or_create_customer( $name, $email ) {
			// Check if customer exists
			$customer = get_user_by( 'email', $email );

			if ( $customer ) {
				return $customer->ID;
			}

			// Create new customer
			$username = $this->generate_username( $email );

			// Generate secure password
			$password = wp_generate_password( 12, true );

			// Create WooCommerce customer
			$customer_id = wc_create_new_customer( $email, $username, $password );

			if ( is_wp_error( $customer_id ) ) {
				return $customer_id;
			}

			// Update user display name
			wp_update_user(
				array(
					'ID'           => $customer_id,
					'display_name' => $name,
					'first_name'   => $this->extract_first_name( $name ),
					'last_name'    => $this->extract_last_name( $name ),
				)
			);

			return $customer_id;
		}

		private function generate_username( $email ) {
			$username = explode( '@', $email )[0];
			$original = $username;
			$counter  = 1;

			// Check if username exists and generate unique one
			while ( username_exists( $username ) ) {
				$username = $original . $counter;
				$counter++;
			}

			return $username;
		}

		private function extract_first_name( $full_name ) {
			$parts = explode( ' ', trim( $full_name ) );
			return isset( $parts[0] ) ? $parts[0] : $full_name;
		}

		private function extract_last_name( $full_name ) {
			$parts = explode( ' ', trim( $full_name ) );
			return isset( $parts[1] ) ? implode( ' ', array_slice( $parts, 1 ) ) : '';
		}

		private function create_quote_order( $customer_id, $product_id, $quantity, $message, $customer_email, $customer_phone = '', $customer_cnpj = '', $customer_cep = '' ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return new WP_Error( 'invalid_product', __( 'Product not found.', 'confiar-catalog-mode' ) );
			}

			// Create order
			$order = wc_create_order(
				array(
					'customer_id' => $customer_id,
					'status'      => 'wc-rfq',
				)
			);

			if ( is_wp_error( $order ) ) {
				return $order;
			}

			// Add product to order
			$order->add_product( $product, $quantity );

			// Set billing data (native WooCommerce fields)
			$order->set_billing_email( $customer_email );
			if ( $customer_phone ) {
				$order->set_billing_phone( $customer_phone );
			}
			if ( $customer_cep ) {
				$order->set_billing_postcode( preg_replace( '/\D/', '', $customer_cep ) );
			}

			$order->add_order_note( __( 'Orçamento solicitado via formulário rápido.', 'confiar-catalog-mode' ), false );

			if ( ! empty( $message ) ) {
				$order->add_order_note(
					__( 'Mensagem do cliente: ', 'confiar-catalog-mode' ) . wp_kses_post( $message ),
					false
				);
			}

			// Save custom meta
			$order->update_meta_data( '_quote_client_email', $customer_email );
			$order->update_meta_data( '_quote_message', $message );
			$order->update_meta_data( '_quote_request_date', current_time( 'mysql' ) );
			if ( $customer_cnpj ) {
				$order->update_meta_data( '_quote_cnpj', $customer_cnpj );
			}

			$order->calculate_totals();
			$order->save();

			return $order;
		}
	}
}
