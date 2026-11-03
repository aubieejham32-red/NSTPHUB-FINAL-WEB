<template>
    <Admin_IC_Layout
        :user="resolvedUser"
        :role="resolvedRole"
        :default-collapsed="true"
    >
        <div class="profile-page">

            <!-- =====================================================
                 PROFILE CONTENT
            ====================================================== -->
            <div class="profile-content">

                <!-- =================================================
                     BACK BUTTON
                ================================================== -->
                <button
                    type="button"
                    class="back-button"
                    aria-label="Back to dashboard"
                    @click="goBack"
                >
                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            d="M19 12H5"
                        />

                        <path
                            d="m11 18-6-6 6-6"
                        />
                    </svg>
                </button>


                <!-- =================================================
                     USER INFORMATION
                ================================================== -->
                <div class="profile-information">

                    <!-- =============================================
                         PROFILE PHOTO
                    ============================================== -->
                    <div class="photo-container">

                        <img
                            :src="displayPhoto"
                            :alt="displayName"
                            class="profile-photo"
                            @error="handleImageError"
                        />

                    </div>


                    <!-- =============================================
                         DETAILS
                    ============================================== -->
                    <div class="details-container">

                        <!-- USERNAME -->
                        <h1 class="username">
                            {{ displayUsername }}
                        </h1>


                        <!-- FULL NAME -->
                        <h2 class="full-name">
                            {{ displayName }}
                        </h2>


                        <!-- EMAIL + PHONE -->
                        <div class="contact-information">

                            <!-- EMAIL -->
                            <div class="contact-item email-item">

                                <svg
                                    class="contact-icon mail-icon"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="14"
                                        rx="2"
                                    />

                                    <path
                                        d="m3 7 9 6 9-6"
                                    />
                                </svg>

                                <span>
                                    {{ displayEmail }}
                                </span>

                            </div>


                            <!-- DOT -->
                            <span class="contact-dot"></span>


                            <!-- PHONE -->
                            <div class="contact-item">

                                <svg
                                    class="contact-icon"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M5 4h3l1.5 4-2 1.5a15 15 0 0 0 7 7l1.5-2 4 1.5v3c0 1.1-.9 2-2 2C10.3 21 3 13.7 3 6c0-1.1.9-2 2-2Z"
                                    />
                                </svg>

                                <span>
                                    {{ displayPhone }}
                                </span>

                            </div>

                        </div>


                        <!-- ROLE DESCRIPTION -->
                        <div class="role-description">
                            {{ roleDescription }}
                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ACTION BUTTONS
                ================================================== -->
                <div class="profile-actions">

                    <!-- =============================================
                         PHOTO
                    ============================================== -->
                    <button
                        type="button"
                        class="outline-button"
                        :disabled="uploadingPhoto"
                        @click="openPhotoPicker"
                    >
                        {{
                            uploadingPhoto
                                ? 'UPLOADING...'
                                : photoButtonText
                        }}
                    </button>


                    <!-- HIDDEN FILE INPUT -->
                    <input
                        ref="photoInput"
                        type="file"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="hidden-file-input"
                        @change="handlePhotoSelected"
                    />


                    <!-- =============================================
                         CHANGE PASSWORD
                    ============================================== -->
                    <button
                        type="button"
                        class="outline-button"
                        @click="goToChangePassword"
                    >
                        CHANGE PASSWORD
                    </button>

                </div>


                <!-- =================================================
                     LOGOUT
                ================================================== -->
                <div class="logout-container">

                    <button
                        type="button"
                        class="logout-button"
                        :disabled="loggingOut"
                        @click="logout"
                    >
                        {{
                            loggingOut
                                ? 'LOGGING OUT...'
                                : 'LOGOUT'
                        }}
                    </button>

                </div>

            </div>

        </div>
    </Admin_IC_Layout>
</template>


<script setup>

import {
    computed,
    onBeforeUnmount,
    ref,
} from 'vue';

import {
    router,
} from '@inertiajs/vue3';

import Admin_IC_Layout
    from '@/layouts/Admin_IC_Layout.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
|
| Supports the props currently returned by:
|
| InstructorCoordinatorAuthController
|
| user
| role
| accountType
| auth
|
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


    accountType: {
        type: String,

        default: '',
    },


    university: {
        type: Object,

        default: () => ({}),
    },


    auth: {
        type: Object,

        default: () => ({}),
    },

});


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const photoInput =
    ref(
        null
    );


