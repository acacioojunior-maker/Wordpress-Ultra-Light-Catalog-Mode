<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Confiar_Catalog_Product_Display' ) ) {
	class Confiar_Catalog_Product_Display {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_filter( 'woocommerce_get_price_html', array( $this, 'hide_price' ), 999, 2 );
			add_filter( 'woocommerce_variable_price_html', array( $this, 'hide_price' ), 999, 2 );
			add_filter( 'woocommerce_empty_price_html', array( $this, 'hide_price' ), 999, 2 );
			add_filter( 'woocommerce_grouped_price_html', array( $this, 'hide_price' ), 999, 2 );
			// Loop/shop: blonwe_loop_add_to_cart calls woocommerce_template_loop_add_to_cart internally
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'replace_add_to_cart_button' ), 999 );
			// Single product: hook into woocommerce_single_product_summary (same as WA button, priority 31)
			// The WC add-to-cart form doesn't render when product has no price, so we use this hook instead
			add_action( 'woocommerce_single_product_summary', array( $this, 'inject_single_product_quote_button' ), 31 );
			// Remove Blonwe WhatsApp button (priority 29) when catalog mode is active
			add_action( 'woocommerce_single_product_summary', array( $this, 'maybe_remove_whatsapp_button' ), 1 );
			add_filter( 'woocommerce_widget_shopping_cart_button_text', array( $this, 'hide_cart_button_text' ), 999 );
			add_filter( 'body_class', array( $this, 'add_catalog_body_class' ) );
			// Runs after theme loads: replaces "Item sob consulta." with "Obter orçamento" link
			add_action( 'init', array( $this, 'setup_catalog_hooks' ), 20 );
		}

		public function is_catalog_mode_enabled() {
			return (bool) get_option( 'confiar_catalog_mode_enabled' );
		}

		public function hide_price( $price, $product ) {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return $price;
			}
			return '';
		}

		public function replace_add_to_cart_button( $html ) {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return $html;
			}

			global $product;

			if ( ! $product ) {
				return $html;
			}

			$button_text = get_option( 'confiar_catalog_mode_button_text', __( 'Quick Quote', 'confiar-catalog-mode' ) );

			return sprintf(
				'<a href="#" class="button alt confiar-quick-quote-btn" data-product-id="%s">%s</a>',
				esc_attr( $product->get_id() ),
				esc_html( $button_text )
			);
		}

		// Kept for hooks that only pass text (product_add_to_cart_text filter used by some themes)
		public function replace_button_text( $text ) {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return $text;
			}
			return get_option( 'confiar_catalog_mode_button_text', __( 'Quick Quote', 'confiar-catalog-mode' ) );
		}

		public function maybe_remove_whatsapp_button() {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return;
			}
			// Remove Blonwe theme WhatsApp button (registered conditionally in blonwe-core)
			remove_action( 'woocommerce_single_product_summary', 'blonwe_order_on_whatsapp', 29 );
		}

		public function inject_single_product_quote_button() {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return;
			}

			global $product;
			if ( ! $product ) {
				return;
			}

			$button_text = get_option( 'confiar_catalog_mode_button_text', __( 'Solicitar Orçamento', 'confiar-catalog-mode' ) );

			printf(
				'<div class="confiar-quote-wrapper"><a href="#" class="button button-primary confiar-quick-quote-btn" data-product-id="%s">%s</a></div>',
				esc_attr( $product->get_id() ),
				esc_html( $button_text )
			);
		}

		public function hide_cart_button_text( $text ) {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return $text;
			}
			// Return empty to hide the button completely
			return '';
		}

		public function add_catalog_body_class( $classes ) {
			if ( $this->is_catalog_mode_enabled() ) {
				$classes[] = 'confiar-catalog-active';
			}
			return $classes;
		}

		public function setup_catalog_hooks() {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return;
			}
			// Replace Blonwe's shipping-class label ("Item sob consulta.") with our quote link
			remove_action( 'blonwe_product_box_footer', 'blonwe_shipping_class_name', 10 );
			add_action( 'blonwe_product_box_footer', array( $this, 'output_loop_quote_link' ), 10, 3 );
		}

		public function output_loop_quote_link( $stockprogressbar = '', $stockstatus = '', $shippingclass = '' ) {
			if ( $shippingclass !== 'true' || is_product() ) {
				return;
			}
			global $product;
			if ( ! $product ) {
				return;
			}
			printf(
				'<a href="#" class="product-delivery-time confiar-footer-quote-link confiar-quick-quote-btn" data-product-id="%s">%s</a>',
				esc_attr( $product->get_id() ),
				esc_html__( 'Obter orçamento', 'confiar-catalog-mode' )
			);
		}
	}
}
