(function () {
	'use strict';

	if (typeof window.iwonData === 'undefined') {
		return;
	}

	var data = window.iwonData;

	/**
	 * Prosta walidacja adresu e-mail po stronie klienta.
	 */
	function isValidEmail(value) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
	}

	function setMessage(el, text, type) {
		el.textContent = text;
		el.className = 'iwon-notify__message';
		if (type) {
			el.classList.add('iwon-notify__message--' + type);
		}
	}

	/**
	 * Resetuje widget hCaptcha po nieudanej próbie, aby umożliwić ponowienie.
	 */
	function resetHcaptcha() {
		if (typeof window.hcaptcha === 'undefined') {
			return;
		}
		try {
			window.hcaptcha.reset();
		} catch (e) {
			// Brak aktywnego widgetu – ignorujemy.
		}
	}

	function initContainer(container) {
		var form = container.querySelector('.iwon-notify__form');
		var input = container.querySelector('.iwon-notify__input');
		var button = container.querySelector('.iwon-notify__submit');
		var message = container.querySelector('.iwon-notify__message');
		var productId = container.getAttribute('data-iwon-product');

		if (!form || !input || !button || !message) {
			return;
		}

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			var email = input.value.trim();

			if (!isValidEmail(email)) {
				setMessage(message, data.i18n.invalidEmail, 'error');
				input.focus();
				return;
			}

			// Stan „wysyłanie".
			button.disabled = true;
			input.disabled = true;
			container.classList.add('iwon-notify--loading');
			setMessage(message, data.i18n.sending, 'loading');

			// Wysyłamy całą zawartość formularza, aby do serwera trafiły wszystkie
			// pola hCaptcha: token, identyfikator widgetu oraz ukryte pola anti-spam
			// (honeypot, znacznik czasu, sygnatura). Wybiórcze przekazywanie pól
			// gubiło część z nich, przez co weryfikacja zwracała „Anti-spam check
			// failed.". Body typu FormData wymusza multipart – nie ustawiamy
			// nagłówka Content-Type ręcznie, robi to przeglądarka.
			var body = new FormData(form);
			body.set('action', data.action);
			body.set('nonce', data.nonce);
			body.set('email', email);
			body.set('product_id', productId);

			fetch(data.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			})
				.then(function (response) {
					return response.json().then(function (json) {
						return { ok: response.ok, json: json };
					});
				})
				.then(function (result) {
					var json = result.json || {};
					var payload = json.data || {};

					if (json.success) {
						setMessage(message, payload.message || data.i18n.success, 'success');
						// Po sukcesie ukrywamy pola formularza, zostawiając komunikat.
						input.style.display = 'none';
						button.style.display = 'none';
					} else {
						setMessage(message, payload.message || data.i18n.errorGeneric, 'error');
						button.disabled = false;
						input.disabled = false;
						resetHcaptcha();
					}
				})
				.catch(function () {
					setMessage(message, data.i18n.errorGeneric, 'error');
					button.disabled = false;
					input.disabled = false;
					resetHcaptcha();
				})
				.finally(function () {
					container.classList.remove('iwon-notify--loading');
				});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var containers = document.querySelectorAll('.iwon-notify');
		for (var i = 0; i < containers.length; i++) {
			initContainer(containers[i]);
		}
	});
})();
