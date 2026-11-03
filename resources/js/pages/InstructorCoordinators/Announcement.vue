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
    AlertCircle,
    CalendarDays,
    Check,
    ChevronRight,
    CircleUserRound,
    FilePenLine,
    Filter,
    Inbox,
    Layers3,
    LockKeyhole,
    Mail,
    Megaphone,
    Pencil,
    RotateCcw,
    Search,
    Send,
    Sparkles,
    Trash2,
    UserRound,
    UsersRound,
} from 'lucide-vue-next';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
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

    announcements: {
        type: Array,
        default: () => [],
    },

    canManage: {
        type: Boolean,
        default: false,
    },

    authRole: {
        type: String,
        default: '',
    },

    activeComponent: {
        type: String,
        default: '',
    },

    componentOptions: {
        type: Array,
        default: () => [],
    },

});


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    title: '',

    announcement_date: '',

    description: '',

    component:
        props.activeComponent
        ||
        props.componentOptions?.[0]
        ||
        'ALL',

    recipient:
        'all_students',

    student_email: '',

});


/*
|--------------------------------------------------------------------------
| Local State
|--------------------------------------------------------------------------
*/

const activeFilter =
    ref(
        'all'
    );


const searchTerm =
    ref(
        ''
    );


const editingId =
    ref(
        null
    );


const deletingId =
    ref(
        null
    );


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
| Component
|--------------------------------------------------------------------------
*/

const componentLocked = computed(() => {

    return (
        currentRole.value !==
        'university-admin'
    );

});


const componentLabel = computed(() => {

    const value =
        String(
            props.activeComponent
            ??
            ''
        )
            .trim()
            .toUpperCase();


    return (
        value
        ||
        'ALL'
    );

});


/*
|--------------------------------------------------------------------------
| Editing
|--------------------------------------------------------------------------
*/

const isEditing = computed(() => {

    return (
        editingId.value !==
        null
    );

});


/*
|--------------------------------------------------------------------------
| Recipient
|--------------------------------------------------------------------------
*/

const requiresStudentEmail = computed(() => {

    return (
        form.recipient ===
        'specific_student'
    );

});


/*
|--------------------------------------------------------------------------
| Announcement Count
|--------------------------------------------------------------------------
*/

const announcementCount = computed(() => {

    return Array.isArray(
        props.announcements
    )
        ? props.announcements.length
        : 0;

});


/*
|--------------------------------------------------------------------------
| Filtered Announcements
|--------------------------------------------------------------------------
*/

const filteredAnnouncements = computed(() => {

    let data =
        Array.isArray(
            props.announcements
        )
            ? [
                ...props.announcements,
            ]
            : [];


    const search =
        searchTerm.value
            .trim()
            .toLowerCase();


    if (
        search
    ) {

        data =
            data.filter(
                announcement => {

                    const searchable =
                        [
                            announcement?.title,
                            announcement?.description,
                            announcement?.component,
                            announcement?.student_email,
                        ]
                            .filter(
                                Boolean
                            )
                            .join(
                                ' '
                            )
                            .toLowerCase();


                    return searchable.includes(
                        search
                    );

                }
            );

    }


    if (
        activeFilter.value ===
        'recent'
    ) {

        return data.sort(
            (
                a,
                b
            ) => {

                const dateA =
                    new Date(
                        a.announcement_date
                        ||
                        a.created_at
                        ||
                        0
                    );


                const dateB =
                    new Date(
                        b.announcement_date
                        ||
                        b.created_at
                        ||
                        0
                    );


                return (
                    dateB
                    -
                    dateA
                );

            }
        );

    }


    if (
        activeFilter.value ===
        'old'
    ) {

        return data.sort(
            (
                a,
                b
            ) => {

                const dateA =
                    new Date(
                        a.announcement_date
                        ||
                        a.created_at
                        ||
                        0
                    );


                const dateB =
                    new Date(
                        b.announcement_date
                        ||
                        b.created_at
                        ||
                        0
                    );


                return (
                    dateA
                    -
                    dateB
                );

            }
        );

    }


    return data;

});


/*
|--------------------------------------------------------------------------
| Reset Form
|--------------------------------------------------------------------------
*/

const resetForm = () => {

    form.reset();


    form.component =
        props.activeComponent
        ||
        props.componentOptions?.[0]
        ||
        'ALL';


    form.recipient =
        'all_students';


    form.student_email =
        '';


    editingId.value =
        null;


    form.clearErrors();

};


/*
|--------------------------------------------------------------------------
| Select Recipient
|--------------------------------------------------------------------------
*/

