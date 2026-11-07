<script setup>
import { computed } from 'vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
|
| All displayed information comes from real data passed by the parent page.
|
*/

const props = defineProps({

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    title: {
        type: String,
        default: 'New User',
    },


    /*
    |--------------------------------------------------------------------------
    | User Data
    |--------------------------------------------------------------------------
    */

    user: {
        type: Object,

        default: () => ({
            full_name: '',
            username: '',
            email: '',
            password: '',
            phone_number: '',
            component: '',
            component_name: '',
            role: '',
            access_code: '',
        }),
    },


    /*
    |--------------------------------------------------------------------------
    | Display Options
    |--------------------------------------------------------------------------
    */

    showRole: {
        type: Boolean,
        default: false,
    },


    showPassword: {
        type: Boolean,
        default: true,
    },


    showAccessCode: {
        type: Boolean,
        default: true,
    },


    /*
    |--------------------------------------------------------------------------
    | Button Text
    |--------------------------------------------------------------------------
    */

    sendEmailButtonText: {
        type: String,
        default: 'SEND EMAIL',
    },


    doneButtonText: {
        type: String,
        default: 'DONE',
    },


    /*
    |--------------------------------------------------------------------------
    | Processing States
    |--------------------------------------------------------------------------
    */

    // Server-only credential PDF download URL; never includes a password.
    downloadPdfUrl: {
        type: String,
        default: '',
    },

    sendingEmail: {
        type: Boolean,
        default: false,
    },


    processing: {
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
    'send-email',
    'done',
]);


/*
|--------------------------------------------------------------------------
| Safe User Data
|--------------------------------------------------------------------------
*/

const fullName = computed(() => {
    return props.user?.full_name ?? '';
});


const username = computed(() => {
    return props.user?.username ?? '';
});


const email = computed(() => {
    return props.user?.email ?? '';
});


const password = computed(() => {
    return props.user?.password ?? '';
});


const phoneNumber = computed(() => {
    return props.user?.phone_number ?? '';
});


const role = computed(() => {
    return props.user?.role ?? '';
});


const accessCode = computed(() => {
    return props.user?.access_code ?? '';
});


/*
|--------------------------------------------------------------------------
| Component Display
|--------------------------------------------------------------------------
|
| Supports either:
|
| component_name:
| "ROTC - Reserve Officers' Training Corps"
|
| OR
|
| component:
| "ROTC"
|
*/

const componentDisplay = computed(() => {

    if (
        props.user?.component_name
    ) {
        return props.user.component_name;
    }


    const component =
        props.user?.component ?? '';


    const componentNames = {

        CWTS:
            'CWTS - Civic Welfare Training Service',

        LTS:
            'LTS - Literacy Training Service',

        ROTC:
            "ROTC - Reserve Officers' Training Corps",

    };


    return (
        componentNames[component] ??
        component
    );

});


/*
|--------------------------------------------------------------------------
| Send Email
|--------------------------------------------------------------------------
*/

const sendEmail = () => {

    if (
        props.sendingEmail
    ) {
        return;
    }

    emit(
        'send-email',
        props.user
    );

};


/*
|--------------------------------------------------------------------------
| Done
|--------------------------------------------------------------------------
*/

const done = () => {

    if (
        props.processing
    ) {
        return;
    }

    emit(
        'done',
        props.user
    );

};
</script>


<template>

    <!-- ============================================================
         NEW USER RESULT
    ============================================================= -->

    <section class="new-user-layout">


        <!-- ========================================================
             HEADER
        ========================================================= -->

        <div class="new-user-header">

            <h2 class="new-user-title">
                {{ title }}
            </h2>


            <div class="title-line"></div>

        </div>


        <!-- ========================================================
             USER INFORMATION
        ========================================================= -->

        <div class="user-information">


            <!-- FULL NAME -->

            <div class="information-row">

                <div class="information-label">
                    Full Name
                </div>

                <div class="information-value">
                    {{ fullName }}
                </div>

            </div>


            <!-- USERNAME -->

            <div class="information-row">

                <div class="information-label">
                    Username
                </div>

                <div class="information-value">
                    {{ username }}
                </div>

            </div>


            <!-- EMAIL -->

            <div class="information-row">

                <div class="information-label">
                    Email Address
                </div>

                <div class="information-value">
                    {{ email }}
                </div>

            </div>


            <!-- PASSWORD -->

            <div
                v-if="showPassword"
                class="information-row"
            >

                <div class="information-label">
                    Password
                </div>

                <div class="information-value">
                    {{ password }}
                </div>

            </div>


            <!-- PHONE NUMBER -->

            <div class="information-row">

                <div class="information-label">
                    Phone Number
                </div>

                <div class="information-value">
                    {{ phoneNumber }}
                </div>

            </div>


            <!-- COMPONENT -->

            <div class="information-row">

                <div class="information-label">
                    Components
                </div>

                <div class="information-value">
                    {{ componentDisplay }}
                </div>

            </div>


            <!-- ROLE -->

            <div
                v-if="showRole"
                class="information-row"
            >

                <div class="information-label">
                    Role
                </div>

                <div class="information-value">
                    {{ role }}
                </div>

            </div>


            <!-- ACCESS CODE -->

            <div
                v-if="showAccessCode"
                class="information-row"
            >

                <div class="information-label">
                    Access Code
                </div>

                <div class="information-value">
                    {{ accessCode }}
                </div>

            </div>

        </div>


        <!-- ========================================================
             SEND EMAIL
        ========================================================= -->

        <div class="email-button-area">

            <a
                v-if="downloadPdfUrl"
                :href="downloadPdfUrl"
                class="download-pdf-button"
                title="Download short bond paper PDF with account credentials"
            >
                DOWNLOAD PDF
            </a>

            <button
                type="button"
                class="send-email-button"
                :disabled="sendingEmail"
                @click="sendEmail"
            >

                <span v-if="!sendingEmail">
                    {{ sendEmailButtonText }}
                </span>

                <span v-else>
                    SENDING...
                </span>

            </button>

        </div>


        <!-- ========================================================
             DONE
        ========================================================= -->

        <div class="done-button-area">

            <button
                type="button"
                class="done-button"
                :disabled="processing"
                @click="done"
            >

                <span v-if="!processing">
                    {{ doneButtonText }}
                </span>

                <span v-else>
                    PLEASE WAIT...
                </span>

            </button>

        </div>

    </section>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
|
| All styles are scoped specifically to NewUserLayout.vue.
|
| They will NOT override:
|
| - UniversityAdminDashLayout.vue
| - UAHeader.vue
| - SideBarUA.vue
| - Other pages
|
*/


/*
|--------------------------------------------------------------------------
| Main Container
|--------------------------------------------------------------------------
*/

.new-user-layout {
    width: 100%;
    max-width: 100%;

    min-width: 0;

    margin: 0;

    padding:
        30px
        34px
        40px;

    position: relative;

    background-color: #EFEBE2;

    box-sizing: border-box;

    color: #233E47;
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

.new-user-header {
    width: 100%;

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 35px;

    box-sizing: border-box;
}


.new-user-title {
    flex: 0 0 auto;

    margin: 0;

    color: #54100F;

    font-family:
        Georgia,
        'Times New Roman',
        serif;

    font-size: 19px;

    font-weight: 700;

    line-height: 1.2;
}


.title-line {
    flex: 1;

    height: 1px;

    margin-top: 3px;

    background-color: #233E47;
}


/*
|--------------------------------------------------------------------------
| User Information
|--------------------------------------------------------------------------
*/

.user-information {
    width: 100%;

    display: flex;

    flex-direction: column;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Information Row
|--------------------------------------------------------------------------
*/

.information-row {
    width: 100%;

    min-height: 42px;

    display: grid;

    grid-template-columns:
        110px
        minmax(0, 1fr);

    align-items: center;

    border-bottom:
        1px solid
        rgba(
            35,
            62,
            71,
            0.45
        );

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

.information-label {
    padding:
        8px
        10px
        7px
        0;

    color: #6D7071;

    font-family:
        Georgia,
        'Times New Roman',
        serif;

    font-size: 12px;

    font-weight: 400;

    line-height: 1.2;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Value
|--------------------------------------------------------------------------
*/

.information-value {
    min-width: 0;

    padding:
        5px
        0;

    overflow-wrap: anywhere;

    color: #233E47;

    font-family:
        Georgia,
        'Times New Roman',
        serif;

    font-size: 22px;

    font-weight: 700;

    line-height: 1.25;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Email Button Area
|--------------------------------------------------------------------------
*/

.email-button-area {
    width: 100%;

    display: flex;
    flex-wrap: wrap;
    gap: 12px;

    justify-content: flex-end;

    margin-top: 37px;

    box-sizing: border-box;
}


/* PDF action matches the existing account confirmation styling. */
.download-pdf-button {
    min-width: 195px;
    min-height: 52px;
    padding: 10px 23px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #58761C;
    border-radius: 16px;
    background: #58761C;
    color: #FFFFFF;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 20px;
    font-weight: 500;
    line-height: 1;
    text-decoration: none;
    box-sizing: border-box;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.download-pdf-button:hover {
    background-color: #435b15;
    transform: translateY(-1px);
}

.download-pdf-button:focus-visible {
    outline: 3px solid rgba(255, 189, 54, 0.6);
    outline-offset: 3px;
}

/*
|--------------------------------------------------------------------------
| Send Email Button
|--------------------------------------------------------------------------
*/

.send-email-button {
    min-width: 195px;

    min-height: 52px;

    padding:
        10px
        25px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border:
        2px solid
        #54100F;

    border-radius: 16px;

    background-color: transparent;

    color: #54100F;

    font-family:
        Georgia,
        'Times New Roman',
        serif;

    font-size: 22px;

    font-weight: 500;

    line-height: 1;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease,
        transform 0.2s ease;

    box-sizing: border-box;
}


.send-email-button:hover:not(:disabled) {
    background-color: #54100F;

    color: #FFFFFF;

    transform:
        translateY(-1px);
}


.send-email-button:focus-visible {
    outline:
        3px solid
        rgba(
            255,
            189,
            54,
            0.6
        );

    outline-offset: 3px;
}


.send-email-button:disabled {
    opacity: 0.55;

    cursor: not-allowed;
}


/*
|--------------------------------------------------------------------------
| Done Button Area
|--------------------------------------------------------------------------
*/

.done-button-area {
    width: 100%;

    display: flex;

    justify-content: center;

    margin-top: 40px;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Done Button
|--------------------------------------------------------------------------
*/

.done-button {
    width: 192px;

    min-height: 56px;

    padding:
        10px
        20px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border: none;

    border-radius: 0;

    background-color: #58761C;

    color: #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 23px;

    font-weight: 400;

    line-height: 1;

    letter-spacing: 0.5px;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;

    box-sizing: border-box;
}


.done-button:hover:not(:disabled) {
    background-color: #D99202;

    transform:
        translateY(-1px);
}


.done-button:focus-visible {
    outline:
        3px solid
        rgba(
            255,
            189,
            54,
            0.7
        );

    outline-offset: 3px;
}


.done-button:disabled {
    opacity: 0.55;

    cursor: not-allowed;
}


/*
|--------------------------------------------------------------------------
| Medium Screens
|--------------------------------------------------------------------------
*/

@media (max-width: 900px) {

    .new-user-layout {
        padding:
            25px
            25px
            35px;
    }


    .information-value {
        font-size: 20px;
    }

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 600px) {

    .new-user-layout {
        padding:
            20px
            16px
            30px;
    }


    .new-user-header {
        margin-bottom: 25px;
    }


    .new-user-title {
        font-size: 17px;
    }


    .information-row {
        grid-template-columns:
            90px
            minmax(0, 1fr);

        min-height: 40px;
    }


    .information-label {
        font-size: 10px;
    }


    .information-value {
        font-size: 17px;
    }


    .email-button-area {
        margin-top: 30px;
    }


    .download-pdf-button {
        min-width: 170px;
        min-height: 48px;
        font-size: 18px;
    }

    .send-email-button {
        min-width: 170px;

        min-height: 48px;

        font-size: 18px;
    }


    .done-button-area {
        margin-top: 35px;
    }


    .done-button {
        width: 175px;

        min-height: 52px;

        font-size: 20px;
    }

}


/*
|--------------------------------------------------------------------------
| Very Small Screens
|--------------------------------------------------------------------------
*/

@media (max-width: 420px) {

    .information-row {
        grid-template-columns: 1fr;

        padding:
            7px
            0;
    }


    .information-label {
        padding:
            0
            0
            3px;

        font-size: 10px;
    }


    .information-value {
        padding: 0;

        font-size: 16px;
    }


    .email-button-area {
        justify-content: center;
    }

}

</style>