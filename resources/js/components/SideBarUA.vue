<script setup>
import {
  computed,
  ref,
  watch,
} from 'vue';

import {
  router,
  usePage,
} from '@inertiajs/vue3';


/*
|--------------------------------------------------------------------------
| Inertia Page
|--------------------------------------------------------------------------
*/

const page = usePage();


/*
|--------------------------------------------------------------------------
| Current URL
|--------------------------------------------------------------------------
*/

const currentUrl = computed(() => {
  return page.url ?? '';
});


/*
|--------------------------------------------------------------------------
| Current University Administrator
|--------------------------------------------------------------------------
*/

const universityAdmin = computed(() => {

  return (
    page.props.admin ??
    page.props.auth?.user ??
    null
  );

});


/*
|--------------------------------------------------------------------------
| University Administrator Profile Photo
|--------------------------------------------------------------------------
*/

const profilePhoto = computed(() => {

  return (
    universityAdmin.value?.photo ??
    universityAdmin.value?.profile_photo ??
    null
  );

});


/*
|--------------------------------------------------------------------------
| Profile Photo URL
|--------------------------------------------------------------------------
*/

const profilePhotoUrl = computed(() => {

  const photo = profilePhoto.value;

  if (!photo) {
    return null;
  }


  /*
  |--------------------------------------------------------------------------
  | External URL
  |--------------------------------------------------------------------------
  */

  if (
    photo.startsWith('http://') ||
    photo.startsWith('https://')
  ) {
    return photo;
  }


  /*
  |--------------------------------------------------------------------------
  | Already A Storage URL
  |--------------------------------------------------------------------------
  */

  if (
    photo.startsWith('/storage/')
  ) {
    return photo;
  }


  /*
  |--------------------------------------------------------------------------
  | Public URL
  |--------------------------------------------------------------------------
  */

  if (
    photo.startsWith('/')
  ) {
    return photo;
  }


  /*
  |--------------------------------------------------------------------------
  | Laravel Public Storage
  |--------------------------------------------------------------------------
  */

  return `/storage/${photo}`;

});


/*
|--------------------------------------------------------------------------
| Image Loading Error
|--------------------------------------------------------------------------
*/

const profilePhotoFailed = ref(false);


const handleProfilePhotoError = () => {

  profilePhotoFailed.value = true;

};


/*
|--------------------------------------------------------------------------
| Reset Error When Photo Changes
|--------------------------------------------------------------------------
*/

watch(
  profilePhotoUrl,
  () => {

    profilePhotoFailed.value = false;

  }
);


/*
|--------------------------------------------------------------------------
| Administrator Initials
|--------------------------------------------------------------------------
*/

const administratorInitials = computed(() => {

  const firstName =
    universityAdmin.value?.first_name ??
    '';

  const lastName =
    universityAdmin.value?.last_name ??
    '';

  const firstInitial =
    firstName
      .trim()
      .charAt(0)
      .toUpperCase();

  const lastInitial =
    lastName
      .trim()
      .charAt(0)
      .toUpperCase();

  const initials =
    `${firstInitial}${lastInitial}`;

  return initials || 'UA';

});


/*
|--------------------------------------------------------------------------
| Profile Image Available
|--------------------------------------------------------------------------
*/

const hasProfilePhoto = computed(() => {

  return (
    Boolean(profilePhotoUrl.value) &&
    !profilePhotoFailed.value
  );

});


/*
|--------------------------------------------------------------------------
| Current Sidebar Route
|--------------------------------------------------------------------------
*/

