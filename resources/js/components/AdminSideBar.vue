<template>
    <aside
        class="admin-sidebar"
        :class="{
            'admin-sidebar--collapsed': isCollapsed,
        }"
    >
        <!-- =====================================================
             TOP / HAMBURGER
        ====================================================== -->

        <div class="sidebar-top">

            <button
                type="button"
                class="menu-toggle"
                :aria-label="
                    isCollapsed
                        ? 'Expand sidebar'
                        : 'Collapse sidebar'
                "
                @click="toggleSidebar"
            >
                <FontAwesomeIcon
                    :icon="faBars"
                    class="menu-icon"
                />
            </button>

        </div>


        <!-- =====================================================
             PROFILE
        ====================================================== -->

        <Link
            v-if="!isCollapsed"
            :href="profileUrl"
            class="profile-section profile-link"
            :class="{
                'profile-section--active':
                    isActive(
                        profileUrl,
                        true
                    ),
            }"
            title="View Profile"
        >

            <div class="profile-photo-frame">

                <img
                    :src="profilePhotoUrl"
                    :alt="displayName"
                    class="profile-photo"
                    @error="handleProfileImageError"
                />

            </div>


            <div class="profile-name">
                {{ displayName }}
            </div>


            <div class="profile-role">
                {{ roleText }}
            </div>

        </Link>


        <!-- =====================================================
             BACK TO UNIVERSITY ADMIN DASHBOARD
        ====================================================== -->

        <div
            v-if="isUniversityAdmin"
            class="ua-back-area"
            :class="{
                'ua-back-area--collapsed':
                    isCollapsed,
            }"
        >

            <Link
                :href="universityAdminDashboardUrl"
                class="ua-back-button"
                title="Back to University Admin Dashboard"
                aria-label="Back to University Admin Dashboard"
            >

                <span class="ua-back-icon">
                    <FontAwesomeIcon
                        :icon="faArrowLeft"
                    />
                </span>


                <span
                    v-if="!isCollapsed"
                    class="ua-back-label"
                >
                    Back to University Admin
                </span>

            </Link>

        </div>


        <!-- =====================================================
             NAVIGATION
        ====================================================== -->

        <nav
            class="sidebar-navigation"
            :class="{
                'sidebar-navigation--collapsed':
                    isCollapsed,
            }"
        >

            <!-- ===================================================
                 DASHBOARD
            ==================================================== -->

            <Link
                :href="dashboardUrl"
                class="nav-item"
                :class="{
                    active:
                        isActive(
                            dashboardUrl,
                            true
                        ),
                }"
                title="Dashboard"
            >

                <span class="nav-icon">

                    <FontAwesomeIcon
                        :icon="faHouse"
                    />

                </span>


                <span
                    v-if="!isCollapsed"
                    class="nav-label"
                >
                    Dashboard
                </span>

            </Link>


            <!-- ===================================================
                 STUDENT INFORMATION
            ==================================================== -->

            <Link
                :href="studentInformationUrl"
                class="nav-item"
                :class="{
                    active:
                        isActive(
                            studentInformationUrl
                        ),
                }"
                title="Student's Information"
            >

                <span class="nav-icon">

                    <FontAwesomeIcon
                        :icon="faUserGroup"
                    />

                </span>


                <span
                    v-if="!isCollapsed"
                    class="nav-label"
                >
                    Student's Information
                </span>

            </Link>


            <!-- ===================================================
                 ATTENDANCE
            ==================================================== -->

            <Link
                :href="attendanceUrl"
                class="nav-item"
                :class="{
                    active:
                        isActive(
                            attendanceUrl
                        ),
                }"
                title="Attendance"
            >

                <span class="nav-icon">

                    <FontAwesomeIcon
                        :icon="faUserCheck"
                    />

                </span>


                <span
                    v-if="!isCollapsed"
                    class="nav-label"
                >
                    Attendance
                </span>

            </Link>


            <!-- ===================================================
                 ANNOUNCEMENT
            ==================================================== -->

            <Link
                :href="announcementUrl"
                class="nav-item"
                :class="{
                    active:
                        isActive(
                            announcementUrl
                        ),
                }"
                title="Announcement"
            >

                <span class="nav-icon">

                    <FontAwesomeIcon
                        :icon="faBullhorn"
                    />

                </span>


                <span
                    v-if="!isCollapsed"
                    class="nav-label"
                >
                    Announcement
                </span>

            </Link>


            <!-- ===================================================
                 SCHEDULE
            ==================================================== -->

            <Link
                :href="scheduleUrl"
                class="nav-item"
                :class="{
                    active:
                        isActive(
                            scheduleUrl
                        ),
                }"
                title="Schedule"
            >

                <span class="nav-icon">

                    <FontAwesomeIcon
                        :icon="faCalendarDays"
                    />

                </span>


                <span
                    v-if="!isCollapsed"
                    class="nav-label"
                >
                    Schedule
                </span>

            </Link>


            <!-- ===================================================
                 EXCUSE LETTER
            ==================================================== -->

            <Link
                :href="excuseLetterUrl"
                class="nav-item"
                :class="{
                    active:
                        isActive(
                            excuseLetterUrl
                        ),
                }"
                title="Excuse Letter"
            >

                <span class="nav-icon">

                    <FontAwesomeIcon
                        :icon="faEnvelope"
                    />

                </span>


                <span
                    v-if="!isCollapsed"
                    class="nav-label"
                >
                    Excuse Letter
                </span>

            </Link>

        </nav>

    </aside>
