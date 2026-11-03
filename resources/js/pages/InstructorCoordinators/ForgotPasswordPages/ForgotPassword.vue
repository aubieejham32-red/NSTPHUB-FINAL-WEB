<template>
    <div class="page-container">

        <!-- ==========================================================
             LEFT PANEL
        =========================================================== -->
        <LeftLogo />


        <!-- ==========================================================
             RIGHT PANEL
        =========================================================== -->
        <main class="right-panel">

            <div class="form-wrapper">

                <!-- ==================================================
                     TITLE
                =================================================== -->
                <h1 class="title">
                    Forgot your password?
                </h1>


                <!-- ==================================================
                     SUBTITLE
                =================================================== -->
                <p class="subtitle">
                    Enter the email associated with your
                    Instructor or Coordinator account.
                    We'll send a 6-digit verification code
                    to verify it's you.
                </p>


                <!-- ==================================================
                     FORM
                =================================================== -->
                <form
                    class="forgot-form"
                    @submit.prevent="handleSubmit"
                >

                    <!-- ==============================================
                         EMAIL
                    =============================================== -->
                    <div class="form-group">

                        <label
                            for="email"
                            class="label"
                        >
                            Email
                        </label>


                        <div
                            class="input-wrapper"
                            :class="{
                                'input-wrapper--error': error,
                            }"
                        >

                            <!-- EMAIL ICON -->
                            <svg
                                class="input-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2"
                                />

                                <path
                                    d="m3 7 9 6 9-6"
                                />
                            </svg>


                            <input
                                id="email"
                                v-model="email"
                                type="email"
                                class="input-field"
                                placeholder="Enter your email"
                                autocomplete="email"
                                :disabled="loading"
                                required
                                @input="clearMessages"
                            >

                        </div>

                    </div>


                    <!-- ==============================================
                         ERROR
                    =============================================== -->
                    <div
                        v-if="error"
                        class="message-box error-box"
                    >

                        <svg
                            class="message-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                            />

                            <path
                                d="M12 8v5"
                            />

                            <path
                                d="M12 16.5h.01"
                            />
                        </svg>

                        <span>
                            {{ error }}
                        </span>

                    </div>


                    <!-- ==============================================
                         SUCCESS
                    =============================================== -->
                    <div
                        v-if="success"
                        class="message-box success-box"
                    >

                        <svg
                            class="message-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                            />

                            <path
                                d="m8 12 2.5 2.5L16 9"
                            />
                        </svg>

                        <span>
                            {{ success }}
                        </span>

                    </div>


                    <!-- ==============================================
                         SUBMIT
                    =============================================== -->
                    <button
                        type="submit"
                        class="submit-btn"
                        :disabled="loading"
                    >

                        <!-- LOADING ICON -->
                        <svg
                            v-if="loading"
                            class="loading-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                            />
                        </svg>


                        <span>
                            {{
                                loading
                                    ? 'Sending...'
                                    : 'Send Verification Code'
                            }}
                        </span>

                    </button>

                </form>


                <!-- ==================================================
                     BACK TO SIGN IN
                =================================================== -->
                <div class="footer-text">

                    <span>
                        Remembered it after all?
                    </span>

                    <button
                        type="button"
                        class="back-link"
                        @click="goToSignIn"
                    >
                        Back to sign in
                    </button>

                </div>

            </div>

        </main>

    </div>
</template>


<script setup>

import {
    ref,
} from 'vue';

import axios
    from 'axios';

import {
    router,
} from '@inertiajs/vue3';

import LeftLogo
    from '@/components/LeftLogo.vue';


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const email =
    ref(
        ''
    );


const loading =
    ref(
        false
    );


const error =
    ref(
        ''
    );


const success =
    ref(
        ''
    );


/*
|--------------------------------------------------------------------------
| Clear Messages
|--------------------------------------------------------------------------
*/

const clearMessages = () => {

    error.value =
        '';


    success.value =
        '';

};


/*
|--------------------------------------------------------------------------
| Submit Forgot Password
|--------------------------------------------------------------------------
|
| Shared endpoint for:
|
| Instructor
| Coordinator Attendance
| Coordinator Announcement
| Coordinator Schedule
|
|
| Backend expected:
|
| POST /instructor-coordinator/forgot-password
|
*/

