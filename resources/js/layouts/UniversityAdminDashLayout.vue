<template>

    <div class="dashboard-layout">

        <!-- =========================================================
             SIDEBAR
        ========================================================== -->

        <SideBarUA
            :currentRoute="resolvedCurrentRoute"
        />


        <!-- =========================================================
             MAIN AREA
        ========================================================== -->

        <main class="main-content">


            <!-- =====================================================
                 PROTECTED HEADER AREA
            ====================================================== -->

            <div class="header-area">

                <UAHeader />

            </div>


            <!-- =====================================================
                 SCROLLABLE PAGE CONTENT
            ====================================================== -->

            <div class="content-body">

                <div class="page-slot">

                    <slot />

                </div>

            </div>

        </main>

    </div>

</template>


<script setup>

import {
    computed,
} from 'vue';

import {
    usePage,
} from '@inertiajs/vue3';

import SideBarUA
    from '@/components/SideBarUA.vue';

import UAHeader
    from '@/components/UAHeader.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
|
| currentRoute may still be manually supplied by a page.
|
| However, when it is not supplied, this layout automatically determines
| the active sidebar item from the current Inertia URL.
|
*/

const props =
    defineProps({

        currentRoute: {

            type: String,

            default: '',

        },

    });


/*
|--------------------------------------------------------------------------
| Inertia Page
|--------------------------------------------------------------------------
*/

const page =
    usePage();


/*
|--------------------------------------------------------------------------
| Automatic Current Route
|--------------------------------------------------------------------------
*/

const automaticCurrentRoute =
    computed(() => {

        const url =
            String(
                page.url
                ??
                ''
            )
                .split('?')[0]
                .replace(
                    /\/+$/,
                    ''
                );


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        if (
            url ===
            '/university-admin/dashboard'
        ) {
            return 'dashboard';
        }


        /*
        |--------------------------------------------------------------------------
        | Student Registration
        |--------------------------------------------------------------------------
        */

        if (
            url.startsWith(
                '/university-admin/student-registration'
            )
        ) {
            return 'student-registration';
        }


        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        |
        | This covers:
        |
        | /university-admin/users/students
        |
        | /university-admin/users/students/1
        |
        | /university-admin/users/students/2
        |
        */

        if (
            url.startsWith(
                '/university-admin/users/students'
            )
        ) {
            return 'students-list';
        }


        /*
        |--------------------------------------------------------------------------
        | Instructors
        |--------------------------------------------------------------------------
        */

        if (
            url.startsWith(
                '/university-admin/instructors'
            )
        ) {
            return 'instructors';
        }


        /*
        |--------------------------------------------------------------------------
        | Coordinators
        |--------------------------------------------------------------------------
        */

        if (
            url.startsWith(
                '/university-admin/coordinators'
            )
        ) {
            return 'coordinators';
        }


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        if (
            url.startsWith(
                '/university-admin/uniadminprofile'
            )
            ||
            url.startsWith(
                '/university-admin/change-password'
            )
        ) {
            return 'profile';
        }


        /*
        |--------------------------------------------------------------------------
        | Default
        |--------------------------------------------------------------------------
        */

        return 'dashboard';

    });


/*
|--------------------------------------------------------------------------
| Resolved Current Route
|--------------------------------------------------------------------------
|
| A page can still explicitly provide:
|
| <UniversityAdminDashLayout current-route="students-list">
|
| Otherwise automatic URL detection is used.
|
*/

const resolvedCurrentRoute =
    computed(() => {

        const manualRoute =
            String(
                props.currentRoute
                ??
                ''
            )
                .trim();


        if (
            manualRoute !==
            ''
        ) {
            return manualRoute;
        }


        return automaticCurrentRoute.value;

    });

</script>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/*
|--------------------------------------------------------------------------
| Dashboard Layout
|--------------------------------------------------------------------------
*/

.dashboard-layout {

    width:
        100vw;

    height:
        100vh;

    display:
        flex;

    position:
        relative;

    overflow:
        hidden;

    background-color:
        #EFEBE2;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    box-sizing:
        border-box;

    isolation:
        isolate;

}


/*
|--------------------------------------------------------------------------
| Main Content
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| Only .content-body scrolls.
|
| The sidebar and header remain fixed inside the dashboard layout.
|
*/

.main-content {

    flex:
        1;

    min-width:
        0;

    min-height:
        0;

    height:
        100vh;

    display:
        flex;

    flex-direction:
        column;

    position:
        relative;

    overflow:
        hidden;

    background-color:
        #EFEBE2;

    box-sizing:
        border-box;

}


/*
|--------------------------------------------------------------------------
| Header Area
|--------------------------------------------------------------------------
*/

.header-area {

    width:
        100%;

    flex:
        0 0 auto;

    position:
        relative;

    z-index:
        50;

    overflow:
        visible;

    box-sizing:
        border-box;

}


/*
|--------------------------------------------------------------------------
| Scrollable Content
|--------------------------------------------------------------------------
*/

.content-body {

    width:
        100%;

    flex:
        1 1 auto;

    min-width:
        0;

    min-height:
        0;

    position:
        relative;

    overflow-x:
        hidden;

    overflow-y:
        auto;

    padding:
        20px
        32px
        40px;

    background-color:
        #EFEBE2;

    box-sizing:
        border-box;

    z-index:
        1;

}


/*
|--------------------------------------------------------------------------
| Page Slot
|--------------------------------------------------------------------------
*/

.page-slot {

    width:
        100%;

    min-width:
        0;

    position:
        relative;

    box-sizing:
        border-box;

}


/*
|--------------------------------------------------------------------------
| Scrollbar
|--------------------------------------------------------------------------
*/

.content-body {

    scrollbar-width:
        thin;

    scrollbar-color:
        #BEBEBE
        #EFEBE2;

}


.content-body::-webkit-scrollbar {

    width:
        8px;

}


.content-body::-webkit-scrollbar-track {

    background:
        #EFEBE2;

}


.content-body::-webkit-scrollbar-thumb {

    border-radius:
        10px;

    background:
        #BEBEBE;

}


.content-body::-webkit-scrollbar-thumb:hover {

    background:
        #58761C;

}


/*
|--------------------------------------------------------------------------
| Global Box Sizing Inside Layout
|--------------------------------------------------------------------------
*/

.dashboard-layout,
.dashboard-layout * {

    box-sizing:
        border-box;

}


/*
|--------------------------------------------------------------------------
| Tablet
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1000px
) {

    .content-body {

        padding:
            18px
            24px
            35px;

    }

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (
    max-width: 700px
) {

    .content-body {

        padding:
            15px
            12px
            30px;

    }

}

</style>