const photoPreview =
    ref(
        null
    );


const uploadingPhoto =
    ref(
        false
    );


const loggingOut =
    ref(
        false
    );


/*
|--------------------------------------------------------------------------
| Resolved User
|--------------------------------------------------------------------------
|
| Supports:
|
| props.user
|
| OR:
|
| props.auth.user
|
*/

const resolvedUser = computed(() => {

    return (
        props.user &&
        Object.keys(
            props.user
        ).length > 0
    )
        ? props.user
        : (
            props.auth?.user ??
            {}
        );

});


/*
|--------------------------------------------------------------------------
| Resolved Role
|--------------------------------------------------------------------------
*/

const resolvedRole = computed(() => {

    return (
        props.role ||
        props.auth?.role ||
        ''
    );

});


/*
|--------------------------------------------------------------------------
| Resolved Account Type
|--------------------------------------------------------------------------
*/

const resolvedAccountType = computed(() => {

    if (
        props.accountType
    ) {

        return props.accountType;
    }


    if (
        props.auth?.account_type
    ) {

        return props.auth.account_type;
    }


    if (
        resolvedRole.value ===
        'instructor'
    ) {

        return 'instructor';
    }


    if (
        resolvedRole.value.startsWith(
            'coordinator-'
        )
    ) {

        return 'coordinator';
    }


    return '';

});


/*
|--------------------------------------------------------------------------
| University
|--------------------------------------------------------------------------
*/

const resolvedUniversity = computed(() => {

    if (
        props.university &&
        Object.keys(
            props.university
        ).length > 0
    ) {

        return props.university;
    }


    return (
        props.auth?.university ??
        resolvedUser.value?.university ??
        {}
    );

});


/*
|--------------------------------------------------------------------------
| Display Name
|--------------------------------------------------------------------------
*/

const displayName = computed(() => {

    return (
        resolvedUser.value?.full_name ||
        resolvedUser.value?.name ||
        'NSTP Staff'
    );

});


/*
|--------------------------------------------------------------------------
| Username
|--------------------------------------------------------------------------
*/

const displayUsername = computed(() => {

    return (
        resolvedUser.value?.username ||
        'NSTP User'
    );

});


/*
|--------------------------------------------------------------------------
| Email
|--------------------------------------------------------------------------
*/

const displayEmail = computed(() => {

    return (
        resolvedUser.value?.email ||
        'No email available'
    );

});


/*
|--------------------------------------------------------------------------
| Phone
|--------------------------------------------------------------------------
*/

const displayPhone = computed(() => {

    return (
        resolvedUser.value?.phone_number ||
        'No phone number'
    );

});


/*
|--------------------------------------------------------------------------
| Component
|--------------------------------------------------------------------------
*/

const componentName = computed(() => {

    if (
        !resolvedUser.value?.component
    ) {

        return 'NSTP';
    }


    return String(
        resolvedUser.value.component
    ).toUpperCase();

});


/*
|--------------------------------------------------------------------------
| University Display Name
|--------------------------------------------------------------------------
*/

const universityDisplayName = computed(() => {

    const university =
        resolvedUniversity.value;


    const acronym =
        university?.acronym;


    const name =
        university?.name;


    /*
    |--------------------------------------------------------------------------
    | Prefer Acronym
    |--------------------------------------------------------------------------
    */

    if (
        acronym
    ) {

        return acronym;
    }


    if (
        name
    ) {

        return name;
    }


    return 'University';

});


/*
|--------------------------------------------------------------------------
| Campus Text
|--------------------------------------------------------------------------
*/

const campusText = computed(() => {

    const university =
        resolvedUniversity.value;


    /*
    |--------------------------------------------------------------------------
    | Campus Type
    |--------------------------------------------------------------------------
    */

    if (
        university?.campus_type
    ) {

        const campus =
            String(
                university.campus_type
            );


        /*
        |--------------------------------------------------------------------------
        | Avoid Duplicate "Campus"
        |--------------------------------------------------------------------------
        */

        if (
            campus
                .toLowerCase()
                .includes(
                    'campus'
                )
        ) {

            return campus;
        }


        return `${campus} Campus`;

    }


    return 'Main Campus';

});