</template>


<script setup>

import {
    computed,
    ref,
} from 'vue';

import {
    Link,
    usePage,
} from '@inertiajs/vue3';


/*
|--------------------------------------------------------------------------
| Font Awesome
|--------------------------------------------------------------------------
*/

import {
    FontAwesomeIcon,
} from '@fortawesome/vue-fontawesome';

import {
    faArrowLeft,
    faBars,
    faBullhorn,
    faCalendarDays,
    faEnvelope,
    faHouse,
    faUserCheck,
    faUserGroup,
} from '@fortawesome/free-solid-svg-icons';


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


    component: {
        type: String,
        default: '',
    },


    defaultCollapsed: {
        type: Boolean,
        default: false,
    },

});


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'collapse-change',
]);


/*
|--------------------------------------------------------------------------
| Current Inertia Page
|--------------------------------------------------------------------------
*/

const page = usePage();


/*
|--------------------------------------------------------------------------
| Role / Component Context
|--------------------------------------------------------------------------
*/

const normalizedRole = computed(() =>
    String(
        props.role
        ?? ''
    )
        .trim()
        .toLowerCase()
        .replace(
            /_/g,
            '-'
        )
);


const isUniversityAdmin = computed(() =>
    normalizedRole.value ===
    'university-admin'
);


const componentName = computed(() => {

    const value =
        String(
            props.component
            ?? props.user?.component
            ?? props.user?.assigned_component
            ?? props.user?.nstp_component
            ?? props.user?.coordinator_component
            ?? ''
        )
            .trim()
            .toUpperCase();


    return [
        'LTS',
        'CWTS',
        'ROTC',
    ].includes(
        value
    )
        ? value
        : '';

});


const withComponent = (
    baseUrl
) => {

    const component =
        componentName.value;


    if (
        !component
    ) {

        return baseUrl;
    }


    const separator =
        baseUrl.includes(
            '?'
        )
            ? '&'
            : '?';


    return (
        `${baseUrl}${separator}component=${encodeURIComponent(component)}`
    );
};


/*
|--------------------------------------------------------------------------
| Profile Route
|--------------------------------------------------------------------------
*/

const profileUrl = computed(() =>
    isUniversityAdmin.value
        ? '/university-admin/uniadminprofile'
        : '/instructor-coordinator/profile'
);


/*
|--------------------------------------------------------------------------
| University Admin Dashboard Route
|--------------------------------------------------------------------------
|
| This route is used by the back button shown only while a University Admin
| is viewing the Instructor/Coordinator component workspace.
|
*/

const universityAdminDashboardUrl =
    '/university-admin/dashboard';


/*
|--------------------------------------------------------------------------
| Dashboard Route
|--------------------------------------------------------------------------
*/

