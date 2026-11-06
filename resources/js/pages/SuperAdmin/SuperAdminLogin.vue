<template>
  <div class="login-container">

    <!-- LEFT PANEL -->
    <div class="left-panel">


      <!-- Badge Branding -->
      <div class="branding">
        <img
          src="/images/nstphub_logo.png"
          alt="NSTP HUB Logo"
          class="logo"
        />
        <h3>National Service Training Program</h3>
        <p class="branding-sub">hub</p>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
      <div class="login-card">

        <!-- Title: Log In -->
        <h1 class="title">
          Log <span>In</span>
        </h1>

        <!-- EMAIL -->
        <div class="form-group">
          <label>Email</label>
          <input
            type="email"
            v-model="email"
            placeholder="Enter your email"
          />
          <p v-if="errors.email" class="error-message">
    {{ firstError("email") }}
</p>
        </div>

        <!-- PASSWORD WITH FUNCTIONAL TOGGLE -->
        <div class="form-group">
          <label>Password</label>
          <div class="password-wrapper">
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="password"
              placeholder="Enter your password"
            />
            <button 
              type="button" 
              class="toggle-password-btn" 
              @click="togglePasswordVisibility"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <!-- Eye Icon (Visible state) -->
              <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#D99202" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <!-- Eye-Slash Icon (Hidden state) -->
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#D99202" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
          <p v-if="errors.password" class="error-message">
    {{ firstError("password") }}
</p>

<p v-if="errors.login" class="error-message">
    {{ firstError("login") }}
</p>
        </div>

        <!-- OPTIONS -->
        <div class="options">
          <label class="remember">
            <input type="checkbox" v-model="remember" />
            <span class="custom-checkbox"></span>
            <span class="remember-text">Remember me</span>
          </label>

          <Link href="/superadmin/forgot-password" class="forgot-link">
            Forgot Password?
          </Link>
        </div>

        <!-- LOGIN BUTTON -->
        <button
          type="button"
          class="login-btn"
          :disabled="loading"
          @click="login"
        >
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>

        <!-- REGISTER LINK -->
        <div class="register">
          <span class="register-prompt">Don't have an account?</span>
          <Link href="/superadmin/register" class="register-link">
            Create now
          </Link>
        </div>

        <!-- DIVIDER -->
        <div class="divider">
          <span>Or sign in with</span>
        </div>

        <!-- GOOGLE SIGN IN -->
        <div class="google-wrapper">
          <button type="button" class="google-btn" @click="loginWithGoogle">
            <img
              src="/images/google.png"
              alt="Google"
            />
            <span>Continue with Google</span>
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { Link, usePage } from "@inertiajs/vue3";

const SUPERADMIN_STORAGE_KEY =
    "savedSuperAdminAccounts";

const normalizeSuperAdmin = (account = {}) => ({
    ...account,
    id: account.id ?? null,
    name:
        account.name ??
        account.full_name ??
        account.username ??
        "Super Admin",
    email: account.email ?? "",
    photo:
        account.photo ??
        account.profile_photo ??
        account.avatar ??
        account.picture ??
        account.profile_picture ??
        "",
    account_type: "superadmin",
});

const email = ref("");
const password = ref("");
const remember = ref(false);

const page = usePage();

const loading = ref(false);
const errors = ref({
    ...(page.props.errors ?? {})
});

const firstError = (field) => {
    const value = errors.value?.[field];

    if (Array.isArray(value)) {
        return value[0] ?? "";
    }

    return value ? String(value) : "";
};

