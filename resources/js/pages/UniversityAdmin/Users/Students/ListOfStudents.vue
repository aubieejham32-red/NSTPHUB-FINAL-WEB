<script setup>

import {
    computed,
    nextTick,
    ref,
} from 'vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import {
    CircleCheck,
    Eye,
    Pencil,
    Trash2,
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

/*
|--------------------------------------------------------------------------
| Delete Student – deliberate, irreversible action
|--------------------------------------------------------------------------
*/

const studentPendingDeletion = ref(null);
const deleteDialog = ref(null);
const deletionInProgress = ref(false);
const deletionError = ref('');
const deletionSuccess = ref('');

const confirmDeleteStudent = (student) => {
    if (deletionInProgress.value) return;
    const studentId = Number(student?.id);
    if (!Number.isInteger(studentId) || studentId <= 0) return;

    deletionError.value = '';
    deletionSuccess.value = '';
    studentPendingDeletion.value = student;
    nextTick(() => deleteDialog.value?.focus());
};

const cancelDeleteStudent = () => {
    if (deletionInProgress.value) return;
    studentPendingDeletion.value = null;
    deletionError.value = '';
};

const deleteStudent = () => {
    const student = studentPendingDeletion.value;
    const studentId = Number(student?.id);

    if (deletionInProgress.value || !Number.isInteger(studentId) || studentId <= 0) {
        return;
    }

    deletionInProgress.value = true;
    deletionError.value = '';

    router.delete(`/university-admin/users/students/${studentId}`, {
        preserveScroll: true,
        onSuccess: () => {
            deletionSuccess.value = `${student.full_name} was deleted successfully.`;
            studentPendingDeletion.value = null;
        },
        onError: () => {
            deletionError.value = 'Unable to delete this student. Please try again.';
        },
        onFinish: () => {
            deletionInProgress.value = false;
        },
    });
};

</script>


<template>

    <Head
        title="List of Enrolled Students"
    />


    <UniversityAdminDashLayout>


        <main class="students-page">


            <div v-if="deletionSuccess" class="delete-feedback delete-success" role="status">
                {{ deletionSuccess }}
            </div>

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

                        <button
                            type="button"
                            class="action-icon-button delete-button"
                            title="Delete Student Account"
                            :aria-label="`Delete ${row.full_name}`"
                            @click.stop="confirmDeleteStudent(row)"
                        >
                            <Trash2 :size="20" :stroke-width="2.4" />
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

            <div
                v-if="studentPendingDeletion"
                class="delete-modal-backdrop"
                role="presentation"
                @click.self="cancelDeleteStudent"
                @keydown.esc="cancelDeleteStudent"
            >
                <section
                    ref="deleteDialog"
                    class="delete-modal"
                    role="alertdialog"
                    aria-modal="true"
                    aria-labelledby="delete-student-title"
                    aria-describedby="delete-student-description"
                    tabindex="-1"
                >
                    <div class="delete-modal-icon">
                        <TriangleAlert :size="29" />
                    </div>
                    <h2 id="delete-student-title">Delete Student Account?</h2>
                    <p id="delete-student-description">
                        Are you sure you want to permanently delete
                        <strong>{{ studentPendingDeletion.full_name }}</strong>
                        ({{ studentPendingDeletion.id_number }})?
                    </p>
                    <p class="delete-warning">
                        This cannot be undone. Related attendance, reports,
                        excuse letters, and ROTC records may also be deleted.
                    </p>
                    <p v-if="deletionError" class="delete-feedback delete-error" role="alert">
                        {{ deletionError }}
                    </p>
                    <div class="delete-modal-actions">
                        <button
                            type="button"
                            class="modal-cancel-button"
                            :disabled="deletionInProgress"
                            @click="cancelDeleteStudent"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="modal-confirm-button"
                            :disabled="deletionInProgress"
                            @click="deleteStudent"
                        >
                            <Trash2 :size="17" />
                            {{ deletionInProgress ? 'Deleting...' : 'Delete Permanently' }}
                        </button>
                    </div>
                </section>
            </div>

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
   DELETE CONFIRMATION
   ========================================================================== */

.delete-button { color: #a22525; }
.delete-button:hover { background: #a22525; border-color: #a22525; }
.delete-feedback { padding: 12px 16px; margin-bottom: 12px; border-radius: 10px; font-size: 14px; }
.delete-success { background: #ebf7e7; border: 1px solid #b5dbab; color: #285526; }
.delete-error { background: #fff0f0; border: 1px solid #f0baba; color: #8d1c1c; }
.delete-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: rgba(12, 20, 24, 0.64);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.delete-modal {
    width: min(100%, 460px);
    padding: 30px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 25px 65px rgba(0, 0, 0, 0.25);
    color: #233e47;
    text-align: center;
}
.delete-modal-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin: 0 auto 16px;
    background: #fff1ee;
    color: #b52b2b;
}
.delete-modal h2 { font-size: 23px; font-weight: 800; margin: 0 0 12px; }
.delete-modal p { font-size: 14px; line-height: 1.6; margin: 0 0 14px; }
.delete-modal strong { overflow-wrap: anywhere; }
.delete-modal .delete-warning {
    padding: 12px;
    border-radius: 10px;
    background: #fff5e7;
    color: #854b00;
    text-align: left;
}
.delete-modal .delete-error { text-align: left; }
.delete-modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }
.delete-modal-actions button {
    min-height: 42px;
    border-radius: 10px;
    border: 1px solid transparent;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
}
.delete-modal-actions button:disabled { opacity: 0.6; cursor: not-allowed; }
.modal-cancel-button { background: #eef0ed; color: #233e47; }
.modal-confirm-button { display: inline-flex; align-items: center; gap: 7px; color: #fff; background: #a22525; }
.modal-confirm-button:hover:not(:disabled) { background: #811b1b; }
@media (max-width: 460px) {
    .delete-modal { padding: 22px; }
    .delete-modal-actions { flex-direction: column-reverse; }
    .delete-modal-actions button { justify-content: center; width: 100%; }
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