const selectRecipient = (
    recipient
) => {

    form.recipient =
        recipient;


    if (
        recipient ===
        'all_students'
    ) {

        form.student_email =
            '';

    }


    form.clearErrors(
        'recipient'
    );


    form.clearErrors(
        'student_email'
    );

};


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submitAnnouncement = () => {

    if (
        !props.canManage
    ) {

        return;

    }


    if (
        isEditing.value
    ) {

        form.put(
            `/instructor-coordinator/announcements/${editingId.value}`,
            {
                preserveScroll:
                    true,

                onSuccess: () => {

                    resetForm();

                },
            }
        );


        return;

    }


    form.post(
        '/instructor-coordinator/announcements',
        {
            preserveScroll:
                true,

            onSuccess: () => {

                resetForm();

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Date Input Helper
|--------------------------------------------------------------------------
*/

const normalizeDateForInput = (
    date
) => {

    if (
        !date
    ) {

        return '';

    }


    const raw =
        String(
            date
        );


    if (
        /^\d{4}-\d{2}-\d{2}$/.test(
            raw
        )
    ) {

        return raw;

    }


    const parsed =
        new Date(
            raw
        );


    if (
        Number.isNaN(
            parsed.getTime()
        )
    ) {

        return '';

    }


    const year =
        parsed.getFullYear();


    const month =
        String(
            parsed.getMonth() + 1
        )
            .padStart(
                2,
                '0'
            );


    const day =
        String(
            parsed.getDate()
        )
            .padStart(
                2,
                '0'
            );


    return `${year}-${month}-${day}`;

};


/*
|--------------------------------------------------------------------------
| Edit Announcement
|--------------------------------------------------------------------------
*/

const editAnnouncement = (
    announcement
) => {

    if (
        !props.canManage
        ||
        !announcement?.can_edit
    ) {

        return;

    }


    editingId.value =
        announcement.id;


    form.title =
        announcement.title
        ??
        '';


    form.announcement_date =
        normalizeDateForInput(
            announcement.announcement_date
        );


    form.description =
        announcement.description
        ??
        '';


    form.component =
        announcement.component
        ??
        props.activeComponent
        ??
        'ALL';


    form.recipient =
        announcement.recipient
        ??
        'all_students';


    form.student_email =
        announcement.student_email
        ??
        '';


    form.clearErrors();


    window.scrollTo({
        top:
            0,

        behavior:
            'smooth',
    });

};


/*
|--------------------------------------------------------------------------
| Delete Announcement
|--------------------------------------------------------------------------
*/

const deleteAnnouncement = (
    announcement
) => {

    if (
        !props.canManage
        ||
        !announcement?.can_delete
    ) {

        return;

    }


    if (
        !window.confirm(
            `Are you sure you want to delete "${announcement.title}"?`
        )
    ) {

        return;

    }


    deletingId.value =
        announcement.id;


    router.delete(
        `/instructor-coordinator/announcements/${announcement.id}`,
        {
            preserveScroll:
                true,

            onFinish: () => {

                deletingId.value =
                    null;

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Display Date
|--------------------------------------------------------------------------
*/

const formatDate = (
    date
) => {

    const normalized =
        normalizeDateForInput(
            date
        );


    if (
        !normalized
    ) {

        return date
            ? String(
                date
            )
            : '';

    }


    const [
        year,
        month,
        day,
    ] =
        normalized.split(
            '-'
        );


    const formatted =
        new Date(
            Number(
                year
            ),
            Number(
                month
            ) - 1,
            Number(
                day
            )
        );


    return formatted.toLocaleDateString(
        'en-US',
        {
            month:
                'short',

            day:
                'numeric',

            year:
                'numeric',
        }
    );

};


/*
|--------------------------------------------------------------------------
| Month
|--------------------------------------------------------------------------
*/

const getMonth = (
    date
) => {

    const normalized =
        normalizeDateForInput(
            date
        );


    if (
        !normalized
    ) {

        return '---';

    }


    const [
        year,
        month,
        day,
    ] =
        normalized.split(
            '-'
        );


    return new Date(
        Number(
            year
        ),
        Number(
            month
        ) - 1,
        Number(
            day
        )
    )
        .toLocaleDateString(
            'en-US',
            {
                month:
                    'short',
            }
        )
        .toUpperCase();

};


/*
|--------------------------------------------------------------------------
| Day
|--------------------------------------------------------------------------
*/

const getDay = (
    date
) => {

    const normalized =
        normalizeDateForInput(
            date
        );


    if (
        !normalized
    ) {

        return '--';

    }


    return normalized
        .split(
            '-'
        )[2];

};


/*
|--------------------------------------------------------------------------
| Recipient Helpers
|--------------------------------------------------------------------------
*/

const isSpecificRecipient = (
    announcement
) => {

    return (
        announcement?.recipient ===
        'specific_student'
    );

};


const recipientText = (
    announcement
) => {

    if (
        isSpecificRecipient(
            announcement
        )
    ) {

        return (
            announcement?.student_email
            ||
            'Specific Student'
        );

    }


    return 'All Students';

};

</script>


<template>

    <Head
        title="Announcements"
    />


    <Admin_IC_Layout
        :user="props.user"
        :role="currentRole"
    >

        <main class="dispatch-page">

            <!-- ============================================================
                 HEADER
            ============================================================= -->

            <section class="dispatch-masthead">

                <div class="masthead-mark">

                    <Megaphone
                        :size="28"
                        :stroke-width="2"
                    />

                </div>


                <div class="masthead-copy">

                    <div class="masthead-kicker">

                        <Sparkles
                            :size="15"
                            :stroke-width="2"
                        />

                        NSTP CAMPUS DISPATCH

                    </div>


                    <h1>
                        Announcements
                    </h1>


                    <p>
                        Official notices, reminders,
                        and student communications.
                    </p>

                </div>


                <div class="masthead-meta">

                    <div class="masthead-meta-item">

                        <span>
                            COMPONENT
                        </span>


                        <strong>
                            {{ componentLabel }}
                        </strong>

                    </div>


                    <div class="masthead-rule"></div>


                    <div class="masthead-meta-item">

                        <span>
                            ISSUES
                        </span>


                        <strong>
                            {{
                                String(
                                    announcementCount
                                )
                                    .padStart(
                                        2,
                                        '0'
                                    )
                            }}
                        </strong>

                    </div>

                </div>


                <div class="masthead-bottom-line">

                    <span>
                        SURIGAO DEL NORTE STATE UNIVERSITY
                    </span>


                    <span>
                        NSTP INFORMATION SERVICE
                    </span>

                </div>

            </section>


            <!-- ============================================================
                 MAIN GRID
            ============================================================= -->

            <div
                class="dispatch-grid"
                :class="{
                    'dispatch-grid--view-only':
                        !props.canManage,
                }"
            >

                <!-- ========================================================
                     COMPOSER
                ========================================================= -->

                <aside
                    v-if="
                        props.canManage
                    "
                    class="composer-sheet"
                >

                    <div class="composer-corner"></div>


                    <header class="composer-heading">

                        <div class="composer-heading-number">

                            {{
                                isEditing
                                    ? 'EDIT'
                                    : 'NEW'
                            }}

                        </div>


                        <div>

                            <span>
                                DISPATCH DESK
                            </span>


                            <h2>
                                {{
                                    isEditing
                                        ? 'Revise Notice'
                                        : 'Compose Notice'
                                }}
                            </h2>

                        </div>

                    </header>


                    <form
                        class="composer-form"
                        @submit.prevent="
                            submitAnnouncement
                        "
                    >

                        <!-- =================================================
                             NOTICE DETAILS
                        ================================================== -->

                        <div class="form-section">

                            <div class="form-section-marker">
                                01
                            </div>


                            <div class="form-section-body">

                                <div class="form-section-title">
                                    NOTICE DETAILS
                                </div>


                                <!-- TITLE -->

                                <div class="field-group">

                                    <label
                                        for="announcement-title"
                                    >
                                        Title
                                    </label>


                                    <div class="field-shell">

                                        <FilePenLine
                                            :size="19"
                                            :stroke-width="2"
                                        />


                                        <input
                                            id="announcement-title"
                                            v-model="
                                                form.title
                                            "
                                            type="text"
                                            maxlength="255"
                                            placeholder="Announcement title"
                                            required
                                        />

                                    </div>


                                    <span
                                        v-if="
                                            form.errors.title
                                        "
                                        class="field-error"
                                    >

                                        <AlertCircle
                                            :size="16"
                                        />

                                        {{ form.errors.title }}

                                    </span>

                                </div>


                                <!-- DATE -->

                                <div class="field-group">

                                    <label
                                        for="announcement-date"
                                    >
                                        Publish Date
                                    </label>


                                    <div class="field-shell">

                                        <CalendarDays
                                            :size="19"
                                            :stroke-width="2"
                                        />


                                        <input
                                            id="announcement-date"
                                            v-model="
                                                form.announcement_date
                                            "
                                            type="date"
                                            required
                                        />

                                    </div>


                                    <span
                                        v-if="
                                            form.errors.announcement_date
                                        "
                                        class="field-error"
                                    >

                                        <AlertCircle
                                            :size="16"
                                        />

                                        {{
                                            form.errors
                                                .announcement_date
                                        }}

                                    </span>

                                </div>


                                <!-- MESSAGE -->

                                <div class="field-group">

                                    <label
                                        for="announcement-description"
                                    >
                                        Message
                                    </label>


                                    <div
                                        class="
                                            field-shell
                                            field-shell--textarea
                                        "
                                    >

                                        <Megaphone
                                            :size="19"
                                            :stroke-width="2"
                                        />


                                        <textarea
                                            id="announcement-description"
                                            v-model="
                                                form.description
                                            "
                                            rows="5"
                                            placeholder="Write the official notice..."
                                            required
                                        ></textarea>

                                    </div>


                                    <span
                                        v-if="
                                            form.errors.description
                                        "
                                        class="field-error"
                                    >

                                        <AlertCircle
                                            :size="16"
                                        />

                                        {{
                                            form.errors.description
                                        }}

                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             DISTRIBUTION
                        ================================================== -->

                        <div class="form-section">

                            <div class="form-section-marker">
                                02
                            </div>


                            <div class="form-section-body">

                                <div class="form-section-title">
                                    DISTRIBUTION
                                </div>


                                <!-- COMPONENT -->

                                <div class="field-group">

                                    <label
                                        for="announcement-component"
                                    >
                                        NSTP Component
                                    </label>


                                    <select
                                        v-if="
                                            !componentLocked
                                        "
                                        id="announcement-component"
                                        v-model="
                                            form.component
                                        "
                                        class="native-select"
                                        required
                                    >

                                        <option
                                            v-for="
                                                component in
                                                props.componentOptions
                                            "
                                            :key="
                                                component
                                            "
                                            :value="
                                                component
                                            "
                                        >
                                            {{
                                                component ===
                                                'ALL'
                                                    ? 'ALL COMPONENTS'
                                                    : component
                                            }}
                                        </option>

                                    </select>


                                    <div
                                        v-else
                                        class="locked-field"
                                    >

                                        <div class="locked-field-icon">

                                            <LockKeyhole
                                                :size="19"
                                                :stroke-width="2"
                                            />

                                        </div>


                                        <div>

                                            <strong>
                                                {{ componentLabel }}
                                            </strong>


                                            <span>
                                                Assigned component
                                            </span>

                                        </div>

                                    </div>


                                    <span
                                        v-if="
                                            form.errors.component
                                        "
                                        class="field-error"
                                    >

                                        <AlertCircle
                                            :size="16"
                                        />

                                        {{
                                            form.errors.component
                                        }}

                                    </span>

                                </div>


                                <!-- RECIPIENT -->

                                <div class="field-group">

                                    <label>
                                        Recipient
                                    </label>


                                    <div class="recipient-grid">

                                        <button
                                            type="button"
                                            class="recipient-card"
                                            :class="{
                                                'recipient-card--selected':
                                                    form.recipient ===
                                                    'all_students',
                                            }"
                                            @click="
                                                selectRecipient(
                                                    'all_students'
                                                )
                                            "
                                        >

                                            <span class="recipient-card-check">

                                                <Check
                                                    v-if="
                                                        form.recipient ===
                                                        'all_students'
                                                    "
                                                    :size="15"
                                                    :stroke-width="3"
                                                />

                                            </span>


                                            <UsersRound
                                                :size="23"
                                                :stroke-width="2"
                                            />


                                            <div>

                                                <strong>
                                                    All Students
                                                </strong>


                                                <small>
                                                    General notice
                                                </small>

                                            </div>

                                        </button>


                                        <button
                                            type="button"
                                            class="recipient-card"
                                            :class="{
                                                'recipient-card--selected':
                                                    form.recipient ===
                                                    'specific_student',
                                            }"
                                            @click="
                                                selectRecipient(
                                                    'specific_student'
                                                )
                                            "
                                        >

                                            <span class="recipient-card-check">

                                                <Check
                                                    v-if="
                                                        form.recipient ===
                                                        'specific_student'
                                                    "
                                                    :size="15"
                                                    :stroke-width="3"
                                                />

                                            </span>


                                            <UserRound
                                                :size="23"
                                                :stroke-width="2"
                                            />


                                            <div>

                                                <strong>
                                                    Specific Student
                                                </strong>


                                                <small>
                                                    Individual notice
                                                </small>

                                            </div>

                                        </button>

                                    </div>

                                </div>


                                <!-- STUDENT EMAIL -->

                                <div
                                    v-if="
                                        requiresStudentEmail
                                    "
                                    class="
                                        field-group
                                        specific-email-field
                                    "
                                >

                                    <label
                                        for="student-email"
                                    >
                                        Student Email
                                    </label>


                                    <div class="field-shell">

                                        <Mail
                                            :size="19"
                                            :stroke-width="2"
                                        />


                                        <input
                                            id="student-email"
                                            v-model="
                                                form.student_email
                                            "
                                            type="email"
                                            placeholder="student@snsu.edu.ph"
                                            required
                                        />

                                    </div>


                                    <span
                                        v-if="
                                            form.errors.student_email
                                        "
                                        class="field-error"
                                    >

                                        <AlertCircle
                                            :size="16"
                                        />

                                        {{
                                            form.errors.student_email
                                        }}

                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             ACTIONS
                        ================================================== -->

                        <div class="composer-actions">

                            <button
                                type="submit"
                                class="dispatch-button"
                                :disabled="
                                    form.processing
                                "
                            >

                                <Send
                                    :size="19"
                                    :stroke-width="2.2"
                                />


                                <span>
                                    {{
                                        form.processing
                                            ? 'PROCESSING...'
                                            : isEditing
                                                ? 'SAVE REVISION'
                                                : 'ISSUE NOTICE'
                                    }}
                                </span>


                                <ChevronRight
                                    :size="19"
                                    :stroke-width="2.2"
                                />

                            </button>


                            <button
                                v-if="
                                    isEditing
                                "
                                type="button"
                                class="cancel-edit-button"
                                :disabled="
                                    form.processing
                                "
                                @click="
                                    resetForm
                                "
                            >

                                <RotateCcw
                                    :size="18"
                                    :stroke-width="2"
                                />

                                Cancel Editing

                            </button>

                        </div>

                    </form>

                </aside>


                <!-- ========================================================
                     ANNOUNCEMENT BOARD
                ========================================================= -->

                <section class="dispatch-board">

                    <header class="board-header">

                        <div class="board-title">

                            <span>
                                OFFICIAL BULLETIN
                            </span>


                            <h2>
                                Campus Dispatches
                            </h2>


                            <p>
                                Browse published NSTP notices
                                and student updates.
                            </p>

                        </div>


                        <div class="board-tools">

                            <label class="dispatch-search">

                                <Search
                                    :size="19"
                                    :stroke-width="2"
                                />


                                <input
                                    v-model="
                                        searchTerm
                                    "
                                    type="search"
                                    placeholder="Search notices..."
                                />

                            </label>


                            <div class="dispatch-filter">

                                <span class="dispatch-filter-icon">

                                    <Filter
                                        :size="16"
                                        :stroke-width="2"
                                    />

                                </span>


                                <button
                                    type="button"
                                    :class="{
                                        active:
                                            activeFilter ===
                                            'all',
                                    }"
                                    @click="
                                        activeFilter =
                                            'all'
                                    "
                                >
                                    ALL
                                </button>


                                <button
                                    type="button"
                                    :class="{
                                        active:
                                            activeFilter ===
                                            'recent',
                                    }"
                                    @click="
                                        activeFilter =
                                            'recent'
                                    "
                                >
                                    NEW
                                </button>


                                <button
                                    type="button"
                                    :class="{
                                        active:
                                            activeFilter ===
                                            'old',
                                    }"
                                    @click="
                                        activeFilter =
                                            'old'
                                    "
                                >
                                    OLD
                                </button>

                            </div>

                        </div>

                    </header>


                    <!-- ====================================================
                         LIST
                    ===================================================== -->

                    <div
                        v-if="
                            filteredAnnouncements.length >
                            0
                        "
                        class="dispatch-list"
                    >

                        <article
                            v-for="
                                (
                                    announcement,
                                    index
                                )
                                in
                                filteredAnnouncements
                            "
                            :key="
                                announcement.id
                            "
                            class="dispatch-sheet"
                        >

                            <div class="issue-number">

                                <small>
                                    ISSUE
                                </small>


                                <strong>
                                    {{
                                        String(
                                            index + 1
                                        )
                                            .padStart(
                                                2,
                                                '0'
                                            )
                                    }}
                                </strong>

                            </div>


                            <div class="date-stamp">

                                <span>
                                    {{
                                        getMonth(
                                            announcement
                                                .announcement_date
                                        )
                                    }}
                                </span>


                                <strong>
                                    {{
                                        getDay(
                                            announcement
                                                .announcement_date
                                        )
                                    }}
                                </strong>

                            </div>


                            <div class="dispatch-sheet-body">

                                <div class="dispatch-sheet-meta">

                                    <span class="component-label">

                                        <Layers3
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        {{
                                            announcement.component ===
                                            'ALL'
                                                ? 'ALL COMPONENTS'
                                                : announcement.component
                                        }}

                                    </span>


                                    <span class="recipient-label">

                                        <CircleUserRound
                                            v-if="
                                                isSpecificRecipient(
                                                    announcement
                                                )
                                            "
                                            :size="14"
                                            :stroke-width="2"
                                        />


                                        <UsersRound
                                            v-else
                                            :size="14"
                                            :stroke-width="2"
                                        />


                                        {{
                                            recipientText(
                                                announcement
                                            )
                                        }}

                                    </span>

                                </div>


                                <h3>
                                    {{ announcement.title }}
                                </h3>


                                <div class="dispatch-sheet-rule">

                                    <span></span>
                                    <span></span>

                                </div>


                                <p class="dispatch-message">
                                    {{ announcement.description }}
                                </p>


                                <footer class="dispatch-sheet-footer">

                                    <div class="published-date">

                                        <CalendarDays
                                            :size="17"
                                            :stroke-width="2"
                                        />


                                        <span>
                                            {{
                                                formatDate(
                                                    announcement
                                                        .announcement_date
                                                )
                                            }}
                                        </span>

                                    </div>


                                    <div class="dispatch-state">

                                        <span></span>

                                        PUBLISHED

                                    </div>


                                    <div
                                        v-if="
                                            announcement.can_edit
                                            ||
                                            announcement.can_delete
                                        "
                                        class="dispatch-actions"
                                    >

                                        <button
                                            v-if="
                                                announcement.can_edit
                                            "
                                            type="button"
                                            class="
                                                dispatch-action
                                                dispatch-action--edit
                                            "
                                            title="Edit announcement"
                                            aria-label="Edit announcement"
                                            @click="
                                                editAnnouncement(
                                                    announcement
                                                )
                                            "
                                        >

                                            <Pencil
                                                :size="19"
                                                :stroke-width="2"
                                            />

                                        </button>


                                        <button
                                            v-if="
                                                announcement.can_delete
                                            "
                                            type="button"
                                            class="
                                                dispatch-action
                                                dispatch-action--delete
                                            "
                                            title="Delete announcement"
                                            aria-label="Delete announcement"
                                            :disabled="
                                                deletingId ===
                                                announcement.id
                                            "
                                            @click="
                                                deleteAnnouncement(
                                                    announcement
                                                )
                                            "
                                        >

                                            <Trash2
                                                :size="19"
                                                :stroke-width="2"
                                            />

                                        </button>

                                    </div>

                                </footer>

                            </div>


                            <div class="sheet-fold"></div>

                        </article>

                    </div>


                    <!-- ====================================================
                         EMPTY
                    ===================================================== -->

                    <div
                        v-else
                        class="dispatch-empty"
                    >

                        <div class="dispatch-empty-icon">

                            <Inbox
                                :size="38"
                                :stroke-width="1.8"
                            />

                        </div>


                        <span>
                            NOTHING ON THE WIRE
                        </span>


                        <h3>
                            No announcements found
                        </h3>


                        <p>
                            {{
                                searchTerm
                                    ? 'Try another search term or change the current filter.'
                                    : 'New NSTP announcements will appear here once published.'
                            }}
                        </p>

                    </div>

                </section>

            </div>

        </main>

    </Admin_IC_Layout>

</template>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Libre+Baskerville:wght@400;700&display=swap'
);


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.dispatch-page {

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


    --font-display:
        "Libre Baskerville",
        Georgia,
        "Times New Roman",
        serif;


    --font-ui:
        "Atkinson Hyperlegible",
        "Segoe UI",
        Arial,
        sans-serif;


    width:
        100%;

    min-width:
        0;

    min-height:
        100%;


    padding:
        30px
        34px
        56px;


    box-sizing:
        border-box;


    background:
        var(--cream);


    color:
        var(--dark);


    font-family:
        var(--font-ui);


    font-size:
        16px;


    line-height:
        1.6;

}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.dispatch-masthead {

    position:
        relative;


    width:
        100%;


    min-height:
        180px;


    margin-bottom:
        26px;


    padding:
        30px
        32px
        45px;


    box-sizing:
        border-box;


    display:
        grid;


    grid-template-columns:
        74px
        minmax(
            0,
            1fr
        )
        auto;


    align-items:
        center;


    gap:
        22px;


    overflow:
        hidden;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.20
        );


    border-radius:
        6px
        24px
        6px
        24px;


    background:
        linear-gradient(
            105deg,
            #FFFFFF 0%,
            rgba(
                255,
                255,
                255,
                0.96
            ) 58%,
            rgba(
                255,
                189,
                54,
                0.10
            ) 100%
        );


    box-shadow:
        0
        16px
        36px
        rgba(
            13,
            23,
            27,
            0.07
        );

}


