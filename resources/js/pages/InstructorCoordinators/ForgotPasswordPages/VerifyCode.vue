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
                    Check your inbox
                </h1>


                <!-- ==================================================
                     SUBTITLE
                =================================================== -->

                <p class="subtitle">
                    We sent a 6-digit code to

                    <strong>
                        {{ email }}
                    </strong>.

                    Enter it below.
                </p>


                <!-- ==================================================
                     ACCOUNT TYPE
                =================================================== -->

                <div
                    v-if="accountLabel"
                    class="account-type-container"
                >
                    <span class="account-type-label">
                        {{ accountLabel }}
                    </span>
                </div>


                <!-- ==================================================
                     VERIFY FORM
                =================================================== -->

                <form
                    class="verify-form"
                    @submit.prevent="handleVerify"
                >

                    <!-- ==============================================
                         OTP BOXES
                    =============================================== -->

                    <div class="otp-container">

                        <input
                            v-for="(digit, index) in digits"
                            :key="index"
                            :ref="
                                (element) => {
                                    inputRefs[index] = element;
                                }
                            "
                            v-model="digits[index]"
                            type="text"
                            maxlength="1"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            class="otp-box"
                            :disabled="loading"
                            @input="onInput(index, $event)"
                            @keydown="onKeyDown(index, $event)"
                            @paste="onPaste"
                        >

                    </div>


                    <!-- ==============================================
                         ERROR
                    =============================================== -->

                    <div
                        v-if="error"
                        class="message-box error-message"
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
                        class="message-box success-message"
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
                         RESEND
                    =============================================== -->

                    <div class="resend-row">

                        <span class="didnt-get">
                            Didn't receive the code?
                        </span>


                        <button
                            type="button"
                            class="resend-btn"
                            :disabled="
                                resendLoading ||
                                loading
                            "
                            @click="handleResend"
                        >
                            {{
                                resendLoading
                                    ? 'Sending...'
                                    : 'Resend Code'
                            }}
                        </button>

                    </div>


                    <!-- ==============================================
                         VERIFY BUTTON
                    =============================================== -->

                    <button
                        type="submit"
                        class="submit-btn"
                        :disabled="
                            !isCodeComplete ||
                            loading
                        "
                    >

                        <!-- LOADER -->

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
                                    ? 'Verifying...'
                                    : 'Verify Code'
                            }}
                        </span>

                    </button>

                </form>


                <!-- ==================================================
                     BACK
                =================================================== -->

                <div class="footer-text">

                    <span>
                        Entered the wrong email?
                    </span>


                    <button
                        type="button"
                        class="back-link"
                        :disabled="
                            loading ||
                            resendLoading
                        "
                        @click="goBack"
                    >
                        Go back
                    </button>

                </div>

            </div>

        </main>

    </div>
</template>


<script setup>

