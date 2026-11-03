<script setup>

import {
    computed,
} from 'vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import {
    CheckCircle2,
    CreditCard,
    IdCard,
    QrCode,
    ShieldCheck,
    UserRound,
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

    university: {
        type: Object,
        default: () => ({}),
    },

    studentIdNumber: {
        type: String,
        default: '',
    },

    qrCodeUrl: {
        type: String,
        default: '',
    },

    profilePhotoUrl: {
        type: String,
        default: '',
    },

    universityLogoUrl: {
        type: String,
        default: '',
    },

    nstpLogoUrl: {
        type: String,
        default: '',
    },

    watermarkUrl: {
        type: String,
        default: '',
    },

    doneUrl: {
        type: String,
        default:
            '/university-admin/student-registration',
    },

});


/*
|--------------------------------------------------------------------------
| Student ID
|--------------------------------------------------------------------------
*/

const resolvedStudentId =
    computed(() => {

        return (
            props.studentIdNumber
            ||
            props.student
                ?.student_id_number
            ||
            props.student
                ?.nstp_id
            ||
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| University
|--------------------------------------------------------------------------
*/

const universityName =
    computed(() => {

        return (
            props.university
                ?.name
            ||
            props.student
                ?.university
                ?.name
            ||
            'UNIVERSITY'
        );

    });


/*
|--------------------------------------------------------------------------
| University Name Lines
|--------------------------------------------------------------------------
*/

const universityNameLines =
    computed(() => {

        const name =
            String(
                universityName.value
            )
                .trim()
                .toUpperCase();


        /*
        |--------------------------------------------------------------------------
        | SNSU
        |--------------------------------------------------------------------------
        */

        if (
            name ===
            'SURIGAO DEL NORTE STATE UNIVERSITY'
        ) {
            return [
                'SURIGAO DEL NORTE',
                'STATE UNIVERSITY',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Generic University Name
        |--------------------------------------------------------------------------
        */

        const words =
            name.split(
                /\s+/
            );


        if (
            words.length <=
            3
        ) {
            return [
                name,
            ];
        }


        const midpoint =
            Math.ceil(
                words.length
                /
                2
            );


        return [

            words
                .slice(
                    0,
                    midpoint
                )
                .join(
                    ' '
                ),

            words
                .slice(
                    midpoint
                )
                .join(
                    ' '
                ),

        ];

    });


/*
|--------------------------------------------------------------------------
| University Logo
|--------------------------------------------------------------------------
*/

const resolvedUniversityLogo =
    computed(() => {

        return (
            props.universityLogoUrl
            ||
            props.university
                ?.logo_url
            ||
            props.student
                ?.university
                ?.logo_url
            ||
            ''
        );

    });


/*
|--------------------------------------------------------------------------
| NSTP HUB Logo / Watermark
|--------------------------------------------------------------------------
|
| University logo and NSTP HUB logo are intentionally separate.
|
| Green header:
|     University logo
|
| Card body watermark:
|     NSTP HUB logo
|
*/

const resolvedNstpHubLogo =
    computed(() => {

        return (
            props.nstpLogoUrl
            ||
            props.watermarkUrl
            ||
            ''
        );

    });


/*
|--------------------------------------------------------------------------
| Full Name
|--------------------------------------------------------------------------
*/

const studentFullName =
    computed(() => {

        if (
            props.student
                ?.full_name
        ) {
            return props.student
                .full_name;
        }


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
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| Card Name
|--------------------------------------------------------------------------
*/

const studentCardName =
    computed(() => {

        const firstName =
            String(
                props.student
                    ?.first_name
                ??
                ''
            )
                .trim()
                .toUpperCase();


        const middleName =
            String(
                props.student
                    ?.middle_name
                ??
                ''
            )
                .trim();


        const surname =
            String(
                props.student
                    ?.surname
                ??
                ''
            )
                .trim()
                .toUpperCase();


        const middleInitial =
            middleName
                ? `${middleName
                    .charAt(
                        0
                    )
                    .toUpperCase()}.`
                : '';


        const composed =
            [
                firstName,
                middleInitial,
                surname,
            ]
                .filter(
                    Boolean
                )
                .join(
                    ' '
                )
                .trim();


        return (
            composed
            ||
            String(
                studentFullName.value
            )
                .toUpperCase()
        );

    });


/*
|--------------------------------------------------------------------------
| Component
|--------------------------------------------------------------------------
*/

const componentCode =
    computed(() => {

        return String(
            props.student
                ?.component
            ??
            ''
        )
            .trim()
            .toUpperCase();

    });


const componentFullLabel =
    computed(() => {

        const labels = {

            CWTS:
                'Civic Welfare Training Service',

            LTS:
                'Literacy Training Service',

            ROTC:
                "Reserve Officers' Training Corps",

        };


        return (
            labels[
                componentCode.value
            ]
            ||
            '-'
        );

    });


/*
|--------------------------------------------------------------------------
| Course
|--------------------------------------------------------------------------
*/

const courseLabel =
    computed(() => {

        return String(
            props.student
                ?.course
            ??
            ''
        )
            .trim();

    });


const courseAcronym =
    computed(() => {

        const provided =
            String(
                props.student
                    ?.course_acronym
                ??
                props.student
                    ?.course_code
                ??
                ''
            )
                .trim()
                .toUpperCase();


        const course =
            courseLabel.value
                .toUpperCase();


        /*
        |--------------------------------------------------------------------------
        | Course Is Already An Acronym
        |--------------------------------------------------------------------------
        */

        if (
            /^[A-Z]{2,12}$/.test(
                course
            )
        ) {
            return course;
        }


        /*
        |--------------------------------------------------------------------------
        | Reject Incorrect Single-Letter Acronyms
        |--------------------------------------------------------------------------
        */

        if (
            provided.length <=
            1
        ) {
            return '';
        }


        return provided;

    });


const courseDisplay =
    computed(() => {

        const acronym =
            courseAcronym.value;


        const course =
            courseLabel.value;


        if (
            !course
        ) {
            return '-';
        }


        if (
            !acronym
            ||
            acronym ===
                course.toUpperCase()
        ) {
            return course;
        }


        return (
            `${acronym} - ${course}`
        );

    });


/*
|--------------------------------------------------------------------------
| Student Photo
|--------------------------------------------------------------------------
*/

const resolvedProfilePhoto =
    computed(() => {

        return (
            props.profilePhotoUrl
            ||
            props.student
                ?.profile_photo_url
            ||
            ''
        );

    });


/*
|--------------------------------------------------------------------------
| QR Code
|--------------------------------------------------------------------------
*/

const resolvedQrCode =
    computed(() => {

        return (
            props.qrCodeUrl
            ||
            props.student
                ?.qr_code_url
            ||
            ''
        );

    });


/*
|--------------------------------------------------------------------------
| Credentials Ready
|--------------------------------------------------------------------------
*/

const credentialsReady =
    computed(() => {

        return (
            resolvedStudentId.value !==
                '-'
            &&
            Boolean(
                resolvedQrCode.value
            )
        );

    });


/*
|--------------------------------------------------------------------------
| Done
|--------------------------------------------------------------------------
*/

const handleDone =
    () => {

        router.visit(
            props.doneUrl
        );

    };

</script>


<template>

    <Head
        title="Student ID Management"
    />


    <UniversityAdminDashLayout
        current-route="student-registration"
    >

        <main class="id-management-page">


            <!-- ====================================================
                 PAGE TITLE
            ===================================================== -->

            <header class="page-header">

                <div class="title-icon">

                    <IdCard
                        :size="30"
                        :stroke-width="1.9"
                    />

                </div>


                <h1>
                    Student ID Management
                </h1>

            </header>


            <!-- ====================================================
                 MANAGEMENT CONTENT
            ===================================================== -->

            <section class="management-content">


                <!-- =================================================
                     DIGITAL STUDENT ID CARD
                ================================================== -->

                <div class="id-card-column">

                    <article class="digital-id-card">


                        <!-- =========================================
                             UNIVERSITY HEADER
                        ========================================== -->

                        <header class="student-card-header">

                            <div class="card-university-logo">

                                <img
                                    v-if="resolvedUniversityLogo"
                                    :src="resolvedUniversityLogo"
                                    :alt="`${universityName} logo`"
                                />


                                <ShieldCheck
                                    v-else
                                    :size="48"
                                    :stroke-width="1.4"
                                />

                            </div>


                            <div class="card-university-name">

                                <span
                                    v-for="line in universityNameLines"
                                    :key="line"
                                >
                                    {{ line }}
                                </span>

                            </div>

                        </header>


                        <!-- =========================================
                             CARD BODY
                        ========================================== -->

                        <section class="student-card-body">


                            <!-- =====================================
                                 NSTP HUB WATERMARK
                            ====================================== -->

                            <img
                                v-if="resolvedNstpHubLogo"
                                :src="resolvedNstpHubLogo"
                                alt=""
                                aria-hidden="true"
                                class="nstp-watermark"
                            />


                            <!-- =====================================
                                 PROGRAM TITLE
                            ====================================== -->

                            <div class="card-program-title">

                                NATIONAL SERVICE TRAINING PROGRAM

                            </div>


                            <div class="card-subtitle">

                                Digital Student ID Card

                            </div>


                            <!-- =====================================
                                 PHOTO + COMPONENT
                            ====================================== -->

                            <div class="student-card-main-row">


                                <!-- PHOTO -->

                                <div class="student-photo-box">

                                    <img
                                        v-if="resolvedProfilePhoto"
                                        :src="resolvedProfilePhoto"
                                        :alt="studentFullName"
                                        class="student-photo"
                                    />


                                    <div
                                        v-else
                                        class="student-photo-placeholder"
                                    >

                                        <UserRound
                                            :size="65"
                                            :stroke-width="1.2"
                                        />

                                    </div>

                                </div>


                                <!-- COMPONENT -->

                                <div class="component-details">

                                    <span class="component-heading">

                                        NSTP COMPONENT:

                                    </span>


                                    <div class="component-value">

                                        <div class="component-first-line">

                                            <strong>
                                                {{ componentCode }}
                                            </strong>


                                            <span
                                                v-if="componentCode"
                                            >
                                                -
                                            </span>

                                        </div>


                                        <span class="component-full-name">

                                            {{ componentFullLabel }}

                                        </span>

                                    </div>

                                </div>

                            </div>


                            <!-- =====================================
                                 STUDENT ID
                            ====================================== -->

                            <div class="card-id-number">

                                <span>
                                    ID NO.
                                </span>


                                <strong>
                                    {{ resolvedStudentId }}
                                </strong>

                            </div>


                            <!-- =====================================
                                 STUDENT NAME
                            ====================================== -->

                            <div class="card-student-name">

                                {{ studentCardName }}

                            </div>


                            <!-- =====================================
                                 COURSE
                            ====================================== -->

                            <div class="card-course">

                                {{ courseDisplay }}

                            </div>


                            <!-- =====================================
                                 QR CODE
                            ====================================== -->

                            <div class="card-qr-area">

                                <img
                                    v-if="resolvedQrCode"
                                    :src="resolvedQrCode"
                                    alt="NSTP Student QR Code"
                                    class="card-qr-image"
                                />


                                <div
                                    v-else
                                    class="card-qr-placeholder"
                                >

                                    <QrCode
                                        :size="85"
                                        :stroke-width="1.3"
                                    />


                                    <span>
                                        QR unavailable
                                    </span>

                                </div>

                            </div>

                        </section>

                    </article>


                    <div class="card-preview-label">

                        <CreditCard
                            :size="16"
                        />


                        <span>
                            Digital Student ID Card
                        </span>

                    </div>

                </div>


                <!-- =================================================
                     RIGHT SIDE
                ================================================== -->

                <aside class="qr-information">


                    <!-- =============================================
                         ID NUMBER
                    ============================================== -->

                    <section class="student-id-display">

                        <span class="side-label">
                            ID NO.
                        </span>


                        <strong>
                            {{ resolvedStudentId }}
                        </strong>

                    </section>


                    <!-- =============================================
                         QR CODE
                    ============================================== -->

                    <section class="qr-display">

                        <span class="side-label">
                            QR CODE
                        </span>


                        <div class="large-qr-container">

                            <img
                                v-if="resolvedQrCode"
                                :src="resolvedQrCode"
                                alt="Official NSTP Student QR Code"
                                class="large-qr-image"
                            />


                            <div
                                v-else
                                class="large-qr-placeholder"
                            >

                                <QrCode
                                    :size="125"
                                    :stroke-width="1.2"
                                />


                                <span>
                                    QR code has not been generated.
                                </span>

                            </div>

                        </div>

                    </section>


                    <!-- =============================================
                         DESCRIPTION
                    ============================================== -->

                    <div class="qr-description">

                        <p>

                            This section displays the student's official
                            NSTP digital identification card and generated
                            QR code for attendance monitoring.

                        </p>

                    </div>


                    <!-- =============================================
                         STATUS
                    ============================================== -->

                    <div
                        class="credential-status"
                        :class="{
                            ready:
                                credentialsReady,

                            incomplete:
                                !credentialsReady,
                        }"
                    >

                        <CheckCircle2
                            v-if="credentialsReady"
                            :size="18"
                        />


                        <QrCode
                            v-else
                            :size="18"
                        />


                        <span>

                            {{
                                credentialsReady
                                    ? 'Student credentials generated successfully.'
                                    : 'Student credentials are incomplete.'
                            }}

                        </span>

                    </div>


                    <!-- =============================================
                         DONE
                    ============================================== -->

                    <button
                        type="button"
                        class="done-button"
                        @click="handleDone"
                    >

                        <CheckCircle2
                            :size="22"
                            :stroke-width="2.2"
                        />


                        <span>
                            DONE
                        </span>

                    </button>

                </aside>

            </section>

        </main>

    </UniversityAdminDashLayout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| NSTP HUB COLOR PALETTE
|--------------------------------------------------------------------------
|
| Cream      #EFEBE2
| Maroon     #54100F
| Green      #58761C
| Yellow     #FFBD36
| Orange     #D99202
| Dark Teal  #233E47
| Near Black #000D12
| White      #FFFFFF
| Gray       #BEBEBE
| Dark       #0D171B
|
*/


/* ==========================================================================
   PAGE
   ========================================================================== */

.id-management-page {
    width: 100%;
    min-width: 0;
    min-height: 100%;

    box-sizing: border-box;

    padding:
        36px
        45px
        55px;

    background:
        #EFEBE2;

    color:
        #233E47;
}


/* ==========================================================================
   PAGE HEADER
   ========================================================================== */

.page-header {
    width: 100%;

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom:
        35px;
}


.title-icon {
    display: none;

    color:
        #54100F;
}


.page-header h1 {
    margin: 0;

    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        37px;

    font-weight:
        800;

    line-height:
        1.1;
}


/* ==========================================================================
   MAIN CONTENT
   ========================================================================== */

.management-content {
    width: 100%;
    max-width: 1010px;

    margin:
        0
        auto;

    display: grid;

    grid-template-columns:
        390px
        380px;

    align-items:
        start;

    justify-content:
        center;

    gap:
        86px;
}


/* ==========================================================================
   CARD COLUMN
   ========================================================================== */

.id-card-column {
    width:
        390px;

    display: flex;

    flex-direction:
        column;

    align-items:
        center;
}


/* ==========================================================================
   DIGITAL ID CARD
   ========================================================================== */

.digital-id-card {
    position:
        relative;

    width:
        356px;

    height:
        620px;

    box-sizing:
        border-box;

    overflow:
        hidden;

    border:
        4px
        solid
        #54100F;

    border-radius:
        10px;

    background:
        #EFEBE2;

    box-shadow:
        4px
        6px
        7px
        rgba(
            0,
            13,
            18,
            0.24
        );
}


/* ==========================================================================
   UNIVERSITY HEADER
   ========================================================================== */

.student-card-header {
    width:
        100%;

    height:
        90px;

    display:
        grid;

    grid-template-columns:
        76px
        minmax(
            0,
            1fr
        );

    align-items:
        center;

    gap:
        12px;

    box-sizing:
        border-box;

    padding:
        11px
        17px;

    background:
        #58761C;

    color:
        #FFFFFF;
}


/* ==========================================================================
   UNIVERSITY LOGO
   ========================================================================== */

.card-university-logo {
    width:
        66px;

    height:
        66px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    color:
        #FFFFFF;
}


.card-university-logo img {
    width:
        100%;

    height:
        100%;

    display:
        block;

    object-fit:
        contain;
}


/* ==========================================================================
   UNIVERSITY NAME
   ========================================================================== */

.card-university-name {
    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    color:
        #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        17px;

    font-weight:
        800;

    line-height:
        1.22;

    letter-spacing:
        0.35px;
}


/* ==========================================================================
   CARD BODY
   ========================================================================== */

.student-card-body {
    position:
        relative;

    width:
        100%;

    height:
        calc(
            100%
            -
            90px
        );

    box-sizing:
        border-box;

    overflow:
        hidden;

    padding:
        20px
        31px
        21px;
}


/* ==========================================================================
   NSTP HUB WATERMARK
   ========================================================================== */

/*
|--------------------------------------------------------------------------
| FINAL WATERMARK POSITION
|--------------------------------------------------------------------------
|
| The watermark is now anchored from the BOTTOM of the card body.
|
| This means:
|
| - It no longer starts behind the upper half of the student photo.
| - The top of the NSTP HUB logo starts near the base of the student photo.
| - The main shield remains behind the ID, name and course area.
| - The QR remains clearly visible above it because the QR has z-index: 2
|   and its own white background.
|
*/

.nstp-watermark {
    position:
        absolute;

    z-index:
        0;

    width:
        245px;

    height:
        445px;

    /*
    |--------------------------------------------------------------------------
    | Anchor From Bottom
    |--------------------------------------------------------------------------
    */

    top:
        auto;

    bottom:
        72px;

    left:
        50%;

    transform:
        translateX(
            -50%
        );

    object-fit:
        contain;

    object-position:
        center;

    opacity:
        0.11;

    pointer-events:
        none;

    user-select:
        none;
}


/* ==========================================================================
   CONTENT ABOVE WATERMARK
   ========================================================================== */

.card-program-title,
.card-subtitle,
.student-card-main-row,
.card-id-number,
.card-student-name,
.card-course,
.card-qr-area {
    position:
        relative;

    z-index:
        2;
}


/* ==========================================================================
   PROGRAM TITLE
   ========================================================================== */

.card-program-title {
    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        12px;

    font-weight:
        700;

    line-height:
        1.25;

    text-align:
        center;
}


.card-subtitle {
    margin-top:
        7px;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        10px;

    text-align:
        center;
}


/* ==========================================================================
   PHOTO + COMPONENT
   ========================================================================== */

.student-card-main-row {
    width:
        100%;

    display:
        grid;

    grid-template-columns:
        132px
        minmax(
            0,
            1fr
        );

    align-items:
        center;

    gap:
        15px;

    margin-top:
        27px;
}


/* ==========================================================================
   PHOTO
   ========================================================================== */

.student-photo-box {
    position:
        relative;

    z-index:
        3;

    width:
        132px;

    height:
        132px;

    overflow:
        hidden;

    box-sizing:
        border-box;

    border:
        2px
        solid
        #54100F;

    background:
        #FFFFFF;
}


.student-photo {
    width:
        100%;

    height:
        100%;

    display:
        block;

    object-fit:
        cover;

    object-position:
        center;
}


.student-photo-placeholder {
    width:
        100%;

    height:
        100%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #FFFFFF;

    color:
        #BEBEBE;
}


/* ==========================================================================
   COMPONENT
   ========================================================================== */

.component-details {
    position:
        relative;

    z-index:
        3;

    min-width:
        0;

    color:
        #233E47;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        11px;

    line-height:
        1.18;
}


.component-heading {
    display:
        block;

    margin-bottom:
        10px;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        10px;

    white-space:
        nowrap;
}


.component-value {
    min-width:
        0;
}


.component-first-line {
    display:
        flex;

    align-items:
        baseline;

    gap:
        3px;
}


.component-value strong {
    color:
        #58761C;

    font-size:
        13px;

    font-weight:
        400;
}


.component-full-name {
    display:
        block;

    margin-top:
        1px;

    color:
        #233E47;

    font-size:
        11px;

    line-height:
        1.15;
}


/* ==========================================================================
   ID NUMBER
   ========================================================================== */

.card-id-number {
    margin-top:
        13px;

    display:
        flex;

    align-items:
        baseline;

    gap:
        2px;

    color:
        #000D12;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9.5px;

    white-space:
        nowrap;
}


.card-id-number strong {
    font-weight:
        400;
}


/* ==========================================================================
   STUDENT NAME
   ========================================================================== */

.card-student-name {
    width:
        100%;

    min-height:
        32px;

    margin-top:
        9px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    box-sizing:
        border-box;

    padding:
        0
        4px;

    color:
        #54100F;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        19px;

    font-weight:
        400;

    font-style:
        italic;

    line-height:
        1.05;

    text-align:
        center;

    white-space:
        nowrap;

    overflow:
        hidden;

    text-overflow:
        ellipsis;
}


/* ==========================================================================
   COURSE
   ========================================================================== */

.card-course {
    width:
        100%;

    min-height:
        18px;

    margin-top:
        2px;

    box-sizing:
        border-box;

    color:
        #233E47;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        8.5px;

    line-height:
        1.25;

    text-align:
        center;

    white-space:
        nowrap;

    overflow:
        hidden;

    text-overflow:
        ellipsis;
}


/* ==========================================================================
   QR ON STUDENT CARD
   ========================================================================== */

.card-qr-area {
    width:
        176px;

    height:
        176px;

    margin:
        15px
        auto
        0;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    box-sizing:
        border-box;

    padding:
        4px;

    background:
        #FFFFFF;
}


.card-qr-image {
    width:
        100%;

    height:
        100%;

    display:
        block;

    object-fit:
        contain;

    image-rendering:
        pixelated;
}


.card-qr-placeholder {
    width:
        100%;

    height:
        100%;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    color:
        #BEBEBE;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    text-align:
        center;
}


/* ==========================================================================
   PREVIEW LABEL
   ========================================================================== */

.card-preview-label {
    display:
        none;

    margin-top:
        15px;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        11px;
}


/* ==========================================================================
   RIGHT SIDE
   ========================================================================== */

.qr-information {
    width:
        380px;

    box-sizing:
        border-box;

    padding-top:
        5px;
}


/* ==========================================================================
   LABEL
   ========================================================================== */

.side-label {
    display:
        block;

    margin-bottom:
        6px;

    color:
        #0D171B;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        15px;
}


/* ==========================================================================
   SIDE ID
   ========================================================================== */

.student-id-display {
    margin-bottom:
        13px;
}


.student-id-display strong {
    display:
        block;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        29px;

    font-weight:
        400;

    line-height:
        1.08;

    letter-spacing:
        0.45px;
}


/* ==========================================================================
   LARGE QR
   ========================================================================== */

.large-qr-container {
    width:
        300px;

    height:
        300px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    box-sizing:
        border-box;

    padding:
        7px;

    background:
        #FFFFFF;
}


.large-qr-image {
    width:
        100%;

    height:
        100%;

    display:
        block;

    object-fit:
        contain;

    image-rendering:
        pixelated;
}


.large-qr-placeholder {
    width:
        100%;

    height:
        100%;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    gap:
        12px;

    box-sizing:
        border-box;

    padding:
        22px;

    color:
        #BEBEBE;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        12px;

    text-align:
        center;
}


/* ==========================================================================
   QR DESCRIPTION
   ========================================================================== */

.qr-description {
    width:
        300px;

    margin-top:
        16px;
}


.qr-description p {
    margin:
        0;

    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        11px;

    line-height:
        1.4;
}


/* ==========================================================================
   CREDENTIAL STATUS
   ========================================================================== */

.credential-status {
    width:
        300px;

    min-height:
        38px;

    margin-top:
        13px;

    display:
        none;

    align-items:
        center;

    gap:
        7px;

    box-sizing:
        border-box;

    padding:
        8px
        10px;

    border-radius:
        6px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        10px;
}


.credential-status.ready {
    color:
        #58761C;

    background:
        rgba(
            88,
            118,
            28,
            0.08
        );
}


.credential-status.incomplete {
    color:
        #D99202;

    background:
        rgba(
            217,
            146,
            2,
            0.08
        );
}


/* ==========================================================================
   DONE BUTTON
   ========================================================================== */

.done-button {
    width:
        250px;

    min-height:
        55px;

    margin:
        30px
        0
        0
        auto;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    box-sizing:
        border-box;

    padding:
        9px
        22px;

    border:
        0;

    border-radius:
        8px;

    background:
        #54100F;

    color:
        #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        23px;

    cursor:
        pointer;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.15s
        ease;
}


.done-button svg {
    display:
        none;
}


.done-button:hover {
    background:
        #6B1715;

    transform:
        translateY(
            -1px
        );
}


/* ==========================================================================
   LARGE DESKTOP
   ========================================================================== */

@media (
    min-width: 1400px
) {

    .management-content {
        max-width:
            1050px;

        gap:
            100px;
    }

}


/* ==========================================================================
   MEDIUM DESKTOP
   ========================================================================== */

@media (
    max-width: 1120px
) {

    .management-content {
        grid-template-columns:
            370px
            330px;

        gap:
            50px;
    }


    .id-card-column {
        width:
            370px;
    }


    .qr-information {
        width:
            330px;
    }


    .large-qr-container {
        width:
            280px;

        height:
            280px;
    }


    .qr-description,
    .credential-status {
        width:
            280px;
    }

}


/* ==========================================================================
   TABLET
   ========================================================================== */

@media (
    max-width: 850px
) {

    .page-header {
        justify-content:
            center;
    }


    .title-icon {
        display:
            flex;
    }


    .page-header h1 {
        font-size:
            29px;
    }


    .management-content {
        grid-template-columns:
            1fr;

        justify-items:
            center;

        gap:
            50px;
    }


    .id-card-column {
        width:
            100%;
    }


    .qr-information {
        width:
            100%;

        max-width:
            380px;

        display:
            flex;

        flex-direction:
            column;

        align-items:
            center;

        text-align:
            center;
    }


    .side-label,
    .student-id-display strong {
        text-align:
            center;
    }


    .qr-description {
        text-align:
            left;
    }


    .credential-status {
        display:
            flex;
    }


    .done-button {
        margin:
            27px
            auto
            0;
    }


    .done-button svg {
        display:
            block;
    }


    .card-preview-label {
        display:
            flex;
    }

}


/* ==========================================================================
   MOBILE
   ========================================================================== */

@media (
    max-width: 560px
) {

    .id-management-page {
        padding:
            22px
            10px
            38px;
    }


    .page-header {
        margin-bottom:
            28px;
    }


    .title-icon {
        display:
            none;
    }


    .page-header h1 {
        font-size:
            25px;

        text-align:
            center;
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Exact Card Ratio
    |--------------------------------------------------------------------------
    */

    .digital-id-card {
        width:
            min(
                356px,
                calc(
                    100vw
                    -
                    30px
                )
            );

        height:
            auto;

        aspect-ratio:
            356
            /
            620;
    }


    .student-card-header {
        height:
            14.5%;

        grid-template-columns:
            21%
            minmax(
                0,
                1fr
            );

        padding:
            3%
            4.5%;
    }


    .card-university-logo {
        width:
            100%;

        max-width:
            66px;

        aspect-ratio:
            1
            /
            1;

        height:
            auto;
    }


    .card-university-name {
        font-size:
            clamp(
                13px,
                4.5vw,
                17px
            );
    }


    .student-card-body {
        height:
            85.5%;

        padding:
            5%
            8.5%
            5%;
    }


    /*
    |--------------------------------------------------------------------------
    | Responsive Watermark
    |--------------------------------------------------------------------------
    |
    | Still anchored from bottom on phones.
    |
    */

    .nstp-watermark {
        width:
            69%;

        height:
            auto;

        aspect-ratio:
            1
            /
            1;

        top:
            auto;

        bottom:
            13%;

        left:
            50%;

        transform:
            translateX(
                -50%
            );

        opacity:
            0.11;
    }


    .card-program-title {
        font-size:
            clamp(
                9px,
                3vw,
                12px
            );
    }


    .card-subtitle {
        font-size:
            clamp(
                8px,
                2.4vw,
                10px
            );
    }


    .student-card-main-row {
        grid-template-columns:
            44%
            minmax(
                0,
                1fr
            );

        gap:
            5%;

        margin-top:
            9%;
    }


    .student-photo-box {
        width:
            100%;

        height:
            auto;

        aspect-ratio:
            1
            /
            1;
    }


    .component-heading {
        font-size:
            clamp(
                8px,
                2.4vw,
                10px
            );
    }


    .component-details,
    .component-full-name {
        font-size:
            clamp(
                8.5px,
                2.5vw,
                11px
            );
    }


    .component-value strong {
        font-size:
            clamp(
                10px,
                3vw,
                13px
            );
    }


    .card-id-number {
        font-size:
            clamp(
                8px,
                2.4vw,
                9.5px
            );
    }


    .card-student-name {
        font-size:
            clamp(
                15px,
                5vw,
                19px
            );
    }


    .card-course {
        font-size:
            clamp(
                7px,
                2.2vw,
                8.5px
            );
    }


    .card-qr-area {
        width:
            55%;

        height:
            auto;

        aspect-ratio:
            1
            /
            1;
    }


    /*
    |--------------------------------------------------------------------------
    | Side QR
    |--------------------------------------------------------------------------
    */

    .student-id-display strong {
        font-size:
            27px;
    }


    .large-qr-container {
        width:
            min(
                300px,
                calc(
                    100vw
                    -
                    50px
                )
            );

        height:
            auto;

        aspect-ratio:
            1
            /
            1;
    }


    .qr-description,
    .credential-status {
        width:
            min(
                300px,
                calc(
                    100vw
                    -
                    50px
                )
            );
    }


    .done-button {
        width:
            min(
                250px,
                calc(
                    100vw
                    -
                    70px
                )
            );
    }

}


/* ==========================================================================
   SMALL MOBILE
   ========================================================================== */

@media (
    max-width: 390px
) {

    .id-management-page {
        padding:
            18px
            7px
            30px;
    }


    .page-header h1 {
        font-size:
            22px;
    }


    .digital-id-card {
        border-width:
            3px;

        border-radius:
            8px;
    }


    /*
    |--------------------------------------------------------------------------
    | Small Screen Watermark
    |--------------------------------------------------------------------------
    |
    | Do NOT use top: 27%.
    | That old rule pushed the watermark back upward.
    |
    */

    .nstp-watermark {
        width:
            70%;

        height:
            auto;

        aspect-ratio:
            1
            /
            1;

        top:
            auto;

        bottom:
            13%;

        opacity:
            0.10;
    }


    .large-qr-container {
        width:
            275px;

        height:
            275px;
    }


    .qr-description,
    .credential-status {
        width:
            275px;
    }

}

</style>