/*
|--------------------------------------------------------------------------
| HEADER TOP ACCENT
|--------------------------------------------------------------------------
*/

.dispatch-masthead::before {

    content:
        "";


    position:
        absolute;


    top:
        0;


    right:
        0;


    width:
        190px;


    height:
        9px;


    background:
        linear-gradient(
            90deg,
            var(--green),
            var(--yellow),
            var(--maroon)
        );

}


/*
|--------------------------------------------------------------------------
| WATERMARK REMOVED
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| The old:
|
| .dispatch-masthead::after {
|     content: "NSTP";
| }
|
| HAS BEEN COMPLETELY REMOVED.
|
| There is now no faded NSTP background text.
|
*/


/*
|--------------------------------------------------------------------------
| HEADER ICON
|--------------------------------------------------------------------------
*/

.masthead-mark {

    position:
        relative;


    z-index:
        2;


    width:
        66px;


    height:
        82px;


    display:
        flex;


    align-items:
        center;


    justify-content:
        center;


    border-radius:
        4px
        18px
        4px
        18px;


    background:
        var(--dark-teal);


    color:
        var(--white);


    box-shadow:
        8px
        8px
        0
        rgba(
            255,
            189,
            54,
            0.38
        );

}


/*
|--------------------------------------------------------------------------
| HEADER TEXT
|--------------------------------------------------------------------------
*/

