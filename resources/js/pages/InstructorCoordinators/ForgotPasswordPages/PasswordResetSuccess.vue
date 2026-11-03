<template>

    <div class="page-container">

        <!-- ==========================================================
             LEFT PANEL
        =========================================================== -->

        <LeftLogo />


        <!-- ==========================================================
             RIGHT PANEL
        =========================================================== -->

        <main class="right-panel">

            <div class="content-wrapper">

                <!-- ==================================================
                     SUCCESS ICON
                =================================================== -->

                <div class="success-icon-wrapper">

                    <svg
                        class="success-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />

                        <path
                            d="m8 12 2.5 2.5L16 9"
                        />
                    </svg>

                </div>


                <!-- ==================================================
                     TITLE
                =================================================== -->

                <h1 class="title">
                    All Set
                </h1>


                <!-- ==================================================
                     SUBTITLE
                =================================================== -->

                <p class="subtitle">

                    Your password has been reset successfully.

                    Use your new password the next time you log in
                    to your NSTP Hub Instructor or Coordinator
                    account.

                </p>


                <!-- ==================================================
                     ACCOUNT TYPE
                =================================================== -->

                <div
                    v-if="accountLabel"
                    class="account-type-container"
                >

                    <span class="account-type-label">

                        {{ accountLabel }}

                    </span>

                </div>


                <!-- ==================================================
                     CONTINUE BUTTON
                =================================================== -->

                <button
                    type="button"
                    class="action-btn"
                    @click="handleContinue"
                >

                    <svg
                        class="button-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M5 12h14"
                        />

                        <path
                            d="m14 7 5 5-5 5"
                        />
                    </svg>


                    <span>
                        Continue to Log in
                    </span>

                </button>

            </div>

        </main>

    </div>

</template>


<script setup>

import {
    computed,
} from 'vue';

import {
    router,
} from '@inertiajs/vue3';

import LeftLogo
    from '@/components/LeftLogo.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
|
| accountType can be:
|
| instructor
| coordinator
|
| It is optional because this success page does not need the account type
| to perform the redirect.
|
*/

const props = defineProps({

    accountType: {
        type: String,

        default: '',
    },

});


/*
|--------------------------------------------------------------------------
| Normalized Account Type
|--------------------------------------------------------------------------
*/

const normalizedAccountType = computed(
    () => {

        const type =
            String(
                props.accountType ??
                ''
            )
                .trim()
                .toLowerCase();


        if (
            type ===
            'instructor'
        ) {

            return 'instructor';

        }


        if (
            type ===
            'coordinator'
        ) {

            return 'coordinator';

        }


        return '';

    }
);


/*
|--------------------------------------------------------------------------
| Account Label
|--------------------------------------------------------------------------
*/

const accountLabel = computed(
    () => {

        if (
            normalizedAccountType.value ===
            'instructor'
        ) {

            return 'INSTRUCTOR ACCOUNT';

        }


        if (
            normalizedAccountType.value ===
            'coordinator'
        ) {

            return 'COORDINATOR ACCOUNT';

        }


        return '';

    }
);


/*
|--------------------------------------------------------------------------
| Continue To Login
|--------------------------------------------------------------------------
|
| Instructor and Coordinator use the same login page.
|
*/

const handleContinue = () => {

    router.visit(
        '/instructor-coordinator/login'
    );

};

</script>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap'
);


/* ==========================================================
   COLOR PALETTE

   Cream       #EFEBE2
   Maroon      #54100F
   Green       #58761C
   Yellow      #FFBD36
   Orange      #D99202
   Dark Teal   #233E47
   Near Black  #000D12
   White       #FFFFFF
   Gray        #BEBEBE
   Dark        #0D171B
========================================================== */


/* ==========================================================
   GLOBAL
========================================================== */

* {

    box-sizing:
        border-box;

}


/* ==========================================================
   PAGE
========================================================== */

.page-container {

    display:
        flex;

    width:
        100%;

    min-height:
        100vh;

    background:
        #EFEBE2;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

}


/* ==========================================================
   RIGHT PANEL
========================================================== */

.right-panel {

    flex:
        1;

    min-width:
        0;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    padding:
        2rem;

    background:
        #EFEBE2;

}


/* ==========================================================
   CONTENT
========================================================== */

.content-wrapper {

    width:
        100%;

    max-width:
        520px;

}


