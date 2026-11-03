<template>

    <SuperAdminLayout active="dashboard">

        <div class="dashboard-page">

            <!-- =========================================
                 HEADER
            ========================================== -->

            <div class="dashboard-header">

                <div class="semester-label">

                    <span class="diamond"></span>

                    <!-- =================================
                         ACADEMIC YEAR LABEL
                    ================================== -->

                    <span class="semester-prefix">

                        ACADEMIC YEAR

                    </span>


                    <!-- =================================
                         ACADEMIC YEAR SELECT
                    ================================== -->

                    <select
                        v-model="selectedAcademicYear"
                        class="
                            period-select
                            academic-year-select
                        "
                        @change="changeAcademicYear"
                    >

                        <option
                            v-for="year in academicYears"
                            :key="year"
                            :value="year"
                        >

                            {{ year }}

                        </option>

                    </select>


                    <!-- =================================
                         SEPARATOR
                    ================================== -->

                    <span class="period-dot">

                        •

                    </span>


                    <!-- =================================
                         SEMESTER SELECT
                    ================================== -->

                    <select
                        v-model="selectedSemester"
                        class="
                            period-select
                            semester-select
                        "
                        @change="changeSemester"
                    >

                        <option
                            v-for="semester in semesterOptions"
                            :key="semester"
                            :value="semester"
                        >

                            {{
                                String(
                                    semester
                                ).toUpperCase()
                            }}

                        </option>

                    </select>

                </div>

            </div>


            <!-- =========================================
                 HERO CARD
            ========================================== -->

            <section class="hero-card">

                <!-- LEFT -->

                <div class="hero-left">

                    <div class="hero-circle">

                        <div class="circle-ring">

                            <div class="circle-inner">

                                <h1>

                                    {{
                                        dashboard.totalUniversities
                                    }}

                                </h1>

                                <span>

                                    UNIVERSITIES

                                </span>

                                <span>

                                    ENROLLED

                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- RIGHT -->

                <div class="hero-right">

                    <h2>

                        Nationwide Program Coverage

                    </h2>


                    <p
                        v-html="
                            dashboard.description
                        "
                    ></p>


                    <div class="component-pills">

                        <!-- CWTS -->

                        <div
                            class="
                                pill
                                pill-green
                            "
                        >

                            <strong>

                                CWTS

                            </strong>

                            <span>

                                •

                                {{
                                    dashboard.cwts
                                }}

                                {{
                                    dashboard.cwts === 1
                                        ? 'school'
                                        : 'schools'
                                }}

                            </span>

                        </div>


                        <!-- LTS -->

                        <div
                            class="
                                pill
                                pill-gold
                            "
                        >

                            <strong>

                                LTS

                            </strong>

                            <span>

                                •

                                {{
                                    dashboard.lts
                                }}

                                {{
                                    dashboard.lts === 1
                                        ? 'school'
                                        : 'schools'
                                }}

                            </span>

                        </div>


                        <!-- ROTC -->

                        <div
                            class="
                                pill
                                pill-blue
                            "
                        >

                            <strong>

                                ROTC

                            </strong>

                            <span>

                                •

                                {{
                                    dashboard.rotc
                                }}

                                {{
                                    dashboard.rotc === 1
                                        ? 'school'
                                        : 'schools'
                                }}

                            </span>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =========================================
                 STATISTICS
            ========================================== -->

            <section class="statistics-grid">

                <!-- =====================================
                     UNIVERSITIES
                ====================================== -->

                <div
                    class="
                        stat-card
                        border-maroon
                    "
                >

                    <div class="stat-title">

                        TOTAL UNIVERSITIES

                    </div>


                    <div class="stat-number">

                        {{
                            dashboard.totalUniversities
                        }}

                    </div>


                    <div
                        class="
                            stat-footer
                            positive
                        "
                    >

                        +{{
                            dashboard.newUniversities
                        }}

                        this semester

                    </div>

                </div>


                <!-- =====================================
                     STUDENTS
                ====================================== -->

                <div
                    class="
                        stat-card
                        border-green
                    "
                >

                    <div class="stat-title">

                        STUDENTS ENROLLED

                    </div>


                    <div class="stat-number">

                        {{
                            dashboard.students
                        }}

                    </div>


                    <div
                        class="
                            stat-footer
                            positive
                        "
                    >

                        Registered students

                    </div>

                </div>


                <!-- =====================================
                     ADMINISTRATORS
                ====================================== -->

                <div
                    class="
                        stat-card
                        border-gold
                    "
                >

                    <div class="stat-title">

                        UNIVERSITY ADMINS

                    </div>


                    <div class="stat-number">

                        {{
                            dashboard.admins
                        }}

                    </div>


                    <div
                        class="
                            stat-footer
                            muted
                        "
                    >

                        {{
                            dashboard.pendingAdmins
                        }}

                        pending activation

                    </div>

                </div>


                <!-- =====================================
                     ACCESS CODES
                ====================================== -->

                <div
                    class="
                        stat-card
                        border-blue
                    "
                >

                    <div class="stat-title">

                        ACCESS CODES ISSUED

                    </div>


                    <div class="stat-number">

                        {{
                            dashboard.accessCodes
                        }}

                    </div>


                    <div
                        class="
                            stat-footer
                            muted
                        "
                    >

                        {{
                            dashboard.expiringCodes
                        }}

                        expiring this week

                    </div>

                </div>

            </section>


            <!-- =========================================
                 CHARTS
            ========================================== -->

            <section class="charts-grid">

                <!-- =====================================
                     COVERAGE BY REGION
                ====================================== -->

                <div class="dashboard-card">

                    <div class="card-header">

                        <h3>

                            Coverage by Region

                        </h3>


                        <span class="card-subtitle">

                            Partner Universities

                        </span>

                    </div>


                    <!-- EMPTY REGION -->

                    <div
                        v-if="
                            !hasRegions
                        "
                        class="chart-empty"
                    >

                        No regional data available for this semester.

                    </div>


                    <!-- REGION LIST -->

                    <div
                        v-for="
                            region in regions
                        "
                        :key="
                            region.name
                        "
                        class="region-row"
                    >

                        <div class="region-name">

                            {{
                                region.name
                            }}

                        </div>


                        <div class="progress-wrapper">

                            <div class="progress-bar">

                                <div
                                    class="
                                        progress-fill
                                        region-fill
                                    "
                                    :style="{
                                        width:
                                            region.percent +
                                            '%'
                                    }"
                                ></div>

                            </div>

                        </div>


                        <div class="region-total">

                            {{
                                region.total
                            }}

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     COMPONENT DISTRIBUTION
                ====================================== -->

                <div class="dashboard-card">

                    <div class="card-header">

                        <h3>

                            Component Distribution

                        </h3>


                        <span class="card-subtitle">

                            Current Enrollment

                        </span>

                    </div>


                    <!-- EMPTY COMPONENTS -->

                    <div
                        v-if="
                            !hasComponents
                        "
                        class="chart-empty"
                    >

                        No component data available for this semester.

                    </div>


                    <!-- COMPONENT ROWS -->

                    <div
                        v-for="
                            component in components
                        "
                        :key="
                            component.name
                        "
                        class="component-row"
                    >

                        <div class="component-header">

                            <span class="component-name">

                                {{
                                    component.name
                                }}

                            </span>


                            <span class="component-percent">

                                {{
                                    component.percent
                                }}%

                            </span>

                        </div>


                        <div class="progress-bar">

                            <div
                                class="progress-fill"
                                :class="
                                    component.class
                                "
                                :style="{
                                    width:
                                        component.percent +
                                        '%'
                                }"
                            ></div>

                        </div>

                    </div>


                    <!-- =================================
                         SUMMARY
                    ================================== -->

                    <div class="component-summary">

                        <!-- CWTS -->

                        <div class="summary-item">

                            <span
                                class="
                                    summary-dot
                                    cwts-dot
                                "
                            ></span>


                            <span>

                                CWTS

                            </span>


                            <strong>

                                {{
                                    dashboard.cwts
                                }}

                            </strong>

                        </div>


                        <!-- LTS -->

                        <div class="summary-item">

                            <span
                                class="
                                    summary-dot
                                    lts-dot
                                "
                            ></span>


                            <span>

                                LTS

                            </span>


                            <strong>

                                {{
                                    dashboard.lts
                                }}

                            </strong>

                        </div>


                        <!-- ROTC -->

                        <div class="summary-item">

                            <span
                                class="
                                    summary-dot
                                    rotc-dot
                                "
                            ></span>


                            <span>

                                ROTC

                            </span>


                            <strong>

                                {{
                                    dashboard.rotc
                                }}

                            </strong>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =========================================
                 RECENTLY ADDED UNIVERSITIES
            ========================================== -->

            <section
                class="
                    dashboard-card
                    recent-card
                "
            >

                <div class="table-header">

                    <div>

                        <h3>

                            Recently Added Universities

                        </h3>


                        <p class="table-subtitle">

                            Latest registered universities
                            in NSTP Hub

                        </p>

                    </div>


                    <button
                        class="view-all-btn"
                        @click="
                            goToUniversities
                        "
                    >

                        View All Universities

                    </button>

                </div>


                <div class="table-responsive">

                    <table class="recent-table">

                        <thead>

                            <tr>

                                <th width="32%">

                                    University

                                </th>


                                <th width="15%">

                                    Region

                                </th>


                                <th width="23%">

                                    Administrator

                                </th>


                                <th width="18%">

                                    Access Code

                                </th>


                                <th width="12%">

                                    Status

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <!-- EMPTY -->

                            <tr
                                v-if="
                                    recentUniversities.length ===
                                    0
                                "
                            >

                                <td
                                    colspan="5"
                                    class="empty-table"
                                >

                                    No universities found for

                                    {{
                                        dashboard.academic_year
                                    }}

                                    •

                                    {{
                                        dashboard.semester
                                    }}.

                                </td>

                            </tr>


                            <!-- UNIVERSITIES -->

                            <tr
                                v-for="
                                    university in
                                    recentUniversities
                                "
                                :key="
                                    university.id
                                "
                            >

                                <!-- =====================
                                     UNIVERSITY
                                ====================== -->

                                <td>

                                    <div class="university-cell">

                                        <div class="university-logo">

                                            <img
                                                v-if="
                                                    university.logo_url
                                                "
                                                :src="
                                                    university.logo_url
                                                "
                                                :alt="
                                                    university.name
                                                "
                                                class="logo-image"
                                            />


                                            <span v-else>

                                                {{
                                                    firstLetter(
                                                        university.name
                                                    )
                                                }}

                                            </span>

                                        </div>


                                        <div class="university-details">

                                            <div class="university-name">

                                                {{
                                                    university.name
                                                }}

                                            </div>


                                            <div class="university-id">

                                                #{{
                                                    university.id
                                                }}

                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- =====================
                                     REGION
                                ====================== -->

                                <td>

                                    <span class="region-badge">

                                        {{
                                            university.region ||
                                            '—'
                                        }}

                                    </span>

                                </td>


                                <!-- =====================
                                     ADMINISTRATOR
                                ====================== -->

                                <td>

                                    <div class="admin-cell">

                                        <div class="admin-avatar">

                                            {{
                                                firstLetter(
                                                    university.admin
                                                )
                                            }}

                                        </div>


                                        <span>

                                            {{
                                                university.admin ||
                                                'No Administrator'
                                            }}

                                        </span>

                                    </div>

                                </td>


                                <!-- =====================
                                     ACCESS CODE
                                ====================== -->

                                <td>

                                    <span class="access-code">

                                        {{
                                            university.access_code ||
                                            '—'
                                        }}

                                    </span>

                                </td>


                                <!-- =====================
                                     STATUS
                                ====================== -->

                                <td>

                                    <span
                                        class="status-badge"
                                        :class="
                                            statusClass(
                                                university.status
                                            )
                                        "
                                    >

                                        {{
                                            university.status ||
                                            'Inactive'
                                        }}

                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </section>


            <!-- =========================================
                 RECENT ACTIVITY
            ========================================== -->

            <section
                class="
                    dashboard-card
                    activity-card
                "
            >

                <div class="card-header">

                    <div>

                        <h3>

                            Recent Activity

                        </h3>


                        <p class="table-subtitle">

                            Latest activities across NSTP Hub

                        </p>

                    </div>

                </div>


                <!-- =====================================
                     ACTIVITY LIST
                ====================================== -->

                <div
                    v-if="
                        activities.length
                    "
                    class="activity-list"
                >

                    <div
                        v-for="
                            activity in activities
                        "
                        :key="
                            activity.id
                        "
                        class="activity-item"
                    >

                        <!-- TIMELINE -->

                        <div class="activity-left">

                            <div
                                class="activity-dot"
                                :class="
                                    activity.color
                                "
                            ></div>


                            <div class="activity-line"></div>

                        </div>


                        <!-- CONTENT -->

                        <div class="activity-content">

                            <div
                                class="activity-text"
                                v-html="
                                    activity.message
                                "
                            ></div>


                            <div class="activity-time">

                                {{
                                    activity.time
                                }}

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     EMPTY
                ====================================== -->

                <div
                    v-else
                    class="empty-state"
                >

                    <div class="empty-icon">

                        📋

                    </div>


                    <h4>

                        No Recent Activity

                    </h4>


                    <p>

                        No activities are available for

                        {{
                            dashboard.academic_year
                        }}

                        •

                        {{
                            dashboard.semester
                        }}.

                    </p>

                </div>

            </section>

        </div>

    </SuperAdminLayout>

