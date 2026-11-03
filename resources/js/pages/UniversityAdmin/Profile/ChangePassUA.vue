<template>
    <div class="change-password-page">

        <!-- ==========================================================
             UNIVERSITY ADMIN HEADER
        =========================================================== -->

        <UAHeader />

        <!-- ==========================================================
             PAGE CONTENT
        =========================================================== -->

        <main class="page-content">

            <!-- ======================================================
                 CHANGE PASSWORD CARD
            ======================================================= -->

            <section class="password-card">

                <!-- ==================================================
                     CARD HEADER
                =================================================== -->

                <div class="card-header">

                    <div class="header-text">

                        <h1>
                            Change Password
                        </h1>

                        <p>
                            Update your password to keep your account secure.
                        </p>

                    </div>

                    <!-- CLOSE BUTTON -->

                    <button
                        type="button"
                        class="close-button"
                        aria-label="Close"
                        @click="closePage"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>

                </div>

                <!-- ==================================================
                     FORM
                =================================================== -->

                <form
                    class="password-form"
                    @submit.prevent="submitPassword"
                >

                    <!-- ==================================================
                         CURRENT PASSWORD
                    =================================================== -->

                    <div class="form-group">

                        <label for="current_password">
                            Current Password
                        </label>

                        <div
                            class="password-input-wrapper"
                            :class="{
                                'has-error': errors.current_password
                            }"
                        >

                            <input
                                id="current_password"
                                v-model="form.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                placeholder="enter current password"
                                autocomplete="current-password"
                                @input="clearError('current_password')"
                            >

                            <button
                                type="button"
                                class="eye-button"
                                @click="showCurrentPassword = !showCurrentPassword"
                            >

                                <!-- EYE OPEN -->

                                <svg
                                    v-if="showCurrentPassword"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696
                                        C3.256 8.884 6.03 5 12 5
                                        c5.97 0 8.744 3.884 9.938 6.652
                                        a1 1 0 0 1 0 .696
                                        C20.744 15.116 17.97 19 12 19
                                        c-5.97 0-8.744-3.884-9.938-6.652"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    />
                                </svg>

                                <!-- EYE CLOSED -->

                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m2 2 20 20" />

                                    <path
                                        d="M6.71 6.71
                                        C4.72 8.1 3.5 10.15 2.46 11.63
                                        a.7.7 0 0 0 0 .74
                                        C4.09 14.72 7.17 19 12 19
                                        c1.44 0 2.73-.38 3.88-.95"
                                    />

                                    <path
                                        d="M10.73 5.08
                                        A8.7 8.7 0 0 1 12 5
                                        c4.83 0 7.91 4.28 9.54 6.63
                                        a.7.7 0 0 1 0 .74
                                        c-.49.71-1.08 1.48-1.78 2.2"
                                    />

                                    <path
                                        d="M14.12 14.12
                                        A3 3 0 0 1 9.88 9.88"
                                    />
                                </svg>

                            </button>

                        </div>

                        <p
                            v-if="errors.current_password"
                            class="error-message"
                        >
                            {{ errors.current_password }}
                        </p>

                    </div>

                    <!-- ==================================================
                         NEW PASSWORD
                    =================================================== -->

                    <div class="form-group">

                        <label for="password">
                            New Password
                        </label>

                        <div
                            class="password-input-wrapper"
                            :class="{
                                'has-error': errors.password
                            }"
                        >

                            <input
                                id="password"
                                v-model="form.password"
                                :type="showNewPassword ? 'text' : 'password'"
                                placeholder="enter new password"
                                autocomplete="new-password"
                                @input="clearError('password')"
                            >

                            <button
                                type="button"
                                class="eye-button"
                                @click="showNewPassword = !showNewPassword"
                            >

                                <!-- EYE OPEN -->

                                <svg
                                    v-if="showNewPassword"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696
                                        C3.256 8.884 6.03 5 12 5
                                        c5.97 0 8.744 3.884 9.938 6.652
                                        a1 1 0 0 1 0 .696
                                        C20.744 15.116 17.97 19 12 19
                                        c-5.97 0-8.744-3.884-9.938-6.652"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    />
                                </svg>

                                <!-- EYE CLOSED -->

                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m2 2 20 20" />

                                    <path
                                        d="M6.71 6.71
                                        C4.72 8.1 3.5 10.15 2.46 11.63
                                        a.7.7 0 0 0 0 .74
                                        C4.09 14.72 7.17 19 12 19
                                        c1.44 0 2.73-.38 3.88-.95"
                                    />

                                    <path
                                        d="M10.73 5.08
                                        A8.7 8.7 0 0 1 12 5
                                        c4.83 0 7.91 4.28 9.54 6.63
                                        a.7.7 0 0 1 0 .74
                                        c-.49.71-1.08 1.48-1.78 2.2"
                                    />

                                    <path
                                        d="M14.12 14.12
                                        A3 3 0 0 1 9.88 9.88"
                                    />
                                </svg>

                            </button>

                        </div>

                        <p
                            v-if="errors.password"
                            class="error-message"
                        >
                            {{ errors.password }}
                        </p>

                    </div>

                    <!-- ==================================================
                         CONFIRM PASSWORD
                    =================================================== -->

                    <div class="form-group">

                        <label for="password_confirmation">
                            Confirm Password
                        </label>

                        <div
                            class="password-input-wrapper"
                            :class="{
                                'has-error': errors.password_confirmation
                            }"
                        >

                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                placeholder="enter confirm password"
                                autocomplete="new-password"
                                @input="clearError('password_confirmation')"
                            >

                            <button
                                type="button"
                                class="eye-button"
                                @click="showConfirmPassword = !showConfirmPassword"
                            >

                                <!-- EYE OPEN -->

                                <svg
                                    v-if="showConfirmPassword"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696
                                        C3.256 8.884 6.03 5 12 5
                                        c5.97 0 8.744 3.884 9.938 6.652
                                        a1 1 0 0 1 0 .696
                                        C20.744 15.116 17.97 19 12 19
                                        c-5.97 0-8.744-3.884-9.938-6.652"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    />
                                </svg>

                                <!-- EYE CLOSED -->

                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m2 2 20 20" />

                                    <path
                                        d="M6.71 6.71
                                        C4.72 8.1 3.5 10.15 2.46 11.63
                                        a.7.7 0 0 0 0 .74
                                        C4.09 14.72 7.17 19 12 19
                                        c1.44 0 2.73-.38 3.88-.95"
                                    />

                                    <path
                                        d="M10.73 5.08
                                        A8.7 8.7 0 0 1 12 5
                                        c4.83 0 7.91 4.28 9.54 6.63
                                        a.7.7 0 0 1 0 .74
                                        c-.49.71-1.08 1.48-1.78 2.2"
                                    />

                                    <path
                                        d="M14.12 14.12
                                        A3 3 0 0 1 9.88 9.88"
                                    />
                                </svg>

                            </button>

                        </div>

                        <p
                            v-if="errors.password_confirmation"
                            class="error-message"
                        >
                            {{ errors.password_confirmation }}
                        </p>

                    </div>

                    <!-- ==================================================
                         SAVE BUTTON
                    =================================================== -->

                    <div class="button-row">

                        <button
                            type="submit"
                            class="save-button"
                            :disabled="processing"
                        >

                            <span v-if="processing">
                                SAVING...
                            </span>

                            <span v-else>
                                Save Changes
                            </span>

                        </button>

                    </div>

                </form>

            </section>

        </main>

    </div>
