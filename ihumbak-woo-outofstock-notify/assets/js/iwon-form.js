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
	 * Sprawdza, czy pole formularza pochodzi z widgetu hCaptcha
	 * (odpowiedź, id widgetu, nonce, podpis) i powinno trafić do żądania.
	 */
	function isHcaptchaField(key) {
		return (
			key === 'iwon_notify_nonce' ||
			key.indexOf('h-captcha') === 0 ||
			key.indexOf('g-recaptcha') === 0 ||
			key.indexOf('hcaptcha') === 0
		);
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

			var body = new URLSearchParams();
			body.append('action', data.action);
			body.append('nonce', data.nonce);
			body.append('email', email);
			body.append('product_id', productId);

			// Dołącz pola hCaptcha (jeśli widget jest obecny w formularzu).
			new FormData(form).forEach(function (value, key) {
				if (isHcaptchaField(key)) {
					body.append(key, value);
				}
			});

			fetch(data.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
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