import {
    computed,
    nextTick,
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
| Props
|--------------------------------------------------------------------------
|
| email:
|
| Email entered in ForgotPassword.vue.
|
|
| accountType:
|
| instructor
| coordinator
|
| This is optional because your backend may also keep the account type
| inside the session.
|
*/

const props = defineProps({

    email: {
        type: String,

        required: true,
    },


    accountType: {
        type: String,

        default: '',
    },

});


/*
|--------------------------------------------------------------------------
| OTP Digits
|--------------------------------------------------------------------------
*/

const digits =
    ref([
        '',
        '',
        '',
        '',
        '',
        '',
    ]);


/*
|--------------------------------------------------------------------------
| Input References
|--------------------------------------------------------------------------
*/

const inputRefs =
    ref(
        []
    );


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading =
    ref(
        false
    );


const resendLoading =
    ref(
        false
    );


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

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
| Normalized Account Type
|--------------------------------------------------------------------------
*/

const normalizedAccountType = computed(() => {

    const type =
        String(
            props.accountType ??
            ''
        )
            .trim()
            .toLowerCase();


    if (
        type ===
        'instructor'
    ) {

        return 'instructor';

    }


    if (
        type ===
        'coordinator'
    ) {

        return 'coordinator';

    }


    return '';

});


/*
|--------------------------------------------------------------------------
| Account Label
|--------------------------------------------------------------------------
*/

const accountLabel = computed(() => {

    if (
        normalizedAccountType.value ===
        'instructor'
    ) {

        return 'INSTRUCTOR ACCOUNT';

    }


    if (
        normalizedAccountType.value ===
        'coordinator'
    ) {

        return 'COORDINATOR ACCOUNT';

    }


    return '';

});


/*
|--------------------------------------------------------------------------
| Is Code Complete
|--------------------------------------------------------------------------
*/

const isCodeComplete = computed(() => {

    return digits.value.every(
        (
            digit
        ) => {

            return (
                String(
                    digit
                ).length ===
                1
            );

        }
    );

});


/*
|--------------------------------------------------------------------------
| Verification Code
|--------------------------------------------------------------------------
*/

const verificationCode = computed(() => {

    return digits.value.join(
        ''
    );

});


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
| OTP Input
|--------------------------------------------------------------------------
*/

const onInput = (
    index,
    event
) => {

    /*
    |--------------------------------------------------------------------------
    | Numbers Only
    |--------------------------------------------------------------------------
    */

    const value =
        String(
            event.target.value
        )
            .replace(
                /\D/g,
                ''
            )
            .slice(
                0,
                1
            );


    /*
    |--------------------------------------------------------------------------
    | Save Digit
    |--------------------------------------------------------------------------
    */

    digits.value[index] =
        value;


    /*
    |--------------------------------------------------------------------------
    | Clear Message
    |--------------------------------------------------------------------------
    */

    clearMessages();


    /*
    |--------------------------------------------------------------------------
    | Move To Next Input
    |--------------------------------------------------------------------------
    */

    if (
        value &&
        index <
            digits.value.length -
                1
    ) {

        nextTick(
            () => {

                inputRefs
                    .value[index + 1]
                    ?.focus();

            }
        );

    }

};


/*
|--------------------------------------------------------------------------
| Keyboard Navigation
|--------------------------------------------------------------------------
*/

const onKeyDown = (
    index,
    event
) => {

    /*
    |--------------------------------------------------------------------------
    | Backspace
    |--------------------------------------------------------------------------
    */

    if (
        event.key ===
        'Backspace'
    ) {

        /*
        |--------------------------------------------------------------------------
        | Current Input Has Value
        |--------------------------------------------------------------------------
        */

        if (
            digits.value[index] !==
            ''
        ) {

            digits.value[index] =
                '';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Move To Previous Input
        |--------------------------------------------------------------------------
        */

        if (
            index >
            0
        ) {

            event.preventDefault();


            digits.value[index - 1] =
                '';


            nextTick(
                () => {

                    inputRefs
                        .value[index - 1]
                        ?.focus();

                }
            );

        }


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Left Arrow
    |--------------------------------------------------------------------------
    */

    if (
        event.key ===
        'ArrowLeft' &&
        index >
            0
    ) {

        event.preventDefault();


        inputRefs
            .value[index - 1]
            ?.focus();


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Right Arrow
    |--------------------------------------------------------------------------
    */

    if (
        event.key ===
        'ArrowRight' &&
        index <
            digits.value.length -
                1
    ) {

        event.preventDefault();


        inputRefs
            .value[index + 1]
            ?.focus();

    }

};


/*
|--------------------------------------------------------------------------
| Paste OTP
|--------------------------------------------------------------------------
*/

const onPaste = (
    event
) => {

    /*
    |--------------------------------------------------------------------------
    | Prevent Normal Paste
    |--------------------------------------------------------------------------
    */

    event.preventDefault();


    /*
    |--------------------------------------------------------------------------
    | Extract Numeric Code
    |--------------------------------------------------------------------------
    */

    const pasted =
        event.clipboardData
            ?.getData(
                'text'
            )
            ?.replace(
                /\D/g,
                ''
            )
            ?.slice(
                0,
                6
            ) ??
        '';


    /*
    |--------------------------------------------------------------------------
    | No Digits
    |--------------------------------------------------------------------------
    */

    if (
        !pasted
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Reset OTP
    |--------------------------------------------------------------------------
    */

    digits.value = [
        '',
        '',
        '',
        '',
        '',
        '',
    ];


    /*
    |--------------------------------------------------------------------------
    | Fill Inputs
    |--------------------------------------------------------------------------
    */

    pasted
        .split(
            ''
        )
        .forEach(
            (
                digit,
                index
            ) => {

                if (
                    index <
                    6
                ) {

                    digits.value[index] =
                        digit;

                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Clear Errors
    |--------------------------------------------------------------------------
    */

    clearMessages();


    /*
    |--------------------------------------------------------------------------
    | Focus Last Filled / Next Input
    |--------------------------------------------------------------------------
    */

    nextTick(
        () => {

            const nextIndex =
                Math.min(
                    pasted.length,
                    5
                );


            inputRefs
                .value[nextIndex]
                ?.focus();

        }
    );

};


/*
|--------------------------------------------------------------------------
| Verify OTP
|--------------------------------------------------------------------------
|
| Shared endpoint:
|
| POST /instructor-coordinator/verify-code
|
*/

const handleVerify = async () => {

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
    | Code Must Be Complete
    |--------------------------------------------------------------------------
    */

    if (
        !isCodeComplete.value
    ) {

        error.value =
            'Please enter the complete 6-digit verification code.';


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
    | Loading
    |--------------------------------------------------------------------------
    */

    loading.value =
        true;


    try {

        /*
        |--------------------------------------------------------------------------
        | Verify Code
        |--------------------------------------------------------------------------
        */

        const response =
            await axios.post(
                '/instructor-coordinator/verify-code',
                {
                    email:
                        props.email,

                    code:
                        verificationCode.value,

                    account_type:
                        normalizedAccountType.value ||
                        undefined,
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        success.value =
            response.data?.message ??
            'Verification successful.';


        /*
        |--------------------------------------------------------------------------
        | Resolve Account Type
        |--------------------------------------------------------------------------
        |
        | The backend can return:
        |
        | {
        |     account_type: "instructor"
        | }
        |
        | or:
        |
        | {
        |     account_type: "coordinator"
        | }
        |
        */

        const verifiedAccountType =
            response.data?.account_type ||
            normalizedAccountType.value ||
            '';


        /*
        |--------------------------------------------------------------------------
        | Go To Reset Password
        |--------------------------------------------------------------------------
        */

        router.visit(
            '/instructor-coordinator/reset-password',
            {
                method:
                    'get',

                data: {

                    email:
                        props.email,

                    account_type:
                        verifiedAccountType,

                },

                preserveState:
                    false,
            }
        );

    } catch (
        err
    ) {

        console.error(
            'Verification failed:',
            err
        );


        /*
        |--------------------------------------------------------------------------
        | Validation Error
        |--------------------------------------------------------------------------
        */

        if (
            err.response?.status ===
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
                'Invalid verification code.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Code Not Found
        |--------------------------------------------------------------------------
        */

        if (
            err.response?.status ===
            404
        ) {

            error.value =
                err.response.data?.message ??
                'Verification code not found or expired.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Too Many Requests
        |--------------------------------------------------------------------------
        */

        if (
            err.response?.status ===
            429
        ) {

            error.value =
                err.response.data?.message ??
                'Too many verification attempts. Please try again later.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Other Server Error
        |--------------------------------------------------------------------------
        */

        if (
            err.response
        ) {

            error.value =
                err.response.data?.message ??
                'Unable to verify the code. Please try again.';


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
| Resend OTP
|--------------------------------------------------------------------------
|
| Shared endpoint:
|
| POST /instructor-coordinator/resend-code
|
*/

const handleResend = async () => {

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Request
    |--------------------------------------------------------------------------
    */

    if (
        resendLoading.value ||
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
    | Loading
    |--------------------------------------------------------------------------
    */

    resendLoading.value =
        true;


    try {

        /*
        |--------------------------------------------------------------------------
        | Resend
        |--------------------------------------------------------------------------
        */

        const response =
            await axios.post(
                '/instructor-coordinator/resend-code',
                {
                    email:
                        props.email,

                    account_type:
                        normalizedAccountType.value ||
                        undefined,
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        success.value =
            response.data?.message ??
            'A new verification code has been sent.';


        /*
        |--------------------------------------------------------------------------
        | Reset OTP
        |--------------------------------------------------------------------------
        */

        digits.value = [
            '',
            '',
            '',
            '',
            '',
            '',
        ];


        /*
        |--------------------------------------------------------------------------
        | Focus First Input
        |--------------------------------------------------------------------------
        */

        nextTick(
            () => {

                inputRefs
                    .value[0]
                    ?.focus();

            }
        );

    } catch (
        err
    ) {

        console.error(
            'Resend verification code failed:',
            err
        );


        /*
        |--------------------------------------------------------------------------
        | Validation / Server Error
        |--------------------------------------------------------------------------
        */

        if (
            err.response
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
                'Unable to resend the verification code.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Network
        |--------------------------------------------------------------------------
        */

        error.value =
            'Network error. Please try again.';

    } finally {

        /*
        |--------------------------------------------------------------------------
        | Stop Loading
        |--------------------------------------------------------------------------
        */

        resendLoading.value =
            false;

    }

};


/*
|--------------------------------------------------------------------------
| Go Back
|--------------------------------------------------------------------------
*/

const goBack = () => {

    router.visit(
        '/instructor-coordinator/forgot-password'
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
        2rem;

    background:
        #EFEBE2;

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
        2.3rem;

    font-weight:
        700;

    line-height:
        1.25;

}


/* ==========================================================
   SUBTITLE
========================================================== */

.subtitle {

    margin:
        0
        0
        15px;

    color:
        #233E47;

    font-size:
        1rem;

    line-height:
        1.6;

}


.subtitle strong {

    color:
        #54100F;

    font-weight:
        700;

}


/* ==========================================================
   ACCOUNT TYPE
========================================================== */

.account-type-container {

    margin-bottom:
        28px;

}


.account-type-label {

    display:
        inline-flex;

    align-items:
        center;

    min-height:
        27px;

    padding:
        4px
        12px;

    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.24
        );

    border-radius:
        999px;

    background:
        rgba(
            88,
            118,
            28,
            0.09
        );

    color:
        #58761C;

    font-size:
        10px;

    font-weight:
        700;

    letter-spacing:
        0.7px;

}


/* ==========================================================
   FORM
========================================================== */

.verify-form {

    display:
        flex;

    flex-direction:
        column;

}


/* ==========================================================
   OTP
========================================================== */

.otp-container {

    display:
        flex;

    justify-content:
        space-between;

    gap:
        12px;

    margin-bottom:
        25px;

}


/* ==========================================================
   OTP BOX
========================================================== */

.otp-box {

    width:
        60px;

    height:
        60px;

    border-radius:
        14px;

    border:
        2px
        solid
        #54100F;

    background:
        #FFFFFF;

    text-align:
        center;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        1.5rem;

    font-weight:
        700;

    color:
        #54100F;

    outline:
        none;

    transition:
        border-color
        0.25s
        ease,
        box-shadow
        0.25s
        ease,
        background-color
        0.25s
        ease;

}


/* ==========================================================
   OTP HOVER
========================================================== */

.otp-box:hover:not(:disabled) {

    border-color:
        #D99202;

}


/* ==========================================================
   OTP FOCUS
========================================================== */

.otp-box:focus {

    border-color:
        #D99202;

    box-shadow:
        0
        0
        0
        4px
        rgba(
            217,
            146,
            2,
            0.15
        );

}


/* ==========================================================
   OTP DISABLED
========================================================== */

.otp-box:disabled {

    opacity:
        0.65;

    cursor:
        not-allowed;

    background:
        #F7F5F0;

}


/* ==========================================================
   MESSAGE BOX
========================================================== */

.message-box {

    width:
        100%;

    padding:
        12px
        14px;

    margin-bottom:
        20px;

    border-radius:
        10px;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    gap:
        8px;

    text-align:
        center;

    font-size:
        0.9rem;

    line-height:
        1.5;

}


/* ==========================================================
   MESSAGE ICON
========================================================== */

.message-icon {

    width:
        18px;

    height:
        18px;

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

.error-message {

    background:
        rgba(
            84,
            16,
            15,
            0.07
        );

    color:
        #54100F;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.25
        );

}


/* ==========================================================
   SUCCESS
========================================================== */

.success-message {

    background:
        rgba(
            88,
            118,
            28,
            0.09
        );

    color:
        #58761C;

    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.28
        );

}


/* ==========================================================
   RESEND
========================================================== */

.resend-row {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        30px;

}


/* ==========================================================
   DIDN'T GET
========================================================== */

.didnt-get {

    color:
        #233E47;

    font-size:
        0.95rem;

}


/* ==========================================================
   RESEND BUTTON
========================================================== */

.resend-btn {

    padding:
        4px
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
        0.95rem;

    font-weight:
        700;

    cursor:
        pointer;

    transition:
        color
        0.2s
        ease;

}


.resend-btn:hover:not(:disabled) {

    color:
        #54100F;

}


.resend-btn:disabled {

    opacity:
        0.6;

    cursor:
        not-allowed;

}


/* ==========================================================
   VERIFY BUTTON
========================================================== */

.submit-btn {

    width:
        100%;

    min-height:
        54px;

    border:
        none;

    border-radius:
        40px;

    padding:
        14px
        20px;

    background:
        #D99202;

    color:
        #FFFFFF;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        9px;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        1rem;

    font-weight:
        700;

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

    box-shadow:
        0
        6px
        14px
        rgba(
            217,
            146,
            2,
            0.25
        );

}


.submit-btn:hover:not(:disabled) {

    background:
        #C68402;

    transform:
        translateY(
            -1px
        );

}


.submit-btn:active:not(:disabled) {

    transform:
        translateY(
            1px
        );

}


.submit-btn:disabled {

    opacity:
        0.65;

    cursor:
        not-allowed;

    box-shadow:
        none;

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
        spinner
        0.8s
        linear
        infinite;

}


@keyframes spinner {

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
        28px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    flex-wrap:
        wrap;

    gap:
        5px;

    color:
        #233E47;

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
        700;

    text-decoration:
        underline;

    cursor:
        pointer;

}


.back-link:hover:not(:disabled) {

    color:
        #54100F;

}


.back-link:disabled {

    opacity:
        0.6;

    cursor:
        not-allowed;

}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (
    max-width: 900px
) {

    .right-panel {

        padding:
            1.5rem;

    }


    .title {

        font-size:
            1.9rem;

    }


    .otp-container {

        gap:
            8px;

    }


    .otp-box {

        width:
            48px;

        height:
            48px;

        font-size:
            1.2rem;

    }

}


/* ==========================================================
   MOBILE
========================================================== */

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
            0.88rem;

    }


    .otp-container {

        gap:
            6px;

    }


    .otp-box {

        width:
            42px;

        height:
            42px;

        border-radius:
            10px;

        font-size:
            1rem;

    }


    .resend-row {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            12px;

    }


    .submit-btn {

        font-size:
            0.9rem;

    }


    .footer-text,
    .back-link {

        font-size:
            12px;

    }

}

</style>