// Password visibility
const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const login = async () => {

    loading.value = true;
    errors.value = {};

    try {

        const response = await axios.post(
            "/superadmin/login",
            {
                email: email.value,
                password: password.value,
                remember: remember.value
            },
            {
                headers: {
                    Accept: "application/json"
                }
            }
        );

        // Save/refresh the authenticated Super Admin in a dedicated key.
        let accounts = [];

        try {
            const raw = localStorage.getItem(SUPERADMIN_STORAGE_KEY);
            accounts = raw ? JSON.parse(raw) : [];

            if (!Array.isArray(accounts)) {
                accounts = [];
            }
        } catch {
            accounts = [];
        }

        const currentAccount =
            normalizeSuperAdmin(response.data?.user ?? {});

        const index = accounts.findIndex(
            account =>
                Number(account?.id) === Number(currentAccount.id) ||
                String(account?.email ?? '').toLowerCase() ===
                    String(currentAccount.email ?? '').toLowerCase()
        );

        if (index === -1) {
            accounts.push(currentAccount);
        } else {
            accounts[index] = {
                ...accounts[index],
                ...currentAccount,
            };
        }

        localStorage.setItem(
            SUPERADMIN_STORAGE_KEY,
            JSON.stringify(accounts)
        );

        // Redirect to dashboard
        window.location.href = response.data.redirect;

    }
    catch (error) {

        if (error.response?.data?.errors) {

            errors.value = error.response.data.errors;

        }
        else if (error.response?.data?.message) {

            errors.value = {
                login: [error.response.data.message]
            };

        }
        else {

            errors.value = {
                login: ["Unable to connect to the server."]
            };

        }

    }
    finally {

        loading.value = false;

    }

};

const loginWithGoogle = () => {
    window.location.href = "/auth/google/superadmin";
};

</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

/* =====================
    GLOBAL CONTAINER
===================== */
.login-container {
  display: flex;
  height: 100vh;
  width: 100%;
  overflow: hidden;
  background-color: #EFEBE2; 
  font-family: 'Plus Jakarta Sans', sans-serif;
}

/* =====================
    LEFT PANEL
===================== */
.left-panel {
  width: 35%;
  max-width: 440px;
  min-width: 320px;
  background-color: #54100F;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px;
}

.back-btn {
  position: absolute;
  top: 48px;
  left: 48px;
  border: none;
  background: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease;
}

.back-arrow-svg {
  width: 34px;
  height: 34px;
  stroke: #EFEBE2;
}

.back-btn:hover {
  transform: translateX(-4px);
}

