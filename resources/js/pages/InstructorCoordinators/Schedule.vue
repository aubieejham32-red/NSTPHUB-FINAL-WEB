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
    CalendarPlus,
    CheckCircle2,
    ChevronRight,
    Clock3,
    Filter,
    Inbox,
    Layers3,
    LockKeyhole,
    MapPin,
    Pencil,
    RotateCcw,
    Search,
    Send,
    Sparkles,
    Trash2,
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

    schedules: {
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
    schedule_date: '',
    start_time: '',
    end_time: '',
    location: '',

    component:
        props.activeComponent
        ||
        props.componentOptions?.[0]
        ||
        'ALL',
});


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const activeFilter = ref('all');

const searchTerm = ref('');

const editingId = ref(null);

const deletingId = ref(null);


/*
|--------------------------------------------------------------------------
| Computed
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


const isEditing = computed(() => {
    return editingId.value !== null;
});


const isUniversityAdmin = computed(() => {
    return (
        currentRole.value ===
        'university-admin'
    );
});


const componentLabel = computed(() => {
    const value =
        String(
            props.activeComponent ?? ''
        )
            .trim()
            .toUpperCase();

    return value || 'ALL';
});


const scheduleCount = computed(() => {
    return Array.isArray(props.schedules)
        ? props.schedules.length
        : 0;
});


/*
|--------------------------------------------------------------------------
| Filtered Schedules
|--------------------------------------------------------------------------
*/

