<template>
    <div class="admin-ic-layout">

        <!-- =========================================================
             INSTRUCTOR / COORDINATOR / UNIVERSITY ADMIN SIDEBAR
        ========================================================== -->

        <AdminSideBar
            :user="user"
            :role="role"
            :component="component"
            :default-collapsed="defaultCollapsed"
        />


        <!-- =========================================================
             MAIN AREA
        ========================================================== -->

        <main class="main-content">

            <!-- =====================================================
                 PROTECTED HEADER AREA

                 UAHeader stays outside the scrollable container.

                 This means:
                 - The header never scrolls.
                 - Page content cannot scroll over the header.
                 - Only content-body scrolls.
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

import AdminSideBar
    from '@/components/AdminSideBar.vue';

import UAHeader
    from '@/components/UAHeader.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
|
| user:
|
| The currently authenticated account.
|
| This can now be:
|
| - Instructor
| - Coordinator
| - University Administrator
|
|
| role:
|
| instructor
| coordinator-attendance
| coordinator-announcement
| coordinator-schedule
| university-admin
|
|
| component:
|
| The currently selected NSTP component.
|
| Example:
|
| LTS
| CWTS
| ROTC
|
| This is especially important when:
|
| - an instructor manages multiple components
| - a University Admin enters a component dashboard
|
*/

defineProps({

    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

    user: {

        type: Object,

        default: () => ({}),

    },


    /*
    |--------------------------------------------------------------------------
    | Account Role
    |--------------------------------------------------------------------------
    */

    role: {

        type: String,

        default: '',

    },


    /*
    |--------------------------------------------------------------------------
    | Selected NSTP Component
    |--------------------------------------------------------------------------
    |
    | This is passed to AdminSideBar so the sidebar knows whether
    | the current context is LTS, CWTS, or ROTC.
    |
    */

    component: {

        type: String,

        default: '',

    },


    /*
    |--------------------------------------------------------------------------
    | Default Sidebar State
    |--------------------------------------------------------------------------
    |
    | false = expanded
    | true  = collapsed
    |
    */

    defaultCollapsed: {

        type: Boolean,

        default: false,

    },

});

</script>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/*
|--------------------------------------------------------------------------
| Color Palette
|--------------------------------------------------------------------------
|
| Cream       #EFEBE2
| Maroon      #54100F
| Green       #58761C
| Yellow      #FFBD36
| Orange      #D99202
| Blue Gray   #233E47
| Near Black  #000D12
| White       #FFFFFF
| Gray        #BEBEBE
| Dark        #0D171B
|
*/


/*
|--------------------------------------------------------------------------
| Main Layout
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| The entire browser viewport is locked.
|
| The sidebar and header do NOT participate in page scrolling.
| Only .content-body is allowed to scroll.
|
*/

.admin-ic-layout {

    width: 100vw;

    height: 100vh;

    display: flex;

    position: relative;

    overflow: hidden;

    background-color: #EFEBE2;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    box-sizing: border-box;

    isolation: isolate;

}


/*
|--------------------------------------------------------------------------
| Main Content
|--------------------------------------------------------------------------
|
| This fills all remaining space beside AdminSideBar.
|
| DO NOT:
|
| overflow-y: auto;
|
| on this container.
|
| Otherwise UAHeader will scroll together with the page.
|
*/

.main-content {

    flex: 1;

    min-width: 0;

    min-height: 0;

    height: 100vh;

    display: flex;

    flex-direction: column;

    position: relative;

    overflow: hidden;

    background-color: #EFEBE2;

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| Fixed / Protected Header Area
|--------------------------------------------------------------------------
|
| UAHeader lives outside content-body.
|
| Therefore the header stays visible even when the page underneath
| contains a large table, form, list, etc.
|
*/

.header-area {

    width: 100%;

    flex:
        0
        0
        auto;

    position: relative;

    z-index: 50;

    overflow: visible;

    background-color: #58761C;

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| Scrollable Content Area
|--------------------------------------------------------------------------
|
| THIS is the only scrollable area.
|
*/

.content-body {

    width: 100%;

    flex:
        1
        1
        auto;

    min-width: 0;

    min-height: 0;

    position: relative;

    overflow-x: hidden;

    overflow-y: auto;

    padding:
        20px
        32px
        40px;

    background-color: #EFEBE2;

    box-sizing: border-box;

    z-index: 1;

}


/*
|--------------------------------------------------------------------------
| Custom Scrollbar
|--------------------------------------------------------------------------
*/

.content-body::-webkit-scrollbar {

    width: 8px;

}


.content-body::-webkit-scrollbar-track {

    background: transparent;

}


.content-body::-webkit-scrollbar-thumb {

    background:
        rgba(
            88,
            118,
            28,
            0.35
        );

    border-radius: 10px;

}


.content-body::-webkit-scrollbar-thumb:hover {

    background:
        rgba(
            88,
            118,
            28,
            0.55
        );

}


/*
|--------------------------------------------------------------------------
| Slot Protection
|--------------------------------------------------------------------------
|
| Child pages live inside this container.
|
| The child page should NOT use:
|
| height: 100vh;
|
| because Admin_IC_Layout already controls the viewport.
|
*/

.page-slot {

    width: 100%;

    min-width: 0;

    position: relative;

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| Global Box Sizing Inside Layout
|--------------------------------------------------------------------------
*/

.admin-ic-layout,

.admin-ic-layout * {

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| Responsive - Medium Screen
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
| Responsive - Small Screen
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