<script setup>

import {
    computed,
} from 'vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import {
    Eye,
    Download,
    Pencil,
    Sparkles,
    UsersRound,
} from 'lucide-vue-next';

import Admin_IC_Layout
    from '@/layouts/Admin_IC_Layout.vue';

import UsersLayout
    from '@/layouts/usersLayout.vue';


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

    canManage: {
        type: Boolean,
        default: false,
    },

    students: {
        type: [
            Array,
            Object,
        ],

        default: () => [],
    },

});


/*
|--------------------------------------------------------------------------
| Current Role
|--------------------------------------------------------------------------
*/

const currentRole =
    computed(() => {

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
| Role Label
|--------------------------------------------------------------------------
*/

const roleLabel =
    computed(() => {

        switch (
            currentRole.value
        ) {

            case 'instructor':

                return 'INSTRUCTOR';


            case 'coordinator-announcement':

                return 'COORDINATOR - ANNOUNCEMENT';


            case 'coordinator-attendance':

                return 'COORDINATOR - ATTENDANCE';


            case 'coordinator-schedule':

                return 'COORDINATOR - SCHEDULE';


            case 'university-admin':

                return 'UNIVERSITY ADMIN';


            default:

                return String(
                    currentRole.value
                    ||
                    'NSTP USER'
                )
                    .replace(
                        /-/g,
                        ' '
                    )
                    .toUpperCase();

        }

    });


/*
|--------------------------------------------------------------------------
| Component Locked
|--------------------------------------------------------------------------
*/

const componentLocked =
    computed(() => {

        return (
            currentRole.value !==
            'university-admin'
        );

    });


/*
|--------------------------------------------------------------------------
| Active Component
|--------------------------------------------------------------------------
*/

const componentLabel =
    computed(() => {

        const component =
            String(
                props.activeComponent
                ??
                ''
            )
                .trim()
                .toUpperCase();


        return (
            component
            ||
            'ALL'
        );

    });


/*
|--------------------------------------------------------------------------
| Download Student List PDF
|--------------------------------------------------------------------------
*/

const downloadStudentListPdf = () => {
    const params =
        new URLSearchParams();

    if (
        componentLabel.value
        &&
        componentLabel.value !== 'ALL'
    ) {
        params.set(
            'component',
            componentLabel.value
        );
    }

    const suffix =
        params.toString()
            ? `?${params.toString()}`
            : '';

    window.open(
        `/instructor-coordinator/exports/students.pdf${suffix}`,
        '_blank',
        'noopener,noreferrer'
    );
};


/*
|--------------------------------------------------------------------------
| Student Source
|--------------------------------------------------------------------------
*/

const studentSource =
    computed(() => {

        if (
            Array.isArray(
                props.students
            )
        ) {

            return props.students;

        }


        if (
            Array.isArray(
                props.students?.data
            )
        ) {

            return props.students.data;

        }


        return [];

    });


/*
|--------------------------------------------------------------------------
| Build Full Name
|--------------------------------------------------------------------------
*/

const buildFullName = (
    student
) => {

    const existing =
        String(
            student?.full_name
            ??
            student?.name
            ??
            ''
        )
            .trim();


    if (
        existing !==
        ''
    ) {

        return existing;

    }


    const surname =
        String(
            student?.surname
            ??
            student?.last_name
            ??
            ''
        )
            .trim();


    const firstName =
        String(
            student?.first_name
            ??
            ''
        )
            .trim();


    const middleName =
        String(
            student?.middle_name
            ??
            ''
        )
            .trim();


    const middleInitial =
        middleName !==
        ''
            ? `${middleName.charAt(0).toUpperCase()}.`
            : '';


    if (
        surname !==
        ''
        &&
        firstName !==
        ''
    ) {

        return [
            `${surname},`,
            firstName,
            middleInitial,
        ]
            .filter(
                Boolean
            )
            .join(
                ' '
            );

    }


    return (
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
        ||
        '-'
    );

};


/*
|--------------------------------------------------------------------------
| Year & Section
|--------------------------------------------------------------------------
*/

const buildYearSection = (
    student
) => {

    const existing =
        String(
            student?.year_section
            ??
            student?.year_and_section
            ??
            ''
        )
            .trim();


    if (
        existing !==
        ''
    ) {

        return existing;

    }


    const year =
        String(
            student?.year_level
            ??
            student?.year
            ??
            ''
        )
            .trim();


    const section =
        String(
            student?.section
            ??
            ''
        )
            .trim();


    if (
        year !==
        ''
        &&
        section !==
        ''
    ) {

        return `${year} / ${section}`;

    }


    if (
        year !==
        ''
    ) {

        return year;

    }


    if (
        section !==
        ''
    ) {

        return section;

    }


    return '-';

};


/*
|--------------------------------------------------------------------------
| Normalize Status
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
            'ACTIVE',
            'ENROLLED',
            'APPROVED',
            'CONFIRMED',
            'COMPLETED',
        ].includes(
            status
        )
    ) {

        return 'ACTIVE';

    }


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
            'DROPPED',
            'DROP OUT',
        ].includes(
            status
        )
    ) {

        return 'DROPOUT';

    }


    return (
        status
        ||
        'ACTIVE'
    );

};


/*
|--------------------------------------------------------------------------
| Status Display
|--------------------------------------------------------------------------
*/

const formatStatusLabel = (
    value
) => {

    const status =
        String(
            value
            ??
            ''
        )
            .trim()
            .toLowerCase();


    if (
        status ===
        ''
    ) {

        return '-';

    }


    return status.replace(
        /(^|\s)\S/g,
        letter =>
            letter.toUpperCase()
    );

};


/*
|--------------------------------------------------------------------------
| Normalize Students
|--------------------------------------------------------------------------
*/

const normalizedStudents =
    computed(() => {

        return studentSource
            .value
            .map(
                (
                    student,
                    index
                ) => {

                    return {

                        ...student,


                        id:
                            student?.id
                            ??
                            index,


                        id_number:
                            String(
                                student?.student_id_number
                                ??
                                student?.id_number
                                ??
                                student?.student_id
                                ??
                                student?.school_id
                                ??
                                '-'
                            )
                                .trim(),


                        full_name:
                            buildFullName(
                                student
                            ),


                        course:
                            String(
                                student?.course
                                ??
                                student?.program
                                ??
                                student?.degree_program
                                ??
                                '-'
                            )
                                .trim(),


                        year_section:
                            buildYearSection(
                                student
                            ),


                        component:
                            String(
                                student?.component
                                ??
                                student?.nstp_component
                                ??
                                '-'
                            )
                                .trim()
                                .toUpperCase(),


                        status:
                            normalizeStatus(
                                student?.status
                                ??
                                student?.student_status
                                ??
                                student?.enrollment_status
                                ??
                                student?.registration_status
                                ??
                                'ACTIVE'
                            ),


                        can_edit:
                            student?.can_edit
                            ??
                            props.canManage,

                    };

                }
            );

    });


/*
|--------------------------------------------------------------------------
| Visible Students
|--------------------------------------------------------------------------
*/

const visibleStudents =
    computed(() => {

        if (
            !componentLocked.value
        ) {

            return normalizedStudents.value;

        }


        const assignedComponent =
            componentLabel.value;


        if (
            assignedComponent ===
            'ALL'
        ) {

            return normalizedStudents.value;

        }


        return normalizedStudents
            .value
            .filter(
                student => {

                    return (
                        String(
                            student?.component
                            ??
                            ''
                        )
                            .trim()
                            .toUpperCase()
                        ===
                        assignedComponent
                    );

                }
            );

    });


/*
|--------------------------------------------------------------------------
| Component Options
|--------------------------------------------------------------------------
*/

const availableComponents =
    computed(() => {

        if (
            Array.isArray(
                props.componentOptions
            )
            &&
            props.componentOptions.length >
            0
        ) {

            return props.componentOptions
                .map(
                    component =>
                        String(
                            component
                        )
                            .trim()
                            .toUpperCase()
                )
                .filter(
                    component =>
                        component !==
                        'ALL'
                );

        }


        return [
            'LTS',
            'CWTS',
            'ROTC',
        ];

    });


/*
|--------------------------------------------------------------------------
| Table Columns
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
            '22%',

        align:
            'left',
    },


    {
        key:
            'course',

        label:
            'COURSE',

        width:
            '14%',
    },


    {
        key:
            'year_section',

        label:
            'YR & SECTION',

        width:
            '15%',
    },


    {
        key:
            'component',

        label:
            'COMPONENTS',

        width:
            '13%',
    },


    {
        key:
            'status',

        label:
            'STATUS',

        width:
            '15%',
    },


    {
        key:
            'action',

        label:
            'ACTION',

        width:
            '12%',
    },

];


/*
|--------------------------------------------------------------------------
| View Student
|--------------------------------------------------------------------------
*/

const viewStudent = (
    student
) => {

    const studentId =
        Number(
            student?.id
        );


    if (
        !Number.isInteger(
            studentId
        )
        ||
        studentId <=
        0
    ) {

        return;

    }


    router.visit(
        `/instructor-coordinator/students/${studentId}`,
        {
            preserveScroll:
                false,
        }
    );

};


/*
|--------------------------------------------------------------------------
| Edit Student
|--------------------------------------------------------------------------
*/

const editStudent = (
    student
) => {

    const studentId =
        Number(
            student?.id
        );


    if (
        !Number.isInteger(
            studentId
        )
        ||
        studentId <=
        0
        ||
        props.canManage !==
        true
    ) {

        return;

    }


    router.visit(
        `/instructor-coordinator/students/${studentId}/edit`,
        {
            preserveScroll:
                false,
        }
    );

};

</script>


<template>

    <Head
        title="Student Information"
    />


    <Admin_IC_Layout
        :user="props.user"
        :role="currentRole"
    >

        <main class="student-list-page">


            <!-- ========================================================
                 GREEN DOMINANT SCHEDULE-STYLE HEADER
            ========================================================= -->

            <section class="student-masthead">


                <!-- ICON -->

                <div class="masthead-mark">

                    <UsersRound
                        :size="30"
                        :stroke-width="2"
                    />

                </div>


                <!-- COPY -->

                <div class="masthead-copy">

                    <div class="masthead-kicker">

                        <Sparkles
                            :size="15"
                            :stroke-width="2"
                        />

                        <span>
                            NSTP STUDENT INFORMATION CENTER
                        </span>

                    </div>


                    <h1>
                        Enrolled Students
                    </h1>


                    <p>
                        View approved and enrolled students assigned
                        to your NSTP component.
                    </p>

                </div>


                <!-- META -->

                <div class="masthead-meta">


                    <!-- COMPONENT -->

                    <div class="masthead-meta-item">

                        <span>
                            COMPONENT
                        </span>


                        <strong>
                            {{ componentLabel }}
                        </strong>

                    </div>


                    <div class="masthead-rule"></div>


                    <!-- STUDENTS -->

                    <div class="masthead-meta-item">

                        <span>
                            STUDENTS
                        </span>


                        <strong>
                            {{
                                String(
                                    visibleStudents.length
                                ).padStart(
                                    2,
                                    '0'
                                )
                            }}
                        </strong>

                    </div>

                </div>


                <!-- FOOTER -->

                <div class="masthead-bottom-line">

                    <span>
                        SURIGAO DEL NORTE STATE UNIVERSITY
                    </span>


                    <span>
                        {{ roleLabel }} · NSTP STUDENT MANAGEMENT
                    </span>

                </div>

            </section>


            <div class="student-list-export-row">
                <button
                    type="button"
                    class="student-list-export-button"
                    @click="downloadStudentListPdf"
                >
                    <Download :size="18" :stroke-width="2.2" />
                    <span>Download Student List PDF</span>
                </button>
            </div>


            <!-- ========================================================
                 USERS TABLE
            ========================================================= -->

            <section class="student-table-shell">

                <div class="table-section-accent"></div>


                <UsersLayout

                    title="LIST OF ENROLLED STUDENTS"

                    :show-add-button="false"

                    search-placeholder="Search Enrolled Students..."

                    :search-fields="[
                        'id_number',
                        'full_name',
                        'course',
                        'year_section',
                        'component',
                        'status',
                    ]"

                    :rows="
                        visibleStudents
                    "

                    :columns="
                        columns
                    "

                    :components="
                        availableComponents
                    "

                    component-field="component"

                    component-filter-label="Components"

                    :show-component-filter="
                        !componentLocked
                    "

                    :actions="[]"

                    empty-message="No enrolled students found."

                >


                    <!-- ====================================================
                         CUSTOM CELLS
                    ===================================================== -->

                    <template
                        #cell="{
                            row,
                            column,
                            value
                        }"
                    >


                        <!-- ACTION -->

                        <div
                            v-if="
                                column.key ===
                                'action'
                            "
                            class="student-actions"
                        >


                            <!-- VIEW -->

                            <button

                                type="button"

                                class="
                                    action-icon-button
                                    view-button
                                "

                                title="View Student Profile"

                                :aria-label="
                                    `View ${row.full_name}`
                                "

                                @click.stop="
                                    viewStudent(
                                        row
                                    )
                                "

                            >

                                <Eye
                                    :size="22"
                                    :stroke-width="2.4"
                                />

                            </button>


                            <!-- EDIT -->

                            <button
                                v-if="
                                    row.can_edit ===
                                    true
                                "

                                type="button"

                                class="
                                    action-icon-button
                                    edit-button
                                "

                                title="Edit Student Profile"

                                :aria-label="
                                    `Edit ${row.full_name}`
                                "

                                @click.stop="
                                    editStudent(
                                        row
                                    )
                                "

                            >

                                <Pencil
                                    :size="20"
                                    :stroke-width="2.4"
                                />

                            </button>

                        </div>


                        <!-- STATUS -->

                        <span
                            v-else-if="
                                column.key ===
                                'status'
                            "
                            class="student-status"
                            :class="{
                                'student-status--active':
                                    row.status ===
                                    'ACTIVE',

                                'student-status--warning':
                                    row.status ===
                                    'WARNING FOR DROPOUT',

                                'student-status--dropout':
                                    row.status ===
                                    'DROPOUT',
                            }"
                        >

                            {{
                                formatStatusLabel(
                                    value
                                )
                            }}

                        </span>


                        <!-- COMPONENT -->

                        <span
                            v-else-if="
                                column.key ===
                                'component'
                            "
                            class="component-pill"
                        >

                            {{
                                value
                                ??
                                '-'
                            }}

                        </span>


                        <!-- NORMAL CELL -->

                        <span
                            v-else
                            class="student-cell-value"
                        >

                            {{
                                value
                                ??
                                '-'
                            }}

                        </span>

                    </template>

                </UsersLayout>

            </section>

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

