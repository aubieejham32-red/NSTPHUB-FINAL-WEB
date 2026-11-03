<script setup>

import {
    router,
} from '@inertiajs/vue3';

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import UsersLayout
    from '@/layouts/usersLayout.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    /*
    |--------------------------------------------------------------------------
    | Real Coordinator Data
    |--------------------------------------------------------------------------
    */

    coordinators: {
        type: Array,
        default: () => [],
    },


    /*
    |--------------------------------------------------------------------------
    | University's Real NSTP Components
    |--------------------------------------------------------------------------
    */

    components: {
        type: Array,
        default: () => [],
    },

});


/*
|--------------------------------------------------------------------------
| Coordinator Table Columns
|--------------------------------------------------------------------------
*/

const columns = [

    /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

    {
        key: 'id',

        label: 'ID',

        width: '13%',

        formatter: (
            value
        ) => {

            if (
                value === null ||
                value === undefined
            ) {

                return '-';

            }


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

        width: '24%',

        align: 'left',
    },


    /*
    |--------------------------------------------------------------------------
    | Component
    |--------------------------------------------------------------------------
    */

    {
        key: 'component',

        label: 'COMPONENTS',

        width: '18%',
    },


    /*
    |--------------------------------------------------------------------------
    | Coordinator Type
    |--------------------------------------------------------------------------
    */

    {
        key: 'role',

        label: 'COORDINATOR TYPE',

        width: '20%',
    },


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    {
        key: 'status',

        label: 'STATUS',

        width: '14%',
    },

];


/*
|--------------------------------------------------------------------------
| Coordinator Roles
|--------------------------------------------------------------------------
|
| These correspond to coordinators.role in the database.
|
*/

const coordinatorRoles = [

    'Coordinator-Attendance',

    'Coordinator-Announcement',

    'Coordinator-Schedule',

];


/*
|--------------------------------------------------------------------------
| Table Actions
|--------------------------------------------------------------------------
*/

const actions = [
    'edit',
    'delete',
];


/*
|--------------------------------------------------------------------------
| Add Coordinator
|--------------------------------------------------------------------------
*/

const addCoordinator = () => {

    router.visit(
        '/university-admin/coordinators/create'
    );

};


/*
|--------------------------------------------------------------------------
| Edit Coordinator
|--------------------------------------------------------------------------
*/

const editCoordinator = (
    coordinator
) => {

    if (
        !coordinator?.id
    ) {

        console.error(
            'Coordinator ID is missing.'
        );

        return;

    }


    router.visit(
        `/university-admin/coordinators/${coordinator.id}/edit`
    );

};


/*
|--------------------------------------------------------------------------
| Delete Coordinator
|--------------------------------------------------------------------------
*/

const deleteCoordinator = (
    coordinator
) => {

    if (
        !coordinator?.id
    ) {

        console.error(
            'Coordinator ID is missing.'
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Confirm Delete
    |--------------------------------------------------------------------------
    */

    const confirmed =
        window.confirm(
            `Are you sure you want to delete ${coordinator.full_name}?`
        );


    if (
        !confirmed
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Delete Real Coordinator
    |--------------------------------------------------------------------------
    */

    router.delete(
        `/university-admin/coordinators/${coordinator.id}`,
        {

            preserveScroll:
                true,


            onError: (
                errors
            ) => {

                console.error(
                    'Unable to delete coordinator:',
                    errors
                );

            },

        }
    );

};


/*
|--------------------------------------------------------------------------
| Filter Event
|--------------------------------------------------------------------------
*/

const handleSearch = (
    filters
) => {

    console.log(
        'Coordinator filters:',
        filters
    );

};

</script>


<template>

    <UniversityAdminDashLayout>


        <!-- ========================================================
             COORDINATOR LIST
        ========================================================= -->

        <UsersLayout

            title="COORDINATOR"

            add-button-text="Add Coordinator"

            search-placeholder="Search for a coordinator..."

            :rows="
                props.coordinators
            "

            :columns="
                columns
            "

            :search-fields="[
                'id',
                'full_name',
                'username',
                'email',
                'phone_number',
                'component',
                'role',
                'status',
            ]"

            :components="
                props.components
            "

            component-field="component"

            component-filter-label="Components"

            :show-coordinator-type-filter="
                true
            "

            :coordinator-roles="
                coordinatorRoles
            "

            role-field="role"

            :actions="
                actions
            "

            empty-message="No coordinators found."

            @add="
                addCoordinator
            "

            @edit="
                editCoordinator
            "

            @delete="
                deleteCoordinator
            "

            @search="
                handleSearch
            "

        />

    </UniversityAdminDashLayout>

</template>