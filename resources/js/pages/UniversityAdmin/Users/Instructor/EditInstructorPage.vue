<script setup>

import {

    computed,

    onBeforeUnmount,

    onMounted,

    ref,

} from 'vue';

import {

    router,

    useForm,

} from '@inertiajs/vue3';

import UniversityAdminDashLayout

    from '@/layouts/UniversityAdminDashLayout.vue';



/*

|--------------------------------------------------------------------------

| Props

|--------------------------------------------------------------------------

*/

const props = defineProps({

    /*

    |--------------------------------------------------------------------------

    | Instructor

    |--------------------------------------------------------------------------

    */

    instructor: {

        type: Object,

        required: true,

    },



    /*

    |--------------------------------------------------------------------------

    | University Components

    |--------------------------------------------------------------------------

    */

    components: {

        type: Array,

        default: () => [],

    },

});



/*

|--------------------------------------------------------------------------

| Form

|--------------------------------------------------------------------------

|

| IMPORTANT:

|

| Password is intentionally NOT included.

|

*/

const form = useForm({

    full_name:

        props.instructor?.full_name ??

        '',

    username:

        props.instructor?.username ??

        '',

    email:

        props.instructor?.email ??

        '',

    phone_number:

        props.instructor?.phone_number ??

        '',

    components:
        Array.isArray(props.instructor?.components)
            ? props.instructor.components
                .map(item => String(item?.component ?? item ?? '').trim().toUpperCase())
                .filter(Boolean)
            : Array.isArray(props.instructor?.component_codes)
                ? props.instructor.component_codes
                    .map(item => String(item ?? '').trim().toUpperCase())
                    .filter(Boolean)
                : (props.instructor?.component
                    ? [String(props.instructor.component).trim().toUpperCase()]
                    : []),

    component:
        String(props.instructor?.component ?? '')
            .trim()
            .toUpperCase(),

    status:

        props.instructor?.status ??

        'active',

});



/*

|--------------------------------------------------------------------------

| Dropdown State

|--------------------------------------------------------------------------

*/

const componentDropdownOpen =

    ref(false);

const componentDropdownRef =

    ref(null);



/*

|--------------------------------------------------------------------------

| Component Names

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

        .map((component) => {

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

        })

        .filter((option) => {

            return (

                option.value !== '' &&

                option.value !== 'ALL'

            );

        });

});



/*

|--------------------------------------------------------------------------

| Selected Component Label

|--------------------------------------------------------------------------

*/

const selectedComponentLabel = computed(() => {
    const selected = Array.isArray(form.components)
        ? form.components
        : [];

    if (!selected.length) {
        return 'Select one or more components';
    }

    if (selected.length === 1) {
        const option = componentOptions.value.find(
            item => item.value === selected[0]
        );

        return option?.label ?? selected[0];
    }

    return `${selected.length} components selected`;
});


/*
|--------------------------------------------------------------------------
| Toggle Component Dropdown
|--------------------------------------------------------------------------
*/

const toggleComponentDropdown = () => {

    componentDropdownOpen.value =

        !componentDropdownOpen.value;

};



/*

|--------------------------------------------------------------------------

| Select Component

|--------------------------------------------------------------------------

*/

const toggleComponent = (option) => {
    const value = String(option?.value ?? '')
        .trim()
        .toUpperCase();

    if (!value) {
        return;
    }

    const current = Array.isArray(form.components)
        ? [...form.components]
        : [];

    form.components = current.includes(value)
        ? current.filter(component => component !== value)
        : [...current, value];

    // Keep the legacy/default component synchronized.
    form.component = form.components[0] ?? '';
};


const isComponentSelected = (value) =>
    Array.isArray(form.components) &&
    form.components.includes(value);


const clearComponents = () => {
    form.components = [];
    form.component = '';
};


/*
|--------------------------------------------------------------------------
| Close Dropdown On Outside Click
|--------------------------------------------------------------------------
*/

const handleOutsideClick = (

    event

) => {

    if (

        componentDropdownRef.value &&

        !componentDropdownRef.value.contains(

            event.target

        )

    ) {

        componentDropdownOpen.value =

            false;

    }

};



/*

|--------------------------------------------------------------------------

| Mounted

|--------------------------------------------------------------------------

*/