/*
|--------------------------------------------------------------------------
| Role Description
|--------------------------------------------------------------------------
*/

const roleDescription = computed(() => {

    const component =
        componentName.value;


    const university =
        universityDisplayName.value;


    const campus =
        campusText.value;


    /*
    |--------------------------------------------------------------------------
    | Instructor
    |--------------------------------------------------------------------------
    */

    if (
        resolvedRole.value ===
        'instructor'
    ) {

        return `${component} Instructor at ${university} ${campus}`;

    }


    /*
    |--------------------------------------------------------------------------
    | Attendance Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        resolvedRole.value ===
        'coordinator-attendance'
    ) {

        return `${component} Coordinator Attendance at the ${university} ${campus}`;

    }


    /*
    |--------------------------------------------------------------------------
    | Announcement Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        resolvedRole.value ===
        'coordinator-announcement'
    ) {

        return `${component} Coordinator Announcement at the ${university} ${campus}`;

    }


    /*
    |--------------------------------------------------------------------------
    | Schedule Coordinator
    |--------------------------------------------------------------------------
    */

    if (
        resolvedRole.value ===
        'coordinator-schedule'
    ) {

        return `${component} Coordinator Schedule at the ${university} ${campus}`;

    }


    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    */

    if (
        resolvedAccountType.value ===
        'coordinator'
    ) {

        return `${component} Coordinator at the ${university} ${campus}`;

    }


    return `${component} Staff at ${university} ${campus}`;

});


/*
|--------------------------------------------------------------------------
| Database Profile Photo
|--------------------------------------------------------------------------
*/

const databaseProfilePhoto = computed(() => {

    const photo =
        resolvedUser.value?.profile_photo;


    /*
    |--------------------------------------------------------------------------
    | Default Avatar
    |--------------------------------------------------------------------------
    */

    if (
        !photo
    ) {

        return '/images/default-avatar.png';

    }


    const value =
        String(
            photo
        );


    /*
    |--------------------------------------------------------------------------
    | External URL
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Existing Storage URL
    |--------------------------------------------------------------------------
    */

    if (
        value.startsWith(
            '/storage/'
        )
    ) {

        return value;

    }


    /*
    |--------------------------------------------------------------------------
    | Existing Public URL
    |--------------------------------------------------------------------------
    */

    if (
        value.startsWith(
            '/'
        )
    ) {

        return value;

    }


    /*
    |--------------------------------------------------------------------------
    | Laravel Public Storage
    |--------------------------------------------------------------------------
    */

    return `/storage/${value}`;

});


/*
|--------------------------------------------------------------------------
| Display Photo
|--------------------------------------------------------------------------
*/

const displayPhoto = computed(() => {

    return (
        photoPreview.value ||
        databaseProfilePhoto.value
    );

});


/*
|--------------------------------------------------------------------------
| Photo Button Text
|--------------------------------------------------------------------------
*/

const photoButtonText = computed(() => {

    return resolvedUser.value?.profile_photo
        ? 'EDIT PHOTO'
        : 'UPLOAD PHOTO';

});


/*
|--------------------------------------------------------------------------
| Broken Image
|--------------------------------------------------------------------------
*/