</template>


<script setup>

import {
    computed,
    ref,
} from 'vue'

import {
    router,
} from '@inertiajs/vue3'

import SuperAdminLayout
    from '@/layouts/SuperAdminLayout.vue'


/*
|--------------------------------------------------------------------------
| Props From Laravel / Inertia
|--------------------------------------------------------------------------
*/

const props = defineProps({

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    dashboard: {
        type: Object,
        required: true,
    },


    /*
    |--------------------------------------------------------------------------
    | Selected Period
    |--------------------------------------------------------------------------
    */

    periodFilters: {
        type: Object,
        default: () => ({
            academic_year: '',
            semester: '',
        }),
    },


    /*
    |--------------------------------------------------------------------------
    | Available Academic Years
    |--------------------------------------------------------------------------
    */

    academicYears: {
        type: Array,
        default: () => [],
    },


    /*
    |--------------------------------------------------------------------------
    | Available Semesters
    |--------------------------------------------------------------------------
    */

    semesterOptions: {
        type: Array,
        default: () => [],
    },


    /*
    |--------------------------------------------------------------------------
    | Regions
    |--------------------------------------------------------------------------
    */

    regions: {
        type: Array,
        required: true,
    },


    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    */

    components: {
        type: Array,
        required: true,
    },


    /*
    |--------------------------------------------------------------------------
    | Recently Added Universities
    |--------------------------------------------------------------------------
    */

    recentUniversities: {
        type: Array,
        required: true,
    },


    /*
    |--------------------------------------------------------------------------
    | Activities
    |--------------------------------------------------------------------------
    */

    activities: {
        type: Array,
        required: true,
    },

})