onMounted(() => {

    document.addEventListener(

        'click',

        handleOutsideClick

    );

});



/*

|--------------------------------------------------------------------------

| Before Unmount

|--------------------------------------------------------------------------

*/

onBeforeUnmount(() => {

    document.removeEventListener(

        'click',

        handleOutsideClick

    );

});



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

| Update Instructor

|--------------------------------------------------------------------------

*/

const updateInstructor = () => {
    // Keep the old component column as the instructor's default component.
    form.component = Array.isArray(form.components)
        ? (form.components[0] ?? '')
        : '';

    /*

    |--------------------------------------------------------------------------

    | Validate Instructor ID

    |--------------------------------------------------------------------------

    */

    if (

        !props.instructor?.id

    ) {

        console.error(

            'Instructor ID is missing.'

        );

        return;

    }



    /*

    |--------------------------------------------------------------------------

    | Submit Update

    |--------------------------------------------------------------------------

    |

    | Password is NOT included in this request.

    |

    */

    form.put(

        `/university-admin/instructors/${props.instructor.id}`,

        {

            preserveScroll:

                true,



            onSuccess: () => {

                router.visit(

                    '/university-admin/instructors'

                );

            },



            onError: (

                errors

            ) => {

                console.error(

                    'Unable to update instructor:',

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

             EDIT INSTRUCTOR PAGE

        ========================================================= -->

        <section class="edit-instructor-page">



            <!-- ====================================================

                 BACK AREA

            ===================================================== -->

            <div class="back-area">

                <button

                    type="button"

                    class="back-button"

                    title="Back to Instructors"

                    aria-label="Back to instructors"

                    @click="

                        goBackToInstructors

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

                 EDIT USER LAYOUT

            ===================================================== -->

            <div class="edit-user-layout">

                <section class="edit-user-card">



                    <!-- ================================================

                         HEADER

                    ================================================= -->

                    <header class="form-header">

                        <h1>

                            Edit Instructor

                        </h1>

                    </header>



                    <!-- ================================================

                         FORM

                    ================================================= -->

                    <form

                        class="user-form"

                        @submit.prevent="

                            updateInstructor

                        "

                    >

                        <div class="form-content">



                            <!-- ========================================

                                 FULL NAME

                            ========================================= -->

                            <div class="form-group">

                                <label

                                    class="form-label"

                                    for="full_name"

                                >

                                    FULL NAME

                                </label>



                                <input

                                    id="full_name"

                                    v-model="form.full_name"

                                    type="text"

                                    class="form-input"

                                    placeholder="e.g Emerald S. Tunner"

                                    autocomplete="name"

                                />



                                <div

                                    v-if="

                                        form.errors.full_name

                                    "

                                    class="form-error"

                                >

                                    {{ form.errors.full_name }}

                                </div>

                            </div>



                            <!-- ========================================

                                 USERNAME

                            ========================================= -->

                            <div class="form-group">

                                <label

                                    class="form-label"

                                    for="username"

                                >

                                    USERNAME

                                </label>



                                <input

                                    id="username"

                                    v-model="form.username"

                                    type="text"

                                    class="form-input"

                                    placeholder="e.g emeraldtunner"

                                    autocomplete="username"

                                />



                                <div

                                    v-if="

                                        form.errors.username

                                    "

                                    class="form-error"

                                >

                                    {{ form.errors.username }}

                                </div>

                            </div>



                            <!-- ========================================

                                 EMAIL ADDRESS

                            ========================================= -->

                            <div class="form-group">

                                <label

                                    class="form-label"

                                    for="email"

                                >

                                    EMAIL ADDRESS

                                </label>



                                <input

                                    id="email"

                                    v-model="form.email"

                                    type="email"

                                    class="form-input"

                                    placeholder="e.g emeraldtunner@ssct.edu.ph"

                                    autocomplete="email"

                                />



                                <div

                                    v-if="

                                        form.errors.email

                                    "

                                    class="form-error"

                                >

                                    {{ form.errors.email }}

                                </div>

                            </div>



                            <!-- ========================================

                                 PHONE NUMBER

                            ========================================= -->

                            <div class="form-group">

                                <label

                                    class="form-label"

                                    for="phone_number"

                                >

                                    PHONE NUMBER

                                </label>



                                <input

                                    id="phone_number"

                                    v-model="form.phone_number"

                                    type="text"

                                    class="form-input"

                                    placeholder="e.g 097873215674"

                                    autocomplete="tel"

                                />



                                <div

                                    v-if="

                                        form.errors.phone_number

                                    "

                                    class="form-error"

                                >

                                    {{ form.errors.phone_number }}

                                </div>

                            </div>



                            <!-- ========================================

                                 NO PASSWORD FIELD

                            =========================================

                            |

                            | Password is intentionally not displayed

                            | or editable on this page.

                            |

                            ========================================= -->



                            <!-- ========================================
                                 COMPONENTS
                            ========================================= -->

                            <div
                                ref="componentDropdownRef"
                                class="form-group dropdown-form-group"
                            >
                                <label class="form-label">
                                    COMPONENTS
                                </label>

                                <div class="custom-dropdown">
                                    <button
                                        type="button"
                                        class="dropdown-trigger"
                                        :class="{
                                            'dropdown-open': componentDropdownOpen,
                                        }"
                                        @click.stop="toggleComponentDropdown"
                                    >
                                        <span>{{ selectedComponentLabel }}</span>

                                        <span
                                            class="dropdown-arrow"
                                            :class="{
                                                'arrow-open': componentDropdownOpen,
                                            }"
                                        ></span>
                                    </button>

                                    <div
                                        v-if="componentDropdownOpen"
                                        class="dropdown-menu"
                                    >
                                        <button
                                            type="button"
                                            class="dropdown-option dropdown-placeholder"
                                            @click.stop="clearComponents"
                                        >
                                            Clear selected components
                                        </button>

                                        <button
                                            v-for="option in componentOptions"
                                            :key="option.value"
                                            type="button"
                                            class="dropdown-option component-option"
                                            :class="{
                                                selected: isComponentSelected(option.value),
                                            }"
                                            @click.stop="toggleComponent(option)"
                                        >
                                            <span
                                                class="component-check"
                                                :class="{
                                                    checked: isComponentSelected(option.value),
                                                }"
                                            >
                                                <span></span>
                                            </span>

                                            <span class="component-option-label">
                                                {{ option.label }}
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        form.errors.components ||
                                        form.errors['components.0'] ||
                                        form.errors.component
                                    "
                                    class="form-error"
                                >
                                    {{
                                        form.errors.components ||
                                        form.errors['components.0'] ||
                                        form.errors.component
                                    }}
                                </div>

                                <div
                                    v-if="form.components.length"
                                    class="selected-components"
                                >
                                    <span
                                        v-for="component in form.components"
                                        :key="component"
                                        class="selected-component-chip"
                                    >
                                        {{ component }}
                                    </span>
                                </div>
                            </div>

                            <!-- ========================================

                                 DIVIDER

                            ========================================= -->

                            <div

                                class="form-divider"

                            >

                            </div>



                            <!-- ========================================

                                 ACCOUNT STATUS

                            ========================================= -->

                            <div

                                class="

                                    form-group

                                    status-group

                                "

                            >

                                <label

                                    class="form-label"

                                >

                                    ACCOUNT STATUS

                                </label>



                                <div

                                    class="status-options"

                                >



                                    <!-- ACTIVE -->

                                    <label

                                        class="radio-option"

                                    >

                                        <input

                                            v-model="

                                                form.status

                                            "

                                            type="radio"

                                            name="status"

                                            value="active"

                                        />



                                        <span

                                            class="custom-radio"

                                        >

                                        </span>



                                        <span

                                            class="radio-text"

                                        >

                                            Active

                                        </span>

                                    </label>



                                    <!-- INACTIVE -->

                                    <label

                                        class="radio-option"

                                    >

                                        <input

                                            v-model="

                                                form.status

                                            "

                                            type="radio"

                                            name="status"

                                            value="inactive"

                                        />



                                        <span

                                            class="custom-radio"

                                        >

                                        </span>



                                        <span

                                            class="radio-text"

                                        >

                                            Inactive

                                        </span>

                                    </label>

                                </div>



                                <div

                                    v-if="

                                        form.errors.status

                                    "

                                    class="form-error"

                                >

                                    {{ form.errors.status }}

                                </div>

                            </div>

                        </div>



                        <!-- ============================================

                             FOOTER

                        ============================================= -->

                        <footer

                            class="form-footer"

                        >

                            <button

                                type="button"

                                class="cancel-button"

                                :disabled="

                                    form.processing

                                "

                                @click="

                                    goBackToInstructors

                                "

                            >

                                Cancel

                            </button>



                            <button

                                type="submit"

                                class="submit-button"

                                :disabled="

                                    form.processing

                                "

                            >

                                {{

                                    form.processing

                                        ? 'Saving...'

                                        : 'Save Changes'

                                }}

                            </button>

                        </footer>

                    </form>

                </section>

            </div>

        </section>

    </UniversityAdminDashLayout>

</template>



<style scoped>

/* ================================================================

   NSTPHUB PALETTE

   #EFEBE2

   #54100F

   #58761C

   #FFBD36

   #D99202

   #233E47

   #000D12

   #FFFFFF

   #BEBEBE

   #0D171B

\================================================================ */



/* ================================================================

   PAGE

\================================================================ */

.edit-instructor-page {

    width: 100%;

    min-width: 0;

    position: relative;

    margin: 0;

    padding: 0;

    box-sizing: border-box;

    background:

        transparent;

}



/* ================================================================

   BACK AREA

\================================================================ */

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

    box-sizing:

        border-box;

}



