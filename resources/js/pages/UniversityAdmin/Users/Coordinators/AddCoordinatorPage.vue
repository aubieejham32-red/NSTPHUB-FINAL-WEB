<script setup>

import {
    computed,
} from 'vue';

import {
    router,
    useForm,
} from '@inertiajs/vue3';

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import AddUserLayout
    from '@/layouts/addUserLayout.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    components: {
        type: Array,
        default: () => [],
    },

});


/*
|--------------------------------------------------------------------------
| Coordinator Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    full_name: '',

    username: '',

    email: '',

    phone_number: '',

    password: '',

    component: '',

    role: '',

    status: 'active',

});


/*
|--------------------------------------------------------------------------
| NSTP Component Names
|--------------------------------------------------------------------------
*/

const componentNames = {

    CWTS:
        'CWTS - Civic Welfare Training Service',

    LTS:
        'LTS - Literacy Training Service',

    ROTC:
        "ROTC - Reserve Officers' Training Corps",

};


/*
|--------------------------------------------------------------------------
| Component Options
|--------------------------------------------------------------------------
*/

const componentOptions = computed(() => {

    return props.components
        .map(
            (component) => {

                const value =
                    String(
                        component ?? ''
                    )
                        .trim()
                        .toUpperCase();


                return {

                    value:
                        value,

                    label:
                        componentNames[value] ??
                        value,

                };

            }
        )
        .filter(
            (component) => {

                return (
                    component.value !== ''
                );

            }
        );

});


/*
|--------------------------------------------------------------------------
| Coordinator Role Options
|--------------------------------------------------------------------------
*/

const roleOptions = [

    {
        value:
            'Coordinator-Attendance',

        label:
            'Coordinator - Attendance',
    },

    {
        value:
            'Coordinator-Announcement',

        label:
            'Coordinator - Announcement',
    },

    {
        value:
            'Coordinator-Schedule',

        label:
            'Coordinator - Schedule',
    },

];


/*
|--------------------------------------------------------------------------
| Update Form
|--------------------------------------------------------------------------
*/

const updateForm = (
    updatedForm
) => {

    form.full_name =
        updatedForm.full_name ??
        '';

    form.username =
        updatedForm.username ??
        '';

    form.email =
        updatedForm.email ??
        '';

    form.phone_number =
        updatedForm.phone_number ??
        '';

    form.password =
        updatedForm.password ??
        '';

    form.component =
        updatedForm.component ??
        '';

    form.role =
        updatedForm.role ??
        '';

    form.status =
        updatedForm.status ??
        'active';

};


/*
|--------------------------------------------------------------------------
| Get Random Character
|--------------------------------------------------------------------------
*/

const getRandomCharacter = (
    characters
) => {

    const randomArray =
        new Uint32Array(
            1
        );


    crypto.getRandomValues(
        randomArray
    );


    const index =
        randomArray[0] %
        characters.length;


    return characters[
        index
    ];

};


/*
|--------------------------------------------------------------------------
| Shuffle String
|--------------------------------------------------------------------------
*/

const shuffleString = (
    value
) => {

    const characters =
        value.split(
            ''
        );


    for (
        let index =
            characters.length - 1;

        index > 0;

        index--
    ) {

        const randomArray =
            new Uint32Array(
                1
            );


        crypto.getRandomValues(
            randomArray
        );


        const randomIndex =
            randomArray[0] %
            (
                index + 1
            );


        [
            characters[index],
            characters[randomIndex],
        ] = [
            characters[randomIndex],
            characters[index],
        ];

    }


    return characters.join(
        ''
    );

};


/*
|--------------------------------------------------------------------------
| Generate Password
|--------------------------------------------------------------------------
*/