const handleSubmit = async () => {

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Request
    |--------------------------------------------------------------------------
    */

    if (
        loading.value
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Reset Messages
    |--------------------------------------------------------------------------
    */

    error.value =
        '';


    success.value =
        '';


    /*
    |--------------------------------------------------------------------------
    | Normalize Email
    |--------------------------------------------------------------------------
    */

    const normalizedEmail =
        email.value
            .trim()
            .toLowerCase();


    /*
    |--------------------------------------------------------------------------
    | Email Required
    |--------------------------------------------------------------------------
    */

    if (
        !normalizedEmail
    ) {

        error.value =
            'Please enter your email address.';


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Loading
    |--------------------------------------------------------------------------
    */

    loading.value =
        true;


    try {

        /*
        |--------------------------------------------------------------------------
        | Send Email To Backend
        |--------------------------------------------------------------------------
        */

        const response =
            await axios.post(
                '/instructor-coordinator/forgot-password',
                {
                    email:
                        normalizedEmail,
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        success.value =
            response.data?.message ??
            'Verification code sent successfully.';


        /*
        |--------------------------------------------------------------------------
        | Go To Verification Page
        |--------------------------------------------------------------------------
        */

        router.visit(
            '/instructor-coordinator/forgot-password/verify-code',
            {
                method:
                    'get',

                data: {
                    email:
                        normalizedEmail,
                },

                preserveState:
                    false,
            }
        );

    } catch (
        err
    ) {

        console.error(
            'Forgot password request failed:',
            err
        );


        /*
        |--------------------------------------------------------------------------
        | Server Responded
        |--------------------------------------------------------------------------
        */

        if (
            err.response
        ) {

            /*
            |--------------------------------------------------------------------------
            | Account Not Found
            |--------------------------------------------------------------------------
            */

            if (
                err.response.status ===
                404
            ) {

                error.value =
                    err.response.data?.message ??
                    'No Instructor or Coordinator account was found with this email address.';


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Validation Error
            |--------------------------------------------------------------------------
            */

            if (
                err.response.status ===
                422
            ) {

                if (
                    err.response.data?.errors
                ) {

                    error.value =
                        Object.values(
                            err.response.data.errors
                        )
                            .flat()
                            .join(
                                ' '
                            );


                    return;

                }


                error.value =
                    err.response.data?.message ??
                    'Please check the information you entered.';


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Other Server Error
            |--------------------------------------------------------------------------
            */

            error.value =
                err.response.data?.message ??
                'Something went wrong while sending the verification code.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Network Error
        |--------------------------------------------------------------------------
        */

        error.value =
            'Unable to connect to the server. Please try again.';

    } finally {

        /*
        |--------------------------------------------------------------------------
        | Stop Loading
        |--------------------------------------------------------------------------
        */

        loading.value =
            false;

    }

};


/*
|--------------------------------------------------------------------------
| Back To Sign In
|--------------------------------------------------------------------------
|
| Goes directly to the shared Instructor / Coordinator login page.
|
*/

const goToSignIn = () => {

    router.visit(
        '/instructor-coordinator/login'
    );

};

</script>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap'
);


/* ==========================================================
   COLOR PALETTE

   Cream       #EFEBE2
   Maroon      #54100F
   Green       #58761C
   Yellow      #FFBD36
   Orange      #D99202
   Dark Teal   #233E47
   Near Black  #000D12
   White       #FFFFFF
   Gray        #BEBEBE
   Dark        #0D171B
========================================================== */


/* ==========================================================
   PAGE
========================================================== */

.page-container {

    display:
        flex;

    width:
        100%;

    min-height:
        100vh;

    background:
        #EFEBE2;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    box-sizing:
        border-box;

}


/* ==========================================================
   RIGHT PANEL
========================================================== */

.right-panel {

    flex:
        1;

    min-width:
        0;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    padding:
        40px;

    box-sizing:
        border-box;

}


/* ==========================================================
   FORM WRAPPER
========================================================== */

.form-wrapper {

    width:
        100%;

    max-width:
        520px;

}


/* ==========================================================
   TITLE
========================================================== */

.title {

    margin:
        0
        0
        12px;

    color:
        #54100F;

    font-size:
        2.2rem;

    font-weight:
        700;

    line-height:
        1.25;

}


/* ==========================================================
   SUBTITLE
========================================================== */

.subtitle {

    max-width:
        490px;

    margin:
        0
        0
        35px;

    color:
        #233E47;

    font-size:
        15px;

    font-weight:
        400;

    line-height:
        1.7;

}


/* ==========================================================
   FORM
========================================================== */

.forgot-form {

    display:
        flex;

    flex-direction:
        column;

    gap:
        20px;

}


/* ==========================================================
   FORM GROUP
========================================================== */

.form-group {

    display:
        flex;

    flex-direction:
        column;

    gap:
        9px;

}


/* ==========================================================
   LABEL
========================================================== */

.label {

    color:
        #233E47;

    font-size:
        14px;

    font-weight:
        600;

}


/* ==========================================================
   INPUT WRAPPER
========================================================== */

.input-wrapper {

    width:
        100%;

    min-height:
        54px;

    display:
        flex;

    align-items:
        center;

    border:
        1.5px
        solid
        #D99202;

    border-radius:
        12px;

    background:
        #FFFFFF;

    overflow:
        hidden;

    transition:
        border-color
        0.2s
        ease,
        box-shadow
        0.2s
        ease;

}


/* ==========================================================
   INPUT FOCUS
========================================================== */

.input-wrapper:focus-within {

    border-color:
        #58761C;

    box-shadow:
        0
        0
        0
        4px
        rgba(
            88,
            118,
            28,
            0.10
        );

}


/* ==========================================================
   INPUT ERROR
========================================================== */

.input-wrapper--error {

    border-color:
        #54100F;

}


/* ==========================================================
   INPUT ICON
========================================================== */

.input-icon {

    width:
        20px;

    height:
        20px;

    margin-left:
        17px;

    flex-shrink:
        0;

    fill:
        none;

    stroke:
        #233E47;

    stroke-width:
        1.8;

    stroke-linecap:
        round;

    stroke-linejoin:
        round;

}


/* ==========================================================
   INPUT
========================================================== */

.input-field {

    flex:
        1;

    min-width:
        0;

    height:
        52px;

    padding:
        0
        16px;

    border:
        none;

    outline:
        none;

    background:
        transparent;

    color:
        #000D12;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        15px;

}


.input-field::placeholder {

    color:
        #8C8C8C;

}


.input-field:disabled {

    cursor:
        not-allowed;

    opacity:
        0.65;

}


/* ==========================================================
   MESSAGE BOX
========================================================== */

.message-box {

    width:
        100%;

    min-height:
        44px;

    padding:
        10px
        13px;

    display:
        flex;

    align-items:
        flex-start;

    gap:
        9px;

    border-radius:
        9px;

    font-size:
        13px;

    font-weight:
        600;

    line-height:
        1.5;

    box-sizing:
        border-box;

}


/* ==========================================================
   MESSAGE ICON
========================================================== */

.message-icon {

    width:
        18px;

    height:
        18px;

    margin-top:
        1px;

    flex-shrink:
        0;

    fill:
        none;

    stroke:
        currentColor;

    stroke-width:
        1.8;

    stroke-linecap:
        round;

    stroke-linejoin:
        round;

}


/* ==========================================================
   ERROR
========================================================== */

.error-box {

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.25
        );

    background:
        rgba(
            84,
            16,
            15,
            0.06
        );

    color:
        #54100F;

}


/* ==========================================================
   SUCCESS
========================================================== */

.success-box {

    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.30
        );

    background:
        rgba(
            88,
            118,
            28,
            0.08
        );

    color:
        #58761C;

}


/* ==========================================================
   SUBMIT BUTTON
========================================================== */

.submit-btn {

    width:
        100%;

    min-height:
        54px;

    padding:
        14px
        20px;

    border:
        none;

    border-radius:
        30px;

    background:
        #D99202;

    color:
        #FFFFFF;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    gap:
        9px;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        16px;

    font-weight:
        600;

    cursor:
        pointer;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.2s
        ease,
        box-shadow
        0.2s
        ease;

}


.submit-btn:hover:not(:disabled) {

    background:
        #BF8100;

    transform:
        translateY(
            -1px
        );

    box-shadow:
        0
        7px
        18px
        rgba(
            217,
            146,
            2,
            0.22
        );

}


.submit-btn:active:not(:disabled) {

    transform:
        translateY(
            0
        );

}


.submit-btn:disabled {

    opacity:
        0.65;

    cursor:
        not-allowed;

}


/* ==========================================================
   LOADING
========================================================== */

.loading-icon {

    width:
        18px;

    height:
        18px;

    fill:
        none;

    stroke:
        currentColor;

    stroke-width:
        2.5;

    stroke-linecap:
        round;

    stroke-dasharray:
        35;

    stroke-dashoffset:
        13;

    animation:
        spin
        0.8s
        linear
        infinite;

}


@keyframes spin {

    to {

        transform:
            rotate(
                360deg
            );

    }

}


/* ==========================================================
   FOOTER
========================================================== */

.footer-text {

    margin-top:
        30px;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        5px;

    color:
        #233E47;

    text-align:
        center;

    font-size:
        14px;

}


/* ==========================================================
   BACK LINK
========================================================== */

.back-link {

    padding:
        0;

    border:
        none;

    background:
        transparent;

    color:
        #D99202;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        14px;

    font-weight:
        600;

    text-decoration:
        underline;

    cursor:
        pointer;

    transition:
        color
        0.2s
        ease;

}


.back-link:hover {

    color:
        #54100F;

}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (
    max-width: 900px
) {

    .right-panel {

        padding:
            35px
            25px;

    }


    .title {

        font-size:
            1.9rem;

    }

}


@media (
    max-width: 700px
) {

    .page-container {

        flex-direction:
            column;

    }


    .right-panel {

        width:
            100%;

        padding:
            45px
            24px;

    }


    .form-wrapper {

        max-width:
            500px;

    }

}


@media (
    max-width: 480px
) {

    .right-panel {

        padding:
            35px
            18px;

    }


    .title {

        font-size:
            1.65rem;

    }


    .subtitle {

        font-size:
            13px;

        margin-bottom:
            28px;

    }


    .input-wrapper {

        min-height:
            50px;

    }


    .input-field {

        height:
            48px;

        font-size:
            14px;

    }


    .submit-btn {

        min-height:
            50px;

        font-size:
            14px;

    }


    .footer-text,
    .back-link {

        font-size:
            12px;

    }

}

</style>