<script setup>
import Admin_IC_Layout from '@/layouts/Admin_IC_Layout.vue';

import {
    Head,
    router,
    useForm,
} from '@inertiajs/vue3';

import {
    computed,
    ref,
} from 'vue';

import {
    AlertTriangle,
    ArrowLeft,
    BadgeCheck,
    BookOpen,
    Check,
    GraduationCap,
    IdCard,
    Info,
    Layers3,
    Save,
    ShieldCheck,
    UserRound,
    UserRoundX,
} from 'lucide-vue-next';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
|
| Student information comes from Laravel / MySQL.
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

    authRole: {
        type: String,
        default: '',
    },

    student: {
        type: Object,
        default: () => ({}),
    },

    updateEndpoint: {
        type: String,
        default: '',
    },

    viewEndpoint: {
        type: String,
        default: '',
    },

    backEndpoint: {
        type: String,
        default:
            '/instructor-coordinator/attendance',
    },

});


/*
|--------------------------------------------------------------------------
| Current Role
|--------------------------------------------------------------------------
*/

const currentRole = computed(() => {

    return (
        props.authRole
        ||
        props.role
        ||
        ''
    );

});


/*
|--------------------------------------------------------------------------
| Edit Permission
|--------------------------------------------------------------------------
|
| Frontend restriction only.
| Laravel controller must still validate authorization.
|
*/

const canEdit = computed(() => {

    return [
        'university-admin',
        'instructor',
        'coordinator-attendance',
    ].includes(
        currentRole.value
    );

});


/*
|--------------------------------------------------------------------------
| Normalize Student Status
|--------------------------------------------------------------------------
*/

const normalizeStatus = (
    value
) => {

    const status =
        String(
            value
            ??
            ''
        )
            .trim()
            .toUpperCase()
            .replace(
                /_/g,
                ' '
            )
            .replace(
                /-/g,
                ' '
            );


    if (
        [
            'WARNING',
            'WARNING FOR DROPOUT',
            'AT RISK',
            'AT RISK FOR DROPOUT',
        ].includes(
            status
        )
    ) {

        return 'WARNING FOR DROPOUT';

    }


    if (
        [
            'DROPOUT',
            'DROP OUT',
            'DROPPED',
        ].includes(
            status
        )
    ) {

        return 'DROPOUT';

    }


    return 'ACTIVE';

};


/*
|--------------------------------------------------------------------------
| Original Database Status
|--------------------------------------------------------------------------
*/

const originalStatus = computed(() => {

    return normalizeStatus(
        props.student?.nstp_status
        ??
        props.student?.status
        ??
        'ACTIVE'
    );

});


/*
|--------------------------------------------------------------------------
| Inertia Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    nstp_status:
        originalStatus.value,

});


/*
|--------------------------------------------------------------------------
| Student Full Name
|--------------------------------------------------------------------------
*/

const studentFullName = computed(() => {

    const surname =
        String(
            props.student?.surname
            ??
            props.student?.last_name
            ??
            ''
        )
            .trim();


    const firstName =
        String(
            props.student?.first_name
            ??
            ''
        )
            .trim();


    const middleName =
        String(
            props.student?.middle_name
            ??
            ''
        )
            .trim();


    const givenNames =
        [
            firstName,
            middleName,
        ]
            .filter(
                Boolean
            )
            .join(
                ' '
            );


    if (
        surname
        &&
        givenNames
    ) {

        return `${surname}, ${givenNames}`
            .toUpperCase();

    }


    const existing =
        String(
            props.student?.full_name
            ??
            props.student?.name
            ??
            ''
        )
            .trim();


    return existing
        ? existing.toUpperCase()
        : 'STUDENT';

});


/*
|--------------------------------------------------------------------------
| Student ID Number
|--------------------------------------------------------------------------
*/

const studentIdNumber = computed(() => {

    return String(
        props.student?.student_id_number
        ??
        props.student?.id_number
        ??
        props.student?.student_id
        ??
        props.student?.school_id
        ??
        '-'
    )
        .trim();

});


/*
|--------------------------------------------------------------------------
| Course
|--------------------------------------------------------------------------
*/

const studentCourse = computed(() => {

    return String(
        props.student?.course
        ??
        props.student?.program
        ??
        props.student?.degree_program
        ??
        '-'
    )
        .trim()
        .toUpperCase();

});


/*
|--------------------------------------------------------------------------
| Year Level
|--------------------------------------------------------------------------
*/

const studentYearLevel = computed(() => {

    const value =
        String(
            props.student?.year_level
            ??
            props.student?.year
            ??
            ''
        )
            .trim();


    return value
        ? value.toUpperCase()
        : '-';

});


/*
|--------------------------------------------------------------------------
| Section
|--------------------------------------------------------------------------
*/

const studentSection = computed(() => {

    const value =
        String(
            props.student?.section
            ??
            ''
        )
            .trim();


    return value
        ? value.toUpperCase()
        : '-';

});


/*
|--------------------------------------------------------------------------
| Year / Section
|--------------------------------------------------------------------------
*/

const studentYearSection = computed(() => {

    const year =
        studentYearLevel.value;


    const section =
        studentSection.value;


    if (
        year === '-'
        &&
        section === '-'
    ) {

        return '-';

    }


    if (
        year === '-'
    ) {

        return section;

    }


    if (
        section === '-'
    ) {

        return year;

    }


    return `${year} • ${section}`;

});


