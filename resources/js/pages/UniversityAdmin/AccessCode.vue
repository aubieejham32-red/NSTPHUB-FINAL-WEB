<template>
  <div class="access-container">

    <!-- LEFT SIDE -->
    <LeftLogo />

    <!-- RIGHT SIDE CONTENT -->
    <div class="right-panel">
      <div class="form-card">

        <h1 class="main-heading">University Admin Access Code</h1>

        <p class="instruction-text">
          Access requires either the entry of the authorization code issued by
          your Super Administrator or direct navigation via your institution's
          dedicated portal link.
        </p>

        <form @submit.prevent="submitAccessCode" class="access-form">

          <div class="input-group">
            <label for="accessCode" class="input-label">
              University Access Code
            </label>

            <input
              id="accessCode"
              v-model="form.access_code"
              type="text"
              class="access-input"
              placeholder="Enter your access code"
              required
            />

            <span
              v-if="form.errors.access_code"
              class="error-message"
            >
              {{ form.errors.access_code }}
            </span>
          </div>

          <button
            type="submit"
            class="btn-continue"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Verifying...' : 'Continue' }}
          </button>

        </form>

      </div>
    </div>

  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import LeftLogo from '@/components/LeftLogo.vue';


const form = useForm({
  access_code: '',
});

const submitAccessCode = () => {
    form.post('/university-admin/access-code/verify', {
        preserveScroll: true,
    });
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

.access-container {
  display: flex;
  width: 100vw;
  height: 100vh;
  background: #EFEBE2;
  font-family: 'Plus Jakarta Sans', sans-serif;
  overflow: hidden;
}

/* ==========================================================================
   RIGHT PANEL
   ========================================================================== */

.right-panel {
  flex: 1;
  background: #EFEBE2;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 60px;
}

.form-card {
  width: 100%;
  max-width: 540px;
  display: flex;
  flex-direction: column;
}

.main-heading {
  color: #54100F;
  font-size: 2.3rem;
  font-weight: 700;
  text-align: center;
  margin-bottom: 16px;
  letter-spacing: -0.5px;
}

.instruction-text {
  color: #233E47;
  font-size: 0.95rem;
  line-height: 1.6;
  text-align: center;
  margin-bottom: 36px;
}

.access-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-label {
  color: #54100F;
  font-size: 0.95rem;
  font-weight: 700;
}

.access-input {
  width: 100%;
  height: 52px;
  padding: 0 20px;
  background: #FFFFFF;
  border: 2px solid #D99202;
  border-radius: 10px;
  font-size: 0.95rem;
  color: #233E47;
  outline: none;
  box-sizing: border-box;
  transition: border-color .2s ease;
}

.access-input::placeholder {
  color: #BEBEBE;
}

.access-input:focus {
  border-color: #58761C;
}

.error-message {
  color: #54100F;
  font-size: .85rem;
  font-weight: 600;
  margin-top: 4px;
}

.btn-continue {
  width: 100%;
  height: 52px;
  background: #D99202;
  color: #FFFFFF;
  border: none;
  border-radius: 26px;
  font-size: 1.05rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(217,146,2,.3);
  transition: .2s ease;
  margin-top: 8px;
}

.btn-continue:hover {
  background: #c08001;
  transform: translateY(-1px);
}

.btn-continue:active {
  transform: translateY(0);
}

.btn-continue:disabled {
  opacity: .7;
  cursor: not-allowed;
}

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (max-width: 900px) {
  .access-container {
    flex-direction: column;
    overflow-y: auto;
  }

  .right-panel {
    padding: 30px;
  }
}
</style>