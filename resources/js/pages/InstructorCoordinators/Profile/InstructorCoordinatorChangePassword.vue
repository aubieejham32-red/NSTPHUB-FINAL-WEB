<template>
    <Admin_IC_Layout
        :user="resolvedUser"
        :role="resolvedRole"
    >
        <div class="change-password-page">

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
                                Update your password to keep your
                                {{ accountLabel }} account secure.
                            </p>

                        </div>


                        <!-- ==================================================
                             CLOSE BUTTON
                        =================================================== -->
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
                                <path
                                    d="M18 6 6 18"
                                />

                                <path
                                    d="m6 6 12 12"
                                />
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
                                    'has-error':
                                        currentPasswordError,
                                }"
                            >
                                <input
                                    id="current_password"
                                    v-model="form.current_password"
                                    :type="
                                        showCurrentPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    placeholder="Enter current password"
                                    autocomplete="current-password"
                                    @input="
                                        clearFieldError(
                                            'current_password'
                                        )
                                    "
                                />


                                <!-- ==================================================
                                     SHOW / HIDE PASSWORD
                                =================================================== -->
                                <button
                                    type="button"
                                    class="eye-button"
                                    :aria-label="
                                        showCurrentPassword
                                            ? 'Hide current password'
                                            : 'Show current password'
                                    "
                                    @click="
                                        showCurrentPassword =
                                            !showCurrentPassword
                                    "
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
                                            d="
                                                M2.062 12.348
                                                a1 1 0 0 1 0-.696
                                                C3.256 8.884 6.03 5 12 5
                                                c5.97 0 8.744 3.884
                                                9.938 6.652
                                                a1 1 0 0 1 0 .696
                                                C20.744 15.116
                                                17.97 19
                                                12 19
                                                c-5.97 0
                                                -8.744-3.884
                                                -9.938-6.652
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
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path
                                            d="m2 2 20 20"
                                        />

                                        <path
                                            d="
                                                M6.71 6.71
                                                C4.72 8.1
                                                3.5 10.15
                                                2.46 11.63
                                                a.7.7 0 0 0 0 .74
                                                C4.09 14.72
                                                7.17 19
                                                12 19
                                                c1.44 0
                                                2.73-.38
                                                3.88-.95
                                            "
                                        />

                                        <path
                                            d="
                                                M10.73 5.08
                                                A8.7 8.7 0 0 1
                                                12 5
                                                c4.83 0
                                                7.91 4.28
                                                9.54 6.63
                                                a.7.7 0 0 1 0 .74
                                                c-.49.71
                                                -1.08 1.48
                                                -1.78 2.2
                                            "
                                        />

                                        <path
                                            d="
                                                M14.12 14.12
                                                A3 3 0 0 1
                                                9.88 9.88
                                            "
                                        />
                                    </svg>

                                </button>

                            </div>


                            <p
                                v-if="currentPasswordError"
                                class="error-message"
                            >
                                {{ currentPasswordError }}
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
                                    'has-error':
                                        passwordError,
                                }"
                            >
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="
                                        showNewPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    placeholder="Enter new password"
                                    autocomplete="new-password"
                                    @input="
                                        clearFieldError(
                                            'password'
                                        )
                                    "
                                />


                                <button
                                    type="button"
                                    class="eye-button"
                                    :aria-label="
                                        showNewPassword
                                            ? 'Hide new password'
                                            : 'Show new password'
                                    "
                                    @click="
                                        showNewPassword =
                                            !showNewPassword
                                    "
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
                                            d="
                                                M2.062 12.348
                                                a1 1 0 0 1 0-.696
                                                C3.256 8.884 6.03 5 12 5
                                                c5.97 0 8.744 3.884
                                                9.938 6.652
                                                a1 1 0 0 1 0 .696
                                                C20.744 15.116
                                                17.97 19
                                                12 19
                                                c-5.97 0
                                                -8.744-3.884
                                                -9.938-6.652
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
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path
                                            d="m2 2 20 20"
                                        />

                                        <path
                                            d="
                                                M6.71 6.71
                                                C4.72 8.1
                                                3.5 10.15
                                                2.46 11.63
                                                a.7.7 0 0 0 0 .74
                                                C4.09 14.72
                                                7.17 19
                                                12 19
                                                c1.44 0
                                                2.73-.38
                                                3.88-.95
                                            "
                                        />

                                        <path
                                            d="
                                                M10.73 5.08
                                                A8.7 8.7 0 0 1
                                                12 5
                                                c4.83 0
                                                7.91 4.28
                                                9.54 6.63
                                                a.7.7 0 0 1 0 .74
                                                c-.49.71
                                                -1.08 1.48
                                                -1.78 2.2
                                            "
                                        />

                                        <path
                                            d="
                                                M14.12 14.12
                                                A3 3 0 0 1
                                                9.88 9.88
                                            "
                                        />
                                    </svg>

                                </button>

                            </div>


                            <p
                                v-if="passwordError"
                                class="error-message"
                            >
                                {{ passwordError }}
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
                                    'has-error':
                                        passwordConfirmationError,
                                }"
                            >
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="
                                        showConfirmPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    placeholder="Enter confirm password"
                                    autocomplete="new-password"
                                    @input="
                                        clearFieldError(
                                            'password_confirmation'
                                        )
                                    "
                                />


                                <button
                                    type="button"
                                    class="eye-button"
                                    :aria-label="
                                        showConfirmPassword
                                            ? 'Hide confirm password'
                                            : 'Show confirm password'
                                    "
                                    @click="
                                        showConfirmPassword =
                                            !showConfirmPassword
                                    "
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
                                            d="
                                                M2.062 12.348
                                                a1 1 0 0 1 0-.696
                                                C3.256 8.884 6.03 5 12 5
                                                c5.97 0 8.744 3.884
                                                9.938 6.652
                                                a1 1 0 0 1 0 .696
                                                C20.744 15.116
                                                17.97 19
                                                12 19
                                                c-5.97 0
                                                -8.744-3.884
                                                -9.938-6.652
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
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path
                                            d="m2 2 20 20"
                                        />

                                        <path
                                            d="
                                                M6.71 6.71
                                                C4.72 8.1
                                                3.5 10.15
                                                2.46 11.63
                                                a.7.7 0 0 0 0 .74
                                                C4.09 14.72
                                                7.17 19
                                                12 19
                                                c1.44 0
                                                2.73-.38
                                                3.88-.95
                                            "
                                        />

                                        <path
                                            d="
                                                M10.73 5.08
                                                A8.7 8.7 0 0 1
                                                12 5
                                                c4.83 0
                                                7.91 4.28
                                                9.54 6.63
                                                a.7.7 0 0 1 0 .74
                                                c-.49.71
                                                -1.08 1.48
                                                -1.78 2.2
                                            "
                                        />

                                        <path
                                            d="
                                                M14.12 14.12
                                                A3 3 0 0 1
                                                9.88 9.88
                                            "
                                        />
                                    </svg>

                                </button>

                            </div>


                            <p
                                v-if="passwordConfirmationError"
                                class="error-message"
                            >
                                {{ passwordConfirmationError }}
                            </p>

                        </div>


                        <!-- ==================================================
                             SAVE BUTTON
                        =================================================== -->
                        <div class="button-row">

                            <button
                                type="submit"
                                class="save-button"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? 'SAVING...'
                                        : 'Save Changes'
                                }}
                            </button>

                        </div>

                    </form>

                </section>

            </main>

        </div>
    </Admin_IC_Layout>