/*
|--------------------------------------------------------------------------
| NSTP Component
|--------------------------------------------------------------------------
*/

const studentComponent = computed(() => {

    return String(
        props.student?.component
        ??
        props.student?.nstp_component
        ??
        '-'
    )
        .trim()
        .toUpperCase();

});


/*
|--------------------------------------------------------------------------
| Profile Photo
|--------------------------------------------------------------------------
*/

const rawProfilePhoto = computed(() => {

    return String(
        props.student?.profile_photo_url
        ??
        props.student?.profile_photo
        ??
        props.student?.photo_url
        ??
        props.student?.avatar
        ??
        ''
    )
        .trim();

});


const profilePhoto = computed(() => {

    const photo =
        rawProfilePhoto.value;


    if (
        !photo
    ) {

        return '';

    }


    if (
        photo.startsWith(
            'http://'
        )
        ||
        photo.startsWith(
            'https://'
        )
        ||
        photo.startsWith(
            '/'
        )
    ) {

        return photo;

    }


    return `/storage/${photo}`;

});


const photoLoadFailed =
    ref(
        false
    );


const hasProfilePhoto = computed(() => {

    return (
        profilePhoto.value !== ''
        &&
        !photoLoadFailed.value
    );

});


const handleProfilePhotoError = () => {

    photoLoadFailed.value =
        true;

};


/*
|--------------------------------------------------------------------------
| Student Initials
|--------------------------------------------------------------------------
*/

const studentInitials = computed(() => {

    const first =
        String(
            props.student?.first_name
            ??
            ''
        )
            .trim()
            .charAt(
                0
            );


    const last =
        String(
            props.student?.surname
            ??
            props.student?.last_name
            ??
            ''
        )
            .trim()
            .charAt(
                0
            );


    return (
        `${first}${last}`
            .toUpperCase()
        ||
        'ST'
    );

});


/*
|--------------------------------------------------------------------------
| Status Options
|--------------------------------------------------------------------------
|
| Requested visual reference:
|
| ACTIVE                 = GREEN
| WARNING FOR DROPOUT    = MAROON
| DROPOUT                = BLACK
|
*/

const statusOptions = [

    {
        value:
            'ACTIVE',

        title:
            'ACTIVE',

        description:
            'Student remains active and in good NSTP standing.',

        icon:
            BadgeCheck,

        className:
            'status-choice--active',
    },

    {
        value:
            'WARNING FOR DROPOUT',

        title:
            'WARNING FOR DROPOUT',

        description:
            'Student requires attention due to attendance concerns.',

        icon:
            AlertTriangle,

        className:
            'status-choice--warning',
    },

    {
        value:
            'DROPOUT',

        title:
            'DROPOUT',

        description:
            'Student is officially marked as an NSTP dropout.',

        icon:
            UserRoundX,

        className:
            'status-choice--dropout',
    },

];


/*
|--------------------------------------------------------------------------
| Selected Status
|--------------------------------------------------------------------------
*/

const selectedStatus = computed(() => {

    return statusOptions.find(
        option =>
            option.value ===
            form.nstp_status
    )
    ??
    statusOptions[0];

});


/*
|--------------------------------------------------------------------------
| Detect Status Change
|--------------------------------------------------------------------------
*/

const statusChanged = computed(() => {

    return (
        form.nstp_status !==
        originalStatus.value
    );

});


/*
|--------------------------------------------------------------------------
| Select Status
|--------------------------------------------------------------------------
*/

const selectStatus = (
    value
) => {

    if (
        !canEdit.value
        ||
        form.processing
    ) {

        return;

    }


    form.nstp_status =
        value;


    form.clearErrors(
        'nstp_status'
    );

};


/*
|--------------------------------------------------------------------------
| Resolve Update Endpoint
|--------------------------------------------------------------------------
*/

const resolvedUpdateEndpoint = computed(() => {

    if (
        props.updateEndpoint
    ) {

        return props.updateEndpoint;

    }


    return `/instructor-coordinator/attendance/students/${props.student?.id}/status`;

});


/*
|--------------------------------------------------------------------------
| Resolve View Endpoint
|--------------------------------------------------------------------------
*/

const resolvedViewEndpoint = computed(() => {

    if (
        props.viewEndpoint
    ) {

        return props.viewEndpoint;

    }


    return `/instructor-coordinator/attendance/students/${props.student?.id}`;

});


/*
|--------------------------------------------------------------------------
| Back To Attendance List
|--------------------------------------------------------------------------
*/

const goBackToList = () => {

    router.visit(
        props.backEndpoint
        ||
        '/instructor-coordinator/attendance',
        {
            preserveScroll:
                false,

            preserveState:
                false,
        }
    );

};


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

const cancelEdit = () => {

    router.visit(
        resolvedViewEndpoint.value,
        {
            preserveScroll:
                false,
        }
    );

};


/*
|--------------------------------------------------------------------------
| Save Student Status
|--------------------------------------------------------------------------
*/

const saveStatus = () => {

    if (
        !canEdit.value
        ||
        !statusChanged.value
        ||
        form.processing
    ) {

        return;

    }


    form.patch(
        resolvedUpdateEndpoint.value,
        {
            preserveScroll:
                true,

            onSuccess: () => {

                /*
                |--------------------------------------------------------------------------
                | Laravel may redirect to the student attendance view.
                |--------------------------------------------------------------------------
                */

            },
        }
    );

};

</script>