const currentRoute = computed(() => {

  const url =
    currentUrl.value
      .split('?')[0];


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
    return 'students-registration';
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
  | Students
  |--------------------------------------------------------------------------
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
  | Components - LTS
  |--------------------------------------------------------------------------
  */

  if (
    url.startsWith(
      '/university-admin/components/lts'
    )
  ) {
    return 'comp-lts';
  }


  /*
  |--------------------------------------------------------------------------
  | Components - CWTS
  |--------------------------------------------------------------------------
  */

  if (
    url.startsWith(
      '/university-admin/components/cwts'
    )
  ) {
    return 'comp-cwts';
  }


  /*
  |--------------------------------------------------------------------------
  | Components - ROTC
  |--------------------------------------------------------------------------
  */

  if (
    url.startsWith(
      '/university-admin/components/rotc'
    )
  ) {
    return 'comp-rotc';
  }


  /*
  |--------------------------------------------------------------------------
  | Reports - LTS
  |--------------------------------------------------------------------------
  */

  if (
    url.startsWith(
      '/university-admin/reports/lts'
    )
  ) {
    return 'rep-lts';
  }


  /*
  |--------------------------------------------------------------------------
  | Reports - CWTS
  |--------------------------------------------------------------------------
  */

  if (
    url.startsWith(
      '/university-admin/reports/cwts'
    )
  ) {
    return 'rep-cwts';
  }


  /*
  |--------------------------------------------------------------------------
  | Reports - ROTC
  |--------------------------------------------------------------------------
  */

  if (
    url.startsWith(
      '/university-admin/reports/rotc'
    )
  ) {
    return 'rep-rotc';
  }


  /*
  |--------------------------------------------------------------------------
  | Reports Main
  |--------------------------------------------------------------------------
  */

  if (
    url ===
    '/university-admin/reports'
  ) {
    return 'rep-lts';
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
  ) {
    return 'profile';
  }


  /*
  |--------------------------------------------------------------------------
  | Default
  |--------------------------------------------------------------------------
  */

  return '';

});


/*
|--------------------------------------------------------------------------
| Active Dropdown Group
|--------------------------------------------------------------------------
*/

const activeGroup = ref(null);


/*
|--------------------------------------------------------------------------
| Toggle Dropdown
|--------------------------------------------------------------------------
*/

const toggleGroup = (
  group
) => {

  activeGroup.value =
    activeGroup.value === group
      ? null
      : group;

};


/*
|--------------------------------------------------------------------------
| Check If Dropdown Group Is Active
|--------------------------------------------------------------------------
*/

const isGroupActive = (
  group
) => {

  const groups = {

    users: [
      'instructors',
      'coordinators',
      'students-list',
    ],

    components: [
      'comp-lts',
      'comp-cwts',
      'comp-rotc',
    ],

    reports: [
      'rep-lts',
      'rep-cwts',
      'rep-rotc',
    ],

  };


  return (
    activeGroup.value === group ||
    groups[group]?.includes(
      currentRoute.value
    )
  );

};


/*
|--------------------------------------------------------------------------
| Navigate
|--------------------------------------------------------------------------
*/

const navigateTo = (
  url
) => {

  router.visit(
    url,
    {
      preserveScroll: true,
    }
  );

};

</script>


