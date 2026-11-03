<script setup>

import {
    computed,
} from 'vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import {
    CircleCheck,
    Eye,
    Pencil,
    TriangleAlert,
    UserX,
} from 'lucide-vue-next';

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import UsersLayout
    from '@/layouts/usersLayout.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
|
| Data comes from:
|
| StudentController@index
|
| GET
|
| /university-admin/users/students
|
*/

const props = defineProps({

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
| Student Source
|--------------------------------------------------------------------------
|
| Supports:
|
| students: [...]
|
| OR:
|
| students: {
|     data: [...]
| }
|
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
| Full Name
|--------------------------------------------------------------------------
*/

const buildFullName = (
    student
) => {

    const existingFullName =
        String(
            student?.full_name
            ??
            student?.name
            ??
            ''
        )
            .trim();


    if (
        existingFullName !==
        ''
    ) {
        return existingFullName;
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


    /*
    |--------------------------------------------------------------------------
    | Active
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Warning
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Dropout
    |--------------------------------------------------------------------------
    */

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
| Normalized Student Collection
|--------------------------------------------------------------------------
|
| The controller already formats most of these values.
|
| These fallbacks make the Vue page defensive in case the backend response
| changes slightly later.
|
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

                        /*
                        |--------------------------------------------------------------------------
                        | Original Data
                        |--------------------------------------------------------------------------
                        */

                        ...student,


                        /*
                        |--------------------------------------------------------------------------
                        | ID
                        |--------------------------------------------------------------------------
                        */

                        id:
                            student?.id
                            ??
                            index,


                        /*
                        |--------------------------------------------------------------------------
                        | Student ID
                        |--------------------------------------------------------------------------
                        */

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


                        /*
                        |--------------------------------------------------------------------------
                        | Full Name
                        |--------------------------------------------------------------------------
                        */

                        full_name:
                            buildFullName(
                                student
                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | Course
                        |--------------------------------------------------------------------------
                        */

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


                        /*
                        |--------------------------------------------------------------------------
                        | Year & Section
                        |--------------------------------------------------------------------------
                        */

                        year_section:
                            buildYearSection(
                                student
                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | Component
                        |--------------------------------------------------------------------------
                        */

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


                        /*
                        |--------------------------------------------------------------------------
                        | Status
                        |--------------------------------------------------------------------------
                        */

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

                    };

                }
            );

    });


/*
|--------------------------------------------------------------------------
| Available NSTP Components
|--------------------------------------------------------------------------
*/

const availableComponents = [
    'LTS',
    'CWTS',
    'ROTC',
];


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
| Status Styling
|--------------------------------------------------------------------------
*/

const statusClass = (
    status
) => {

    switch (
        normalizeStatus(
            status
        )
    ) {

        case 'ACTIVE':

            return 'status-active';


        case 'WARNING FOR DROPOUT':

            return 'status-warning';


        case 'DROPOUT':

            return 'status-dropout';


        default:

            return 'status-default';

    }

};


/*
|--------------------------------------------------------------------------
| View Student Profile
|--------------------------------------------------------------------------
|
| Controller:
|
| StudentController@show
|
| Route:
|
| GET
| /university-admin/users/students/{student}
|
| Vue:
|
| StudentProfileInfo.vue
|
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
        `/university-admin/users/students/${studentId}`,
        {
            preserveScroll:
                false,
        }
    );

};


/*
|--------------------------------------------------------------------------
| Edit Student Profile
|--------------------------------------------------------------------------
|
| Controller:
|
| StudentController@edit
|
| Route:
|
| GET
| /university-admin/users/students/{student}/edit
|
| Vue:
|
| EditableProfileInfo.vue
|
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
    ) {
        return;
    }


    router.visit(
        `/university-admin/users/students/${studentId}/edit`,
        {
            preserveScroll:
                false,
        }
    );

};

</script>


<template>

    <Head
        title="List of Enrolled Students"
    />


    <UniversityAdminDashLayout>


        <main class="students-page">


            <UsersLayout

                title="LIST OF ENROLLED STUDENTS"

                :show-add-button="false"

                search-placeholder="Search Registered Students..."

                :search-fields="[
                    'id_number',
                    'full_name',
                    'course',
                    'year_section',
                    'component',
                    'status',
                ]"

                :rows="
                    normalizedStudents
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

                :actions="[]"

                empty-message="No enrolled students found."

            >


                <!-- ====================================================
                     CUSTOM TABLE CELLS
                ===================================================== -->

                <template
                    #cell="{
                        row,
                        column,
                        value
                    }"
                >


                    <!-- ================================================
                         STATUS
                    ================================================= -->

                    <div
                        v-if="
                            column.key ===
                            'status'
                        "
                        class="status-wrapper"
                        :class="
                            statusClass(
                                row.status
                            )
                        "
                    >


                        <!-- ACTIVE -->

                        <CircleCheck
                            v-if="
                                normalizeStatus(
                                    row.status
                                ) ===
                                'ACTIVE'
                            "
                            class="status-icon"
                            :size="16"
                            :stroke-width="2.4"
                        />


                        <!-- WARNING -->

                        <TriangleAlert
                            v-else-if="
                                normalizeStatus(
                                    row.status
                                ) ===
                                'WARNING FOR DROPOUT'
                            "
                            class="status-icon"
                            :size="16"
                            :stroke-width="2.4"
                        />


                        <!-- DROPOUT -->

                        <UserX
                            v-else-if="
                                normalizeStatus(
                                    row.status
                                ) ===
                                'DROPOUT'
                            "
                            class="status-icon"
                            :size="16"
                            :stroke-width="2.4"
                        />


                        <span
                            class="status-text"
                        >

                            {{
                                normalizeStatus(
                                    row.status
                                )
                            }}

                        </span>

                    </div>


                    <!-- ================================================
                         ACTION
                    ================================================= -->

                    <div
                        v-else-if="
                            column.key ===
                            'action'
                        "
                        class="student-actions"
                    >


                        <!-- ============================================
                             VIEW PROFILE
                        ============================================= -->

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


                        <!-- ============================================
                             EDIT PROFILE
                        ============================================= -->

                        <button

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


                    <!-- ================================================
                         NORMAL CELL
                    ================================================= -->

                    <span
                        v-else
                        class="normal-cell"
                    >

                        {{
                            value
                            ??
                            '-'
                        }}

                    </span>

                </template>

            </UsersLayout>


        </main>


    </UniversityAdminDashLayout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
|
| This page intentionally does NOT style the internal classes of:
|
| UniversityAdminDashLayout.vue
| usersLayout.vue
|
| There are no deep selectors here.
|
| usersLayout.vue keeps full control of:
|
| - panel
| - header
| - title
| - search area
| - search input
| - search button
| - component filter
| - dropdown
| - table
| - responsive table behavior
|
| This file styles only elements created directly by ListOfStudents.vue.
|
*/


/* ==========================================================================
   PAGE WRAPPER
   ========================================================================== */

.students-page {

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
        18px;

    box-sizing:
        border-box;

}


/* ==========================================================================
   NORMAL CELL CONTENT
   ========================================================================== */

.normal-cell {

    display:
        inline-block;

    max-width:
        100%;

    overflow-wrap:
        anywhere;

}


/* ==========================================================================
   STATUS
   ========================================================================== */

.status-wrapper {

    width:
        100%;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        11px;

    font-weight:
        800;

    line-height:
        1.2;

    text-align:
        center;

    text-transform:
        uppercase;

}


.status-icon {

    flex-shrink:
        0;

}


/* ==========================================================================
   ACTIVE
   ========================================================================== */

.status-active {

    color:
        var(
            --green
        );

}


/* ==========================================================================
   WARNING
   ========================================================================== */

.status-warning {

    color:
        var(
            --maroon
        );

}


/* ==========================================================================
   DROPOUT
   ========================================================================== */

.status-dropout {

    color:
        var(
            --near-black
        );

    font-weight:
        900;

}


/* ==========================================================================
   DEFAULT
   ========================================================================== */

.status-default {

    color:
        var(
            --dark-teal
        );

}


/* ==========================================================================
   ACTIONS
   ========================================================================== */

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


/* ==========================================================================
   ACTION BUTTON
   ========================================================================== */

.action-icon-button {

    width:
        36px;

    height:
        36px;

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
        10px;

    background:
        transparent;

    color:
        var(
            --maroon
        );

    cursor:
        pointer;

    transition:
        background-color
        0.18s
        ease,
        border-color
        0.18s
        ease,
        color
        0.18s
        ease,
        transform
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


/* ==========================================================================
   VIEW
   ========================================================================== */

.view-button:hover {

    border-color:
        var(
            --dark-teal
        );

    background:
        var(
            --dark-teal
        );

}


/* ==========================================================================
   EDIT
   ========================================================================== */

.edit-button:hover {

    border-color:
        var(
            --orange
        );

    background:
        var(
            --orange
        );

}


/* ==========================================================================
   TABLET
   ========================================================================== */

@media (
    max-width: 1100px
) {

    .students-page {

        padding:
            14px;

    }

}


/* ==========================================================================
   MOBILE
   ========================================================================== */

@media (
    max-width: 700px
) {

    .students-page {

        padding:
            10px;

    }


    .action-icon-button {

        width:
            34px;

        height:
            34px;

    }

}

</style>