/*
|--------------------------------------------------------------------------
| Computed Dashboard Data
|--------------------------------------------------------------------------
*/

const dashboard =
    computed(
        () =>
            props.dashboard
    )


const regions =
    computed(
        () =>
            props.regions
    )


const components =
    computed(
        () =>
            props.components
    )


const recentUniversities =
    computed(
        () =>
            props.recentUniversities
    )


const activities =
    computed(
        () =>
            props.activities
    )


/*
|--------------------------------------------------------------------------
| Academic Year Options
|--------------------------------------------------------------------------
*/

const academicYears =
    computed(
        () =>
            props.academicYears
    )


/*
|--------------------------------------------------------------------------
| Semester Options
|--------------------------------------------------------------------------
*/

const semesterOptions =
    computed(
        () =>
            props.semesterOptions
    )


/*
|--------------------------------------------------------------------------
| Selected Academic Year
|--------------------------------------------------------------------------
*/

const selectedAcademicYear =
    ref(
        props.periodFilters
            ?.academic_year ||
        props.dashboard
            ?.academic_year ||
        ''
    )


/*
|--------------------------------------------------------------------------
| Selected Semester
|--------------------------------------------------------------------------
*/

const selectedSemester =
    ref(
        props.periodFilters
            ?.semester ||
        props.dashboard
            ?.semester ||
        ''
    )


/*
|--------------------------------------------------------------------------
| Change Academic Year
|--------------------------------------------------------------------------
|
| When the Academic Year changes:
|
| /superadmin/dashboard?academic_year=2026-2027
|
| The backend chooses the correct semester options for that year.
|
*/

const changeAcademicYear =
    () => {

        router.get(
            '/superadmin/dashboard',
            {
                academic_year:
                    selectedAcademicYear.value,
            },
            {
                preserveScroll:
                    true,

                preserveState:
                    false,

                replace:
                    true,
            }
        )

    }


/*
|--------------------------------------------------------------------------
| Change Semester
|--------------------------------------------------------------------------
|
| Example:
|
| /superadmin/dashboard
| ?academic_year=2026-2027
| &semester=2nd Semester
|
*/

const changeSemester =
    () => {

        router.get(
            '/superadmin/dashboard',
            {
                academic_year:
                    selectedAcademicYear.value,

                semester:
                    selectedSemester.value,
            },
            {
                preserveScroll:
                    true,

                preserveState:
                    false,

                replace:
                    true,
            }
        )

    }


/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

const goToUniversities =
    () => {

        router.visit(
            '/superadmin/universities'
        )

    }


/*
|--------------------------------------------------------------------------
| Format Number
|--------------------------------------------------------------------------
*/

const formatNumber =
    (
        value
    ) => {

        if (
            value === null ||
            value === undefined
        ) {

            return '0'

        }


        return Number(
            value
        ).toLocaleString()

    }


/*
|--------------------------------------------------------------------------
| Status Class
|--------------------------------------------------------------------------
*/

const statusClass =
    (
        status
    ) => {

        if (
            !status
        ) {

            return 'inactive'

        }


        switch (
            String(
                status
            )
                .trim()
                .toLowerCase()
        ) {

            case 'active':

                return 'active'


            case 'pending':

                return 'pending'


            case 'inactive':

                return 'inactive'


            default:

                return 'inactive'

        }

    }


/*
|--------------------------------------------------------------------------
| First Letter
|--------------------------------------------------------------------------
*/

const firstLetter =
    (
        text
    ) => {

        if (
            !text
        ) {

            return '?'

        }


        return String(
            text
        )
            .charAt(
                0
            )
            .toUpperCase()

    }


/*
|--------------------------------------------------------------------------
| Hero Description
|--------------------------------------------------------------------------
*/

const heroDescription =
    computed(
        () =>
            dashboard.value.description
    )


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const totalUniversities =
    computed(
        () =>
            formatNumber(
                dashboard.value
                    .totalUniversities
            )
    )


const totalStudents =
    computed(
        () =>
            formatNumber(
                dashboard.value
                    .students
            )
    )


const totalAdmins =
    computed(
        () =>
            formatNumber(
                dashboard.value
                    .admins
            )
    )


const totalAccessCodes =
    computed(
        () =>
            formatNumber(
                dashboard.value
                    .accessCodes
            )
    )


/*
|--------------------------------------------------------------------------
| Component Totals
|--------------------------------------------------------------------------
*/

const totalCWTS =
    computed(
        () =>
            dashboard.value
                .cwts
    )


const totalLTS =
    computed(
        () =>
            dashboard.value
                .lts
    )


const totalROTC =
    computed(
        () =>
            dashboard.value
                .rotc
    )


/*
|--------------------------------------------------------------------------
| Empty States
|--------------------------------------------------------------------------
*/

const hasRegions =
    computed(
        () =>
            regions.value.length >
            0
    )


const hasComponents =
    computed(
        () =>
            components.value.length >
            0
    )


const hasUniversities =
    computed(
        () =>
            recentUniversities.value
                .length >
            0
    )


const hasActivities =
    computed(
        () =>
            activities.value.length >
            0
    )

</script>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/* =====================================================
   RESET
===================================================== */

*,
*::before,
*::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


html {
    scroll-behavior: smooth;
}


:deep(body) {
    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    background:
        #EFEBE2;

    color:
        #233E47;
}


/* =====================================================
   ROOT COLORS
===================================================== */

:root {
    --background:
        #EFEBE2;

    --white:
        #FFFFFF;

    --maroon:
        #54100F;

    --green:
        #58761C;

    --yellow:
        #FFBD36;

    --gold:
        #D99202;

    --blue:
        #233E47;

    --dark:
        #000D12;

    --dark-2:
        #0D171B;

    --gray:
        #BEBEBE;

    --border:
        #E6E0D7;

    --shadow:
        0
        10px
        30px
        rgba(
            0,
            0,
            0,
            .08
        );

    --shadow-hover:
        0
        18px
        40px
        rgba(
            0,
            0,
            0,
            .12
        );

    --radius:
        24px;

    --transition:
        .25s ease;
}


/* =====================================================
   PAGE LAYOUT
===================================================== */

.dashboard-page {
    width:
        100%;

    display:
        flex;

    flex-direction:
        column;

    gap:
        32px;

    padding:
        10px
        0
        50px;
}


:deep(.page-content) {
    width:
        100%;

    display:
        flex;

    flex-direction:
        column;

    gap:
        32px;
}


/* =====================================================
   DEFAULT CARD
===================================================== */