<template>

    <Head
        title="Edit Student Attendance"
    />


    <Admin_IC_Layout
        :user="props.user"
        :role="currentRole"
    >

        <main
            class="edit-student-attendance-page"
        >

            <!-- ============================================================
                 BACK TO LIST
            ============================================================= -->

            <div
                class="edit-back-navigation"
            >

                <button
                    type="button"
                    class="edit-back-button"
                    title="Back to List"
                    aria-label="Back to List"
                    @click="
                        goBackToList
                    "
                >

                    <ArrowLeft
                        :size="18"
                        :stroke-width="2.3"
                    />


                    <span>
                        Back to List
                    </span>

                </button>

            </div>


            <!-- ============================================================
                 PAGE HERO
            ============================================================= -->

            <section
                class="edit-page-hero"
            >

                <div
                    class="edit-page-hero__icon"
                >

                    <ShieldCheck
                        :size="27"
                        :stroke-width="2.1"
                    />

                </div>


                <div
                    class="edit-page-hero__content"
                >

                    <span>
                        NSTP ATTENDANCE MANAGEMENT
                    </span>


                    <h1>
                        Edit Student Standing
                    </h1>


                    <p>
                        Update the student's official NSTP status.
                        Changes will be saved to the student's database profile.
                    </p>

                </div>


                <div
                    class="edit-page-hero__security"
                >

                    <ShieldCheck
                        :size="17"
                        :stroke-width="2.2"
                    />


                    <span>
                        AUTHORIZED EDIT
                    </span>

                </div>

            </section>


            <!-- ============================================================
                 STUDENT PROFILE
            ============================================================= -->

            <section
                class="student-profile-card"
            >

                <!-- DECORATION -->

                <div
                    class="
                        profile-decoration
                        profile-decoration--one
                    "
                ></div>


                <div
                    class="
                        profile-decoration
                        profile-decoration--two
                    "
                ></div>


                <!-- PHOTO -->

                <div
                    class="student-photo-wrapper"
                >

                    <div
                        class="student-photo-ring"
                    >

                        <img
                            v-if="
                                hasProfilePhoto
                            "
                            :src="
                                profilePhoto
                            "
                            :alt="
                                `${studentFullName} profile photo`
                            "
                            class="student-photo"
                            @error="
                                handleProfilePhotoError
                            "
                        />


                        <div
                            v-else
                            class="student-photo-fallback"
                        >

                            <span>
                                {{ studentInitials }}
                            </span>

                        </div>

                    </div>


                    <div
                        class="student-photo-badge"
                    >

                        <UserRound
                            :size="16"
                            :stroke-width="2.3"
                        />

                    </div>

                </div>


                <!-- PRIMARY INFO -->

                <div
                    class="student-primary-info"
                >

                    <span
                        class="student-profile-eyebrow"
                    >
                        DATABASE STUDENT PROFILE
                    </span>


                    <h2>
                        {{ studentFullName }}
                    </h2>


                    <div
                        class="student-id-row"
                    >

                        <IdCard
                            :size="18"
                            :stroke-width="2"
                        />


                        <span>
                            {{ studentIdNumber }}
                        </span>

                    </div>


                    <!-- META -->

                    <div
                        class="student-profile-meta"
                    >

                        <!-- COURSE -->

                        <article
                            class="student-meta-card"
                        >

                            <div
                                class="student-meta-card__icon"
                            >

                                <GraduationCap
                                    :size="17"
                                    :stroke-width="2"
                                />

                            </div>


                            <div>

                                <small>
                                    COURSE
                                </small>


                                <strong>
                                    {{ studentCourse }}
                                </strong>

                            </div>

                        </article>


                        <!-- YEAR / SECTION -->

                        <article
                            class="student-meta-card"
                        >

                            <div
                                class="student-meta-card__icon"
                            >

                                <BookOpen
                                    :size="17"
                                    :stroke-width="2"
                                />

                            </div>


                            <div>

                                <small>
                                    YEAR / SECTION
                                </small>


                                <strong>
                                    {{ studentYearSection }}
                                </strong>

                            </div>

                        </article>


                        <!-- COMPONENT -->

                        <article
                            class="
                                student-meta-card
                                student-meta-card--component
                            "
                        >

                            <div
                                class="student-meta-card__icon"
                            >

                                <Layers3
                                    :size="17"
                                    :stroke-width="2"
                                />

                            </div>


                            <div>

                                <small>
                                    NSTP COMPONENT
                                </small>


                                <strong>
                                    {{ studentComponent }}
                                </strong>

                            </div>

                        </article>

                    </div>

                </div>


                <!-- CURRENT STATUS -->

                <div
                    class="current-status-panel"
                >

                    <span
                        class="current-status-panel__label"
                    >
                        CURRENT DATABASE STATUS
                    </span>


                    <div
                        class="current-status-value"
                        :class="
                            selectedStatus.className
                        "
                    >

                        <component
                            :is="
                                selectedStatus.icon
                            "
                            :size="20"
                            :stroke-width="2.2"
                        />


                        <span>
                            {{ form.nstp_status }}
                        </span>

                    </div>


                    <p>
                        This status is stored in
                        <strong>
                            users.nstp_status
                        </strong>.
                    </p>

                </div>

            </section>


            <!-- ============================================================
                 STATUS EDITOR
            ============================================================= -->

            <section
                class="status-editor"
            >

                <!-- HEADER -->

                <header
                    class="status-editor__header"
                >

                    <div
                        class="status-editor__heading"
                    >

                        <div
                            class="status-editor__heading-icon"
                        >

                            <ShieldCheck
                                :size="23"
                                :stroke-width="2.1"
                            />

                        </div>


                        <div>

                            <span>
                                EDIT STATUS
                            </span>


                            <h2>
                                Select Student Standing
                            </h2>


                            <p>
                                Choose one official NSTP status below.
                            </p>

                        </div>

                    </div>


                    <div
                        class="status-editor__note"
                    >

                        <Info
                            :size="16"
                            :stroke-width="2"
                        />


                        <span>
                            Saving will update this student in the database.
                        </span>

                    </div>

                </header>


                <!-- VALIDATION ERROR -->

                <div
                    v-if="
                        form.errors.nstp_status
                    "
                    class="status-error"
                >

                    <AlertTriangle
                        :size="18"
                        :stroke-width="2.2"
                    />


                    <span>
                        {{ form.errors.nstp_status }}
                    </span>

                </div>


                <!-- ========================================================
                     STATUS OPTIONS
                ========================================================= -->

                <div
                    class="status-choice-list"
                >

                    <!-- ACTIVE -->

                    <button
                        type="button"
                        class="
                            status-choice
                            status-choice--active
                        "
                        :class="{
                            'status-choice--selected':
                                form.nstp_status ===
                                'ACTIVE',
                        }"
                        :disabled="
                            !canEdit
                            ||
                            form.processing
                        "
                        @click="
                            selectStatus(
                                'ACTIVE'
                            )
                        "
                    >

                        <div
                            class="status-choice__selection"
                        >

                            <Check
                                v-if="
                                    form.nstp_status ===
                                    'ACTIVE'
                                "
                                :size="18"
                                :stroke-width="3"
                            />

                        </div>


                        <div
                            class="status-choice__icon"
                        >

                            <BadgeCheck
                                :size="31"
                                :stroke-width="1.8"
                            />

                        </div>


                        <div
                            class="status-choice__content"
                        >

                            <strong>
                                ACTIVE
                            </strong>


                            <small>
                                Student remains officially active.
                            </small>

                        </div>

                    </button>


                    <!-- DIVIDER -->

                    <div
                        class="status-choice-divider"
                    ></div>


                    <!-- WARNING -->

                    <button
                        type="button"
                        class="
                            status-choice
                            status-choice--warning
                        "
                        :class="{
                            'status-choice--selected':
                                form.nstp_status ===
                                'WARNING FOR DROPOUT',
                        }"
                        :disabled="
                            !canEdit
                            ||
                            form.processing
                        "
                        @click="
                            selectStatus(
                                'WARNING FOR DROPOUT'
                            )
                        "
                    >

                        <div
                            class="status-choice__selection"
                        >

                            <Check
                                v-if="
                                    form.nstp_status ===
                                    'WARNING FOR DROPOUT'
                                "
                                :size="18"
                                :stroke-width="3"
                            />

                        </div>


                        <div
                            class="status-choice__icon"
                        >

                            <AlertTriangle
                                :size="31"
                                :stroke-width="1.8"
                            />

                        </div>


                        <div
                            class="status-choice__content"
                        >

                            <strong>
                                WARNING FOR DROPOUT
                            </strong>


                            <small>
                                Student requires attendance attention.
                            </small>

                        </div>

                    </button>


                    <!-- DIVIDER -->

                    <div
                        class="status-choice-divider"
                    ></div>


                    <!-- DROPOUT -->

                    <button
                        type="button"
                        class="
                            status-choice
                            status-choice--dropout
                        "
                        :class="{
                            'status-choice--selected':
                                form.nstp_status ===
                                'DROPOUT',
                        }"
                        :disabled="
                            !canEdit
                            ||
                            form.processing
                        "
                        @click="
                            selectStatus(
                                'DROPOUT'
                            )
                        "
                    >

                        <div
                            class="status-choice__selection"
                        >

                            <Check
                                v-if="
                                    form.nstp_status ===
                                    'DROPOUT'
                                "
                                :size="18"
                                :stroke-width="3"
                            />

                        </div>


                        <div
                            class="status-choice__icon"
                        >

                            <UserRoundX
                                :size="31"
                                :stroke-width="1.8"
                            />

                        </div>


                        <div
                            class="status-choice__content"
                        >

                            <strong>
                                DROPOUT
                            </strong>


                            <small>
                                Student is officially marked as dropout.
                            </small>

                        </div>

                    </button>

                </div>


                <!-- ========================================================
                     CHANGE PREVIEW
                ========================================================= -->

                <div
                    class="status-change-preview"
                >

                    <span
                        class="status-change-preview__label"
                    >
                        STATUS CHANGE
                    </span>


                    <div
                        class="status-change-preview__flow"
                    >

                        <span
                            class="status-preview-pill"
                            :class="{
                                'status-preview-pill--active':
                                    originalStatus ===
                                    'ACTIVE',

                                'status-preview-pill--warning':
                                    originalStatus ===
                                    'WARNING FOR DROPOUT',

                                'status-preview-pill--dropout':
                                    originalStatus ===
                                    'DROPOUT',
                            }"
                        >
                            {{ originalStatus }}
                        </span>


                        <span
                            class="status-change-arrow"
                        >
                            →
                        </span>


                        <span
                            class="status-preview-pill"
                            :class="{
                                'status-preview-pill--active':
                                    form.nstp_status ===
                                    'ACTIVE',

                                'status-preview-pill--warning':
                                    form.nstp_status ===
                                    'WARNING FOR DROPOUT',

                                'status-preview-pill--dropout':
                                    form.nstp_status ===
                                    'DROPOUT',
                            }"
                        >
                            {{ form.nstp_status }}
                        </span>

                    </div>


                    <small
                        v-if="
                            !statusChanged
                        "
                    >
                        No changes selected.
                    </small>


                    <small
                        v-else
                        class="status-change-preview__changed"
                    >
                        Status has been changed and is ready to save.
                    </small>

                </div>


                <!-- ========================================================
                     ACTION BUTTONS
                ========================================================= -->

                <footer
                    class="status-editor__actions"
                >

                    <button
                        type="button"
                        class="cancel-button"
                        :disabled="
                            form.processing
                        "
                        @click="
                            cancelEdit
                        "
                    >

                        <ArrowLeft
                            :size="18"
                            :stroke-width="2.2"
                        />


                        <span>
                            CANCEL
                        </span>

                    </button>


                    <button
                        type="button"
                        class="save-button"
                        :disabled="
                            !canEdit
                            ||
                            !statusChanged
                            ||
                            form.processing
                        "
                        @click="
                            saveStatus
                        "
                    >

                        <Save
                            :size="18"
                            :stroke-width="2.2"
                        />


                        <span>
                            {{
                                form.processing
                                    ? 'SAVING...'
                                    : 'SAVE STATUS'
                            }}
                        </span>

                    </button>

                </footer>

            </section>

        </main>

    </Admin_IC_Layout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
