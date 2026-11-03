<script setup>

import {
    computed,
    ref,
} from 'vue';

import {
    Head,
    router,
    useForm,
} from '@inertiajs/vue3';


/*
|--------------------------------------------------------------------------
| Layouts
|--------------------------------------------------------------------------
*/

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import UsersLayout
    from '@/layouts/usersLayout.vue';


/*
|--------------------------------------------------------------------------
| Real Icons
|--------------------------------------------------------------------------
*/

import {
    BookOpenCheck,
    CalendarDays,
    CheckCircle2,
    Clock3,
    MinusCircle,
    ShieldCheck,
    SlidersHorizontal,
    Sparkles,
    UsersRound,
    X,
} from 'lucide-vue-next';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    students: {
        type: Array,
        default: () => [],
    },

    components: {
        type: Array,
        default: () => [],
    },

    statistics: {
        type: Object,
        default: () => ({}),
    },

    registrationDeadline: {
        type: Object,
        default: null,
    },

});


/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

const selectedStatus =
    ref('ALL');


const statusOptions = [
    'ALL',
    'PENDING',
    'APPROVED',
];


/*
|--------------------------------------------------------------------------
| Deadline Editor
|--------------------------------------------------------------------------
*/

const showDeadlineEditor =
    ref(false);


/*
|--------------------------------------------------------------------------
| Normalize Date
|--------------------------------------------------------------------------
*/

const normalizeDateForInput = (
    value
) => {

    if (!value) {
        return '';
    }

    return String(
        value
    ).slice(
        0,
        10
    );

};


/*
|--------------------------------------------------------------------------
| Normalize Time
|--------------------------------------------------------------------------
*/

const normalizeTimeForInput = (
    value
) => {

    if (!value) {
        return '';
    }

    return String(
        value
    ).slice(
        0,
        5
    );

};


/*
|--------------------------------------------------------------------------
| Deadline Form
|--------------------------------------------------------------------------
*/

const deadlineForm =
    useForm({

        start_date:
            normalizeDateForInput(
                props.registrationDeadline
                    ?.start_date
            ),

        end_date:
            normalizeDateForInput(
                props.registrationDeadline
                    ?.end_date
            ),

        end_time:
            normalizeTimeForInput(
                props.registrationDeadline
                    ?.end_time
            ),

    });


/*
|--------------------------------------------------------------------------
| Has Deadline
|--------------------------------------------------------------------------
*/

const hasDeadline =
    computed(() => {

        return Boolean(
            props.registrationDeadline
            &&
            (
                props.registrationDeadline
                    ?.start_date
                ||
                props.registrationDeadline
                    ?.end_date
            )
        );

    });


/*
|--------------------------------------------------------------------------
| Student Full Name
|--------------------------------------------------------------------------
*/

const studentName = (
    student
) => {

    if (
        student?.full_name
    ) {
        return student.full_name;
    }


    const givenNames =
        [
            student?.first_name,
            student?.middle_name,
        ]
            .filter(Boolean)
            .join(' ')
            .trim();


    if (
        student?.surname
        &&
        givenNames
    ) {
        return `${student.surname}, ${givenNames}`;
    }


    return (
        student?.name
        ??
        student?.email
        ??
        'Unnamed Student'
    );

};


/*
|--------------------------------------------------------------------------
| Registration Status
|--------------------------------------------------------------------------
*/

const normalizeStatus = (
    student
) => {

    const backendDisplayStatus =
        String(
            student?.display_status
            ??
            ''
        )
            .trim()
            .toUpperCase();


    if (
        backendDisplayStatus ===
        'APPROVED'
    ) {
        return 'APPROVED';
    }


    if (
        backendDisplayStatus ===
        'PENDING'
    ) {
        return 'PENDING';
    }


    const rawStatus =
        String(
            student?.registration_status
            ??
            student?.status
            ??
            ''
        )
            .trim()
            .toLowerCase();


    if (
        [
            'approved',
            'confirmed',
            'completed',
        ].includes(
            rawStatus
        )
    ) {
        return 'APPROVED';
    }


    return 'PENDING';

};


/*
|--------------------------------------------------------------------------
| Normalized Students
|--------------------------------------------------------------------------
*/

const normalizedStudents =
    computed(() => {

        return props.students.map(
            (
                student,
                index
            ) => {

                const idNumber =
                    student?.student_id_number
                    ??
                    student?.id_number
                    ??
                    student?.student_number
                    ??
                    `NSTP-${String(
                        student?.id
                        ??
                        index + 1
                    ).padStart(
                        6,
                        '0'
                    )}`;


                const yearLevel =
                    student?.year_level
                    ??
                    student?.year
                    ??
                    '';


                const section =
                    student?.section
                    ??
                    '';


                const yearSection =
                    [
                        yearLevel,
                        section,
                    ]
                        .filter(Boolean)
                        .join(' / ');


                const component =
                    String(
                        student?.component
                        ??
                        ''
                    )
                        .trim()
                        .toUpperCase();


                return {

                    ...student,

                    id_number:
                        idNumber,

                    full_name:
                        studentName(
                            student
                        ),

                    course:
                        student?.course
                        ??
                        '-',

                    year_section:
                        yearSection
                        ||
                        '-',

                    component:
                        component
                        ||
                        '-',

                    status:
                        normalizeStatus(
                            student
                        ),

                };

            }
        );

    });


/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

const statusFilteredStudents =
    computed(() => {

        if (
            selectedStatus.value ===
            'ALL'
        ) {
            return normalizedStudents.value;
        }


        return normalizedStudents.value.filter(
            student => {

                return (
                    student.status ===
                    selectedStatus.value
                );

            }
        );

    });