.student-list-page {

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


    --green-deep:
        #455F15;

    --green-soft:
        rgba(
            88,
            118,
            28,
            0.10
        );

    --green-border:
        rgba(
            88,
            118,
            28,
            0.24
        );


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
        var(
            --cream
        );


    color:
        var(
            --dark
        );


    font-family:
        var(
            --font-ui
        );


    font-size:
        16px;


    line-height:
        1.6;

}


/*
|--------------------------------------------------------------------------
| GREEN DOMINANT MASTHEAD
|--------------------------------------------------------------------------
*/

.student-masthead {

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
            88,
            118,
            28,
            0.27
        );


    border-radius:
        6px
        24px
        6px
        24px;


    /*
    |--------------------------------------------------------------------------
    | Green is dominant here.
    |--------------------------------------------------------------------------
    */

    background:
        linear-gradient(
            105deg,
            #FFFFFF 0%,
            #FFFFFF 57%,
            rgba(
                88,
                118,
                28,
                0.13
            ) 100%
        );


    box-shadow:
        0
        16px
        36px
        rgba(
            88,
            118,
            28,
            0.10
        );

}


/*
|--------------------------------------------------------------------------
| TOP ACCENT
|--------------------------------------------------------------------------
|
| Dominant:
|
| #58761C
|
*/

