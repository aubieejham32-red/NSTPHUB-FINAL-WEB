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
                    New Password
                </h1>


                <!-- ==================================================
                     SUBTITLE
                =================================================== -->
                <p class="subtitle">
                    Establish a unique account password known
                    exclusively to you.
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
                     RESET PASSWORD FORM
                =================================================== -->
                <form
                    class="reset-form"
                    @submit.prevent="handleResetPassword"
                >

                    <!-- ==============================================
                         NEW PASSWORD
                    =============================================== -->
                    <div class="form-group">

                        <label
                            for="password"
                            class="label"
                        >
                            New Password
                        </label>


                        <div
                            class="password-wrapper"
                            :class="{
                                'password-wrapper--error':
                                    errorMessage,
                            }"
                        >

                            <!-- LOCK ICON -->
                            <svg
                                class="input-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <rect
                                    x="4"
                                    y="10"
                                    width="16"
                                    height="10"
                                    rx="2"
                                />

                                <path
                                    d="M8 10V7a4 4 0 0 1 8 0v3"
                                />
                            </svg>


                            <input
                                id="password"
                                v-model="newPassword"
                                :type="
                                    showNewPassword
                                        ? 'text'
                                        : 'password'
                                "
                                class="input-field"
                                placeholder="Enter your new password"
                                autocomplete="new-password"
                                :disabled="loading"
                                required
                                @input="clearMessages"
                            >


                            <!-- SHOW / HIDE -->
                            <button
                                type="button"
                                class="eye-button"
                                :disabled="loading"
                                :aria-label="
                                    showNewPassword
                                        ? 'Hide password'
                                        : 'Show password'
                                "
                                @click="
                                    showNewPassword =
                                        !showNewPassword
                                "
                            >

                                <!-- EYE OPEN -->
                                <svg
                                    v-if="showNewPassword"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="
                                            M2.5 12
                                            s3.5-6
                                            9.5-6
                                            9.5 6
                                            9.5 6
                                            -3.5 6
                                            -9.5 6
                                            -9.5-6
                                            -9.5-6Z
                                        "
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
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="m3 3 18 18"
                                    />

                                    <path
                                        d="
                                            M10.6 6.2
                                            A9.7 9.7 0 0 1
                                            12 6
                                            c6 0
                                            9.5 6
                                            9.5 6
                                            a17 17 0 0 1
                                            -2.1 2.8
                                        "
                                    />

                                    <path
                                        d="
                                            M6.6 6.7
                                            C3.9 8.5
                                            2.5 12
                                            2.5 12
                                            S6 18
                                            12 18
                                            c1.3 0
                                            2.4-.3
                                            3.4-.7
                                        "
                                    />

                                    <path
                                        d="
                                            M10 10
                                            a3 3 0 0 0
                                            4 4
                                        "
                                    />
                                </svg>

                            </button>

                        </div>

                    </div>


                    <!-- ==============================================
                         CONFIRM PASSWORD
                    =============================================== -->
                    <div class="form-group">

                        <label
                            for="password_confirmation"
                            class="label"
                        >
                            Confirm Password
                        </label>


                        <div
                            class="password-wrapper"
                            :class="{
                                'password-wrapper--error':
                                    errorMessage,
                            }"
                        >

                            <!-- LOCK ICON -->
                            <svg
                                class="input-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <rect
                                    x="4"
                                    y="10"
                                    width="16"
                                    height="10"
                                    rx="2"
                                />

                                <path
                                    d="M8 10V7a4 4 0 0 1 8 0v3"
                                />
                            </svg>


                            <input
                                id="password_confirmation"
                                v-model="confirmPassword"
                                :type="
                                    showConfirmPassword
                                        ? 'text'
                                        : 'password'
                                "
                                class="input-field"
                                placeholder="Re-enter your password"
                                autocomplete="new-password"
                                :disabled="loading"
                                required
                                @input="clearMessages"
                            >


                            <!-- SHOW / HIDE -->
                            <button
                                type="button"
                                class="eye-button"
                                :disabled="loading"
                                :aria-label="
                                    showConfirmPassword
                                        ? 'Hide password'
                                        : 'Show password'
                                "
                                @click="
                                    showConfirmPassword =
                                        !showConfirmPassword
                                "
                            >

                                <!-- EYE OPEN -->
                                <svg
                                    v-if="showConfirmPassword"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="
                                            M2.5 12
                                            s3.5-6
                                            9.5-6
                                            9.5 6
                                            9.5 6
                                            -3.5 6
                                            -9.5 6
                                            -9.5-6
                                            -9.5-6Z
                                        "
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
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="m3 3 18 18"
                                    />

                                    <path
                                        d="
                                            M10.6 6.2
                                            A9.7 9.7 0 0 1
                                            12 6
                                            c6 0
                                            9.5 6
                                            9.5 6
                                            a17 17 0 0 1
                                            -2.1 2.8
                                        "
                                    />

                                    <path
                                        d="
                                            M6.6 6.7
                                            C3.9 8.5
                                            2.5 12
                                            2.5 12
                                            S6 18
                                            12 18
                                            c1.3 0
                                            2.4-.3
                                            3.4-.7
                                        "
                                    />

                                    <path
                                        d="
                                            M10 10
                                            a3 3 0 0 0
                                            4 4
                                        "
                                    />
                                </svg>

                            </button>

                        </div>

                    </div>


                    <!-- ==============================================
                         PASSWORD REQUIREMENT
                    =============================================== -->
                    <div class="password-requirement">

                        <span
                            class="requirement-dot"
                            :class="{
                                valid:
                                    newPassword.length >=
                                    8,
                            }"
                        ></span>

                        <span>
                            Password must contain at least 8 characters.
                        </span>

                    </div>


                    <!-- ==============================================
                         ERROR
                    =============================================== -->
                    <div
                        v-if="errorMessage"
                        class="message-box error-msg"
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
                            {{ errorMessage }}
                        </span>

                    </div>


                    <!-- ==============================================
                         SUCCESS
                    =============================================== -->
                    <div
                        v-if="successMessage"
                        class="message-box success-msg"
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
                            {{ successMessage }}
                        </span>

                    </div>


                    <!-- ==============================================
                         RESET BUTTON
                    =============================================== -->
                    <button
                        type="submit"
                        class="submit-btn"
                        :disabled="
                            loading ||
                            !canSubmit
                        "
                    >

                        <!-- LOADING -->
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
                                    ? 'Resetting...'
                                    : 'Reset Password'
                            }}
                        </span>

                    </button>

                </form>

            </div>

        </main>

    </div>