/*
|--------------------------------------------------------------------------
| Components
|--------------------------------------------------------------------------
*/

const availableComponents =
    computed(() => {

        const universityComponents =
            props.components
                .map(
                    component => {

                        return String(
                            component
                            ??
                            ''
                        )
                            .trim()
                            .toUpperCase();

                    }
                )
                .filter(Boolean);


        if (
            universityComponents.length >
            0
        ) {

            return [
                ...new Set(
                    universityComponents
                ),
            ];

        }


        return [
            ...new Set(
                normalizedStudents.value
                    .map(
                        student =>
                            student.component
                    )
                    .filter(
                        component => {

                            return [
                                'CWTS',
                                'LTS',
                                'ROTC',
                            ].includes(
                                component
                            );

                        }
                    )
            ),
        ];

    });


/*
|--------------------------------------------------------------------------
| Count Component
|--------------------------------------------------------------------------
*/

const countComponent = (
    component
) => {

    return normalizedStudents.value.filter(
        student => {

            return (
                student.component ===
                component
            );

        }
    ).length;

};


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const cwtsCount =
    computed(() => {

        return Number(
            props.statistics?.cwts
            ??
            countComponent(
                'CWTS'
            )
        );

    });


const rotcCount =
    computed(() => {

        return Number(
            props.statistics?.rotc
            ??
            countComponent(
                'ROTC'
            )
        );

    });


const ltsCount =
    computed(() => {

        return Number(
            props.statistics?.lts
            ??
            countComponent(
                'LTS'
            )
        );

    });


const pendingCount =
    computed(() => {

        if (
            props.statistics?.pending !==
            undefined
        ) {

            return Number(
                props.statistics.pending
            );

        }


        return normalizedStudents.value.filter(
            student => {

                return (
                    student.status ===
                    'PENDING'
                );

            }
        ).length;

    });


const approvedCount =
    computed(() => {

        if (
            props.statistics?.approved !==
            undefined
        ) {

            return Number(
                props.statistics.approved
            );

        }


        return normalizedStudents.value.filter(
            student => {

                return (
                    student.status ===
                    'APPROVED'
                );

            }
        ).length;

    });


const totalStudents =
    computed(() => {

        return normalizedStudents.value.length;

    });


/*
|--------------------------------------------------------------------------
| Columns
|--------------------------------------------------------------------------
*/

const columns = [

    {
        key:
            'id_number',

        label:
            'ID NO.',

        width:
            '13%',
    },

    {
        key:
            'full_name',

        label:
            'NAME',

        width:
            '24%',

        align:
            'left',
    },

    {
        key:
            'course',

        label:
            'COURSE',

        width:
            '13%',
    },

    {
        key:
            'year_section',

        label:
            'YR & SECTION',

        width:
            '16%',
    },

    {
        key:
            'component',

        label:
            'COMPONENT',

        width:
            '15%',
    },

    {
        key:
            'status',

        label:
            'STATUS',

        width:
            '19%',
    },

];


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const setStatus = (
    status
) => {

    selectedStatus.value =
        status;

};


/*
|--------------------------------------------------------------------------
| Deadline Actions
|--------------------------------------------------------------------------
*/

const openDeadlineEditor =
    () => {

        showDeadlineEditor.value =
            true;

    };


const closeDeadlineEditor =
    () => {

        deadlineForm.clearErrors();

        showDeadlineEditor.value =
            false;

    };


const saveDeadline =
    () => {

        if (
            deadlineForm.processing
        ) {
            return;
        }


        deadlineForm.post(
            '/university-admin/student-registration/deadline',
            {

                preserveScroll:
                    true,

                onSuccess:
                    () => {

                        showDeadlineEditor.value =
                            false;

                    },

            }
        );

    };


const removeDeadline =
    () => {

        const confirmed =
            window.confirm(
                'Remove the current NSTP student registration period?'
            );


        if (
            !confirmed
        ) {
            return;
        }


        router.delete(
            '/university-admin/student-registration/deadline',
            {
                preserveScroll:
                    true,
            }
        );

    };


/*
|--------------------------------------------------------------------------
| View Student
|--------------------------------------------------------------------------
*/

const viewStudent = (
    student
) => {

    if (
        !student?.id
    ) {
        return;
    }


    router.visit(
        `/university-admin/student-registration/${student.id}`
    );

};


/*
|--------------------------------------------------------------------------
| Display Date
|--------------------------------------------------------------------------
*/

const displayDate = (
    value
) => {

    if (
        !value
    ) {
        return '-';
    }


    const date =
        new Date(
            `${String(
                value
            ).slice(
                0,
                10
            )}T00:00:00`
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
                'numeric',

            year:
                'numeric',
        }
    );

};


/*
|--------------------------------------------------------------------------
| Display Time
|--------------------------------------------------------------------------
*/

const displayTime = (
    value
) => {

    if (
        !value
    ) {
        return '-';
    }


    const normalized =
        String(
            value
        ).slice(
            0,
            5
        );


    const match =
        normalized.match(
            /^(\d{2}):(\d{2})$/
        );


    if (
        !match
    ) {
        return normalized;
    }


    const date =
        new Date();


    date.setHours(
        Number(
            match[1]
        ),
        Number(
            match[2]
        ),
        0,
        0
    );


    return date.toLocaleTimeString(
        'en-US',
        {
            hour:
                'numeric',

            minute:
                '2-digit',

            hour12:
                true,
        }
    );

};

</script>