.student-masthead::before {

    content:
        "";


    position:
        absolute;


    top:
        0;


    right:
        0;


    width:
        245px;


    height:
        9px;


    background:
        linear-gradient(
            90deg,
            #58761C 0%,
            #58761C 65%,
            #FFBD36 82%,
            #D99202 100%
        );

}


/*
|--------------------------------------------------------------------------
| NO WATERMARK
|--------------------------------------------------------------------------
|
| No ::after decorative watermark is used.
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


    /*
    |--------------------------------------------------------------------------
    | GREEN DOMINANT
    |--------------------------------------------------------------------------
    */

    background:
        #58761C;


    color:
        #FFFFFF;


    box-shadow:
        8px
        8px
        0
        rgba(
            255,
            189,
            54,
            0.45
        );

}


/*
|--------------------------------------------------------------------------
| HEADER COPY
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


/*
|--------------------------------------------------------------------------
| Kicker
|--------------------------------------------------------------------------
*/

.masthead-kicker {

    margin-bottom:
        7px;


    display:
        inline-flex;


    align-items:
        center;


    gap:
        7px;


    /*
    |--------------------------------------------------------------------------
    | Supporting maroon accent
    |--------------------------------------------------------------------------
    */

    color:
        #54100F;


    font-size:
        13px;


    font-weight:
        700;


    letter-spacing:
        1.2px;

}


