<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Confiar_Catalog_Admin_Quote_Manager' ) ) {
	class Confiar_Catalog_Admin_Quote_Manager {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			// Legacy orders (CPT-based)
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_order_type_column' ), 10, 2 );
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_type_column' ), 10 );
			// HPOS (High-Performance Order Storage)
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'render_order_type_column' ), 10, 2 );
			add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_order_type_column' ), 10 );

			add_action( 'add_meta_boxes', array( $this, 'add_quote_metabox' ) );
			add_action( 'wp_ajax_confiar_send_quote_response', array( $this, 'handle_send_response' ) );
		}

		public function add_order_type_column( $columns ) {
			$new_columns = array();

			foreach ( $columns as $key => $value ) {
				$new_columns[ $key ] = $value;

				if ( 'order_status' === $key ) {
					$new_columns['quote_type'] = __( 'Type', 'confiar-catalog-mode' );
				}
			}

			return $new_columns;
		}

		public function render_order_type_column( $column_name, $post_id ) {
			if ( 'quote_type' === $column_name ) {
				$order = wc_get_order( $post_id );

				if ( ! $order ) {
					return;
				}

				if ( 'rfq' === $order->get_status() ) {
					echo '<span class="dashicons dashicons-format-quote" title="' . esc_attr__( 'Quote Request', 'confiar-catalog-mode' ) . '"></span> ';
					echo esc_html__( 'Quote', 'confiar-catalog-mode' );
				} else {
					echo '—';
				}
			}
		}

		public function add_quote_metabox() {
			$screen = get_current_screen();

			$valid_screens = array( 'shop_order', 'woocommerce_page_wc-orders' );
			if ( ! $screen || ! in_array( $screen->id, $valid_screens, true ) ) {
				return;
			}

			foreach ( $valid_screens as $screen_id ) {
				add_meta_box(
					'confiar-quote-response',
					__( 'Resposta ao Orçamento', 'confiar-catalog-mode' ),
					array( $this, 'render_quote_metabox' ),
					$screen_id,
					'normal',
					'high'
				);
			}
		}

		public function render_quote_metabox( $post ) {
			$order = wc_get_order( $post );

			if ( ! $order || 'rfq' !== $order->get_status() ) {
				echo wp_kses_post( __( 'Este pedido não é uma solicitação de orçamento.', 'confiar-catalog-mode' ) );
				return;
			}

			$customer_message = $order->get_meta( '_quote_message' );
			$customer_phone   = $order->get_billing_phone();
			$customer_cnpj    = $order->get_meta( '_quote_cnpj' );
			$customer_cep     = $order->get_billing_postcode();
			$response_message = $order->get_meta( '_quote_response_message' );
			$response_price   = $order->get_meta( '_quote_response_price' );

			wp_nonce_field( 'confiar_quote_response_nonce', 'confiar_quote_response_nonce_field' );

			$default_message = __( 'Sua cotação de hoje.', 'confiar-catalog-mode' );
			?>
			<div class="confiar-metabox-content">
				<?php if ( $customer_phone || $customer_cnpj || $customer_cep ) : ?>
					<div class="confiar-customer-message">
						<h4><?php esc_html_e( 'Dados do Cliente:', 'confiar-catalog-mode' ); ?></h4>
						<?php if ( $customer_phone ) : ?>
							<p><strong><?php esc_html_e( 'Telefone:', 'confiar-catalog-mode' ); ?></strong> <?php echo esc_html( $customer_phone ); ?></p>
						<?php endif; ?>
						<?php if ( $customer_cnpj ) : ?>
							<p><strong><?php esc_html_e( 'CNPJ:', 'confiar-catalog-mode' ); ?></strong> <?php echo esc_html( $customer_cnpj ); ?></p>
						<?php endif; ?>
						<?php if ( $customer_cep ) : ?>
							<p><strong><?php esc_html_e( 'CEP:', 'confiar-catalog-mode' ); ?></strong> <?php echo esc_html( $customer_cep ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $customer_message ) : ?>
					<div class="confiar-customer-message">
						<h4><?php esc_html_e( 'Mensagem do Cliente:', 'confiar-catalog-mode' ); ?></h4>
						<p><?php echo wp_kses_post( $customer_message ); ?></p>
					</div>
				<?php endif; ?>

				<div class="confiar-response-section">
					<h4><?php esc_html_e( 'Enviar Proposta de Preço', 'confiar-catalog-mode' ); ?></h4>

					<div class="confiar-form-group">
						<label for="quote_price">
							<?php esc_html_e( 'Preço Cotado (R$)', 'confiar-catalog-mode' ); ?>
						</label>
						<input
							type="number"
							id="quote_price"
							name="quote_price"
							step="0.01"
							min="0"
							value="<?php echo esc_attr( $response_price ); ?>"
							placeholder="<?php esc_attr_e( '0,00', 'confiar-catalog-mode' ); ?>"
						>
					</div>

					<div class="confiar-form-group">
						<label for="quote_response_message">
							<?php esc_html_e( 'Sua Mensagem', 'confiar-catalog-mode' ); ?>
						</label>
						<textarea
							id="quote_response_message"
							name="quote_response_message"
							rows="5"
							placeholder="<?php echo esc_attr( $default_message ); ?>"
						><?php echo wp_kses_post( $response_message ? $response_message : '' ); ?></textarea>
					</div>

					<button
						type="button"
						id="send-quote-response"
						class="button button-primary"
						data-order-id="<?php echo esc_attr( $order->get_id() ); ?>"
					>
						<?php esc_html_e( 'Enviar Resposta ao Cliente', 'confiar-catalog-mode' ); ?>
					</button>
				</div>
			</div>

			<style>
				.confiar-metabox-content {
					padding: 10px 0;
				}

				.confiar-customer-message {
					background: #f9f9f9;
					padding: 12px;
					margin-bottom: 20px;
					border-left: 4px solid #0073aa;
				}

				.confiar-response-section {
					margin-top: 20px;
				}

				.confiar-form-group {
					margin-bottom: 15px;
				}

				.confiar-form-group label {
					display: block;
					margin-bottom: 5px;
					font-weight: 500;
				}

				.confiar-form-group input,
				.confiar-form-group textarea {
					width: 100%;
					max-width: 600px;
				}
			</style>

			<script>
				document.getElementById('send-quote-response').addEventListener('click', function() {
					const orderId = this.getAttribute('data-order-id');
					const price = document.getElementById('quote_price').value;
					const message = document.getElementById('quote_response_message').value;
					const nonce = document.querySelector('[name="confiar_quote_response_nonce_field"]').value;

					fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams({
							action: 'confiar_send_quote_response',
							order_id: orderId,
							price: price,
							message: message,
							nonce: nonce,
						}),
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							alert('<?php esc_attr_e( 'Resposta enviada com sucesso ao cliente!', 'confiar-catalog-mode' ); ?>');
							location.reload();
						} else {
							alert('<?php esc_attr_e( 'Erro ao enviar resposta. Tente novamente.', 'confiar-catalog-mode' ); ?>');
						}
					});
				});
			</script>
			<?php
		}

		public function handle_send_response() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'confiar_quote_response_nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'Invalid security check.', 'confiar-catalog-mode' ) ) );
			}

			if ( ! current_user_can( 'manage_woocommerce_orders' ) && ! current_user_can( 'edit_shop_orders' ) ) {
				wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'confiar-catalog-mode' ) ) );
			}

			$order_id = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : 0;
			$price    = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : '';
			$message  = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

			$order = wc_get_order( $order_id );

			if ( ! $order || 'rfq' !== $order->get_status() ) {
				wp_send_json_error( array( 'message' => __( 'Invalid order.', 'confiar-catalog-mode' ) ) );
			}

			// Save meta data
			$order->update_meta_data( '_quote_response_price', $price );
			$order->update_meta_data( '_quote_response_message', $message );
			$order->update_meta_data( '_quote_response_date', current_time( 'mysql' ) );
			$order->save();

			// Send email to customer
			$this->send_quote_response_email( $order, $price, $message );

			wp_send_json_success( array( 'message' => __( 'Resposta enviada com sucesso!', 'confiar-catalog-mode' ) ) );
		}

		private function send_quote_response_email( $order, $price, $message ) {
			$customer_email = $order->get_billing_email();
			$store_name     = get_bloginfo( 'name' );
			$store_email    = get_option( 'woocommerce_email_from_address', get_option( 'admin_email' ) );

			$subject = sprintf(
				__( '[%s] Sua cotação para o pedido #%d', 'confiar-catalog-mode' ),
				$store_name,
				$order->get_id()
			);

			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . $store_name . ' <' . $store_email . '>',
				// Reply-To points to store so customer replies go to the right place
				'Reply-To: ' . $store_name . ' <' . $store_email . '>',
			);

			$email_body = '<h2>' . esc_html( get_bloginfo( 'name' ) ) . '</h2>';
			$email_body .= '<p>' . __( 'Thank you for your quote request!', 'confiar-catalog-mode' ) . '</p>';
			$email_body .= '<p>' . __( 'Here is our custom quote for you:', 'confiar-catalog-mode' ) . '</p>';

			if ( $price ) {
				$email_body .= '<h3>' . esc_html( __( 'Price', 'confiar-catalog-mode' ) ) . ': ' . wc_price( floatval( $price ) ) . '</h3>';
			}

			if ( $message ) {
				$email_body .= '<div style="background: #f9f9f9; padding: 12px; border-left: 4px solid #0073aa;">';
				$email_body .= wp_kses_post( nl2br( $message ) );
				$email_body .= '</div>';
			}

			$email_body .= '<p>';
			$email_body .= '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '" class="button">';
			$email_body .= esc_html__( 'Proceed to Checkout', 'confiar-catalog-mode' );
			$email_body .= '</a>';
			$email_body .= '</p>';

			wp_mail( $customer_email, $subject, $email_body, $headers );
		}
	}
}
