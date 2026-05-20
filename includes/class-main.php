<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Confiar_Catalog_Main' ) ) {
	class Confiar_Catalog_Main {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'init', array( $this, 'register_rfq_status' ) );
			$this->load_classes();
			$this->init_classes();
		}

		public function register_rfq_status() {
			register_post_status(
				'wc-rfq',
				array(
					'label'                     => _x( 'Orçamento Pendente', 'Order status', 'confiar-catalog-mode' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop(
						'Orçamento Pendente <span class="count">(%s)</span>',
						'Orçamentos Pendentes <span class="count">(%s)</span>',
						'confiar-catalog-mode'
					),
				)
			);

			add_filter( 'wc_order_statuses', array( $this, 'add_rfq_order_status' ) );
		}

		public function add_rfq_order_status( $statuses ) {
			$statuses['wc-rfq'] = _x( 'Orçamento Pendente', 'Order status', 'confiar-catalog-mode' );
			return $statuses;
		}

		private function load_classes() {
			require_once CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'includes/class-settings.php';
			require_once CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'includes/class-product-display.php';
			require_once CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'includes/class-quote-form.php';
			require_once CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'includes/class-order-handler.php';
			require_once CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'includes/class-email-notifier.php';
			require_once CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'includes/class-admin-quote-manager.php';
		}

		private function init_classes() {
			Confiar_Catalog_Settings::get_instance();
			Confiar_Catalog_Product_Display::get_instance();
			Confiar_Catalog_Quote_Form::get_instance();
			Confiar_Catalog_Order_Handler::get_instance();
			Confiar_Catalog_Email_Notifier::get_instance();
			Confiar_Catalog_Admin_Quote_Manager::get_instance();
		}
	}
}
