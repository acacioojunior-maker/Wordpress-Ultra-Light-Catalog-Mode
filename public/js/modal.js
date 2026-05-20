(function($) {
	'use strict';

	var ConfiarCatalogMode = {
		init: function() {
			this.cacheElements();
			this.bindEvents();
		},

		cacheElements: function() {
			this.$modal = $('#confiar-quote-modal');
			this.$form = $('#confiar-quote-form');
			this.$overlay = $('.confiar-modal-overlay');
			this.$closeBtn = $('.confiar-modal-close');
			this.$submitBtn = $('.confiar-submit-btn');
			this.$message = $('#confiar-quote-message');
		},

		bindEvents: function() {
			var self = this;

			// Open modal on button click
			$(document).on('click', '.confiar-quick-quote-btn', function(e) {
				e.preventDefault();
				var productId = $(this).data('product-id');
				self.openModal(productId);
			});

			// Close modal
			this.$closeBtn.on('click', function() {
				self.closeModal();
			});

			this.$overlay.on('click', function() {
				self.closeModal();
			});

			// Close on ESC key
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape') {
					self.closeModal();
				}
			});

			// Form submit
			this.$form.on('submit', function(e) {
				e.preventDefault();
				self.submitForm();
			});

			// Character counter
			$('#message').on('input', function() {
				var length = $(this).val().length;
				$('.char-count').text(length + '/500');
			});

			// Phone mask: (XX) XXXXX-XXXX or (XX) XXXX-XXXX
			$('#customer_phone').on('input', function() {
				var v = $(this).val().replace(/\D/g, '').substring(0, 11);
				if (v.length > 2) { v = '(' + v.substring(0, 2) + ') ' + v.substring(2); }
				if (v.replace(/\D/g, '').length > 10) {
					v = v.substring(0, v.length - 1 - (v.length > 10 ? 0 : 0));
					var digits = v.replace(/\D/g, '');
					v = '(' + digits.substring(0, 2) + ') ' + digits.substring(2, 7) + '-' + digits.substring(7);
				} else if (v.replace(/\D/g, '').length > 6) {
					var digits = v.replace(/\D/g, '');
					v = '(' + digits.substring(0, 2) + ') ' + digits.substring(2, 6) + '-' + digits.substring(6);
				}
				$(this).val(v);
			});

			// CNPJ mask: XX.XXX.XXX/XXXX-XX
			$('#customer_cnpj').on('input', function() {
				var v = $(this).val().replace(/\D/g, '').substring(0, 14);
				if (v.length > 12) { v = v.substring(0, 12) + '-' + v.substring(12); }
				if (v.replace(/\D/g, '').length > 8) {
					var d = v.replace(/\D/g, '');
					v = d.substring(0, 2) + '.' + d.substring(2, 5) + '.' + d.substring(5, 8) + '/' + d.substring(8, 12) + (d.length > 12 ? '-' + d.substring(12) : '');
				} else if (v.replace(/\D/g, '').length > 5) {
					var d = v.replace(/\D/g, '');
					v = d.substring(0, 2) + '.' + d.substring(2, 5) + '.' + d.substring(5);
				} else if (v.replace(/\D/g, '').length > 2) {
					var d = v.replace(/\D/g, '');
					v = d.substring(0, 2) + '.' + d.substring(2);
				}
				$(this).val(v);
			});

			// CEP mask: XXXXX-XXX
			$('#customer_cep').on('input', function() {
				var v = $(this).val().replace(/\D/g, '').substring(0, 8);
				if (v.length > 5) { v = v.substring(0, 5) + '-' + v.substring(5); }
				$(this).val(v);
			});
		},

		openModal: function(productId) {
			var self = this;
			var productName = '';

			// Get product name from page if on product page
			if ($('h1.product_title').length) {
				productName = $('h1.product_title').text();
			} else {
				// Get from product element if on shop page
				var $productEl = $('[data-product-id="' + productId + '"]').closest('.product');
				if ($productEl.length) {
					productName = $productEl.find('h2, .woocommerce-loop-product__title').text();
				}
			}

			this.$form[0].reset();
			this.$message.hide().text('');
			$('.confiar-form-group input, .confiar-form-group textarea').removeClass('error');
			$('#product_id').val(productId);
			$('#product_name').val(productName);
			$('.char-count').text('0/500');
			$('.confiar-error').removeClass('show').text('');

			this.$modal.addClass('active');

			// Focus first input
			$('#customer_name').focus();
		},

		closeModal: function() {
			this.$modal.removeClass('active');
			this.$form[0].reset();
			this.$message.hide().text('');
		},

		submitForm: function() {
			var self = this;

			if (!this.validateForm()) {
				return;
			}

			var formData = {
				action: 'confiar_submit_quote',
				nonce: confiarCatalogMode.nonce,
				customer_name: $('#customer_name').val(),
				customer_email: $('#customer_email').val(),
				customer_phone: $('#customer_phone').val(),
				customer_cnpj: $('#customer_cnpj').val(),
				customer_cep: $('#customer_cep').val(),
				product_id: $('#product_id').val(),
				quantity: $('#quantity').val(),
				message: $('#message').val(),
			};

			this.$submitBtn.prop('disabled', true).text(confiarCatalogMode.strings.sending);

			$.ajax({
				url: confiarCatalogMode.ajaxurl,
				type: 'POST',
				data: formData,
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						self.showMessage(response.data.message, 'success');
						setTimeout(function() {
							self.closeModal();
						}, 2000);
					} else {
						self.showMessage(response.data.message, 'error');
					}
				},
				error: function() {
					self.showMessage(confiarCatalogMode.strings.error, 'error');
				},
				complete: function() {
					self.$submitBtn.prop('disabled', false).text(confiarCatalogMode.strings.submit);
				},
			});
		},

		validateForm: function() {
			var isValid = true;
			var name = $('#customer_name').val().trim();
			var email = $('#customer_email').val().trim();
			var phone = $('#customer_phone').val().trim();
			var cnpj = $('#customer_cnpj').val().replace(/\D/g, '');
			var cep = $('#customer_cep').val().replace(/\D/g, '');
			var quantity = parseInt($('#quantity').val(), 10);

			// Name validation
			if (name.length < 3) {
				this.showFieldError('customer_name', confiarCatalogMode.strings.invalidName);
				isValid = false;
			} else {
				this.clearFieldError('customer_name');
			}

			// Email validation
			if (!this.isValidEmail(email)) {
				this.showFieldError('customer_email', confiarCatalogMode.strings.invalidEmail);
				isValid = false;
			} else {
				this.clearFieldError('customer_email');
			}

			// Phone validation (required)
			if (phone.replace(/\D/g, '').length < 8) {
				this.showFieldError('customer_phone', confiarCatalogMode.strings.invalidPhone);
				isValid = false;
			} else {
				this.clearFieldError('customer_phone');
			}

			// CNPJ validation (optional, but if filled must have 14 digits)
			if (cnpj.length > 0 && cnpj.length !== 14) {
				this.showFieldError('customer_cnpj', confiarCatalogMode.strings.invalidCnpj);
				isValid = false;
			} else {
				this.clearFieldError('customer_cnpj');
			}

			// CEP validation (optional, but if filled must have 8 digits)
			if (cep.length > 0 && cep.length !== 8) {
				this.showFieldError('customer_cep', confiarCatalogMode.strings.invalidCep);
				isValid = false;
			} else {
				this.clearFieldError('customer_cep');
			}

			// Quantity validation
			if (isNaN(quantity) || quantity <= 0) {
				this.showFieldError('quantity', confiarCatalogMode.strings.invalidQty);
				isValid = false;
			} else {
				this.clearFieldError('quantity');
			}

			return isValid;
		},

		isValidEmail: function(email) {
			var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return re.test(email);
		},

		showFieldError: function(fieldId, message) {
			var $field = $('#' + fieldId);
			var $error = $field.siblings('.confiar-error');
			$error.text(message).addClass('show');
			$field.addClass('error');
		},

		clearFieldError: function(fieldId) {
			var $field = $('#' + fieldId);
			var $error = $field.siblings('.confiar-error');
			$error.removeClass('show').text('');
			$field.removeClass('error');
		},

		showMessage: function(message, type) {
			this.$message
				.text(message)
				.removeClass('success error')
				.addClass(type)
				.show();
		},
	};

	// Initialize on document ready
	$(document).ready(function() {
		ConfiarCatalogMode.init();
	});
})(jQuery);
