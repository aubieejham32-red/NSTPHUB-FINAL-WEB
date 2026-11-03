<template>
  <div class="access-container">

    <!-- LEFT SIDE -->
    <LeftLogo />

    <!-- RIGHT SIDE CONTENT -->
    <div class="right-panel">
      <div class="form-card">

        <!-- PAGE TITLE -->
        <h1 class="main-heading">
          Instructor & Coordinator Login
        </h1>

        <!-- INSTRUCTION -->
        <p class="instruction-text">
          Access requires either the entry of the authorization code issued by
          your Super Administrator or direct navigation via your institution's
          dedicated portal link.
        </p>

        <!-- ACCESS CODE FORM -->
        <form
          @submit.prevent="submitAccessCode"
          class="access-form"
        >

          <!-- ACCESS CODE -->
          <div class="input-group">

            <label
              for="accessCode"
              class="input-label"
            >
              University Access Code
            </label>

            <input
              id="accessCode"
              v-model="form.access_code"
              type="text"
              class="access-input"
              placeholder="Enter your access code"
              autocomplete="off"
              required
            />

            <!-- VALIDATION ERROR -->
            <span
              v-if="form.errors.access_code"
              class="error-message"
            >
              {{ form.errors.access_code }}
            </span>

          </div>

          <!-- CONTINUE BUTTON -->
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

import {
  useForm,
} from '@inertiajs/vue3';

import LeftLogo
  from '@/components/LeftLogo.vue';


/*
|--------------------------------------------------------------------------
| Access Code Form
|--------------------------------------------------------------------------
*/

const form = useForm({

  access_code: '',

});


/*
|--------------------------------------------------------------------------
| Submit Access Code
|--------------------------------------------------------------------------
|
| Instructor and Coordinator use the shared university access code.
|
*/

const submitAccessCode = () => {

  form.post(
    '/instructor-coordinator/access-code/verify',
    {

      preserveScroll: true,

    }
  );

};

</script>


<style scoped>

@import url(
  'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/* ==========================================================================
   MAIN CONTAINER
   ========================================================================== */

.access-container {

  display: flex;

  width: 100vw;

  height: 100vh;

  background: #EFEBE2;

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

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


/* ==========================================================================
   FORM CARD
   ========================================================================== */

.form-card {

  width: 100%;

  max-width: 540px;

  display: flex;

  flex-direction: column;

}


/* ==========================================================================
   MAIN HEADING
   ========================================================================== */

.main-heading {

  color: #54100F;

  font-size: 2.3rem;

  font-weight: 700;

  text-align: center;

  margin-bottom: 16px;

  letter-spacing: -0.5px;

}


/* ==========================================================================
   INSTRUCTION TEXT
   ========================================================================== */

.instruction-text {

  color: #233E47;

  font-size: 0.95rem;

  line-height: 1.6;

  text-align: center;

  margin-bottom: 36px;

}


/* ==========================================================================
   ACCESS FORM
   ========================================================================== */

.access-form {

  display: flex;

  flex-direction: column;

  gap: 24px;

}


/* ==========================================================================
   INPUT GROUP
   ========================================================================== */

.input-group {

  display: flex;

  flex-direction: column;

  gap: 8px;

}


/* ==========================================================================
   INPUT LABEL
   ========================================================================== */

.input-label {

  color: #54100F;

  font-size: 0.95rem;

  font-weight: 700;

}


/* ==========================================================================
   ACCESS INPUT
   ========================================================================== */

.access-input {

  width: 100%;

  height: 52px;

  padding:
    0
    20px;

  background: #FFFFFF;

  border:
    2px
    solid
    #D99202;

  border-radius: 10px;

  font-size: 0.95rem;

  color: #233E47;

  outline: none;

  box-sizing: border-box;

  transition:
    border-color
    0.2s
    ease;

}


/* ==========================================================================
   PLACEHOLDER
   ========================================================================== */

.access-input::placeholder {

  color: #BEBEBE;

}


/* ==========================================================================
   INPUT FOCUS
   ========================================================================== */

.access-input:focus {

  border-color: #58761C;

}


/* ==========================================================================
   ERROR MESSAGE
   ========================================================================== */

.error-message {

  color: #54100F;

  font-size: 0.85rem;

  font-weight: 600;

  margin-top: 4px;

}


/* ==========================================================================
   CONTINUE BUTTON
   ========================================================================== */

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

  box-shadow:
    0
    4px
    10px
    rgba(
      217,
      146,
      2,
      0.3
    );

  transition:
    0.2s
    ease;

  margin-top: 8px;

}


/* ==========================================================================
   CONTINUE BUTTON HOVER
   ========================================================================== */

.btn-continue:hover {

  background: #c08001;

  transform:
    translateY(
      -1px
    );

}


/* ==========================================================================
   CONTINUE BUTTON ACTIVE
   ========================================================================== */

.btn-continue:active {

  transform:
    translateY(
      0
    );

}


/* ==========================================================================
   CONTINUE BUTTON DISABLED
   ========================================================================== */

.btn-continue:disabled {

  opacity: 0.7;

  cursor: not-allowed;

}


/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (
  max-width: 900px
) {

  .access-container {

    flex-direction: column;

    overflow-y: auto;

  }


  .right-panel {

    padding: 30px;

  }

}

</style>