<template>

    <Head
        title="Student Registration"
    />


    <UniversityAdminDashLayout
        current-route="student-registration"
    >

        <main
            class="student-registration-page"
        >

            <!-- ============================================================
                 ANNOUNCEMENT-STYLE MASTHEAD
            ============================================================= -->

            <section class="dispatch-masthead">

                <div class="masthead-mark">

                    <UsersRound
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

                        NSTP STUDENT RECORDS

                    </div>


                    <h1>
                        Student Registration
                    </h1>


                    <p>
                        Review student registration activity, manage the
                        enrollment period, and access official NSTP student records.
                    </p>

                </div>


                <div class="masthead-meta">

                    <div class="masthead-meta-item">

                        <span>
                            STUDENTS
                        </span>


                        <strong>
                            {{
                                String(
                                    totalStudents
                                ).padStart(
                                    2,
                                    '0'
                                )
                            }}
                        </strong>

                    </div>


                    <div class="masthead-rule"></div>


                    <div class="masthead-meta-item">

                        <span>
                            COMPONENTS
                        </span>


                        <strong>
                            {{
                                String(
                                    availableComponents.length
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
                        NSTP STUDENT REGISTRATION SERVICE
                    </span>

                </div>

            </section>


            <!-- ============================================================
                 STATISTICS
            ============================================================= -->

            <section
                class="statistics-area"
                aria-label="Student registration statistics"
            >

                <!-- CWTS -->

                <article
                    class="
                        stat-card
                        stat-card--cwts
                    "
                >

                    <div
                        class="stat-icon"
                    >

                        <UsersRound
                            :size="25"
                            :stroke-width="2"
                        />

                    </div>


                    <div
                        class="stat-content"
                    >

                        <span>
                            CWTS
                        </span>


                        <strong>
                            {{ cwtsCount.toLocaleString() }}
                        </strong>


                        <small>
                            Registered Students
                        </small>

                    </div>

                </article>


                <!-- ROTC -->

                <article
                    class="
                        stat-card
                        stat-card--rotc
                    "
                >

                    <div
                        class="stat-icon"
                    >

                        <ShieldCheck
                            :size="25"
                            :stroke-width="2"
                        />

                    </div>


                    <div
                        class="stat-content"
                    >

                        <span>
                            ROTC
                        </span>


                        <strong>
                            {{ rotcCount.toLocaleString() }}
                        </strong>


                        <small>
                            Registered Students
                        </small>

                    </div>

                </article>


                <!-- LTS -->

                <article
                    class="
                        stat-card
                        stat-card--lts
                    "
                >

                    <div
                        class="stat-icon"
                    >

                        <BookOpenCheck
                            :size="25"
                            :stroke-width="2"
                        />

                    </div>


                    <div
                        class="stat-content"
                    >

                        <span>
                            LTS
                        </span>


                        <strong>
                            {{ ltsCount.toLocaleString() }}
                        </strong>


                        <small>
                            Registered Students
                        </small>

                    </div>

                </article>


                <!-- PENDING -->

                <article
                    class="
                        stat-card
                        stat-card--pending
                    "
                >

                    <div
                        class="stat-icon"
                    >

                        <Clock3
                            :size="25"
                            :stroke-width="2"
                        />

                    </div>


                    <div
                        class="stat-content"
                    >

                        <span>
                            Pending
                        </span>


                        <strong>
                            {{ pendingCount.toLocaleString() }}
                        </strong>


                        <small>
                            Awaiting Approval
                        </small>

                    </div>

                </article>


                <!-- APPROVED -->

                <article
                    class="
                        stat-card
                        stat-card--approved
                    "
                >

                    <div
                        class="stat-icon"
                    >

                        <CheckCircle2
                            :size="25"
                            :stroke-width="2"
                        />

                    </div>


                    <div
                        class="stat-content"
                    >

                        <span>
                            Approved
                        </span>


                        <strong>
                            {{ approvedCount.toLocaleString() }}
                        </strong>


                        <small>
                            Approved Students
                        </small>

                    </div>

                </article>

            </section>


            <!-- ============================================================
                 REGISTRATION PERIOD
            ============================================================= -->

            <section
                class="deadline-panel"
            >

                <!-- COLLAPSED -->

                <div
                    v-if="
                        !showDeadlineEditor
                    "
                    class="deadline-collapsed"
                >

                    <div
                        class="deadline-intro"
                    >

                        <div
                            class="deadline-icon"
                        >

                            <CalendarDays
                                :size="27"
                                :stroke-width="2"
                            />

                        </div>


                        <div>

                            <span
                                class="section-kicker"
                            >
                                REGISTRATION CONTROL
                            </span>


                            <h2>
                                Registration Period
                            </h2>


                            <p>
                                Set the dates and closing time during
                                which students may submit or update
                                their NSTP registration.
                            </p>

                        </div>

                    </div>


                    <button
                        type="button"
                        class="set-deadline-button"
                        @click="
                            openDeadlineEditor
                        "
                    >

                        <CalendarDays
                            :size="20"
                            :stroke-width="2.2"
                        />

                        Set Deadline

                    </button>

                </div>


                <!-- EDITOR -->

                <form
                    v-else
                    class="deadline-editor"
                    @submit.prevent="
                        saveDeadline
                    "
                >

                    <header
                        class="section-header"
                    >

                        <div>

                            <span
                                class="section-kicker"
                            >
                                REGISTRATION SETTINGS
                            </span>


                            <h2>
                                Set Registration Period
                            </h2>

                        </div>


                        <button
                            type="button"
                            class="close-button"
                            aria-label="Close registration period editor"
                            @click="
                                closeDeadlineEditor
                            "
                        >

                            <X
                                :size="21"
                                :stroke-width="2.2"
                            />

                        </button>

                    </header>


                    <div
                        class="deadline-fields"
                    >

                        <label
                            class="deadline-field"
                        >

                            <span>
                                Submitted Date
                            </span>


                            <input
                                v-model="
                                    deadlineForm.start_date
                                "
                                type="date"
                            />


                            <small
                                v-if="
                                    deadlineForm.errors.start_date
                                "
                                class="field-error"
                            >
                                {{
                                    deadlineForm.errors.start_date
                                }}
                            </small>

                        </label>


                        <label
                            class="deadline-field"
                        >

                            <span>
                                Editable Until
                            </span>


                            <input
                                v-model="
                                    deadlineForm.end_date
                                "
                                type="date"
                            />


                            <small
                                v-if="
                                    deadlineForm.errors.end_date
                                "
                                class="field-error"
                            >
                                {{
                                    deadlineForm.errors.end_date
                                }}
                            </small>

                        </label>


                        <label
                            class="deadline-field"
                        >

                            <span>
                                Closing Time
                            </span>


                            <input
                                v-model="
                                    deadlineForm.end_time
                                "
                                type="time"
                            />


                            <small
                                v-if="
                                    deadlineForm.errors.end_time
                                "
                                class="field-error"
                            >
                                {{
                                    deadlineForm.errors.end_time
                                }}
                            </small>

                        </label>

                    </div>


                    <button
                        type="submit"
                        class="save-deadline-button"
                        :disabled="
                            deadlineForm.processing
                        "
                    >

                        <ShieldCheck
                            :size="20"
                            :stroke-width="2.2"
                        />


                        {{
                            deadlineForm.processing
                                ? 'Saving Registration Period...'
                                : 'Save Registration Period'
                        }}

                    </button>

                </form>

            </section>


            <!-- ============================================================
                 CURRENT REGISTRATION PERIOD
            ============================================================= -->

            <section
                v-if="
                    hasDeadline
                "
                class="current-period-panel"
            >

                <header
                    class="section-header"
                >

                    <div>

                        <span
                            class="section-kicker"
                        >
                            CURRENT REGISTRATION PERIOD
                        </span>


                        <h2>
                            Active Student Registration
                        </h2>


                        <p>
                            Current submission and editing schedule
                            for student registration.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="remove-deadline-button"
                        title="Remove registration period"
                        @click="
                            removeDeadline
                        "
                    >

                        <MinusCircle
                            :size="19"
                            :stroke-width="2.2"
                        />

                        <span>
                            Remove
                        </span>

                    </button>

                </header>


                <div
                    class="current-deadline-fields"
                >

                    <!-- SUBMITTED -->

                    <div
                        class="
                            deadline-value
                            deadline-value--submitted
                        "
                    >

                        <span>
                            Submitted Date
                        </span>


                        <div>

                            <CalendarDays
                                :size="19"
                                :stroke-width="2"
                            />

                            <strong>
                                {{
                                    displayDate(
                                        props
                                            .registrationDeadline
                                            ?.start_date
                                    )
                                }}
                            </strong>

                        </div>

                    </div>


                    <!-- EDITABLE -->

                    <div
                        class="
                            deadline-value
                            deadline-value--editable
                        "
                    >

                        <span>
                            Editable Until
                        </span>


                        <div>

                            <CalendarDays
                                :size="19"
                                :stroke-width="2"
                            />

                            <strong>
                                {{
                                    displayDate(
                                        props
                                            .registrationDeadline
                                            ?.end_date
                                    )
                                }}
                            </strong>

                        </div>

                    </div>


                    <!-- TIME -->

                    <div
                        class="
                            deadline-value
                            deadline-value--time
                        "
                    >

                        <span>
                            Closing Time
                        </span>


                        <div>

                            <Clock3
                                :size="19"
                                :stroke-width="2"
                            />

                            <strong>
                                {{
                                    displayTime(
                                        props
                                            .registrationDeadline
                                            ?.end_time
                                    )
                                }}
                            </strong>

                        </div>

                    </div>

                </div>

            </section>


            <!-- ============================================================
                 STATUS FILTER
            ============================================================= -->

            <section
                class="student-directory-section"
            >

                <div
                    class="registration-filter-bar"
                >

                    <div
                        class="registration-filter-title"
                    >

                        <div
                            class="registration-filter-icon"
                        >

                            <SlidersHorizontal
                                :size="21"
                                :stroke-width="2.1"
                            />

                        </div>


                        <div>

                            <span>
                                REGISTRATION STATUS
                            </span>


                            <strong>
                                Filter Students
                            </strong>


                            <p>
                                Show all students, pending registrations,
                                or approved registrations.
                            </p>

                        </div>

                    </div>


                    <div
                        class="status-tabs"
                        role="group"
                        aria-label="Registration status filter"
                    >

                        <button
                            v-for="
                                status in
                                statusOptions
                            "
                            :key="
                                status
                            "
                            type="button"
                            class="status-tab"
                            :class="[
                                `status-tab--${status.toLowerCase()}`,
                                {
                                    active:
                                        selectedStatus ===
                                        status,
                                },
                            ]"
                            @click="
                                setStatus(
                                    status
                                )
                            "
                        >

                            <UsersRound
                                v-if="
                                    status ===
                                    'ALL'
                                "
                                :size="17"
                                :stroke-width="2"
                            />


                            <Clock3
                                v-else-if="
                                    status ===
                                    'PENDING'
                                "
                                :size="17"
                                :stroke-width="2"
                            />


                            <CheckCircle2
                                v-else
                                :size="17"
                                :stroke-width="2"
                            />


                            {{ status }}

                        </button>

                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | UsersLayout
                |--------------------------------------------------------------------------
                |
                | Intentionally NOT styled from this page.
                | No :deep() selectors.
                |
                -->

                <UsersLayout
                    title="STUDENT DIRECTORY"
                    :show-add-button="false"
                    search-placeholder="Search Students..."
                    :search-fields="[
                        'id_number',
                        'full_name',
                        'course',
                        'year_section',
                        'component',
                        'status',
                    ]"
                    :rows="
                        statusFilteredStudents
                    "
                    :columns="
                        columns
                    "
                    :components="
                        availableComponents
                    "
                    component-field="component"
                    component-filter-label="Components"
                    :show-component-filter="true"
                    :actions="[
                        'view',
                    ]"
                    empty-message="No students found."
                    @view="
                        viewStudent
                    "
                />

            </section>

        </main>

    </UniversityAdminDashLayout>