/* ================================================================

   BACK BUTTON

\================================================================ */

.back-button {

    width: 50px;

    height: 50px;

    flex:

        0

        0

        50px;

    display:

        inline-flex;

    align-items:

        center;

    justify-content:

        center;

    margin: 0;

    padding: 0;

    border:

        none;

    outline:

        none;

    background-color:

        transparent;

    color:

        #54100F;

    cursor:

        pointer;

    transition:

        color 0.2s ease,

        transform 0.2s ease;

    box-sizing:

        border-box;

}



.back-button svg {

    display:

        block;

    width:

        42px;

    height:

        42px;

    pointer-events:

        none;

}



.back-button:hover {

    color:

        #D99202;

    transform:

        translateX(-2px);

}



.back-button:focus-visible {

    outline:

        2px solid

        #D99202;

    outline-offset:

        2px;

    border-radius:

        6px;

}



/* ================================================================

   EDIT USER LAYOUT

\================================================================ */

.edit-user-layout {

    width: 100%;

    box-sizing:

        border-box;

    padding:

        14px;

    background:

        #EFEBE2;

    font-family:

        Georgia,

        "Times New Roman",

        serif;

    color:

        #233E47;

}



/* ================================================================

   CARD

\================================================================ */

.edit-user-card {

    width:

        100%;

    max-width:

        760px;

    margin:

        0 auto;

    overflow:

        visible;

    border:

        2px solid

        #58761C;

    border-radius:

        9px;

    background:

        #FFFFFF;

    box-sizing:

        border-box;

}



