<?php
/**
 * Customer Quote Notification Email Template
 */

defined( 'ABSPATH' ) || exit;
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'Orçamento Recebido', 'confiar-catalog-mode' ); ?></title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
			line-height: 1.6;
			color: #333;
			background: #f9f9f9;
		}
		.container {
			max-width: 600px;
			margin: 0 auto;
			background: #fff;
			padding: 20px;
			border-radius: 8px;
		}
		.header {
			text-align: center;
			padding-bottom: 20px;
			border-bottom: 2px solid #ffc21f;
		}
		.header h1 {
			margin: 0;
			color: #333;
		}
		.content {
			padding: 20px 0;
		}
		.product-info {
			background: #f9f9f9;
			padding: 15px;
			border-radius: 4px;
			margin: 20px 0;
		}
		.product-info p {
			margin: 8px 0;
		}
		.product-info strong {
			color: #333;
		}
		.footer {
			text-align: center;
			padding-top: 20px;
			border-top: 1px solid #ddd;
			font-size: 12px;
			color: #666;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			<p><?php esc_html_e( 'Solicitação de Orçamento Recebida', 'confiar-catalog-mode' ); ?></p>
		</div>

		<div class="content">
			<p><?php esc_html_e( 'Olá', 'confiar-catalog-mode' ); ?> <?php echo esc_html( $customer_name ); ?>,</p>

			<p><?php esc_html_e( 'Recebemos sua solicitação de orçamento! Nossa equipe irá analisá-la e enviar uma proposta em breve.', 'confiar-catalog-mode' ); ?></p>

			<div class="product-info">
				<h3><?php esc_html_e( 'Detalhes do Orçamento:', 'confiar-catalog-mode' ); ?></h3>
				<p>
					<strong><?php esc_html_e( 'Número do Orçamento:', 'confiar-catalog-mode' ); ?></strong>
					#<?php echo esc_html( $order->get_id() ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Data:', 'confiar-catalog-mode' ); ?></strong>
					<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Itens:', 'confiar-catalog-mode' ); ?></strong>
				</p>
				<ul style="margin: 10px 0; padding-left: 20px;">
					<?php
					foreach ( $order->get_items() as $item ) {
						?>
						<li>
							<?php echo esc_html( $item->get_name() ); ?> ×
							<?php echo esc_html( $item->get_quantity() ); ?>
						</li>
						<?php
					}
					?>
				</ul>
			</div>

			<p><?php esc_html_e( 'Você receberá um e-mail com nossa proposta e valores em breve.', 'confiar-catalog-mode' ); ?></p>

			<p><?php esc_html_e( 'Em caso de dúvidas, entre em contato conosco respondendo este e-mail.', 'confiar-catalog-mode' ); ?></p>

			<p><?php esc_html_e( 'Atenciosamente', 'confiar-catalog-mode' ); ?>,<br>
			<?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		</div>

		<div class="footer">
			<p><?php echo wp_kses_post( get_bloginfo( 'description' ) ); ?></p>
			<p><?php echo esc_html( get_bloginfo( 'url' ) ); ?></p>
		</div>
	</div>
</body>
</html>
