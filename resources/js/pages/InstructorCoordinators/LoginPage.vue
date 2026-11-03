<template>
  <div class="login-container">

    <!-- LEFT SIDE -->
    <LeftLogo />

    <!-- RIGHT SIDE -->
    <div class="right-panel">

      <div class="login-card">

        <!-- ==========================================================
             TITLE
        =========================================================== -->

        <h1 class="title">
          Log <span>In</span>
        </h1>


        <!-- ==========================================================
             LOGIN FORM
        =========================================================== -->

        <form
          class="login-form"
          @submit.prevent="submitLogin"
        >

          <!-- ========================================================
               EMAIL
          ========================================================= -->

          <div class="form-group">

            <label for="email">
              Email
            </label>

            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="Enter your email"
              autocomplete="email"
              required
            >


            <!-- EMAIL ERROR -->

            <p
              v-if="form.errors.email"
              class="error-message"
            >
              {{ form.errors.email }}
            </p>

          </div>


          <!-- ========================================================
               PASSWORD
          ========================================================= -->

          <div class="form-group">

            <label for="password">
              Password
            </label>


            <div class="password-wrapper">

              <input
                id="password"
                v-model="form.password"
                :type="
                  showPassword
                    ? 'text'
                    : 'password'
                "
                placeholder="Enter your password"
                autocomplete="current-password"
                required
              >


              <!-- ====================================================
                   SHOW / HIDE PASSWORD
              ===================================================== -->

              <button
                type="button"
                class="toggle-password-btn"
                :aria-label="
                  showPassword
                    ? 'Hide password'
                    : 'Show password'
                "
                @click="togglePasswordVisibility"
              >

                <!-- EYE -->

                <svg
                  v-if="showPassword"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="#D99202"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="eye-svg"
                >

                  <path
                    d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8"
                  />

                  <circle
                    cx="12"
                    cy="12"
                    r="3"
                  />

                </svg>


                <!-- EYE SLASH -->

                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="#D99202"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="eye-svg"
                >

                  <path
                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"
                  />

                  <path
                    d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"
                  />

                  <path
                    d="M14.12 14.12a3 3 0 1 1-4.24-4.24"
                  />

                  <line
                    x1="1"
                    y1="1"
                    x2="23"
                    y2="23"
                  />

                </svg>

              </button>

            </div>


            <!-- PASSWORD ERROR -->

            <p
              v-if="form.errors.password"
              class="error-message"
            >
              {{ form.errors.password }}
            </p>


            <!-- LOGIN ERROR -->

            <p
              v-if="form.errors.login"
              class="error-message"
            >
              {{ form.errors.login }}
            </p>

          </div>


          <!-- ========================================================
               OPTIONS
          ========================================================= -->

          <div class="options">

            <!-- ======================================================
                 REMEMBER ME
            ======================================================= -->

            <label class="remember">

              <input
                v-model="form.remember"
                type="checkbox"
              >

              <span class="custom-checkbox"></span>

              <span class="remember-text">
                Remember me
              </span>

            </label>


            <!-- ======================================================
                 FORGOT PASSWORD
            ======================================================= -->

            <Link
              href="/instructor-coordinator/forgot-password"
              class="forgot-link"
            >
              Forgot Password?
            </Link>

          </div>


          <!-- ========================================================
               LOGIN BUTTON
          ========================================================= -->

          <button
            type="submit"
            class="login-btn"
            :disabled="form.processing"
          >
            {{
              form.processing
                ? 'Logging in...'
                : 'Login'
            }}
          </button>

        </form>


        <!-- ==========================================================
             DIVIDER
        =========================================================== -->

        <div class="divider">

          <span>
            Or sign in with
          </span>

        </div>


        <!-- ==========================================================
             GOOGLE
        =========================================================== -->

        <div class="google-wrapper">

          <a
            href="/auth/google/instructor-coordinator"
            class="google-btn"
          >

            <img
              src="/images/google.png"
              alt="Google"
            >

            <span>
              Continue with Google
            </span>

          </a>

        </div>

      </div>

    </div>

  </div>
</template>


<script setup>

import {
  ref,
} from 'vue';

import {
  Link,
  useForm,
} from '@inertiajs/vue3';

import LeftLogo
  from '@/components/LeftLogo.vue';