</template>


<script setup>

import {
    computed,
    ref,
} from 'vue';

import {
    router,
    useForm,
} from '@inertiajs/vue3';

import Admin_IC_Layout
    from '@/layouts/Admin_IC_Layout.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
|
| This page works with:
|
| Instructor
| Coordinator Attendance
| Coordinator Announcement
| Coordinator Schedule
|
*/

const props = defineProps({

    user: {
        type: Object,

        default: () => ({}),
    },


    role: {
        type: String,

        default: '',
    },


    accountType: {
        type: String,

        default: '',
    },


    auth: {
        type: Object,

        default: () => ({}),
    },

});


/*
|--------------------------------------------------------------------------
| Resolve User
|--------------------------------------------------------------------------
*/

const resolvedUser = computed(() => {

    if (
        props.user &&
        Object.keys(
            props.user
        ).length > 0
    ) {

        return props.user;

    }


    return (
        props.auth?.user ??
        {}
    );

});


/*
|--------------------------------------------------------------------------
| Resolve Role
|--------------------------------------------------------------------------
*/

const resolvedRole = computed(() => {

    return (
        props.role ||
        props.auth?.role ||
        ''
    );

});


/*
|--------------------------------------------------------------------------
| Resolve Account Type
|--------------------------------------------------------------------------
*/