.masthead-copy {

    position:
        relative;


    z-index:
        2;


    min-width:
        0;

}


.masthead-kicker {

    margin-bottom:
        7px;


    display:
        inline-flex;


    align-items:
        center;


    gap:
        7px;


    color:
        var(--maroon);


    font-size:
        13px;


    font-weight:
        700;


    letter-spacing:
        1.3px;

}


.masthead-copy h1 {

    margin:
        0;


    color:
        var(--dark-teal);


    font-family:
        var(--font-display);


    font-size:
        clamp(
            38px,
            4vw,
            54px
        );


    font-weight:
        700;


    line-height:
        1.1;


    letter-spacing:
        -0.8px;

}


.masthead-copy p {

    margin:
        12px
        0
        0;


    color:
        #3C4B50;


    font-size:
        16px;


    line-height:
        1.6;

}


/*
|--------------------------------------------------------------------------
| HEADER META
|--------------------------------------------------------------------------
*/

.masthead-meta {

    position:
        relative;


    z-index:
        2;


    min-width:
        260px;


    padding:
        16px
        19px;


    display:
        flex;


    align-items:
        stretch;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.18
        );


    border-radius:
        5px
        15px
        5px
        15px;


    background:
        rgba(
            239,
            235,
            226,
            0.82
        );

}