.dashboard-card {
    background:
        var(--white);

    border-radius:
        var(--radius);

    border:
        1px solid
        var(--border);

    box-shadow:
        var(--shadow);

    transition:
        var(--transition);

    overflow:
        hidden;
}


.dashboard-card:hover {
    transform:
        translateY(-3px);

    box-shadow:
        var(--shadow-hover);
}


/* =====================================================
   HEADINGS
===================================================== */

h1,
h2,
h3,
h4,
h5,
h6 {
    color:
        var(--maroon);

    font-weight:
        800;

    margin:
        0;
}


p {
    margin:
        0;

    color:
        #5B6770;

    line-height:
        1.7;
}


/* =====================================================
   LINKS
===================================================== */

a {
    color:
        inherit;

    text-decoration:
        none;
}


/* =====================================================
   BUTTON RESET
===================================================== */

button {
    border:
        none;

    outline:
        none;

    cursor:
        pointer;

    font-family:
        inherit;

    transition:
        .25s ease;
}


/* =====================================================
   GRID DEFAULTS
===================================================== */

.statistics-grid {
    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            1fr
        );

    gap:
        24px;
}


.charts-grid {
    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        24px;
}


/* =====================================================
   TABLE WRAPPER
===================================================== */

.table-responsive {
    width:
        100%;

    overflow-x:
        auto;
}


/* =====================================================
   SCROLLBAR
===================================================== */

.table-responsive::-webkit-scrollbar {
    height:
        8px;
}


.table-responsive::-webkit-scrollbar-thumb {
    background:
        #D3CBBE;

    border-radius:
        999px;
}


.table-responsive::-webkit-scrollbar-track {
    background:
        transparent;
}


/* =====================================================
   EMPTY STATE
===================================================== */

.empty-state {
    padding:
        60px
        20px;

    text-align:
        center;
}


.empty-icon {
    font-size:
        42px;

    margin-bottom:
        15px;
}


.empty-state h4 {
    color:
        var(--maroon);

    margin-bottom:
        8px;
}


.empty-state p {
    color:
        #777;

    font-size:
        15px;
}


/* =====================================================
   CHART EMPTY
===================================================== */

.chart-empty {
    min-height:
        120px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        20px;

    color:
        #8A8A8A;

    font-size:
        14px;

    text-align:
        center;
}


/* =====================================================
   RESPONSIVE BASE
===================================================== */

@media (
    max-width:
    1200px
) {

    .statistics-grid {
        grid-template-columns:
            repeat(
                2,
                1fr
            );
    }


    .charts-grid {
        grid-template-columns:
            1fr;
    }

}


@media (
    max-width:
    768px
) {

    .dashboard-page {
        gap:
            24px;
    }


    .statistics-grid {
        grid-template-columns:
            1fr;
    }

}


/* =====================================================
   DASHBOARD HEADER
===================================================== */

.dashboard-header {
    width:
        100%;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        20px;
}


/* =====================================================
   ACADEMIC YEAR / SEMESTER FILTER
===================================================== */

.semester-label {
    display:
        inline-flex;

    align-items:
        center;

    gap:
        9px;

    min-height:
        52px;

    padding:
        7px
        16px;

    background:
        #FFFFFF;

    border:
        1px solid
        var(--border);

    border-radius:
        999px;

    box-shadow:
        0
        6px
        18px
        rgba(
            0,
            0,
            0,
            .06
        );

    color:
        var(--maroon);

    font-size:
        14px;

    font-weight:
        700;

    letter-spacing:
        .4px;

    text-transform:
        uppercase;
}


/* =====================================================
   ACADEMIC YEAR LABEL
===================================================== */

.semester-prefix {
    white-space:
        nowrap;

    color:
        var(--maroon);

    font-weight:
        800;
}


/* =====================================================
   PERIOD SELECT
===================================================== */

.period-select {
    min-height:
        36px;

    padding:
        0
        32px
        0
        10px;

    border:
        1px solid
        transparent;

    border-radius:
        10px;

    outline:
        none;

    background:
        #F8F6F1;

    color:
        #54100F;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        14px;

    font-weight:
        800;

    text-transform:
        uppercase;

    cursor:
        pointer;

    transition:
        all
        .2s
        ease;
}


.period-select:hover {
    background:
        #EFEBE2;

    border-color:
        #E0D9CE;
}


.period-select:focus {
    background:
        #FFFFFF;

    border-color:
        #D99202;

    box-shadow:
        0
        0
        0
        3px
        rgba(
            217,
            146,
            2,
            .12
        );
}


/* =====================================================
   ACADEMIC YEAR SELECT
===================================================== */

.academic-year-select {
    min-width:
        125px;
}


/* =====================================================
   SEMESTER SELECT
===================================================== */

.semester-select {
    min-width:
        165px;
}


/* =====================================================
   PERIOD DOT
===================================================== */

.period-dot {
    color:
        #54100F;

    font-size:
        17px;

    font-weight:
        900;
}


/* =====================================================
   DIAMOND
===================================================== */

.diamond {
    width:
        10px;

    height:
        10px;

    background:
        var(--gold);

    transform:
        rotate(
            45deg
        );

    border-radius:
        2px;

    flex-shrink:
        0;
}


/* =====================================================
   HEADER TITLE
===================================================== */

.dashboard-title {
    display:
        flex;

    flex-direction:
        column;

    gap:
        6px;
}


.dashboard-title h1 {
    font-size:
        38px;

    font-weight:
        800;

    color:
        var(--maroon);

    line-height:
        1;
}


.dashboard-title p {
    font-size:
        15px;

    color:
        #66727C;
}


/* =====================================================
   HEADER ACTIONS
===================================================== */

.header-actions {
    display:
        flex;

    align-items:
        center;

    gap:
        14px;
}


.header-button {
    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        10px;

    padding:
        12px
        22px;

    border-radius:
        14px;

    background:
        var(--maroon);

    color:
        #FFFFFF;

    font-size:
        14px;

    font-weight:
        700;

    transition:
        var(--transition);
}


.header-button:hover {
    background:
        #6B1A18;

    transform:
        translateY(-2px);
}


.header-button svg {
    width:
        18px;

    height:
        18px;
}


/* =====================================================
   HEADER RESPONSIVE
===================================================== */

@media (
    max-width:
    992px
) {

    .dashboard-header {
        flex-direction:
            column;

        align-items:
            flex-start;
    }


    .header-actions {
        width:
            100%;
    }

}


@media (
    max-width:
    768px
) {

    .semester-label {
        width:
            100%;

        flex-wrap:
            wrap;

        justify-content:
            center;

        border-radius:
            20px;

        padding:
            10px
            14px;
    }


    .academic-year-select,
    .semester-select {
        flex:
            1;

        min-width:
            140px;
    }


    .dashboard-title h1 {
        font-size:
            30px;
    }

}


@media (
    max-width:
    480px
) {

    .semester-label {
        flex-direction:
            column;

        align-items:
            stretch;

        font-size:
            12px;
    }


    .semester-prefix {
        text-align:
            center;
    }


    .period-dot {
        display:
            none;
    }


    .academic-year-select,
    .semester-select {
        width:
            100%;

        min-width:
            0;
    }


    .dashboard-title h1 {
        font-size:
            26px;
    }

}


