<template>
  <div class="nstp-hub-layout">

    <!-- ==========================================================
         NAVIGATION BAR
    =========================================================== -->

    <header class="navbar">

      <div class="nav-left">

        <!-- LOGO -->

        <div class="logo-container">

          <img
            src="/images/nstphub_logo.png"
            alt="NSTP Hub Logo"
            class="navbar-logo"
          />

        </div>


        <!-- BRAND -->

        <div class="brand-text">

          <span class="main-title">
            National Service Training Program
          </span>


          <div class="sub-title">

            <span class="hub-highlight">
              HUB
            </span>

            <span class="bullet-separator"></span>

            <span class="portal-text">
              DIGITAL PORTAL
            </span>

          </div>

        </div>

      </div>


      <!-- ========================================================
           NAVIGATION
      ========================================================= -->

      <nav class="nav-links">

        <a
          href="#why-the-hub"
          @click.prevent="scrollToSection('why-the-hub')"
        >
          Why the Hub
        </a>


        <a
          href="#components"
          @click.prevent="scrollToSection('components')"
        >
          Components
        </a>


        <!-- SIGN IN -->

        <button
          type="button"
          class="btn-signin"
          @click="handleSignIn"
        >

          <span>
            Sign In
          </span>


          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="signin-arrow"
          >

            <path d="M5 12h14" />

            <path d="m13 6 6 6-6 6" />

          </svg>

        </button>

      </nav>

    </header>


    <!-- ==========================================================
         HERO SECTION
    =========================================================== -->

    <section class="hero-section">

      <!-- DECORATIONS -->

      <div class="hero-orb hero-orb-one"></div>

      <div class="hero-orb hero-orb-two"></div>


      <!-- ========================================================
           HERO CONTENT
      ========================================================= -->

      <div class="hero-content">

        <!-- EYEBROW -->

        <div class="hero-eyebrow">

          <span class="eyebrow-line"></span>

          <span class="eyebrow-text">
            SERVE • LEAD • GROW
          </span>

        </div>


        <!-- TITLE -->

        <h1 class="welcome-heading">

          Mabuhay.

          <span class="heading-second-line">
            Welcome to
          </span>

          <span class="italic-accent">
            NSTP Hub.
          </span>

        </h1>


        <!-- DESCRIPTION -->

        <p class="hero-description">

          A unified digital space for managing students,
          coordinators, instructors, attendance, schedules,
          announcements, and NSTP activities across

          <strong>
            CWTS
          </strong>,

          <strong>
            ROTC
          </strong>

          and

          <strong>
            LTS
          </strong>.

        </p>


        <!-- HERO META -->

        <div class="hero-meta">

          <div class="meta-item">

            <strong>
              3
            </strong>

            <span>
              NSTP Components
            </span>

          </div>


          <span class="meta-divider"></span>


          <div class="meta-item">

            <strong>
              1
            </strong>

            <span>
              Unified Platform
            </span>

          </div>

        </div>

      </div>


      <!-- ========================================================
           FADED NSTP LOGO
      ========================================================= -->

      <div class="hero-artwork-container">

        <img
          src="/images/nstphub_logo.png"
          alt="NSTP Hub Graphic"
          class="hero-artwork-img"
        />

      </div>

    </section>


    <!-- ==========================================================
         WHY THE HUB
    =========================================================== -->

    <section
      id="why-the-hub"
      class="why-hub-section"
    >

      <!-- BACKGROUND DECORATIONS -->

      <div class="why-decoration why-decoration-one"></div>

      <div class="why-decoration why-decoration-two"></div>


      <div class="why-hub-container">

        <!-- ========================================================
             LEFT CONTENT
        ========================================================= -->

        <div class="why-hub-left">

          <div class="section-kicker">

            <span class="kicker-line"></span>

            <span class="section-tagline">
              WHY THE HUB MATTERS
            </span>

          </div>


          <h2 class="why-hub-title">

            Less paperwork.

            <br>

            More time for

            <span>
              meaningful service.
            </span>

          </h2>


          <p class="why-hub-description">

            NSTP Hub brings registration, attendance,
            scheduling, communication, and reporting into one
            organized platform.

            It gives university administrators, instructors,
            and coordinators the tools they need to support
            students more efficiently.

          </p>


          <!-- HIGHLIGHT -->

          <div class="why-highlight">

            <div class="highlight-icon">

              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
              >

                <path d="M20 6 9 17l-5-5" />

              </svg>

            </div>


            <span>
              One system for the complete NSTP workflow.
            </span>

          </div>

        </div>


        <!-- ========================================================
             FEATURE GRID
        ========================================================= -->

        <div class="why-hub-grid">

          <!-- FEATURE 1 -->

          <article class="feature-card">

            <div class="feature-top">

              <div class="feature-icon">

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >

                  <ellipse
                    cx="12"
                    cy="5"
                    rx="8"
                    ry="3"
                  />

                  <path
                    d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"
                  />

                  <path
                    d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"
                  />

                </svg>

              </div>


              <span class="feature-number">
                01
              </span>

            </div>


            <h3>
              One central database.
            </h3>


            <p>
              Student information is organized in a single system,
              reducing duplicate records, paperwork, and manual encoding.
            </p>

          </article>


          <!-- FEATURE 2 -->

          <article class="feature-card">

            <div class="feature-top">

              <div class="feature-icon">

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >

                  <rect
                    x="4"
                    y="4"
                    width="16"
                    height="16"
                    rx="2"
                  />

                  <path d="M8 2v4" />

                  <path d="M16 2v4" />

                  <path d="M4 9h16" />

                  <path d="m8 14 2 2 5-5" />

                </svg>

              </div>


              <span class="feature-number">
                02
              </span>

            </div>


            <h3>
              Smarter attendance.
            </h3>


            <p>
              Track NSTP sessions, participation, and accumulated
              service hours using structured attendance records.
            </p>

          </article>


          <!-- FEATURE 3 -->

          <article class="feature-card">

            <div class="feature-top">

              <div class="feature-icon">

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >

                  <path
                    d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"
                  />

                  <path d="M8 9h8" />

                  <path d="M8 13h5" />

                </svg>

              </div>


              <span class="feature-number">
                03
              </span>

            </div>


            <h3>
              Better communication.
            </h3>


            <p>
              Keep students informed through centralized announcements,
              schedules, and updates from their NSTP staff.
            </p>

          </article>


          <!-- FEATURE 4 -->

          <article class="feature-card">

            <div class="feature-top">

              <div class="feature-icon">

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >

                  <path d="M4 19V9" />

                  <path d="M10 19V5" />

                  <path d="M16 19v-7" />

                  <path d="M22 19H2" />

                </svg>

              </div>


              <span class="feature-number">
                04
              </span>

            </div>


            <h3>
              Clearer reporting.
            </h3>


            <p>
              Organize NSTP participation and performance information
              into reports that support faster administrative decisions.
            </p>

          </article>

        </div>

      </div>

    </section>


    <!-- ==========================================================
         COMPONENTS
    =========================================================== -->

    <section
      id="components"
      class="components-section"
    >

      <!-- ========================================================
           HEADER
      ========================================================= -->

      <div class="components-header">

        <div>

          <div class="section-kicker light-kicker">

            <span class="kicker-line"></span>

            <span class="section-tagline">
              NSTP COMPONENTS
            </span>

          </div>


          <h2 class="components-title">

            Three pathways.

            <br>

            <span>
              One purpose.
            </span>

          </h2>

        </div>


        <p class="components-subtitle">

          Each NSTP component provides a different way to serve,
          develop leadership, and contribute to the community.

          NSTP Hub creates one consistent digital experience across
          all three programs.

        </p>

      </div>


      <!-- ========================================================
           COMPONENT GRID
      ========================================================= -->

      <div class="components-grid">

        <!-- ======================================================
             CWTS
        ======================================================= -->

        <article class="component-card cwts-card">

          <div class="component-card-header">

            <span class="component-number">
              01
            </span>


            <div class="component-icon cwts-icon">

              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
              >

                <path
                  d="M12 21s-7-4.4-7-11a4 4 0 0 1 7-2.7A4 4 0 0 1 19 10c0 6.6-7 11-7 11Z"
                />

              </svg>

            </div>

          </div>


          <div class="component-accent green-accent"></div>


          <span class="card-abbr green-text">
            CWTS
          </span>


          <h3 class="card-title">
            Civic Welfare Training Service
          </h3>


          <p class="component-summary">
            Build stronger communities through civic engagement,
            public service, health, safety, and environmental initiatives.
          </p>


          <div class="component-features">

            <span>
              Community Service
            </span>

            <span>
              Civic Engagement
            </span>

          </div>

        </article>


        <!-- ======================================================
             ROTC
        ======================================================= -->

        <article class="component-card rotc-card">

          <div class="component-card-header">

            <span class="component-number">
              02
            </span>


            <div class="component-icon rotc-icon">

              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
              >

                <path
                  d="M12 3 4 7v5c0 5 3.4 8.4 8 9 4.6-.6 8-4 8-9V7l-8-4Z"
                />

                <path
                  d="m9 12 2 2 4-4"
                />

              </svg>

            </div>

          </div>


          <div class="component-accent burgundy-accent"></div>


          <span class="card-abbr burgundy-text">
            ROTC
          </span>


          <h3 class="card-title">
            Reserve Officers' Training Corps
          </h3>


          <p class="component-summary">
            Develop discipline, leadership, preparedness,
            teamwork, and service through structured military training.
          </p>


          <div class="component-features">

            <span>
              Leadership
            </span>

            <span>
              Discipline
            </span>

          </div>

        </article>


        <!-- ======================================================
             LTS
        ======================================================= -->

        <article class="component-card lts-card">

          <div class="component-card-header">

            <span class="component-number">
              03
            </span>


            <div class="component-icon lts-icon">

              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
              >

                <path
                  d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5Z"
                />

                <path
                  d="M20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5A2.5 2.5 0 0 1 20 21.5Z"
                />

              </svg>

            </div>

          </div>


          <div class="component-accent teal-accent"></div>


          <span class="card-abbr teal-text">
            LTS
          </span>


          <h3 class="card-title">
            Literacy Training Service
          </h3>


          <p class="component-summary">
            Support communities through literacy, education,
            mentorship, and learning-focused service activities.
          </p>


          <div class="component-features">

            <span>
              Literacy
            </span>

            <span>
              Education
            </span>

          </div>

        </article>

      </div>

    </section>


    <!-- ==========================================================
         FOOTER
    =========================================================== -->

    <footer class="footer">

      <div class="footer-brand">

        <img
          src="/images/nstphub_logo.png"
          alt="NSTP Hub"
        >


        <div>

          <strong>
            NSTP HUB
          </strong>

          <span>
            National Service Training Program
          </span>

        </div>

      </div>


      <span class="footer-copy">
        Serve. Lead. Grow.
      </span>

    </footer>

  </div>
