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

			// Phone mask: (XX) XXXXX-XXXX (mobile) or (XX) XXXX-XXXX (landline)
			$('#customer_phone').on('input', function() {
				var d = $(this).val().replace(/\D/g, '').substring(0, 11);
				var v = d;
				if (d.length > 10) {
					v = '(' + d.substring(0, 2) + ') ' + d.substring(2, 7) + '-' + d.substring(7);
				} else if (d.length > 6) {
					v = '(' + d.substring(0, 2) + ') ' + d.substring(2, 6) + '-' + d.substring(6);
				} else if (d.length > 2) {
					v = '(' + d.substring(0, 2) + ') ' + d.substring(2);
				} else if (d.length > 0) {
					v = '(' + d;
				}
				$(this).val(v);
			});

			// CNPJ mask: XX.XXX.XXX/XXXX-XX
			$('#customer_cnpj').on('input', function() {
				var d = $(this).val().replace(/\D/g, '').substring(0, 14);
				var v = d;
				if (d.length > 12) {
					v = d.substring(0, 2) + '.' + d.substring(2, 5) + '.' + d.substring(5, 8) + '/' + d.substring(8, 12) + '-' + d.substring(12);
				} else if (d.length > 8) {
					v = d.substring(0, 2) + '.' + d.substring(2, 5) + '.' + d.substring(5, 8) + '/' + d.substring(8);
				} else if (d.length > 5) {
					v = d.substring(0, 2) + '.' + d.substring(2, 5) + '.' + d.substring(5);
				} else if (d.length > 2) {
					v = d.substring(0, 2) + '.' + d.substring(2);
				}
				$(this).val(v);
				// Clear lookup feedback whenever the user edits the field
				if (d.length < 14) {
					$('#cnpj-feedback').text('').removeClass('success error loading warning');
					$('#customer_company').val('');
				}
			});

			// CNPJ lookup on blur — calls Brasil API (free, no key needed)
			$('#customer_cnpj').on('blur', function() {
				var cnpj = $(this).val().replace(/\D/g, '');
				if (cnpj.length === 14 && ConfiarCatalogMode.isValidCNPJ(cnpj)) {
					ConfiarCatalogMode.lookupCnpj(cnpj);
				}
			});

			// CEP mask: XXXXX-XXX
			$('#customer_cep').on('input', function() {
				var v = $(this).val().replace(/\D/g, '').substring(0, 8);
				if (v.length > 5) { v = v.substring(0, 5) + '-' + v.substring(5); }
				$(this).val(v);
				if (v.replace(/\D/g, '').length < 8) {
					$('#cep-feedback').text('').removeClass('success error loading');
					$('#customer_city, #customer_state, #customer_neighborhood, #customer_address').val('');
				}
			});

			// CEP lookup on blur
			$('#customer_cep').on('blur', function() {
				var cep = $(this).val().replace(/\D/g, '');
				if (cep.length === 8) {
					ConfiarCatalogMode.lookupCep(cep);
				}
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
			$('#cep-feedback').text('').removeClass('success error loading');
			$('#cnpj-feedback').text('').removeClass('success error loading warning');
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
			$('#cep-feedback, #cnpj-feedback').text('').removeClass('success error loading warning');
		},

		submitForm: function() {
			var self = this;

			if (!this.validateForm()) {
				return;
			}

			var formData = {
				action: 'confiar_submit_quote',
				nonce: confiarCatalogMode.nonce,
				customer_name:         $('#customer_name').val(),
				customer_email:        $('#customer_email').val(),
				customer_phone:        $('#customer_phone').val(),
				customer_cnpj:         $('#customer_cnpj').val(),
				customer_company:      $('#customer_company').val(),
				customer_cep:          $('#customer_cep').val(),
				customer_city:         $('#customer_city').val(),
				customer_state:        $('#customer_state').val(),
				customer_neighborhood: $('#customer_neighborhood').val(),
				customer_address:      $('#customer_address').val(),
				product_id:            $('#product_id').val(),
				quantity:              $('#quantity').val(),
				message:               $('#message').val(),
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

			// CNPJ validation (optional, full digit-verifier algorithm)
			if (cnpj.length > 0 && !this.isValidCNPJ(cnpj)) {
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

		isValidCNPJ: function(cnpj) {
			if (cnpj.length !== 14) return false;
			if (/^(\d)\1+$/.test(cnpj)) return false; // rejects 00000000000000, 11111111111111...
			var calcDigit = function(cnpj, len) {
				var sum = 0, pos = len - 7;
				for (var i = len; i >= 1; i--) {
					sum += parseInt(cnpj.charAt(len - i)) * pos--;
					if (pos < 2) pos = 9;
				}
				var r = sum % 11 < 2 ? 0 : 11 - (sum % 11);
				return r;
			};
			return calcDigit(cnpj, 12) === parseInt(cnpj.charAt(12)) &&
			       calcDigit(cnpj, 13) === parseInt(cnpj.charAt(13));
		},

		// Consulta CNPJ na Brasil API (brasilapi.com.br) — gratuita, sem chave de API
		lookupCnpj: function(cnpj) {
			var $feedback = $('#cnpj-feedback');
			$feedback.text(confiarCatalogMode.strings.cnpjSearching)
			         .removeClass('success error warning').addClass('loading');

			$.getJSON('https://brasilapi.com.br/api/cnpj/v1/' + cnpj)
				.done(function(data) {
					var razao    = data.razao_social || '';
					var fantasia = (data.nome_fantasia || '').trim();
					var situacao = data.descricao_situacao_cadastral || '';
					var isAtiva  = situacao.toUpperCase() === 'ATIVA';

					// Show company name + situation in feedback
					var icon = isAtiva ? '✓' : '⚠';
					var feedbackText = icon + ' ' + razao;
					if (situacao) feedbackText += ' — ' + situacao;
					$feedback.text(feedbackText)
					         .removeClass('loading error')
					         .addClass(isAtiva ? 'success' : 'warning');

					// Save razão social in hidden field (sent with form)
					$('#customer_company').val(razao);

					// Pre-fill customer name only if the field is still empty
					if (!$('#customer_name').val().trim()) {
						$('#customer_name').val(fantasia || razao);
						ConfiarCatalogMode.clearFieldError('customer_name');
					}

					// Fill address fields from CNPJ data
					var cepDigits = String(data.cep || '').replace(/\D/g, '');
					if (cepDigits.length === 8) {
						$('#customer_cep').val(cepDigits.substring(0, 5) + '-' + cepDigits.substring(5));
					}

					var address = data.logradouro || '';
					if (address && data.numero) address += ', ' + data.numero;

					$('#customer_city').val(data.municipio || '');
					$('#customer_state').val(data.uf || '');
					$('#customer_neighborhood').val(data.bairro || '');
					$('#customer_address').val(address);

					// Also update CEP feedback with location
					if (data.municipio && data.uf) {
						var cepDisplay = data.municipio + ' - ' + data.uf;
						if (data.bairro) cepDisplay += ', ' + data.bairro;
						$('#cep-feedback').text('✓ ' + cepDisplay)
						                  .removeClass('loading error').addClass('success');
					}
				})
				.fail(function(jqXHR) {
					$('#customer_company').val('');
					if (jqXHR.status === 404) {
						$feedback.text(confiarCatalogMode.strings.cnpjNotFound)
						         .removeClass('loading success warning').addClass('error');
					} else {
						// Network error or CORS — silently clear
						$feedback.text('').removeClass('loading success warning error');
					}
				});
		},

		lookupCep: function(cep) {
			var $feedback = $('#cep-feedback');
			$feedback.text(confiarCatalogMode.strings.cepSearching)
			         .removeClass('success error').addClass('loading');

			$.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
				if (data.erro) {
					$feedback.text(confiarCatalogMode.strings.cepNotFound)
					         .removeClass('loading success').addClass('error');
					$('#customer_city, #customer_state, #customer_neighborhood, #customer_address').val('');
				} else {
					var display = data.localidade + ' - ' + data.uf;
					if (data.bairro) display += ', ' + data.bairro;
					$feedback.text('✓ ' + display)
					         .removeClass('loading error').addClass('success');
					$('#customer_city').val(data.localidade || '');
					$('#customer_state').val(data.uf || '');
					$('#customer_neighborhood').val(data.bairro || '');
					$('#customer_address').val(data.logradouro || '');
				}
			}).fail(function() {
				$feedback.text('').removeClass('loading error success');
			});
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