</template>


<script setup>

import {
    computed,
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
| Account email being reset.
|
|
| accountType:
|
| instructor
| coordinator
|
| The backend should preferably also store the verified account type
| in the session so users cannot change it manually.
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
| Form State
|--------------------------------------------------------------------------
*/

const newPassword =
    ref(
        ''
    );


const confirmPassword =
    ref(
        ''
    );


/*
|--------------------------------------------------------------------------
| Password Visibility
|--------------------------------------------------------------------------
*/

const showNewPassword =
    ref(
        false
    );


const showConfirmPassword =
    ref(
        false
    );


/*
|--------------------------------------------------------------------------
| Request State
|--------------------------------------------------------------------------
*/

const loading =
    ref(
        false
    );


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

const errorMessage =
    ref(
        ''
    );


const successMessage =
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
| Can Submit
|--------------------------------------------------------------------------
*/

const canSubmit = computed(() => {

    return (
        newPassword.value.length >=
            8 &&
        confirmPassword.value.length >
            0
    );

});


/*
|--------------------------------------------------------------------------
| Clear Messages
|--------------------------------------------------------------------------
*/

const clearMessages = () => {

    errorMessage.value =
        '';


    successMessage.value =
        '';

};


/*
|--------------------------------------------------------------------------
| Reset Password
|--------------------------------------------------------------------------
|
| Shared endpoint:
|
| POST /instructor-coordinator/reset-password
|
| The backend determines whether the verified account belongs to:
|
| Instructor
| Coordinator
|
*/

const handleResetPassword = async () => {

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Submission
    |--------------------------------------------------------------------------
    */

    if (
        loading.value
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Clear Messages
    |--------------------------------------------------------------------------
    */

    errorMessage.value =
        '';


    successMessage.value =
        '';


    /*
    |--------------------------------------------------------------------------
    | New Password Required
    |--------------------------------------------------------------------------
    */

    if (
        !newPassword.value
    ) {

        errorMessage.value =
            'Please enter your new password.';


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Minimum Length
    |--------------------------------------------------------------------------
    */

    if (
        newPassword.value.length <
        8
    ) {

        errorMessage.value =
            'Password must contain at least 8 characters.';


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Confirmation Required
    |--------------------------------------------------------------------------
    */

    if (
        !confirmPassword.value
    ) {

        errorMessage.value =
            'Please confirm your new password.';


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Password Match
    |--------------------------------------------------------------------------
    */

    if (
        newPassword.value !==
        confirmPassword.value
    ) {

        errorMessage.value =
            'Passwords do not match.';


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Start Loading
    |--------------------------------------------------------------------------
    */

    loading.value =
        true;


    try {

        /*
        |--------------------------------------------------------------------------
        | Reset Password
        |--------------------------------------------------------------------------
        */

        const response =
            await axios.post(
                '/instructor-coordinator/reset-password',
                {
                    /*
                    |--------------------------------------------------------------------------
                    | Email
                    |--------------------------------------------------------------------------
                    */

                    email:
                        props.email,


                    /*
                    |--------------------------------------------------------------------------
                    | Account Type
                    |--------------------------------------------------------------------------
                    |
                    | The server should still use its verified session value as
                    | the source of truth.
                    |
                    */

                    account_type:
                        normalizedAccountType.value ||
                        undefined,


                    /*
                    |--------------------------------------------------------------------------
                    | Password
                    |--------------------------------------------------------------------------
                    */

                    password:
                        newPassword.value,


                    /*
                    |--------------------------------------------------------------------------
                    | Confirmation
                    |--------------------------------------------------------------------------
                    */

                    password_confirmation:
                        confirmPassword.value,

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        successMessage.value =
            response.data?.message ??
            'Password reset successfully.';


        /*
        |--------------------------------------------------------------------------
        | Resolve Account Type
        |--------------------------------------------------------------------------
        */

        const accountType =
            response.data?.account_type ||
            normalizedAccountType.value ||
            '';


        /*
        |--------------------------------------------------------------------------
        | Go To Success Page
        |--------------------------------------------------------------------------
        */

        router.visit(
            '/instructor-coordinator/password-reset-success',
            {
                method:
                    'get',

                data: {

                    email:
                        props.email,

                    account_type:
                        accountType,

                },

                preserveState:
                    false,
            }
        );

    } catch (
        error
    ) {

        console.error(
            'Password reset failed:',
            error
        );


        /*
        |--------------------------------------------------------------------------
        | Validation Error
        |--------------------------------------------------------------------------
        */

        if (
            error.response?.status ===
            422
        ) {

            /*
            |--------------------------------------------------------------------------
            | Laravel Validation Errors
            |--------------------------------------------------------------------------
            */

            if (
                error.response.data?.errors
            ) {

                errorMessage.value =
                    Object.values(
                        error.response.data.errors
                    )
                        .flat()
                        .join(
                            ' '
                        );


                return;

            }


            errorMessage.value =
                error.response.data?.message ??
                'Unable to reset password.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Account Not Found
        |--------------------------------------------------------------------------
        */

        if (
            error.response?.status ===
            404
        ) {

            errorMessage.value =
                error.response.data?.message ??
                'Instructor or Coordinator account not found.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Forbidden / Verification Missing
        |--------------------------------------------------------------------------
        */

        if (
            error.response?.status ===
                403 ||
            error.response?.status ===
                419
        ) {

            errorMessage.value =
                error.response.data?.message ??
                'Your password reset session has expired. Please request a new verification code.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Server Error
        |--------------------------------------------------------------------------
        */

        if (
            error.response
        ) {

            errorMessage.value =
                error.response.data?.message ??
                'Something went wrong. Please try again.';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Network Error
        |--------------------------------------------------------------------------
        */

        errorMessage.value =
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
        2rem;

    background:
        #EFEBE2;

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


/* ==========================================================
   ACCOUNT TYPE
========================================================== */

.account-type-container {

    margin-bottom:
        30px;

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
            0.25
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

.reset-form {

    display:
        flex;

    flex-direction:
        column;

    gap:
        24px;

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
        8px;

}


/* ==========================================================
   LABEL
========================================================== */

.label {

    color:
        #233E47;

    font-size:
        1rem;

    font-weight:
        600;

}


/* ==========================================================
   PASSWORD WRAPPER
========================================================== */

.password-wrapper {

    width:
        100%;

    min-height:
        56px;

    display:
        flex;

    align-items:
        center;

    overflow:
        hidden;

    border:
        2px
        solid
        #D99202;

    border-radius:
        12px;

    background:
        #FFFFFF;

    box-sizing:
        border-box;

    transition:
        border-color
        0.25s
        ease,
        box-shadow
        0.25s
        ease;

}


/* ==========================================================
   FOCUS
========================================================== */

.password-wrapper:focus-within {

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
            0.15
        );

}


/* ==========================================================
   ERROR BORDER
========================================================== */

.password-wrapper--error {

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
        54px;

    padding:
        0
        12px;

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
        0.95rem;

    box-sizing:
        border-box;

}


/* ==========================================================
   PLACEHOLDER
========================================================== */

.input-field::placeholder {

    color:
        #8C98A4;

}


/* ==========================================================
   DISABLED INPUT
========================================================== */

.input-field:disabled {

    cursor:
        not-allowed;

    opacity:
        0.65;

}


/* ==========================================================
   EYE BUTTON
========================================================== */

.eye-button {

    width:
        52px;

    height:
        54px;

    padding:
        0;

    border:
        none;

    background:
        transparent;

    color:
        #233E47;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    flex-shrink:
        0;

    cursor:
        pointer;

    transition:
        color
        0.2s
        ease,
        background-color
        0.2s
        ease;

}


.eye-button:hover:not(:disabled) {

    color:
        #D99202;

    background:
        rgba(
            217,
            146,
            2,
            0.06
        );

}


.eye-button:disabled {

    opacity:
        0.5;

    cursor:
        not-allowed;

}


.eye-button svg {

    width:
        21px;

    height:
        21px;

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
   PASSWORD REQUIREMENT
========================================================== */

.password-requirement {

    margin-top:
        -10px;

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    color:
        #233E47;

    font-size:
        12px;

}


/* ==========================================================
   REQUIREMENT DOT
========================================================== */

.requirement-dot {

    width:
        8px;

    height:
        8px;

    flex-shrink:
        0;

    border-radius:
        50%;

    background:
        #BEBEBE;

    transition:
        background-color
        0.2s
        ease;

}


.requirement-dot.valid {

    background:
        #58761C;

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

.error-msg {

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

    font-weight:
        500;

}


/* ==========================================================
   SUCCESS
========================================================== */

.success-msg {

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

    font-weight:
        500;

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
        40px;

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
        1rem;

    font-weight:
        700;

    cursor:
        pointer;

    transition:
        background-color
        0.25s
        ease,
        transform
        0.25s
        ease,
        box-shadow
        0.25s
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


/* ==========================================================
   SUBMIT HOVER
========================================================== */

.submit-btn:hover:not(:disabled) {

    background:
        #C68402;

    transform:
        translateY(
            -1px
        );

}


/* ==========================================================
   SUBMIT ACTIVE
========================================================== */

.submit-btn:active:not(:disabled) {

    transform:
        translateY(
            1px
        );

}


/* ==========================================================
   DISABLED
========================================================== */

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
   TABLET
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

}


/* ==========================================================
   MOBILE
========================================================== */

@media (
    max-width: 600px
) {

    .page-container {

        flex-direction:
            column;

    }


    .right-panel {

        width:
            100%;

        padding:
            35px
            22px;

    }


    .title {

        font-size:
            1.7rem;

    }


    .subtitle {

        font-size:
            0.95rem;

    }


    .input-field {

        font-size:
            0.9rem;

    }


    .submit-btn {

        font-size:
            0.95rem;

    }

}


/* ==========================================================
   SMALL MOBILE
========================================================== */

@media (
    max-width: 420px
) {

    .right-panel {

        padding:
            30px
            16px;

    }


    .title {

        font-size:
            1.55rem;

    }


    .label {

        font-size:
            0.9rem;

    }


    .password-wrapper {

        min-height:
            52px;

    }


    .input-field {

        height:
            50px;

    }


    .eye-button {

        width:
            48px;

        height:
            50px;

    }

}

</style>