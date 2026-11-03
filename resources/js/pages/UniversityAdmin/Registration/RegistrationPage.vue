<script setup>

import {
    computed,
    ref,
} from 'vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';


/*
|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';


/*
|--------------------------------------------------------------------------
| Real Icons
|--------------------------------------------------------------------------
*/

import {
    ArrowLeft,
    BadgeCheck,
    CircleAlert,
    CreditCard,
    IdCard,
    LoaderCircle,
    QrCode,
    ShieldCheck,
} from 'lucide-vue-next';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    student: {
        type: Object,
        required: true,
    },

    generatedStudentId: {
        type: String,
        default: '',
    },

    generateStudentIdUrl: {
        type: String,
        default: '',
    },

    generateCredentialsUrl: {
        type: String,
        default: '',
    },

    backUrl: {
        type: String,

        default:
            '/university-admin/student-registration',
    },

});


/*
|--------------------------------------------------------------------------
| Processing State
|--------------------------------------------------------------------------
*/

const generatingStudentId =
    ref(false);


const generatingCredentials =
    ref(false);


/*
|--------------------------------------------------------------------------
| Local Student ID
|--------------------------------------------------------------------------
*/

const localStudentId =
    ref(
        props.generatedStudentId
        ||
        props.student
            ?.student_id_number
        ||
        props.student
            ?.nstp_id
        ||
        ''
    );


/*
|--------------------------------------------------------------------------
| Student Full Name
|--------------------------------------------------------------------------
*/