const generatePassword = () => {

    const uppercase =
        'ABCDEFGHJKLMNPQRSTUVWXYZ';

    const lowercase =
        'abcdefghijkmnopqrstuvwxyz';

    const numbers =
        '23456789';

    const symbols =
        '!@#$%&*';


    const allCharacters =
        uppercase +
        lowercase +
        numbers +
        symbols;


    let password =
        '';


    /*
    |--------------------------------------------------------------------------
    | Required Character Types
    |--------------------------------------------------------------------------
    */

    password +=
        getRandomCharacter(
            uppercase
        );

    password +=
        getRandomCharacter(
            lowercase
        );

    password +=
        getRandomCharacter(
            numbers
        );

    password +=
        getRandomCharacter(
            symbols
        );


    /*
    |--------------------------------------------------------------------------
    | Complete Password
    |--------------------------------------------------------------------------
    */

    while (
        password.length <
        12
    ) {

        password +=
            getRandomCharacter(
                allCharacters
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Shuffle Password
    |--------------------------------------------------------------------------
    */

    form.password =
        shuffleString(
            password
        );

};


/*
|--------------------------------------------------------------------------
| Back To Coordinator List
|--------------------------------------------------------------------------
*/

const goBackToCoordinators = () => {

    router.visit(
        '/university-admin/coordinators'
    );

};


/*
|--------------------------------------------------------------------------
| Submit Coordinator
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| Do NOT manually redirect inside onSuccess.
|
| The Laravel CoordinatorController already redirects to:
|
| university-admin.coordinators.new
|
| Laravel/Inertia will automatically follow that redirect and load:
|
| NewCoordinatorPage.vue
|
*/

const submitCoordinator = () => {

    form.post(
        '/university-admin/coordinators',
        {

            /*
            |--------------------------------------------------------------------------
            | Preserve Scroll
            |--------------------------------------------------------------------------
            */

            preserveScroll:
                false,


            /*
            |--------------------------------------------------------------------------
            | Error
            |--------------------------------------------------------------------------
            */

            onError: (
                errors
            ) => {

                console.error(
                    'Unable to add coordinator:',
                    errors
                );

            },

        }
    );

};

</script>


<template>

    <!-- ============================================================
         UNIVERSITY ADMIN DASHBOARD
    ============================================================= -->

    <UniversityAdminDashLayout>


        <!-- ========================================================
             ADD COORDINATOR PAGE
        ========================================================= -->

        <section class="add-coordinator-page">


            <!-- ====================================================
                 BACK AREA
            ===================================================== -->

            <div class="back-area">

                <button
                    type="button"
                    class="back-button"
                    title="Back to Coordinators"
                    aria-label="Back to coordinators"
                    @click="
                        goBackToCoordinators
                    "
                >

                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >

                        <path
                            d="
                                M20 11H7.83
                                L13.42 5.41
                                L12 4
                                L4 12
                                L12 20
                                L13.42 18.59
                                L7.83 13H20V11Z
                            "
                            fill="currentColor"
                        />

                    </svg>

                </button>

            </div>


            <!-- ====================================================
                 FORM
            ===================================================== -->

            <div class="form-area">

                <AddUserLayout

                    :model-value="form"

                    title="Add Coordinator"

                    submit-button-text="Add Coordinator"

                    component-placeholder="Select a component"

                    :component-options="
                        componentOptions
                    "

                    :show-role="true"

                    role-placeholder="Select a Coordinator Role"

                    :role-options="
                        roleOptions
                    "

                    :processing="
                        form.processing
                    "

                    @update:model-value="
                        updateForm
                    "

                    @generate-password="
                        generatePassword
                    "

                    @cancel="
                        goBackToCoordinators
                    "

                    @submit="
                        submitCoordinator
                    "

                />

            </div>


            <!-- ====================================================
                 VALIDATION ERRORS
            ===================================================== -->

            <div
                v-if="
                    Object.keys(
                        form.errors
                    ).length > 0
                "
                class="validation-errors"
            >

                <p class="validation-title">

                    Please fix the following:

                </p>


                <ul>

                    <li
                        v-for="
                            (
                                error,
                                field
                            ) in
                            form.errors
                        "
                        :key="field"
                    >

                        {{ error }}

                    </li>

                </ul>

            </div>

        </section>

    </UniversityAdminDashLayout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| Add Coordinator Page
|--------------------------------------------------------------------------
*/

.add-coordinator-page {
    width: 100%;

    min-width: 0;

    position: relative;

    margin: 0;

    padding: 0;

    background: transparent;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Back Area
|--------------------------------------------------------------------------
*/

.back-area {
    width: 100%;

    display: flex;

    align-items: center;

    justify-content: flex-end;

    margin:
        0
        0
        12px;

    padding: 0;

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Back Button
|--------------------------------------------------------------------------
*/

.back-button {
    width: 50px;

    height: 50px;

    flex:
        0
        0
        50px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    margin: 0;

    padding: 0;

    border: none;

    outline: none;

    background-color:
        transparent;

    color:
        #54100F;

    cursor: pointer;

    transition:
        color 0.2s ease,
        transform 0.2s ease;

    box-sizing:
        border-box;
}


/*
|--------------------------------------------------------------------------
| Back Arrow Icon
|--------------------------------------------------------------------------
*/

.back-button svg {
    display: block;

    width: 42px;

    height: 42px;

    pointer-events:
        none;
}


/*
|--------------------------------------------------------------------------
| Back Arrow Hover
|--------------------------------------------------------------------------
*/

.back-button:hover {
    color:
        #D99202;

    transform:
        translateX(-2px);
}


/*
|--------------------------------------------------------------------------
| Back Arrow Active
|--------------------------------------------------------------------------
*/

.back-button:active {
    transform:
        translateX(-4px)
        scale(0.96);
}


/*
|--------------------------------------------------------------------------
| Keyboard Focus
|--------------------------------------------------------------------------
*/

.back-button:focus-visible {
    outline:
        2px solid
        #D99202;

    outline-offset:
        2px;

    border-radius:
        6px;
}


/*
|--------------------------------------------------------------------------
| Form Area
|--------------------------------------------------------------------------
*/

.form-area {
    width: 100%;

    min-width: 0;

    margin: 0;

    padding: 0;

    position: relative;

    box-sizing:
        border-box;
}


/*
|--------------------------------------------------------------------------
| Validation Errors
|--------------------------------------------------------------------------
*/

.validation-errors {
    width: 100%;

    margin:
        18px
        0
        0;

    box-sizing:
        border-box;

    padding:
        16px
        22px;

    border:
        1px solid
        #54100F;

    border-radius:
        7px;

    background:
        #FFFFFF;

    color:
        #54100F;

    font-family:
        Georgia,
        "Times New Roman",
        serif;
}


.validation-title {
    margin:
        0
        0
        8px;

    font-weight:
        700;
}


.validation-errors ul {
    margin: 0;

    padding-left:
        20px;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (
    max-width: 700px
) {

    .back-area {
        margin-bottom:
            8px;
    }


    .back-button {
        width:
            44px;

        height:
            44px;

        flex-basis:
            44px;
    }


    .back-button svg {
        width:
            36px;

        height:
            36px;
    }

}

</style>