<template>

  <aside class="sidebar">

    <!-- ============================================================
         SIDEBAR HEADER
    ============================================================= -->

    <div class="sidebar-header">

      <img
        src="/images/nstphub_logo.png"
        alt="NSTP Hub"
        class="sidebar-logo"
      />


      <h2 class="sidebar-title">
        NATIONAL SERVICE TRAINING PROGRAM
      </h2>


      <p class="sidebar-subtitle">
        HUB
      </p>

    </div>


    <!-- ============================================================
         SIDEBAR NAVIGATION
    ============================================================= -->

    <nav class="sidebar-nav">


      <!-- ==========================================================
           DASHBOARD
      =========================================================== -->

      <div
        class="menu-item"
        :class="{
          active:
            currentRoute ===
            'dashboard'
        }"
        @click="
          navigateTo(
            '/university-admin/dashboard'
          )
        "
      >

        <svg
          class="menu-icon"
          viewBox="0 0 24 24"
          fill="currentColor"
        >
          <path
            d="
              M10 20
              v-6
              h4
              v6
              h5
              v-8
              h3
              L12 3
              2 12
              h3
              v8
              z
            "
          />
        </svg>


        <span class="menu-text">
          DASHBOARD
        </span>

      </div>


      <!-- ==========================================================
           STUDENT REGISTRATION
      =========================================================== -->

      <div
        class="menu-item"
        :class="{
          active:
            currentRoute ===
            'students-registration'
        }"
        @click="
          navigateTo(
            '/university-admin/student-registration'
          )
        "
      >

        <svg
          class="menu-icon"
          viewBox="0 0 24 24"
          fill="currentColor"
        >
          <path
            d="
              M15 12
              c2.21 0 4-1.79 4-4
              s-1.79-4-4-4
              -4 1.79-4 4
              1.79 4 4 4
              z

              m-9-2
              V7
              H4
              v3
              H1
              v2
              h3
              v3
              h2
              v-3
              h3
              v-2
              H6
              z

              m9 4
              c-2.67 0-8 1.34-8 4
              v2
              h16
              v-2
              c0-2.66-5.33-4-8-4
              z
            "
          />
        </svg>


        <span class="menu-text">
          STUDENT'S REGISTRATION
        </span>

      </div>


      <!-- ==========================================================
           USERS
      =========================================================== -->

      <div class="menu-group">

        <div
          class="menu-item"
          :class="{
            active:
              isGroupActive(
                'users'
              )
          }"
          @click="
            toggleGroup(
              'users'
            )
          "
        >

          <svg
            class="menu-icon"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path
              d="
                M16 11
                c1.66 0 2.99-1.34 2.99-3
                S17.66 5 16 5
                c-1.66 0-3 1.34-3 3
                s1.34 3 3 3
                z

                m-8 0
                c1.66 0 2.99-1.34 2.99-3
                S9.66 5 8 5
                C6.34 5 5 6.34 5 8
                s1.34 3 3 3
                z

                m0 2
                c-2.33 0-7 1.17-7 3.5
                V19
                h14
                v-2.5
                c0-2.33-4.67-3.5-7-3.5
                z

                m8 0
                c-.29 0-.62.02-.97.05
                1.16.84 1.97 1.97 1.97 3.45
                V19
                h6
                v-2.5
                c0-2.33-4.67-3.5-7-3.5
                z
              "
            />
          </svg>


          <span class="menu-text">
            USER'S
          </span>

        </div>


        <div
          v-show="
            activeGroup === 'users' ||
            isGroupActive('users')
          "
          class="sub-menu"
        >

          <a
            href="/university-admin/instructors"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'instructors'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/instructors'
              )
            "
          >
            Instructors
          </a>


          <a
            href="/university-admin/coordinators"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'coordinators'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/coordinators'
              )
            "
          >
            Coordinators
          </a>


          <a
            href="/university-admin/users/students"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'students-list'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/users/students'
              )
            "
          >
            Students
          </a>

        </div>

      </div>


      <!-- ==========================================================
           COMPONENTS
      =========================================================== -->

      <div class="menu-group">

        <div
          class="menu-item"
          :class="{
            active:
              isGroupActive(
                'components'
              )
          }"
          @click="
            toggleGroup(
              'components'
            )
          "
        >

          <svg
            class="menu-icon"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path
              d="
                M21.5 9.75
                l-9-6.5
                a1 1 0 00-1 0
                l-9 6.5
                a1 1 0 000 1.5
                l9 6.5
                a1 1 0 001 0
                l9-6.5
                a1 1 0 000-1.5
                z

                M12 4.61
                l7.33 5.3
                -7.33 5.3
                -7.33-5.3
                L12 4.61
                z

                M3 13.11
                l8 5.78
                v5.5
                l-8-5.78
                v-5.5
                z

                m18 0
                v5.5
                l-8 5.78
                v-5.5
                l8-5.78
                z
              "
            />
          </svg>


          <span class="menu-text">
            COMPONENTS
          </span>

        </div>


        <div
          v-show="
            activeGroup ===
              'components' ||
            isGroupActive(
              'components'
            )
          "
          class="sub-menu"
        >

          <a
            href="/university-admin/components/lts"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'comp-lts'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/components/lts'
              )
            "
          >
            LTS
          </a>


          <a
            href="/university-admin/components/cwts"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'comp-cwts'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/components/cwts'
              )
            "
          >
            CWTS
          </a>


          <a
            href="/university-admin/components/rotc"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'comp-rotc'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/components/rotc'
              )
            "
          >
            ROTC
          </a>

        </div>

      </div>


      <!-- ==========================================================
           REPORTS
      =========================================================== -->

      <div class="menu-group">

        <!-- ========================================================
             MAIN REPORTS BUTTON
        ========================================================= -->

        <div
          class="menu-item"
          :class="{
            active:
              isGroupActive(
                'reports'
              )
          }"
          @click="
            toggleGroup(
              'reports'
            )
          "
        >

          <svg
            class="menu-icon"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path
              d="
                M14 2
                H6
                c-1.1 0-1.99.9-1.99 2
                L4 20
                c0 1.1.89 2 1.99 2
                H18
                c1.1 0 2-.9 2-2
                V8
                l-6-6
                z

                m2 16
                H8
                v-2
                h8
                v2
                z

                m0-4
                H8
                v-2
                h8
                v2
                z

                m-3-5
                V3.5
                L18.5 9
                H13
                z
              "
            />
          </svg>


          <span class="menu-text">
            REPORTS
          </span>

        </div>


        <!-- ========================================================
             REPORT COMPONENTS
        ========================================================= -->

        <div
          v-show="
            activeGroup ===
              'reports' ||
            isGroupActive(
              'reports'
            )
          "
          class="sub-menu"
        >

          <!-- ======================================================
               LTS REPORTS
          ======================================================= -->

          <a
            href="/university-admin/reports/lts"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'rep-lts'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/reports/lts'
              )
            "
          >
            LTS
          </a>


          <!-- ======================================================
               CWTS REPORTS
          ======================================================= -->

          <a
            href="/university-admin/reports/cwts"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'rep-cwts'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/reports/cwts'
              )
            "
          >
            CWTS
          </a>


          <!-- ======================================================
               ROTC REPORTS
          ======================================================= -->

          <a
            href="/university-admin/reports/rotc"
            class="sub-item"
            :class="{
              active:
                currentRoute ===
                'rep-rotc'
            }"
            @click.prevent="
              navigateTo(
                '/university-admin/reports/rotc'
              )
            "
          >
            ROTC
          </a>

        </div>

      </div>


      <!-- ==========================================================
           PROFILE
      =========================================================== -->

      <div
        class="menu-item profile-menu"
        :class="{
          active:
            currentRoute ===
            'profile'
        }"
        @click="
          navigateTo(
            '/university-admin/uniadminprofile'
          )
        "
      >

        <div
          class="profile-avatar"
          :class="{
            'profile-avatar-active':
              currentRoute ===
              'profile'
          }"
        >

          <img
            v-if="hasProfilePhoto"
            :src="profilePhotoUrl"
            :alt="
              universityAdmin?.first_name
                ? `${universityAdmin.first_name} profile photo`
                : 'University Administrator profile photo'
            "
            class="profile-avatar-image"
            @error="handleProfilePhotoError"
          />


          <span
            v-else
            class="profile-avatar-fallback"
          >
            {{ administratorInitials }}
          </span>

        </div>


        <span class="menu-text">
          PROFILE
        </span>

      </div>

    </nav>

  </aside>