const dashboardUrl = computed(() => {

    if (
        isUniversityAdmin.value
        &&
        componentName.value
    ) {

        return (
            `/university-admin/components/${componentName.value.toLowerCase()}`
        );
    }


    return withComponent(
        '/instructor-coordinator/dashboard'
    );
});


/*
|--------------------------------------------------------------------------
| Student Information Route
|--------------------------------------------------------------------------
*/

const studentInformationUrl = computed(() =>
    withComponent(
        '/instructor-coordinator/students'
    )
);


/*
|--------------------------------------------------------------------------
| Attendance Route
|--------------------------------------------------------------------------
*/

const attendanceUrl = computed(() =>
    withComponent(
        '/instructor-coordinator/attendance'
    )
);


/*
|--------------------------------------------------------------------------
| Announcement Route
|--------------------------------------------------------------------------
*/

const announcementUrl = computed(() =>
    withComponent(
        '/instructor-coordinator/announcements'
    )
);


/*
|--------------------------------------------------------------------------
| Schedule Route
|--------------------------------------------------------------------------
*/

const scheduleUrl = computed(() =>
    withComponent(
        '/instructor-coordinator/schedules'
    )
);


/*
|--------------------------------------------------------------------------
| Excuse Letter Route
|--------------------------------------------------------------------------
*/

const excuseLetterUrl = computed(() =>
    withComponent(
        '/instructor-coordinator/excuse-letters'
    )
);


/*
|--------------------------------------------------------------------------
| Sidebar State
|--------------------------------------------------------------------------
*/

const isCollapsed = ref(
    props.defaultCollapsed
);


/*
|--------------------------------------------------------------------------
| Toggle Sidebar
|--------------------------------------------------------------------------
*/

const toggleSidebar = () => {

    isCollapsed.value =
        !isCollapsed.value;


    emit(
        'collapse-change',
        isCollapsed.value
    );

};


/*
|--------------------------------------------------------------------------
| Display Name
|--------------------------------------------------------------------------
*/

const displayName = computed(() => {

    const administratorName =
        [
            props.user?.first_name,
            props.user?.middle_name,
            props.user?.last_name,
        ]
            .filter(Boolean)
            .join(' ')
            .trim();


    const name =
        props.user?.full_name ||
        props.user?.name ||
        administratorName ||
        'NSTP USER';


    return String(
        name
    ).toUpperCase();

});


/*
|--------------------------------------------------------------------------
| Role Text
|--------------------------------------------------------------------------
*/

const roleText = computed(() => {

    const component =
        componentName.value;


    const role =
        normalizedRole.value;


    /*
    |--------------------------------------------------------------------------
    | University Administrator
    |--------------------------------------------------------------------------
    */

    if (
        role ===
        'university-admin'
    ) {

        return component
            ? `UNIVERSITY ADMIN - ${component}`
            : 'UNIVERSITY ADMIN';
    }


    /*
    |--------------------------------------------------------------------------
    | Instructor
    |--------------------------------------------------------------------------
    */

    if (
        role ===
        'instructor'
    ) {

        return component
            ? `NSTP Instructor - ${component}`
            : 'NSTP Instructor';
    }


    /*
    |--------------------------------------------------------------------------
    | Attendance Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        role ===
        'coordinator-attendance'
    ) {

        return component
            ? `NSTP Coordinator: Attendance - ${component}`
            : 'NSTP Coordinator: Attendance';
    }


    /*
    |--------------------------------------------------------------------------
    | Announcement Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        role ===
        'coordinator-announcement'
    ) {

        return component
            ? `NSTP Coordinator: Announcement - ${component}`
            : 'NSTP Coordinator: Announcement';
    }


    /*
    |--------------------------------------------------------------------------
    | Schedule Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        role ===
        'coordinator-schedule'
    ) {

        return component
            ? `NSTP Coordinator: Schedule - ${component}`
            : 'NSTP Coordinator: Schedule';
    }


    /*
    |--------------------------------------------------------------------------
    | Generic Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        role.includes(
            'coordinator'
        )
    ) {

        return component
            ? `NSTP Coordinator - ${component}`
            : 'NSTP Coordinator';
    }


    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    */

    return component
        ? `NSTP Staff - ${component}`
        : 'NSTP Staff';

});