|
| Only EditStudentAttendance.vue is styled.
|
| No usersLayout.vue styles are touched.
| No :deep() selectors.
|
*/


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.edit-student-attendance-page {

    --cream:
        #EFEBE2;

    --maroon:
        #54100F;

    --green:
        #58761C;

    --yellow:
        #FFBD36;

    --orange:
        #D99202;

    --dark-teal:
        #233E47;

    --near-black:
        #000D12;

    --white:
        #FFFFFF;

    --gray:
        #BEBEBE;

    --dark:
        #0D171B;


    width:
        100%;

    min-width:
        0;

    padding:
        30px
        34px
        52px;

    box-sizing:
        border-box;

}


/*
|--------------------------------------------------------------------------
| BACK TO LIST
|--------------------------------------------------------------------------
*/

.edit-back-navigation {

    width:
        100%;

    margin-bottom:
        16px;

    display:
        flex;

    align-items:
        center;

}


.edit-back-button {

    min-height:
        44px;

    padding:
        0
        16px;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.22
        );

    border-radius:
        11px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        9px;

    background:
        var(
            --white
        );

    color:
        var(
            --maroon
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        12px;

    font-weight:
        800;

    cursor:
        pointer;

    box-shadow:
        0
        3px
        9px
        rgba(
            13,
            23,
            27,
            0.035
        );

    transition:
        0.18s
        ease;

}


