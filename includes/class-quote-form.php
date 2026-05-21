<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Confiar_Catalog_Quote_Form' ) ) {
	class Confiar_Catalog_Quote_Form {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'wp_footer', array( $this, 'render_quote_modal' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 11 );
		}

		public function is_catalog_mode_enabled() {
			return (bool) get_option( 'confiar_catalog_mode_enabled' );
		}

		public function enqueue_assets() {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return;
			}

			if ( ! ( is_shop() || is_product() || is_product_category() || is_product_tag() ) ) {
				return;
			}

			wp_enqueue_style(
				'confiar-catalog-modal',
				CONFIAR_CATALOG_MODE_PLUGIN_URL . 'public/css/modal.css',
				array(),
				CONFIAR_CATALOG_MODE_VERSION
			);

			wp_enqueue_script(
				'confiar-catalog-modal',
				CONFIAR_CATALOG_MODE_PLUGIN_URL . 'public/js/modal.js',
				array( 'jquery' ),
				CONFIAR_CATALOG_MODE_VERSION,
				true
			);

			wp_localize_script(
				'confiar-catalog-modal',
				'confiarCatalogMode',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'confiar_quote_nonce' ),
					'strings' => array(
						'success'      => __( 'Orçamento enviado com sucesso! Você receberá um e-mail em breve.', 'confiar-catalog-mode' ),
						'error'        => __( 'Ocorreu um erro ao enviar seu orçamento. Tente novamente.', 'confiar-catalog-mode' ),
						'invalidEmail' => __( 'Por favor, informe um e-mail válido.', 'confiar-catalog-mode' ),
						'invalidName'  => __( 'Por favor, informe seu nome (mínimo 3 caracteres).', 'confiar-catalog-mode' ),
						'invalidQty'   => __( 'Por favor, informe uma quantidade válida.', 'confiar-catalog-mode' ),
						'invalidPhone' => __( 'Por favor, informe um telefone válido.', 'confiar-catalog-mode' ),
						'invalidCnpj'  => __( 'CNPJ inválido. Verifique os dígitos informados.', 'confiar-catalog-mode' ),
						'invalidCep'   => __( 'CEP inválido. Informe os 8 dígitos corretamente.', 'confiar-catalog-mode' ),
						'cepSearching'  => __( 'Buscando endereço...', 'confiar-catalog-mode' ),
						'cepNotFound'   => __( 'CEP não encontrado.', 'confiar-catalog-mode' ),
						'cnpjSearching' => __( 'Consultando Receita Federal...', 'confiar-catalog-mode' ),
						'cnpjNotFound'  => __( 'CNPJ não encontrado na Receita Federal.', 'confiar-catalog-mode' ),
						'cnpjInactive'  => __( 'Empresa com situação cadastral irregular.', 'confiar-catalog-mode' ),
						'sending'       => __( 'Enviando...', 'confiar-catalog-mode' ),
						'submit'       => get_option( 'confiar_catalog_mode_button_text', __( 'Solicitar Orçamento', 'confiar-catalog-mode' ) ),
					),
				)
			);
		}

		public function render_quote_modal() {
			if ( ! $this->is_catalog_mode_enabled() ) {
				return;
			}

			if ( ! ( is_shop() || is_product() || is_product_category() || is_product_tag() ) ) {
				return;
			}

			$notification_text = get_option( 'confiar_catalog_mode_notification_text', __( 'Esta loja está em modo catálogo. Clique em "Pedir orçamento" para solicitar uma cotação deste produto.', 'confiar-catalog-mode' ) );
			$button_text       = get_option( 'confiar_catalog_mode_button_text', __( 'Solicitar Orçamento', 'confiar-catalog-mode' ) );

			?>
			<div id="confiar-quote-modal" class="confiar-modal">
				<div class="confiar-modal-overlay"></div>
				<div class="confiar-modal-content">
					<div class="confiar-modal-header">
						<h2><?php esc_html_e( 'Solicitar Orçamento', 'confiar-catalog-mode' ); ?></h2>
						<button type="button" class="confiar-modal-close" aria-label="<?php esc_attr_e( 'Fechar', 'confiar-catalog-mode' ); ?>">&times;</button>
					</div>
					<div class="confiar-modal-body">
						<?php if ( $notification_text ) : ?>
							<div class="confiar-notification">
								<?php echo wp_kses_post( $notification_text ); ?>
							</div>
						<?php endif; ?>

						<form id="confiar-quote-form" class="confiar-form">
							<?php wp_nonce_field( 'confiar_quote_nonce', 'confiar_quote_nonce_field' ); ?>

							<div class="confiar-form-group">
								<label for="customer_name">
									<?php esc_html_e( 'Nome', 'confiar-catalog-mode' ); ?>
									<span class="required">*</span>
								</label>
								<input
									type="text"
									id="customer_name"
									name="customer_name"
									required
									minlength="3"
									maxlength="100"
									placeholder="<?php esc_attr_e( 'Seu nome completo', 'confiar-catalog-mode' ); ?>"
								>
								<span class="confiar-error"></span>
							</div>

							<div class="confiar-form-group">
								<label for="customer_email">
									<?php esc_html_e( 'E-mail', 'confiar-catalog-mode' ); ?>
									<span class="required">*</span>
								</label>
								<input
									type="email"
									id="customer_email"
									name="customer_email"
									required
									placeholder="<?php esc_attr_e( 'seu@email.com', 'confiar-catalog-mode' ); ?>"
								>
								<span class="confiar-error"></span>
							</div>

							<div class="confiar-form-group">
								<label for="customer_phone">
									<?php esc_html_e( 'Telefone', 'confiar-catalog-mode' ); ?>
									<span class="required">*</span>
								</label>
								<input
									type="tel"
									id="customer_phone"
									name="customer_phone"
									required
									maxlength="20"
									placeholder="<?php esc_attr_e( '(47) 99999-9999', 'confiar-catalog-mode' ); ?>"
								>
								<span class="confiar-error"></span>
							</div>

							<div class="confiar-form-row">
								<div class="confiar-form-group confiar-form-group--half">
									<label for="customer_cnpj">
										<?php esc_html_e( 'CNPJ', 'confiar-catalog-mode' ); ?>
									</label>
									<input
										type="text"
										id="customer_cnpj"
										name="customer_cnpj"
										maxlength="18"
										placeholder="<?php esc_attr_e( '00.000.000/0001-00', 'confiar-catalog-mode' ); ?>"
									>
									<span class="confiar-error"></span>
									<span id="cnpj-feedback" class="confiar-cnpj-feedback"></span>
								</div>

								<div class="confiar-form-group confiar-form-group--half">
									<label for="customer_cep">
										<?php esc_html_e( 'CEP', 'confiar-catalog-mode' ); ?>
									</label>
									<input
										type="text"
										id="customer_cep"
										name="customer_cep"
										maxlength="9"
										placeholder="<?php esc_attr_e( '89000-000', 'confiar-catalog-mode' ); ?>"
										autocomplete="postal-code"
									>
									<span class="confiar-error"></span>
									<span id="cep-feedback" class="confiar-cep-feedback"></span>
								</div>
							</div>

							<input type="hidden" id="customer_city"         name="customer_city">
							<input type="hidden" id="customer_state"        name="customer_state">
							<input type="hidden" id="customer_neighborhood" name="customer_neighborhood">
							<input type="hidden" id="customer_address"      name="customer_address">
							<input type="hidden" id="customer_company"      name="customer_company">

							<div class="confiar-form-group">
								<label for="product_id">
									<?php esc_html_e( 'Produto', 'confiar-catalog-mode' ); ?>
									<span class="required">*</span>
								</label>
								<input
									type="hidden"
									id="product_id"
									name="product_id"
									required
								>
								<input
									type="text"
									id="product_name"
									name="product_name"
									readonly
									placeholder="<?php esc_attr_e( 'Produto aparecerá aqui', 'confiar-catalog-mode' ); ?>"
								>
							</div>

							<div class="confiar-form-group">
								<label for="quantity">
									<?php esc_html_e( 'Quantidade', 'confiar-catalog-mode' ); ?>
									<span class="required">*</span>
								</label>
								<input
									type="number"
									id="quantity"
									name="quantity"
									required
									min="1"
									value="1"
									placeholder="<?php esc_attr_e( '1', 'confiar-catalog-mode' ); ?>"
								>
								<span class="confiar-error"></span>
							</div>

							<div class="confiar-form-group">
								<label for="message">
									<?php esc_html_e( 'Mensagem (opcional)', 'confiar-catalog-mode' ); ?>
								</label>
								<textarea
									id="message"
									name="message"
									maxlength="500"
									rows="4"
									placeholder="<?php esc_attr_e( 'Informações adicionais...', 'confiar-catalog-mode' ); ?>"
								></textarea>
								<small class="char-count">0/500</small>
							</div>

							<button type="submit" class="button button-primary confiar-submit-btn">
								<?php echo esc_html( $button_text ); ?>
							</button>
						</form>

						<div id="confiar-quote-message" class="confiar-message" style="display: none;"></div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
