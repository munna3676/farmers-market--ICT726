/* =========================================================
   Client-side validation (progressive enhancement).
   Server-side validation in PHP is the real security boundary;
   this just gives instant feedback to the user.
   ========================================================= */

function showError(input, message) {
  const group = input.closest('.form-group') || input.parentElement;
  let errorEl = group.querySelector('.error-text');
  if (!errorEl) {
    errorEl = document.createElement('div');
    errorEl.className = 'error-text';
    group.appendChild(errorEl);
  }
  errorEl.textContent = message;
  input.setAttribute('aria-invalid', 'true');
}

function clearError(input) {
  const group = input.closest('.form-group') || input.parentElement;
  const errorEl = group.querySelector('.error-text');
  if (errorEl) errorEl.textContent = '';
  input.removeAttribute('aria-invalid');
}

document.addEventListener('DOMContentLoaded', function () {
  const forms = document.querySelectorAll('form[data-validate]');

  forms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      let valid = true;

      form.querySelectorAll('[required]').forEach(function (input) {
        clearError(input);
        if (!input.value.trim()) {
          showError(input, 'This field is required.');
          valid = false;
        }
      });

      form.querySelectorAll('input[type="email"]').forEach(function (input) {
        if (input.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
          showError(input, 'Please enter a valid email address.');
          valid = false;
        }
      });

      const password = form.querySelector('input[name="password"]');
      if (password && password.value && password.value.length < 8) {
        showError(password, 'Password must be at least 8 characters.');
        valid = false;
      }

      const confirmPassword = form.querySelector('input[name="confirm_password"]');
      if (confirmPassword && password && confirmPassword.value !== password.value) {
        showError(confirmPassword, 'Passwords do not match.');
        valid = false;
      }

      const postcode = form.querySelector('input[name="postcode"], input[name="delivery_postcode"]');
      if (postcode && postcode.value && !/^\d{4}$/.test(postcode.value)) {
        showError(postcode, 'Postcode must be exactly 4 digits.');
        valid = false;
      }

      const phone = form.querySelector('input[name="phone"]');
      if (phone && phone.value && !/^(\+?61|0)[2-478]\d{8}$/.test(phone.value.replace(/\s+/g, ''))) {
        showError(phone, 'Please enter a valid Australian phone number.');
        valid = false;
      }

      const qty = form.querySelector('input[name="quantity"]');
      if (qty && qty.value && (isNaN(qty.value) || Number(qty.value) < 1)) {
        showError(qty, 'Quantity must be at least 1.');
        valid = false;
      }

      if (!valid) e.preventDefault();
    });
  });
});
