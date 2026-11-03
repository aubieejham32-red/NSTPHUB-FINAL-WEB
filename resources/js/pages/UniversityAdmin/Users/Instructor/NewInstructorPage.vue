<script setup>

import {
    ref,
} from 'vue';

import {
    router,
} from '@inertiajs/vue3';


/*
|--------------------------------------------------------------------------
| Layouts
|--------------------------------------------------------------------------
*/

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import NewUserLayout
    from '@/layouts/NewUserLayout.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    instructor: {
        type: Object,
        required: true,
    },

});


/*
|--------------------------------------------------------------------------
| Processing States
|--------------------------------------------------------------------------
*/

const sendingEmail =
    ref(false);

const processing =
    ref(false);

const emailSent =
    ref(false);

const emailMessage =
    ref('');

const emailError =
    ref('');


/*
|--------------------------------------------------------------------------
| Back To Instructor List
|--------------------------------------------------------------------------
*/

const goBackToInstructors = () => {

    router.visit(
        '/university-admin/instructors'
    );

};


/*
|--------------------------------------------------------------------------
| Get Temporary Password
|--------------------------------------------------------------------------
|
| The password stored in the database is hashed, so Laravel cannot retrieve
| the original password from the database.
|
| This page should receive either:
|
| instructor.password
|
| or:
|
| instructor.temporary_password
|
*/

const getTemporaryPassword = () => {

    return (
        props.instructor?.temporary_password ??
        props.instructor?.password ??
        ''
    );

};


/*
|--------------------------------------------------------------------------
| Send Instructor Email
|--------------------------------------------------------------------------
*/