</template>


<style scoped>

@import url(
  'https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap'
);


/* ==========================================================
   SIDEBAR
========================================================== */

.sidebar {
  width: 290px;
  min-width: 290px;
  height: 100vh;

  background: #EFEBE2;

  display: flex;
  flex-direction: column;

  flex-shrink: 0;

  overflow-y: auto;

  border-right: 2px solid #919191;

  box-sizing: border-box;
}


/* ==========================================================
   HEADER
========================================================== */

.sidebar-header {
  padding: 40px 24px 34px;

  display: flex;
  flex-direction: column;

  align-items: center;
  justify-content: center;
}


.sidebar-logo {
  width: 150px;
  height: auto;

  margin-bottom: 24px;

  filter:
    drop-shadow(
      0 0 5px
      rgba(217, 146, 2, 0.35)
    )
    drop-shadow(
      0 0 10px
      rgba(217, 146, 2, 0.28)
    )
    drop-shadow(
      0 0 16px
      rgba(217, 146, 2, 0.20)
    )
    drop-shadow(
      0 4px 6px
      rgba(0, 0, 0, 0.18)
    );

  transition:
    filter 0.3s ease;
}


.sidebar-title {
  margin: 0;

  width: 190px;

  text-align: center;

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

  font-size: 15px;

  line-height: 1.4;

  font-weight: 600;

  color: #54100F;
}


.sidebar-subtitle {
  margin-top: 4px;

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

  font-size: 10px;

  font-weight: 500;

  color: #233E47;

  letter-spacing: 2px;
}


/* ==========================================================
   NAVIGATION
========================================================== */

.sidebar-nav {
  padding: 20px 0 40px;
}


/* ==========================================================
   MENU ITEM
========================================================== */

.menu-item {
  display: flex;

  align-items: center;

  gap: 16px;

  padding: 14px 24px;

  cursor: pointer;

  transition: 0.25s ease;

  user-select: none;

  color: #233E47;
}


.menu-item:hover {
  background:
    rgba(
      217,
      146,
      2,
      0.08
    );
}