</template>


<style scoped>

@import url('https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Libre+Baskerville:wght@400;700&display=swap');


/*
|--------------------------------------------------------------------------
| DESIGN SYSTEM
|--------------------------------------------------------------------------
|
| NO GRADIENTS.
| NO :deep().
| NO UsersLayout overrides.
|
*/

.student-registration-page {
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

    width: 100%;
    min-width: 0;
    min-height: 100%;

    padding:
        34px
        34px
        56px;

    box-sizing: border-box;

    background:
        var(--cream);

    color:
        var(--dark);

    font-family:
        'Atkinson Hyperlegible',
        Arial,
        Helvetica,
        sans-serif;
}


/*
|--------------------------------------------------------------------------
| ANNOUNCEMENT-STYLE MASTHEAD
|--------------------------------------------------------------------------
*/

.dispatch-masthead {
    position: relative;

    width: 100%;
    max-width: 1240px;

    min-height: 180px;

    margin:
        0
        auto
        26px;

    padding:
        30px
        32px
        45px;

    box-sizing: border-box;

    display: grid;

    grid-template-columns:
        74px
        minmax(
            0,
            1fr
        )
        auto;

    align-items: center;

    gap: 22px;

    overflow: hidden;

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
| MASTHEAD TOP ACCENT
|--------------------------------------------------------------------------
*/

.dispatch-masthead::before {
    content: "";

    position: absolute;

    top: 0;
    right: 0;

    width: 190px;
    height: 9px;

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
| MASTHEAD ICON
|--------------------------------------------------------------------------
*/

.masthead-mark {
    position: relative;
    z-index: 2;

    width: 66px;
    height: 82px;

    display: flex;

    align-items: center;
    justify-content: center;

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
| MASTHEAD COPY
|--------------------------------------------------------------------------
*/

.masthead-copy {
    position: relative;
    z-index: 2;

    min-width: 0;
}


.masthead-kicker {
    margin-bottom: 7px;

    display: inline-flex;

    align-items: center;

    gap: 7px;

    color:
        var(--maroon);

    font-size: 13px;

    font-weight: 700;

    letter-spacing: 1.3px;
}


.masthead-copy h1 {
    margin: 0;

    color:
        var(--dark-teal);

    font-family:
        'Libre Baskerville',
        Georgia,
        'Times New Roman',
        serif;

    font-size:
        clamp(
            38px,
            4vw,
            54px
        );

    font-weight: 700;

    line-height: 1.1;

    letter-spacing: -0.8px;
}


.masthead-copy p {
    max-width: 760px;

    margin:
        12px
        0
        0;

    color:
        #3C4B50;

    font-size: 16px;

    line-height: 1.6;
}


/*
|--------------------------------------------------------------------------
| MASTHEAD META
|--------------------------------------------------------------------------
*/

.masthead-meta {
    position: relative;
    z-index: 2;

    min-width: 260px;

    padding:
        16px
        19px;

    display: flex;

    align-items: stretch;

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
    flex: 1;

    display: flex;

    flex-direction: column;

    gap: 5px;
}


.masthead-meta-item span {
    color:
        #526066;

    font-size: 12px;

    font-weight: 700;

    letter-spacing: 0.7px;
}


.masthead-meta-item strong {
    color:
        var(--dark-teal);

    font-family:
        'Libre Baskerville',
        Georgia,
        serif;

    font-size: 20px;

    font-weight: 700;
}


.masthead-meta-item:first-child strong {
    color:
        var(--green);
}


.masthead-meta-item:last-child strong {
    color:
        var(--maroon);
}


.masthead-rule {
    width: 1px;

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
    position: absolute;
    z-index: 2;

    right: 32px;
    bottom: 14px;
    left: 32px;

    padding-top: 9px;

    border-top:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.14
        );

    display: flex;

    justify-content: space-between;

    gap: 20px;

    color:
        #5B666A;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 0.8px;
}


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

.statistics-area {
    width: 100%;
    max-width: 1240px;

    margin:
        0
        auto
        22px;

    display: grid;

    grid-template-columns:
        repeat(
            5,
            minmax(
                0,
                1fr
            )
        );

    gap: 13px;
}


.stat-card {
    min-width: 0;

    min-height: 128px;

    padding:
        18px;

    box-sizing: border-box;

    border:
        1px
        solid
        rgba(
            190,
            190,
            190,
            0.78
        );

    border-radius: 16px;

    display: flex;

    align-items: center;

    gap: 14px;

    background:
        var(--white);

    position: relative;
    overflow: hidden;

    box-shadow:
        0
        7px
        16px
        rgba(
            13,
            23,
            27,
            0.035
        );

    transition:
        transform
        0.15s
        ease,
        box-shadow
        0.15s
        ease;
}


.stat-card:hover {
    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        11px
        20px
        rgba(
            13,
            23,
            27,
            0.065
        );
}


.stat-icon {
    width: 55px;
    height: 55px;

    flex:
        0
        0
        55px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

    font-weight: 700;
}


.stat-content {
    min-width: 0;
}


.stat-content > span {
    display: block;

    color:
        var(--dark);

    font-size: 18px;

    line-height: 1.25;

    font-weight: 700;
}


.stat-content strong {
    display: block;

    margin-top: 4px;

    color:
        var(--near-black);

    font-size: 34px;

    line-height: 1;

    font-weight: 700;
}


.stat-content small {
    display: block;

    margin-top: 8px;

    color:
        var(--dark-teal);

    font-size: 14px;

    line-height: 1.4;

    font-weight: 400;
}



.stat-card::after {
    content: "";

    position: absolute;

    right: 0;
    bottom: 0;

    width: 44px;
    height: 5px;

    border-radius:
        5px
        0
        0
        0;
}


.stat-card--cwts::after {
    background:
        var(--dark-teal);
}


.stat-card--rotc::after {
    background:
        var(--maroon);
}


.stat-card--lts::after {
    background:
        var(--orange);
}


.stat-card--pending::after {
    background:
        var(--yellow);
}


.stat-card--approved::after {
    background:
        var(--green);
}


/*
|--------------------------------------------------------------------------
| CWTS
|--------------------------------------------------------------------------
*/

.stat-card--cwts {
    border-top:
        5px
        solid
        var(--dark-teal);
}


.stat-card--cwts
.stat-icon {
    background:
        rgba(
            35,
            62,
            71,
            0.11
        );

    color:
        var(--dark-teal);
}


/*
|--------------------------------------------------------------------------
| ROTC
|--------------------------------------------------------------------------
*/

.stat-card--rotc {
    border-top:
        5px
        solid
        var(--maroon);
}


.stat-card--rotc
.stat-icon {
    background:
        rgba(
            84,
            16,
            15,
            0.09
        );

    color:
        var(--maroon);
}


/*
|--------------------------------------------------------------------------
| LTS
|--------------------------------------------------------------------------
*/

.stat-card--lts {
    border-top:
        5px
        solid
        var(--orange);
}


.stat-card--lts
.stat-icon {
    background:
        rgba(
            217,
            146,
            2,
            0.13
        );

    color:
        var(--orange);
}


/*
|--------------------------------------------------------------------------
| PENDING
|--------------------------------------------------------------------------
*/

.stat-card--pending {
    border-top:
        5px
        solid
        var(--yellow);
}


.stat-card--pending
.stat-icon {
    background:
        rgba(
            255,
            189,
            54,
            0.21
        );

    color:
        var(--orange);
}


/*
|--------------------------------------------------------------------------
| APPROVED
|--------------------------------------------------------------------------
*/

.stat-card--approved {
    border-top:
        5px
        solid
        var(--green);
}


.stat-card--approved
.stat-icon {
    background:
        rgba(
            88,
            118,
            28,
            0.12
        );

    color:
        var(--green);
}


/*
|--------------------------------------------------------------------------
| SHARED PANELS
|--------------------------------------------------------------------------
*/

.deadline-panel,
.current-period-panel {
    width: 100%;
    max-width: 1240px;

    margin:
        0
        auto
        18px;

    box-sizing: border-box;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.14
        );

    border-radius: 18px;

    background:
        var(--white);

    box-shadow:
        0
        7px
        18px
        rgba(
            13,
            23,
            27,
            0.04
        );
}


/*
|--------------------------------------------------------------------------
| DEADLINE COLLAPSED
|--------------------------------------------------------------------------
*/

.deadline-collapsed {
    min-height: 116px;

    padding:
        20px
        22px;

    box-sizing: border-box;

    display: flex;

    align-items: center;

    justify-content:
        space-between;

    gap: 24px;
}


.deadline-intro {
    min-width: 0;

    display: flex;

    align-items: center;

    gap: 16px;
}


.deadline-icon {
    width: 60px;
    height: 60px;

    flex:
        0
        0
        60px;

    border:
        1px
        solid
        rgba(
            217,
            146,
            2,
            0.24
        );

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(
            255,
            189,
            54,
            0.18
        );

    color:
        var(--orange);
}


.section-kicker {
    display: block;

    margin-bottom: 5px;

    color:
        var(--maroon);

    font-size: 13px;

    line-height: 1.3;

    font-weight: 700;

    letter-spacing: 0.7px;
}


.deadline-intro h2,
.section-header h2 {
    margin: 0;

    color:
        var(--near-black);

    font-family:
        'Libre Baskerville',
        Georgia,
        serif;

    font-size: 27px;

    line-height: 1.25;
}


.deadline-intro p,
.section-header p {
    max-width: 720px;

    margin:
        8px
        0
        0;

    color:
        var(--dark-teal);

    font-size: 16px;

    line-height: 1.6;
}


/*
|--------------------------------------------------------------------------
| SET DEADLINE BUTTON
|--------------------------------------------------------------------------
*/

.set-deadline-button {
    min-width: 184px;

    min-height: 51px;

    padding:
        0
        18px;

    border:
        0;

    border-radius: 13px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 9px;

    background:
        var(--green);

    color:
        var(--white);

    font-family:
        'Atkinson Hyperlegible',
        Arial,
        sans-serif;

    font-size: 17px;

    font-weight: 700;

    cursor: pointer;

    transition:
        transform
        0.15s
        ease,
        box-shadow
        0.15s
        ease;
}


.set-deadline-button:hover {
    transform:
        translateY(
            -1px
        );

    box-shadow:
        0
        7px
        15px
        rgba(
            88,
            118,
            28,
            0.22
        );
}


/*
|--------------------------------------------------------------------------
| EDITOR
|--------------------------------------------------------------------------
*/

.deadline-editor,
.current-period-panel {
    padding:
        22px;

}


.section-header {
    display: flex;

    align-items: flex-start;

    justify-content:
        space-between;

    gap: 18px;
}


.close-button {
    width: 43px;
    height: 43px;

    flex:
        0
        0
        43px;

    padding: 0;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.18
        );

    border-radius: 12px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    background:
        var(--white);

    color:
        var(--maroon);

    cursor: pointer;
}