.masthead-meta-item {

    flex:
        1;


    display:
        flex;


    flex-direction:
        column;


    gap:
        5px;

}


.masthead-meta-item span {

    color:
        #526066;


    font-size:
        12px;


    font-weight:
        700;


    letter-spacing:
        0.7px;

}


.masthead-meta-item strong {

    color:
        var(--dark-teal);


    font-family:
        var(--font-display);


    font-size:
        20px;


    font-weight:
        700;

}


.masthead-rule {

    width:
        1px;


    margin:
        0
        18px;


    background:
        rgba(
            35,
            62,
            71,
            0.18
        );

}


/*
|--------------------------------------------------------------------------
| HEADER FOOTER
|--------------------------------------------------------------------------
*/

.masthead-bottom-line {

    position:
        absolute;


    z-index:
        2;


    right:
        32px;


    bottom:
        14px;


    left:
        32px;


    padding-top:
        9px;


    border-top:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.14
        );


    display:
        flex;


    justify-content:
        space-between;


    gap:
        20px;


    color:
        #5B666A;


    font-size:
        11px;


    font-weight:
        700;


    letter-spacing:
        0.8px;

}


/*
|--------------------------------------------------------------------------
| GRID
|--------------------------------------------------------------------------
*/

.dispatch-grid {

    width:
        100%;


    display:
        grid;


    grid-template-columns:
        minmax(
            360px,
            420px
        )
        minmax(
            0,
            1fr
        );


    gap:
        28px;


    align-items:
        start;

}


.dispatch-grid--view-only {

    grid-template-columns:
        minmax(
            0,
            1fr
        );

}


/*
|--------------------------------------------------------------------------
| COMPOSER
|--------------------------------------------------------------------------
*/

.composer-sheet {

    position:
        sticky;


    top:
        18px;


    overflow:
        hidden;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.18
        );


    border-radius:
        6px
        22px
        6px
        22px;


    background:
        var(--white);


    box-shadow:
        0
        18px
        40px
        rgba(
            13,
            23,
            27,
            0.08
        );

}


.composer-corner {

    position:
        absolute;


    top:
        0;


    right:
        0;


    width:
        48px;


    height:
        48px;


    overflow:
        hidden;


    pointer-events:
        none;

}


.composer-corner::before {

    content:
        "";


    position:
        absolute;


    top:
        -24px;


    right:
        -24px;


    width:
        48px;


    height:
        48px;


    transform:
        rotate(
            45deg
        );


    background:
        var(--yellow);

}


/*
|--------------------------------------------------------------------------
| COMPOSER HEADER
|--------------------------------------------------------------------------
*/

.composer-heading {

    min-height:
        105px;


    padding:
        21px
        22px
        20px
        25px;


    display:
        flex;


    align-items:
        center;


    gap:
        14px;


    background:
        var(--dark-teal);

}


.composer-heading-number {

    min-width:
        54px;


    height:
        34px;


    padding:
        0
        10px;


    border-radius:
        5px;


    display:
        inline-flex;


    align-items:
        center;


    justify-content:
        center;


    background:
        var(--yellow);


    color:
        var(--near-black);


    font-size:
        12px;


    font-weight:
        700;

}


.composer-heading span {

    display:
        block;


    margin-bottom:
        4px;


    color:
        var(--yellow);


    font-size:
        12px;


    font-weight:
        700;


    letter-spacing:
        0.7px;

}


.composer-heading h2 {

    margin:
        0;


    color:
        var(--white);


    font-family:
        var(--font-display);


    font-size:
        26px;

}


/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

.composer-form {

    padding:
        0
        21px
        24px;

}


.form-section {

    display:
        grid;


    grid-template-columns:
        49px
        minmax(
            0,
            1fr
        );

}


.form-section
+
.form-section {

    border-top:
        1px
        dashed
        rgba(
            35,
            62,
            71,
            0.18
        );

}


.form-section-marker {

    padding-top:
        27px;


    display:
        flex;


    justify-content:
        center;


    color:
        var(--dark-teal);


    font-family:
        var(--font-display);


    font-size:
        15px;


    font-weight:
        700;

}


.form-section-body {

    padding:
        24px
        0
        25px
        18px;

}


.form-section-title {

    margin-bottom:
        20px;


    color:
        var(--dark-teal);


    font-size:
        13px;


    font-weight:
        700;


    letter-spacing:
        1px;

}


/*
|--------------------------------------------------------------------------
| FIELDS
|--------------------------------------------------------------------------
*/

.field-group {

    display:
        flex;


    flex-direction:
        column;


    gap:
        8px;

}


.field-group
+
.field-group {

    margin-top:
        18px;

}


.field-group
>
label {

    color:
        var(--dark-teal);


    font-size:
        15px;


    font-weight:
        700;

}


.field-shell {

    min-height:
        52px;


    padding:
        0
        14px;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.25
        );


    border-radius:
        6px
        12px
        6px
        12px;


    display:
        flex;


    align-items:
        center;


    gap:
        10px;


    background:
        var(--white);


    color:
        var(--dark-teal);

}