/* =====================================================
   HERO CARD
===================================================== */

.hero-card {
    position:
        relative;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        48px;

    width:
        100%;

    min-height:
        340px;

    padding:
        48px;

    overflow:
        hidden;

    background:
        linear-gradient(
            135deg,
            #54100F
            0%,
            #6C1917
            45%,
            #233E47
            100%
        );

    border-radius:
        32px;

    box-shadow:
        0
        18px
        40px
        rgba(
            0,
            0,
            0,
            .18
        );
}


/* Decorative Glow */

.hero-card::before {
    content:
        "";

    position:
        absolute;

    top:
        -120px;

    right:
        -120px;

    width:
        320px;

    height:
        320px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            .05
        );
}


.hero-card::after {
    content:
        "";

    position:
        absolute;

    bottom:
        -90px;

    left:
        -90px;

    width:
        240px;

    height:
        240px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            .04
        );
}


/* =====================================================
   LEFT SIDE
===================================================== */

.hero-left {
    position:
        relative;

    z-index:
        2;

    width:
        280px;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    flex-shrink:
        0;
}


/* =====================================================
   RIGHT SIDE
===================================================== */

.hero-right {
    position:
        relative;

    z-index:
        2;

    flex:
        1;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    align-items:
        flex-start;

    gap:
        22px;
}


/* =====================================================
   HERO TITLE
===================================================== */

.hero-right h2 {
    color:
        #FFFFFF;

    font-size:
        40px;

    font-weight:
        800;

    line-height:
        1.15;

    letter-spacing:
        -1px;

    margin-bottom:
        18px;
}


/* =====================================================
   HERO DESCRIPTION
===================================================== */

.hero-right p {
    max-width:
        760px;

    color:
        rgba(
            255,
            255,
            255,
            .88
        );

    font-size:
        16px;

    font-weight:
        400;

    line-height:
        1.9;

    margin-bottom:
        28px;
}


/* =====================================================
   HERO RESPONSIVE
===================================================== */

@media (
    max-width:
    1200px
) {

    .hero-card {
        gap:
            36px;

        padding:
            40px;
    }


    .hero-right h2 {
        font-size:
            34px;
    }

}


@media (
    max-width:
    992px
) {

    .hero-card {
        flex-direction:
            column;

        align-items:
            center;

        text-align:
            center;

        padding:
            40px
            30px;
    }


    .hero-left {
        width:
            100%;
    }


    .hero-right {
        align-items:
            center;
    }


    .hero-right p {
        max-width:
            100%;
    }


    .component-pills {
        justify-content:
            center;
    }

}


@media (
    max-width:
    768px
) {

    .hero-card {
        padding:
            32px
            24px;

        border-radius:
            24px;
    }


    .hero-right h2 {
        font-size:
            28px;
    }


    .hero-right p {
        font-size:
            15px;

        line-height:
            1.7;
    }

}


@media (
    max-width:
    480px
) {

    .hero-card {
        padding:
            24px
            18px;

        gap:
            28px;
    }


    .hero-right h2 {
        font-size:
            24px;
    }


    .hero-right p {
        font-size:
            14px;
    }

}


/* =====================================================
   HERO CIRCLE
===================================================== */

.hero-circle {
    position:
        relative;

    width:
        260px;

    height:
        260px;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;
}


/* =====================================================
   OUTER RING
===================================================== */

.circle-ring {
    position:
        relative;

    width:
        100%;

    height:
        100%;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    border-radius:
        50%;

    background:
        radial-gradient(
            circle at center,
            rgba(
                255,
                255,
                255,
                .05
            )
            0%,
            rgba(
                255,
                255,
                255,
                .02
            )
            70%,
            transparent
            100%
        );

    border:
        12px solid
        rgba(
            255,
            255,
            255,
            .18
        );

    backdrop-filter:
        blur(
            8px
        );

    box-shadow:
        inset
        0
        0
        35px
        rgba(
            255,
            255,
            255,
            .08
        ),
        0
        20px
        45px
        rgba(
            0,
            0,
            0,
            .28
        );
}


/* Decorative Ring */

.circle-ring::before {
    content:
        "";

    position:
        absolute;

    inset:
        14px;

    border-radius:
        50%;

    border:
        2px dashed
        rgba(
            255,
            255,
            255,
            .12
        );
}


.circle-ring::after {
    content:
        "";

    position:
        absolute;

    width:
        18px;

    height:
        18px;

    top:
        20px;

    right:
        42px;

    border-radius:
        50%;

    background:
        var(--yellow);

    box-shadow:
        0
        0
        18px
        rgba(
            255,
            189,
            54,
            .55
        );
}


/* =====================================================
   INNER CIRCLE
===================================================== */

.circle-inner {
    width:
        175px;

    height:
        175px;

    border-radius:
        50%;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    align-items:
        center;

    text-align:
        center;

    background:
        linear-gradient(
            180deg,
            rgba(
                255,
                255,
                255,
                .16
            ),
            rgba(
                255,
                255,
                255,
                .05
            )
        );

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .22
        );

    backdrop-filter:
        blur(
            14px
        );

    box-shadow:
        inset
        0
        0
        25px
        rgba(
            255,
            255,
            255,
            .10
        );
}


/* =====================================================
   NUMBER
===================================================== */

.circle-inner h1 {
    color:
        #FFFFFF;

    font-size:
        72px;

    font-weight:
        800;

    line-height:
        1;

    margin-bottom:
        10px;

    text-shadow:
        0
        3px
        8px
        rgba(
            0,
            0,
            0,
            .30
        );
}


/* =====================================================
   LABEL
===================================================== */

.circle-inner span {
    display:
        block;

    color:
        rgba(
            255,
            255,
            255,
            .92
        );

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        2px;

    text-transform:
        uppercase;

    line-height:
        1.5;
}


/* =====================================================
   HOVER EFFECT
===================================================== */

.hero-card:hover
.circle-ring {
    transform:
        rotate(
            2deg
        );

    transition:
        .35s ease;
}


.hero-card:hover
.circle-inner {
    transform:
        scale(
            1.03
        );

    transition:
        .35s ease;
}


/* =====================================================
   HERO CIRCLE RESPONSIVE
===================================================== */

@media (
    max-width:
    992px
) {

    .hero-circle {
        width:
            230px;

        height:
            230px;
    }


    .circle-inner {
        width:
            155px;

        height:
            155px;
    }


    .circle-inner h1 {
        font-size:
            60px;
    }

}


@media (
    max-width:
    768px
) {

    .hero-circle {
        width:
            200px;

        height:
            200px;
    }


    .circle-ring {
        border-width:
            10px;
    }


    .circle-inner {
        width:
            140px;

        height:
            140px;
    }


    .circle-inner h1 {
        font-size:
            50px;
    }


    .circle-inner span {
        font-size:
            11px;

        letter-spacing:
            1.5px;
    }

}


@media (
    max-width:
    480px
) {

    .hero-circle {
        width:
            170px;

        height:
            170px;
    }


    .circle-inner {
        width:
            118px;

        height:
            118px;
    }


    .circle-inner h1 {
        font-size:
            40px;
    }


    .circle-inner span {
        font-size:
            10px;
    }

}


/* =====================================================
   COMPONENT PILLS
===================================================== */