/*
|--------------------------------------------------------------------------
| DEADLINE FIELDS
|--------------------------------------------------------------------------
*/

.deadline-fields,
.current-deadline-fields {
    margin-top: 19px;

    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(
                0,
                1fr
            )
        );

    gap: 14px;
}


.deadline-field {
    min-width: 0;

    display: flex;

    flex-direction: column;
}


.deadline-field > span,
.deadline-value > span {
    display: block;

    margin-bottom: 8px;

    color:
        var(--dark);

    font-size: 16px;

    line-height: 1.4;

    font-weight: 700;
}


.deadline-field input {
    width: 100%;
    height: 52px;

    padding:
        0
        14px;

    box-sizing: border-box;

    border:
        1px
        solid
        var(--gray);

    border-radius: 12px;

    outline: 0;

    background:
        var(--white);

    color:
        var(--near-black);

    font-family:
        'Atkinson Hyperlegible',
        Arial,
        sans-serif;

    font-size: 17px;

    font-weight: 400;
}


.deadline-field input:focus {
    border-color:
        var(--dark-teal);

    box-shadow:
        0
        0
        0
        3px
        rgba(
            35,
            62,
            71,
            0.09
        );
}


.field-error {
    margin-top: 6px;

    color:
        var(--maroon);

    font-size: 14px;

    line-height: 1.4;

    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| SAVE BUTTON
|--------------------------------------------------------------------------
*/

.save-deadline-button {
    width: 100%;

    min-height: 52px;

    margin-top: 17px;

    padding:
        0
        18px;

    border: 0;

    border-radius: 13px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 9px;

    background:
        var(--dark-teal);

    color:
        var(--white);

    font-family:
        'Atkinson Hyperlegible',
        Arial,
        sans-serif;

    font-size: 17px;

    font-weight: 700;

    cursor: pointer;
}


.save-deadline-button:disabled {
    opacity: 0.58;

    cursor: not-allowed;
}


/*
|--------------------------------------------------------------------------
| CURRENT PERIOD
|--------------------------------------------------------------------------
*/

.current-period-panel {
    border-left:
        6px
        solid
        var(--maroon);
}


.remove-deadline-button {
    min-height: 43px;

    padding:
        0
        14px;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.20
        );

    border-radius: 12px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    background:
        rgba(
            84,
            16,
            15,
            0.06
        );

    color:
        var(--maroon);

    font-family:
        'Atkinson Hyperlegible',
        Arial,
        sans-serif;

    font-size: 15px;

    font-weight: 700;

    cursor: pointer;
}


