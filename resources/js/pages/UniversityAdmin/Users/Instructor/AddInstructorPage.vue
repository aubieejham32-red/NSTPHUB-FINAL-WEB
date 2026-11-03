<script setup>

import { computed } from 'vue';

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
| Instructor Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    full_name: '',
    username: '',
    email: '',
    phone_number: '',
    password: '',
    components: [],
    status: 'active',
});


/*
|--------------------------------------------------------------------------
| NSTP Component Options
|--------------------------------------------------------------------------
*/

const componentNames = {
    CWTS: 'CWTS - Civic Welfare Training Service',
    LTS: 'LTS - Literacy Training Service',
    ROTC: "ROTC - Reserve Officers' Training Corps",
};


const componentOptions = computed(() =>
    (Array.isArray(props.components)
        ? props.components
        : []
    )
        .map(component => {
            const value = String(component ?? '')
                .trim()
                .toUpperCase();

            return {
                value,
                label: componentNames[value] ?? value,
            };
        })
        .filter(option =>
            ['LTS', 'CWTS', 'ROTC'].includes(option.value)
        )
);


/*
|--------------------------------------------------------------------------
| Update Form
|--------------------------------------------------------------------------
*/

const updateForm = (updatedForm) => {

    form.full_name =
        updatedForm.full_name ?? '';

    form.username =
        updatedForm.username ?? '';

    form.email =
        updatedForm.email ?? '';

    form.phone_number =
        updatedForm.phone_number ?? '';

    form.password =
        updatedForm.password ?? '';

    form.components =
        Array.isArray(updatedForm.components)
            ? updatedForm.components
            : (updatedForm.component
                ? [updatedForm.component]
                : []);

    form.status =
        updatedForm.status ?? 'active';

};


/*
|--------------------------------------------------------------------------
| Get Random Character
|--------------------------------------------------------------------------
*/

const getRandomCharacter = (characters) => {

    const randomArray =
        new Uint32Array(1);

    crypto.getRandomValues(
        randomArray
    );

    const index =
        randomArray[0] %
        characters.length;

    return characters[index];

};


/*
|--------------------------------------------------------------------------
| Shuffle Password
|--------------------------------------------------------------------------
*/

const shuffleString = (value) => {

    const characters =
        value.split('');

    for (
        let index = characters.length - 1;
        index > 0;
        index--
    ) {

        const randomArray =
            new Uint32Array(1);

        crypto.getRandomValues(
            randomArray
        );

        const randomIndex =
            randomArray[0] %
            (index + 1);

        [
            characters[index],
            characters[randomIndex],
        ] = [
            characters[randomIndex],
            characters[index],
        ];

    }

    return characters.join('');

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

    let password = '';


    /*
    |--------------------------------------------------------------------------
    | Guarantee Required Character Types
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
    | Complete 12 Character Password
    |--------------------------------------------------------------------------
    */

    while (
        password.length < 12
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
| Back To Instructor List
|--------------------------------------------------------------------------
*/

const goBackToInstructors = () => {

    router.visit(
        '/university-admin/instructors'
    );

};


/*
|--------------------------------------------------------------------------
| Submit Instructor
|--------------------------------------------------------------------------
*/

const submitInstructor = () => {

    form.post(
        '/university-admin/instructors',
        {
            preserveScroll: true,

            onSuccess: () => {

                form.reset();

            },

            onError: (errors) => {

                console.error(
                    'Unable to add instructor:',
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
             ADD INSTRUCTOR PAGE
        ========================================================= -->

        <section class="add-instructor-page">


            <!-- ====================================================
                 BACK BUTTON
            ===================================================== -->

            <div class="back-area">

                <button
                    type="button"
                    class="back-button"
                    title="Back to Instructors"
                    aria-label="Back to instructors"
                    @click="goBackToInstructors"
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
                 FORM CONTENT
            ===================================================== -->

            <div class="form-area">

                <AddUserLayout
                    :model-value="form"

                    title="Add Instructor"

                    submit-button-text="Add Instructor"

                    component-placeholder="Select component(s)"

                    :component-options="componentOptions"

                    :multiple-components="true"

                    :show-role="false"

                    :processing="form.processing"

                    @update:model-value="updateForm"

                    @generate-password="generatePassword"

                    @cancel="goBackToInstructors"

                    @submit="submitInstructor"
                />

            </div>

        </section>

    </UniversityAdminDashLayout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
|
| Everything inside this style block belongs ONLY to this Vue page.
|
| Do not remove "scoped".
|
*/


/*
|--------------------------------------------------------------------------
| Add Instructor Page
|--------------------------------------------------------------------------
*/

.add-instructor-page {
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

    margin: 0 0 12px;

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

    flex: 0 0 50px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    margin: 0;
    padding: 0;

    border: none;

    outline: none;

    background-color: transparent;

    color: #54100F;

    cursor: pointer;

    transition:
        color 0.2s ease,
        transform 0.2s ease;

    box-sizing: border-box;
}


.back-button svg {
    display: block;

    width: 42px;
    height: 42px;

    pointer-events: none;
}


.back-button:hover {
    color: #D99202;

    transform:
        translateX(-2px);
}


.back-button:focus-visible {
    outline:
        2px solid #D99202;

    outline-offset: 2px;

    border-radius: 6px;
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

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .back-area {
        margin-bottom: 8px;
    }


    .back-button {
        width: 44px;
        height: 44px;

        flex-basis: 44px;
    }


    .back-button svg {
        width: 36px;
        height: 36px;
    }

}

</style>