const filteredSchedules = computed(() => {
    let data =
        Array.isArray(props.schedules)
            ? [...props.schedules]
            : [];

    const query =
        searchTerm.value
            .trim()
            .toLowerCase();

    if (query) {
        data =
            data.filter(
                schedule => {
                    const searchable = [
                        schedule?.title,
                        schedule?.schedule_date,
                        schedule?.start_time,
                        schedule?.end_time,
                        schedule?.location,
                        schedule?.component,
                    ]
                        .filter(Boolean)
                        .join(' ')
                        .toLowerCase();

                    return searchable.includes(
                        query
                    );
                }
            );
    }

    if (
        activeFilter.value ===
        'recent'
    ) {
        return data.sort(
            (a, b) => {
                const dateA =
                    new Date(
                        `${a.schedule_date || '1970-01-01'}T${a.start_time || '00:00'}`
                    );

                const dateB =
                    new Date(
                        `${b.schedule_date || '1970-01-01'}T${b.start_time || '00:00'}`
                    );

                return dateB - dateA;
            }
        );
    }

    if (
        activeFilter.value ===
        'old'
    ) {
        return data.sort(
            (a, b) => {
                const dateA =
                    new Date(
                        `${a.schedule_date || '1970-01-01'}T${a.start_time || '00:00'}`
                    );

                const dateB =
                    new Date(
                        `${b.schedule_date || '1970-01-01'}T${b.start_time || '00:00'}`
                    );

                return dateA - dateB;
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

    editingId.value = null;

    form.clearErrors();
};


/*
|--------------------------------------------------------------------------
| Submit Schedule
|--------------------------------------------------------------------------
*/

const submitSchedule = () => {
    if (!props.canManage) {
        return;
    }

    if (isEditing.value) {
        form.put(
            `/instructor-coordinator/schedules/${editingId.value}`,
            {
                preserveScroll: true,

                onSuccess: () => {
                    resetForm();
                },
            }
        );

        return;
    }

    form.post(
        '/instructor-coordinator/schedules',
        {
            preserveScroll: true,

            onSuccess: () => {
                resetForm();
            },
        }
    );
};


/*
|--------------------------------------------------------------------------
| Edit Schedule
|--------------------------------------------------------------------------
*/

const editSchedule = (
    schedule
) => {
    if (
        !props.canManage
        ||
        !schedule?.can_edit
    ) {
        return;
    }

    editingId.value =
        schedule.id;

    form.title =
        schedule.title ?? '';

    form.schedule_date =
        normalizeDateForInput(
            schedule.schedule_date
        );

    form.start_time =
        normalizeTimeForInput(
            schedule.start_time
        );

    form.end_time =
        normalizeTimeForInput(
            schedule.end_time
        );

    form.location =
        schedule.location ?? '';

    form.component =
        schedule.component
        ??
        props.activeComponent
        ??
        'ALL';

    form.clearErrors();

    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
};


/*
|--------------------------------------------------------------------------
| Delete Schedule
|--------------------------------------------------------------------------
*/

const deleteSchedule = (
    schedule
) => {
    if (
        !props.canManage
        ||
        !schedule?.can_delete
    ) {
        return;
    }

    const confirmed =
        window.confirm(
            `Are you sure you want to delete "${schedule.title}"?`
        );

    if (!confirmed) {
        return;
    }

    deletingId.value =
        schedule.id;

    router.delete(
        `/instructor-coordinator/schedules/${schedule.id}`,
        {
            preserveScroll: true,

            onFinish: () => {
                deletingId.value =
                    null;
            },
        }
    );
};


/*
|--------------------------------------------------------------------------
| Date Helpers
|--------------------------------------------------------------------------
*/

const normalizeDateForInput = (
    date
) => {
    if (!date) {
        return '';
    }

    const rawDate =
        String(date);

    if (
        /^\d{4}-\d{2}-\d{2}$/.test(
            rawDate
        )
    ) {
        return rawDate;
    }

    const parsed =
        new Date(rawDate);

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
        ).padStart(
            2,
            '0'
        );

    const day =
        String(
            parsed.getDate()
        ).padStart(
            2,
            '0'
        );

    return `${year}-${month}-${day}`;
};


const normalizeTimeForInput = (
    time
) => {
    if (!time) {
        return '';
    }

    const value =
        String(time);

    const match =
        value.match(
            /^(\d{2}):(\d{2})/
        );

    if (!match) {
        return '';
    }

    return `${match[1]}:${match[2]}`;
};


const formatDate = (
    date
) => {
    const normalized =
        normalizeDateForInput(
            date
        );

    if (!normalized) {
        return '';
    }

    const [
        year,
        month,
        day,
    ] =
        normalized.split('-');

    const parsed =
        new Date(
            Number(year),
            Number(month) - 1,
            Number(day)
        );

    return parsed.toLocaleDateString(
        'en-US',
        {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        }
    );
};


const formatDay = (
    date
) => {
    const normalized =
        normalizeDateForInput(
            date
        );

    if (!normalized) {
        return '';
    }

    const [
        year,
        month,
        day,
    ] =
        normalized.split('-');

    const parsed =
        new Date(
            Number(year),
            Number(month) - 1,
            Number(day)
        );

    return parsed.toLocaleDateString(
        'en-US',
        {
            weekday: 'long',
        }
    );
};


const formatMonth = (
    date
) => {
    const normalized =
        normalizeDateForInput(
            date
        );

    if (!normalized) {
        return '---';
    }

    const [
        year,
        month,
        day,
    ] =
        normalized.split('-');

    return new Date(
        Number(year),
        Number(month) - 1,
        Number(day)
    )
        .toLocaleDateString(
            'en-US',
            {
                month: 'short',
            }
        )
        .toUpperCase();
};


const formatDateNumber = (
    date
) => {
    const normalized =
        normalizeDateForInput(
            date
        );

    if (!normalized) {
        return '--';
    }

    return normalized
        .split('-')[2];
};


const formatTime = (
    time
) => {
    if (!time) {
        return '';
    }

    const normalized =
        normalizeTimeForInput(
            time
        );

    if (!normalized) {
        return String(time);
    }

    const [
        hours,
        minutes,
    ] =
        normalized.split(':');

    const date =
        new Date();

    date.setHours(
        Number(hours),
        Number(minutes),
        0,
        0
    );

    return date.toLocaleTimeString(
        'en-US',
        {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        }
    );
};
</script>


<template>

    <Head
        title="Schedule"
    />


    <Admin_IC_Layout
        :user="props.user"
        :role="currentRole"
    >

        <main class="schedule-page">

            <!-- =========================================================
                 HEADER
            ========================================================== -->

            <section class="schedule-masthead">

                <div class="masthead-mark">

                    <CalendarDays
                        :size="30"
                        :stroke-width="2"
                    />

                </div>


                <div class="masthead-copy">

                    <div class="masthead-kicker">

                        <Sparkles
                            :size="15"
                            :stroke-width="2"
                        />

                        <span>
                            NSTP SCHEDULE CENTER
                        </span>

                    </div>


                    <h1>
                        Schedule
                    </h1>


                    <p>
                        Plan, organize, and manage official
                        NSTP activities and events.
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
                            SCHEDULES
                        </span>

                        <strong>
                            {{
                                String(
                                    scheduleCount
                                ).padStart(
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
                        NSTP SCHEDULE MANAGEMENT
                    </span>

                </div>

            </section>


            <!-- =========================================================
                 WORKSPACE
            ========================================================== -->

            <div
                class="schedule-workspace"
                :class="{
                    'schedule-workspace--view-only':
                        !props.canManage,
                }"
            >

                <!-- =====================================================
                     CREATE / EDIT
                ====================================================== -->

                <aside
                    v-if="props.canManage"
                    class="composer-panel"
                >

                    <header class="composer-heading">

                        <div class="composer-heading-number">

                            {{
                                isEditing
                                    ? 'EDIT'
                                    : 'NEW'
                            }}

                        </div>


                        <div class="composer-heading-copy">

                            <span>
                                SCHEDULE DESK
                            </span>

                            <h2>
                                {{
                                    isEditing
                                        ? 'Edit Schedule'
                                        : 'Create Schedule'
                                }}
                            </h2>

                        </div>


                        <CalendarPlus
                            class="composer-heading-icon"
                            :size="29"
                            :stroke-width="1.8"
                        />

                    </header>


                    <form
                        class="schedule-form"
                        @submit.prevent="
                            submitSchedule
                        "
                    >

                        <!-- =============================================
                             EVENT DETAILS
                        ============================================== -->

                        <section class="form-section">

                            <div class="form-section-marker">
                                01
                            </div>


                            <div class="form-section-content">

                                <div class="form-section-title">
                                    EVENT DETAILS
                                </div>


                                <!-- TITLE -->

                                <div class="field-group">

                                    <label
                                        for="schedule-title"
                                    >
                                        Schedule Title
                                    </label>


                                    <div class="field-shell">

                                        <CalendarPlus
                                            :size="19"
                                            :stroke-width="2"
                                        />

                                        <input
                                            id="schedule-title"
                                            v-model="form.title"
                                            type="text"
                                            maxlength="255"
                                            placeholder="e.g. NSTP Orientation"
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

                                        {{
                                            form.errors.title
                                        }}

                                    </span>

                                </div>


                                <!-- DATE -->

                                <div class="field-group">

                                    <label
                                        for="schedule-date"
                                    >
                                        Schedule Date
                                    </label>


                                    <div class="field-shell">

                                        <CalendarDays
                                            :size="19"
                                            :stroke-width="2"
                                        />

                                        <input
                                            id="schedule-date"
                                            v-model="
                                                form.schedule_date
                                            "
                                            type="date"
                                            required
                                        />

                                    </div>


                                    <span
                                        v-if="
                                            form.errors
                                                .schedule_date
                                        "
                                        class="field-error"
                                    >

                                        <AlertCircle
                                            :size="16"
                                        />

                                        {{
                                            form.errors
                                                .schedule_date
                                        }}

                                    </span>

                                </div>


                                <!-- TIME -->

                                <div class="time-fields">

                                    <div class="field-group">

                                        <label
                                            for="schedule-start-time"
                                        >
                                            Start Time
                                        </label>


                                        <div class="field-shell">

                                            <Clock3
                                                :size="19"
                                                :stroke-width="2"
                                            />

                                            <input
                                                id="schedule-start-time"
                                                v-model="
                                                    form.start_time
                                                "
                                                type="time"
                                                required
                                            />

                                        </div>


                                        <span
                                            v-if="
                                                form.errors
                                                    .start_time
                                            "
                                            class="field-error"
                                        >

                                            <AlertCircle
                                                :size="16"
                                            />

                                            {{
                                                form.errors
                                                    .start_time
                                            }}

                                        </span>

                                    </div>


                                    <div class="field-group">

                                        <label
                                            for="schedule-end-time"
                                        >
                                            End Time
                                        </label>


                                        <div class="field-shell">

                                            <Clock3
                                                :size="19"
                                                :stroke-width="2"
                                            />

                                            <input
                                                id="schedule-end-time"
                                                v-model="
                                                    form.end_time
                                                "
                                                type="time"
                                                required
                                            />

                                        </div>


                                        <span
                                            v-if="
                                                form.errors
                                                    .end_time
                                            "
                                            class="field-error"
                                        >

                                            <AlertCircle
                                                :size="16"
                                            />

                                            {{
                                                form.errors
                                                    .end_time
                                            }}

                                        </span>

                                    </div>

                                </div>


                                <!-- LOCATION -->

                                <div class="field-group">

                                    <label
                                        for="schedule-location"
                                    >
                                        Location
                                    </label>


                                    <div class="field-shell">

                                        <MapPin
                                            :size="19"
                                            :stroke-width="2"
                                        />

                                        <input
                                            id="schedule-location"
                                            v-model="
                                                form.location
                                            "
                                            type="text"
                                            maxlength="255"
                                            placeholder="e.g. EB 315"
                                            required
                                        />

                                    </div>


                                    <span
                                        v-if="
                                            form.errors.location
                                        "
                                        class="field-error"
                                    >

                                        <AlertCircle
                                            :size="16"
                                        />

                                        {{
                                            form.errors.location
                                        }}

                                    </span>

                                </div>

                            </div>

                        </section>


                        <!-- =============================================
                             COMPONENT
                        ============================================== -->

                        <section class="form-section">

                            <div class="form-section-marker">
                                02
                            </div>


                            <div class="form-section-content">

                                <div class="form-section-title">
                                    NSTP COMPONENT
                                </div>


                                <div
                                    v-if="
                                        isUniversityAdmin
                                    "
                                    class="field-group"
                                >

                                    <label
                                        for="schedule-component"
                                    >
                                        Component
                                    </label>


                                    <select
                                        id="schedule-component"
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
                                            :key="component"
                                            :value="component"
                                        >

                                            {{
                                                component ===
                                                'ALL'
                                                    ? 'ALL COMPONENTS'
                                                    : component
                                            }}

                                        </option>

                                    </select>


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


                                <div
                                    v-else
                                    class="locked-component"
                                >

                                    <div class="locked-component-icon">

                                        <LockKeyhole
                                            :size="19"
                                            :stroke-width="2"
                                        />

                                    </div>


                                    <div class="locked-component-copy">

                                        <span>
                                            ASSIGNED COMPONENT
                                        </span>

                                        <strong>
                                            {{ componentLabel }}
                                        </strong>

                                        <small>
                                            Your account is assigned
                                            to this NSTP component.
                                        </small>

                                    </div>

                                </div>

                            </div>

                        </section>


                        <!-- =============================================
                             ACTIONS
                        ============================================== -->

                        <div class="composer-actions">

                            <button
                                type="submit"
                                class="submit-button"
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
                                                ? 'UPDATE SCHEDULE'
                                                : 'POST SCHEDULE'
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
                                class="cancel-button"
                                :disabled="
                                    form.processing
                                "
                                @click="resetForm"
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


                <!-- =====================================================
                     BOARD
                ====================================================== -->

                <section class="schedule-board">

                    <header class="board-header">

                        <div class="board-title">

                            <span>
                                OFFICIAL SCHEDULE
                            </span>

                            <h2>
                                NSTP Activities
                            </h2>

                            <p>
                                Review upcoming and previous
                                NSTP schedules and activities.
                            </p>

                        </div>


                        <div class="board-tools">

                            <label class="schedule-search">

                                <Search
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <input
                                    v-model="searchTerm"
                                    type="search"
                                    placeholder="Search schedules..."
                                />

                            </label>


                            <div class="schedule-filter">

                                <span class="schedule-filter-icon">

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
                                    RECENT
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


                    <!-- =============================================
                         SCHEDULE LIST
                    ============================================== -->

                    <div
                        v-if="
                            filteredSchedules.length >
                            0
                        "
                        class="schedule-list"
                    >

                        <article
                            v-for="
                                (
                                    schedule,
                                    index
                                )
                                in
                                filteredSchedules
                            "
                            :key="schedule.id"
                            class="schedule-card"
                        >

                            <!-- EVENT INDEX -->

                            <div class="schedule-index">

                                <small>
                                    EVENT
                                </small>

                                <strong>
                                    {{
                                        String(
                                            index + 1
                                        ).padStart(
                                            2,
                                            '0'
                                        )
                                    }}
                                </strong>

                            </div>


                            <!-- DATE -->

                            <div class="schedule-date-block">

                                <span>
                                    {{
                                        formatMonth(
                                            schedule
                                                .schedule_date
                                        )
                                    }}
                                </span>

                                <strong>
                                    {{
                                        formatDateNumber(
                                            schedule
                                                .schedule_date
                                        )
                                    }}
                                </strong>

                                <small>
                                    {{
                                        formatDay(
                                            schedule
                                                .schedule_date
                                        )
                                    }}
                                </small>

                            </div>


                            <!-- BODY -->

                            <div class="schedule-card-body">

                                <div class="schedule-meta">

                                    <span class="component-label">

                                        <Layers3
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        {{
                                            schedule.component ===
                                            'ALL'
                                                ? 'ALL COMPONENTS'
                                                : schedule.component
                                        }}

                                    </span>


                                    <span class="date-label">

                                        <CalendarDays
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        {{
                                            formatDate(
                                                schedule
                                                    .schedule_date
                                            )
                                        }}

                                    </span>

                                </div>


                                <h3>
                                    {{ schedule.title }}
                                </h3>


                                <div class="schedule-rule">
                                    <span></span>
                                    <span></span>
                                </div>


                                <div class="schedule-details">

                                    <!-- TIME -->

                                    <div class="schedule-detail-card">

                                        <div class="schedule-detail-icon">

                                            <Clock3
                                                :size="20"
                                                :stroke-width="2"
                                            />

                                        </div>


                                        <div>

                                            <small>
                                                TIME
                                            </small>

                                            <strong>
                                                {{
                                                    formatTime(
                                                        schedule
                                                            .start_time
                                                    )
                                                }}

                                                –

                                                {{
                                                    formatTime(
                                                        schedule
                                                            .end_time
                                                    )
                                                }}
                                            </strong>

                                        </div>

                                    </div>


                                    <!-- LOCATION -->

                                    <div class="schedule-detail-card">

                                        <div class="schedule-detail-icon">

                                            <MapPin
                                                :size="20"
                                                :stroke-width="2"
                                            />

                                        </div>


                                        <div>

                                            <small>
                                                LOCATION
                                            </small>

                                            <strong>
                                                {{
                                                    schedule.location
                                                    ||
                                                    'Not specified'
                                                }}
                                            </strong>

                                        </div>

                                    </div>

                                </div>


                                <footer class="schedule-card-footer">

                                    <div class="schedule-status">

                                        <CheckCircle2
                                            :size="17"
                                            :stroke-width="2"
                                        />

                                        <span>
                                            SCHEDULED
                                        </span>

                                    </div>


                                    <div
                                        v-if="
                                            schedule.can_edit
                                            ||
                                            schedule.can_delete
                                        "
                                        class="schedule-actions"
                                    >

                                        <button
                                            v-if="
                                                schedule.can_edit
                                            "
                                            type="button"
                                            class="
                                                schedule-action
                                                schedule-action--edit
                                            "
                                            title="Edit schedule"
                                            aria-label="Edit schedule"
                                            @click="
                                                editSchedule(
                                                    schedule
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
                                                schedule.can_delete
                                            "
                                            type="button"
                                            class="
                                                schedule-action
                                                schedule-action--delete
                                            "
                                            title="Delete schedule"
                                            aria-label="Delete schedule"
                                            :disabled="
                                                deletingId ===
                                                schedule.id
                                            "
                                            @click="
                                                deleteSchedule(
                                                    schedule
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


                            <div class="card-corner"></div>

                        </article>

                    </div>


                    <!-- =============================================
                         EMPTY
                    ============================================== -->

                    <div
                        v-else
                        class="schedule-empty"
                    >

                        <div class="schedule-empty-icon">

                            <Inbox
                                :size="38"
                                :stroke-width="1.8"
                            />

                        </div>

                        <span>
                            SCHEDULE BOARD
                        </span>

                        <h3>
                            No schedules found
                        </h3>

                        <p>
                            {{
                                searchTerm
                                    ? 'Try another search term or select a different schedule filter.'
                                    : 'New NSTP schedules will appear here after they are posted.'
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
| DESIGN SYSTEM
|--------------------------------------------------------------------------
*/

.schedule-page {
    --cream: #EFEBE2;
    --maroon: #54100F;
    --green: #58761C;
    --yellow: #FFBD36;
    --orange: #D99202;
    --dark-teal: #233E47;
    --near-black: #000D12;
    --white: #FFFFFF;
    --gray: #BEBEBE;
    --dark: #0D171B;

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

    width: 100%;
    min-width: 0;
    min-height: 100%;

    padding:
        30px
        34px
        56px;

    box-sizing: border-box;

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
| MASTHEAD
|--------------------------------------------------------------------------
*/

.schedule-masthead {
    position: relative;

    width: 100%;
    min-height: 180px;

    margin-bottom:
        26px;

    padding:
        30px
        32px
        45px;

    box-sizing:
        border-box;

    display: grid;

    grid-template-columns:
        74px
        minmax(0, 1fr)
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
                35,
                62,
                71,
                0.07
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
| TOP ACCENT
|--------------------------------------------------------------------------
*/

.schedule-masthead::before {
    content: "";

    position:
        absolute;

    top: 0;
    right: 0;

    width:
        220px;

    height:
        9px;

    background:
        linear-gradient(
            90deg,
            var(--green),
            var(--yellow),
            var(--dark-teal)
        );
}


/*
|--------------------------------------------------------------------------
| NO WATERMARK
|--------------------------------------------------------------------------
|
| There is intentionally NO .schedule-masthead::after.
|
| The old large faded "NSTP" watermark has been completely removed.
|
*/


/*
|--------------------------------------------------------------------------
| MASTHEAD ICON
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
            0.42
        );
}


/*
|--------------------------------------------------------------------------
| MASTHEAD COPY
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
| MASTHEAD META
|--------------------------------------------------------------------------
*/

.masthead-meta {
    position:
        relative;

    z-index:
        2;

    min-width:
        270px;

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
            0.84
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
| MASTHEAD FOOTER
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
| WORKSPACE
|--------------------------------------------------------------------------
*/

.schedule-workspace {
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


.schedule-workspace--view-only {
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

.composer-panel {
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


/*
|--------------------------------------------------------------------------
| COMPOSER HEADER
|--------------------------------------------------------------------------
*/

.composer-heading {
    min-height:
        108px;

    padding:
        21px
        22px;

    box-sizing:
        border-box;

    display:
        grid;

    grid-template-columns:
        58px
        minmax(0, 1fr)
        34px;

    align-items:
        center;

    gap:
        13px;

    background:
        var(--dark-teal);

    color:
        var(--white);
}


.composer-heading-number {
    min-width:
        58px;

    height:
        38px;

    padding:
        0
        10px;

    box-sizing:
        border-box;

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
        var(--yellow);

    color:
        var(--near-black);

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.6px;
}


.composer-heading-copy {
    min-width:
        0;
}


.composer-heading-copy span {
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
        0.8px;
}


.composer-heading-copy h2 {
    margin:
        0;

    color:
        var(--white);

    font-family:
        var(--font-display);

    font-size:
        27px;

    line-height:
        1.15;
}


.composer-heading-icon {
    color:
        rgba(
            255,
            255,
            255,
            0.65
        );
}


/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

.schedule-form {
    padding:
        0
        22px
        25px
        0;
}


.form-section {
    position:
        relative;

    display:
        grid;

    grid-template-columns:
        51px
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
        28px;

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


.form-section-content {
    padding:
        25px
        0
        26px
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
| FIELD GROUP
|--------------------------------------------------------------------------
*/

.field-group {
    min-width:
        0;

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
        19px;
}


.field-group > label {
    color:
        var(--dark-teal);

    font-size:
        15px;

    font-weight:
        700;
}


/*
|--------------------------------------------------------------------------
| FIELD SHELL
|--------------------------------------------------------------------------
*/

.field-shell {
    min-height:
        52px;

    padding:
        0
        14px;

    box-sizing:
        border-box;

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

    transition:
        border-color
        0.16s
        ease,
        box-shadow
        0.16s
        ease;
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


.field-shell input {
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


.field-shell input::placeholder {
    color:
        #707B7F;

    opacity:
        1;
}


/*
|--------------------------------------------------------------------------
| TIME
|--------------------------------------------------------------------------
*/

.time-fields {
    margin-top:
        19px;

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
        13px;
}


.time-fields
.field-group
+
.field-group {
    margin-top:
        0;
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

    box-sizing:
        border-box;

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

.locked-component {
    min-height:
        78px;

    padding:
        12px
        14px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    border-radius:
        6px
        14px
        6px
        14px;

    background:
        var(--dark-teal);

    color:
        var(--white);
}


.locked-component-icon {
    width:
        42px;

    height:
        42px;

    flex:
        0
        0
        42px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            0.18
        );

    border-radius:
        6px
        11px
        6px
        11px;

    color:
        var(--yellow);
}


.locked-component-copy {
    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    gap:
        2px;
}


.locked-component-copy > span {
    color:
        var(--yellow);

    font-size:
        11px;

    font-weight:
        700;

    letter-spacing:
        0.7px;
}


.locked-component-copy strong {
    color:
        var(--white);

    font-size:
        17px;

    font-weight:
        700;
}


.locked-component-copy small {
    color:
        rgba(
            255,
            255,
            255,
            0.73
        );

    font-size:
        13px;

    line-height:
        1.4;
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

    line-height:
        1.4;
}


/*
|--------------------------------------------------------------------------
| FORM ACTIONS
|--------------------------------------------------------------------------
*/

.composer-actions {
    margin-left:
        51px;

    padding:
        22px
        0
        0
        18px;

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


.submit-button {
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
        minmax(0, 1fr)
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

    transition:
        transform
        0.16s
        ease,
        box-shadow
        0.16s
        ease;
}


.submit-button:hover:not(:disabled) {
    transform:
        translateY(-1px);

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


.submit-button:disabled {
    cursor:
        not-allowed;

    opacity:
        0.55;
}


.cancel-button {
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

.schedule-board {
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


.board-title > span {
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

    line-height:
        1.5;
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


.schedule-search {
    width:
        245px;

    height:
        48px;

    padding:
        0
        13px;

    box-sizing:
        border-box;

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


.schedule-search:focus-within {
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


.schedule-search input {
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


.schedule-search input::placeholder {
    color:
        #68777B;
}


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

.schedule-filter {
    min-height:
        48px;

    padding:
        4px;

    box-sizing:
        border-box;

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


.schedule-filter-icon {
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


.schedule-filter button {
    min-width:
        68px;

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


.schedule-filter button.active {
    background:
        var(--dark-teal);

    color:
        var(--white);
}


/*
|--------------------------------------------------------------------------
| LIST
|--------------------------------------------------------------------------
*/

.schedule-list {
    display:
        flex;

    flex-direction:
        column;

    gap:
        18px;
}


/*
|--------------------------------------------------------------------------
| CARD
|--------------------------------------------------------------------------
*/

.schedule-card {
    position:
        relative;

    min-height:
        230px;

    display:
        grid;

    grid-template-columns:
        74px
        105px
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


.schedule-card:hover {
    transform:
        translateY(-2px);

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
| INDEX
|--------------------------------------------------------------------------
*/

.schedule-index {
    padding-top:
        25px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    background:
        var(--dark-teal);
}


.schedule-index small {
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


.schedule-index strong {
    margin-top:
        5px;

    color:
        var(--yellow);

    font-family:
        var(--font-display);

    font-size:
        26px;
}


/*
|--------------------------------------------------------------------------
| DATE BLOCK
|--------------------------------------------------------------------------
*/

.schedule-date-block {
    padding-top:
        25px;

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
        rgba(
            239,
            235,
            226,
            0.60
        );
}


.schedule-date-block > span {
    color:
        var(--maroon);

    font-size:
        14px;

    font-weight:
        700;

    letter-spacing:
        0.7px;
}


.schedule-date-block > strong {
    margin-top:
        2px;

    color:
        var(--dark-teal);

    font-family:
        var(--font-display);

    font-size:
        39px;

    line-height:
        1;
}


.schedule-date-block > small {
    margin-top:
        7px;

    padding:
        4px
        8px;

    border-radius:
        999px;

    background:
        rgba(
            35,
            62,
            71,
            0.08
        );

    color:
        var(--dark-teal);

    font-size:
        12px;

    font-weight:
        700;
}


/*
|--------------------------------------------------------------------------
| CARD BODY
|--------------------------------------------------------------------------
*/

.schedule-card-body {
    min-width:
        0;

    padding:
        23px
        26px
        20px;
}


.schedule-meta {
    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        8px;
}


.component-label,
.date-label {
    min-height:
        31px;

    padding:
        6px
        11px;

    box-sizing:
        border-box;

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


.date-label {
    background:
        rgba(
            35,
            62,
            71,
            0.09
        );

    color:
        var(--dark-teal);
}


.schedule-card-body h3 {
    margin:
        15px
        0
        0;

    color:
        var(--dark-teal);

    font-family:
        var(--font-display);

    font-size:
        28px;

    font-weight:
        700;

    line-height:
        1.3;

    overflow-wrap:
        anywhere;
}


.schedule-rule {
    margin-top:
        13px;

    display:
        flex;

    align-items:
        center;

    gap:
        6px;
}


.schedule-rule span:first-child {
    width:
        44px;

    height:
        4px;

    background:
        var(--maroon);
}


.schedule-rule span:last-child {
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


/*
|--------------------------------------------------------------------------
| DETAILS
|--------------------------------------------------------------------------
*/

.schedule-details {
    margin-top:
        18px;

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
        11px;
}


.schedule-detail-card {
    min-height:
        68px;

    padding:
        10px
        12px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        11px;

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
        6px
        12px
        6px
        12px;

    background:
        rgba(
            239,
            235,
            226,
            0.38
        );
}


.schedule-detail-icon {
    width:
        42px;

    height:
        42px;

    flex:
        0
        0
        42px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        6px
        11px
        6px
        11px;

    background:
        var(--dark-teal);

    color:
        var(--white);
}


.schedule-detail-card > div:last-child {
    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    gap:
        2px;
}


.schedule-detail-card small {
    color:
        #59686D;

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.5px;
}


.schedule-detail-card strong {
    color:
        var(--dark-teal);

    font-size:
        15px;

    font-weight:
        700;

    line-height:
        1.35;

    overflow-wrap:
        anywhere;
}


/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

.schedule-card-footer {
    margin-top:
        19px;

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

    justify-content:
        space-between;

    gap:
        12px;
}


.schedule-status {
    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    color:
        var(--green);
}


.schedule-status span {
    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.7px;
}


/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/

.schedule-actions {
    display:
        flex;

    align-items:
        center;

    gap:
        7px;
}


.schedule-action {
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


.schedule-action:hover:not(:disabled) {
    transform:
        translateY(-2px);
}


.schedule-action--edit {
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


.schedule-action--edit:hover {
    background:
        var(--dark-teal);

    color:
        var(--white);
}


.schedule-action--delete {
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


.schedule-action--delete:hover:not(:disabled) {
    background:
        var(--maroon);

    color:
        var(--white);
}


.schedule-action:disabled {
    cursor:
        not-allowed;

    opacity:
        0.45;
}


/*
|--------------------------------------------------------------------------
| CARD CORNER
|--------------------------------------------------------------------------
*/

.card-corner {
    position:
        absolute;

    right:
        0;

    bottom:
        0;

    width:
        36px;

    height:
        36px;

    overflow:
        hidden;

    pointer-events:
        none;
}


.card-corner::before {
    content: "";

    position:
        absolute;

    right:
        -18px;

    bottom:
        -18px;

    width:
        36px;

    height:
        36px;

    transform:
        rotate(45deg);

    background:
        rgba(
            255,
            189,
            54,
            0.65
        );
}


/*
|--------------------------------------------------------------------------
| EMPTY
|--------------------------------------------------------------------------
*/

.schedule-empty {
    min-height:
        390px;

    padding:
        40px;

    box-sizing:
        border-box;

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


.schedule-empty-icon {
    width:
        74px;

    height:
        74px;

    margin-bottom:
        17px;

    border-radius:
        6px
        18px
        6px
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


.schedule-empty > span {
    color:
        var(--maroon);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        1px;
}


.schedule-empty h3 {
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


.schedule-empty p {
    max-width:
        430px;

    margin:
        11px
        0
        0;

    color:
        #445358;

    font-size:
        16px;

    line-height:
        1.6;
}


/*
|--------------------------------------------------------------------------
| ACCESSIBILITY
|--------------------------------------------------------------------------
*/

button:focus-visible,
input:focus-visible,
select:focus-visible {
    outline:
        4px
        solid
        rgba(
            255,
            189,
            54,
            0.60
        );

    outline-offset:
        2px;
}


/*
|--------------------------------------------------------------------------
| LARGE SCREENS
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1200px
) {

    .schedule-page {
        padding:
            26px
            26px
            48px;
    }


    .schedule-workspace {
        grid-template-columns:
            minmax(
                340px,
                385px
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


    .schedule-search {
        flex:
            1;
    }


    .schedule-details {
        grid-template-columns:
            1fr;
    }

}


/*
|--------------------------------------------------------------------------
| TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 950px
) {

    .schedule-masthead {
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


    .schedule-workspace {
        grid-template-columns:
            1fr;
    }


    .composer-panel {
        position:
            static;
    }

}


/*
|--------------------------------------------------------------------------
| SMALL TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 720px
) {

    .schedule-page {
        padding:
            18px;
    }


    .schedule-masthead {
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
        width:
            100%;

        align-items:
            stretch;

        flex-direction:
            column;
    }


    .schedule-search,
    .schedule-filter {
        width:
            100%;
    }


    .schedule-filter button {
        flex:
            1;
    }


    .schedule-card {
        grid-template-columns:
            62px
            85px
            minmax(
                0,
                1fr
            );
    }


    .schedule-card-body {
        padding:
            20px
            17px
            17px;
    }

}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 540px
) {

    .schedule-page {
        padding:
            12px;
    }


    .schedule-masthead {
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


    .masthead-bottom-line span:last-child {
        display:
            none;
    }


    .schedule-form {
        padding-right:
            15px;
    }


    .form-section-content {
        padding-right:
            0;
    }


    .time-fields {
        grid-template-columns:
            1fr;
    }


    .composer-actions {
        margin-left:
            0;

        padding-left:
            15px;
    }


    .schedule-card {
        display:
            block;

        padding-top:
            68px;
    }


    .schedule-index {
        position:
            absolute;

        top:
            0;

        left:
            0;

        width:
            50%;

        height:
            56px;

        padding:
            0
            15px;

        box-sizing:
            border-box;

        flex-direction:
            row;

        justify-content:
            flex-start;

        gap:
            7px;
    }


    .schedule-index strong {
        margin:
            0;

        font-size:
            19px;
    }


    .schedule-date-block {
        position:
            absolute;

        top:
            0;

        right:
            0;

        width:
            50%;

        height:
            56px;

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
    }


    .schedule-date-block > span {
        font-size:
            12px;
    }


    .schedule-date-block > strong {
        margin:
            0;

        font-size:
            22px;
    }


    .schedule-date-block > small {
        display:
            none;
    }


    .schedule-card-body h3 {
        font-size:
            24px;
    }


    .schedule-details {
        grid-template-columns:
            1fr;
    }


    .schedule-card-footer {
        align-items:
            flex-start;

        flex-wrap:
            wrap;
    }


    .schedule-actions {
        width:
            100%;

        justify-content:
            flex-end;
    }


    .component-label,
    .date-label {
        font-size:
            12px;
    }

}

</style>