/*
|--------------------------------------------------------------------------
| CURRENT DEADLINE VALUES
|--------------------------------------------------------------------------
*/

.deadline-value {
    min-width: 0;
}


.deadline-value > div {
    min-height: 57px;

    padding:
        0
        15px;

    box-sizing: border-box;

    border-radius: 12px;

    display: flex;

    align-items: center;

    gap: 10px;
}


.deadline-value strong {
    color:
        var(--near-black);

    font-size: 17px;

    line-height: 1.4;

    font-weight: 700;
}


.deadline-value--submitted > div {
    border:
        1px
        solid
        rgba(
            217,
            146,
            2,
            0.33
        );

    background:
        rgba(
            255,
            189,
            54,
            0.14
        );

    color:
        var(--orange);
}


.deadline-value--editable > div {
    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.22
        );

    background:
        rgba(
            35,
            62,
            71,
            0.07
        );

    color:
        var(--dark-teal);
}


.deadline-value--time > div {
    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.28
        );

    background:
        rgba(
            88,
            118,
            28,
            0.08
        );

    color:
        var(--green);
}


/*
|--------------------------------------------------------------------------
| STUDENT DIRECTORY
|--------------------------------------------------------------------------
*/

.student-directory-section {
    width: 100%;
    max-width: 1240px;

    margin:
        28px
        auto
        0;
}