.masthead-kicker svg {

    color:
        #54100F;

}


/*
|--------------------------------------------------------------------------
| Main Title
|--------------------------------------------------------------------------
*/

.masthead-copy h1 {

    margin:
        0;


    /*
    |--------------------------------------------------------------------------
    | GREEN DOMINANT
    |--------------------------------------------------------------------------
    */

    color:
        #58761C;


    font-family:
        var(
            --font-display
        );


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


/*
|--------------------------------------------------------------------------
| Description
|--------------------------------------------------------------------------
*/

.masthead-copy p {

    max-width:
        690px;


    margin:
        12px
        0
        0;


    color:
        #334239;


    font-size:
        16px;


    line-height:
        1.65;

}


/*
|--------------------------------------------------------------------------
| META PANEL
|--------------------------------------------------------------------------
*/

.masthead-meta {

    position:
        relative;


    z-index:
        2;


    min-width:
        285px;


    padding:
        16px
        19px;


    display:
        flex;


    align-items:
        stretch;


    /*
    |--------------------------------------------------------------------------
    | GREEN BORDER
    |--------------------------------------------------------------------------
    */

    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.27
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
            0.90
        );


    box-shadow:
        inset
        4px
        0
        0
        rgba(
            88,
            118,
            28,
            0.13
        );

}


