<template>
  <div class="role-container">

    <!-- ==========================================================
         LEFT PANEL
    =========================================================== -->

    <div class="left-panel">

      <!-- ========================================================
           BACK BUTTON
      ========================================================= -->

      <Link
        href="/"
        class="back-btn"
        aria-label="Back to Welcome Page"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.7"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="back-arrow"
        >
          <path d="M19 12H5" />
          <path d="M12 19l-7-7 7-7" />
        </svg>
      </Link>


      <!-- ========================================================
           BRANDING
      ========================================================= -->

      <div class="branding">

        <img
          src="/images/nstphub_logo.png"
          alt="NSTP HUB Logo"
          class="logo"
        >

        <h3>
          National Service Training Program
        </h3>

        <p class="branding-sub">
          hub
        </p>

      </div>

    </div>


    <!-- ==========================================================
         RIGHT PANEL
    =========================================================== -->

    <main class="right-panel">

      <!-- ========================================================
           BACKGROUND DECORATIONS
      ========================================================= -->

      <div
        class="background-circle background-circle-one"
      ></div>

      <div
        class="background-circle background-circle-two"
      ></div>


      <!-- ========================================================
           CONTENT
      ========================================================= -->

      <div class="role-content">

        <!-- ======================================================
             TOP DECORATION
        ======================================================= -->

        <div class="welcome-eyebrow">

          <span class="eyebrow-line"></span>


          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="eyebrow-icon"
            aria-hidden="true"
          >
            <path
              d="M2 9.5L12 4l10 5.5L12 15 2 9.5Z"
            />

            <path
              d="M6 12v4.5c3.5 2.5 8.5 2.5 12 0V12"
            />

            <path
              d="M22 9.5V15"
            />
          </svg>


          <span class="eyebrow-line"></span>

        </div>


        <!-- ======================================================
             TITLE
        ======================================================= -->

        <h1 class="welcome-title">
          WELCOME!
        </h1>


        <div class="title-accent"></div>


        <!-- ======================================================
             DESCRIPTION
        ======================================================= -->

        <p class="welcome-description">

          Select your assigned portal to continue securely into

          <strong>
            NSTP Hub
          </strong>.

          Choose the role that matches your account and responsibilities.

        </p>


        <!-- ======================================================
             ROLE CARDS
        ======================================================= -->

        <div class="role-options">

          <!-- ====================================================
               UNIVERSITY ADMINISTRATOR
          ===================================================== -->

          <Link
            href="/university-admin/access-code"
            class="role-card university-card"
            aria-label="Continue as University Administrator"
          >

            <!-- ==================================================
                 CARD HEADER
            =================================================== -->

            <div class="card-header">

              <span class="card-number">
                01
              </span>

              <span class="role-badge admin-badge">
                UNIVERSITY ADMIN
              </span>

            </div>


            <!-- ==================================================
                 ICON
            =================================================== -->

            <div class="icon-box">

              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="role-svg"
                aria-hidden="true"
              >

                <!-- Head -->

                <circle
                  cx="12"
                  cy="7"
                  r="3.25"
                />


                <!-- Body -->

                <path
                  d="M5.5 20v-2a6.5 6.5 0 0 1 13 0v2"
                />


                <!-- Collar -->

                <path
                  d="M9 13l3 3 3-3"
                />


                <!-- Tie -->

                <path
                  d="M12 16l-1.2 3L12 21l1.2-2L12 16Z"
                />

              </svg>

            </div>


            <!-- ==================================================
                 ACCENT
            =================================================== -->

            <div class="card-accent"></div>


            <!-- ==================================================
                 TEXT
            =================================================== -->

            <div class="card-copy">

              <h2>

                University

                <span>
                  Administrator
                </span>

              </h2>


              <p>
                Manage student registration, user accounts,
                NSTP components, and university reports.
              </p>

            </div>


            <!-- ==================================================
                 ACTION
            =================================================== -->

            <div class="card-footer">

              <span class="continue-text">
                Continue to portal
              </span>


              <div class="arrow-button">

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="arrow-icon"
                >

                  <path
                    d="M5 12h14"
                  />

                  <path
                    d="m13 6 6 6-6 6"
                  />

                </svg>

              </div>

            </div>

          </Link>


          <!-- ====================================================
               INSTRUCTOR / COORDINATOR
          ===================================================== -->

          <Link
            href="/instructor-coordinator/access-code"
            class="role-card staff-card"
            aria-label="Continue as Instructor or Coordinator"
          >

            <!-- ==================================================
                 CARD HEADER
            =================================================== -->

            <div class="card-header">

              <span class="card-number">
                02
              </span>

              <span class="role-badge staff-badge">
                NSTP STAFF
              </span>

            </div>


            <!-- ==================================================
                 ICON
            =================================================== -->

            <div class="icon-box staff-icon-box">

              <!-- Staff Group Icon -->

              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="role-svg"
                aria-hidden="true"
              >

                <!-- Center Person -->

                <circle
                  cx="12"
                  cy="7"
                  r="3"
                />

                <path
                  d="M6.8 19.5v-1.8a5.2 5.2 0 0 1 10.4 0v1.8"
                />


                <!-- Left Person -->

                <circle
                  cx="5"
                  cy="9"
                  r="2"
                />

                <path
                  d="M1.5 18v-1.2A3.8 3.8 0 0 1 6 13"
                />


                <!-- Right Person -->

                <circle
                  cx="19"
                  cy="9"
                  r="2"
                />

                <path
                  d="M22.5 18v-1.2A3.8 3.8 0 0 0 18 13"
                />

              </svg>


              <!-- Gear Badge -->

              <div class="gear-badge">

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="gear-icon"
                >

                  <circle
                    cx="12"
                    cy="12"
                    r="3"
                  />

                  <path
                    d="
                      M19.4 15
                      a1.7 1.7 0 0 0 .3 1.9
                      l.1.1
                      a2 2 0 1 1-2.8 2.8
                      l-.1-.1
                      a1.7 1.7 0 0 0-1.9-.3
                      1.7 1.7 0 0 0-1 1.6
                      v.2
                      a2 2 0 1 1-4 0
                      V21
                      a1.7 1.7 0 0 0-1-1.6
                      1.7 1.7 0 0 0-1.9.3
                      l-.1.1
                      A2 2 0 1 1 4.2 17
                      l.1-.1
                      a1.7 1.7 0 0 0 .3-1.9
                      1.7 1.7 0 0 0-1.6-1
                      H2.8
                      a2 2 0 1 1 0-4
                      H3
                      a1.7 1.7 0 0 0 1.6-1
                      1.7 1.7 0 0 0-.3-1.9
                      L4.2 7
                      A2 2 0 1 1 7 4.2
                      l.1.1
                      a1.7 1.7 0 0 0 1.9.3
                      1.7 1.7 0 0 0 1-1.6
                      V2.8
                      a2 2 0 1 1 4 0
                      V3
                      a1.7 1.7 0 0 0 1 1.6
                      1.7 1.7 0 0 0 1.9-.3
                      l.1-.1
                      A2 2 0 1 1 19.8 7
                      l-.1.1
                      a1.7 1.7 0 0 0-.3 1.9
                      1.7 1.7 0 0 0 1.6 1
                      h.2
                      a2 2 0 1 1 0 4
                      H21
                      a1.7 1.7 0 0 0-1.6 1Z
                    "
                  />

                </svg>

              </div>

            </div>


            <!-- ==================================================
                 ACCENT
            =================================================== -->

            <div class="card-accent staff-accent"></div>


            <!-- ==================================================
                 TEXT
            =================================================== -->

            <div class="card-copy">

              <h2>

                Instructor

                <span>
                  / Coordinator
                </span>

              </h2>


              <p>
                Manage student information, attendance, announcements,
                schedules, and excuse letters for your assigned NSTP
                component.
              </p>

            </div>


            <!-- ==================================================
                 ACTION
            =================================================== -->

            <div class="card-footer">

              <span class="continue-text">
                Continue to portal
              </span>


              <div class="arrow-button staff-arrow">

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="arrow-icon"
                >

                  <path
                    d="M5 12h14"
                  />

                  <path
                    d="m13 6 6 6-6 6"
                  />

                </svg>

              </div>

            </div>

          </Link>

        </div>


        <!-- ======================================================
             BOTTOM NOTE
        ======================================================= -->

        <div class="portal-note">

          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="note-icon"
          >

            <circle
              cx="12"
              cy="12"
              r="9"
            />

            <path
              d="M12 11v5"
            />

            <path
              d="M12 8h.01"
            />

          </svg>


          <span>
            You will be asked to verify your university before signing in.
          </span>

        </div>

      </div>

    </main>

  </div>