.component-pills {
    display:
        flex;

    flex-wrap:
        wrap;

    align-items:
        center;

    gap:
        14px;
}


/* =====================================================
   PILL BASE
===================================================== */

.pill {
    display:
        inline-flex;

    align-items:
        center;

    gap:
        8px;

    padding:
        12px
        18px;

    border-radius:
        999px;

    font-size:
        14px;

    font-weight:
        700;

    transition:
        all
        .30s
        ease;

    cursor:
        default;

    user-select:
        none;

    backdrop-filter:
        blur(
            10px
        );

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .15
        );
}


.pill strong {
    font-weight:
        800;

    letter-spacing:
        .4px;
}


.pill span {
    font-weight:
        600;
}


/* =====================================================
   CWTS
===================================================== */

.pill-green {
    background:
        rgba(
            88,
            118,
            28,
            .18
        );

    color:
        #DFF3C3;

    border-color:
        rgba(
            88,
            118,
            28,
            .45
        );
}


.pill-green:hover {
    background:
        #58761C;

    color:
        #FFFFFF;

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        10px
        20px
        rgba(
            88,
            118,
            28,
            .35
        );
}


/* =====================================================
   LTS
===================================================== */

.pill-gold {
    background:
        rgba(
            255,
            189,
            54,
            .18
        );

    color:
        #FFF3C5;

    border-color:
        rgba(
            255,
            189,
            54,
            .45
        );
}


.pill-gold:hover {
    background:
        #FFBD36;

    color:
        #54100F;

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        10px
        20px
        rgba(
            255,
            189,
            54,
            .35
        );
}


/* =====================================================
   ROTC
===================================================== */

.pill-blue {
    background:
        rgba(
            35,
            62,
            71,
            .32
        );

    color:
        #D8EDF4;

    border-color:
        rgba(
            255,
            255,
            255,
            .18
        );
}


.pill-blue:hover {
    background:
        #233E47;

    color:
        #FFFFFF;

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        10px
        20px
        rgba(
            35,
            62,
            71,
            .40
        );
}


/* =====================================================
   PILLS RESPONSIVE
===================================================== */

@media (
    max-width:
    992px
) {

    .hero-right {
        align-items:
            center;

        text-align:
            center;
    }


    .hero-right p {
        max-width:
            100%;
    }


    .component-pills {
        justify-content:
            center;
    }

}


@media (
    max-width:
    768px
) {

    .hero-right h2 {
        font-size:
            30px;
    }


    .hero-right p {
        font-size:
            15px;

        line-height:
            1.8;
    }


    .pill {
        width:
            100%;

        justify-content:
            center;
    }

}


@media (
    max-width:
    480px
) {

    .hero-right h2 {
        font-size:
            24px;
    }


    .hero-right p {
        font-size:
            14px;
    }


    .pill {
        font-size:
            13px;

        padding:
            11px
            16px;
    }

}


/* =====================================================
   STATISTICS GRID
===================================================== */

.statistics-grid {
    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            1fr
        );

    gap:
        24px;

    align-items:
        stretch;
}


/* =====================================================
   STAT CARD
===================================================== */

.stat-card {
    position:
        relative;

    background:
        #FFFFFF;

    border-radius:
        24px;

    padding:
        28px;

    overflow:
        hidden;

    border:
        1px solid
        rgba(
            0,
            0,
            0,
            .06
        );

    box-shadow:
        0
        10px
        30px
        rgba(
            0,
            0,
            0,
            .08
        );

    transition:
        all
        .30s
        ease;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        space-between;

    min-height:
        180px;
}


.stat-card:hover {
    transform:
        translateY(
            -6px
        );

    box-shadow:
        0
        18px
        40px
        rgba(
            0,
            0,
            0,
            .14
        );
}


/* =====================================================
   TOP BORDER COLORS
===================================================== */

.border-maroon {
    border-top:
        6px solid
        #54100F;
}


.border-green {
    border-top:
        6px solid
        #58761C;
}


.border-gold {
    border-top:
        6px solid
        #D99202;
}


.border-blue {
    border-top:
        6px solid
        #233E47;
}


/* =====================================================
   DECORATIVE ICON BACKGROUND
===================================================== */

.stat-card::after {
    content:
        "";

    position:
        absolute;

    top:
        -35px;

    right:
        -35px;

    width:
        120px;

    height:
        120px;

    border-radius:
        50%;

    background:
        rgba(
            84,
            16,
            15,
            .05
        );
}


.border-green::after {
    background:
        rgba(
            88,
            118,
            28,
            .08
        );
}


.border-gold::after {
    background:
        rgba(
            217,
            146,
            2,
            .08
        );
}


.border-blue::after {
    background:
        rgba(
            35,
            62,
            71,
            .08
        );
}


/* =====================================================
   TITLE
===================================================== */

.stat-title {
    color:
        #6E7378;

    font-size:
        13px;

    font-weight:
        800;

    text-transform:
        uppercase;

    letter-spacing:
        1px;

    margin-bottom:
        18px;
}


/* =====================================================
   NUMBER
===================================================== */

.stat-number {
    color:
        #54100F;

    font-size:
        46px;

    font-weight:
        800;

    line-height:
        1;

    margin-bottom:
        18px;
}


/* =====================================================
   FOOTER
===================================================== */

.stat-footer {
    margin-top:
        auto;

    font-size:
        14px;

    font-weight:
        600;

    display:
        flex;

    align-items:
        center;

    gap:
        8px;
}


.stat-footer.positive {
    color:
        #58761C;
}


.stat-footer.muted {
    color:
        #8B8B8B;
}


.stat-footer::before {
    content:
        "";

    width:
        8px;

    height:
        8px;

    border-radius:
        50%;

    background:
        currentColor;

    flex-shrink:
        0;
}


/* =====================================================
   CARD ANIMATION
===================================================== */

.stat-card:hover
.stat-number {
    transform:
        scale(
            1.05
        );

    transition:
        .25s ease;
}


.stat-card:hover
.stat-title {
    color:
        #54100F;
}


/* =====================================================
   STAT RESPONSIVE
===================================================== */

@media (
    max-width:
    1200px
) {

    .statistics-grid {
        grid-template-columns:
            repeat(
                2,
                1fr
            );
    }

}


@media (
    max-width:
    768px
) {

    .statistics-grid {
        grid-template-columns:
            1fr;

        gap:
            18px;
    }


    .stat-card {
        min-height:
            160px;

        padding:
            24px;
    }


    .stat-number {
        font-size:
            40px;
    }

}


@media (
    max-width:
    480px
) {

    .stat-card {
        padding:
            20px;
    }


    .stat-title {
        font-size:
            12px;
    }


    .stat-number {
        font-size:
            34px;
    }


    .stat-footer {
        font-size:
            13px;
    }

}


/* =====================================================
   CHARTS GRID
===================================================== */

.charts-grid {
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
        24px;

    align-items:
        start;
}


/* =====================================================
   DASHBOARD CARD
===================================================== */

.dashboard-card {
    background:
        #FFFFFF;

    border-radius:
        24px;

    border:
        1px solid
        rgba(
            0,
            0,
            0,
            .06
        );

    box-shadow:
        0
        10px
        30px
        rgba(
            0,
            0,
            0,
            .08
        );

    padding:
        28px;

    transition:
        .30s ease;
}