/*
|--------------------------------------------------------------------------
| Profile Photo URL
|--------------------------------------------------------------------------
*/

const profilePhotoUrl = computed(() => {

    const photo =
        props.user?.profile_photo
        ?? props.user?.photo;


    if (
        !photo
    ) {

        return '/images/default-avatar.png';

    }


    const value =
        String(
            photo
        );


    if (
        value.startsWith(
            'http://'
        ) ||
        value.startsWith(
            'https://'
        )
    ) {

        return value;

    }


    if (
        value.startsWith(
            '/storage/'
        )
    ) {

        return value;

    }


    if (
        value.startsWith(
            '/'
        )
    ) {

        return value;

    }


    return `/storage/${value}`;

});


/*
|--------------------------------------------------------------------------
| Handle Broken Profile Image
|--------------------------------------------------------------------------
*/

const handleProfileImageError = (
    event
) => {

    if (
        event.target.src.includes(
            'default-avatar.png'
        )
    ) {

        return;

    }


    event.target.src =
        '/images/default-avatar.png';

};


/*
|--------------------------------------------------------------------------
| Active Route
|--------------------------------------------------------------------------
|
| Prefix checking means Attendance remains highlighted on:
|
| /attendance
| /attendance/students/{student}
|
| Student Information remains highlighted on:
|
| /students
| /students/{student}
| /students/{student}/edit
|
| Excuse Letter remains highlighted on:
|
| /excuse-letters
| /excuse-letters/{excuseLetter}/...
|
*/

const isActive = (
    path,
    exact = false
) => {

    const currentUrl =
        String(
            page.url
            ?? ''
        )
            .split(
                '?'
            )[0]
            .replace(
                /\/+$/,
                ''
            );


    const targetUrl =
        String(
            path?.value
            ?? path
            ?? ''
        )
            .split(
                '?'
            )[0]
            .replace(
                /\/+$/,
                ''
            );


    if (
        !targetUrl
    ) {

        return false;
    }


    if (
        exact
    ) {

        return currentUrl ===
            targetUrl;
    }


    return (
        currentUrl === targetUrl
        ||
        currentUrl.startsWith(
            `${targetUrl}/`
        )
    );

};

</script>


<style scoped>

/* ==========================================================
   PALETTE

   Cream       #EFEBE2
   Maroon      #54100F
   Green       #58761C
   Yellow      #FFBD36
   Orange      #D99202
   Blue Gray   #233E47
   Near Black  #000D12
   White       #FFFFFF
   Gray        #BEBEBE
   Dark        #0D171B
========================================================== */


/* ==========================================================
   SIDEBAR
========================================================== */

.admin-sidebar {

    width:
        320px;

    min-width:
        250px;

    height:
        100vh;

    background:
        #EFEBE2;

    border-right:
        1px
        solid
        #BEBEBE;

    box-sizing:
        border-box;

    display:
        flex;

    flex-direction:
        column;

    position:
        relative;

    overflow:
        hidden;

    transition:
        width
        0.28s
        ease,
        min-width
        0.28s
        ease;

}


/* ==========================================================
   COLLAPSED SIDEBAR
========================================================== */

.admin-sidebar--collapsed {

    width:
        72px;

    min-width:
        72px;

}


/* ==========================================================
   SIDEBAR TOP
========================================================== */

.sidebar-top {

    min-height:
        88px;

    display:
        flex;

    align-items:
        flex-start;

    padding:
        20px
        0
        0
        28px;

    box-sizing:
        border-box;

}


.admin-sidebar--collapsed
.sidebar-top {

    padding:
        20px
        0
        0;

    justify-content:
        center;

}


/* ==========================================================
   HAMBURGER
========================================================== */

.menu-toggle {

    width:
        48px;

    height:
        48px;

    padding:
        5px;

    border:
        none;

    background:
        transparent;

    color:
        #58761C;

    cursor:
        pointer;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        8px;

    transition:
        background-color
        0.2s
        ease,
        color
        0.2s
        ease,
        transform
        0.2s
        ease;

}