const sendEmail = () => {

    /*
    |--------------------------------------------------------------------------
    | Prevent Multiple Requests
    |--------------------------------------------------------------------------
    */

    if (
        sendingEmail.value ||
        emailSent.value
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Make Sure Instructor Has ID
    |--------------------------------------------------------------------------
    */

    if (
        !props.instructor?.id
    ) {

        emailError.value =
            'Instructor ID is missing.';

        console.error(
            'Instructor ID is missing.'
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Get Temporary Password
    |--------------------------------------------------------------------------
    */

    const temporaryPassword =
        getTemporaryPassword();


    if (
        !temporaryPassword
    ) {

        emailError.value =
            'Temporary password is missing. The instructor password must be passed to this page after creating the account.';

        console.error(
            'Temporary instructor password is missing.'
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Reset Messages
    |--------------------------------------------------------------------------
    */

    emailMessage.value =
        '';

    emailError.value =
        '';

    sendingEmail.value =
        true;


    /*
    |--------------------------------------------------------------------------
    | Send Email Request
    |--------------------------------------------------------------------------
    */

    router.post(
        `/university-admin/instructors/${props.instructor.id}/send-email`,
        {
            temporary_password:
                temporaryPassword,
        },
        {
            preserveScroll: true,

            preserveState: true,

            onSuccess: () => {

                emailSent.value =
                    true;

                emailMessage.value =
                    `Account information was successfully sent to ${props.instructor.email}.`;

                console.log(
                    'Instructor email sent successfully.'
                );

            },

            onError: (errors) => {

                emailError.value =
                    errors.send_email ??
                    errors.temporary_password ??
                    errors.email ??
                    'Unable to send the instructor email. Please check your mail configuration.';

                console.error(
                    'Unable to send instructor email:',
                    errors
                );

            },

            onFinish: () => {

                sendingEmail.value =
                    false;

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Done
|--------------------------------------------------------------------------
*/

const done = () => {

    if (
        processing.value
    ) {
        return;
    }


    processing.value =
        true;


    router.visit(
        '/university-admin/instructors',
        {
            onFinish: () => {

                processing.value =
                    false;

            },
        }
    );

};

</script>


<template>

    <!-- ============================================================
         UNIVERSITY ADMIN DASHBOARD LAYOUT
    ============================================================= -->

    <UniversityAdminDashLayout
        current-route="users"
    >

        <!-- ========================================================
             NEW INSTRUCTOR PAGE
        ========================================================= -->

        <section class="new-instructor-page">


            <!-- ====================================================
                 BACK BUTTON
            ===================================================== -->

            <div class="back-area">

                <button
                    type="button"
                    class="back-button"
                    title="Back to Instructors"
                    aria-label="Back to instructors"
                    @click="goBackToInstructors"
                >

                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >

                        <path
                            d="
                                M20 11H7.83
                                L13.42 5.41
                                L12 4
                                L4 12
                                L12 20
                                L13.42 18.59
                                L7.83 13H20V11Z
                            "
                            fill="currentColor"
                        />

                    </svg>

                </button>

            </div>


            <!-- ====================================================
                 NEW USER LAYOUT
            ===================================================== -->

            <div class="new-user-container">

                <NewUserLayout
                    title="New Instructor"

                    :user="instructor"

                    :show-role="false"

                    :show-password="true"

                    :show-access-code="true"

                    :sending-email="sendingEmail"

                    :processing="processing"

                    :send-email-button-text="
                        emailSent
                            ? 'EMAIL SENT'
                            : 'SEND EMAIL'
                    "

                    done-button-text="DONE"

                    @send-email="sendEmail"

                    @done="done"
                />


                <!-- ================================================
                     EMAIL SUCCESS MESSAGE
                ================================================= -->

                <div
                    v-if="emailMessage"
                    class="email-message email-message-success"
                >

                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            fill="currentColor"
                            d="
                                M9 16.17
                                4.83 12
                                3.41 13.41
                                9 19
                                21 7
                                19.59 5.59
                                9 16.17Z
                            "
                        />
                    </svg>

                    <span>
                        {{ emailMessage }}
                    </span>

                </div>


                <!-- ================================================
                     EMAIL ERROR MESSAGE
                ================================================= -->

                <div
                    v-if="emailError"
                    class="email-message email-message-error"
                >

                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            fill="currentColor"
                            d="
                                M12 2
                                C6.48 2 2 6.48 2 12
                                S6.48 22 12 22
                                22 17.52 22 12
                                17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z
                            "
                        />
                    </svg>

                    <span>
                        {{ emailError }}
                    </span>

                </div>

            </div>

        </section>

    </UniversityAdminDashLayout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| New Instructor Page
|--------------------------------------------------------------------------
*/

.new-instructor-page {
    width: 100%;

    min-width: 0;

    position: relative;

    margin: 0;

    padding:
        0
        0
        40px;

    box-sizing: border-box;

    background-color: transparent;
}


/*
|--------------------------------------------------------------------------
| Back Area
|--------------------------------------------------------------------------
*/

.back-area {
    width: 100%;

    display: flex;

    align-items: center;

    justify-content: flex-start;

    margin:
        0
        0
        16px;

    padding: 0;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Back Button
|--------------------------------------------------------------------------
*/

.back-button {
    width: 50px;
    height: 50px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    flex: 0 0 50px;

    margin: 0;

    padding: 0;

    border: none;

    outline: none;

    background-color: transparent;

    color: #54100F;

    cursor: pointer;

    transition:
        color 0.2s ease,
        transform 0.2s ease;

    box-sizing: border-box;
}


.back-button svg {
    display: block;

    width: 44px;
    height: 44px;

    pointer-events: none;
}


.back-button:hover {
    color: #D99202;

    transform:
        translateX(-3px);
}


.back-button:focus-visible {
    outline:
        2px solid
        #D99202;

    outline-offset: 2px;

    border-radius: 5px;
}


/*
|--------------------------------------------------------------------------
| New User Container
|--------------------------------------------------------------------------
*/

.new-user-container {
    width: 100%;

    min-width: 0;

    margin: 0;

    padding: 0;

    position: relative;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Email Messages
|--------------------------------------------------------------------------
*/

.email-message {
    width: 100%;

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 18px;

    padding:
        13px
        16px;

    border-radius: 8px;

    box-sizing: border-box;

    font-size: 14px;

    font-weight: 600;

    line-height: 1.5;
}


.email-message svg {
    width: 21px;

    height: 21px;

    flex: 0 0 21px;
}


.email-message-success {
    color: #58761C;

    background-color: rgba(
        88,
        118,
        28,
        0.10
    );

    border:
        1px solid
        rgba(
            88,
            118,
            28,
            0.30
        );
}


.email-message-error {
    color: #54100F;

    background-color: rgba(
        84,
        16,
        15,
        0.08
    );

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.25
        );
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .new-instructor-page {
        padding-bottom: 30px;
    }


    .back-area {
        margin-bottom: 10px;
    }


    .back-button {
        width: 44px;
        height: 44px;

        flex-basis: 44px;
    }


    .back-button svg {
        width: 38px;
        height: 38px;
    }


    .email-message {
        font-size: 13px;
    }

}

</style>