.field-shell:focus-within,
.native-select:focus {

    border-color:
        var(--green);


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


.field-shell input,
.field-shell textarea {

    flex:
        1;


    min-width:
        0;


    width:
        100%;


    border:
        0;


    outline:
        0;


    background:
        transparent;


    color:
        var(--near-black);


    font-family:
        var(--font-ui);


    font-size:
        16px;


    line-height:
        1.5;

}


.field-shell input::placeholder,
.field-shell textarea::placeholder {

    color:
        #707B7F;


    opacity:
        1;

}


.field-shell--textarea {

    min-height:
        150px;


    padding-top:
        14px;


    align-items:
        flex-start;

}


.field-shell--textarea svg {

    margin-top:
        3px;

}


.field-shell textarea {

    min-height:
        120px;


    resize:
        vertical;

}


/*
|--------------------------------------------------------------------------
| SELECT
|--------------------------------------------------------------------------
*/

.native-select {

    width:
        100%;


    min-height:
        52px;


    padding:
        0
        14px;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.25
        );


    border-radius:
        6px
        12px
        6px
        12px;


    outline:
        0;


    background:
        var(--white);


    color:
        var(--dark-teal);


    font-family:
        var(--font-ui);


    font-size:
        16px;


    font-weight:
        700;

}


/*
|--------------------------------------------------------------------------
| LOCKED COMPONENT
|--------------------------------------------------------------------------
*/

.locked-field {

    min-height:
        62px;


    padding:
        10px
        12px;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.18
        );


    border-radius:
        6px
        12px
        6px
        12px;


    display:
        flex;


    align-items:
        center;


    gap:
        11px;


    background:
        rgba(
            35,
            62,
            71,
            0.07
        );

}


.locked-field-icon {

    width:
        39px;


    height:
        39px;


    flex:
        0
        0
        39px;


    border-radius:
        5px
        10px
        5px
        10px;


    display:
        flex;


    align-items:
        center;


    justify-content:
        center;


    background:
        var(--dark-teal);


    color:
        var(--white);

}


.locked-field
>
div:last-child {

    display:
        flex;


    flex-direction:
        column;


    gap:
        3px;

}


.locked-field strong {

    color:
        var(--dark-teal);


    font-size:
        16px;

}


.locked-field span {

    color:
        #56656A;


    font-size:
        13px;

}


/*
|--------------------------------------------------------------------------
| RECIPIENT
|--------------------------------------------------------------------------
*/

.recipient-grid {

    display:
        grid;


    grid-template-columns:
        repeat(
            2,
            minmax(
                0,
                1fr
            )
        );


    gap:
        10px;

}


.recipient-card {

    position:
        relative;


    min-height:
        112px;


    padding:
        16px
        14px;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.20
        );


    border-radius:
        6px
        14px
        6px
        14px;


    display:
        flex;


    flex-direction:
        column;


    align-items:
        flex-start;


    gap:
        9px;


    background:
        rgba(
            239,
            235,
            226,
            0.52
        );


    color:
        var(--dark-teal);


    text-align:
        left;


    cursor:
        pointer;


    transition:
        transform
        0.16s
        ease,
        border-color
        0.16s
        ease,
        background
        0.16s
        ease;

}


.recipient-card:hover {

    transform:
        translateY(
            -2px
        );


    border-color:
        var(--dark-teal);


    background:
        var(--white);

}


.recipient-card--selected {

    border-color:
        var(--green);


    background:
        rgba(
            88,
            118,
            28,
            0.10
        );

}


.recipient-card-check {

    position:
        absolute;


    top:
        11px;


    right:
        11px;


    width:
        25px;


    height:
        25px;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.25
        );


    border-radius:
        50%;


    display:
        flex;


    align-items:
        center;


    justify-content:
        center;

}


.recipient-card--selected
.recipient-card-check {

    border-color:
        var(--green);


    background:
        var(--green);


    color:
        var(--white);

}


.recipient-card div {

    display:
        flex;


    flex-direction:
        column;


    gap:
        3px;

}


.recipient-card strong {

    color:
        var(--dark-teal);


    font-size:
        15px;

}


.recipient-card small {

    color:
        #59686D;


    font-size:
        13px;

}


.specific-email-field {

    padding:
        14px;


    border-left:
        4px
        solid
        var(--orange);


    background:
        rgba(
            217,
            146,
            2,
            0.07
        );

}


/*
|--------------------------------------------------------------------------
| ERRORS
|--------------------------------------------------------------------------
*/

.field-error {

    display:
        flex;


    align-items:
        center;


    gap:
        6px;


    color:
        var(--maroon);


    font-size:
        14px;


    font-weight:
        700;

}


/*
|--------------------------------------------------------------------------
| COMPOSER ACTIONS
|--------------------------------------------------------------------------
*/

.composer-actions {

    padding:
        22px
        0
        0;


    border-top:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.11
        );


    display:
        flex;


    flex-direction:
        column;


    gap:
        9px;

}


.dispatch-button {

    width:
        100%;


    min-height:
        56px;


    padding:
        12px
        16px;


    border:
        0;


    border-radius:
        6px
        14px
        6px
        14px;


    display:
        grid;


    grid-template-columns:
        24px
        minmax(
            0,
            1fr
        )
        22px;


    align-items:
        center;


    gap:
        8px;


    background:
        var(--green);


    color:
        var(--white);


    font-family:
        var(--font-ui);


    font-size:
        15px;


    font-weight:
        700;


    cursor:
        pointer;


    box-shadow:
        0
        8px
        18px
        rgba(
            88,
            118,
            28,
            0.22
        );

}


.dispatch-button:hover:not(:disabled) {

    transform:
        translateY(
            -1px
        );


    box-shadow:
        0
        12px
        22px
        rgba(
            88,
            118,
            28,
            0.30
        );

}


.dispatch-button:disabled {

    cursor:
        not-allowed;


    opacity:
        0.55;

}


.cancel-edit-button {

    min-height:
        46px;


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
        6px
        11px
        6px
        11px;


    display:
        inline-flex;


    align-items:
        center;


    justify-content:
        center;


    gap:
        8px;


    background:
        transparent;


    color:
        var(--maroon);


    font-family:
        var(--font-ui);


    font-size:
        14px;


    font-weight:
        700;


    cursor:
        pointer;

}


/*
|--------------------------------------------------------------------------
| BOARD
|--------------------------------------------------------------------------
*/

.dispatch-board {

    min-width:
        0;

}


.board-header {

    min-height:
        125px;


    margin-bottom:
        18px;


    padding:
        0
        0
        18px;


    border-bottom:
        3px
        double
        rgba(
            35,
            62,
            71,
            0.35
        );


    display:
        flex;


    align-items:
        flex-end;


    justify-content:
        space-between;


    gap:
        22px;

}


.board-title span {

    display:
        block;


    margin-bottom:
        5px;


    color:
        var(--maroon);


    font-size:
        13px;


    font-weight:
        700;


    letter-spacing:
        1px;

}


.board-title h2 {

    margin:
        0;


    color:
        var(--dark-teal);


    font-family:
        var(--font-display);


    font-size:
        34px;


    line-height:
        1.15;

}


.board-title p {

    margin:
        8px
        0
        0;


    color:
        #4E5C61;


    font-size:
        15px;

}


/*
|--------------------------------------------------------------------------
| BOARD TOOLS
|--------------------------------------------------------------------------
*/

.board-tools {

    display:
        flex;


    align-items:
        center;


    gap:
        10px;

}


.dispatch-search {

    width:
        245px;


    height:
        48px;


    padding:
        0
        13px;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.22
        );


    border-radius:
        6px
        11px
        6px
        11px;


    display:
        flex;


    align-items:
        center;


    gap:
        8px;


    background:
        var(--white);


    color:
        var(--dark-teal);

}