/* ================================================================

   HEADER

\================================================================ */

.form-header {

    min-height:

        77px;

    display:

        flex;

    align-items:

        center;

    padding:

        0

        34px;

    box-sizing:

        border-box;

    border-radius:

        7px

        7px

        0

        0;

    background:

        #58761C;

}



.form-header h1 {

    margin:

        0;

    color:

        #FFFFFF;

    font-size:

        29px;

    font-weight:

        700;

    line-height:

        1.2;

}



/* ================================================================

   FORM

\================================================================ */

.user-form {

    width:

        100%;

}



.form-content {

    padding:

        32px

        52px

        22px;

    box-sizing:

        border-box;

}



/* ================================================================

   FORM GROUP

\================================================================ */

.form-group {

    position:

        relative;

    margin-bottom:

        20px;

}



/* ================================================================

   LABEL

\================================================================ */

.form-label {

    display:

        block;

    margin-bottom:

        10px;

    color:

        #54100F;

    font-family:

        Arial,

        Helvetica,

        sans-serif;

    font-size:

        14px;

    font-weight:

        800;

}



/* ================================================================

   INPUT

\================================================================ */

.form-input {

    width:

        100%;

    height:

        37px;

    padding:

        0

        18px;

    box-sizing:

        border-box;

    border:

        1.7px solid

        #58761C;

    border-radius:

        7px;

    outline:

        none;

    background:

        #EFEBE2;

    color:

        #233E47;

    font-family:

        Georgia,

        "Times New Roman",

        serif;

    font-size:

        14px;

    transition:

        border-color 0.15s ease,

        box-shadow 0.15s ease;

}