.branding {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo {
  width: 200px;
  height: auto;
  margin-bottom: 24px;
  filter: 
    drop-shadow(1px 1px 0px #EFEBE2)
    drop-shadow(-1px -1px 0px #EFEBE2)
    drop-shadow(1px -1px 0px #EFEBE2)
    drop-shadow(-1px 1px 0px #EFEBE2)
    drop-shadow(0px 4px 5px rgba(0, 0, 0, 0.6)); 
}

.branding h3 {
  color: #FFFFFF;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.5px;
}

.branding-sub {
  color: #FFFFFF;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.75rem;
  font-weight: 400;
  margin-top: 6px;
  opacity: 0.7;
  text-transform: lowercase;
}

/* =====================
    RIGHT PANEL
===================== */
.right-panel {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
}

.login-card {
  width: 100%;
  max-width: 580px;
}

/* =====================
    TYPOGRAPHY & FORM CONTROLS
===================== */
.title {
  text-align: center;
  font-family: 'Lora', serif;
  font-size: 4.5rem; 
  font-weight: 500;
  color: #54100F; 
  margin-bottom: 40px;
  margin-top: 0;
}

.title span {
  color: #D99202; 
}

.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  font-family: 'Lora', serif;
  font-size: 1.5rem;
  font-weight: 500;
  color: #233E47; 
  margin-bottom: 8px;
}

/* Relative container to securely place absolute toggle button */
.password-wrapper {
  position: relative;
  width: 100%;
}

.form-group input {
  width: 100%;
  height: 56px;
  border: 2px solid #D99202; 
  border-radius: 12px;
  background-color: #EEF2FB; 
  padding: 0 52px 0 20px; /* Right padding keeps your typed text from running under the toggle icon */
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 1rem;
  color: #000D12;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group input:focus {
  border-color: #FFBD36;
  box-shadow: 0 0 0 3px rgba(217, 146, 2, 0.15);
}

.form-group input::placeholder {
  color: #BEBEBE;
}

/* Layout context rules for the eye switch */
.toggle-password-btn {
  position: absolute;
  right: 18px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.eye-svg {
  width: 24px;
  height: 24px;
  transition: opacity 0.2s ease;
}

.toggle-password-btn:hover .eye-svg {
  opacity: 0.8;
}

/* =====================
    CUSTOM OPTIONS
===================== */
.options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 35px;
}

.remember {
  display: flex;
  align-items: center;
  cursor: pointer;
  position: relative;
  user-select: none;
}

.remember input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.custom-checkbox {
  height: 18px;
  width: 18px;
  border: 2px solid #D99202;
  border-radius: 3px;
  margin-right: 10px;
  display: inline-block;
  position: relative;
  box-sizing: border-box;
}

.remember input:checked ~ .custom-checkbox {
  background-color: #D99202;
}

.remember input:checked ~ .custom-checkbox::after {
  content: "";
  position: absolute;
  left: 4px;
  top: 1px;
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.remember-text {
  color: #D99202;
  font-size: 0.95rem;
  font-weight: 500;
}

.forgot-link {
  text-decoration: none;
  color: #D99202;
  font-size: 0.95rem;
  font-weight: 500;
}

.forgot-link:hover {
  text-decoration: underline;
}

/* =====================
    SUBMIT BUTTON
===================== */
.login-btn {
  width: 100%;
  height: 60px;
  border: none;
  border-radius: 30px;
  background-color: #D99202;
  color: #FFFFFF;
  font-family: 'Lora', serif;
  font-size: 1.8rem;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0px 4px 12px rgba(217, 146, 2, 0.3);
  transition: background-color 0.2s ease, transform 0.1s ease;
}

.login-btn:hover {
  background-color: #c18400;
}

.login-btn:active {
  transform: scale(0.99);
}

.login-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* =====================
    REGISTER PROMPT
===================== */
.register {
  text-align: center;
  margin-top: 28px;
}

.register-prompt {
  font-family: 'Lora', serif;
  font-size: 1.5rem;
  color: #233E47;
}

.register-link {
  margin-left: 10px;
  text-decoration: none;
  color: #D99202;
  font-family: 'Lora', serif;
  font-size: 1.5rem;
  font-weight: 500;
}

.register-link:hover {
  text-decoration: underline;
}

/* =====================
    DIVIDER LINE
===================== */
.divider {
  margin: 35px 0;
  display: flex;
  align-items: center;
  text-align: center;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 2px;
  background-color: #233E47;
}

.divider span {
  margin: 0 16px;
  color: #233E47;
  font-size: 0.9rem;
  font-weight: 500;
}

/* =====================
    GOOGLE SIGN IN
===================== */
.google-wrapper {
  display: flex;
  justify-content: center;
}

.google-btn {
  width: 260px;
  height: 52px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  border: 2px solid #D99202;
  border-radius: 8px;
  background-color: #EFEBE2; 
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.google-btn:hover {
  background-color: rgba(217, 146, 2, 0.08);
}

.google-btn img {
  width: 24px;
  height: 24px;
}

.google-btn span {
  color: #D99202;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 1.25rem;
  font-weight: 600;
}

/* =====================
    RESPONSIVE DESIGN
===================== */
@media (max-width: 900px) {
  .left-panel {
    display: none;
  }
  .right-panel {
    padding: 24px;
  }
  .login-card {
    width: 100%;
    max-width: 100%;
  }
  .title {
    font-size: 3.5rem;
  }
}

.error-message {
    color: #dc2626;
    font-size: 14px;
    margin-top: 6px;
    margin-left: 4px;
    font-weight: 500;
}
</style>