.dashboard-card:hover {
    transform:
        translateY(
            -4px
        );

    box-shadow:
        0
        18px
        40px
        rgba(
            0,
            0,
            0,
            .12
        );
}


/* =====================================================
   CARD HEADER
===================================================== */

.card-header {
    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        28px;
}


.card-header h3 {
    color:
        #54100F;

    font-size:
        22px;

    font-weight:
        800;

    margin:
        0;
}


.card-subtitle {
    color:
        #8A8A8A;

    font-size:
        13px;

    font-weight:
        600;
}


/* =====================================================
   REGION ROW
===================================================== */

.region-row {
    display:
        grid;

    grid-template-columns:
        140px
        1fr
        50px;

    gap:
        16px;

    align-items:
        center;

    margin-bottom:
        22px;
}


.region-row:last-child {
    margin-bottom:
        0;
}


.region-name {
    color:
        #233E47;

    font-size:
        14px;

    font-weight:
        700;
}


.region-total {
    color:
        #54100F;

    font-size:
        15px;

    font-weight:
        800;

    text-align:
        right;
}


/* =====================================================
   PROGRESS
===================================================== */

.progress-wrapper {
    width:
        100%;
}


.progress-bar {
    width:
        100%;

    height:
        12px;

    background:
        #EFEBE2;

    border-radius:
        999px;

    overflow:
        hidden;
}


.progress-fill {
    height:
        100%;

    border-radius:
        999px;

    transition:
        width
        .8s
        ease;
}


/* =====================================================
   REGION BAR
===================================================== */

.region-fill {
    background:
        linear-gradient(
            90deg,
            #54100F,
            #7B2A28
        );
}


/* =====================================================
   COMPONENT ROW
===================================================== */

.component-row {
    margin-bottom:
        24px;
}


.component-row:last-of-type {
    margin-bottom:
        30px;
}


.component-header {
    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        10px;
}


.component-name {
    color:
        #233E47;

    font-size:
        15px;

    font-weight:
        700;
}


.component-percent {
    color:
        #54100F;

    font-weight:
        800;

    font-size:
        14px;
}


/* =====================================================
   COMPONENT COLORS
===================================================== */

.cwts-fill {
    background:
        #58761C;
}


.lts-fill {
    background:
        #FFBD36;
}


.rotc-fill {
    background:
        #233E47;
}


/* =====================================================
   COMPONENT SUMMARY
===================================================== */

.component-summary {
    display:
        flex;

    justify-content:
        space-between;

    gap:
        14px;

    padding-top:
        24px;

    border-top:
        1px solid
        #ECECEC;
}


.summary-item {
    flex:
        1;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    background:
        #F8F8F8;

    border-radius:
        14px;

    padding:
        14px;

    font-size:
        14px;

    font-weight:
        700;

    color:
        #233E47;
}


.summary-item strong {
    color:
        #54100F;

    font-size:
        16px;

    margin-left:
        auto;
}


.summary-dot {
    width:
        10px;

    height:
        10px;

    border-radius:
        50%;

    flex-shrink:
        0;
}


.cwts-dot {
    background:
        #58761C;
}


.lts-dot {
    background:
        #FFBD36;
}


.rotc-dot {
    background:
        #233E47;
}


/* =====================================================
   CHART ANIMATION
===================================================== */

.region-row:hover
.progress-fill,
.component-row:hover
.progress-fill {
    filter:
        brightness(
            1.08
        );
}


.region-row:hover
.region-name,
.component-row:hover
.component-name {
    color:
        #54100F;
}


/* =====================================================
   CHART RESPONSIVE
===================================================== */

@media (
    max-width:
    1200px
) {

    .charts-grid {
        grid-template-columns:
            1fr;
    }

}


@media (
    max-width:
    768px
) {

    .dashboard-card {
        padding:
            22px;
    }


    .card-header {
        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            6px;
    }


    .region-row {
        grid-template-columns:
            110px
            1fr
            40px;

        gap:
            12px;
    }


    .component-summary {
        flex-direction:
            column;
    }

}


@media (
    max-width:
    480px
) {

    .region-row {
        grid-template-columns:
            1fr;

        gap:
            8px;
    }


    .region-total {
        text-align:
            left;
    }


    .component-header {
        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            6px;
    }

}


/* =====================================================
   RECENT UNIVERSITIES TABLE
===================================================== */

.recent-card {
    padding:
        0;

    overflow:
        hidden;
}


/* =====================================================
   TABLE HEADER
===================================================== */

.table-header {
    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    padding:
        26px
        30px;

    border-bottom:
        1px solid
        #EFEFEF;

    background:
        #FFFFFF;
}


.table-header h3 {
    color:
        #54100F;

    font-size:
        22px;

    font-weight:
        800;

    margin:
        0;
}


.table-subtitle {
    margin-top:
        7px;

    color:
        #8A8A8A;

    font-size:
        13px;

    font-weight:
        500;
}


.view-all-btn {
    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        12px
        22px;

    background:
        #54100F;

    color:
        #FFFFFF;

    border:
        none;

    border-radius:
        12px;

    font-size:
        14px;

    font-weight:
        700;

    cursor:
        pointer;

    transition:
        .30s ease;
}


.view-all-btn:hover {
    background:
        #6B1B19;

    transform:
        translateY(
            -2px
        );
}


/* =====================================================
   TABLE
===================================================== */

.recent-table {
    width:
        100%;

    border-collapse:
        collapse;

    background:
        #FFFFFF;
}


.recent-table thead {
    background:
        #F9F8F6;
}


.recent-table th {
    padding:
        18px
        30px;

    text-align:
        left;

    font-size:
        13px;

    font-weight:
        800;

    color:
        #233E47;

    text-transform:
        uppercase;

    letter-spacing:
        .7px;

    border-bottom:
        1px solid
        #ECECEC;
}


.recent-table td {
    padding:
        22px
        30px;

    font-size:
        15px;

    color:
        #233E47;

    border-bottom:
        1px solid
        #F3F3F3;

    vertical-align:
        middle;
}


.recent-table tbody tr {
    transition:
        .25s ease;
}


.recent-table tbody tr:hover {
    background:
        #FCFBF9;
}


.recent-table tbody tr:last-child
td {
    border-bottom:
        none;
}


/* =====================================================
   UNIVERSITY COLUMN
===================================================== */

.university-cell {
    display:
        flex;

    align-items:
        center;

    gap:
        14px;
}


.university-logo {
    width:
        52px;

    height:
        52px;

    border-radius:
        50%;

    overflow:
        hidden;

    background:
        #F4F4F4;

    border:
        2px solid
        #E6E0D7;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    flex-shrink:
        0;
}


.logo-image {
    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

    display:
        block;
}


.university-logo span {
    font-weight:
        700;

    color:
        #54100F;
}


.university-details {
    display:
        flex;

    flex-direction:
        column;

    gap:
        4px;
}


.university-name {
    color:
        #54100F;

    font-weight:
        800;

    font-size:
        15px;
}


.university-id {
    color:
        #8A8A8A;

    font-size:
        12px;

    margin-top:
        4px;
}


/* =====================================================
   REGION BADGE
===================================================== */

.region-badge {
    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        7px
        14px;

    border-radius:
        999px;

    background:
        #EEF4FF;

    color:
        #233E47;

    font-size:
        13px;

    font-weight:
        700;
}