/*
|--------------------------------------------------------------------------
| STATUS FILTER BAR
|--------------------------------------------------------------------------
*/

.registration-filter-bar {
    margin-bottom: 14px;

    padding:
        17px
        18px;

    box-sizing: border-box;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.15
        );

    border-radius: 17px;

    display: flex;

    align-items: center;

    justify-content:
        space-between;

    gap: 20px;

    background:
        var(--white);

    box-shadow:
        0
        7px
        16px
        rgba(
            13,
            23,
            27,
            0.035
        );
}


.registration-filter-title {
    min-width: 0;

    display: flex;

    align-items: center;

    gap: 13px;
}


.registration-filter-icon {
    width: 51px;
    height: 51px;

    flex:
        0
        0
        51px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.16
        );

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        var(--cream);

    color:
        var(--dark-teal);
}


.registration-filter-title span {
    display: block;

    margin-bottom: 2px;

    color:
        var(--orange);

    font-size: 13px;

    line-height: 1.3;

    font-weight: 700;

    letter-spacing: 0.7px;
}


.registration-filter-title strong {
    display: block;

    color:
        var(--near-black);

    font-size: 21px;

    line-height: 1.3;

    font-weight: 700;
}


.registration-filter-title p {
    margin:
        4px
        0
        0;

    color:
        var(--dark-teal);

    font-size: 15px;

    line-height: 1.5;
}


