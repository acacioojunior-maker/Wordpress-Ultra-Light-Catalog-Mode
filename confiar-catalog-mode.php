<?php
/**
 * Plugin Name: Confiar Catalog Mode
 * Plugin URI: https://github.com/acacioojunior-maker/Wordpress-Ultra-Light-Catalog-Mode
 * Description: Modo catálogo com orçamento rápido para WooCommerce
 * Author: Confiar
 * Version: 1.0.9
 * Text Domain: confiar-catalog-mode
 * Domain Path: /languages
 * WC requires at least: 3.9
 * WC tested up to: 8.0
 */

defined( 'ABSPATH' ) || exit;

// GitHub auto-updates via plugin-update-checker
require_once plugin_dir_path( __FILE__ ) . 'lib/plugin-update-checker/plugin-update-checker.php';
$confiar_updater = YahnisElsts\PluginUpdateChecker\v5p6\PucFactory::buildUpdateChecker(
	'https://github.com/acacioojunior-maker/Wordpress-Ultra-Light-Catalog-Mode',
	__FILE__,
	'confiar-catalog-mode'
);
$confiar_updater->setBranch( 'main' );
// Token defined in wp-config.php as: define('CONFIAR_GITHUB_TOKEN', 'ghp_...');
if ( defined( 'CONFIAR_GITHUB_TOKEN' ) && CONFIAR_GITHUB_TOKEN ) {
	$confiar_updater->setAuthentication( CONFIAR_GITHUB_TOKEN );
}

// Declare HPOS compatibility — must run before WooCommerce initializes
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

if ( ! class_exists( 'Confiar_Catalog_Mode' ) ) {
	class Confiar_Catalog_Mode {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->define_constants();
			$this->load_dependencies();
			$this->init_hooks();
		}

		private function define_constants() {
			define( 'CONFIAR_CATALOG_MODE_VERSION', '1.0.9' );
			define( 'CONFIAR_CATALOG_MODE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			define( 'CONFIAR_CATALOG_MODE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			define( 'CONFIAR_CATALOG_MODE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}

		private function load_dependencies() {
			require_once CONFIAR_CATALOG_MODE_PLUGIN_DIR . 'includes/class-main.php';
		}

		private function init_hooks() {
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		}

		public function activate() {
			if ( ! get_option( 'confiar_catalog_mode_enabled' ) ) {
				add_option( 'confiar_catalog_mode_enabled', false );
			}
			flush_rewrite_rules();
		}

		public function deactivate() {
			flush_rewrite_rules();
		}

		public function init_plugin() {
			load_plugin_textdomain(
				'confiar-catalog-mode',
				false,
				dirname( CONFIAR_CATALOG_MODE_PLUGIN_BASENAME ) . '/languages'
			);

			Confiar_Catalog_Main::get_instance();
		}
	}

	add_action(
		'plugins_loaded',
		function() {
			// Require WooCommerce
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action(
					'admin_notices',
					function() {
						echo '<div class="error"><p><strong>Confiar Catalog Mode</strong> requires WooCommerce to be installed and active.</p></div>';
					}
				);
				return;
			}

			Confiar_Catalog_Mode::get_instance()->init_plugin();
		}
	);
}
