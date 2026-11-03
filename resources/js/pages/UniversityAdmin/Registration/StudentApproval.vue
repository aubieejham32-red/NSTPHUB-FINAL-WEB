<script setup>

import {
    computed,
    ref,
} from 'vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import {
    ArrowLeft,
    CheckCircle2,
    Clock3,
    FileSignature,
    Hourglass,
    ShieldCheck,
    X,
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

    timeline: {
        type: Object,
        default: () => ({}),
    },

    signatureUrl: {
        type: String,
        default: '',
    },

    approveUrl: {
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
| State
|--------------------------------------------------------------------------
*/

const approving =
    ref(false);


const signatureFailed =
    ref(false);


/*
|--------------------------------------------------------------------------
| Custom Approval Modal
|--------------------------------------------------------------------------
*/

const showApproveModal =
    ref(false);


/*
|--------------------------------------------------------------------------
| Registration Status
|--------------------------------------------------------------------------
*/

const registrationStatus =
    computed(() => {

        /*
        |--------------------------------------------------------------------------
        | Prefer Backend Display Status
        |--------------------------------------------------------------------------
        */

        const displayStatus =
            String(
                props.student
                    ?.display_status
                ??
                ''
            )
                .trim()
                .toUpperCase();


        if (
            displayStatus ===
            'APPROVED'
        ) {

            return 'APPROVED';

        }


        if (
            displayStatus ===
            'PENDING'
        ) {

            return 'PENDING';

        }


        /*
        |--------------------------------------------------------------------------
        | Fall Back To Internal Status
        |--------------------------------------------------------------------------
        */

        const status =
            String(
                props.student
                    ?.registration_status
                ??
                props.student
                    ?.status
                ??
                'pending'
            )
                .trim()
                .toLowerCase();


        if (
            [
                'approved',
                'confirmed',
                'completed',
            ].includes(
                status
            )
        ) {

            return 'APPROVED';

        }


        return 'PENDING';

    });


const isApproved =
    computed(() => {

        return (
            registrationStatus.value ===
            'APPROVED'
        );

    });


/*
|--------------------------------------------------------------------------
| Student Display Name
|--------------------------------------------------------------------------
*/

const studentDisplayName =
    computed(() => {

        return (
            props.student
                ?.full_name
            ??
            props.student
                ?.name
            ??
            'this student'
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
            ??
            component
            ??
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
            ??
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
| Year & Section
|--------------------------------------------------------------------------
*/

const yearAndSection =
    computed(() => {

        const values = [

            props.student
                ?.year_level,

            props.student
                ?.section,

        ]
            .filter(
                Boolean
            );


        return (
            values.join(
                ' / '
            )
            ||
            '-'
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
| Signature
|--------------------------------------------------------------------------
*/

const studentSignatureUrl =
    computed(() => {

        if (
            signatureFailed.value
        ) {

            return '';

        }


        return (
            props.signatureUrl
            ||
            props.student
                ?.signature_url
            ||
            ''
        );

    });


const handleSignatureError =
    () => {

        signatureFailed.value =
            true;

    };


/*
|--------------------------------------------------------------------------
| Date Formatting
|--------------------------------------------------------------------------
*/

const formatNumericDate =
    (
        value
    ) => {

        if (
            !value
        ) {

            return '-';

        }


        /*
        |--------------------------------------------------------------------------
        | Avoid UTC Shifting Date-Only Values
        |--------------------------------------------------------------------------
        */

        const raw =
            String(
                value
            );


        const datePart =
            raw.slice(
                0,
                10
            );


        const parts =
            datePart.split(
                '-'
            );


        if (
            parts.length ===
                3
            &&
            parts[
                0
            ].length ===
                4
        ) {

            return (
                `${parts[1]}/${parts[2]}/${parts[0]}`
            );

        }


        const date =
            new Date(
                value
            );


        if (
            Number.isNaN(
                date.getTime()
            )
        ) {

            return raw;

        }


        return date.toLocaleDateString(
            'en-US',

            {
                month:
                    '2-digit',

                day:
                    '2-digit',

                year:
                    'numeric',
            }
        );

    };


/*
|--------------------------------------------------------------------------
| Timeline Date
|--------------------------------------------------------------------------
*/

const formatTimelineDate =
    (
        value
    ) => {

        if (
            !value
        ) {

            return '-';

        }


        const date =
            new Date(
                value
            );


        if (
            Number.isNaN(
                date.getTime()
            )
        ) {

            return String(
                value
            );

        }


        return date.toLocaleDateString(
            'en-US',

            {
                month:
                    'long',

                day:
                    '2-digit',

                year:
                    'numeric',

                timeZone:
                    'Asia/Manila',
            }
        );

    };


/*
|--------------------------------------------------------------------------
| Birthdate
|--------------------------------------------------------------------------
*/

const birthdateLabel =
    computed(() => {

        return formatNumericDate(
            props.student
                ?.birth_date
        );

    });


/*
|--------------------------------------------------------------------------
| Submitted Date
|--------------------------------------------------------------------------
*/

const submittedDate =
    computed(() => {

        return formatTimelineDate(
            props.timeline
                ?.submitted_at
            ??
            props.student
                ?.registration_submitted_at
        );

    });


/*
|--------------------------------------------------------------------------
| Editable Until
|--------------------------------------------------------------------------
*/

const editableUntilDate =
    computed(() => {

        return formatTimelineDate(
            props.timeline
                ?.editable_until
        );

    });


/*
|--------------------------------------------------------------------------
| Remaining Days
|--------------------------------------------------------------------------
*/

const remainingDays =
    computed(() => {

        /*
        |--------------------------------------------------------------------------
        | Prefer Laravel
        |--------------------------------------------------------------------------
        */

        if (
            props.timeline
                ?.remaining_days !==
                undefined
            &&
            props.timeline
                ?.remaining_days !==
                null
        ) {

            return Math.max(
                0,

                Number(
                    props.timeline
                        .remaining_days
                )
                ||
                0
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Browser Fallback
        |--------------------------------------------------------------------------
        */

        const editableUntil =
            props.timeline
                ?.editable_until;


        if (
            !editableUntil
        ) {

            return 0;

        }


        const deadline =
            new Date(
                editableUntil
            );


        if (
            Number.isNaN(
                deadline.getTime()
            )
        ) {

            return 0;

        }


        const difference =
            deadline.getTime()
            -
            Date.now();


        if (
            difference <=
            0
        ) {

            return 0;

        }


        return Math.ceil(
            difference
            /
            (
                1000
                *
                60
                *
                60
                *
                24
            )
        );

    });


/*
|--------------------------------------------------------------------------
| Approval Availability
|--------------------------------------------------------------------------
*/

const canApprove =
    computed(() => {

        if (
            isApproved.value
        ) {

            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | Laravel Is Authority
        |--------------------------------------------------------------------------
        */

        if (
            typeof props.timeline
                ?.can_approve ===
            'boolean'
        ) {

            return props.timeline
                .can_approve;

        }


        /*
        |--------------------------------------------------------------------------
        | Browser Fallback
        |--------------------------------------------------------------------------
        */

        const editableUntil =
            props.timeline
                ?.editable_until;


        if (
            !editableUntil
        ) {

            return false;

        }


        const deadline =
            new Date(
                editableUntil
            );


        if (
            Number.isNaN(
                deadline.getTime()
            )
        ) {

            return false;

        }


        return (
            Date.now()
            >=
            deadline.getTime()
        );

    });


/*
|--------------------------------------------------------------------------
| Timeline Message
|--------------------------------------------------------------------------
*/

const timelineNotice =
    computed(() => {

        if (
            isApproved.value
        ) {

            return (
                'This NSTP student registration has already been approved.'
            );

        }


        if (
            canApprove.value
        ) {

            return (
                'The student editing period has ended. This registration is now ready for approval.'
            );

        }


        return (
            'Approve is locked while the student editing window is active — this protects the student from a decision made on information they can still change.'
        );

    });


/*
|--------------------------------------------------------------------------
| Approval Button Text
|--------------------------------------------------------------------------
*/

const approvalButtonText =
    computed(() => {

        if (
            approving.value
        ) {

            return 'APPROVING...';

        }


        if (
            isApproved.value
        ) {

            return 'APPROVED';

        }


        return 'APPROVE';

    });


/*
|--------------------------------------------------------------------------
| Approve URL
|--------------------------------------------------------------------------
*/

const resolvedApproveUrl =
    computed(() => {

        if (
            props.approveUrl
        ) {

            return props.approveUrl;

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
            '/approve'
        );

    });


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


/*
|--------------------------------------------------------------------------
| Open Custom Approval Modal
|--------------------------------------------------------------------------
|
| No window.confirm().
| No alert().
| No browser-native popup.
|
*/

const openApproveModal =
    () => {

        if (
            !canApprove.value
            ||
            approving.value
            ||
            !resolvedApproveUrl.value
        ) {

            return;

        }


        showApproveModal.value =
            true;

    };


/*
|--------------------------------------------------------------------------
| Close Custom Approval Modal
|--------------------------------------------------------------------------
*/

const closeApproveModal =
    () => {

        if (
            approving.value
        ) {

            return;

        }


        showApproveModal.value =
            false;

    };


/*
|--------------------------------------------------------------------------
| Confirm Approval
|--------------------------------------------------------------------------
*/

const confirmApproval =
    () => {

        if (
            !canApprove.value
            ||
            approving.value
            ||
            !resolvedApproveUrl.value
        ) {

            return;

        }


        approving.value =
            true;


        router.patch(
            resolvedApproveUrl.value,

            {},

            {
                preserveScroll:
                    true,


                /*
                |--------------------------------------------------------------------------
                | Close Modal On Successful Approval
                |--------------------------------------------------------------------------
                */

                onSuccess: () => {

                    showApproveModal.value =
                        false;

                },


                /*
                |--------------------------------------------------------------------------
                | Finish
                |--------------------------------------------------------------------------
                */

                onFinish: () => {

                    approving.value =
                        false;

                },
            }
        );

    };

</script>


<template>

    <Head
        title="Student Registration Approval"
    />


    <UniversityAdminDashLayout>

        <main class="student-approval-page">


            <!-- ====================================================
                 PAGE HEADER
            ===================================================== -->

            <header class="page-heading">

                <h1>
                    Student Registration
                </h1>


                <div
                    class="status-badge"
                    :class="{
                        approved:
                            isApproved,

                        pending:
                            !isApproved,
                    }"
                >

                    <CheckCircle2
                        v-if="isApproved"
                        :size="17"
                        :stroke-width="2"
                    />


                    <Clock3
                        v-else
                        :size="17"
                        :stroke-width="2"
                    />


                    <span>
                        {{ registrationStatus }}
                    </span>

                </div>

            </header>


            <!-- ====================================================
                 REGISTRATION INFORMATION
            ===================================================== -->

            <section class="registration-card">


                <!-- =================================================
                     SUBJECT + COMPONENT
                ================================================== -->

                <div class="two-column-fields">

                    <div class="field-row">

                        <span class="field-label">
                            Subject
                        </span>


                        <div class="field-value">

                            {{
                                student.subject
                                ??
                                '-'
                            }}

                        </div>

                    </div>


                    <div class="field-row">

                        <span class="field-label">
                            Components
                        </span>


                        <div class="field-value">
                            {{ componentLabel }}
                        </div>

                    </div>

                </div>


                <!-- =================================================
                     TERM
                ================================================== -->

                <div class="field-row full-field">

                    <span class="field-label">
                        Term
                    </span>


                    <div class="field-value">
                        {{ termLabel }}
                    </div>

                </div>


                <!-- =================================================
                     PERSONAL DETAILS
                ================================================== -->

                <h2 class="section-title">
                    Personal Details
                </h2>


                <!-- =================================================
                     NAME
                ================================================== -->

                <div class="special-row">

                    <span class="field-label">
                        Name
                    </span>


                    <div class="special-field-area">

                        <div class="triple-value-box">

                            <span>

                                {{
                                    student.surname
                                    ??
                                    '-'
                                }}

                            </span>


                            <span>

                                {{
                                    student.first_name
                                    ??
                                    '-'
                                }}

                            </span>


                            <span>

                                {{
                                    student.middle_name
                                    ??
                                    '-'
                                }}

                            </span>

                        </div>


                        <div class="triple-sublabels">

                            <span>
                                Surname
                            </span>

                            <span>
                                First Name
                            </span>

                            <span>
                                Middle Name
                            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     COURSE
                ================================================== -->

                <div class="field-row full-field">

                    <span class="field-label">
                        Course
                    </span>


                    <div class="field-value">

                        {{
                            student.course
                            ??
                            '-'
                        }}

                    </div>

                </div>


                <!-- =================================================
                     YEAR + GENDER
                ================================================== -->

                <div class="two-column-fields">

                    <div class="field-row">

                        <span class="field-label">
                            Yr &amp; Section
                        </span>


                        <div class="field-value">
                            {{ yearAndSection }}
                        </div>

                    </div>


                    <div class="field-row">

                        <span class="field-label">
                            Gender
                        </span>


                        <div class="field-value">
                            {{ genderLabel }}
                        </div>

                    </div>

                </div>


                <!-- =================================================
                     BIRTHDATE
                ================================================== -->

                <div class="field-row full-field">

                    <span class="field-label">
                        Birthdate
                    </span>


                    <div class="field-value">
                        {{ birthdateLabel }}
                    </div>

                </div>


                <!-- =================================================
                     ADDRESS
                ================================================== -->

                <h2 class="section-title">
                    Address
                </h2>


                <div class="special-row">

                    <span class="field-label">
                        Address
                    </span>


                    <div class="special-field-area">

                        <div class="triple-value-box">

                            <span>

                                {{
                                    student.city_address
                                    ??
                                    '-'
                                }}

                            </span>


                            <span>

                                {{
                                    student.municipality
                                    ??
                                    '-'
                                }}

                            </span>


                            <span>

                                {{
                                    student.province
                                    ??
                                    '-'
                                }}

                            </span>

                        </div>


                        <div class="triple-sublabels">

                            <span>
                                City Address
                            </span>

                            <span>
                                Municipality
                            </span>

                            <span>
                                Province
                            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     EMAIL + CONTACT
                ================================================== -->

                <div class="two-column-fields contact-fields">

                    <div class="field-row">

                        <span class="field-label">
                            Email
                        </span>


                        <div class="field-value">

                            {{
                                student.email
                                ??
                                '-'
                            }}

                        </div>

                    </div>


                    <div class="field-row">

                        <span class="field-label">
                            Contact
                        </span>


                        <div class="field-value">

                            {{
                                student.contact_number
                                ??
                                '-'
                            }}

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     EMERGENCY CONTACT
                ================================================== -->

                <h2 class="section-title emergency-title">
                    Person to be notified in case of emergency
                </h2>


                <div class="field-row full-field">

                    <span class="field-label guardian-label">
                        Parents / Guardian
                    </span>


                    <div class="field-value">

                        {{
                            student.guardian_name
                            ??
                            '-'
                        }}

                    </div>

                </div>


                <div class="field-row full-field">

                    <span class="field-label">
                        Address
                    </span>


                    <div class="field-value">

                        {{
                            student.guardian_address
                            ??
                            '-'
                        }}

                    </div>

                </div>


                <div class="field-row full-field">

                    <span class="field-label guardian-label">
                        Contact No. / Telephone No.
                    </span>


                    <div class="field-value">

                        {{
                            student.guardian_contact_number
                            ??
                            '-'
                        }}

                    </div>

                </div>


                <!-- =================================================
                     SIGNATURE
                ================================================== -->

                <div class="signature-area">

                    <div class="signature-display">

                        <img
                            v-if="studentSignatureUrl"
                            :src="studentSignatureUrl"
                            alt="Student digital signature"
                            class="signature-image"
                            @error="handleSignatureError"
                        />


                        <div
                            v-else
                            class="signature-placeholder"
                        >

                            <FileSignature
                                :size="48"
                                :stroke-width="1.4"
                            />


                            <span>
                                Signature unavailable
                            </span>

                        </div>

                    </div>


                    <p>
                        Student Digital Signature
                    </p>

                </div>

            </section>


            <!-- ====================================================
                 REGISTRATION TIMELINE
            ===================================================== -->

            <section class="timeline-card">

                <h2>
                    Registration Timeline
                </h2>


                <div class="timeline-grid">


                    <!-- =============================================
                         SUBMITTED
                    ============================================== -->

                    <div class="timeline-item">

                        <strong>
                            {{ submittedDate }}
                        </strong>

                        <span>
                            Submitted
                        </span>

                    </div>


                    <!-- =============================================
                         EDITABLE UNTIL
                    ============================================== -->

                    <div class="timeline-item">

                        <strong>
                            {{ editableUntilDate }}
                        </strong>

                        <span>
                            Editable Until
                        </span>

                    </div>


                    <!-- =============================================
                         REMAINING DAYS
                    ============================================== -->

                    <div class="timeline-item">

                        <strong>

                            {{ remainingDays }}

                            {{
                                remainingDays ===
                                    1
                                    ? 'Day'
                                    : 'Days'
                            }}

                        </strong>

                        <span>
                            Remaining Days
                        </span>

                    </div>


                    <!-- =============================================
                         STATUS
                    ============================================== -->

                    <div
                        class="timeline-item timeline-status"
                        :class="{
                            approved:
                                isApproved,

                            pending:
                                !isApproved,
                        }"
                    >

                        <strong>
                            {{ registrationStatus }}
                        </strong>

                        <span>
                            Status
                        </span>

                    </div>

                </div>


                <!-- ================================================
                     TIMELINE NOTICE
                ================================================= -->

                <div
                    class="timeline-notice"
                    :class="{
                        ready:
                            canApprove,

                        completed:
                            isApproved,
                    }"
                >

                    <ShieldCheck
                        v-if="
                            canApprove
                            ||
                            isApproved
                        "
                        :size="24"
                        :stroke-width="2"
                    />


                    <Hourglass
                        v-else
                        :size="24"
                        :stroke-width="2"
                    />


                    <p>
                        {{ timelineNotice }}
                    </p>

                </div>

            </section>


            <!-- ====================================================
                 ACTION BUTTONS
            ===================================================== -->

            <footer class="page-actions">

                <!-- =================================================
                     BACK
                ================================================== -->

                <button
                    type="button"
                    class="back-button"
                    @click="goBack"
                >

                    <ArrowLeft
                        :size="23"
                        :stroke-width="2.5"
                    />


                    <span>
                        BACK
                    </span>

                </button>


                <!-- =================================================
                     APPROVE
                ================================================== -->

                <button
                    type="button"
                    class="approve-button"
                    :class="{
                        active:
                            canApprove,

                        approved:
                            isApproved,
                    }"
                    :disabled="
                        !canApprove
                        ||
                        approving
                    "
                    @click="openApproveModal"
                >

                    <CheckCircle2
                        :size="24"
                        :stroke-width="2.5"
                    />


                    <span>
                        {{ approvalButtonText }}
                    </span>

                </button>

            </footer>

        </main>


        <!-- ========================================================
             CUSTOM APPROVAL MODAL
        ========================================================= -->

        <Teleport to="body">

            <Transition name="modal-fade">

                <div
                    v-if="showApproveModal"
                    class="approval-modal-overlay"
                    role="presentation"
                    @click.self="closeApproveModal"
                >

                    <section
                        class="approval-modal"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="approval-modal-title"
                    >

                        <!-- =========================================
                             TOP ACCENT
                        ========================================== -->

                        <div class="approval-modal-accent"></div>


                        <!-- =========================================
                             CLOSE
                        ========================================== -->

                        <button
                            type="button"
                            class="approval-modal-close"
                            aria-label="Close confirmation"
                            :disabled="approving"
                            @click="closeApproveModal"
                        >

                            <X
                                :size="22"
                                :stroke-width="2"
                            />

                        </button>


                        <!-- =========================================
                             ICON
                        ========================================== -->

                        <div class="approval-modal-icon">

                            <CheckCircle2
                                :size="42"
                                :stroke-width="1.8"
                            />

                        </div>


                        <!-- =========================================
                             TITLE
                        ========================================== -->

                        <h2
                            id="approval-modal-title"
                            class="approval-modal-title"
                        >
                            Confirm Student Approval
                        </h2>


                        <!-- =========================================
                             MESSAGE
                        ========================================== -->

                        <p class="approval-modal-message">

                            Are you sure you want to approve the NSTP
                            registration of

                            <strong>
                                {{ studentDisplayName }}
                            </strong>?

                        </p>


                        <!-- =========================================
                             NOTICE
                        ========================================== -->

                        <div class="approval-modal-note">

                            <ShieldCheck
                                :size="20"
                                :stroke-width="2"
                            />


                            <span>

                                Once approved, the student can proceed
                                to NSTP Student ID and QR credential
                                generation.

                            </span>

                        </div>


                        <!-- =========================================
                             ACTIONS
                        ========================================== -->

                        <div class="approval-modal-actions">

                            <button
                                type="button"
                                class="modal-cancel-button"
                                :disabled="approving"
                                @click="closeApproveModal"
                            >

                                <X
                                    :size="19"
                                    :stroke-width="2.3"
                                />

                                <span>
                                    CANCEL
                                </span>

                            </button>


                            <button
                                type="button"
                                class="modal-approve-button"
                                :disabled="approving"
                                @click="confirmApproval"
                            >

                                <CheckCircle2
                                    :size="20"
                                    :stroke-width="2.4"
                                />


                                <span>

                                    {{
                                        approving
                                            ? 'APPROVING...'
                                            : 'YES, APPROVE'
                                    }}

                                </span>

                            </button>

                        </div>

                    </section>

                </div>

            </Transition>

        </Teleport>

    </UniversityAdminDashLayout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| NSTP HUB COLORS
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

.student-approval-page {
    width: 100%;
    max-width: 1180px;
    min-height: 100%;

    margin: 0 auto;

    box-sizing: border-box;

    padding:
        55px
        42px
        70px;

    background:
        #EFEBE2;

    color:
        #233E47;
}


/* ==========================================================================
   HEADER
   ========================================================================== */

.page-heading {
    width: 100%;

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 24px;

    margin-bottom: 25px;
}


.page-heading h1 {
    margin: 0;

    color:
        #54100F;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 42px;

    line-height: 1.1;

    font-weight: 700;
}


.status-badge {
    min-width: 145px;
    height: 48px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 7px;

    box-sizing: border-box;

    padding:
        8px
        20px;

    border-radius: 16px;

    color:
        #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 13px;
}


.status-badge.pending {
    background:
        #D99202;
}


.status-badge.approved {
    background:
        #58761C;
}


/* ==========================================================================
   REGISTRATION CARD
   ========================================================================== */

.registration-card {
    width: 100%;

    box-sizing: border-box;

    padding:
        32px
        30px
        35px;

    border:
        2px solid
        rgba(
            190,
            190,
            190,
            0.65
        );

    background:
        transparent;
}


/* ==========================================================================
   FIELD GRID
   ========================================================================== */

.two-column-fields {
    width: 100%;

    display: grid;

    grid-template-columns:
        repeat(
            2,
            minmax(
                0,
                1fr
            )
        );

    gap:
        20px
        28px;

    margin-bottom: 18px;
}


.field-row {
    width: 100%;
    min-width: 0;

    display: grid;

    grid-template-columns:
        100px
        minmax(
            0,
            1fr
        );

    align-items: center;

    gap: 12px;
}


.full-field {
    margin-bottom: 18px;
}


.field-label {
    color:
        #0D171B;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 14px;

    line-height: 1.3;
}


.field-value {
    min-width: 0;
    min-height: 45px;

    display: flex;

    align-items: center;

    box-sizing: border-box;

    padding:
        10px
        18px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.72
        );

    border-radius: 13px;

    background:
        #FFFFFF;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 13px;

    line-height: 1.4;

    overflow-wrap:
        anywhere;
}


/* ==========================================================================
   SECTION TITLES
   ========================================================================== */

.section-title {
    margin:
        34px
        0
        26px;

    color:
        #54100F;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 23px;

    font-weight: 700;
}


.emergency-title {
    margin-top: 43px;
}


/* ==========================================================================
   NAME / ADDRESS SPECIAL FIELDS
   ========================================================================== */

.special-row {
    width: 100%;

    display: grid;

    grid-template-columns:
        100px
        minmax(
            0,
            1fr
        );

    align-items: start;

    gap: 12px;

    margin-bottom: 22px;
}


.special-row > .field-label {
    padding-top: 14px;
}


.special-field-area {
    min-width: 0;
}


.triple-value-box {
    width: 100%;
    min-height: 45px;

    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(
                0,
                1fr
            )
        );

    align-items: center;

    gap: 10px;

    box-sizing: border-box;

    padding:
        8px
        18px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.72
        );

    border-radius: 13px;

    background:
        #FFFFFF;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 13px;
}


.triple-value-box span {
    min-width: 0;

    text-align: center;

    overflow-wrap:
        anywhere;
}


.triple-sublabels {
    width: 100%;

    display: grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap: 10px;

    padding-top: 7px;

    color:
        #0D171B;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 12px;

    text-align: center;
}


/* ==========================================================================
   CONTACT
   ========================================================================== */

.contact-fields {
    margin-top: 38px;
}


.guardian-label {
    line-height: 1.3;
}


/* ==========================================================================
   SIGNATURE
   ========================================================================== */

.signature-area {
    width: 350px;
    max-width: 100%;

    margin:
        40px
        30px
        10px
        auto;

    display: flex;

    flex-direction: column;

    align-items: center;
}


.signature-display {
    width: 100%;
    height: 155px;

    display: flex;

    align-items: center;
    justify-content: center;

    overflow: hidden;

    background:
        transparent;
}


.signature-image {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: contain;

    object-position: center;

    background:
        transparent;
}


.signature-placeholder {
    width: 100%;
    height: 100%;

    display: flex;

    flex-direction: column;

    align-items: center;
    justify-content: center;

    gap: 8px;

    color:
        #BEBEBE;
}


.signature-placeholder span {
    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 12px;
}


.signature-area p {
    margin:
        6px
        0
        0;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 13px;
}


/* ==========================================================================
   TIMELINE
   ========================================================================== */

.timeline-card {
    width: calc(
        100%
        -
        40px
    );

    margin:
        48px
        auto
        0;

    box-sizing: border-box;

    padding:
        22px
        24px
        20px;

    border:
        2px solid
        rgba(
            190,
            190,
            190,
            0.65
        );
}


.timeline-card h2 {
    margin:
        0
        0
        27px;

    color:
        #54100F;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 20px;
}


.timeline-grid {
    display: grid;

    grid-template-columns:
        repeat(
            4,
            minmax(
                0,
                1fr
            )
        );

    gap: 22px;
}


.timeline-item {
    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 7px;

    text-align: center;
}


.timeline-item strong {
    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 15px;

    font-weight: 800;
}


.timeline-item span {
    color:
        #0D171B;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 12px;
}


.timeline-status.pending strong {
    color:
        #D99202;
}


.timeline-status.approved strong {
    color:
        #58761C;
}


/* ==========================================================================
   TIMELINE NOTICE
   ========================================================================== */

.timeline-notice {
    width: 100%;
    min-height: 50px;

    margin-top: 23px;

    display: flex;

    align-items: center;

    gap: 13px;

    box-sizing: border-box;

    padding:
        10px
        18px;

    border:
        1px solid
        #D99202;

    border-radius: 7px;

    background:
        rgba(
            255,
            189,
            54,
            0.35
        );

    color:
        #D99202;
}


.timeline-notice.ready,
.timeline-notice.completed {
    border-color:
        #58761C;

    background:
        rgba(
            88,
            118,
            28,
            0.10
        );

    color:
        #58761C;
}


.timeline-notice svg {
    flex-shrink: 0;
}


.timeline-notice p {
    margin: 0;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 11px;

    line-height: 1.5;
}


/* ==========================================================================
   PAGE ACTIONS
   ========================================================================== */

.page-actions {
    width: calc(
        100%
        -
        80px
    );

    margin:
        55px
        auto
        0;

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 30px;
}


.back-button,
.approve-button {
    min-width: 220px;
    min-height: 68px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 9px;

    padding:
        12px
        30px;

    border: 0;

    border-radius: 7px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 23px;

    font-weight: 900;
}


.back-button {
    background:
        #58761C;

    color:
        #FFFFFF;

    cursor: pointer;
}


.back-button:hover {
    background:
        #496516;
}


.approve-button {
    background:
        #D4D0C3;

    color:
        #AAA596;

    cursor:
        not-allowed;
}


.approve-button.active {
    background:
        #58761C;

    color:
        #FFFFFF;

    cursor: pointer;
}


.approve-button.active:hover {
    background:
        #496516;
}


.approve-button.approved {
    background:
        rgba(
            88,
            118,
            28,
            0.18
        );

    color:
        #58761C;
}


/* ==========================================================================
   CUSTOM APPROVAL MODAL
   ========================================================================== */

.approval-modal-overlay {
    position: fixed;

    inset: 0;

    z-index: 99999;

    display: flex;

    align-items: center;
    justify-content: center;

    box-sizing: border-box;

    padding: 24px;

    background:
        rgba(
            0,
            13,
            18,
            0.58
        );

    backdrop-filter:
        blur(
            2px
        );
}


/* ==========================================================================
   MODAL CARD
   ========================================================================== */

.approval-modal {
    position: relative;

    width: 100%;

    max-width: 540px;

    overflow: hidden;

    box-sizing: border-box;

    padding:
        46px
        42px
        36px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.28
        );

    border-radius: 18px;

    background:
        #EFEBE2;

    box-shadow:
        0
        22px
        70px
        rgba(
            0,
            13,
            18,
            0.30
        );
}


/* ==========================================================================
   MODAL TOP ACCENT
   ========================================================================== */

.approval-modal-accent {
    position: absolute;

    top: 0;
    left: 0;
    right: 0;

    height: 12px;

    background:
        #54100F;
}


/* ==========================================================================
   MODAL CLOSE
   ========================================================================== */

.approval-modal-close {
    position: absolute;

    top: 21px;
    right: 21px;

    width: 38px;
    height: 38px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.18
        );

    border-radius: 50%;

    background:
        transparent;

    color:
        #54100F;

    cursor: pointer;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.2s
        ease;
}


.approval-modal-close:hover:not(:disabled) {
    background:
        rgba(
            84,
            16,
            15,
            0.08
        );

    transform:
        rotate(
            5deg
        );
}


.approval-modal-close:disabled {
    opacity: 0.45;

    cursor: not-allowed;
}


/* ==========================================================================
   MODAL ICON
   ========================================================================== */

.approval-modal-icon {
    width: 76px;
    height: 76px;

    margin:
        8px
        auto
        20px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background:
        rgba(
            88,
            118,
            28,
            0.12
        );

    color:
        #58761C;
}


/* ==========================================================================
   MODAL TITLE
   ========================================================================== */

.approval-modal-title {
    margin:
        0
        0
        14px;

    color:
        #54100F;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 28px;

    font-weight: 700;

    line-height: 1.2;

    text-align: center;
}


/* ==========================================================================
   MODAL MESSAGE
   ========================================================================== */

.approval-modal-message {
    max-width: 430px;

    margin:
        0
        auto;

    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 15px;

    line-height: 1.65;

    text-align: center;
}


.approval-modal-message strong {
    color:
        #54100F;

    font-weight: 700;
}


/* ==========================================================================
   MODAL NOTE
   ========================================================================== */

.approval-modal-note {
    width: 100%;

    margin-top: 24px;

    display: flex;

    align-items: flex-start;

    gap: 10px;

    box-sizing: border-box;

    padding:
        13px
        15px;

    border:
        1px solid
        rgba(
            88,
            118,
            28,
            0.45
        );

    border-radius: 10px;

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


.approval-modal-note svg {
    flex-shrink: 0;
}


.approval-modal-note span {
    color:
        #233E47;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 12px;

    line-height: 1.5;
}


/* ==========================================================================
   MODAL BUTTONS
   ========================================================================== */

.approval-modal-actions {
    width: 100%;

    margin-top: 30px;

    display: grid;

    grid-template-columns:
        1fr
        1fr;

    gap: 14px;
}


.modal-cancel-button,
.modal-approve-button {
    min-height: 52px;

    display: flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    box-sizing: border-box;

    padding:
        10px
        18px;

    border-radius: 10px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 13px;

    font-weight: 800;

    cursor: pointer;

    transition:
        transform
        0.15s
        ease,
        background-color
        0.2s
        ease,
        border-color
        0.2s
        ease;
}


/* ==========================================================================
   MODAL CANCEL
   ========================================================================== */

.modal-cancel-button {
    border:
        2px solid
        #54100F;

    background:
        transparent;

    color:
        #54100F;
}


.modal-cancel-button:hover:not(:disabled) {
    background:
        rgba(
            84,
            16,
            15,
            0.07
        );

    transform:
        translateY(
            -1px
        );
}


/* ==========================================================================
   MODAL APPROVE
   ========================================================================== */

.modal-approve-button {
    border:
        2px solid
        #58761C;

    background:
        #58761C;

    color:
        #FFFFFF;
}


.modal-approve-button:hover:not(:disabled) {
    background:
        #496516;

    border-color:
        #496516;

    transform:
        translateY(
            -1px
        );
}


.modal-cancel-button:disabled,
.modal-approve-button:disabled {
    opacity: 0.55;

    cursor: not-allowed;

    transform: none;
}


/* ==========================================================================
   MODAL TRANSITION
   ========================================================================== */

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition:
        opacity
        0.2s
        ease;
}


.modal-fade-enter-active .approval-modal,
.modal-fade-leave-active .approval-modal {
    transition:
        opacity
        0.2s
        ease,
        transform
        0.2s
        ease;
}


.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}