.dispatch-search:focus-within {

    border-color:
        var(--green);


    box-shadow:
        0
        0
        0
        3px
        rgba(
            88,
            118,
            28,
            0.08
        );

}


.dispatch-search input {

    min-width:
        0;


    width:
        100%;


    border:
        0;


    outline:
        0;


    background:
        transparent;


    color:
        var(--dark);


    font-family:
        var(--font-ui);


    font-size:
        15px;

}


.dispatch-search input::placeholder {

    color:
        #68777B;

}


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

.dispatch-filter {

    min-height:
        48px;


    padding:
        4px;


    display:
        flex;


    align-items:
        center;


    gap:
        3px;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.18
        );


    border-radius:
        6px
        11px
        6px
        11px;


    background:
        rgba(
            255,
            255,
            255,
            0.70
        );

}


.dispatch-filter-icon {

    width:
        34px;


    display:
        flex;


    align-items:
        center;


    justify-content:
        center;


    color:
        var(--dark-teal);

}


.dispatch-filter button {

    min-width:
        56px;


    height:
        38px;


    padding:
        0
        10px;


    border:
        0;


    border-radius:
        5px
        8px
        5px
        8px;


    background:
        transparent;


    color:
        var(--dark-teal);


    font-family:
        var(--font-ui);


    font-size:
        13px;


    font-weight:
        700;


    cursor:
        pointer;

}


.dispatch-filter button.active {

    background:
        var(--dark-teal);


    color:
        var(--white);

}


/*
|--------------------------------------------------------------------------
| ANNOUNCEMENT LIST
|--------------------------------------------------------------------------
*/

.dispatch-list {

    display:
        flex;


    flex-direction:
        column;


    gap:
        18px;

}


/*
|--------------------------------------------------------------------------
| ANNOUNCEMENT CARD
|--------------------------------------------------------------------------
*/

.dispatch-sheet {

    position:
        relative;


    min-height:
        210px;


    display:
        grid;


    grid-template-columns:
        68px
        86px
        minmax(
            0,
            1fr
        );


    overflow:
        hidden;


    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.18
        );


    border-radius:
        6px
        19px
        6px
        19px;


    background:
        var(--white);


    box-shadow:
        0
        9px
        25px
        rgba(
            13,
            23,
            27,
            0.055
        );


    transition:
        transform
        0.18s
        ease,
        box-shadow
        0.18s
        ease;

}


.dispatch-sheet:hover {

    transform:
        translateY(
            -2px
        );


    box-shadow:
        0
        14px
        32px
        rgba(
            13,
            23,
            27,
            0.09
        );

}


/*
|--------------------------------------------------------------------------
| ISSUE NUMBER
|--------------------------------------------------------------------------
*/

.issue-number {

    padding-top:
        23px;


    border-right:
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


    flex-direction:
        column;


    align-items:
        center;


    background:
        var(--dark-teal);

}


.issue-number small {

    color:
        rgba(
            255,
            255,
            255,
            0.75
        );


    font-size:
        11px;


    font-weight:
        700;


    letter-spacing:
        0.6px;

}


.issue-number strong {

    margin-top:
        5px;


    color:
        var(--yellow);


    font-family:
        var(--font-display);


    font-size:
        24px;

}


/*
|--------------------------------------------------------------------------
| DATE
|--------------------------------------------------------------------------
*/

.date-stamp {

    padding-top:
        24px;


    border-right:
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


    flex-direction:
        column;


    align-items:
        center;

}


.date-stamp span {

    color:
        var(--maroon);


    font-size:
        13px;


    font-weight:
        700;

}


.date-stamp strong {

    margin-top:
        3px;


    color:
        var(--dark-teal);


    font-family:
        var(--font-display);


    font-size:
        35px;


    line-height:
        1;

}


/*
|--------------------------------------------------------------------------
| ANNOUNCEMENT CONTENT
|--------------------------------------------------------------------------
*/

.dispatch-sheet-body {

    min-width:
        0;


    padding:
        22px
        25px
        19px;

}


.dispatch-sheet-meta {

    display:
        flex;


    flex-wrap:
        wrap;


    gap:
        8px;

}


.component-label,
.recipient-label {

    min-height:
        30px;


    padding:
        6px
        11px;


    border-radius:
        999px;


    display:
        inline-flex;


    align-items:
        center;


    gap:
        6px;


    font-size:
        13px;


    font-weight:
        700;

}


.component-label {

    background:
        rgba(
            88,
            118,
            28,
            0.13
        );


    color:
        #425A16;

}


.recipient-label {

    background:
        rgba(
            35,
            62,
            71,
            0.10
        );


    color:
        var(--dark-teal);

}


.dispatch-sheet h3 {

    margin:
        14px
        0
        0;


    color:
        var(--dark-teal);


    font-family:
        var(--font-display);


    font-size:
        27px;


    line-height:
        1.3;


    overflow-wrap:
        anywhere;

}


.dispatch-sheet-rule {

    margin-top:
        13px;


    display:
        flex;


    align-items:
        center;


    gap:
        6px;

}


.dispatch-sheet-rule
span:first-child {

    width:
        42px;


    height:
        4px;


    background:
        var(--maroon);

}


.dispatch-sheet-rule
span:last-child {

    flex:
        1;


    height:
        1px;


    background:
        rgba(
            35,
            62,
            71,
            0.16
        );

}


.dispatch-message {

    max-width:
        900px;


    margin:
        15px
        0
        0;


    color:
        #334449;


    font-size:
        16px;


    line-height:
        1.75;


    white-space:
        pre-line;


    overflow-wrap:
        anywhere;

}


/*
|--------------------------------------------------------------------------
| CARD FOOTER
|--------------------------------------------------------------------------
*/

.dispatch-sheet-footer {

    margin-top:
        20px;


    padding-top:
        14px;


    border-top:
        1px
        dashed
        rgba(
            35,
            62,
            71,
            0.16
        );


    display:
        flex;


    align-items:
        center;


    gap:
        15px;

}


.published-date {

    display:
        inline-flex;


    align-items:
        center;


    gap:
        7px;


    color:
        var(--maroon);


    font-size:
        14px;


    font-weight:
        700;

}


.dispatch-state {

    display:
        inline-flex;


    align-items:
        center;


    gap:
        6px;


    color:
        var(--dark-teal);


    font-size:
        12px;


    font-weight:
        700;

}


.dispatch-state
>
span {

    width:
        9px;


    height:
        9px;


    border-radius:
        50%;


    background:
        var(--green);

}


/*
|--------------------------------------------------------------------------
| CARD ACTIONS
|--------------------------------------------------------------------------
*/

.dispatch-actions {

    margin-left:
        auto;


    display:
        flex;


    gap:
        7px;

}


.dispatch-action {

    width:
        42px;


    height:
        42px;


    padding:
        0;


    border:
        1px
        solid
        transparent;


    border-radius:
        6px
        10px
        6px
        10px;


    display:
        inline-flex;


    align-items:
        center;


    justify-content:
        center;


    cursor:
        pointer;


    transition:
        transform
        0.15s
        ease,
        background
        0.15s
        ease,
        color
        0.15s
        ease;

}