.form-input::placeholder {

    color:

        #233E47;

    opacity:

        0.9;

}



.form-input:focus {

    border-color:

        #58761C;

    box-shadow:

        0

        0

        0

        2px

        rgba(

            88,

            118,

            28,

            0.14

        );

}



/* ================================================================

   FORM ERROR

\================================================================ */

.form-error {

    margin-top:

        6px;

    color:

        #54100F;

    font-family:

        Arial,

        Helvetica,

        sans-serif;

    font-size:

        12px;

    font-weight:

        600;

}



/* ================================================================

   CUSTOM DROPDOWN

\================================================================ */

.dropdown-form-group {

    z-index:

        15;

}



.custom-dropdown {

    position:

        relative;

    width:

        100%;

}



/* ================================================================

   DROPDOWN TRIGGER

\================================================================ */

.dropdown-trigger {

    position:

        relative;

    z-index:

        17;

    width:

        100%;

    min-height:

        37px;

    display:

        flex;

    align-items:

        center;

    justify-content:

        space-between;

    gap:

        15px;

    padding:

        7px

        18px;

    box-sizing:

        border-box;

    border:

        1.7px solid

        #58761C;

    border-radius:

        7px;

    outline:

        none;

    background:

        #FFFFFF;

    color:

        #54100F;

    font-family:

        Georgia,

        "Times New Roman",

        serif;

    font-size:

        14px;

    text-align:

        left;

    cursor:

        pointer;

}



.dropdown-trigger.dropdown-open {

    border-radius:

        7px

        7px

        0

        0;

}



/* ================================================================

   DROPDOWN ARROW

\================================================================ */

.dropdown-arrow {

    width:

        0;

    height:

        0;

    flex-shrink:

        0;

    border-left:

        8px solid

        transparent;

    border-right:

        8px solid

        transparent;

    border-top:

        9px solid

        #54100F;

    transition:

        transform 0.15s ease;

}



.dropdown-arrow\.arrow-open {

    transform:

        rotate(180deg);

}



/* ================================================================

   DROPDOWN MENU

\================================================================ */

.dropdown-menu {

    position:

        absolute;

    z-index:

        30;

    top:

        calc(

            100% - 1px

        );

    left:

        0;

    width:

        100%;

    box-sizing:

        border-box;

    border:

        1.7px solid

        #58761C;

    background:

        #FFFFFF;

    overflow:

        hidden;

}



/* ================================================================

   DROPDOWN OPTIONS

\================================================================ */

.dropdown-option {

    width:

        100%;

    min-height:

        36px;

    display:

        flex;

    align-items:

        center;

    padding:

        7px

        30px;

    box-sizing:

        border-box;

    border:

        none;

    background:

        #FFFFFF;

    color:

        #233E47;

    font-family:

        Georgia,

        "Times New Roman",

        serif;

    font-size:

        16px;

    text-align:

        left;

    cursor:

        pointer;

    transition:

        background 0.15s ease,

        color 0.15s ease;

}



.dropdown-placeholder {

    background:

        rgba(

            88,

            118,

            28,

            0.45

        );

    color:

        #54100F;

}



.dropdown-option:hover {

    background:

        rgba(

            88,

            118,

            28,

            0.25

        );

}



.dropdown-option.selected {

    background:

        rgba(

            88,

            118,

            28,

            0.45

        );

    color:

        #54100F;

}



/* ================================================================

   DIVIDER

\================================================================ */



/* ================================================================
   MULTI-COMPONENT OPTIONS
================================================================ */

.component-option {
    gap: 12px;
}

.component-check {
    width: 18px;
    height: 18px;
    flex: 0 0 18px;
    border: 1.5px solid #58761C;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #FFFFFF;
    box-sizing: border-box;
}

.component-check.checked {
    background: #58761C;
}