/*
|--------------------------------------------------------------------------
| Password Visibility
|--------------------------------------------------------------------------
*/

const showPassword =
  ref(
    false
  );


/*
|--------------------------------------------------------------------------
| Login Form
|--------------------------------------------------------------------------
*/

const form =
  useForm({

    email:
      '',

    password:
      '',

    remember:
      false,

  });


/*
|--------------------------------------------------------------------------
| Toggle Password Visibility
|--------------------------------------------------------------------------
*/

const togglePasswordVisibility = () => {

  showPassword.value =
    !showPassword.value;

};


/*
|--------------------------------------------------------------------------
| Submit Login
|--------------------------------------------------------------------------
|
| Shared by:
|
| Instructor
| Coordinator - Attendance
| Coordinator - Announcement
| Coordinator - Schedule
|
*/

const submitLogin = () => {

  form.post(
    '/instructor-coordinator/login',
    {

      /*
      |--------------------------------------------------------------------------
      | Preserve Scroll
      |--------------------------------------------------------------------------
      */

      preserveScroll:
        true,


      /*
      |--------------------------------------------------------------------------
      | Success
      |--------------------------------------------------------------------------
      */

      onSuccess: () => {

        /*
        |--------------------------------------------------------------------------
        | Laravel handles the redirect.
        |--------------------------------------------------------------------------
        */

      },


      /*
      |--------------------------------------------------------------------------
      | Error
      |--------------------------------------------------------------------------
      */

      onError: () => {

        /*
        |--------------------------------------------------------------------------
        | Clear Password Only
        |--------------------------------------------------------------------------
        */

        form.reset(
          'password'
        );

      },

    }
  );

};

</script>


<style scoped>