const handleImageError = (
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
| Open Photo Picker
|--------------------------------------------------------------------------
*/

const openPhotoPicker = () => {

    if (
        uploadingPhoto.value
    ) {

        return;

    }


    photoInput.value?.click();

};


/*
|--------------------------------------------------------------------------
| Selected Photo
|--------------------------------------------------------------------------
*/

const handlePhotoSelected = (
    event
) => {

    const file =
        event.target.files?.[0];


    if (
        !file
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Remove Previous Preview
    |--------------------------------------------------------------------------
    */

    if (
        photoPreview.value
    ) {

        URL.revokeObjectURL(
            photoPreview.value
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Create Preview
    |--------------------------------------------------------------------------
    */

    photoPreview.value =
        URL.createObjectURL(
            file
        );


    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    uploadProfilePhoto(
        file
    );

};


/*
|--------------------------------------------------------------------------
| Upload Profile Photo
|--------------------------------------------------------------------------
|
| Expected route:
|
| POST /instructor-coordinator/profile/photo
|
*/

const uploadProfilePhoto = (
    file
) => {

    uploadingPhoto.value =
        true;


    router.post(
        '/instructor-coordinator/profile/photo',
        {
            profile_photo:
                file,
        },
        {
            forceFormData:
                true,

            preserveScroll:
                true,

            onSuccess: () => {

                uploadingPhoto.value =
                    false;


                /*
                |--------------------------------------------------------------------------
                | Clear Input
                |--------------------------------------------------------------------------
                */

                if (
                    photoInput.value
                ) {

                    photoInput.value.value =
                        '';

                }

            },

            onError: () => {

                uploadingPhoto.value =
                    false;

            },

            onFinish: () => {

                uploadingPhoto.value =
                    false;

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

const goToChangePassword = () => {

    router.visit(
        '/instructor-coordinator/change-password'
    );

};


/*
|--------------------------------------------------------------------------
| Back
|--------------------------------------------------------------------------
*/

const goBack = () => {

    router.visit(
        '/instructor-coordinator/dashboard'
    );

};


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

const logout = () => {

    if (
        loggingOut.value
    ) {

        return;

    }


    loggingOut.value =
        true;


    router.post(
        '/instructor-coordinator/logout',
        {},
        {
            onFinish: () => {

                loggingOut.value =
                    false;

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Cleanup Preview URL
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {

    if (
        photoPreview.value
    ) {

        URL.revokeObjectURL(
            photoPreview.value
        );

    }

});

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
   PAGE
========================================================== */

.profile-page {

    width: 100%;

    min-height: 100%;

    background:
        #EFEBE2;

    box-sizing:
        border-box;

    display:
        flex;

    justify-content:
        center;

}


/* ==========================================================
   CONTENT
========================================================== */

.profile-content {

    width: 100%;

    max-width: 1150px;

    position:
        relative;

    padding:
        78px
        65px
        60px;

    box-sizing:
        border-box;

}


/* ==========================================================
   BACK BUTTON
========================================================== */

.back-button {

    position:
        absolute;

    top:
        84px;

    left:
        55px;

    width:
        54px;

    height:
        54px;

    border:
        none;

    background:
        transparent;

    color:
        #54100F;

    cursor:
        pointer;

    padding:
        4px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        50%;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.2s
        ease;

}


.back-button:hover {

    background:
        rgba(
            84,
            16,
            15,
            0.06
        );

}


.back-button:active {

    transform:
        scale(
            0.94
        );

}


.back-button svg {

    width:
        44px;

    height:
        44px;

    fill:
        none;

    stroke:
        currentColor;

    stroke-width:
        2.8;

    stroke-linecap:
        round;

    stroke-linejoin:
        round;

}


/* ==========================================================
   PROFILE INFORMATION
========================================================== */

.profile-information {

    min-height:
        170px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        24px;

    margin-bottom:
        38px;

}


/* ==========================================================
   PHOTO
========================================================== */

.photo-container {

    width:
        150px;

    height:
        150px;

    border:
        7px
        solid
        #FFFFFF;

    border-radius:
        10px;

    overflow:
        hidden;

    background:
        #FFFFFF;

    box-sizing:
        border-box;

    box-shadow:
        0
        1px
        4px
        rgba(
            0,
            13,
            18,
            0.08
        );

    flex-shrink:
        0;

}


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
   DETAILS
========================================================== */

.details-container {

    min-width:
        360px;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    color:
        #54100F;

}


/* ==========================================================
   USERNAME
========================================================== */

.username {

    margin:
        0;

    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        25px;

    font-weight:
        800;

    line-height:
        1.15;

}


/* ==========================================================
   FULL NAME
========================================================== */

.full-name {

    margin:
        12px
        0
        9px;

    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        17px;

    font-weight:
        500;

    line-height:
        1.2;

}


/* ==========================================================
   CONTACT INFORMATION
========================================================== */

.contact-information {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        9px;

    min-height:
        22px;

    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        12px;

}


/* ==========================================================
   CONTACT ITEM
========================================================== */

.contact-item {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

}


.email-item {

    font-style:
        italic;

}


/* ==========================================================
   CONTACT ICONS
========================================================== */

.contact-icon {

    width:
        13px;

    height:
        13px;

    fill:
        none;

    stroke:
        #54100F;

    stroke-width:
        1.8;

    stroke-linecap:
        round;

    stroke-linejoin:
        round;

    flex-shrink:
        0;

}


.mail-icon {

    width:
        12px;

    height:
        12px;

}


/* ==========================================================
   CONTACT DOT
========================================================== */

.contact-dot {

    width:
        8px;

    height:
        8px;

    border-radius:
        50%;

    background:
        #233E47;

    flex-shrink:
        0;

}


/* ==========================================================
   ROLE DESCRIPTION
========================================================== */

.role-description {

    margin-top:
        7px;

    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        14px;

    font-weight:
        600;

    line-height:
        1.3;

}


/* ==========================================================
   PROFILE ACTIONS
========================================================== */

.profile-actions {

    width:
        100%;

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
        42px;

    margin-top:
        18px;

}


/* ==========================================================
   OUTLINE BUTTON
========================================================== */

.outline-button {

    width:
        100%;

    height:
        44px;

    border:
        2px
        solid
        #54100F;

    background:
        transparent;

    color:
        #54100F;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        16px;

    font-weight:
        400;

    cursor:
        pointer;

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


.outline-button:hover:not(:disabled) {

    background:
        #54100F;

    color:
        #FFFFFF;

}


.outline-button:active:not(:disabled) {

    transform:
        translateY(
            1px
        );

}


.outline-button:disabled {

    opacity:
        0.6;

    cursor:
        not-allowed;

}


/* ==========================================================
   HIDDEN FILE INPUT
========================================================== */

.hidden-file-input {

    display:
        none;

}


/* ==========================================================
   LOGOUT
========================================================== */

.logout-container {

    width:
        100%;

    display:
        flex;

    justify-content:
        center;

    margin-top:
        54px;

}


/* ==========================================================
   LOGOUT BUTTON
========================================================== */

.logout-button {

    width:
        54%;

    min-width:
        360px;

    height:
        47px;

    border:
        none;

    background:
        #54100F;

    color:
        #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        17px;

    font-weight:
        400;

    cursor:
        pointer;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.2s
        ease,
        box-shadow
        0.2s
        ease;

}


.logout-button:hover:not(:disabled) {

    background:
        #6A1715;

    box-shadow:
        0
        4px
        10px
        rgba(
            84,
            16,
            15,
            0.18
        );

}


.logout-button:active:not(:disabled) {

    transform:
        translateY(
            1px
        );

}


.logout-button:disabled {

    opacity:
        0.65;

    cursor:
        not-allowed;

}


/* ==========================================================
   MEDIUM SCREEN
========================================================== */

@media (
    max-width: 1100px
) {

    .profile-content {

        padding:
            70px
            50px
            50px;

    }


    .back-button {

        left:
            30px;

    }


    .profile-information {

        gap:
            20px;

    }


    .photo-container {

        width:
            140px;

        height:
            140px;

    }


    .username {

        font-size:
            23px;

    }


    .profile-actions {

        gap:
            28px;

    }

}


/* ==========================================================
   TABLET
========================================================== */

@media (
    max-width: 850px
) {

    .profile-content {

        padding:
            90px
            32px
            45px;

    }


    .back-button {

        top:
            24px;

        left:
            22px;

    }


    .profile-information {

        flex-direction:
            column;

        text-align:
            center;

        gap:
            18px;

    }


    .details-container {

        min-width:
            0;

        width:
            100%;

        align-items:
            center;

    }


    .contact-information {

        justify-content:
            center;

    }


    .profile-actions {

        grid-template-columns:
            1fr;

        gap:
            18px;

        margin-top:
            34px;

    }


    .logout-container {

        margin-top:
            30px;

    }


    .logout-button {

        width:
            100%;

        min-width:
            0;

    }

}


/* ==========================================================
   MOBILE
========================================================== */

@media (
    max-width: 520px
) {

    .profile-content {

        padding:
            82px
            18px
            35px;

    }


    .photo-container {

        width:
            125px;

        height:
            125px;

    }


    .username {

        font-size:
            21px;

    }


    .full-name {

        font-size:
            16px;

    }


    .contact-information {

        flex-direction:
            column;

        gap:
            6px;

    }


    .contact-dot {

        display:
            none;

    }


    .role-description {

        font-size:
            13px;

    }


    .outline-button {

        font-size:
            14px;

    }


    .logout-button {

        font-size:
            15px;

    }

}

</style>