.modal-fade-enter-from .approval-modal,
.modal-fade-leave-to .approval-modal {
    opacity: 0;

    transform:
        translateY(
            12px
        )
        scale(
            0.97
        );
}


/* ==========================================================================
   TABLET
   ========================================================================== */

@media (
    max-width: 1000px
) {

    .student-approval-page {
        padding:
            42px
            28px
            55px;
    }


    .page-heading h1 {
        font-size:
            35px;
    }


    .registration-card {
        padding:
            27px
            22px;
    }


    .field-row,
    .special-row {
        grid-template-columns:
            85px
            minmax(
                0,
                1fr
            );
    }


    .timeline-card,
    .page-actions {
        width:
            100%;
    }

}


/* ==========================================================================
   SMALL TABLET
   ========================================================================== */

@media (
    max-width: 760px
) {

    .two-column-fields {
        grid-template-columns:
            1fr;
    }


    .timeline-grid {
        grid-template-columns:
            repeat(
                2,
                1fr
            );

        row-gap:
            25px;
    }


    .back-button,
    .approve-button {
        min-width:
            180px;

        min-height:
            60px;

        font-size:
            19px;
    }

}


/* ==========================================================================
   MOBILE
   ========================================================================== */

@media (
    max-width: 600px
) {

    .student-approval-page {
        padding:
            25px
            14px
            40px;
    }


    .page-heading {
        align-items:
            flex-start;
    }


    .page-heading h1 {
        font-size:
            28px;
    }


    .status-badge {
        min-width:
            95px;

        height:
            36px;

        padding:
            6px
            10px;

        font-size:
            10px;
    }


    .registration-card {
        padding:
            20px
            13px;
    }


    .field-row,
    .special-row {
        grid-template-columns:
            1fr;

        gap:
            7px;
    }


    .special-row > .field-label {
        padding-top:
            0;
    }


    .field-value {
        min-height:
            42px;

        font-size:
            12px;
    }


    .triple-value-box {
        padding:
            8px;

        font-size:
            10px;
    }


    .triple-sublabels {
        font-size:
            9px;
    }


    .section-title {
        font-size:
            20px;
    }


    .signature-area {
        width:
            280px;

        margin:
            36px
            auto
            5px;
    }


    .signature-display {
        height:
            130px;
    }


    .timeline-card {
        margin-top:
            32px;

        padding:
            17px
            13px;
    }


    .timeline-grid {
        gap:
            23px
            12px;
    }


    .timeline-item strong {
        font-size:
            12px;
    }


    .timeline-item span {
        font-size:
            10px;
    }


    .timeline-notice {
        padding:
            10px
            12px;
    }


    .timeline-notice p {
        font-size:
            9.5px;
    }


    .page-actions {
        margin-top:
            35px;

        gap:
            18px;
    }


    .back-button,
    .approve-button {
        width:
            46%;

        min-width:
            0;

        min-height:
            55px;

        padding:
            10px;

        font-size:
            16px;
    }


    /*
    |--------------------------------------------------------------------------
    | Mobile Modal
    |--------------------------------------------------------------------------
    */

    .approval-modal-overlay {
        padding:
            15px;
    }


    .approval-modal {
        padding:
            42px
            22px
            25px;

        border-radius:
            14px;
    }


    .approval-modal-icon {
        width:
            64px;

        height:
            64px;

        margin-bottom:
            15px;
    }


    .approval-modal-title {
        font-size:
            23px;
    }


    .approval-modal-message {
        font-size:
            13px;
    }


    .approval-modal-actions {
        grid-template-columns:
            1fr;

        gap:
            10px;
    }

}


/* ==========================================================================
   VERY SMALL MOBILE
   ========================================================================== */

@media (
    max-width: 400px
) {

    .student-approval-page {
        padding:
            20px
            9px
            35px;
    }


    .page-heading h1 {
        font-size:
            23px;
    }


    .status-badge {
        min-width:
            80px;

        font-size:
            9px;
    }


    .triple-value-box,
    .triple-sublabels {
        gap:
            4px;
    }


    .timeline-grid {
        grid-template-columns:
            1fr;
    }


    .page-actions {
        width:
            100%;

        gap:
            10px;
    }


    .back-button,
    .approve-button {
        flex:
            1;

        width:
            auto;

        font-size:
            14px;
    }


    .approval-modal {
        padding:
            40px
            17px
            20px;
    }


    .approval-modal-title {
        font-size:
            20px;
    }


    .approval-modal-note {
        padding:
            11px;
    }

}

</style>