</template>


<script setup>

import {
  router,
} from '@inertiajs/vue3';


/*
|--------------------------------------------------------------------------
| Smooth Scroll
|--------------------------------------------------------------------------
*/

const scrollToSection = (
  id
) => {

  const element =
    document.getElementById(
      id
    );


  if (
    element
  ) {

    element.scrollIntoView({
      behavior:
        'smooth',
    });

  }

};


/*
|--------------------------------------------------------------------------
| Sign In
|--------------------------------------------------------------------------
*/

const handleSignIn = () => {

  router.visit(
    '/role'
  );

};

</script>


<style scoped>

@import url(
  'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/* ==========================================================
   ROOT
========================================================== */

.nstp-hub-layout {

  --cream:
    #EFEBE2;

  --maroon:
    #54100F;

  --green:
    #58761C;

  --yellow:
    #FFBD36;

  --gold:
    #D99202;

  --teal:
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

  min-height:
    100vh;

  margin:
    0;

  overflow-x:
    hidden;

  color:
    var(--near-black);

  background:
    var(--cream);

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

}


/* ==========================================================
   NAVBAR
========================================================== */

.navbar {

  position:
    sticky;

  top:
    0;

  z-index:
    1000;

  min-height:
    92px;

  padding:
    10px
    clamp(
      28px,
      5vw,
      76px
    );

  display:
    flex;

  align-items:
    center;

  justify-content:
    space-between;

  box-sizing:
    border-box;

  color:
    var(--white);

  background:
    rgba(
      84,
      16,
      15,
      0.98
    );

  border-bottom:
    1px
    solid
    rgba(
      255,
      189,
      54,
      0.16
    );

  box-shadow:
    0
    8px
    28px
    rgba(
      0,
      13,
      18,
      0.13
    );

}


/* ==========================================================
   NAV LEFT
========================================================== */

.nav-left {

  display:
    flex;

  align-items:
    center;

  gap:
    15px;

}


.logo-container {

  width:
    67px;

  height:
    67px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

}


.navbar-logo {

  width:
    62px;

  height:
    62px;

  object-fit:
    contain;

  filter:
    drop-shadow(
      0
      5px
      8px
      rgba(
        0,
        0,
        0,
        0.32
      )
    );

}


/* ==========================================================
   BRAND
========================================================== */

.brand-text {

  display:
    flex;

  flex-direction:
    column;

  gap:
    4px;

}


.main-title {

  color:
    var(--white);

  font-size:
    0.92rem;

  font-weight:
    600;

  letter-spacing:
    0.3px;

}


.sub-title {

  display:
    flex;

  align-items:
    center;

  gap:
    8px;

}


.hub-highlight,
.portal-text {

  color:
    var(--gold);

  font-size:
    0.68rem;

  font-weight:
    800;

  letter-spacing:
    1.5px;

}


.bullet-separator {

  width:
    4px;

  height:
    4px;

  border-radius:
    50%;

  background:
    var(--gold);

}


/* ==========================================================
   NAVIGATION
========================================================== */

.nav-links {

  display:
    flex;

  align-items:
    center;

  gap:
    34px;

}


.nav-links a {

  position:
    relative;

  padding:
    10px
    0;

  color:
    rgba(
      255,
      255,
      255,
      0.74
    );

  text-decoration:
    none;

  font-size:
    0.85rem;

  font-weight:
    600;

  transition:
    color
    0.2s
    ease;

}


.nav-links a::after {

  content:
    "";

  position:
    absolute;

  left:
    0;

  bottom:
    4px;

  width:
    100%;

  height:
    2px;

  border-radius:
    10px;

  background:
    var(--gold);

  transform:
    scaleX(
      0
    );

  transform-origin:
    left;

  transition:
    transform
    0.2s
    ease;

}


.nav-links a:hover {

  color:
    var(--white);

}


.nav-links a:hover::after {

  transform:
    scaleX(
      1
    );

}


/* ==========================================================
   SIGN IN BUTTON
========================================================== */

.btn-signin {

  height:
    44px;

  padding:
    0
    19px;

  display:
    flex;

  align-items:
    center;

  gap:
    10px;

  color:
    var(--near-black);

  border:
    none;

  border-radius:
    999px;

  background:
    var(--gold);

  font-family:
    inherit;

  font-size:
    0.82rem;

  font-weight:
    800;

  cursor:
    pointer;

  box-shadow:
    0
    8px
    20px
    rgba(
      217,
      146,
      2,
      0.2
    );

  transition:
    transform
    0.2s
    ease,
    background
    0.2s
    ease,
    box-shadow
    0.2s
    ease;

}


.signin-arrow {

  width:
    16px;

  height:
    16px;

}


.btn-signin:hover {

  transform:
    translateY(
      -2px
    );

  background:
    var(--yellow);

  box-shadow:
    0
    12px
    24px
    rgba(
      217,
      146,
      2,
      0.28
    );

}


/* ==========================================================
   HERO
========================================================== */

.hero-section {

  position:
    relative;

  min-height:
    calc(
      100vh - 92px
    );

  display:
    grid;

  grid-template-columns:
    minmax(
      0,
      1fr
    )
    minmax(
      380px,
      0.8fr
    );

  align-items:
    center;

  gap:
    clamp(
      30px,
      5vw,
      75px
    );

  overflow:
    hidden;

  padding:
    65px
    clamp(
      40px,
      8vw,
      135px
    );

  box-sizing:
    border-box;

  background:
    #EFEBE2;

}


/* ==========================================================
   HERO DECORATION
========================================================== */

.hero-orb {

  position:
    absolute;

  border-radius:
    50%;

  pointer-events:
    none;

}


.hero-orb-one {

  width:
    500px;

  height:
    500px;

  top:
    -390px;

  left:
    29%;

  border:
    1px
    solid
    rgba(
      217,
      146,
      2,
      0.08
    );

}


.hero-orb-two {

  width:
    360px;

  height:
    360px;

  bottom:
    -300px;

  left:
    -160px;

  background:
    rgba(
      88,
      118,
      28,
      0.025
    );

}


/* ==========================================================
   HERO CONTENT
========================================================== */

.hero-content {

  position:
    relative;

  z-index:
    3;

  max-width:
    650px;

}


.hero-eyebrow {

  display:
    flex;

  align-items:
    center;

  gap:
    13px;

  margin-bottom:
    24px;

}


.eyebrow-line {

  width:
    40px;

  height:
    2px;

  border-radius:
    10px;

  background:
    var(--gold);

}


.eyebrow-text {

  color:
    var(--green);

  font-size:
    0.68rem;

  font-weight:
    800;

  letter-spacing:
    2.1px;

}


/* ==========================================================
   HERO TITLE
========================================================== */

.welcome-heading {

  margin:
    0;

  color:
    var(--near-black);

  font-family:
    'Lora',
    serif;

  font-size:
    clamp(
      3.7rem,
      6vw,
      6rem
    );

  line-height:
    0.98;

  font-weight:
    500;

  letter-spacing:
    -3px;

}


.heading-second-line {

  display:
    block;

}


.italic-accent {

  display:
    inline-block;

  margin-top:
    4px;

  color:
    var(--maroon);

  font-style:
    italic;

}


/* ==========================================================
   HERO DESCRIPTION
========================================================== */

.hero-description {

  max-width:
    610px;

  margin:
    30px
    0
    0;

  color:
    var(--teal);

  font-size:
    1.03rem;

  line-height:
    1.85;

  font-weight:
    500;

}


.hero-description strong {

  color:
    var(--maroon);

  font-weight:
    800;

}


/* ==========================================================
   HERO META
========================================================== */

.hero-meta {

  margin-top:
    38px;

  display:
    flex;

  align-items:
    center;

  gap:
    18px;

}


.meta-item {

  display:
    flex;

  align-items:
    center;

  gap:
    9px;

}


.meta-item strong {

  color:
    var(--gold);

  font-family:
    'Lora',
    serif;

  font-size:
    1.75rem;

  font-weight:
    600;

}


.meta-item span {

  max-width:
    90px;

  color:
    rgba(
      35,
      62,
      71,
      0.7
    );

  font-size:
    0.62rem;

  line-height:
    1.35;

  font-weight:
    700;

  text-transform:
    uppercase;

  letter-spacing:
    0.65px;

}


.meta-divider {

  width:
    1px;

  height:
    31px;

  background:
    rgba(
      84,
      16,
      15,
      0.13
    );

}


/* ==========================================================
   FADED NSTP WATERMARK
========================================================== */

.hero-artwork-container {

  position:
    relative;

  z-index:
    1;

  width:
    100%;

  min-height:
    520px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  overflow:
    visible;

  pointer-events:
    none;

}


.hero-artwork-img {

  width:
    clamp(
      400px,
      34vw,
      540px
    );

  max-width:
    100%;

  height:
    auto;

  display:
    block;

  object-fit:
    contain;

  opacity:
    0.12;

  transform:
    none;

  filter:
    saturate(
      0.72
    )
    contrast(
      0.92
    );

  user-select:
    none;

}


/* ==========================================================
   WHY THE HUB
========================================================== */

.why-hub-section {

  position:
    relative;

  overflow:
    hidden;

  padding:
    120px
    clamp(
      40px,
      8vw,
      130px
    );

  color:
    var(--white);

  background:
    var(--dark);

}


/* ==========================================================
   WHY DECORATIONS
========================================================== */

.why-decoration {

  position:
    absolute;

  border-radius:
    50%;

  pointer-events:
    none;

}


.why-decoration-one {

  width:
    460px;

  height:
    460px;

  top:
    -320px;

  right:
    -180px;

  border:
    1px
    solid
    rgba(
      217,
      146,
      2,
      0.08
    );

}


.why-decoration-two {

  width:
    320px;

  height:
    320px;

  bottom:
    -230px;

  left:
    50px;

  background:
    rgba(
      88,
      118,
      28,
      0.04
    );

}


/* ==========================================================
   WHY GRID
========================================================== */

.why-hub-container {

  position:
    relative;

  z-index:
    2;

  max-width:
    1450px;

  margin:
    0
    auto;

  display:
    grid;

  grid-template-columns:
    0.9fr
    1.15fr;

  gap:
    90px;

  align-items:
    center;

}


/* ==========================================================
   SECTION KICKER
========================================================== */

.section-kicker {

  display:
    flex;

  align-items:
    center;

  gap:
    12px;

  margin-bottom:
    20px;

}


.kicker-line {

  width:
    36px;

  height:
    2px;

  background:
    var(--gold);

}


.section-tagline {

  color:
    var(--gold);

  font-size:
    0.66rem;

  font-weight:
    800;

  letter-spacing:
    1.8px;

}


/* ==========================================================
   WHY TITLE
========================================================== */

.why-hub-title {

  margin:
    0;

  color:
    var(--white);

  font-family:
    'Lora',
    serif;

  font-size:
    clamp(
      2.7rem,
      4vw,
      4.2rem
    );

  line-height:
    1.08;

  font-weight:
    500;

  letter-spacing:
    -1.8px;

}


.why-hub-title span {

  color:
    var(--gold);

  font-style:
    italic;

}


/* ==========================================================
   WHY DESCRIPTION
========================================================== */

.why-hub-description {

  max-width:
    570px;

  margin:
    28px
    0
    0;

  color:
    rgba(
      190,
      190,
      190,
      0.82
    );

  font-size:
    0.92rem;

  line-height:
    1.85;

  font-weight:
    500;

}


/* ==========================================================
   WHY HIGHLIGHT
========================================================== */

.why-highlight {

  margin-top:
    30px;

  display:
    flex;

  align-items:
    center;

  gap:
    11px;

  color:
    rgba(
      255,
      255,
      255,
      0.85
    );

  font-size:
    0.76rem;

  font-weight:
    600;

}


.highlight-icon {

  width:
    28px;

  height:
    28px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  color:
    var(--dark);

  border-radius:
    50%;

  background:
    var(--yellow);

}


.highlight-icon svg {

  width:
    15px;

  height:
    15px;

}


/* ==========================================================
   FEATURE GRID
========================================================== */

.why-hub-grid {

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
    20px;

}


/* ==========================================================
   FEATURE CARD
========================================================== */

.feature-card {

  min-height:
    230px;

  padding:
    25px;

  border:
    1px
    solid
    rgba(
      255,
      255,
      255,
      0.07
    );

  border-radius:
    18px;

  background:
    rgba(
      255,
      255,
      255,
      0.025
    );

  box-shadow:
    inset
    0
    1px
    0
    rgba(
      255,
      255,
      255,
      0.03
    );

  transition:
    transform
    0.25s
    ease,
    border-color
    0.25s
    ease,
    background
    0.25s
    ease;

}


.feature-card:hover {

  transform:
    translateY(
      -6px
    );

  border-color:
    rgba(
      217,
      146,
      2,
      0.35
    );

  background:
    rgba(
      255,
      255,
      255,
      0.04
    );

}


/* ==========================================================
   FEATURE TOP
========================================================== */

.feature-top {

  display:
    flex;

  align-items:
    flex-start;

  justify-content:
    space-between;

  margin-bottom:
    25px;

}


.feature-icon {

  width:
    47px;

  height:
    47px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  color:
    var(--gold);

  border:
    1px
    solid
    rgba(
      217,
      146,
      2,
      0.18
    );

  border-radius:
    13px;

  background:
    rgba(
      217,
      146,
      2,
      0.08
    );

}


.feature-icon svg {

  width:
    23px;

  height:
    23px;

}


.feature-number {

  color:
    rgba(
      255,
      255,
      255,
      0.17
    );

  font-family:
    'Lora',
    serif;

  font-size:
    0.85rem;

}


/* ==========================================================
   FEATURE TEXT
========================================================== */

.feature-card h3 {

  margin:
    0;

  color:
    var(--white);

  font-family:
    'Lora',
    serif;

  font-size:
    1.25rem;

  line-height:
    1.35;

  font-weight:
    500;

}


.feature-card p {

  margin:
    12px
    0
    0;

  color:
    rgba(
      190,
      190,
      190,
      0.75
    );

  font-size:
    0.76rem;

  line-height:
    1.7;

  font-weight:
    500;

}


/* ==========================================================
   COMPONENTS
========================================================== */

.components-section {

  padding:
    120px
    clamp(
      40px,
      8vw,
      130px
    );

  background:
    linear-gradient(
      180deg,
      #EFEBE2,
      #F8F5EF
    );

}


/* ==========================================================
   COMPONENT HEADER
========================================================== */

.components-header {

  max-width:
    1450px;

  margin:
    0
    auto
    65px;

  display:
    grid;

  grid-template-columns:
    1fr
    1fr;

  gap:
    80px;

  align-items:
    end;

}


.light-kicker .section-tagline {

  color:
    var(--green);

}


.components-title {

  margin:
    0;

  color:
    var(--near-black);

  font-family:
    'Lora',
    serif;

  font-size:
    clamp(
      2.7rem,
      4vw,
      4.2rem
    );

  line-height:
    1.08;

  font-weight:
    500;

  letter-spacing:
    -1.7px;

}


.components-title span {

  color:
    var(--maroon);

  font-style:
    italic;

}


.components-subtitle {

  margin:
    0;

  color:
    rgba(
      35,
      62,
      71,
      0.82
    );

  font-size:
    0.9rem;

  line-height:
    1.8;

  font-weight:
    500;

}


/* ==========================================================
   COMPONENT GRID
========================================================== */

.components-grid {

  max-width:
    1450px;

  margin:
    0
    auto;

  display:
    grid;

  grid-template-columns:
    repeat(
      3,
      minmax(
        0,
        1fr
      )
    );

  gap:
    25px;

}


/* ==========================================================
   COMPONENT CARD
========================================================== */

.component-card {

  position:
    relative;

  min-height:
    355px;

  padding:
    30px;

  display:
    flex;

  flex-direction:
    column;

  overflow:
    hidden;

  border:
    1px
    solid
    rgba(
      84,
      16,
      15,
      0.12
    );

  border-radius:
    22px;

  background:
    rgba(
      255,
      255,
      255,
      0.86
    );

  box-shadow:
    0
    16px
    40px
    rgba(
      13,
      23,
      27,
      0.065
    );

  transition:
    transform
    0.28s
    ease,
    box-shadow
    0.28s
    ease,
    border-color
    0.28s
    ease;

}


.component-card::before {

  content:
    "";

  position:
    absolute;

  top:
    0;

  left:
    0;

  width:
    100%;

  height:
    5px;

}


.cwts-card::before {

  background:
    var(--green);

}


.rotc-card::before {

  background:
    var(--maroon);

}


.lts-card::before {

  background:
    var(--teal);

}


.component-card:hover {

  transform:
    translateY(
      -7px
    );

  border-color:
    rgba(
      217,
      146,
      2,
      0.35
    );

  box-shadow:
    0
    24px
    48px
    rgba(
      13,
      23,
      27,
      0.11
    );

}


/* ==========================================================
   COMPONENT HEADER
========================================================== */

.component-card-header {

  display:
    flex;

  align-items:
    flex-start;

  justify-content:
    space-between;

  margin-bottom:
    27px;

}


.component-number {

  color:
    var(--gray);

  font-family:
    'Lora',
    serif;

  font-size:
    0.82rem;

  font-weight:
    600;

}


.component-icon {

  width:
    57px;

  height:
    57px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  border-radius:
    16px;

}


.component-icon svg {

  width:
    28px;

  height:
    28px;

}


.cwts-icon {

  color:
    var(--green);

  background:
    rgba(
      88,
      118,
      28,
      0.08
    );

}


.rotc-icon {

  color:
    var(--maroon);

  background:
    rgba(
      84,
      16,
      15,
      0.07
    );

}


.lts-icon {

  color:
    var(--teal);

  background:
    rgba(
      35,
      62,
      71,
      0.07
    );

}


/* ==========================================================
   COMPONENT ACCENT
========================================================== */

.component-accent {

  width:
    42px;

  height:
    3px;

  margin-bottom:
    20px;

  border-radius:
    10px;

}


.green-accent {

  background:
    var(--green);

}


.burgundy-accent {

  background:
    var(--maroon);

}


.teal-accent {

  background:
    var(--teal);

}


/* ==========================================================
   COMPONENT ABBREVIATION
========================================================== */

.card-abbr {

  display:
    block;

  margin-bottom:
    10px;

  font-size:
    0.67rem;

  font-weight:
    800;

  letter-spacing:
    1.7px;

}


.green-text {

  color:
    var(--green);

}


.burgundy-text {

  color:
    var(--maroon);

}


.teal-text {

  color:
    var(--teal);

}


/* ==========================================================
   COMPONENT TITLE
========================================================== */

.card-title {

  max-width:
    330px;

  margin:
    0;

  color:
    var(--near-black);

  font-family:
    'Lora',
    serif;

  font-size:
    1.75rem;

  line-height:
    1.18;

  font-weight:
    500;

}


/* ==========================================================
   COMPONENT SUMMARY
========================================================== */

.component-summary {

  margin:
    19px
    0
    0;

  color:
    rgba(
      35,
      62,
      71,
      0.76
    );

  font-size:
    0.79rem;

  line-height:
    1.7;

  font-weight:
    500;

}


/* ==========================================================
   COMPONENT TAGS
========================================================== */

.component-features {

  margin-top:
    25px;

  display:
    flex;

  flex-wrap:
    wrap;

  gap:
    7px;

}


.component-features span {

  padding:
    7px
    10px;

  color:
    var(--teal);

  border:
    1px
    solid
    rgba(
      35,
      62,
      71,
      0.1
    );

  border-radius:
    999px;

  background:
    rgba(
      35,
      62,
      71,
      0.035
    );

  font-size:
    0.58rem;

  font-weight:
    700;

}


/* ==========================================================
   FOOTER
========================================================== */

.footer {

  min-height:
    95px;

  padding:
    15px
    clamp(
      30px,
      6vw,
      90px
    );

  display:
    flex;

  align-items:
    center;

  justify-content:
    space-between;

  box-sizing:
    border-box;

  background:
    var(--near-black);

}


.footer-brand {

  display:
    flex;

  align-items:
    center;

  gap:
    12px;

}


.footer-brand img {

  width:
    48px;

  height:
    48px;

  object-fit:
    contain;

}


.footer-brand div {

  display:
    flex;

  flex-direction:
    column;

  gap:
    2px;

}


.footer-brand strong {

  color:
    var(--gold);

  font-size:
    0.72rem;

  letter-spacing:
    1.4px;

}


.footer-brand span {

  color:
    rgba(
      255,
      255,
      255,
      0.5
    );

  font-size:
    0.61rem;

}


.footer-copy {

  color:
    rgba(
      255,
      255,
      255,
      0.42
    );

  font-size:
    0.65rem;

  font-weight:
    600;

  letter-spacing:
    0.7px;

}


/* ==========================================================
   MEDIUM SCREEN
========================================================== */

@media (
  max-width: 1150px
) {

  .hero-section {

    grid-template-columns:
      1fr
      0.75fr;

    padding:
      65px
      55px;

  }


  .hero-artwork-container {

    min-height:
      450px;

  }


  .hero-artwork-img {

    width:
      420px;

  }


  .why-hub-container {

    gap:
      55px;

  }

}


/* ==========================================================
   TABLET
========================================================== */

@media (
  max-width: 900px
) {

  .navbar {

    padding:
      10px
      28px;

  }


  .hero-section {

    min-height:
      auto;

    grid-template-columns:
      1fr;

    padding:
      70px
      45px
      75px;

    text-align:
      center;

  }


  .hero-content {

    margin:
      0
      auto;

  }


  .hero-eyebrow {

    justify-content:
      center;

  }


  .hero-description {

    margin-left:
      auto;

    margin-right:
      auto;

  }


  .hero-meta {

    justify-content:
      center;

  }


  .hero-artwork-container {

    min-height:
      380px;

  }


  .hero-artwork-img {

    width:
      min(
        400px,
        75vw
      );

    opacity:
      0.11;

  }


  .why-hub-container {

    grid-template-columns:
      1fr;

  }


  .why-hub-left {

    max-width:
      650px;

  }


  .components-header {

    grid-template-columns:
      1fr;

    gap:
      30px;

  }


  .components-grid {

    grid-template-columns:
      1fr;

  }


  .component-card {

    min-height:
      320px;

  }

}


/* ==========================================================
   MOBILE
========================================================== */

@media (
  max-width: 680px
) {

  .navbar {

    min-height:
      75px;

    padding:
      8px
      18px;

  }


  .navbar-logo {

    width:
      52px;

    height:
      52px;

  }


  .logo-container {

    width:
      54px;

    height:
      54px;

  }


  .brand-text {

    display:
      none;

  }


  .nav-links {

    gap:
      10px;

  }


  .nav-links a {

    display:
      none;

  }


  .btn-signin {

    height:
      40px;

    padding:
      0
      16px;

  }


  .hero-section {

    padding:
      60px
      20px
      65px;

  }


  .welcome-heading {

    font-size:
      3.25rem;

    letter-spacing:
      -2px;

  }


  .hero-description {

    font-size:
      0.92rem;

  }


  .hero-meta {

    margin-top:
      32px;

  }


  .hero-artwork-container {

    min-height:
      280px;

  }


  .hero-artwork-img {

    width:
      280px;

    max-width:
      80vw;

    opacity:
      0.1;

  }


  .why-hub-section,
  .components-section {

    padding:
      85px
      20px;

  }


  .why-hub-grid {

    grid-template-columns:
      1fr;

  }


  .why-hub-title,
  .components-title {

    font-size:
      2.6rem;

  }


  .feature-card {

    min-height:
      auto;

  }


  .component-card {

    padding:
      25px;

  }


  .footer {

    flex-direction:
      column;

    gap:
      15px;

    padding:
      23px;

    text-align:
      center;

  }

}

</style>