</template>


<script setup>

import {
  Link,
} from '@inertiajs/vue3';

</script>


<style scoped>

@import url(
  'https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/* ==========================================================
   GLOBAL
========================================================== */

.role-container {

  width:
    100%;

  height:
    100vh;

  display:
    flex;

  overflow:
    hidden;

  background:
    #EFEBE2;

  font-family:
    'Plus Jakarta Sans',
    sans-serif;

}


/* ==========================================================
   LEFT PANEL
========================================================== */

.left-panel {

  width:
    35%;

  max-width:
    440px;

  min-width:
    320px;

  height:
    100vh;

  flex-shrink:
    0;

  position:
    relative;

  display:
    flex;

  flex-direction:
    column;

  justify-content:
    center;

  align-items:
    center;

  padding:
    40px;

  box-sizing:
    border-box;

  background:
    #54100F;

}


/* ==========================================================
   BACK BUTTON
========================================================== */

.back-btn {

  position:
    absolute;

  top:
    45px;

  left:
    45px;

  z-index:
    10;

  width:
    48px;

  height:
    48px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  color:
    #EFEBE2;

  text-decoration:
    none;

  border-radius:
    50%;

  transition:
    color
    0.2s
    ease,
    transform
    0.2s
    ease,
    background
    0.2s
    ease;

}


.back-arrow {

  width:
    36px;

  height:
    36px;

}


.back-btn:hover {

  color:
    #FFBD36;

  background:
    rgba(
      255,
      255,
      255,
      0.08
    );

  transform:
    translateX(
      -4px
    );

}


/* ==========================================================
   BRANDING
========================================================== */

.branding {

  width:
    100%;

  display:
    flex;

  flex-direction:
    column;

  align-items:
    center;

  justify-content:
    center;

  text-align:
    center;

}


.logo {

  width:
    200px;

  max-width:
    100%;

  height:
    auto;

  margin-bottom:
    24px;

  object-fit:
    contain;

  filter:
    drop-shadow(
      1px
      1px
      0
      #EFEBE2
    )
    drop-shadow(
      -1px
      -1px
      0
      #EFEBE2
    )
    drop-shadow(
      1px
      -1px
      0
      #EFEBE2
    )
    drop-shadow(
      -1px
      1px
      0
      #EFEBE2
    )
    drop-shadow(
      0
      4px
      5px
      rgba(
        0,
        0,
        0,
        0.6
      )
    );

}


.branding h3 {

  margin:
    0;

  color:
    #FFFFFF;

  font-size:
    0.95rem;

  font-weight:
    500;

  letter-spacing:
    0.5px;

}


.branding-sub {

  margin:
    6px
    0
    0;

  color:
    #FFFFFF;

  font-size:
    0.75rem;

  opacity:
    0.7;

}


/* ==========================================================
   RIGHT PANEL
========================================================== */

.right-panel {

  position:
    relative;

  flex:
    1;

  min-width:
    0;

  height:
    100vh;

  display:
    flex;

  justify-content:
    center;

  align-items:
    center;

  overflow:
    hidden;

  padding:
    44px
    54px;

  box-sizing:
    border-box;

  background:
    linear-gradient(
      145deg,
      #EFEBE2 0%,
      #FFFFFF 50%,
      #EFEBE2 100%
    );

}


/* ==========================================================
   BACKGROUND DECORATIONS
========================================================== */

.background-circle {

  position:
    absolute;

  border-radius:
    50%;

  pointer-events:
    none;

}


.background-circle-one {

  width:
    430px;

  height:
    430px;

  top:
    -280px;

  right:
    -190px;

  border:
    1px
    solid
    rgba(
      217,
      146,
      2,
      0.1
    );

}


.background-circle-two {

  width:
    320px;

  height:
    320px;

  left:
    -130px;

  bottom:
    -220px;

  background:
    rgba(
      88,
      118,
      28,
      0.025
    );

}


/* ==========================================================
   CONTENT
========================================================== */

.role-content {

  position:
    relative;

  z-index:
    2;

  width:
    100%;

  max-width:
    900px;

  display:
    flex;

  flex-direction:
    column;

  align-items:
    center;

  text-align:
    center;

}


/* ==========================================================
   EYEBROW
========================================================== */

.welcome-eyebrow {

  display:
    flex;

  align-items:
    center;

  gap:
    13px;

  margin-bottom:
    17px;

  color:
    #D99202;

}


.eyebrow-line {

  width:
    44px;

  height:
    1px;

  background:
    #D99202;

  opacity:
    0.55;

}


.eyebrow-icon {

  width:
    23px;

  height:
    23px;

}


/* ==========================================================
   WELCOME TITLE
========================================================== */

.welcome-title {

  margin:
    0;

  color:
    #D99202;

  font-family:
    'Lora',
    serif;

  font-size:
    clamp(
      3.5rem,
      5vw,
      4.8rem
    );

  line-height:
    1;

  font-weight:
    500;

  letter-spacing:
    -1.3px;

}


.title-accent {

  width:
    62px;

  height:
    3px;

  margin:
    19px
    0
    20px;

  border-radius:
    20px;

  background:
    #D99202;

}


/* ==========================================================
   DESCRIPTION
========================================================== */

.welcome-description {

  max-width:
    650px;

  margin:
    0;

  color:
    #233E47;

  font-size:
    1rem;

  line-height:
    1.7;

  font-weight:
    500;

}


.welcome-description strong {

  color:
    #54100F;

  font-weight:
    700;

}


/* ==========================================================
   ROLE OPTIONS
========================================================== */

.role-options {

  width:
    100%;

  margin-top:
    38px;

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
    26px;

}


/* ==========================================================
   ROLE CARD
========================================================== */

.role-card {

  position:
    relative;

  min-height:
    290px;

  padding:
    22px
    24px
    20px;

  display:
    flex;

  flex-direction:
    column;

  align-items:
    flex-start;

  overflow:
    hidden;

  box-sizing:
    border-box;

  color:
    #54100F;

  text-decoration:
    none;

  border:
    1.5px
    solid
    rgba(
      84,
      16,
      15,
      0.42
    );

  border-radius:
    17px;

  background:
    rgba(
      255,
      255,
      255,
      0.9
    );

  box-shadow:
    0
    12px
    28px
    rgba(
      13,
      23,
      27,
      0.07
    );

  transition:
    transform
    0.25s
    ease,
    box-shadow
    0.25s
    ease,
    border-color
    0.25s
    ease;

}


/* ==========================================================
   TOP COLOR STRIPE
========================================================== */

.role-card::before {

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
    4px;

  background:
    linear-gradient(
      90deg,
      #54100F,
      #D99202,
      #FFBD36
    );

  transform:
    scaleX(
      0.18
    );

  transform-origin:
    left;

  transition:
    transform
    0.3s
    ease;

}


/* ==========================================================
   CARD HOVER
========================================================== */

.role-card:hover {

  transform:
    translateY(
      -7px
    );

  border-color:
    #D99202;

  box-shadow:
    0
    20px
    42px
    rgba(
      84,
      16,
      15,
      0.13
    );

}


.role-card:hover::before {

  transform:
    scaleX(
      1
    );

}


/* ==========================================================
   CARD HEADER
========================================================== */

.card-header {

  width:
    100%;

  display:
    flex;

  align-items:
    center;

  justify-content:
    space-between;

  margin-bottom:
    18px;

}


.card-number {

  color:
    #BEBEBE;

  font-family:
    'Lora',
    serif;

  font-size:
    0.8rem;

  font-weight:
    600;

}


.role-badge {

  padding:
    6px
    10px;

  border-radius:
    999px;

  font-size:
    0.58rem;

  font-weight:
    800;

  letter-spacing:
    1px;

}


.admin-badge {

  color:
    #54100F;

  background:
    rgba(
      84,
      16,
      15,
      0.06
    );

  border:
    1px
    solid
    rgba(
      84,
      16,
      15,
      0.1
    );

}


.staff-badge {

  color:
    #58761C;

  background:
    rgba(
      88,
      118,
      28,
      0.07
    );

  border:
    1px
    solid
    rgba(
      88,
      118,
      28,
      0.15
    );

}


/* ==========================================================
   ICON BOX
========================================================== */

.icon-box {

  position:
    relative;

  width:
    76px;

  height:
    76px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  margin-bottom:
    17px;

  color:
    #54100F;

  border:
    1px
    solid
    rgba(
      217,
      146,
      2,
      0.24
    );

  border-radius:
    20px;

  background:
    linear-gradient(
      145deg,
      rgba(
        255,
        189,
        54,
        0.14
      ),
      rgba(
        239,
        235,
        226,
        0.4
      )
    );

  transition:
    transform
    0.25s
    ease,
    background
    0.25s
    ease;

}


.role-card:hover .icon-box {

  transform:
    translateY(
      -3px
    );

  background:
    rgba(
      255,
      189,
      54,
      0.18
    );

}


.staff-icon-box {

  border-color:
    rgba(
      88,
      118,
      28,
      0.22
    );

}


.role-svg {

  width:
    43px;

  height:
    43px;

}


/* ==========================================================
   GEAR BADGE
========================================================== */

.gear-badge {

  position:
    absolute;

  right:
    -7px;

  bottom:
    -6px;

  width:
    27px;

  height:
    27px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  color:
    #FFFFFF;

  border:
    3px
    solid
    #FFFFFF;

  border-radius:
    50%;

  background:
    #D99202;

}


.gear-icon {

  width:
    13px;

  height:
    13px;

}


/* ==========================================================
   ACCENT
========================================================== */

.card-accent {

  width:
    38px;

  height:
    2px;

  margin-bottom:
    13px;

  border-radius:
    20px;

  background:
    #D99202;

}


.staff-accent {

  background:
    #58761C;

}


/* ==========================================================
   CARD COPY
========================================================== */

.card-copy {

  text-align:
    left;

}


.card-copy h2 {

  margin:
    0;

  color:
    #54100F;

  font-family:
    'Lora',
    serif;

  font-size:
    1.65rem;

  line-height:
    1.08;

  font-weight:
    600;

}


.card-copy h2 span {

  color:
    #D99202;

}


.card-copy p {

  margin:
    10px
    0
    0;

  color:
    #233E47;

  font-size:
    0.76rem;

  line-height:
    1.6;

  font-weight:
    500;

  opacity:
    0.8;

}


/* ==========================================================
   CARD FOOTER
========================================================== */

.card-footer {

  width:
    100%;

  margin-top:
    auto;

  padding-top:
    18px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    space-between;

}


.continue-text {

  color:
    #54100F;

  font-size:
    0.72rem;

  font-weight:
    800;

  letter-spacing:
    0.3px;

}


.arrow-button {

  width:
    35px;

  height:
    35px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  color:
    #FFFFFF;

  border-radius:
    50%;

  background:
    #54100F;

  transition:
    transform
    0.25s
    ease,
    background
    0.25s
    ease;

}


.staff-arrow {

  background:
    #58761C;

}


.arrow-icon {

  width:
    16px;

  height:
    16px;

}


.role-card:hover .arrow-button {

  transform:
    translateX(
      4px
    );

  background:
    #D99202;

}


/* ==========================================================
   PORTAL NOTE
========================================================== */

.portal-note {

  margin-top:
    25px;

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  gap:
    8px;

  color:
    #233E47;

  font-size:
    0.72rem;

  font-weight:
    500;

  opacity:
    0.65;

}


.note-icon {

  width:
    15px;

  height:
    15px;

  color:
    #D99202;

}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (
  max-width: 1100px
) {

  .left-panel {

    width:
      32%;

    min-width:
      280px;

  }


  .logo {

    width:
      175px;

  }


  .right-panel {

    padding:
      35px
      28px;

  }


  .role-content {

    max-width:
      760px;

  }


  .welcome-title {

    font-size:
      3.7rem;

  }


  .role-options {

    gap:
      18px;

  }


  .role-card {

    padding:
      20px;

  }


  .card-copy h2 {

    font-size:
      1.4rem;

  }

}


/* ==========================================================
   TABLET
========================================================== */

@media (
  max-width: 900px
) {

  .left-panel {

    display:
      none;

  }


  .role-container {

    min-height:
      100vh;

    height:
      auto;

    overflow-y:
      auto;

  }


  .right-panel {

    width:
      100%;

    min-height:
      100vh;

    height:
      auto;

    padding:
      50px
      30px;

  }

}


/* ==========================================================
   MOBILE
========================================================== */

@media (
  max-width: 650px
) {

  .right-panel {

    padding:
      42px
      20px;

  }


  .welcome-title {

    font-size:
      3rem;

  }


  .welcome-description {

    max-width:
      480px;

    font-size:
      0.9rem;

  }


  .role-options {

    grid-template-columns:
      1fr;

    gap:
      18px;

    margin-top:
      30px;

  }


  .role-card {

    width:
      100%;

    max-width:
      390px;

    min-height:
      285px;

    margin:
      0
      auto;

  }


  .portal-note {

    max-width:
      340px;

    line-height:
      1.45;

  }

}

</style>