.edit-back-button:hover {

    border-color:
        var(
            --maroon
        );

    background:
        rgba(
            84,
            16,
            15,
            0.04
        );

    transform:
        translateX(
            -2px
        );

}


/*
|--------------------------------------------------------------------------
| PAGE HERO
|--------------------------------------------------------------------------
*/

.edit-page-hero {

    position:
        relative;

    width:
        100%;

    margin-bottom:
        18px;

    padding:
        22px
        24px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        16px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.13
        );

    border-radius:
        21px;

    background:
        linear-gradient(
            135deg,
            rgba(
                255,
                255,
                255,
                0.98
            ),
            rgba(
                239,
                235,
                226,
                0.93
            )
        );

    box-shadow:
        0
        12px
        32px
        rgba(
            13,
            23,
            27,
            0.06
        );

}


.edit-page-hero::after {

    content:
        '';

    position:
        absolute;

    right:
        -60px;

    bottom:
        -90px;

    width:
        190px;

    height:
        190px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            189,
            54,
            0.10
        );

    pointer-events:
        none;

}


.edit-page-hero__icon {

    position:
        relative;

    z-index:
        1;

    width:
        54px;

    height:
        54px;

    flex:
        0
        0
        54px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        16px;

    background:
        var(
            --maroon
        );

    color:
        var(
            --white
        );

    box-shadow:
        0
        9px
        20px
        rgba(
            84,
            16,
            15,
            0.18
        );

}


.edit-page-hero__content {

    position:
        relative;

    z-index:
        1;

    min-width:
        0;

    flex:
        1;

}