/*
|--------------------------------------------------------------------------
| META ITEM
|--------------------------------------------------------------------------
*/

.masthead-meta-item {

    flex:
        1;


    min-width:
        0;


    display:
        flex;


    flex-direction:
        column;


    gap:
        5px;

}


.masthead-meta-item span {

    color:
        #50603E;


    font-size:
        12px;


    font-weight:
        700;


    letter-spacing:
        0.7px;

}


/*
|--------------------------------------------------------------------------
| Meta Value
|--------------------------------------------------------------------------
*/

.masthead-meta-item strong {

    /*
    |--------------------------------------------------------------------------
    | GREEN DOMINANT
    |--------------------------------------------------------------------------
    */

    color:
        #58761C;


    font-family:
        var(
            --font-display
        );


    font-size:
        21px;


    font-weight:
        700;


    line-height:
        1.3;

}


/*
|--------------------------------------------------------------------------
| Meta Divider
|--------------------------------------------------------------------------
*/

.masthead-rule {

    width:
        1px;


    margin:
        0
        18px;


    background:
        rgba(
            88,
            118,
            28,
            0.25
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
            88,
            118,
            28,
            0.19
        );


    display:
        flex;


    justify-content:
        space-between;


    gap:
        20px;


    color:
        #4D5945;


    font-size:
        11px;


    font-weight:
        700;


    letter-spacing:
        0.75px;

}


.masthead-bottom-line
span:last-child {

    color:
        #58761C;

}


/*
|--------------------------------------------------------------------------
| TABLE SECTION SHELL
|--------------------------------------------------------------------------
*/

.student-table-shell {

    position:
        relative;


    width:
        100%;


    min-width:
        0;


    padding-top:
        5px;

}


/*
|--------------------------------------------------------------------------
| GREEN TABLE ACCENT
|--------------------------------------------------------------------------
*/

.table-section-accent {

    width:
        100%;


    height:
        5px;


    margin-bottom:
        10px;


    border-radius:
        999px;


    background:
        linear-gradient(
            90deg,
            #58761C 0%,
            #58761C 70%,
            #FFBD36 70%,
            #FFBD36 84%,
            #D99202 84%,
            #D99202 93%,
            #54100F 93%,
            #54100F 100%
        );

}


/*
|--------------------------------------------------------------------------
| STUDENT CELL
|--------------------------------------------------------------------------
*/

.student-cell-value {

    color:
        #263329;


    font-size:
        15px;

}


/*
|--------------------------------------------------------------------------
| COMPONENT PILL
|--------------------------------------------------------------------------
*/

.component-pill {

    min-height:
        32px;


    padding:
        5px
        12px;


    box-sizing:
        border-box;


    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.24
        );


    border-radius:
        999px;


    display:
        inline-flex;


    align-items:
        center;


    justify-content:
        center;


    background:
        rgba(
            88,
            118,
            28,
            0.10
        );


    color:
        #455F15;


    font-size:
        13px;


    font-weight:
        700;

}


/*
|--------------------------------------------------------------------------
| STUDENT STATUS
|--------------------------------------------------------------------------
*/