const resolvedAccountType = computed(() => {

    if (
        props.accountType
    ) {

        return props.accountType;

    }


    if (
        props.auth?.account_type
    ) {

        return props.auth.account_type;

    }


    if (
        resolvedRole.value ===
        'instructor'
    ) {

        return 'instructor';

    }


    if (
        resolvedRole.value.startsWith(
            'coordinator-'
        )
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

    /*
    |--------------------------------------------------------------------------
    | Instructor
    |--------------------------------------------------------------------------
    */

    if (
        resolvedAccountType.value ===
        'instructor'
    ) {

        return 'Instructor';

    }


    /*
    |--------------------------------------------------------------------------
    | Attendance Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        resolvedRole.value ===
        'coordinator-attendance'
    ) {

        return 'Attendance Coordinator';

    }


    /*
    |--------------------------------------------------------------------------
    | Announcement Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        resolvedRole.value ===
        'coordinator-announcement'
    ) {

        return 'Announcement Coordinator';

    }


    /*
    |--------------------------------------------------------------------------
    | Schedule Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        resolvedRole.value ===
        'coordinator-schedule'
    ) {

        return 'Schedule Coordinator';

    }


    if (
        resolvedAccountType.value ===
        'coordinator'
    ) {

        return 'Coordinator';

    }


    return 'NSTP Staff';

});


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    current_password:
        '',

    password:
        '',

    password_confirmation:
        '',

});


/*
|--------------------------------------------------------------------------
| Local Validation Errors
|--------------------------------------------------------------------------
*/

const localErrors = ref({

    current_password:
        '',

    password:
        '',

    password_confirmation:
        '',

});


/*
|--------------------------------------------------------------------------
| Password Visibility
|--------------------------------------------------------------------------
*/

const showCurrentPassword =
    ref(
        false
    );


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
| Current Password Error
|--------------------------------------------------------------------------
*/

const currentPasswordError = computed(() => {

    return (
        localErrors.value.current_password ||
        form.errors.current_password ||
        ''
    );

});


/*
|--------------------------------------------------------------------------
| Password Error
|--------------------------------------------------------------------------
*/

const passwordError = computed(() => {

    return (
        localErrors.value.password ||
        form.errors.password ||
        ''
    );

});


/*
|--------------------------------------------------------------------------
| Password Confirmation Error
|--------------------------------------------------------------------------
*/

const passwordConfirmationError = computed(() => {

    return (
        localErrors.value.password_confirmation ||
        form.errors.password_confirmation ||
        ''
    );

});


/*
|--------------------------------------------------------------------------
| Clear Field Error
|--------------------------------------------------------------------------
*/

const clearFieldError = (
    field
) => {

    localErrors.value[field] =
        '';


    form.clearErrors(
        field
    );

};


/*
|--------------------------------------------------------------------------
| Clear Local Errors
|--------------------------------------------------------------------------
*/

const clearLocalErrors = () => {

    localErrors.value = {

        current_password:
            '',

        password:
            '',

        password_confirmation:
            '',

    };

};


/*
|--------------------------------------------------------------------------
| Validate Form
|--------------------------------------------------------------------------
*/

const validateForm = () => {

    /*
    |--------------------------------------------------------------------------
    | Reset Local Errors
    |--------------------------------------------------------------------------
    */

    clearLocalErrors();


    let valid =
        true;


    /*
    |--------------------------------------------------------------------------
    | Current Password Required
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We only check whether a value exists here.
    |
    | We do NOT trim or modify the actual password that is submitted,
    | because spaces may legitimately be part of a password.
    |
    */

    if (
        !form.current_password
    ) {

        localErrors.value.current_password =
            'Please enter your current password.';

        valid =
            false;

    }


    /*
    |--------------------------------------------------------------------------
    | New Password Required
    |--------------------------------------------------------------------------
    */

    if (
        !form.password
    ) {

        localErrors.value.password =
            'Please enter your new password.';

        valid =
            false;

    } else if (
        form.password.length <
        8
    ) {

        localErrors.value.password =
            'New password must contain at least 8 characters.';

        valid =
            false;

    }


    /*
    |--------------------------------------------------------------------------
    | Confirmation Required
    |--------------------------------------------------------------------------
    */

    if (
        !form.password_confirmation
    ) {

        localErrors.value.password_confirmation =
            'Please confirm your new password.';

        valid =
            false;

    } else if (
        form.password !==
        form.password_confirmation
    ) {

        localErrors.value.password_confirmation =
            'The password confirmation does not match.';

        valid =
            false;

    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Same Password
    |--------------------------------------------------------------------------
    */

    if (
        form.current_password &&
        form.password &&
        form.current_password ===
            form.password
    ) {

        localErrors.value.password =
            'Your new password must be different from your current password.';

        valid =
            false;

    }


    return valid;

};


/*
|--------------------------------------------------------------------------
| Submit Password
|--------------------------------------------------------------------------
|
| Shared endpoint for:
|
| Instructor
| Coordinator
|
*/

const submitPassword = () => {

    if (
        form.processing
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Local Validation
    |--------------------------------------------------------------------------
    */

    if (
        !validateForm()
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    form.post(
        '/instructor-coordinator/change-password',
        {
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
                | Reset Form
                |--------------------------------------------------------------------------
                */

                form.reset();


                /*
                |--------------------------------------------------------------------------
                | Reset Visibility
                |--------------------------------------------------------------------------
                */

                showCurrentPassword.value =
                    false;

                showNewPassword.value =
                    false;

                showConfirmPassword.value =
                    false;


                /*
                |--------------------------------------------------------------------------
                | Clear Local Errors
                |--------------------------------------------------------------------------
                */

                clearLocalErrors();

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Close Page
|--------------------------------------------------------------------------
|
| Return to the shared Instructor / Coordinator profile.
|
*/

const closePage = () => {

    router.visit(
        '/instructor-coordinator/profile'
    );

};

</script>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/* ==========================================================
   PAGE
========================================================== */

.change-password-page {

    width:
        100%;

    min-height:
        100%;

    background:
        #EFEBE2;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    color:
        #000D12;

    box-sizing:
        border-box;

}


/* ==========================================================
   PAGE CONTENT
========================================================== */

.page-content {

    width:
        100%;

    min-height:
        100%;

    display:
        flex;

    justify-content:
        center;

    align-items:
        flex-start;

    padding:
        60px
        40px
        90px;

    box-sizing:
        border-box;

}


/* ==========================================================
   PASSWORD CARD
========================================================== */

.password-card {

    width:
        100%;

    max-width:
        1050px;

    min-height:
        620px;

    background:
        #FFFFFF;

    border:
        1.5px
        solid
        #BEBEBE;

    border-radius:
        18px;

    padding:
        65px
        90px
        55px;

    box-sizing:
        border-box;

    box-shadow:
        0
        7px
        24px
        rgba(
            0,
            0,
            0,
            0.06
        );

}


/* ==========================================================
   CARD HEADER
========================================================== */

.card-header {

    display:
        flex;

    align-items:
        flex-start;

    justify-content:
        space-between;

    gap:
        40px;

    margin-bottom:
        55px;

}


/* ==========================================================
   TITLE
========================================================== */

.header-text h1 {

    margin:
        0;

    color:
        #54100F;

    font-size:
        40px;

    font-weight:
        700;

    line-height:
        1.2;

}


/* ==========================================================
   SUBTITLE
========================================================== */

.header-text p {

    margin:
        24px
        0
        0;

    color:
        #233E47;

    font-size:
        19px;

    font-weight:
        400;

    line-height:
        1.6;

}


/* ==========================================================
   CLOSE BUTTON
========================================================== */

.close-button {

    width:
        58px;

    height:
        58px;

    padding:
        0;

    margin-top:
        -8px;

    border:
        none;

    border-radius:
        50%;

    background:
        transparent;

    color:
        #54100F;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    flex-shrink:
        0;

    cursor:
        pointer;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.2s
        ease;

}


.close-button svg {

    width:
        50px;

    height:
        50px;

}


.close-button:hover {

    background:
        #EFEBE2;

    transform:
        rotate(
            4deg
        );

}


/* ==========================================================
   FORM
========================================================== */

.password-form {

    width:
        100%;

}


/* ==========================================================
   FORM GROUP
========================================================== */

.form-group {

    width:
        100%;

    margin-bottom:
        29px;

}


/* ==========================================================
   LABEL
========================================================== */

.form-group label {

    display:
        block;

    margin:
        0
        0
        10px
        34px;

    color:
        #54100F;

    font-family:
        Georgia,
        'Times New Roman',
        serif;

    font-size:
        22px;

    font-weight:
        400;

}


/* ==========================================================
   PASSWORD INPUT WRAPPER
========================================================== */

.password-input-wrapper {

    width:
        100%;

    height:
        57px;

    border:
        2px
        solid
        #233E47;

    border-radius:
        30px;

    background:
        #FFFFFF;

    display:
        flex;

    align-items:
        center;

    overflow:
        hidden;

    box-sizing:
        border-box;

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

.password-input-wrapper:focus-within {

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
            0.12
        );

}


/* ==========================================================
   INPUT
========================================================== */

.password-input-wrapper input {

    width:
        100%;

    height:
        100%;

    border:
        none;

    outline:
        none;

    background:
        transparent;

    padding:
        0
        25px
        0
        34px;

    box-sizing:
        border-box;

    color:
        #000D12;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        15px;

    font-weight:
        400;

}


.password-input-wrapper input::placeholder {

    color:
        #233E47;

    opacity:
        0.75;

}


/* ==========================================================
   EYE BUTTON
========================================================== */

.eye-button {

    width:
        65px;

    height:
        100%;

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

    cursor:
        pointer;

    flex-shrink:
        0;

    transition:
        color
        0.2s
        ease;

}


.eye-button:hover {

    color:
        #54100F;

}


.eye-button svg {

    width:
        27px;

    height:
        27px;

}


/* ==========================================================
   ERROR STATE
========================================================== */

.password-input-wrapper.has-error {

    border-color:
        #54100F;

    box-shadow:
        0
        0
        0
        4px
        rgba(
            84,
            16,
            15,
            0.08
        );

}


/* ==========================================================
   ERROR MESSAGE
========================================================== */

.error-message {

    margin:
        8px
        0
        0
        34px;

    color:
        #54100F;

    font-size:
        13px;

    font-weight:
        600;

}


/* ==========================================================
   BUTTON ROW
========================================================== */

.button-row {

    width:
        100%;

    display:
        flex;

    justify-content:
        flex-end;

    margin-top:
        38px;

}


/* ==========================================================
   SAVE BUTTON
========================================================== */

.save-button {

    width:
        320px;

    height:
        58px;

    border:
        none;

    border-radius:
        10px;

    background:
        #58761C;

    color:
        #FFFFFF;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        17px;

    font-weight:
        500;

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


.save-button:hover:not(:disabled) {

    background:
        #476017;

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        8px
        20px
        rgba(
            88,
            118,
            28,
            0.22
        );

}


.save-button:active:not(:disabled) {

    transform:
        translateY(
            0
        );

}


.save-button:disabled {

    opacity:
        0.6;

    cursor:
        not-allowed;

}


/* ==========================================================
   DESKTOP
========================================================== */

@media (
    max-width: 1200px
) {

    .password-card {

        max-width:
            950px;

        min-height:
            590px;

        padding:
            58px
            75px
            48px;

    }


    .page-content {

        padding-top:
            55px;

    }

}


/* ==========================================================
   TABLET
========================================================== */

@media (
    max-width: 900px
) {

    .page-content {

        padding:
            45px
            30px
            70px;

    }


    .password-card {

        max-width:
            760px;

        min-height:
            auto;

        padding:
            50px
            55px
            42px;

    }


    .header-text h1 {

        font-size:
            34px;

    }


    .header-text p {

        font-size:
            17px;

    }


    .form-group label {

        font-size:
            20px;

    }


    .password-input-wrapper {

        height:
            53px;

    }


    .save-button {

        width:
            290px;

        height:
            54px;

    }

}


/* ==========================================================
   SMALL TABLET
========================================================== */

@media (
    max-width: 768px
) {

    .page-content {

        padding:
            40px
            22px
            60px;

    }


    .password-card {

        padding:
            42px
            35px
            35px;

    }


    .card-header {

        margin-bottom:
            40px;

    }


    .header-text h1 {

        font-size:
            30px;

    }


    .header-text p {

        margin-top:
            16px;

        font-size:
            15px;

    }


    .close-button {

        width:
            44px;

        height:
            44px;

    }


    .close-button svg {

        width:
            37px;

        height:
            37px;

    }


    .form-group {

        margin-bottom:
            24px;

    }


    .form-group label {

        margin-left:
            20px;

        font-size:
            18px;

    }


    .password-input-wrapper {

        height:
            49px;

    }


    .password-input-wrapper input {

        padding-left:
            20px;

        font-size:
            13px;

    }


    .eye-button {

        width:
            52px;

    }


    .eye-button svg {

        width:
            22px;

        height:
            22px;

    }


    .error-message {

        margin-left:
            20px;

    }

}


/* ==========================================================
   MOBILE
========================================================== */

@media (
    max-width: 576px
) {

    .page-content {

        padding:
            28px
            15px
            45px;

    }


    .password-card {

        width:
            100%;

        min-height:
            auto;

        border-radius:
            14px;

        padding:
            30px
            20px
            26px;

    }


    .card-header {

        gap:
            15px;

        margin-bottom:
            30px;

    }


    .header-text h1 {

        font-size:
            25px;

    }


    .header-text p {

        font-size:
            13px;

        line-height:
            1.6;

    }


    .close-button {

        width:
            38px;

        height:
            38px;

        margin-top:
            -4px;

    }


    .close-button svg {

        width:
            31px;

        height:
            31px;

    }


    .form-group {

        margin-bottom:
            21px;

    }


    .form-group label {

        margin:
            0
            0
            7px
            15px;

        font-size:
            16px;

    }


    .password-input-wrapper {

        height:
            46px;

    }


    .password-input-wrapper input {

        padding-left:
            15px;

        font-size:
            12px;

    }


    .eye-button {

        width:
            46px;

    }


    .eye-button svg {

        width:
            20px;

        height:
            20px;

    }


    .error-message {

        margin-left:
            15px;

    }


    .button-row {

        margin-top:
            27px;

    }


    .save-button {

        width:
            100%;

        height:
            49px;

        font-size:
            14px;

    }

}

</style>