.edit-page-hero__content > span {

    display:
        block;

    margin-bottom:
        4px;

    color:
        var(
            --orange
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        1.3px;

}


.edit-page-hero__content h1 {

    margin:
        0;

    color:
        var(
            --dark-teal
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        clamp(
            28px,
            3vw,
            38px
        );

    line-height:
        1;

}


.edit-page-hero__content p {

    margin:
        7px
        0
        0;

    color:
        rgba(
            13,
            23,
            27,
            0.55
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        10px;

    line-height:
        1.45;

}


.edit-page-hero__security {

    position:
        relative;

    z-index:
        1;

    min-height:
        36px;

    padding:
        8px
        12px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    border-radius:
        999px;

    background:
        rgba(
            88,
            118,
            28,
            0.10
        );

    color:
        var(
            --green
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    font-weight:
        900;

    white-space:
        nowrap;

}


/*
|--------------------------------------------------------------------------
| PROFILE CARD
|--------------------------------------------------------------------------
*/

.student-profile-card {

    position:
        relative;

    width:
        100%;

    margin-bottom:
        20px;

    padding:
        26px;

    box-sizing:
        border-box;

    display:
        grid;

    grid-template-columns:
        132px
        minmax(
            0,
            1fr
        )
        300px;

    gap:
        24px;

    align-items:
        center;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.13
        );

    border-radius:
        24px;

    background:
        linear-gradient(
            135deg,
            var(
                --white
            ),
            rgba(
                239,
                235,
                226,
                0.94
            )
        );

    box-shadow:
        0
        15px
        40px
        rgba(
            13,
            23,
            27,
            0.065
        );

}


/*
|--------------------------------------------------------------------------
| PROFILE DECORATION
|--------------------------------------------------------------------------
*/

.profile-decoration {

    position:
        absolute;

    border-radius:
        50%;

    pointer-events:
        none;

}


.profile-decoration--one {

    top:
        -140px;

    right:
        120px;

    width:
        270px;

    height:
        270px;

    background:
        rgba(
            255,
            189,
            54,
            0.09
        );

}


.profile-decoration--two {

    right:
        -100px;

    bottom:
        -150px;

    width:
        280px;

    height:
        280px;

    background:
        rgba(
            88,
            118,
            28,
            0.07
        );

}


/*
|--------------------------------------------------------------------------
| PROFILE PHOTO
|--------------------------------------------------------------------------
*/

.student-photo-wrapper {

    position:
        relative;

    z-index:
        2;

    width:
        122px;

    height:
        122px;

}


.student-photo-ring {

    width:
        100%;

    height:
        100%;

    padding:
        5px;

    box-sizing:
        border-box;

    overflow:
        hidden;

    border:
        2px
        solid
        var(
            --maroon
        );

    border-radius:
        29px;

    background:
        var(
            --white
        );

    box-shadow:
        0
        10px
        26px
        rgba(
            84,
            16,
            15,
            0.16
        );

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

    border-radius:
        23px;

}


.student-photo-fallback {

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

    border-radius:
        23px;

    background:
        linear-gradient(
            145deg,
            var(
                --dark-teal
            ),
            var(
                --near-black
            )
        );

}


.student-photo-fallback span {

    color:
        var(
            --white
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        34px;

    font-weight:
        800;

}


.student-photo-badge {

    position:
        absolute;

    right:
        -7px;

    bottom:
        -6px;

    width:
        37px;

    height:
        37px;

    border:
        4px
        solid
        var(
            --cream
        );

    border-radius:
        12px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        var(
            --green
        );

    color:
        var(
            --white
        );

}


/*
|--------------------------------------------------------------------------
| STUDENT PRIMARY INFO
|--------------------------------------------------------------------------
*/

.student-primary-info {

    position:
        relative;

    z-index:
        2;

    min-width:
        0;

}


.student-profile-eyebrow {

    display:
        block;

    margin-bottom:
        5px;

    color:
        var(
            --orange
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        1.2px;

}


.student-primary-info h2 {

    margin:
        0;

    color:
        var(
            --dark-teal
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        clamp(
            24px,
            2.5vw,
            35px
        );

    line-height:
        1.05;

}


.student-id-row {

    margin-top:
        8px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    color:
        var(
            --maroon
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        12px;

    font-weight:
        800;

}


/*
|--------------------------------------------------------------------------
| STUDENT META
|--------------------------------------------------------------------------
*/

.student-profile-meta {

    margin-top:
        18px;

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        8px;

}


.student-meta-card {

    min-width:
        135px;

    min-height:
        50px;

    padding:
        8px
        11px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.11
        );

    border-radius:
        12px;

    background:
        rgba(
            255,
            255,
            255,
            0.72
        );

    color:
        var(
            --dark-teal
        );

}


.student-meta-card--component {

    color:
        var(
            --maroon
        );

}


.student-meta-card__icon {

    width:
        31px;

    height:
        31px;

    flex:
        0
        0
        31px;

    border-radius:
        9px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        rgba(
            35,
            62,
            71,
            0.07
        );

}


.student-meta-card > div:last-child {

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    gap:
        2px;

}


.student-meta-card small {

    color:
        rgba(
            13,
            23,
            27,
            0.45
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        7px;

    font-weight:
        900;

}


.student-meta-card strong {

    max-width:
        150px;

    color:
        currentColor;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9px;

    font-weight:
        900;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

    white-space:
        nowrap;

}


/*
|--------------------------------------------------------------------------
| CURRENT STATUS PANEL
|--------------------------------------------------------------------------
*/

.current-status-panel {

    position:
        relative;

    z-index:
        2;

    padding:
        18px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    border-radius:
        17px;

    background:
        rgba(
            255,
            255,
            255,
            0.82
        );

}


.current-status-panel__label {

    display:
        block;

    margin-bottom:
        9px;

    color:
        rgba(
            13,
            23,
            27,
            0.48
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        0.8px;

}


.current-status-value {

    min-height:
        44px;

    padding:
        9px
        13px;

    box-sizing:
        border-box;

    border-radius:
        13px;

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    color:
        var(
            --white
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        10px;

    font-weight:
        900;

}


.current-status-value.status-choice--active {

    background:
        var(
            --green
        );

}


.current-status-value.status-choice--warning {

    background:
        var(
            --maroon
        );

}


.current-status-value.status-choice--dropout {

    background:
        var(
            --near-black
        );

}


.current-status-panel p {

    margin:
        9px
        0
        0;

    color:
        rgba(
            13,
            23,
            27,
            0.45
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    line-height:
        1.45;

}


/*
|--------------------------------------------------------------------------
| STATUS EDITOR
|--------------------------------------------------------------------------
*/

.status-editor {

    width:
        100%;

    padding:
        26px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.13
        );

    border-radius:
        24px;

    background:
        var(
            --white
        );

    box-shadow:
        0
        15px
        40px
        rgba(
            13,
            23,
            27,
            0.065
        );

}


/*
|--------------------------------------------------------------------------
| STATUS EDITOR HEADER
|--------------------------------------------------------------------------
*/

.status-editor__header {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        22px;

    margin-bottom:
        22px;

}


.status-editor__heading {

    min-width:
        0;

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

}


.status-editor__heading-icon {

    width:
        46px;

    height:
        46px;

    flex:
        0
        0
        46px;

    border-radius:
        13px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        rgba(
            84,
            16,
            15,
            0.08
        );

    color:
        var(
            --maroon
        );

}


.status-editor__heading span {

    display:
        block;

    margin-bottom:
        2px;

    color:
        var(
            --orange
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1px;

}


.status-editor__heading h2 {

    margin:
        0;

    color:
        var(
            --dark-teal
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        22px;

}


.status-editor__heading p {

    margin:
        4px
        0
        0;

    color:
        rgba(
            13,
            23,
            27,
            0.50
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9px;

}


.status-editor__note {

    max-width:
        290px;

    padding:
        9px
        11px;

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

    border-radius:
        11px;

    background:
        rgba(
            255,
            189,
            54,
            0.11
        );

    color:
        var(
            --dark-teal
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    line-height:
        1.4;

}


/*
|--------------------------------------------------------------------------
| VALIDATION ERROR
|--------------------------------------------------------------------------
*/

.status-error {

    margin-bottom:
        15px;

    padding:
        11px
        13px;

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.20
        );

    border-radius:
        11px;

    background:
        rgba(
            84,
            16,
            15,
            0.06
        );

    color:
        var(
            --maroon
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        10px;

    font-weight:
        800;

}


/*
|--------------------------------------------------------------------------
| STATUS CHOICE LIST
|--------------------------------------------------------------------------
|
| Designed after the supplied reference:
|
| ACTIVE
| ----------------
| WARNING FOR DROPOUT
| ----------------
| DROPOUT
|
*/

.status-choice-list {

    width:
        100%;

    padding:
        18px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.10
        );

    border-radius:
        20px;

    background:
        var(
            --cream
        );

}


/*
|--------------------------------------------------------------------------
| STATUS CHOICE
|--------------------------------------------------------------------------
*/

.status-choice {

    position:
        relative;

    width:
        100%;

    min-height:
        108px;

    padding:
        18px
        65px
        18px
        25px;

    border:
        3px
        solid
        transparent;

    border-radius:
        999px;

    display:
        flex;

    align-items:
        center;

    gap:
        18px;

    overflow:
        hidden;

    color:
        var(
            --white
        );

    text-align:
        left;

    cursor:
        pointer;

    transition:
        transform
        0.18s
        ease,
        box-shadow
        0.18s
        ease,
        border-color
        0.18s
        ease;

}


.status-choice:hover:not(
    :disabled
) {

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        13px
        28px
        rgba(
            13,
            23,
            27,
            0.17
        );

}


.status-choice:disabled {

    cursor:
        not-allowed;

    opacity:
        0.68;

}


.status-choice--selected {

    border-color:
        var(
            --yellow
        );

    box-shadow:
        0
        0
        0
        4px
        rgba(
            255,
            189,
            54,
            0.18
        );

}


/*
|--------------------------------------------------------------------------
| STATUS COLORS
|--------------------------------------------------------------------------
*/

.status-choice--active {

    background:
        linear-gradient(
            135deg,
            var(
                --green
            ),
            #68862A
        );

}


.status-choice--warning {

    background:
        linear-gradient(
            135deg,
            var(
                --maroon
            ),
            #711A18
        );

}


.status-choice--dropout {

    background:
        linear-gradient(
            135deg,
            #000000,
            var(
                --near-black
            )
        );

}


/*
|--------------------------------------------------------------------------
| STATUS DIVIDER
|--------------------------------------------------------------------------
*/

.status-choice-divider {

    width:
        calc(
            100% + 36px
        );

    height:
        10px;

    margin:
        18px
        -18px;

    background:
        var(
            --maroon
        );

}


/*
|--------------------------------------------------------------------------
| STATUS SELECTION CHECK
|--------------------------------------------------------------------------
*/

.status-choice__selection {

    position:
        absolute;

    top:
        50%;

    right:
        23px;

    width:
        32px;

    height:
        32px;

    transform:
        translateY(
            -50%
        );

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            0.32
        );

    border-radius:
        50%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        rgba(
            255,
            255,
            255,
            0.10
        );

}


/*
|--------------------------------------------------------------------------
| STATUS ICON
|--------------------------------------------------------------------------
*/

.status-choice__icon {

    width:
        58px;

    height:
        58px;

    flex:
        0
        0
        58px;

    border-radius:
        18px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        rgba(
            255,
            255,
            255,
            0.12
        );

}


/*
|--------------------------------------------------------------------------
| STATUS CONTENT
|--------------------------------------------------------------------------
*/

.status-choice__content {

    min-width:
        0;

    flex:
        1;

}


.status-choice__content strong {

    display:
        block;

    color:
        var(
            --white
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        clamp(
            24px,
            3vw,
            39px
        );

    font-weight:
        500;

    line-height:
        1;

    letter-spacing:
        0.5px;

}


.status-choice__content small {

    display:
        block;

    margin-top:
        7px;

    color:
        rgba(
            255,
            255,
            255,
            0.70
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9px;

    line-height:
        1.4;

}


/*
|--------------------------------------------------------------------------
| STATUS PREVIEW
|--------------------------------------------------------------------------
*/

.status-change-preview {

    margin-top:
        18px;

    padding:
        15px
        16px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.10
        );

    border-radius:
        14px;

    background:
        rgba(
            239,
            235,
            226,
            0.52
        );

}


.status-change-preview__label {

    display:
        block;

    margin-bottom:
        9px;

    color:
        rgba(
            13,
            23,
            27,
            0.46
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        0.8px;

}


.status-change-preview__flow {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        10px;

}


.status-change-arrow {

    color:
        var(
            --gray
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        18px;

    font-weight:
        900;

}


.status-preview-pill {

    min-height:
        31px;

    padding:
        6px
        12px;

    border-radius:
        999px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    color:
        var(
            --white
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    font-weight:
        900;

}


.status-preview-pill--active {

    background:
        var(
            --green
        );

}


.status-preview-pill--warning {

    background:
        var(
            --maroon
        );

}


.status-preview-pill--dropout {

    background:
        var(
            --near-black
        );

}


.status-change-preview > small {

    display:
        block;

    margin-top:
        8px;

    color:
        rgba(
            13,
            23,
            27,
            0.45
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

}


.status-change-preview__changed {

    color:
        var(
            --green
        )
        !important;

    font-weight:
        800;

}


/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/

.status-editor__actions {

    margin-top:
        20px;

    padding-top:
        18px;

    border-top:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.10
        );

    display:
        flex;

    align-items:
        center;

    justify-content:
        flex-end;

    gap:
        10px;

}


.cancel-button,
.save-button {

    min-height:
        44px;

    padding:
        10px
        18px;

    border:
        0;

    border-radius:
        12px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        0.4px;

    cursor:
        pointer;

    transition:
        0.18s
        ease;

}


.cancel-button {

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.14
        );

    background:
        var(
            --cream
        );

    color:
        var(
            --dark-teal
        );

}


.cancel-button:hover:not(
    :disabled
) {

    background:
        var(
            --dark-teal
        );

    color:
        var(
            --white
        );

}


.save-button {

    min-width:
        160px;

    background:
        linear-gradient(
            135deg,
            var(
                --green
            ),
            #68862A
        );

    color:
        var(
            --white
        );

    box-shadow:
        0
        8px
        20px
        rgba(
            88,
            118,
            28,
            0.20
        );

}


.save-button:hover:not(
    :disabled
) {

    transform:
        translateY(
            -1px
        );

    box-shadow:
        0
        12px
        25px
        rgba(
            88,
            118,
            28,
            0.28
        );

}


.cancel-button:disabled,
.save-button:disabled {

    cursor:
        not-allowed;

    opacity:
        0.48;

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1050px
) {

    .student-profile-card {

        grid-template-columns:
            120px
            minmax(
                0,
                1fr
            );

    }


    .current-status-panel {

        grid-column:
            1 / -1;

    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - SMALL TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 760px
) {

    .edit-student-attendance-page {

        padding:
            18px;

    }


    .edit-page-hero {

        align-items:
            flex-start;

        flex-wrap:
            wrap;

    }


    .edit-page-hero__security {

        margin-left:
            70px;

    }


    .student-profile-card {

        grid-template-columns:
            1fr;

    }


    .student-photo-wrapper {

        width:
            104px;

        height:
            104px;

    }


    .status-editor__header {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .status-editor__note {

        max-width:
            none;

    }


    .status-choice {

        min-height:
            96px;

    }


    .status-choice__content strong {

        font-size:
            25px;

    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - MOBILE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 520px
) {

    .edit-student-attendance-page {

        padding:
            12px;

    }


    .edit-page-hero,
    .student-profile-card,
    .status-editor {

        padding:
            17px;

        border-radius:
            17px;

    }


    .edit-page-hero__security {

        margin-left:
            0;

    }


    .student-profile-meta {

        display:
            grid;

        grid-template-columns:
            1fr;

    }


    .student-meta-card {

        min-width:
            0;

    }


    .status-choice-list {

        padding:
            12px;

    }


    .status-choice-divider {

        width:
            calc(
                100% + 24px
            );

        margin:
            14px
            -12px;

        height:
            7px;

    }


    .status-choice {

        min-height:
            88px;

        padding:
            14px
            55px
            14px
            18px;

        gap:
            12px;

    }


    .status-choice__icon {

        width:
            45px;

        height:
            45px;

        flex-basis:
            45px;

        border-radius:
            14px;

    }


    .status-choice__content strong {

        font-size:
            18px;

    }


    .status-choice__content small {

        font-size:
            8px;

    }


    .status-choice__selection {

        right:
            14px;

        width:
            27px;

        height:
            27px;

    }


    .status-editor__actions {

        align-items:
            stretch;

        flex-direction:
            column-reverse;

    }


    .cancel-button,
    .save-button {

        width:
            100%;

    }

}

</style>