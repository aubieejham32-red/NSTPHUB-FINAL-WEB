<template>
  <div class="login-container">

    <!-- LEFT PANEL -->
    <div class="left-panel">
      <!-- Back Button with Rounded SVG Arrow -->
      <button class="back-btn" @click="goBack" aria-label="Go Back">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#EFEBE2" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="back-arrow-svg">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
      </button>

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

        <!-- Title: Create An Account -->
        <h1 class="title">
          Create An Account
        </h1>

        <!-- NAME -->
        <div class="form-group">
          <label>Name</label>
          <input
            type="text"
            v-model="form.name"
            placeholder="Enter your name"
            autocomplete="name"
            :class="{ 'input-error': form.errors.name }"
          />
          <span v-if="form.errors.name" class="error-text">{{ form.errors.name }}</span>
        </div>

        <!-- USERNAME -->
        <div class="form-group">
          <label>Username</label>
          <input
            type="text"
            v-model="form.username"
            placeholder="Enter your username"
            autocomplete="username"
            :class="{ 'input-error': form.errors.username }"
          />
          <span v-if="form.errors.username" class="error-text">{{ form.errors.username }}</span>
        </div>

        <!-- EMAIL -->
        <div class="form-group">
          <label>Email</label>
          <input
            type="email"
            v-model="form.email"
            placeholder="Enter your email"
            autocomplete="email"
            :class="{ 'input-error': form.errors.email }"
          />
          <span v-if="form.errors.email" class="error-text">{{ form.errors.email }}</span>
        </div>

        <!-- PASSWORD WITH FUNCTIONAL TOGGLE -->
        <div class="form-group">
          <label>Password</label>
          <div class="password-wrapper">
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              placeholder="Enter your password"
              autocomplete="new-password"
              :class="{ 'input-error': form.errors.password }"
            />
            <button 
              type="button" 
              class="toggle-password-btn" 
              @click="togglePasswordVisibility"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <!-- Eye Icon (Visible) -->
              <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#D99202" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <!-- Eye-Slash Icon (Hidden) -->
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#D99202" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
          <span v-if="form.errors.password" class="error-text">{{ form.errors.password }}</span>
        </div>

        <!-- SUBMIT BUTTON -->
        <div class="btn-container">
          <button
            class="create-btn"
            @click="handleCreateAccount"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Creating...' : 'Create Account' }}
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
})

// Visibility state tracking logic
const showPassword = ref(false)

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value
}

const goBack = () => {
    router.get('/superadmin/login')
}

const handleCreateAccount = () => {
    form.password_confirmation = form.password
    
    form.post('/superadmin/register', {
        onSuccess: () => {
            form.reset()
        }
    })
}
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
  /* Multi-directional flat stroke using #EFEBE2 to prevent blurring */
  filter: 
    drop-shadow(1px 1px 0px #EFEBE2)
    drop-shadow(-1px -1px 0px #EFEBE2)
    drop-shadow(1px -1px 0px #EFEBE2)
    drop-shadow(-1px 1px 0px #EFEBE2)
    /* A clean bottom shadow to ground the logo */
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
  font-size: 3.2rem;
  font-weight: 600;
  color: #54100F; 
  margin-bottom: 40px;
  margin-top: 0;
}

.form-group {
  margin-bottom: 28px;
  position: relative;
}

.form-group label {
  display: block;
  font-family: 'Lora', serif;
  font-size: 1.5rem;
  font-weight: 500;
  color: #233E47; 
  margin-bottom: 8px;
}

/* Positions inner button absolute relative to wrapper box */
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
  padding: 0 52px 0 20px; /* Increased right padding to prevent text overlay on the eye icon */
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

.form-group input.input-error {
  border-color: #ea3838;
}

/* Eye Toggle Layout Rules */
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

.error-text {
  color: #ea3838;
  font-size: 0.85rem;
  font-weight: 600;
  display: block;
  margin-top: 6px;
}

/* =====================
    BUTTONS
===================== */
.btn-container {
  display: flex;
  justify-content: center;
  margin-top: 40px;
}

.create-btn {
  width: 260px;
  height: 54px;
  border: 2px solid #D99202; 
  border-radius: 8px;
  background-color: #EFEBE2; 
  color: #D99202; 
  font-family: 'Lora', serif;
  font-size: 1.35rem;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0px 4px 10px rgba(217, 146, 2, 0.15);
  transition: background-color 0.2s ease, transform 0.1s ease;
}

.create-btn:hover:not(:disabled) {
  background-color: rgba(217, 146, 2, 0.08);
}

.create-btn:active:not(:disabled) {
  transform: scale(0.98);
}

.create-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
    font-size: 2.5rem;
  }
}
</style>