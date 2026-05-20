<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Confiar_Catalog_Email_Notifier' ) ) {
	class Confiar_Catalog_Email_Notifier {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'confiar_quote_submitted', array( $this, 'send_notifications' ), 10, 3 );
		}

		public function send_notifications( $order, $customer_name, $customer_email ) {
			$this->send_customer_email( $order, $customer_name, $customer_email );
			$this->send_admin_email( $order, $customer_name, $customer_email );
		}

		private function get_from_header() {
			$store_name  = get_bloginfo( 'name' );
			$store_email = get_option( 'woocommerce_email_from_address', get_option( 'admin_email' ) );
			return 'From: ' . $store_name . ' <' . $store_email . '>';
		}

		private function send_customer_email( $order, $customer_name, $customer_email ) {
			$subject = sprintf(
				__( '[%s] Seu orçamento foi recebido', 'confiar-catalog-mode' ),
				get_bloginfo( 'name' )
			);

			$store_email = get_option( 'woocommerce_email_from_address', get_option( 'admin_email' ) );

			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				$this->get_from_header(),
				'Reply-To: ' . get_bloginfo( 'name' ) . ' <' . $store_email . '>',
			);

			$message = $this->get_customer_email_template( $order, $customer_name );

			wp_mail( $customer_email, $subject, $message, $headers );
		}

		private function send_admin_email( $order, $customer_name, $customer_email ) {
			$admin_email = get_option( 'woocommerce_email_from_address', get_option( 'admin_email' ) );

			$subject = sprintf(
				__( '[%s] Novo orçamento recebido #%d', 'confiar-catalog-mode' ),
				get_bloginfo( 'name' ),
				$order->get_id()
			);

			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				$this->get_from_header(),
				// Reply-To points to the customer so admin can reply directly from email client
				'Reply-To: ' . $customer_name . ' <' . $customer_email . '>',
			);

			$message = $this->get_admin_email_template( $order, $customer_name, $customer_email );

			wp_mail( $admin_email, $subject, $message, $headers );
		}

		private function get_customer_email_template( $order, $customer_name ) {
			ob_start();
			include CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'templates/emails/customer-quote-notification.php';
			return ob_get_clean();
		}

		private function get_admin_email_template( $order, $customer_name, $customer_email ) {
			ob_start();
			include CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'templates/emails/admin-quote-notification.php';
			return ob_get_clean();
		}
	}
}
