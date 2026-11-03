<script setup>
import { router } from '@inertiajs/vue3';

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import UsersLayout
    from '@/layouts/usersLayout.vue';


/*
|--------------------------------------------------------------------------
| Props From Laravel / Inertia
|--------------------------------------------------------------------------
*/

const props = defineProps({

    instructors: {
        type: Array,
        default: () => [],
    },

    components: {
        type: Array,
        default: () => [
            'CWTS',
            'LTS',
            'ROTC',
        ],
    },

});


/*
|--------------------------------------------------------------------------
| Instructor Table Columns
|--------------------------------------------------------------------------
*/

const instructorColumns = [

    /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

    {
        key: 'id',

        label: 'ID',

        width: '13%',

        formatter: (value) => {

            return String(
                value
            ).padStart(
                2,
                '0'
            );

        },
    },


    /*
    |--------------------------------------------------------------------------
    | Name
    |--------------------------------------------------------------------------
    */

    {
        key: 'full_name',

        label: 'NAME',

        width: '28%',

        align: 'left',
    },


    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    */

    {
        key: 'components_display',

        label: 'COMPONENTS',

        width: '22%',
    },


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    {
        key: 'status',

        label: 'STATUS',

        width: '19%',
    },

];


/*
|--------------------------------------------------------------------------
| Searchable Fields
|--------------------------------------------------------------------------
*/

const instructorSearchFields = [
    'full_name',
    'username',
    'email',
    'phone_number',
    'component',
    'components_display',
    'status',
];


/*
|--------------------------------------------------------------------------
| Add Instructor
|--------------------------------------------------------------------------
*/

const addInstructor = () => {

    router.visit(
        '/university-admin/instructors/create'
    );

};


/*
|--------------------------------------------------------------------------
| Edit Instructor
|--------------------------------------------------------------------------
|
| This opens:
|
| /university-admin/instructors/{id}/edit
|
| Example:
|
| /university-admin/instructors/1/edit
|
| Laravel will then call:
|
| InstructorController::edit()
|
| which will render:
|
| EditInstructorPage.vue
|
*/

const editInstructor = (
    instructor
) => {

    /*
    |--------------------------------------------------------------------------
    | Make Sure Instructor Exists
    |--------------------------------------------------------------------------
    */

    if (
        !instructor ||
        !instructor.id
    ) {

        console.error(
            'Unable to edit instructor. Instructor ID is missing.'
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Go To Edit Instructor Page
    |--------------------------------------------------------------------------
    */

    router.visit(
        `/university-admin/instructors/${instructor.id}/edit`
    );

};


/*
|--------------------------------------------------------------------------
| Delete Instructor
|--------------------------------------------------------------------------
*/

const deleteInstructor = (
    instructor
) => {

    /*
    |--------------------------------------------------------------------------
    | Instructor Name
    |--------------------------------------------------------------------------
    */

    const instructorName =
        instructor.full_name ??
        instructor.name ??
        'this instructor';


    /*
    |--------------------------------------------------------------------------
    | Confirmation
    |--------------------------------------------------------------------------
    */

    const confirmed =
        window.confirm(
            `Are you sure you want to delete ${instructorName}?`
        );


    if (
        !confirmed
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Delete Instructor
    |--------------------------------------------------------------------------
    */

    router.delete(
        `/university-admin/instructors/${instructor.id}`,
        {

            preserveScroll:
                true,


            onSuccess: () => {

                console.log(
                    'Instructor deleted successfully.'
                );

            },


            onError: (
                errors
            ) => {

                console.error(
                    'Unable to delete instructor:',
                    errors
                );

            },

        }
    );

};
</script>


<template>

    <!-- ============================================================
         UNIVERSITY ADMIN DASHBOARD
    ============================================================= -->

    <UniversityAdminDashLayout>

        <!-- ========================================================
             INSTRUCTOR LIST
        ========================================================= -->

        <UsersLayout

            title="INSTRUCTORS"

            add-button-text="Add Instructor"

            search-placeholder="Search for an instructor..."

            empty-message="No instructors found."

            :rows="
                props.instructors
            "

            :columns="
                instructorColumns
            "

            :search-fields="
                instructorSearchFields
            "

            :components="
                props.components
            "

            component-field="component"

            component-filter-label="Components"

            :show-add-button="
                true
            "

            :show-component-filter="
                true
            "

            :actions="[
                'edit',
                'delete'
            ]"

            @add="
                addInstructor
            "

            @edit="
                editInstructor
            "

            @delete="
                deleteInstructor
            "

        />

    </UniversityAdminDashLayout>

</template>