.menu-toggle:hover {

    color:
        #D99202;

    background:
        rgba(
            88,
            118,
            28,
            0.08
        );

}


.menu-toggle:active {

    transform:
        scale(
            0.95
        );

}


.menu-icon {

    width:
        34px;

    height:
        34px;

}


/* ==========================================================
   PROFILE SECTION
========================================================== */

.profile-section {

    width:
        100%;

    padding:
        15px
        20px
        50px;

    box-sizing:
        border-box;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

}


/* ==========================================================
   PROFILE LINK
========================================================== */

.profile-link {

    text-decoration:
        none;

    color:
        inherit;

    cursor:
        pointer;

    transition:
        background-color
        0.2s
        ease;

}


.profile-link:hover {

    background:
        rgba(
            88,
            118,
            28,
            0.05
        );

}


/* ==========================================================
   ACTIVE PROFILE
========================================================== */

.profile-section--active
.profile-photo-frame {

    border-color:
        #D99202;

}


.profile-section--active
.profile-name {

    color:
        #D99202;

}


/* ==========================================================
   PROFILE PHOTO FRAME
========================================================== */

.profile-photo-frame {

    width:
        150px;

    height:
        150px;

    border-radius:
        50%;

    border:
        2px
        solid
        #58761C;

    background:
        #FFFFFF;

    overflow:
        hidden;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    flex-shrink:
        0;

    transition:
        border-color
        0.2s
        ease,
        transform
        0.2s
        ease,
        box-shadow
        0.2s
        ease;

}


.profile-link:hover
.profile-photo-frame {

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        5px
        12px
        rgba(
            88,
            118,
            28,
            0.15
        );

}


/* ==========================================================
   PROFILE PHOTO
========================================================== */

.profile-photo {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

    display:
        block;

}


/* ==========================================================
   PROFILE NAME
========================================================== */

.profile-name {

    width:
        100%;

    margin-top:
        24px;

    color:
        #58761C;

    text-align:
        center;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        16px;

    font-weight:
        500;

    line-height:
        1.4;

    white-space:
        nowrap;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

    transition:
        color
        0.2s
        ease;

}


.profile-link:hover
.profile-name {

    color:
        #D99202;

}


/* ==========================================================
   PROFILE ROLE
========================================================== */

.profile-role {

    width:
        100%;

    margin-top:
        10px;

    color:
        #233E47;

    text-align:
        center;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        12px;

    font-weight:
        700;

    line-height:
        1.4;

    white-space:
        nowrap;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

}


/* ==========================================================
   UNIVERSITY ADMIN BACK BUTTON
========================================================== */

.ua-back-area {

    width: 100%;

    padding:
        0
        28px
        18px;

    box-sizing: border-box;

}


.ua-back-button {

    width: 100%;

    min-height: 46px;

    padding:
        8px
        12px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.22
        );

    border-radius: 9px;

    display: flex;

    align-items: center;

    gap: 12px;

    background:
        rgba(
            255,
            255,
            255,
            0.42
        );

    color: #54100F;

    text-decoration: none;

    box-sizing: border-box;

    transition:
        background-color 0.18s ease,
        border-color 0.18s ease,
        color 0.18s ease,
        transform 0.18s ease;

}


.ua-back-button:hover {

    background: #54100F;

    border-color: #54100F;

    color: #FFFFFF;

    transform:
        translateX(-2px);

}


.ua-back-icon {

    width: 30px;

    height: 30px;

    flex:
        0
        0
        30px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

}


.ua-back-icon svg {

    width: 22px;

    height: 22px;

}


