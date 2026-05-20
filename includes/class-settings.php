<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Confiar_Catalog_Settings' ) ) {
	class Confiar_Catalog_Settings {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}

		public function add_settings_page() {
			add_options_page(
				__( 'Confiar Catalog Mode', 'confiar-catalog-mode' ),
				__( 'Catalog Mode', 'confiar-catalog-mode' ),
				'manage_options',
				'confiar-catalog-mode',
				array( $this, 'render_settings_page' )
			);
		}

		public function register_settings() {
			register_setting(
				'confiar_catalog_mode_group',
				'confiar_catalog_mode_enabled',
				array(
					'type'              => 'boolean',
					'sanitize_callback' => array( $this, 'sanitize_enabled' ),
					'show_in_rest'      => false,
				)
			);

			register_setting(
				'confiar_catalog_mode_group',
				'confiar_catalog_mode_button_text',
				array(
					'type'              => 'string',
					'default'           => __( 'Solicitar Orçamento', 'confiar-catalog-mode' ),
					'sanitize_callback' => 'sanitize_text_field',
					'show_in_rest'      => false,
				)
			);

			register_setting(
				'confiar_catalog_mode_group',
				'confiar_catalog_mode_notification_text',
				array(
					'type'              => 'string',
					'default'           => __( 'Esta loja está em modo catálogo. Clique em "Solicitar Orçamento" para solicitar uma proposta.', 'confiar-catalog-mode' ),
					'sanitize_callback' => 'sanitize_text_field',
					'show_in_rest'      => false,
				)
			);

			add_settings_section(
				'confiar_catalog_mode_section',
				__( 'Catalog Mode Settings', 'confiar-catalog-mode' ),
				array( $this, 'render_section_description' ),
				'confiar_catalog_mode_group'
			);

			add_settings_field(
				'confiar_catalog_mode_enabled',
				__( 'Enable Catalog Mode', 'confiar-catalog-mode' ),
				array( $this, 'render_enabled_field' ),
				'confiar_catalog_mode_group',
				'confiar_catalog_mode_section'
			);

			add_settings_field(
				'confiar_catalog_mode_button_text',
				__( 'Button Text', 'confiar-catalog-mode' ),
				array( $this, 'render_button_text_field' ),
				'confiar_catalog_mode_group',
				'confiar_catalog_mode_section'
			);

			add_settings_field(
				'confiar_catalog_mode_notification_text',
				__( 'Notification Text', 'confiar-catalog-mode' ),
				array( $this, 'render_notification_text_field' ),
				'confiar_catalog_mode_group',
				'confiar_catalog_mode_section'
			);
		}

		public function render_settings_page() {
			?>
			<div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'confiar_catalog_mode_group' );
					do_settings_sections( 'confiar_catalog_mode_group' );
					submit_button();
					?>
				</form>
			</div>
			<?php
		}

		public function render_section_description() {
			echo wp_kses_post( __( 'Configure the catalog mode to hide prices and show a quick quote button instead of add to cart.', 'confiar-catalog-mode' ) );
		}

		public function render_enabled_field() {
			$enabled = get_option( 'confiar_catalog_mode_enabled' );
			?>
			<input
				type="checkbox"
				name="confiar_catalog_mode_enabled"
				value="1"
				<?php checked( 1, $enabled ); ?>
			>
			<label><?php esc_html_e( 'Enable catalog mode (hide prices and show quote button)', 'confiar-catalog-mode' ); ?></label>
			<?php
		}

		public function render_button_text_field() {
			$text = get_option( 'confiar_catalog_mode_button_text', __( 'Solicitar Orçamento', 'confiar-catalog-mode' ) );
			?>
			<input
				type="text"
				name="confiar_catalog_mode_button_text"
				value="<?php echo esc_attr( $text ); ?>"
				class="regular-text"
			>
			<?php
		}

		public function render_notification_text_field() {
			$text = get_option( 'confiar_catalog_mode_notification_text', __( 'Esta loja está em modo catálogo. Clique em "Solicitar Orçamento" para solicitar uma proposta.', 'confiar-catalog-mode' ) );
			?>
			<textarea
				name="confiar_catalog_mode_notification_text"
				class="large-text"
				rows="3"
			><?php echo esc_textarea( $text ); ?></textarea>
			<?php
		}

		public function sanitize_enabled( $input ) {
			return (bool) $input;
		}
	}
}