/*
|--------------------------------------------------------------------------
| STATUS TABS
|--------------------------------------------------------------------------
*/

.status-tabs {
    display: flex;

    align-items: center;

    gap: 8px;
}


.status-tab {
    min-height: 44px;

    padding:
        0
        15px;

    border:
        1px
        solid
        var(--gray);

    border-radius: 11px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    background:
        var(--white);

    font-family:
        'Atkinson Hyperlegible',
        Arial,
        sans-serif;

    font-size: 15px;

    font-weight: 700;

    cursor: pointer;

    transition:
        transform
        0.14s
        ease,
        background
        0.14s
        ease,
        color
        0.14s
        ease,
        border-color
        0.14s
        ease;
}


.status-tab:hover {
    transform:
        translateY(
            -1px
        );
}


/*
|--------------------------------------------------------------------------
| ALL
|--------------------------------------------------------------------------
*/

.status-tab--all {
    color:
        var(--dark-teal);
}


.status-tab--all:hover {
    border-color:
        var(--dark-teal);
}


.status-tab--all.active {
    border-color:
        var(--dark-teal);

    background:
        var(--dark-teal);

    color:
        var(--white);
}


/*
|--------------------------------------------------------------------------
| PENDING
|--------------------------------------------------------------------------
*/

.status-tab--pending {
    color:
        var(--orange);
}


.status-tab--pending:hover {
    border-color:
        var(--orange);
}


.status-tab--pending.active {
    border-color:
        var(--orange);

    background:
        var(--orange);

    color:
        var(--white);
}


/*
|--------------------------------------------------------------------------
| APPROVED
|--------------------------------------------------------------------------
*/

.status-tab--approved {
    color:
        var(--green);
}


.status-tab--approved:hover {
    border-color:
        var(--green);
}


.status-tab--approved.active {
    border-color:
        var(--green);

    background:
        var(--green);

    color:
        var(--white);
}


/*
|--------------------------------------------------------------------------
| FOCUS ACCESSIBILITY
|--------------------------------------------------------------------------
*/

button:focus-visible,
input:focus-visible {
    outline:
        3px
        solid
        var(--yellow);

    outline-offset:
        3px;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - 1100
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1100px
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

        width: 100%;

        min-width: 0;

        box-sizing: border-box;
    }

    .statistics-area {
        grid-template-columns:
            repeat(
                3,
                minmax(
                    0,
                    1fr
                )
            );
    }


}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 800px
) {


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
        width: 54px;
        height: 68px;
    }


    .masthead-copy h1 {
        font-size: 38px;
    }


    .masthead-bottom-line {
        right: 22px;
        left: 22px;

        font-size: 10px;
    }

    .student-registration-page {
        padding:
            26px
            20px
            44px;
    }


    .statistics-area {
        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );
    }


    .deadline-collapsed {
        align-items:
            stretch;

        flex-direction:
            column;
    }


    .set-deadline-button {
        width: 100%;
    }


    .deadline-fields,
    .current-deadline-fields {
        grid-template-columns:
            1fr;
    }


    .registration-filter-bar {
        align-items:
            flex-start;

        flex-direction:
            column;
    }


    .status-tabs {
        width: 100%;
    }


    .status-tab {
        flex: 1;
    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - MOBILE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 560px
) {


    .dispatch-masthead {
        min-height: 0;

        grid-template-columns:
            1fr;

        padding:
            18px
            18px
            48px;
    }


    .masthead-mark {
        width: 54px;
        height: 54px;
    }


    .masthead-copy h1 {
        font-size: 34px;
    }


    .masthead-copy p {
        font-size: 15px;
    }


    .masthead-meta {
        grid-column: auto;

        min-width: 0;
    }


    .masthead-bottom-line
    span:last-child {
        display: none;
    }

    .student-registration-page {
        padding:
            18px
            11px
            34px;
    }



    .statistics-area {
        grid-template-columns:
            1fr;
    }


    .stat-card {
        min-height:
            108px;
    }


    .stat-content > span {
        font-size:
            17px;
    }


    .stat-content strong {
        font-size:
            31px;
    }


    .stat-content small {
        font-size:
            14px;
    }


    .deadline-panel,
    .current-period-panel {
        border-radius:
            15px;
    }


    .deadline-collapsed,
    .deadline-editor,
    .current-period-panel {
        padding:
            17px;
    }


    .deadline-intro {
        align-items:
            flex-start;
    }


    .deadline-icon {
        width: 52px;
        height: 52px;

        flex-basis:
            52px;
    }


    .deadline-intro h2,
    .section-header h2 {
        font-size:
            23px;
    }


    .deadline-intro p,
    .section-header p {
        font-size:
            15px;
    }


    .section-header {
        align-items:
            flex-start;
    }


    .remove-deadline-button span {
        display:
            none;
    }


    .remove-deadline-button {
        width: 43px;

        padding: 0;
    }


    .registration-filter-title {
        align-items:
            flex-start;
    }


    .registration-filter-title strong {
        font-size:
            19px;
    }


    .registration-filter-title p {
        font-size:
            14px;
    }


    .status-tabs {
        display: grid;

        grid-template-columns:
            1fr;
    }


    .status-tab {
        width: 100%;
    }

}

</style>