.component-check.checked span {
    width: 8px;
    height: 4px;
    margin-top: -2px;
    border-left: 2px solid #FFFFFF;
    border-bottom: 2px solid #FFFFFF;
    transform: rotate(-45deg);
}

.component-option-label {
    min-width: 0;
    flex: 1;
}

.selected-components {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.selected-component-chip {
    min-height: 28px;
    padding: 5px 10px;
    border: 1px solid rgba(88, 118, 28, 0.35);
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    background: rgba(88, 118, 28, 0.10);
    color: #54100F;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    font-weight: 800;
}

.form-divider {

    width:

        100%;

    height:

        1px;

    margin:

        8px

        0

        26px;

    background:

        #BEBEBE;

}



/* ================================================================

   STATUS

\================================================================ */

.status-group {

    margin-bottom:

        0;

}



.status-options {

    display:

        flex;

    align-items:

        center;

    gap:

        35px;

    padding-left:

        16px;

}



.radio-option {

    position:

        relative;

    display:

        inline-flex;

    align-items:

        center;

    gap:

        9px;

    color:

        #233E47;

    font-size:

        13px;

    cursor:

        pointer;

}



.radio-option input {

    position:

        absolute;

    opacity:

        0;

    pointer-events:

        none;

}



/* ================================================================

   CUSTOM RADIO

\================================================================ */

.custom-radio {

    position:

        relative;

    width:

        15px;

    height:

        15px;

    box-sizing:

        border-box;

    border:

        1.5px solid

        #58761C;

    border-radius:

        50%;

    background:

        #FFFFFF;

}



.radio-option input:checked +

.custom-radio::after {

    content:

        "";

    position:

        absolute;

    top:

        50%;

    left:

        50%;

    width:

        9px;

    height:

        9px;

    border-radius:

        50%;

    background:

        #58761C;

    transform:

        translate(

            -50%,

            -50%

        );

}



/* ================================================================

   FOOTER

\================================================================ */

.form-footer {

    min-height:

        76px;

    display:

        flex;

    align-items:

        center;

    justify-content:

        flex-end;

    gap:

        14px;

    padding:

        15px

        30px;

    box-sizing:

        border-box;

    border-top:

        1.5px solid

        #58761C;

}



/* ================================================================

   CANCEL BUTTON

\================================================================ */

.cancel-button {

    min-width:

        86px;

    height:

        30px;

    padding:

        0

        18px;

    border:

        1px solid

        #BEBEBE;

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

    font-size:

        12px;

    cursor:

        pointer;

}



.cancel-button:hover:not(:disabled) {

    border-color:

        #54100F;

}



/* ================================================================

   SUBMIT BUTTON

\================================================================ */

.submit-button {

    min-width:

        143px;

    height:

        30px;

    padding:

        0

        22px;

    border:

        none;

    border-radius:

        6px;

    background:

        #54100F;

    color:

        #FFFFFF;

    font-family:

        Georgia,

        "Times New Roman",

        serif;

    font-size:

        12px;

    cursor:

        pointer;

    transition:

        background 0.15s ease,

        transform 0.15s ease;

}



.submit-button:hover:not(:disabled) {

    background:

        #6B1715;

}



.submit-button:active:not(:disabled) {

    transform:

        scale(0.98);

}



.cancel-button:disabled,

.submit-button:disabled {

    cursor:

        not-allowed;

    opacity:

        0.6;

}



/* ================================================================

   RESPONSIVE

\================================================================ */

@media (max-width: 700px) {

    .edit-user-layout {

        padding:

            8px;

    }



    .form-header {

        min-height:

            70px;

        padding:

            0

            25px;

    }



    .form-header h1 {

        font-size:

            25px;

    }



    .form-content {

        padding:

            28px

            34px

            20px;

    }



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



@media (max-width: 500px) {

    .edit-user-layout {

        padding:

            5px;

    }



    .form-content {

        padding:

            25px

            24px

            20px;

    }



    .status-options {

        flex-direction:

            column;

        align-items:

            flex-start;

        gap:

            12px;

    }



    .form-footer {

        justify-content:

            center;

        padding:

            15px

            20px;

    }



    .cancel-button,

    .submit-button {

        flex:

            1;

    }

}

</style>