/* ==========================================================
   SUCCESS ICON
========================================================== */

.success-icon-wrapper {

    width:
        72px;

    height:
        72px;

    margin-bottom:
        25px;

    border-radius:
        50%;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    background:
        rgba(
            88,
            118,
            28,
            0.10
        );

    border:
        2px
        solid
        rgba(
            88,
            118,
            28,
            0.24
        );

}


/* ==========================================================
   SUCCESS ICON SVG
========================================================== */

.success-icon {

    width:
        37px;

    height:
        37px;

    fill:
        none;

    stroke:
        #58761C;

    stroke-width:
        2;

    stroke-linecap:
        round;

    stroke-linejoin:
        round;

}


/* ==========================================================
   TITLE
========================================================== */

.title {

    margin:
        0
        0
        16px;

    color:
        #54100F;

    font-size:
        2.3rem;

    font-weight:
        700;

    line-height:
        1.25;

}


/* ==========================================================
   SUBTITLE
========================================================== */

.subtitle {

    margin:
        0;

    color:
        #233E47;

    font-size:
        1rem;

    font-weight:
        400;

    line-height:
        1.7;

}


/* ==========================================================
   ACCOUNT TYPE
========================================================== */

.account-type-container {

    margin-top:
        20px;

}


/* ==========================================================
   ACCOUNT TYPE LABEL
========================================================== */

.account-type-label {

    display:
        inline-flex;

    align-items:
        center;

    min-height:
        28px;

    padding:
        5px
        12px;

    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.25
        );

    border-radius:
        999px;

    background:
        rgba(
            88,
            118,
            28,
            0.09
        );

    color:
        #58761C;

    font-size:
        10px;

    font-weight:
        700;

    letter-spacing:
        0.7px;

}


/* ==========================================================
   ACTION BUTTON
========================================================== */

.action-btn {

    width:
        100%;

    min-height:
        54px;

    margin-top:
        40px;

    padding:
        14px
        20px;

    border:
        none;

    border-radius:
        40px;

    background:
        #D99202;

    color:
        #FFFFFF;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    gap:
        9px;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size:
        1rem;

    font-weight:
        700;

    cursor:
        pointer;

    transition:
        background-color
        0.25s
        ease,
        transform
        0.25s
        ease,
        box-shadow
        0.25s
        ease;

    box-shadow:
        0
        6px
        14px
        rgba(
            217,
            146,
            2,
            0.25
        );

}


/* ==========================================================
   BUTTON HOVER
========================================================== */

.action-btn:hover {

    background:
        #C68402;

    transform:
        translateY(
            -1px
        );

    box-shadow:
        0
        9px
        20px
        rgba(
            217,
            146,
            2,
            0.30
        );

}


/* ==========================================================
   BUTTON ACTIVE
========================================================== */

.action-btn:active {

    transform:
        translateY(
            1px
        );

}


/* ==========================================================
   BUTTON ICON
========================================================== */

.button-icon {

    width:
        19px;

    height:
        19px;

    fill:
        none;

    stroke:
        currentColor;

    stroke-width:
        2;

    stroke-linecap:
        round;

    stroke-linejoin:
        round;

}


/* ==========================================================
   TABLET
========================================================== */

@media (
    max-width: 900px
) {

    .right-panel {

        padding:
            1.5rem;

    }


    .title {

        font-size:
            1.9rem;

    }

}


/* ==========================================================
   MOBILE
========================================================== */

@media (
    max-width: 600px
) {

    .page-container {

        flex-direction:
            column;

    }


    .right-panel {

        width:
            100%;

        padding:
            35px
            22px;

    }


    .title {

        font-size:
            1.7rem;

    }


    .subtitle {

        font-size:
            0.95rem;

    }


    .action-btn {

        font-size:
            0.95rem;

    }

}


/* ==========================================================
   SMALL MOBILE
========================================================== */

@media (
    max-width: 420px
) {

    .right-panel {

        padding:
            30px
            16px;

    }


    .success-icon-wrapper {

        width:
            62px;

        height:
            62px;

    }


    .success-icon {

        width:
            32px;

        height:
            32px;

    }


    .title {

        font-size:
            1.55rem;

    }


    .subtitle {

        font-size:
            0.88rem;

    }


    .action-btn {

        min-height:
            50px;

        margin-top:
            32px;

        font-size:
            0.9rem;

    }

}

</style>