.ua-back-label {

    min-width: 0;

    overflow: hidden;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 12px;

    font-weight: 700;

    line-height: 1.25;

    text-transform: uppercase;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.ua-back-area--collapsed {

    padding:
        0
        10px
        18px;

}


.ua-back-area--collapsed
.ua-back-button {

    width: 52px;

    height: 48px;

    min-height: 48px;

    padding: 0;

    margin: 0 auto;

    justify-content: center;

}


.ua-back-area--collapsed
.ua-back-icon {

    width: 30px;

    height: 30px;

    flex-basis: 30px;

}


/* ==========================================================
   NAVIGATION
========================================================== */

.sidebar-navigation {

    width:
        100%;

    padding:
        0
        28px
        30px;

    box-sizing:
        border-box;

    display:
        flex;

    flex-direction:
        column;

    gap:
        10px;

}


/* ==========================================================
   COLLAPSED NAVIGATION
========================================================== */

.sidebar-navigation--collapsed {

    padding:
        125px
        0
        30px;

    align-items:
        center;

    gap:
        9px;

}


/* ==========================================================
   NAV ITEM
========================================================== */

.nav-item {

    width:
        100%;

    min-height:
        46px;

    padding:
        6px
        4px;

    box-sizing:
        border-box;

    border-radius:
        8px;

    display:
        flex;

    align-items:
        center;

    gap:
        16px;

    color:
        #233E47;

    text-decoration:
        none;

    transition:
        color
        0.18s
        ease,
        background-color
        0.18s
        ease;

}


/* ==========================================================
   NAV HOVER
========================================================== */

.nav-item:hover {

    color:
        #D99202;

    background:
        rgba(
            217,
            146,
            2,
            0.05
        );

}


/* ==========================================================
   ACTIVE
========================================================== */

.nav-item.active {

    color:
        #D99202;

}


/* ==========================================================
   COLLAPSED NAV ITEM
========================================================== */

.admin-sidebar--collapsed
.nav-item {

    width:
        52px;

    height:
        48px;

    min-height:
        48px;

    padding:
        0;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        0;

}


/* ==========================================================
   ICON HOLDER
========================================================== */

.nav-icon {

    width:
        32px;

    height:
        32px;

    min-width:
        32px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    color:
        currentColor;

}


/* ==========================================================
   REAL FONT AWESOME ICONS
========================================================== */

.nav-icon svg {

    width:
        30px;

    height:
        30px;

    color:
        currentColor;

    fill:
        currentColor;

    display:
        block;

}


/* ==========================================================
   INDIVIDUAL ICON ADJUSTMENTS
========================================================== */

.nav-item:nth-child(1)
.nav-icon svg {

    width:
        31px;

    height:
        31px;

}


.nav-item:nth-child(2)
.nav-icon svg {

    width:
        33px;

    height:
        33px;

}


.nav-item:nth-child(3)
.nav-icon svg {

    width:
        31px;

    height:
        31px;

}


.nav-item:nth-child(4)
.nav-icon svg {

    width:
        32px;

    height:
        32px;

}


.nav-item:nth-child(5)
.nav-icon svg {

    width:
        31px;

    height:
        31px;

}


.nav-item:nth-child(6)
.nav-icon svg {

    width:
        32px;

    height:
        32px;

}


/* ==========================================================
   NAV LABEL
========================================================== */

.nav-label {

    color:
        currentColor;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        13px;

    font-weight:
        500;

    text-transform:
        uppercase;

    white-space:
        nowrap;

}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (
    max-width: 1200px
) {

    .admin-sidebar {

        width:
            245px;

        min-width:
            245px;

    }


    .admin-sidebar--collapsed {

        width:
            72px;

        min-width:
            72px;

    }


    .profile-photo-frame {

        width:
            125px;

        height:
            125px;

    }


    .profile-name {

        font-size:
            11px;

    }


    .profile-role {

        font-size:
            8px;

    }


    .nav-label {

        font-size:
            11px;

    }

}


/* ==========================================================
   TABLET / SMALL SCREEN
========================================================== */

@media (
    max-width: 900px
) {

    .admin-sidebar {

        width:
            230px;

        min-width:
            230px;

    }


    .admin-sidebar--collapsed {

        width:
            68px;

        min-width:
            68px;

    }


    .sidebar-top {

        padding-left:
            20px;

    }


    .admin-sidebar--collapsed
    .sidebar-top {

        padding-left:
            0;

    }


    .profile-photo-frame {

        width:
            105px;

        height:
            105px;

    }


    .sidebar-navigation {

        padding-left:
            20px;

        padding-right:
            20px;

    }


    .sidebar-navigation--collapsed {

        padding-left:
            0;

        padding-right:
            0;

    }

}

</style>