.menu-item.active {
  color: #D99202;
}


.menu-item.active .menu-text {
  color: #D99202;
}


.menu-item.active .menu-icon {
  color: #D99202;
}


/* ==========================================================
   ICON
========================================================== */

.menu-icon {
  width: 22px;
  height: 22px;

  color: #233E47;

  flex-shrink: 0;

  transition: 0.25s;
}


.menu-item:hover .menu-icon {
  color: #D99202;
}


/* ==========================================================
   TEXT
========================================================== */

.menu-text {
  font-family:
    'Plus Jakarta Sans',
    sans-serif;

  font-size: 15px;

  font-weight: 600;

  color: #233E47;

  transition: 0.25s;
}


.menu-item:hover .menu-text {
  color: #D99202;
}


/* ==========================================================
   GROUP
========================================================== */

.menu-group {
  display: flex;

  flex-direction: column;
}


/* ==========================================================
   SUB MENU
========================================================== */

.sub-menu {
  display: flex;

  flex-direction: column;

  padding-left: 62px;

  padding-top: 6px;
  padding-bottom: 10px;

  gap: 10px;
}


.sub-item {
  text-decoration: none;

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

  font-size: 14px;

  font-weight: 500;

  color: #233E47;

  transition: 0.25s;
}


.sub-item:hover {
  color: #D99202;
}


.sub-item.active {
  color: #D99202;

  font-weight: 700;
}


/* ==========================================================
   PROFILE MENU
========================================================== */

.profile-menu {
  gap: 10px;

  min-height: 62px;
}


/* ==========================================================
   PROFILE AVATAR
========================================================== */

.profile-avatar {
  width: 34px;
  height: 34px;

  min-width: 34px;

  border-radius: 50%;

  overflow: hidden;

  display: flex;

  align-items: center;
  justify-content: center;

  background: #FFFFFF;

  border: 2px solid #233E47;

  box-shadow:
    0 2px 6px
    rgba(0, 13, 18, 0.15);

  transition:
    border-color 0.25s ease,
    box-shadow 0.25s ease,
    transform 0.25s ease;
}


/* ==========================================================
   REAL PROFILE IMAGE
========================================================== */

.profile-avatar-image {
  width: 100%;
  height: 100%;

  display: block;

  object-fit: cover;

  object-position: center;

  border-radius: 50%;
}


/* ==========================================================
   FALLBACK INITIALS
========================================================== */

.profile-avatar-fallback {
  width: 100%;
  height: 100%;

  display: flex;

  align-items: center;
  justify-content: center;

  background: #233E47;

  color: #FFFFFF;

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

  font-size: 11px;

  font-weight: 700;

  letter-spacing: 0.5px;

  line-height: 1;
}


/* ==========================================================
   PROFILE HOVER
========================================================== */

.profile-menu:hover .profile-avatar {
  border-color: #D99202;

  transform: scale(1.04);

  box-shadow:
    0 0 0 3px
    rgba(217, 146, 2, 0.12),
    0 3px 8px
    rgba(0, 13, 18, 0.16);
}


/* ==========================================================
   PROFILE ACTIVE
========================================================== */

.profile-menu.active .profile-avatar,
.profile-avatar-active {
  border-color: #D99202;

  box-shadow:
    0 0 0 3px
    rgba(217, 146, 2, 0.15),
    0 3px 8px
    rgba(0, 13, 18, 0.16);
}


/* ==========================================================
   SCROLLBAR
========================================================== */

.sidebar::-webkit-scrollbar {
  width: 6px;
}


.sidebar::-webkit-scrollbar-thumb {
  background: #D99202;

  border-radius: 20px;
}


.sidebar::-webkit-scrollbar-track {
  background: transparent;
}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (
  max-width: 768px
) {

  .sidebar {
    width: 220px;
    min-width: 220px;
  }


  .sidebar-header {
    padding:
      24px
      14px
      20px;
  }


  .sidebar-logo {
    width: 82px;
  }


  .menu-item {
    padding:
      13px
      18px;

    gap: 14px;
  }


  .menu-text {
    font-size: 14px;
  }


  .menu-icon {
    width: 20px;
    height: 20px;
  }


  .sub-menu {
    padding-left: 52px;
  }


  .profile-menu {
    gap: 10px;
  }


  .profile-avatar {
    width: 32px;
    height: 32px;

    min-width: 32px;
  }

}

</style>