.dispatch-action:hover:not(:disabled) {

    transform:
        translateY(
            -2px
        );

}


.dispatch-action--edit {

    border-color:
        rgba(
            35,
            62,
            71,
            0.18
        );


    background:
        rgba(
            35,
            62,
            71,
            0.08
        );


    color:
        var(--dark-teal);

}


.dispatch-action--edit:hover {

    background:
        var(--dark-teal);


    color:
        var(--white);

}


.dispatch-action--delete {

    border-color:
        rgba(
            84,
            16,
            15,
            0.18
        );


    background:
        rgba(
            84,
            16,
            15,
            0.07
        );


    color:
        var(--maroon);

}


.dispatch-action--delete:hover:not(:disabled) {

    background:
        var(--maroon);


    color:
        var(--white);

}


.dispatch-action:disabled {

    cursor:
        not-allowed;


    opacity:
        0.45;

}


/*
|--------------------------------------------------------------------------
| PAPER FOLD
|--------------------------------------------------------------------------
*/

.sheet-fold {

    position:
        absolute;


    right:
        0;


    bottom:
        0;


    width:
        34px;


    height:
        34px;


    overflow:
        hidden;


    pointer-events:
        none;

}


.sheet-fold::before {

    content:
        "";


    position:
        absolute;


    right:
        -17px;


    bottom:
        -17px;


    width:
        34px;


    height:
        34px;


    transform:
        rotate(
            45deg
        );


    background:
        rgba(
            255,
            189,
            54,
            0.60
        );

}


/*
|--------------------------------------------------------------------------
| EMPTY
|--------------------------------------------------------------------------
*/

.dispatch-empty {

    min-height:
        380px;


    padding:
        38px;


    border:
        1px
        dashed
        rgba(
            35,
            62,
            71,
            0.28
        );


    border-radius:
        6px
        20px
        6px
        20px;


    display:
        flex;


    flex-direction:
        column;


    align-items:
        center;


    justify-content:
        center;


    background:
        rgba(
            255,
            255,
            255,
            0.64
        );


    text-align:
        center;

}


.dispatch-empty-icon {

    width:
        72px;


    height:
        72px;


    margin-bottom:
        17px;


    border-radius:
        5px
        18px
        5px
        18px;


    display:
        flex;


    align-items:
        center;


    justify-content:
        center;


    background:
        var(--dark-teal);


    color:
        var(--white);

}


.dispatch-empty
>
span {

    color:
        var(--maroon);


    font-size:
        13px;


    font-weight:
        700;


    letter-spacing:
        1px;

}


.dispatch-empty h3 {

    margin:
        9px
        0
        0;


    color:
        var(--dark-teal);


    font-family:
        var(--font-display);


    font-size:
        29px;

}


.dispatch-empty p {

    max-width:
        420px;


    margin:
        11px
        0
        0;


    color:
        #445358;


    font-size:
        16px;

}


/*
|--------------------------------------------------------------------------
| ACCESSIBILITY
|--------------------------------------------------------------------------
*/

button:focus-visible,
input:focus-visible,
textarea:focus-visible,
select:focus-visible {

    outline:
        4px
        solid
        rgba(
            255,
            189,
            54,
            0.55
        );


    outline-offset:
        2px;

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1180px
) {

    .dispatch-page {

        padding:
            26px
            26px
            48px;

    }


    .dispatch-grid {

        grid-template-columns:
            minmax(
                340px,
                380px
            )
            minmax(
                0,
                1fr
            );


        gap:
            22px;

    }


    .masthead-meta {

        min-width:
            230px;

    }


    .board-header {

        align-items:
            flex-start;


        flex-direction:
            column;

    }


    .board-tools {

        width:
            100%;

    }


    .dispatch-search {

        flex:
            1;

    }

}


@media (
    max-width: 930px
) {

    .dispatch-masthead {

        grid-template-columns:
            68px
            minmax(
                0,
                1fr
            );

    }


    .masthead-meta {

        grid-column:
            1
            /
            -1;


        width:
            100%;


        box-sizing:
            border-box;

    }


    .dispatch-grid {

        grid-template-columns:
            1fr;

    }


    .composer-sheet {

        position:
            static;

    }

}


@media (
    max-width: 700px
) {

    .dispatch-page {

        padding:
            18px;

    }


    .dispatch-masthead {

        padding:
            22px
            22px
            48px;


        grid-template-columns:
            58px
            minmax(
                0,
                1fr
            );

    }


    .masthead-mark {

        width:
            54px;


        height:
            68px;

    }


    .masthead-copy h1 {

        font-size:
            38px;

    }


    .masthead-bottom-line {

        right:
            22px;


        left:
            22px;


        font-size:
            10px;

    }


    .board-tools {

        align-items:
            stretch;


        flex-direction:
            column;

    }


    .dispatch-search,
    .dispatch-filter {

        width:
            100%;

    }


    .dispatch-filter button {

        flex:
            1;

    }


    .dispatch-sheet {

        grid-template-columns:
            58px
            70px
            minmax(
                0,
                1fr
            );

    }


    .dispatch-sheet-body {

        padding:
            20px
            17px
            17px;

    }

}


@media (
    max-width: 520px
) {

    .dispatch-page {

        padding:
            12px;

    }


    .dispatch-masthead {

        min-height:
            0;


        grid-template-columns:
            1fr;


        padding:
            18px
            18px
            48px;

    }


    .masthead-mark {

        width:
            54px;


        height:
            54px;

    }


    .masthead-copy h1 {

        font-size:
            34px;

    }


    .masthead-copy p {

        font-size:
            15px;

    }


    .masthead-meta {

        grid-column:
            auto;


        min-width:
            0;

    }


    .masthead-bottom-line
    span:last-child {

        display:
            none;

    }


    .recipient-grid {

        grid-template-columns:
            1fr;

    }


    .dispatch-sheet {

        display:
            block;


        padding-top:
            60px;

    }


    .issue-number {

        position:
            absolute;


        top:
            0;


        left:
            0;


        width:
            50%;


        height:
            50px;


        padding:
            0
            15px;


        box-sizing:
            border-box;


        border:
            0;


        flex-direction:
            row;


        justify-content:
            flex-start;


        gap:
            7px;

    }


    .issue-number strong {

        margin:
            0;


        font-size:
            18px;

    }


    .date-stamp {

        position:
            absolute;


        top:
            0;


        right:
            0;


        width:
            50%;


        height:
            50px;


        padding:
            0
            15px;


        box-sizing:
            border-box;


        border:
            0;


        flex-direction:
            row;


        justify-content:
            flex-end;


        gap:
            7px;


        background:
            rgba(
                239,
                235,
                226,
                0.90
            );

    }


    .date-stamp strong {

        margin:
            0;


        font-size:
            21px;

    }


    .dispatch-sheet h3 {

        font-size:
            24px;

    }


    .dispatch-sheet-footer {

        align-items:
            flex-start;


        flex-wrap:
            wrap;

    }


    .dispatch-actions {

        width:
            100%;


        margin-left:
            0;


        justify-content:
            flex-end;

    }

}

</style>