/* =====================================================
   ADMIN
===================================================== */

.admin-cell {
    display:
        flex;

    align-items:
        center;

    gap:
        10px;
}


.admin-avatar {
    width:
        36px;

    height:
        36px;

    border-radius:
        50%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #EFEBE2;

    color:
        #54100F;

    font-size:
        14px;

    font-weight:
        800;

    flex-shrink:
        0;
}


.admin-name {
    font-weight:
        700;

    color:
        #233E47;
}


/* =====================================================
   ACCESS CODE
===================================================== */

.access-code {
    font-family:
        "Courier New",
        monospace;

    font-weight:
        700;

    color:
        #54100F;

    background:
        #F8F6F2;

    padding:
        8px
        14px;

    border-radius:
        10px;

    display:
        inline-block;

    letter-spacing:
        .8px;
}


/* =====================================================
   STATUS BADGE
===================================================== */

.status-badge {
    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        8px
        16px;

    border-radius:
        999px;

    font-size:
        13px;

    font-weight:
        700;

    min-width:
        90px;
}


.status-badge.active {
    background:
        #EAF6E5;

    color:
        #58761C;
}


.status-badge.pending {
    background:
        #FFF7E4;

    color:
        #D99202;
}


.status-badge.inactive {
    background:
        #FDECEC;

    color:
        #A52929;
}


/* =====================================================
   EMPTY TABLE
===================================================== */

.empty-table {
    text-align:
        center;

    padding:
        60px
        20px !important;

    color:
        #8A8A8A !important;

    font-size:
        15px !important;
}


/* =====================================================
   TABLE RESPONSIVE
===================================================== */

@media (
    max-width:
    992px
) {

    .table-header {
        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            15px;
    }


    .recent-table {
        display:
            block;

        overflow-x:
            auto;

        white-space:
            nowrap;
    }

}


@media (
    max-width:
    768px
) {

    .recent-table th,
    .recent-table td {
        padding:
            18px;
    }


    .view-all-btn {
        width:
            100%;
    }

}


@media (
    max-width:
    480px
) {

    .table-header {
        padding:
            20px;
    }


    .table-header h3 {
        font-size:
            19px;
    }


    .university-logo {
        width:
            40px;

        height:
            40px;
    }


    .university-name {
        font-size:
            14px;
    }

}


/* =====================================================
   RECENT ACTIVITY
===================================================== */

.activity-list {
    display:
        flex;

    flex-direction:
        column;

    gap:
        20px;
}


.activity-item {
    display:
        flex;

    align-items:
        flex-start;

    gap:
        18px;

    padding:
        18px;

    border-radius:
        18px;

    transition:
        all
        .30s
        ease;

    border:
        1px solid
        transparent;
}


.activity-item:hover {
    background:
        #FAF9F6;

    border-color:
        #ECE8DF;

    transform:
        translateX(
            4px
        );
}


/* =====================================================
   ACTIVITY LEFT
===================================================== */

.activity-left {
    position:
        relative;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    min-height:
        55px;
}


/* =====================================================
   ACTIVITY DOT
===================================================== */

.activity-dot {
    width:
        14px;

    height:
        14px;

    border-radius:
        50%;

    margin-top:
        6px;

    flex-shrink:
        0;

    position:
        relative;

    z-index:
        2;
}


.activity-dot::after {
    content:
        "";

    position:
        absolute;

    inset:
        -5px;

    border-radius:
        50%;

    opacity:
        .25;

    background:
        inherit;
}


.activity-dot.green {
    background:
        #58761C;
}


.activity-dot.gold {
    background:
        #D99202;
}


.activity-dot.maroon {
    background:
        #54100F;
}


.activity-dot.blue {
    background:
        #233E47;
}


.activity-line {
    width:
        2px;

    flex:
        1;

    min-height:
        30px;

    margin-top:
        8px;

    background:
        #E8E2D8;
}


/* =====================================================
   ACTIVITY CONTENT
===================================================== */

.activity-content {
    flex:
        1;
}


.activity-text {
    color:
        #233E47;

    font-size:
        15px;

    line-height:
        1.8;
}


.activity-text
:deep(strong) {
    color:
        #54100F;

    font-weight:
        800;
}


.activity-time {
    margin-top:
        8px;

    font-size:
        13px;

    color:
        #8B8B8B;

    font-weight:
        600;
}


/* =====================================================
   CARD TITLES
===================================================== */

.dashboard-card h3 {
    color:
        #54100F;

    font-size:
        22px;

    font-weight:
        800;

    margin-bottom:
        24px;
}


/* =====================================================
   BADGES
===================================================== */

.badge {
    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        6px
        14px;

    border-radius:
        999px;

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        .4px;
}


.badge-success {
    background:
        #EAF6E5;

    color:
        #58761C;
}


.badge-warning {
    background:
        #FFF4DB;

    color:
        #D99202;
}


.badge-danger {
    background:
        #FFECEC;

    color:
        #54100F;
}


.badge-info {
    background:
        #EAF2F5;

    color:
        #233E47;
}


/* =====================================================
   FADE ANIMATION
===================================================== */

@keyframes fadeUp {

    from {
        opacity:
            0;

        transform:
            translateY(
                18px
            );
    }


    to {
        opacity:
            1;

        transform:
            translateY(
                0
            );
    }

}


.hero-card,
.stat-card,
.dashboard-card {
    animation:
        fadeUp
        .5s
        ease;
}


/* =====================================================
   SCROLLBAR
===================================================== */

::-webkit-scrollbar {
    width:
        10px;

    height:
        10px;
}


::-webkit-scrollbar-thumb {
    background:
        #CFC7BB;

    border-radius:
        999px;
}


::-webkit-scrollbar-thumb:hover {
    background:
        #B7AEA0;
}


::-webkit-scrollbar-track {
    background:
        #F5F3EE;
}


/* =====================================================
   UTILITY CLASSES
===================================================== */

.text-maroon {
    color:
        #54100F;
}


.text-green {
    color:
        #58761C;
}


.text-gold {
    color:
        #D99202;
}


.text-blue {
    color:
        #233E47;
}


.bg-maroon {
    background:
        #54100F;
}


.bg-green {
    background:
        #58761C;
}


.bg-gold {
    background:
        #D99202;
}


.bg-blue {
    background:
        #233E47;
}


.shadow {
    box-shadow:
        0
        10px
        30px
        rgba(
            0,
            0,
            0,
            .08
        );
}


.rounded {
    border-radius:
        20px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (
    max-width:
    992px
) {

    .activity-item {
        padding:
            16px;
    }

}


@media (
    max-width:
    768px
) {

    .dashboard-card {
        padding:
            20px;
    }


    .dashboard-card h3 {
        font-size:
            20px;
    }


    .activity-text {
        font-size:
            14px;
    }


    .activity-time {
        font-size:
            12px;
    }

}


@media (
    max-width:
    480px
) {

    .activity-item {
        gap:
            12px;

        padding:
            14px;
    }


    .activity-dot {
        width:
            12px;

        height:
            12px;
    }


    .dashboard-card h3 {
        font-size:
            18px;
    }

}


/* =====================================================
   END OF DASHBOARD CSS
===================================================== */

</style>