const studentFullName =
    computed(() => {

        /*
        |--------------------------------------------------------------------------
        | Backend Accessor
        |--------------------------------------------------------------------------
        */

        if (
            props.student
                ?.full_name
        ) {

            return props.student
                .full_name;

        }


        /*
        |--------------------------------------------------------------------------
        | Compose Name
        |--------------------------------------------------------------------------
        */

        const givenNames =
            [
                props.student
                    ?.first_name,

                props.student
                    ?.middle_name,
            ]
                .filter(
                    Boolean
                )
                .join(
                    ' '
                )
                .trim();


        if (
            props.student
                ?.surname
            &&
            givenNames
        ) {

            return (
                `${props.student.surname}, ${givenNames}`
            );

        }


        return (
            props.student
                ?.name
            ||
            props.student
                ?.email
            ||
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| Component
|--------------------------------------------------------------------------
*/

const componentLabel =
    computed(() => {

        const component =
            String(
                props.student
                    ?.component
                ??
                ''
            )
                .trim()
                .toUpperCase();


        const labels = {

            CWTS:
                'CWTS - Civic Welfare Training Service',

            LTS:
                'LTS - Literacy Training Service',

            ROTC:
                "ROTC - Reserve Officers' Training Corps",

        };


        return (
            labels[
                component
            ]
            ||
            component
            ||
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| Term
|--------------------------------------------------------------------------
*/

const termLabel =
    computed(() => {

        const term =
            String(
                props.student
                    ?.term
                ??
                ''
            )
                .trim()
                .toUpperCase();


        if (
            term ===
            '1ST SEM'
        ) {

            return '1st Semester';

        }


        if (
            term ===
            '2ND SEM'
        ) {

            return '2nd Semester';

        }


        if (
            term ===
            'SUMMER'
        ) {

            return 'Summer';

        }


        return (
            props.student
                ?.term
            ||
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| Year And Section
|--------------------------------------------------------------------------
*/

const yearSection =
    computed(() => {

        const year =
            props.student
                ?.year_level
            ??
            '';


        const section =
            props.student
                ?.section
            ??
            '';


        if (
            year
            &&
            section
        ) {

            return (
                `${year} / ${section}`
            );

        }


        return (
            year
            ||
            section
            ||
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| Gender
|--------------------------------------------------------------------------
*/

const genderLabel =
    computed(() => {

        const gender =
            String(
                props.student
                    ?.gender
                ??
                ''
            )
                .trim()
                .toLowerCase();


        if (
            !gender
        ) {

            return '-';

        }


        return (
            gender
                .charAt(
                    0
                )
                .toUpperCase()
            +
            gender.slice(
                1
            )
        );

    });


/*
|--------------------------------------------------------------------------
| Birthdate
|--------------------------------------------------------------------------
|
| Avoid converting a date-only database value through UTC because that can
| shift the displayed date.
|
*/

const birthdateLabel =
    computed(() => {

        const value =
            props.student
                ?.birth_date;


        if (
            !value
        ) {

            return '-';

        }


        const raw =
            String(
                value
            )
                .slice(
                    0,
                    10
                );


        const parts =
            raw.split(
                '-'
            );


        if (
            parts.length ===
                3
            &&
            parts[
                0
            ]?.length ===
                4
        ) {

            return (
                `${parts[1]} / ${parts[2]} / ${parts[0]}`
            );

        }


        return String(
            value
        );

    });


/*
|--------------------------------------------------------------------------
| Full Address
|--------------------------------------------------------------------------
*/

const fullAddress =
    computed(() => {

        if (
            props.student
                ?.full_address
        ) {

            return props.student
                .full_address;

        }


        return (
            [
                props.student
                    ?.city_address,

                props.student
                    ?.municipality,

                props.student
                    ?.province,
            ]
                .filter(
                    Boolean
                )
                .join(
                    ', '
                )
            ||
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| Guardian Address
|--------------------------------------------------------------------------
*/

const guardianAddress =
    computed(() => {

        return (
            props.student
                ?.guardian_address
            ||
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| Has Student ID
|--------------------------------------------------------------------------
*/

const hasStudentId =
    computed(() => {

        return (
            String(
                localStudentId.value
                ??
                ''
            )
                .trim()
                .length
            >
            0
        );

    });


/*
|--------------------------------------------------------------------------
| Generate Student ID URL
|--------------------------------------------------------------------------
*/

const resolvedGenerateStudentIdUrl =
    computed(() => {

        if (
            props.generateStudentIdUrl
        ) {

            return props.generateStudentIdUrl;

        }


        if (
            !props.student
                ?.id
        ) {

            return '';

        }


        return (
            '/university-admin/student-registration/'
            +
            props.student.id
            +
            '/generate-student-id'
        );

    });


/*
|--------------------------------------------------------------------------
| Generate Credentials URL
|--------------------------------------------------------------------------
*/

const resolvedGenerateCredentialsUrl =
    computed(() => {

        if (
            props.generateCredentialsUrl
        ) {

            return props.generateCredentialsUrl;

        }


        if (
            !props.student
                ?.id
        ) {

            return '';

        }


        return (
            '/university-admin/student-registration/'
            +
            props.student.id
            +
            '/generate-credentials'
        );

    });


/*
|--------------------------------------------------------------------------
| Generate Student ID
|--------------------------------------------------------------------------
*/

const generateStudentId =
    () => {

        if (
            generatingStudentId.value
            ||
            !resolvedGenerateStudentIdUrl.value
        ) {

            return;

        }


        generatingStudentId.value =
            true;


        router.post(
            resolvedGenerateStudentIdUrl.value,

            {},

            {
                preserveScroll:
                    true,


                /*
                |--------------------------------------------------------------------------
                | Update ID From Fresh Inertia Props
                |--------------------------------------------------------------------------
                */

                onSuccess: (
                    page
                ) => {

                    const generatedId =
                        page?.props
                            ?.generatedStudentId
                        ??
                        page?.props
                            ?.student
                            ?.student_id_number
                        ??
                        page?.props
                            ?.student
                            ?.nstp_id
                        ??
                        '';


                    if (
                        generatedId
                    ) {

                        localStudentId.value =
                            generatedId;

                    }

                },


                onFinish: () => {

                    generatingStudentId.value =
                        false;

                },
            }
        );

    };


/*
|--------------------------------------------------------------------------
| Generate QR + ID Card
|--------------------------------------------------------------------------
*/

const generateCredentials =
    () => {

        if (
            !hasStudentId.value
        ) {

            return;

        }


        if (
            generatingCredentials.value
            ||
            !resolvedGenerateCredentialsUrl.value
        ) {

            return;

        }


        generatingCredentials.value =
            true;


        router.post(
            resolvedGenerateCredentialsUrl.value,

            {},

            {
                preserveScroll:
                    true,

                onFinish: () => {

                    generatingCredentials.value =
                        false;

                },
            }
        );

    };


/*
|--------------------------------------------------------------------------
| Back
|--------------------------------------------------------------------------
*/

const goBack =
    () => {

        router.visit(
            props.backUrl
        );

    };

</script>


<template>

    <Head
        title="Student Information Review"
    />


    <UniversityAdminDashLayout
        current-route="student-registration"
    >

        <main class="registration-page">


            <!-- ====================================================
                 REVIEW CARD
            ===================================================== -->

            <section class="review-card">


                <!-- =================================================
                     MAROON TOP BAR
                ================================================== -->

                <div class="review-top-bar"></div>


                <!-- =================================================
                     TITLE
                ================================================== -->

                <header class="review-header">

                    <h1>
                        STUDENT INFORMATION
                        <br />
                        REVIEW
                    </h1>

                </header>


                <!-- =================================================
                     STUDENT INFORMATION
                ================================================== -->

                <section class="student-information">


                    <!-- SUBJECT -->

                    <div class="information-row">

                        <span class="information-label">
                            Subject
                        </span>

                        <span class="information-value">
                            {{
                                student.subject
                                ??
                                '-'
                            }}
                        </span>

                    </div>


                    <!-- COMPONENT -->

                    <div class="information-row">

                        <span class="information-label">
                            Components
                        </span>

                        <span class="information-value">
                            {{ componentLabel }}
                        </span>

                    </div>


                    <!-- TERM -->

                    <div class="information-row">

                        <span class="information-label">
                            Term
                        </span>

                        <span class="information-value">
                            {{ termLabel }}
                        </span>

                    </div>


                    <!-- NAME -->

                    <div class="information-row">

                        <span class="information-label">
                            Name
                        </span>

                        <span class="information-value">
                            {{ studentFullName }}
                        </span>

                    </div>


                    <!-- COURSE -->

                    <div class="information-row">

                        <span class="information-label">
                            Course
                        </span>

                        <span class="information-value">
                            {{
                                student.course
                                ??
                                '-'
                            }}
                        </span>

                    </div>


                    <!-- YEAR & SECTION -->

                    <div class="information-row">

                        <span class="information-label">
                            Yr &amp; Section
                        </span>

                        <span class="information-value">
                            {{ yearSection }}
                        </span>

                    </div>


                    <!-- GENDER -->

                    <div class="information-row">

                        <span class="information-label">
                            Gender
                        </span>

                        <span class="information-value">
                            {{ genderLabel }}
                        </span>

                    </div>


                    <!-- BIRTHDATE -->

                    <div class="information-row">

                        <span class="information-label">
                            Birthdate
                        </span>

                        <span class="information-value">
                            {{ birthdateLabel }}
                        </span>

                    </div>


                    <!-- ADDRESS -->

                    <div class="information-row">

                        <span class="information-label">
                            Address
                        </span>

                        <span class="information-value">
                            {{ fullAddress }}
                        </span>

                    </div>


                    <!-- EMAIL -->

                    <div class="information-row">

                        <span class="information-label">
                            Email
                        </span>

                        <span class="information-value">
                            {{
                                student.email
                                ??
                                '-'
                            }}
                        </span>

                    </div>


                    <!-- CONTACT -->

                    <div class="information-row">

                        <span class="information-label">
                            Contact
                        </span>

                        <span class="information-value">
                            {{
                                student.contact_number
                                ??
                                '-'
                            }}
                        </span>

                    </div>


                    <!-- GUARDIAN -->

                    <div class="information-row">

                        <span class="information-label guardian-label">
                            Parents / Guardian
                        </span>

                        <span class="information-value">
                            {{
                                student.guardian_name
                                ??
                                '-'
                            }}
                        </span>

                    </div>


                    <!-- GUARDIAN ADDRESS -->

                    <div class="information-row">

                        <span class="information-label">
                            Address
                        </span>

                        <span class="information-value">
                            {{ guardianAddress }}
                        </span>

                    </div>


                    <!-- GUARDIAN CONTACT -->

                    <div class="information-row">

                        <span class="information-label">
                            Contact
                        </span>

                        <span class="information-value">
                            {{
                                student.guardian_contact_number
                                ??
                                '-'
                            }}
                        </span>

                    </div>

                </section>


                <!-- =================================================
                     STUDENT ID GENERATION
                ================================================== -->

                <section class="student-id-section">


                    <!-- =============================================
                         ID GENERATION BOX
                    ============================================== -->

                    <div class="generate-id-box">

                        <div class="generate-id-information">

                            <strong>
                                Generate Student ID No.
                            </strong>


                            <span
                                v-if="!hasStudentId"
                                class="student-id-status not-generated"
                            >
                                Not yet generated
                            </span>


                            <span
                                v-else
                                class="student-id-status generated"
                            >
                                {{ localStudentId }}
                            </span>

                        </div>


                        <button
                            type="button"
                            class="generate-button"
                            :disabled="generatingStudentId"
                            @click="generateStudentId"
                        >

                            <LoaderCircle
                                v-if="generatingStudentId"
                                :size="17"
                                class="spin-icon"
                            />


                            <IdCard
                                v-else
                                :size="17"
                                :stroke-width="2"
                            />


                            <span>

                                {{
                                    generatingStudentId
                                        ? 'Generating...'
                                        : (
                                            hasStudentId
                                                ? 'Generated'
                                                : 'Generate'
                                        )
                                }}

                            </span>

                        </button>

                    </div>


                    <!-- =============================================
                         NOT GENERATED MESSAGE
                    ============================================== -->

                    <div
                        v-if="!hasStudentId"
                        class="student-id-message pending-message"
                    >

                        <CircleAlert
                            :size="16"
                            :stroke-width="2"
                        />

                        <span>
                            Please generate the Student ID No. first.
                        </span>

                    </div>


                    <!-- =============================================
                         SUCCESS MESSAGE
                    ============================================== -->

                    <div
                        v-else
                        class="student-id-message success-message"
                    >

                        <BadgeCheck
                            :size="17"
                            :stroke-width="2"
                        />

                        <span>
                            Student ID No. generated successfully.
                        </span>

                    </div>


                    <!-- =============================================
                         GENERATE QR + ID CARD
                    ============================================== -->

                    <button
                        type="button"
                        class="credentials-button"
                        :disabled="
                            !hasStudentId
                            ||
                            generatingCredentials
                        "
                        @click="generateCredentials"
                    >

                        <LoaderCircle
                            v-if="generatingCredentials"
                            :size="19"
                            class="spin-icon"
                        />


                        <template v-else>

                            <QrCode
                                :size="19"
                                :stroke-width="2"
                            />

                            <CreditCard
                                :size="19"
                                :stroke-width="2"
                            />

                        </template>


                        <span>

                            {{
                                generatingCredentials
                                    ? 'Generating...'
                                    : 'Generate QR Code and ID Card'
                            }}

                        </span>

                    </button>


                    <!-- =============================================
                         SECURITY NOTE
                    ============================================== -->

                    <div class="credential-note">

                        <ShieldCheck
                            :size="16"
                            :stroke-width="2"
                        />

                        <span>
                            Student credentials can only be generated
                            after an NSTP Student ID number has been assigned.
                        </span>

                    </div>

                </section>

            </section>


            <!-- ====================================================
                 BACK BUTTON
            ===================================================== -->

            <div class="bottom-actions">

                <button
                    type="button"
                    class="back-button"
                    @click="goBack"
                >

                    <ArrowLeft
                        :size="21"
                        :stroke-width="2.5"
                    />

                    <span>
                        BACK
                    </span>

                </button>

            </div>

        </main>

    </UniversityAdminDashLayout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| NSTP HUB COLOR PALETTE
|--------------------------------------------------------------------------
|
| Cream       #EFEBE2
| Maroon      #54100F
| Green       #58761C
| Yellow      #FFBD36
| Orange      #D99202
| Dark Teal   #233E47
| Near Black  #000D12
| White       #FFFFFF
| Gray        #BEBEBE
| Dark        #0D171B
|
*/


/* ==========================================================================
   PAGE
   ========================================================================== */

.registration-page {
    width: 100%;
    min-width: 0;
    min-height: 100%;

    box-sizing: border-box;

    padding:
        32px
        34px
        55px;

    background:
        #EFEBE2;

    color:
        #233E47;
}


/* ==========================================================================
   REVIEW CARD
   ========================================================================== */

.review-card {
    position: relative;

    width: 100%;
    max-width: 760px;

    margin:
        0
        auto;

    overflow: hidden;

    box-sizing: border-box;

    padding:
        0
        55px
        58px;

    border:
        1px solid
        rgba(
            35,
            62,
            71,
            0.60
        );

    border-radius:
        18px;

    background:
        #EFEBE2;
}


/* ==========================================================================
   TOP MAROON BAR
   ========================================================================== */

.review-top-bar {
    position: absolute;

    top: 0;
    left: 0;
    right: 0;

    height: 27px;

    background:
        #54100F;
}


/* ==========================================================================
   HEADER
   ========================================================================== */

.review-header {
    display: flex;

    align-items: center;
    justify-content: center;

    box-sizing: border-box;

    padding:
        70px
        20px
        50px;
}


.review-header h1 {
    margin: 0;

    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 31px;

    font-weight: 900;

    line-height: 1.2;

    text-align: center;
}


/* ==========================================================================
   STUDENT INFORMATION
   ========================================================================== */

.student-information {
    width: 100%;
    max-width: 610px;

    margin:
        0
        auto;
}


/* ==========================================================================
   INFORMATION ROW
   ========================================================================== */

.information-row {
    width: 100%;

    display: grid;

    grid-template-columns:
        125px
        minmax(
            0,
            1fr
        );

    align-items: start;

    gap: 18px;

    box-sizing: border-box;

    padding:
        5px
        0;
}


.information-label {
    padding-top: 2px;

    color:
        #0D171B;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 12px;

    font-weight: 400;

    line-height: 1.45;
}


.information-value {
    min-width: 0;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 16px;

    font-weight: 400;

    line-height: 1.45;

    overflow-wrap:
        anywhere;
}


.guardian-label {
    white-space:
        nowrap;
}


/* ==========================================================================
   STUDENT ID AREA
   ========================================================================== */

.student-id-section {
    width: 100%;
    max-width: 590px;

    margin:
        78px
        auto
        0;
}


/* ==========================================================================
   GENERATE ID BOX
   ========================================================================== */

.generate-id-box {
    width: 100%;

    min-height: 85px;

    display: grid;

    grid-template-columns:
        minmax(
            0,
            1fr
        )
        180px;

    align-items: center;

    gap: 24px;

    box-sizing: border-box;

    padding:
        13px
        22px;

    border:
        2px solid
        #54100F;

    background:
        transparent;
}


/* ==========================================================================
   ID INFORMATION
   ========================================================================== */

.generate-id-information {
    min-width: 0;

    display: flex;

    flex-direction: column;

    align-items: flex-start;
}


.generate-id-information strong {
    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 16px;

    font-weight: 800;
}


.student-id-status {
    display: block;

    margin-top: 5px;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 13px;
}


.student-id-status.not-generated {
    color:
        #54100F;
}


.student-id-status.generated {
    color:
        #58761C;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 14px;

    font-weight: 800;

    letter-spacing:
        0.4px;
}


/* ==========================================================================
   GENERATE BUTTON
   ========================================================================== */

.generate-button {
    min-height: 40px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 7px;

    box-sizing: border-box;

    padding:
        8px
        18px;

    border: 0;

    border-radius:
        7px;

    background:
        #58761C;

    color:
        #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 14px;

    cursor: pointer;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.15s
        ease;
}


.generate-button:hover:not(:disabled) {
    background:
        #496516;

    transform:
        translateY(
            -1px
        );
}


.generate-button:disabled {
    opacity:
        0.65;

    cursor:
        not-allowed;
}


/* ==========================================================================
   STUDENT ID MESSAGE
   ========================================================================== */

.student-id-message {
    min-height: 53px;

    display: flex;

    align-items: center;
    justify-content: center;

    gap: 7px;

    box-sizing: border-box;

    padding-top: 15px;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 12px;

    text-align: center;
}


.pending-message {
    color:
        #54100F;
}


.success-message {
    color:
        #58761C;
}


/* ==========================================================================
   CREDENTIAL BUTTON
   ========================================================================== */

.credentials-button {
    width: fit-content;

    min-width: 360px;
    min-height: 49px;

    margin:
        10px
        auto
        0;

    display: flex;

    align-items: center;
    justify-content: center;

    gap: 7px;

    box-sizing: border-box;

    padding:
        10px
        24px;

    border: 0;

    border-radius:
        8px;

    background:
        #58761C;

    color:
        #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 15px;

    cursor: pointer;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.15s
        ease;
}


.credentials-button:hover:not(:disabled) {
    background:
        #496516;

    transform:
        translateY(
            -1px
        );
}


.credentials-button:disabled {
    background:
        rgba(
            88,
            118,
            28,
            0.38
        );

    color:
        rgba(
            255,
            255,
            255,
            0.72
        );

    cursor:
        not-allowed;

    transform:
        none;
}


/* ==========================================================================
   CREDENTIAL NOTE
   ========================================================================== */

.credential-note {
    width: 100%;
    max-width: 500px;

    margin:
        25px
        auto
        0;

    display: flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 10px;

    line-height: 1.4;

    text-align: center;
}


.credential-note svg {
    flex-shrink: 0;

    color:
        #58761C;
}


/* ==========================================================================
   BOTTOM ACTIONS
   ========================================================================== */

.bottom-actions {
    width: 100%;
    max-width: 760px;

    margin:
        27px
        auto
        0;

    display: flex;

    justify-content: flex-start;
}


/* ==========================================================================
   BACK BUTTON
   ========================================================================== */

.back-button {
    min-width: 170px;
    min-height: 55px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    box-sizing: border-box;

    padding:
        10px
        24px;

    border: 0;

    border-radius:
        7px;

    background:
        #58761C;

    color:
        #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 17px;

    font-weight: 900;

    cursor: pointer;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.15s
        ease;
}


.back-button:hover {
    background:
        #496516;

    transform:
        translateY(
            -1px
        );
}


/* ==========================================================================
   SPINNER
   ========================================================================== */

.spin-icon {
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


/* ==========================================================================
   TABLET
   ========================================================================== */

@media (
    max-width: 900px
) {

    .registration-page {
        padding:
            27px
            20px
            45px;
    }


    .review-card {
        max-width:
            700px;

        padding:
            0
            42px
            50px;
    }


    .review-header {
        padding:
            62px
            10px
            42px;
    }


    .review-header h1 {
        font-size:
            28px;
    }


    .information-row {
        grid-template-columns:
            115px
            minmax(
                0,
                1fr
            );

        gap:
            15px;
    }


    .information-value {
        font-size:
            15px;
    }


    .student-id-section {
        margin-top:
            65px;
    }

}


/* ==========================================================================
   MOBILE
   ========================================================================== */

@media (
    max-width: 600px
) {

    .registration-page {
        padding:
            18px
            12px
            35px;
    }


    .review-card {
        width:
            100%;

        padding:
            0
            25px
            38px;

        border-radius:
            15px;
    }


    .review-top-bar {
        height:
            21px;
    }


    .review-header {
        padding:
            52px
            5px
            35px;
    }


    .review-header h1 {
        font-size:
            23px;
    }


    .information-row {
        grid-template-columns:
            92px
            minmax(
                0,
                1fr
            );

        gap:
            9px;

        padding:
            4px
            0;
    }


    .information-label {
        font-size:
            10px;
    }


    .information-value {
        font-size:
            12px;
    }


    .guardian-label {
        white-space:
            normal;
    }


    .student-id-section {
        margin-top:
            55px;
    }


    .generate-id-box {
        grid-template-columns:
            minmax(
                0,
                1fr
            )
            125px;

        min-height:
            72px;

        gap:
            9px;

        padding:
            11px
            13px;
    }


    .generate-id-information strong {
        font-size:
            12px;
    }


    .student-id-status {
        font-size:
            10px;
    }


    .student-id-status.generated {
        font-size:
            10px;
    }


    .generate-button {
        min-height:
            33px;

        padding:
            6px
            10px;

        font-size:
            10px;
    }


    .student-id-message {
        min-height:
            42px;

        padding-top:
            12px;

        font-size:
            10px;
    }


    .credentials-button {
        min-width:
            255px;

        min-height:
            41px;

        padding:
            8px
            15px;

        font-size:
            11px;
    }


    .credential-note {
        max-width:
            310px;

        margin-top:
            20px;

        font-size:
            8px;
    }


    .bottom-actions {
        margin-top:
            21px;
    }


    .back-button {
        min-width:
            125px;

        min-height:
            46px;

        padding:
            8px
            15px;

        font-size:
            13px;
    }

}


/* ==========================================================================
   VERY SMALL MOBILE
   ========================================================================== */

@media (
    max-width: 430px
) {

    .registration-page {
        padding:
            14px
            8px
            28px;
    }


    .review-card {
        padding:
            0
            18px
            33px;
    }


    .review-header h1 {
        font-size:
            20px;
    }


    .information-row {
        grid-template-columns:
            78px
            minmax(
                0,
                1fr
            );

        gap:
            7px;
    }


    .information-label {
        font-size:
            9px;
    }


    .information-value {
        font-size:
            10.5px;
    }


    .generate-id-box {
        grid-template-columns:
            1fr;

        text-align:
            center;
    }


    .generate-id-information {
        align-items:
            center;
    }


    .generate-button {
        width:
            100%;
    }


    .credentials-button {
        width:
            100%;

        min-width:
            0;
    }

}

</style>