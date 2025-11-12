// ==================== Dark Mode Toggle ====================
window.addEventListener('DOMContentLoaded', () => {
  const themeToggle = document.getElementById("themeToggle");
  if (themeToggle) {
    if (localStorage.getItem("theme") === "dark") document.body.classList.add("dark-mode");
    themeToggle.addEventListener("click", () => {
      document.body.classList.toggle("dark-mode");
      localStorage.setItem("theme", document.body.classList.contains("dark-mode") ? "dark" : "light");
    });
  }
});

// ==================== Helper Functions ====================
function showErrorMessage(input, message) {
  input.classList.add('is-invalid');
  const msg = document.createElement('div');
  msg.className = 'invalid-feedback';
  msg.innerText = message;
  input.parentNode.appendChild(msg);
}

function clearErrors(form) {
  form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid'));
  form.querySelectorAll('.invalid-feedback').forEach(e => e.remove());
}

function showGeneralError(message) {
  let errorDiv = document.getElementById('errorScreen');
  if (!errorDiv) {
    errorDiv = document.createElement('div');
    errorDiv.id = 'errorScreen';
    errorDiv.className = 'alert alert-danger text-center';
    errorDiv.style.margin = '20px auto';
    errorDiv.style.maxWidth = '600px';
    errorDiv.style.borderRadius = '10px';
    const formContainer = document.getElementById('registerForm');
    formContainer.parentNode.insertBefore(errorDiv, formContainer);
  }
  errorDiv.innerText = message;
  errorDiv.style.display = 'block';
}

// ==================== Live Username/Email Check ====================
function checkAvailability(field, value) {
  const data = new FormData();
  data.append('field', field);
  data.append('value', value);

  fetch('check_availability.php', { method: 'POST', body: data })
    .then(res => res.json())
    .then(res => {
      const input = document.querySelector(`[name="${field}"]`);
      input.classList.remove('is-invalid');
      const existingMsg = input.parentNode.querySelector('.invalid-feedback');
      if (existingMsg) existingMsg.remove();
      if (!res.available) showErrorMessage(input, `${field.charAt(0).toUpperCase() + field.slice(1)} already exists`);
    })
    .catch(err => console.error(err));
}

function debounce(fn, delay = 500) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), delay);
  };
}

// ==================== Register Form ====================
const registerForm = document.getElementById('registerForm');
if (registerForm) {
  const emailInput = registerForm.querySelector('[name="email"]');
  const usernameInput = registerForm.querySelector('[name="username"]');

  emailInput.addEventListener('input', debounce(() => checkAvailability('email', emailInput.value.trim())));
  usernameInput.addEventListener('input', debounce(() => checkAvailability('username', usernameInput.value.trim())));

  registerForm.addEventListener('submit', e => {
    e.preventDefault();
    clearErrors(registerForm);

    const formData = new FormData(registerForm);
    const submitBtn = registerForm.querySelector('[type="submit"]');

    // Save original text
    const originalText = submitBtn.innerHTML;

    // ===== Add loading state =====
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-75');
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Registering...`;

    fetch('register.php', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        // ===== Restore button =====
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-75');
        submitBtn.innerHTML = originalText;

        if (data.success) {
          let successScreen = document.getElementById('successScreen');
          if (!successScreen) {
            successScreen = document.createElement('div');
            successScreen.id = 'successScreen';
            successScreen.className = 'success-screen text-center';
            successScreen.style.padding = '30px';
            registerForm.parentNode.appendChild(successScreen);
          }

          registerForm.style.display = 'none';
          successScreen.style.display = 'block';
          successScreen.innerHTML = `
            <div class="mb-3">
              <i class="bi bi-check-circle-fill" style="font-size:48px; color: #28a745;"></i>
            </div>
            <h4 class="mb-2">Registration Successful</h4>
            <p class="mb-3">Please check your email to activate your account.</p>
          `;

          registerForm.reset();
        } else if (data.errors) {
          for (const field in data.errors) {
            const input = registerForm.querySelector(`[name="${field}"]`);
            if (input) showErrorMessage(input, data.errors[field]);
            else showGeneralError(data.errors[field]);
          }
        } else if (data.message) {
          showGeneralError(data.message);
        }
      })
      .catch(err => {
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-75');
        submitBtn.innerHTML = originalText;
        showGeneralError('Server error. Check console for details.');
      });
  });

  registerForm.querySelectorAll('input').forEach(input => input.addEventListener('input', () => input.classList.remove('is-invalid')));
}

// ==================== Login AJAX ====================
const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');

if (loginForm) {
  loginForm.addEventListener('submit', e => {
    e.preventDefault();

    loginMessage.innerHTML = '';
    loginForm.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid'));
    loginForm.querySelectorAll('.invalid-feedback').forEach(f => f.remove());

    const formData = new FormData(loginForm);
    const submitBtn = loginForm.querySelector('[type="submit"]');

    // ===== Add loading state =====
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-75');
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...`;

    fetch('login.php', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        // ===== Restore button =====
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-75');
        submitBtn.innerHTML = originalText;

        if (data.success) {
          loginMessage.innerHTML = `<div class="alert alert-success">Login successful! Redirecting...</div>`;
          setTimeout(() => window.location.href = 'dashboard.php', 1200);
        } else {
          if (data.errors) {
            for (const field in data.errors) {
              const input = loginForm.querySelector(`[name="${field}"]`);
              if (input) {
                input.classList.add('is-invalid');
                const msg = document.createElement('div');
                msg.className = 'invalid-feedback';
                msg.innerText = data.errors[field];
                input.parentNode.appendChild(msg);
              }
            }
          } else if (data.message) {
            loginMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
          }
        }
      })
      .catch(err => {
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-75');
        submitBtn.innerHTML = originalText;
        loginMessage.innerHTML = `<div class="alert alert-danger">Server error. Check console.</div>`;
      });
  });
}