.student-status {

    min-height:
        32px;


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


    justify-content:
        center;


    font-size:
        13px;


    font-weight:
        700;


    line-height:
        1.25;


    text-align:
        center;

}


/*
|--------------------------------------------------------------------------
| ACTIVE
|--------------------------------------------------------------------------
*/

.student-status--active {

    background:
        rgba(
            88,
            118,
            28,
            0.14
        );


    color:
        #455F15;

}


/*
|--------------------------------------------------------------------------
| WARNING
|--------------------------------------------------------------------------
*/

.student-status--warning {

    background:
        rgba(
            217,
            146,
            2,
            0.15
        );


    color:
        #795100;

}


/*
|--------------------------------------------------------------------------
| DROPOUT
|--------------------------------------------------------------------------
*/

.student-status--dropout {

    background:
        rgba(
            84,
            16,
            15,
            0.11
        );


    color:
        #54100F;

}


/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/

.student-actions {

    width:
        100%;


    display:
        flex;


    align-items:
        center;


    justify-content:
        center;


    gap:
        9px;

}


/*
|--------------------------------------------------------------------------
| ACTION BUTTON
|--------------------------------------------------------------------------
*/

.action-icon-button {

    width:
        39px;


    height:
        39px;


    display:
        inline-flex;


    align-items:
        center;


    justify-content:
        center;


    flex-shrink:
        0;


    padding:
        0;


    border:
        1px
        solid
        transparent;


    border-radius:
        6px
        11px
        6px
        11px;


    background:
        transparent;


    cursor:
        pointer;


    transition:
        background-color
        0.18s
        ease,
        color
        0.18s
        ease,
        border-color
        0.18s
        ease,
        transform
        0.18s
        ease,
        box-shadow
        0.18s
        ease;

}


.action-icon-button:hover {

    color:
        var(
            --white
        );


    transform:
        translateY(
            -2px
        );

}


.action-icon-button:active {

    transform:
        scale(
            0.94
        );

}


/*
|--------------------------------------------------------------------------
| VIEW BUTTON
|--------------------------------------------------------------------------
|
| Green is the primary action.
|
*/

.view-button {

    border-color:
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
        #58761C;

}


.view-button:hover {

    border-color:
        #58761C;


    background:
        #58761C;


    color:
        #FFFFFF;


    box-shadow:
        0
        6px
        14px
        rgba(
            88,
            118,
            28,
            0.22
        );

}


/*
|--------------------------------------------------------------------------
| EDIT BUTTON
|--------------------------------------------------------------------------
*/

.edit-button {

    border-color:
        rgba(
            217,
            146,
            2,
            0.25
        );


    background:
        rgba(
            217,
            146,
            2,
            0.07
        );


    color:
        #D99202;

}


.edit-button:hover {

    border-color:
        #D99202;


    background:
        #D99202;


    color:
        #FFFFFF;

}


/*
|--------------------------------------------------------------------------
| ACCESSIBILITY
|--------------------------------------------------------------------------
*/

.action-icon-button:focus-visible {

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
| LARGE SCREEN
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1200px
) {

    .student-list-page {

        padding:
            26px
            26px
            48px;

    }


    .masthead-meta {

        min-width:
            235px;

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

    .student-masthead {

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

}


/*
|--------------------------------------------------------------------------
| SMALL TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 720px
) {

    .student-list-page {

        padding:
            18px;

    }


    .student-masthead {

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

}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 540px
) {

    .student-list-page {

        padding:
            12px;

    }


    .student-masthead {

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


    .masthead-bottom-line {

        right:
            18px;


        left:
            18px;

    }


    .masthead-bottom-line span:last-child {

        display:
            none;

    }

}


/*
|--------------------------------------------------------------------------
| VERY SMALL MOBILE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 420px
) {

    .masthead-copy h1 {

        font-size:
            30px;

    }


    .masthead-meta {

        flex-direction:
            column;


        gap:
            12px;

    }


    .masthead-rule {

        width:
            100%;


        height:
            1px;


        margin:
            0;

    }


    .masthead-meta-item strong {

        font-size:
            18px;

    }

}



.student-list-export-row {
    display: flex;
    justify-content: flex-end;
    margin: 16px 0 10px;
}

.student-list-export-button {
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 14px;
    border: 0;
    border-radius: 9px;
    background: #54100f;
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
}

.student-list-export-button:hover {
    opacity: .9;
}

</style>