@import url(
  'https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/* ==========================================================
   GLOBAL
========================================================== */

.login-container {

  display:
    flex;

  width:
    100%;

  height:
    100vh;

  overflow:
    hidden;

  background:
    #EFEBE2;

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

}


/* ==========================================================
   RIGHT PANEL
========================================================== */

.right-panel {

  flex:
    1;

  display:
    flex;

  justify-content:
    center;

  align-items:
    center;

  padding:
    48px;

  background:
    #EFEBE2;

}


.login-card {

  width:
    100%;

  max-width:
    580px;

}


/* ==========================================================
   TITLE
========================================================== */

.title {

  text-align:
    center;

  font-family:
    'Lora',
    serif;

  font-size:
    4.5rem;

  font-weight:
    500;

  color:
    #54100F;

  margin:
    0
    0
    42px;

}


.title span {

  color:
    #D99202;

}


/* ==========================================================
   FORM
========================================================== */

.login-form {

  display:
    flex;

  flex-direction:
    column;

}


.form-group {

  margin-bottom:
    24px;

}


.form-group label {

  display:
    block;

  margin-bottom:
    8px;

  font-family:
    'Lora',
    serif;

  font-size:
    1.45rem;

  font-weight:
    500;

  color:
    #233E47;

}


.form-group input {

  width:
    100%;

  height:
    58px;

  padding:
    0
    20px;

  border:
    2px
    solid
    #D99202;

  border-radius:
    12px;

  background:
    #EEF2FB;

  font-size:
    1rem;

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

  color:
    #233E47;

  outline:
    none;

  box-sizing:
    border-box;

  transition:
    0.2s
    ease;

}


.form-group input::placeholder {

  color:
    #BEBEBE;

}


.form-group input:focus {

  border-color:
    #FFBD36;

  box-shadow:
    0
    0
    0
    3px
    rgba(
      217,
      146,
      2,
      0.15
    );

}


/* ==========================================================
   PASSWORD
========================================================== */

.password-wrapper {

  position:
    relative;

}


.password-wrapper input {

  padding-right:
    56px;

}


.toggle-password-btn {

  position:
    absolute;

  right:
    18px;

  top:
    50%;

  transform:
    translateY(
      -50%
    );

  border:
    none;

  background:
    transparent;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  cursor:
    pointer;

  padding:
    0;

}


.eye-svg {

  width:
    24px;

  height:
    24px;

}


.toggle-password-btn:hover .eye-svg {

  opacity:
    0.8;

}


/* ==========================================================
   ERRORS
========================================================== */

.error-message {

  margin-top:
    6px;

  color:
    #C62828;

  font-size:
    0.85rem;

  font-weight:
    600;

}


/* ==========================================================
   OPTIONS
========================================================== */

.options {

  display:
    flex;

  justify-content:
    space-between;

  align-items:
    center;

  margin-bottom:
    34px;

}


/* ==========================================================
   REMEMBER ME
========================================================== */

.remember {

  display:
    flex;

  align-items:
    center;

  cursor:
    pointer;

  user-select:
    none;

  position:
    relative;

}


.remember input {

  position:
    absolute;

  opacity:
    0;

}


.custom-checkbox {

  width:
    18px;

  height:
    18px;

  border:
    2px
    solid
    #D99202;

  border-radius:
    4px;

  margin-right:
    10px;

  display:
    inline-block;

  position:
    relative;

  box-sizing:
    border-box;

}


.remember input:checked + .custom-checkbox {

  background:
    #D99202;

}


.remember input:checked + .custom-checkbox::after {

  content:
    "";

  position:
    absolute;

  left:
    4px;

  top:
    1px;

  width:
    5px;

  height:
    9px;

  border:
    solid
    #FFFFFF;

  border-width:
    0
    2px
    2px
    0;

  transform:
    rotate(
      45deg
    );

}


.remember-text {

  color:
    #D99202;

  font-size:
    0.95rem;

  font-weight:
    600;

}


/* ==========================================================
   FORGOT PASSWORD
========================================================== */

.forgot-link {

  color:
    #D99202;

  text-decoration:
    none;

  font-size:
    0.95rem;

  font-weight:
    600;

  transition:
    color
    0.2s
    ease;

}


.forgot-link:hover {

  color:
    #54100F;

  text-decoration:
    underline;

}


/* ==========================================================
   LOGIN BUTTON
========================================================== */

.login-btn {

  width:
    100%;

  height:
    60px;

  border:
    none;

  border-radius:
    30px;

  background:
    #D99202;

  color:
    #FFFFFF;

  font-family:
    'Lora',
    serif;

  font-size:
    1.7rem;

  font-weight:
    500;

  cursor:
    pointer;

  box-shadow:
    0
    4px
    12px
    rgba(
      217,
      146,
      2,
      0.30
    );

  transition:
    0.2s
    ease;

}


.login-btn:hover:not(:disabled) {

  background:
    #C68500;

  transform:
    translateY(
      -1px
    );

}


.login-btn:active {

  transform:
    translateY(
      0
    );

}


.login-btn:disabled {

  opacity:
    0.7;

  cursor:
    not-allowed;

}


/* ==========================================================
   DIVIDER
========================================================== */

.divider {

  display:
    flex;

  align-items:
    center;

  margin:
    34px
    0;

}


.divider::before,
.divider::after {

  content:
    "";

  flex:
    1;

  height:
    2px;

  background:
    #233E47;

}


.divider span {

  margin:
    0
    16px;

  color:
    #233E47;

  font-size:
    0.9rem;

  font-weight:
    500;

}


/* ==========================================================
   GOOGLE BUTTON
========================================================== */

.google-wrapper {

  display:
    flex;

  justify-content:
    center;

}


.google-btn {

  width:
    270px;

  height:
    54px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  gap:
    12px;

  border:
    2px
    solid
    #D99202;

  border-radius:
    10px;

  background:
    #FFFFFF;

  cursor:
    pointer;

  text-decoration:
    none;

  transition:
    0.2s
    ease;

}


.google-btn:hover {

  background:
    rgba(
      217,
      146,
      2,
      0.08
    );

}


.google-btn img {

  width:
    24px;

  height:
    24px;

}


.google-btn span {

  color:
    #D99202;

  font-size:
    1rem;

  font-weight:
    700;

}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (
  max-width: 900px
) {

  .login-container {

    flex-direction:
      column;

    overflow-y:
      auto;

  }


  .right-panel {

    padding:
      30px
      24px;

  }


  .login-card {

    max-width:
      100%;

  }


  .title {

    font-size:
      3.5rem;

  }


  .options {

    flex-direction:
      column;

    align-items:
      flex-start;

    gap:
      16px;

  }


  .google-btn {

    width:
      100%;

  }

}

</style>