</template>

<script setup>

import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import UAHeader from '@/components/UAHeader.vue'

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = reactive({

    current_password: '',

    password: '',

    password_confirmation: '',

})

/*
|--------------------------------------------------------------------------
| Validation Errors
|--------------------------------------------------------------------------
*/

const errors = reactive({

    current_password: '',

    password: '',

    password_confirmation: '',

})

/*
|--------------------------------------------------------------------------
| Password Visibility
|--------------------------------------------------------------------------
*/

const showCurrentPassword = ref(false)

const showNewPassword = ref(false)

const showConfirmPassword = ref(false)

/*
|--------------------------------------------------------------------------
| Processing
|--------------------------------------------------------------------------
*/

const processing = ref(false)

/*
|--------------------------------------------------------------------------
| Clear Error
|--------------------------------------------------------------------------
*/

function clearError(field) {

    errors[field] = ''

}

/*
|--------------------------------------------------------------------------
| Validate Form
|--------------------------------------------------------------------------
*/

function validateForm() {

    errors.current_password = ''

    errors.password = ''

    errors.password_confirmation = ''

    let valid = true

    /*
    |--------------------------------------------------------------------------
    | Current Password
    |--------------------------------------------------------------------------
    */

    if (!form.current_password.trim()) {

        errors.current_password =
            'Please enter your current password.'

        valid = false

    }

    /*
    |--------------------------------------------------------------------------
    | New Password
    |--------------------------------------------------------------------------
    */

    if (!form.password) {

        errors.password =
            'Please enter your new password.'

        valid = false

    } else if (form.password.length < 8) {

        errors.password =
            'New password must contain at least 8 characters.'

        valid = false

    }

    /*
    |--------------------------------------------------------------------------
    | Confirm Password
    |--------------------------------------------------------------------------
    */

    if (!form.password_confirmation) {

        errors.password_confirmation =
            'Please confirm your new password.'

        valid = false

    } else if (
        form.password !==
        form.password_confirmation
    ) {

        errors.password_confirmation =
            'The password confirmation does not match.'

        valid = false

    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Same Password
    |--------------------------------------------------------------------------
    */

    if (
        form.current_password &&
        form.password &&
        form.current_password === form.password
    ) {

        errors.password =
            'Your new password must be different from your current password.'

        valid = false

    }

    return valid

}

/*
|--------------------------------------------------------------------------
| Submit Password
|--------------------------------------------------------------------------
*/

function submitPassword() {

    if (processing.value) {

        return

    }

    if (!validateForm()) {

        return

    }

    processing.value = true

    router.post(
        '/university-admin/change-password',
        {
            current_password:
                form.current_password,

            password:
                form.password,

            password_confirmation:
                form.password_confirmation,
        },
        {
            preserveScroll: true,

            /*
            |--------------------------------------------------------------------------
            | Error
            |--------------------------------------------------------------------------
            */

            onError: (serverErrors) => {

                if (serverErrors.current_password) {

                    errors.current_password =
                        getErrorMessage(
                            serverErrors.current_password
                        )

                }

                if (serverErrors.password) {

                    errors.password =
                        getErrorMessage(
                            serverErrors.password
                        )

                }

                if (
                    serverErrors.password_confirmation
                ) {

                    errors.password_confirmation =
                        getErrorMessage(
                            serverErrors.password_confirmation
                        )

                }

            },

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            onSuccess: () => {

                form.current_password = ''

                form.password = ''

                form.password_confirmation = ''

                showCurrentPassword.value = false

                showNewPassword.value = false

                showConfirmPassword.value = false

            },

            /*
            |--------------------------------------------------------------------------
            | Finish
            |--------------------------------------------------------------------------
            */

            onFinish: () => {

                processing.value = false

            },
        }
    )

}

/*
|--------------------------------------------------------------------------
| Error Helper
|--------------------------------------------------------------------------
*/

function getErrorMessage(error) {

    if (Array.isArray(error)) {

        return error[0] || ''

    }

    return error || ''

}

/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

function closePage() {

    router.visit(
        '/university-admin/uniadminprofile'
    )

}

</script>

<style scoped>

@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

/* ==========================================================
   PAGE
========================================================== */

.change-password-page{

    width:100%;

    min-height:100vh;

    background:#EFEBE2;

    font-family:'Plus Jakarta Sans',sans-serif;

    color:#000D12;

}


/* ==========================================================
   PAGE CONTENT
========================================================== */

.page-content{

    width:100%;

    min-height:calc(100vh - 95px);

    display:flex;

    justify-content:center;

    align-items:flex-start;

    padding:85px 40px 90px;

    box-sizing:border-box;

}


/* ==========================================================
   PASSWORD CARD
========================================================== */

.password-card{

    width:100%;

    /*
    |--------------------------------------------------------------------------
    | BIGGER CARD
    |--------------------------------------------------------------------------
    */

    max-width:1050px;

    min-height:620px;

    background:#FFFFFF;

    border:1.5px solid #7A7A7A;

    border-radius:18px;

    padding:65px 90px 55px;

    box-sizing:border-box;

    box-shadow:
        0 7px 24px
        rgba(0,0,0,.06);

}


/* ==========================================================
   CARD HEADER
========================================================== */

.card-header{

    display:flex;

    align-items:flex-start;

    justify-content:space-between;

    gap:40px;

    margin-bottom:55px;

}


/* ==========================================================
   TITLE
========================================================== */

.header-text h1{

    margin:0;

    color:#54100F;

    /*
    |--------------------------------------------------------------------------
    | BIGGER TITLE
    |--------------------------------------------------------------------------
    */

    font-size:40px;

    font-weight:700;

    line-height:1.2;

}


/* ==========================================================
   SUBTITLE
========================================================== */

.header-text p{

    margin:

        24px
        0
        0;

    color:#233E47;

    /*
    |--------------------------------------------------------------------------
    | BIGGER SUBTITLE
    |--------------------------------------------------------------------------
    */

    font-size:19px;

    font-weight:400;

    line-height:1.6;

}


/* ==========================================================
   CLOSE BUTTON
========================================================== */

.close-button{

    /*
    |--------------------------------------------------------------------------
    | BIGGER CLOSE BUTTON
    |--------------------------------------------------------------------------
    */

    width:58px;

    height:58px;

    padding:0;

    margin-top:-8px;

    border:none;

    border-radius:50%;

    background:transparent;

    color:#54100F;

    display:flex;

    align-items:center;

    justify-content:center;

    flex-shrink:0;

    cursor:pointer;

    transition:
        background .2s ease,
        transform .2s ease;

}

.close-button svg{

    width:50px;

    height:50px;

}

.close-button:hover{

    background:#EFEBE2;

    transform:rotate(4deg);

}


/* ==========================================================
   FORM
========================================================== */

.password-form{

    width:100%;

}


/* ==========================================================
   FORM GROUP
========================================================== */

.form-group{

    width:100%;

    /*
    |--------------------------------------------------------------------------
    | MORE SPACE BETWEEN INPUTS
    |--------------------------------------------------------------------------
    */

    margin-bottom:29px;

}


/* ==========================================================
   LABEL
========================================================== */

.form-group label{

    display:block;

    margin:

        0
        0
        10px
        34px;

    color:#54100F;

    font-family:
        Georgia,
        'Times New Roman',
        serif;

    /*
    |--------------------------------------------------------------------------
    | BIGGER LABEL
    |--------------------------------------------------------------------------
    */

    font-size:22px;

    font-weight:400;

}


/* ==========================================================
   PASSWORD INPUT WRAPPER
========================================================== */

.password-input-wrapper{

    width:100%;

    /*
    |--------------------------------------------------------------------------
    | BIGGER INPUT
    |--------------------------------------------------------------------------
    */

    height:57px;

    border:
        2px
        solid
        #233E47;

    border-radius:30px;

    background:#FFFFFF;

    display:flex;

    align-items:center;

    overflow:hidden;

    box-sizing:border-box;

    transition:
        border-color .2s ease,
        box-shadow .2s ease;

}


/* ==========================================================
   INPUT FOCUS
========================================================== */

.password-input-wrapper:focus-within{

    border-color:#58761C;

    box-shadow:
        0
        0
        0
        4px
        rgba(88,118,28,.12);

}


/* ==========================================================
   INPUT
========================================================== */

.password-input-wrapper input{

    width:100%;

    height:100%;

    border:none;

    outline:none;

    background:transparent;

    padding:

        0
        25px
        0
        34px;

    box-sizing:border-box;

    color:#000D12;

    font-family:'Plus Jakarta Sans',sans-serif;

    /*
    |--------------------------------------------------------------------------
    | BIGGER INPUT TEXT
    |--------------------------------------------------------------------------
    */

    font-size:15px;

    font-weight:400;

}

.password-input-wrapper input::placeholder{

    color:#233E47;

    opacity:.75;

}


/* ==========================================================
   EYE BUTTON
========================================================== */

.eye-button{

    /*
    |--------------------------------------------------------------------------
    | BIGGER EYE BUTTON
    |--------------------------------------------------------------------------
    */

    width:65px;

    height:100%;

    border:none;

    background:transparent;

    color:#233E47;

    display:flex;

    justify-content:center;

    align-items:center;

    cursor:pointer;

    flex-shrink:0;

    transition:.2s;

}

.eye-button:hover{

    color:#54100F;

}

.eye-button svg{

    /*
    |--------------------------------------------------------------------------
    | BIGGER EYE ICON
    |--------------------------------------------------------------------------
    */

    width:27px;

    height:27px;

}


/* ==========================================================
   ERROR STATE
========================================================== */

.password-input-wrapper.has-error{

    border-color:#54100F;

    box-shadow:
        0
        0
        0
        4px
        rgba(84,16,15,.08);

}


/* ==========================================================
   ERROR MESSAGE
========================================================== */

.error-message{

    margin:

        8px
        0
        0
        34px;

    color:#54100F;

    font-size:13px;

    font-weight:600;

}


/* ==========================================================
   BUTTON ROW
========================================================== */

.button-row{

    width:100%;

    display:flex;

    justify-content:flex-end;

    /*
    |--------------------------------------------------------------------------
    | MORE SPACE ABOVE BUTTON
    |--------------------------------------------------------------------------
    */

    margin-top:38px;

}


/* ==========================================================
   SAVE BUTTON
========================================================== */

.save-button{

    /*
    |--------------------------------------------------------------------------
    | BIGGER BUTTON
    |--------------------------------------------------------------------------
    */

    width:320px;

    height:58px;

    border:none;

    border-radius:10px;

    background:#58761C;

    color:#FFFFFF;

    font-family:'Plus Jakarta Sans',sans-serif;

    font-size:17px;

    font-weight:500;

    cursor:pointer;

    transition:
        background .2s ease,
        transform .2s ease,
        box-shadow .2s ease;

}

.save-button:hover:not(:disabled){

    background:#476017;

    transform:translateY(-2px);

    box-shadow:
        0
        8px
        20px
        rgba(88,118,28,.22);

}

.save-button:active:not(:disabled){

    transform:translateY(0);

}

.save-button:disabled{

    opacity:.6;

    cursor:not-allowed;

}


/* ==========================================================
   DESKTOP
========================================================== */

@media (max-width:1200px){

    .password-card{

        max-width:950px;

        min-height:590px;

        padding:
            58px
            75px
            48px;

    }

    .page-content{

        padding-top:70px;

    }

}


/* ==========================================================
   TABLET
========================================================== */

@media (max-width:900px){

    .page-content{

        padding:
            55px
            30px
            70px;

    }

    .password-card{

        max-width:760px;

        min-height:auto;

        padding:
            50px
            55px
            42px;

    }

    .header-text h1{

        font-size:34px;

    }

    .header-text p{

        font-size:17px;

    }

    .form-group label{

        font-size:20px;

    }

    .password-input-wrapper{

        height:53px;

    }

    .save-button{

        width:290px;

        height:54px;

    }

}


/* ==========================================================
   SMALL TABLET
========================================================== */

@media (max-width:768px){

    .page-content{

        padding:
            40px
            22px
            60px;

    }

    .password-card{

        padding:
            42px
            35px
            35px;

    }

    .card-header{

        margin-bottom:40px;

    }

    .header-text h1{

        font-size:30px;

    }

    .header-text p{

        margin-top:16px;

        font-size:15px;

    }

    .close-button{

        width:44px;

        height:44px;

    }

    .close-button svg{

        width:37px;

        height:37px;

    }

    .form-group{

        margin-bottom:24px;

    }

    .form-group label{

        margin-left:20px;

        font-size:18px;

    }

    .password-input-wrapper{

        height:49px;

    }

    .password-input-wrapper input{

        padding-left:20px;

        font-size:13px;

    }

    .eye-button{

        width:52px;

    }

    .eye-button svg{

        width:22px;

        height:22px;

    }

    .error-message{

        margin-left:20px;

    }

}


/* ==========================================================
   MOBILE
========================================================== */

@media (max-width:576px){

    .page-content{

        padding:
            28px
            15px
            45px;

    }

    .password-card{

        width:100%;

        min-height:auto;

        border-radius:14px;

        padding:
            30px
            20px
            26px;

    }

    .card-header{

        gap:15px;

        margin-bottom:30px;

    }

    .header-text h1{

        font-size:25px;

    }

    .header-text p{

        font-size:13px;

        line-height:1.6;

    }

    .close-button{

        width:38px;

        height:38px;

        margin-top:-4px;

    }

    .close-button svg{

        width:31px;

        height:31px;

    }

    .form-group{

        margin-bottom:21px;

    }

    .form-group label{

        margin:
            0
            0
            7px
            15px;

        font-size:16px;

    }

    .password-input-wrapper{

        height:46px;

    }

    .password-input-wrapper input{

        padding-left:15px;

        font-size:12px;

    }

    .eye-button{

        width:46px;

    }

    .eye-button svg{

        width:20px;

        height:20px;

    }

    .error-message{

        margin-left:15px;

    }

    .button-row{

        margin-top:27px;

    }

    .save-button{

        width:100%;

        height:49px;

        font-size:14px;

    }

}

</style>