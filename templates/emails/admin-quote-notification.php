<?php
/**
 * Admin Quote Notification Email Template
 */

defined( 'ABSPATH' ) || exit;

$customer_phone = $order->get_billing_phone();
$customer_cnpj  = $order->get_meta( '_quote_cnpj' );
$customer_cep   = $order->get_billing_postcode();
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'Novo Pedido de Orçamento', 'confiar-catalog-mode' ); ?></title>
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
			border-bottom: 2px solid #0073aa;
		}
		.header h1 {
			margin: 0;
			color: #0073aa;
		}
		.alert {
			background: #fff8e5;
			border-left: 4px solid #ffc21f;
			padding: 15px;
			margin: 20px 0;
			border-radius: 4px;
		}
		.customer-info {
			background: #f0f0f0;
			padding: 15px;
			border-radius: 4px;
			margin: 20px 0;
		}
		.customer-info p {
			margin: 8px 0;
		}
		.product-info {
			background: #f9f9f9;
			padding: 15px;
			border-radius: 4px;
			margin: 20px 0;
		}
		.product-info h4 {
			margin: 0 0 10px 0;
			color: #333;
		}
		table {
			width: 100%;
			border-collapse: collapse;
			margin: 10px 0;
		}
		table th,
		table td {
			padding: 10px;
			text-align: left;
			border-bottom: 1px solid #ddd;
		}
		table th {
			background: #f5f5f5;
			font-weight: 600;
		}
		.action-button {
			display: inline-block;
			padding: 12px 24px;
			background: #0073aa;
			color: #fff;
			text-decoration: none;
			border-radius: 4px;
			font-weight: 600;
			margin: 20px 0;
		}
		.footer {
			text-align: center;
			padding-top: 20px;
			border-top: 1px solid #ddd;
			font-size: 12px;
			color: #666;
		}
		.message-box {
			background: #e8f4f8;
			border-left: 4px solid #0073aa;
			padding: 15px;
			margin: 15px 0;
			border-radius: 4px;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1><?php esc_html_e( 'Novo Pedido de Orçamento', 'confiar-catalog-mode' ); ?></h1>
			<p><?php esc_html_e( 'Um cliente enviou uma solicitação de orçamento', 'confiar-catalog-mode' ); ?></p>
		</div>

		<div class="alert">
			<strong><?php esc_html_e( 'Ação Necessária', 'confiar-catalog-mode' ); ?>:</strong>
			<?php esc_html_e( 'Analise este orçamento e envie uma proposta ao cliente pelo painel.', 'confiar-catalog-mode' ); ?>
		</div>

		<div class="customer-info">
			<h3><?php esc_html_e( 'Dados do Cliente', 'confiar-catalog-mode' ); ?></h3>
			<p>
				<strong><?php esc_html_e( 'Nome:', 'confiar-catalog-mode' ); ?></strong>
				<?php echo esc_html( $customer_name ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'E-mail:', 'confiar-catalog-mode' ); ?></strong>
				<a href="mailto:<?php echo esc_attr( $customer_email ); ?>">
					<?php echo esc_html( $customer_email ); ?>
				</a>
			</p>
			<?php if ( $customer_phone ) : ?>
				<p>
					<strong><?php esc_html_e( 'Telefone:', 'confiar-catalog-mode' ); ?></strong>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $customer_phone ) ); ?>">
						<?php echo esc_html( $customer_phone ); ?>
					</a>
				</p>
			<?php endif; ?>
			<?php if ( $customer_cnpj ) : ?>
				<p>
					<strong><?php esc_html_e( 'CNPJ:', 'confiar-catalog-mode' ); ?></strong>
					<?php echo esc_html( $customer_cnpj ); ?>
				</p>
			<?php endif; ?>
			<?php if ( $customer_cep ) : ?>
				<p>
					<strong><?php esc_html_e( 'CEP:', 'confiar-catalog-mode' ); ?></strong>
					<?php echo esc_html( $customer_cep ); ?>
				</p>
			<?php endif; ?>
		</div>

		<div class="product-info">
			<h4><?php esc_html_e( 'Detalhes do Orçamento', 'confiar-catalog-mode' ); ?></h4>
			<p>
				<strong><?php esc_html_e( 'Orçamento Nº:', 'confiar-catalog-mode' ); ?></strong>
				<?php echo esc_html( $order->get_id() ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Data:', 'confiar-catalog-mode' ); ?></strong>
				<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
			</p>

			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Produto', 'confiar-catalog-mode' ); ?></th>
						<th style="text-align: right;"><?php esc_html_e( 'Qtd', 'confiar-catalog-mode' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $order->get_items() as $item ) {
						?>
						<tr>
							<td><?php echo esc_html( $item->get_name() ); ?></td>
							<td style="text-align: right;"><?php echo esc_html( $item->get_quantity() ); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>

		<?php
		$customer_message = $order->get_meta( '_quote_message' );
		if ( $customer_message ) {
			?>
			<div class="message-box">
				<h4><?php esc_html_e( 'Mensagem do Cliente', 'confiar-catalog-mode' ); ?></h4>
				<p><?php echo wp_kses_post( nl2br( $customer_message ) ); ?></p>
			</div>
			<?php
		}
		?>

		<div style="text-align: center;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders&id=' . $order->get_id() . '&action=edit' ) ); ?>" class="action-button">
				<?php esc_html_e( 'Ver Orçamento no Painel', 'confiar-catalog-mode' ); ?>
			</a>
		</div>

		<div class="footer">
			<p><?php esc_html_e( 'Mensagem automática do seu painel de administração.', 'confiar-catalog-mode' ); ?></p>
		</div>
	</div>
</body>
</html>
