<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    /*
    |--------------------------------------------------------------------------
    | Header / Buttons
    |--------------------------------------------------------------------------
    */

    title: {
        type: String,
        default: 'Add Instructor',
    },

    submitButtonText: {
        type: String,
        default: 'Add Instructor',
    },


    /*
    |--------------------------------------------------------------------------
    | Form Values
    |--------------------------------------------------------------------------
    */

    modelValue: {
        type: Object,

        default: () => ({
            full_name: '',
            username: '',
            email: '',
            phone_number: '',
            password: '',
            components: [],
            component: '',
            role: '',
            status: 'active',
        }),
    },


    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    */

    componentOptions: {
        type: Array,
        default: () => [],
    },

    multipleComponents: {
        type: Boolean,
        default: false,
    },


    /*
    |--------------------------------------------------------------------------
    | Coordinator Role
    |--------------------------------------------------------------------------
    */

    showRole: {
        type: Boolean,
        default: false,
    },

    roleOptions: {
        type: Array,
        default: () => [],
    },


    /*
    |--------------------------------------------------------------------------
    | Placeholders
    |--------------------------------------------------------------------------
    */

    componentPlaceholder: {
        type: String,
        default: 'Select a component',
    },

    rolePlaceholder: {
        type: String,
        default: 'Select a Coordinator Role',
    },


    /*
    |--------------------------------------------------------------------------
    | Processing
    |--------------------------------------------------------------------------
    */

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
    'update:modelValue',
    'generate-password',
    'cancel',
    'submit',
]);


/*
|--------------------------------------------------------------------------
| Dropdown State
|--------------------------------------------------------------------------
*/

const componentDropdownOpen =
    ref(false);

const roleDropdownOpen =
    ref(false);

const componentDropdownRef =
    ref(null);

const roleDropdownRef =
    ref(null);


/*
|--------------------------------------------------------------------------
| Update Form Field
|--------------------------------------------------------------------------
*/

const updateField = (
    field,
    value
) => {

    emit(
        'update:modelValue',
        {
            ...props.modelValue,

            [field]:
                value,
        }
    );

};


/*
|--------------------------------------------------------------------------
| Selected Component Label
|--------------------------------------------------------------------------
*/

const selectedComponentLabel = computed(() => {

    if (
        props.multipleComponents
    ) {

        const selected =
            Array.isArray(
                props.modelValue.components
            )
                ? props.modelValue.components
                : [];


        if (
            selected.length === 0
        ) {
            return props.componentPlaceholder;
        }


        if (
            selected.length === 1
        ) {
            const option = props.componentOptions.find(
                item => item.value === selected[0]
            );

            return option?.label ?? selected[0];
        }


        return `${selected.length} components selected`;
    }


    if (
        !props.modelValue.component
    ) {
        return props.componentPlaceholder;
    }


    const selected =
        props.componentOptions.find(
            option =>
                option.value ===
                props.modelValue.component
        );


    return selected?.label ?? props.modelValue.component;
});


/*
|--------------------------------------------------------------------------
| Selected Role Label
|--------------------------------------------------------------------------
*/

const selectedRoleLabel = computed(() => {

    if (
        !props.modelValue.role
    ) {

        return props.rolePlaceholder;

    }


    const selected =
        props.roleOptions.find(
            (option) => {

                return (
                    option.value ===
                    props.modelValue.role
                );

            }
        );


    return (
        selected?.label ??
        props.modelValue.role
    );

});


/*
|--------------------------------------------------------------------------
| Toggle Component Dropdown
|--------------------------------------------------------------------------
*/

const toggleComponentDropdown = () => {

    componentDropdownOpen.value =
        !componentDropdownOpen.value;


    /*
    |--------------------------------------------------------------------------
    | Close Role Dropdown
    |--------------------------------------------------------------------------
    */

    roleDropdownOpen.value =
        false;

};


/*
|--------------------------------------------------------------------------
| Select Component
|--------------------------------------------------------------------------
*/

const selectComponent = (
    option
) => {

    if (
        props.multipleComponents
    ) {

        const current =
            Array.isArray(
                props.modelValue.components
            )
                ? [
                    ...props.modelValue.components,
                ]
                : [];


        const next =
            current.includes(
                option.value
            )
                ? current.filter(
                    component =>
                        component !== option.value
                )
                : [
                    ...current,
                    option.value,
                ];


        emit(
            'update:modelValue',
            {
                ...props.modelValue,
                components: next,
                component: next[0] ?? '',
            }
        );


        return;
    }


    updateField(
        'component',
        option.value
    );


    componentDropdownOpen.value = false;
};


const clearComponent = () => {

    if (
        props.multipleComponents
    ) {

        emit(
            'update:modelValue',
            {
                ...props.modelValue,
                components: [],
                component: '',
            }
        );


        return;
    }


    updateField(
        'component',
        ''
    );


    componentDropdownOpen.value = false;
};


const componentIsSelected = (
    value
) => {

    if (
        props.multipleComponents
    ) {

        return Array.isArray(
            props.modelValue.components
        )
            && props.modelValue.components.includes(
                value
            );
    }


    return props.modelValue.component === value;
};


/*
|--------------------------------------------------------------------------
| Toggle Role Dropdown
|--------------------------------------------------------------------------
*/

const toggleRoleDropdown = () => {

    roleDropdownOpen.value =
        !roleDropdownOpen.value;


    /*
    |--------------------------------------------------------------------------
    | Close Component Dropdown
    |--------------------------------------------------------------------------
    */

    componentDropdownOpen.value =
        false;

};


/*
|--------------------------------------------------------------------------
| Select Role
|--------------------------------------------------------------------------
*/

const selectRole = (
    option
) => {

    updateField(
        'role',
        option.value
    );


    roleDropdownOpen.value =
        false;

};


/*
|--------------------------------------------------------------------------
| Clear Role
|--------------------------------------------------------------------------
*/

const clearRole = () => {

    updateField(
        'role',
        ''
    );


    roleDropdownOpen.value =
        false;

};


/*
|--------------------------------------------------------------------------
| Generate Password
|--------------------------------------------------------------------------
*/

const generatePassword = () => {

    emit(
        'generate-password'
    );

};


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

const cancelForm = () => {

    emit(
        'cancel'
    );

};


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submitForm = () => {

    if (
        props.processing
    ) {

        return;

    }


    emit(
        'submit'
    );

};


/*
|--------------------------------------------------------------------------
| Close Dropdown When Clicking Outside
|--------------------------------------------------------------------------
*/

const handleOutsideClick = (
    event
) => {

    /*
    |--------------------------------------------------------------------------
    | Component Dropdown
    |--------------------------------------------------------------------------
    */

    if (
        componentDropdownRef.value &&
        !componentDropdownRef.value.contains(
            event.target
        )
    ) {

        componentDropdownOpen.value =
            false;

    }


    /*
    |--------------------------------------------------------------------------
    | Role Dropdown
    |--------------------------------------------------------------------------
    */

    if (
        roleDropdownRef.value &&
        !roleDropdownRef.value.contains(
            event.target
        )
    ) {

        roleDropdownOpen.value =
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
</script>


<template>

    <div class="add-user-layout">

        <section class="add-user-card">


            <!-- ====================================================
                 HEADER
            ===================================================== -->

            <header class="form-header">

                <h1>

                    {{ title }}

                </h1>

            </header>


            <!-- ====================================================
                 FORM
            ===================================================== -->

            <form
                class="user-form"
                @submit.prevent="
                    submitForm
                "
            >

                <div class="form-content">


                    <!-- ================================================
                         FULL NAME
                    ================================================= -->

                    <div class="form-group">

                        <label class="form-label">

                            FULL NAME

                        </label>


                        <input
                            :value="
                                modelValue.full_name
                            "
                            type="text"
                            class="form-input"
                            placeholder="e.g Emerald S. Tunner"
                            @input="
                                updateField(
                                    'full_name',
                                    $event.target.value
                                )
                            "
                        />

                    </div>


                    <!-- ================================================
                         USERNAME
                    ================================================= -->

                    <div class="form-group">

                        <label class="form-label">

                            USERNAME

                        </label>


                        <input
                            :value="
                                modelValue.username
                            "
                            type="text"
                            class="form-input"
                            placeholder="e.g emeraldtunner"
                            @input="
                                updateField(
                                    'username',
                                    $event.target.value
                                )
                            "
                        />

                    </div>


                    <!-- ================================================
                         EMAIL
                    ================================================= -->

                    <div class="form-group">

                        <label class="form-label">

                            EMAIL ADDRESS

                        </label>


                        <input
                            :value="
                                modelValue.email
                            "
                            type="email"
                            class="form-input"
                            placeholder="e.g emeraldtunner@ssct.edu.ph"
                            @input="
                                updateField(
                                    'email',
                                    $event.target.value
                                )
                            "
                        />

                    </div>


                    <!-- ================================================
                         PHONE
                    ================================================= -->

                    <div class="form-group">

                        <label class="form-label">

                            PHONE NUMBER

                        </label>


                        <input
                            :value="
                                modelValue.phone_number
                            "
                            type="text"
                            class="form-input"
                            placeholder="e.g 097873215674"
                            @input="
                                updateField(
                                    'phone_number',
                                    $event.target.value
                                )
                            "
                        />

                    </div>


                    <!-- ================================================
                         PASSWORD
                    ================================================= -->

                    <div class="form-group">

                        <label class="form-label">

                            PASSWORD

                        </label>


                        <div class="password-row">

                            <input
                                :value="
                                    modelValue.password
                                "
                                type="text"
                                class="
                                    form-input
                                    password-input
                                "
                                @input="
                                    updateField(
                                        'password',
                                        $event.target.value
                                    )
                                "
                            />


                            <button
                                type="button"
                                class="generate-button"
                                :disabled="
                                    processing
                                "
                                @click="
                                    generatePassword
                                "
                            >

                                GENERATE

                            </button>

                        </div>

                    </div>


                    <!-- ================================================
                         COMPONENTS
                    ================================================= -->

                    <div
                        ref="componentDropdownRef"
                        class="
                            form-group
                            dropdown-form-group
                        "
                    >

                        <label class="form-label">

                            COMPONENTS

                        </label>


                        <div class="custom-dropdown">


                            <!-- COMPONENT TRIGGER -->

                            <button
                                type="button"
                                class="dropdown-trigger"
                                :class="{
                                    'dropdown-open':
                                        componentDropdownOpen
                                }"
                                @click.stop="
                                    toggleComponentDropdown
                                "
                            >

                                <span>

                                    {{
                                        selectedComponentLabel
                                    }}

                                </span>


                                <span
                                    class="dropdown-arrow"
                                    :class="{
                                        'arrow-open':
                                            componentDropdownOpen
                                    }"
                                >
                                </span>

                            </button>


                            <!-- COMPONENT MENU -->

                            <Transition name="dropdown">

                                <div
                                    v-if="
                                        componentDropdownOpen
                                    "
                                    class="dropdown-menu"
                                >

                                    <!-- PLACEHOLDER -->

                                    <button
                                        type="button"
                                        class="
                                            dropdown-option
                                            dropdown-placeholder
                                        "
                                        @click="
                                            clearComponent
                                        "
                                    >

                                        {{
                                            multipleComponents
                                                ? 'Clear selected components'
                                                : componentPlaceholder
                                        }}

                                    </button>


                                    <!-- OPTIONS -->

                                    <button
                                        v-for="
                                            option in
                                            componentOptions
                                        "
                                        :key="
                                            option.value
                                        "
                                        type="button"
                                        class="dropdown-option"
                                        :class="{
                                            selected:
                                                componentIsSelected(
                                                    option.value
                                                )
                                        }"
                                        @click="
                                            selectComponent(
                                                option
                                            )
                                        "
                                    >

                                        <span
                                            v-if="multipleComponents"
                                            class="component-checkbox"
                                            :class="{
                                                checked:
                                                    componentIsSelected(
                                                        option.value
                                                    ),
                                            }"
                                        >
                                            <span></span>
                                        </span>

                                        <span>
                                            {{ option.label }}
                                        </span>

                                    </button>

                                </div>

                            </Transition>

                        </div>

                    </div>


                    <!-- ================================================
                         ROLE
                    ================================================= -->

                    <div
                        v-if="
                            showRole
                        "
                        ref="roleDropdownRef"
                        class="
                            form-group
                            dropdown-form-group
                        "
                    >

                        <label class="form-label">

                            ROLE

                        </label>


                        <div class="custom-dropdown">


                            <!-- ROLE TRIGGER -->

                            <button
                                type="button"
                                class="dropdown-trigger"
                                :class="{
                                    'dropdown-open':
                                        roleDropdownOpen
                                }"
                                @click.stop="
                                    toggleRoleDropdown
                                "
                            >

                                <span>

                                    {{
                                        selectedRoleLabel
                                    }}

                                </span>


                                <span
                                    class="dropdown-arrow"
                                    :class="{
                                        'arrow-open':
                                            roleDropdownOpen
                                    }"
                                >
                                </span>

                            </button>


                            <!-- ROLE MENU -->

                            <Transition name="dropdown">

                                <div
                                    v-if="
                                        roleDropdownOpen
                                    "
                                    class="dropdown-menu"
                                >

                                    <!-- PLACEHOLDER -->

                                    <button
                                        type="button"
                                        class="
                                            dropdown-option
                                            dropdown-placeholder
                                        "
                                        @click="
                                            clearRole
                                        "
                                    >

                                        {{
                                            rolePlaceholder
                                        }}

                                    </button>


                                    <!-- OPTIONS -->

                                    <button
                                        v-for="
                                            option in
                                            roleOptions
                                        "
                                        :key="
                                            option.value
                                        "
                                        type="button"
                                        class="dropdown-option"
                                        :class="{
                                            selected:
                                                modelValue.role ===
                                                option.value
                                        }"
                                        @click="
                                            selectRole(
                                                option
                                            )
                                        "
                                    >

                                        {{ option.label }}

                                    </button>

                                </div>

                            </Transition>

                        </div>

                    </div>


                    <!-- ================================================
                         DIVIDER
                    ================================================= -->

                    <div class="form-divider">
                    </div>


                    <!-- ================================================
                         STATUS
                    ================================================= -->

                    <div
                        class="
                            form-group
                            status-group
                        "
                    >

                        <label class="form-label">

                            ACCOUNT STATUS

                        </label>


                        <div class="status-options">


                            <!-- ACTIVE -->

                            <label class="radio-option">

                                <input
                                    type="radio"
                                    name="status"
                                    value="active"
                                    :checked="
                                        modelValue.status ===
                                        'active'
                                    "
                                    @change="
                                        updateField(
                                            'status',
                                            'active'
                                        )
                                    "
                                />


                                <span class="custom-radio">
                                </span>


                                <span class="radio-text">

                                    Active

                                </span>

                            </label>


                            <!-- INACTIVE -->

                            <label class="radio-option">

                                <input
                                    type="radio"
                                    name="status"
                                    value="inactive"
                                    :checked="
                                        modelValue.status ===
                                        'inactive'
                                    "
                                    @change="
                                        updateField(
                                            'status',
                                            'inactive'
                                        )
                                    "
                                />


                                <span class="custom-radio">
                                </span>


                                <span class="radio-text">

                                    Inactive

                                </span>

                            </label>

                        </div>

                    </div>

                </div>


                <!-- ====================================================
                     FOOTER
                ===================================================== -->

                <footer class="form-footer">

                    <button
                        type="button"
                        class="cancel-button"
                        :disabled="
                            processing
                        "
                        @click="
                            cancelForm
                        "
                    >

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="submit-button"
                        :disabled="
                            processing
                        "
                    >

                        {{
                            processing
                                ? 'Saving...'
                                : submitButtonText
                        }}

                    </button>

                </footer>

            </form>

        </section>

    </div>

</template>


<style scoped>

/* ================================================================
   PAGE
================================================================ */

.add-user-layout {
    width: 100%;

    box-sizing: border-box;

    padding: 14px;

    background: #EFEBE2;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    color: #233E47;
}


/* ================================================================
   CARD
================================================================ */

.add-user-card {
    width: 100%;

    max-width: 760px;

    margin:
        0
        auto;

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
================================================================ */

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
    margin: 0;

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
================================================================ */

.user-form {
    width: 100%;
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
================================================================ */

.form-group {
    position:
        relative;

    margin-bottom:
        20px;
}


/* ================================================================
   LABEL
================================================================ */

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
================================================================ */

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
   PASSWORD
================================================================ */

.password-row {
    display:
        grid;

    grid-template-columns:
        minmax(
            0,
            1fr
        )
        94px;

    gap:
        12px;
}


.generate-button {
    height:
        37px;

    border:
        none;

    background:
        #58761C;

    color:
        #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        12px;

    cursor:
        pointer;

    transition:
        background-color 0.15s ease,
        transform 0.15s ease;
}


.generate-button:hover:not(:disabled) {
    background:
        #54100F;
}


.generate-button:active:not(:disabled) {
    transform:
        scale(0.98);
}


/* ================================================================
   DROPDOWN FORM GROUP
================================================================ */

.dropdown-form-group {
    position:
        relative;

    z-index:
        auto;
}


/* ================================================================
   CUSTOM DROPDOWN
================================================================ */

.custom-dropdown {
    position:
        relative;

    width:
        100%;
}


/* ================================================================
   DROPDOWN TRIGGER
================================================================ */

.dropdown-trigger {
    position:
        relative;

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
================================================================ */

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
        transform 0.18s ease;
}


.dropdown-arrow.arrow-open {
    transform:
        rotate(180deg);
}


/* ================================================================
   DROPDOWN MENU
================================================================
   IMPORTANT FIX:

   The dropdown is NOT absolute anymore.

   It stays in the normal document flow, so opening COMPONENTS
   automatically pushes ROLE downward instead of covering it.
================================================================ */

.dropdown-menu {
    position:
        relative;

    width:
        100%;

    margin-top:
        -1px;

    box-sizing:
        border-box;

    overflow:
        hidden;

    border:
        1.7px solid
        #58761C;

    border-top:
        none;

    background:
        #FFFFFF;

    border-radius:
        0
        0
        7px
        7px;
}


/* ================================================================
   DROPDOWN OPTIONS
================================================================ */

.dropdown-option {
    width:
        100%;

    min-height:
        40px;

    display:
        flex;

    align-items:
        center;

    padding:
        8px
        34px;

    box-sizing:
        border-box;

    border:
        none;

    border-bottom:
        1px solid
        rgba(
            88,
            118,
            28,
            0.18
        );

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
        background-color 0.15s ease,
        color 0.15s ease;
}


.dropdown-option:last-child {
    border-bottom:
        none;
}


/* ================================================================
   PLACEHOLDER
================================================================ */

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


/* ================================================================
   HOVER
================================================================ */

.dropdown-option:hover {
    background:
        rgba(
            88,
            118,
            28,
            0.25
        );
}


/* ================================================================
   SELECTED
================================================================ */

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
   DROPDOWN ANIMATION
================================================================ */

.dropdown-enter-active,
.dropdown-leave-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}


.dropdown-enter-from,
.dropdown-leave-to {
    opacity:
        0;

    transform:
        translateY(-4px);
}


/* ================================================================
   DIVIDER
================================================================ */

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
================================================================ */

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
================================================================ */

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
================================================================ */

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
================================================================ */

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
================================================================ */

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
        background-color 0.15s ease,
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
.submit-button:disabled,
.generate-button:disabled {
    cursor:
        not-allowed;

    opacity:
        0.6;
}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (
    max-width: 700px
) {

    .add-user-layout {
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

}


@media (
    max-width: 500px
) {

    .add-user-layout {
        padding:
            5px;
    }


    .form-content {
        padding:
            25px
            24px
            20px;
    }


    .password-row {
        grid-template-columns:
            1fr;
    }


    .generate-button {
        width:
            115px;

        justify-self:
            end;
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



/* ================================================================
   MULTI-COMPONENT CHECKBOX
================================================================ */

.component-checkbox {
    width: 18px;
    height: 18px;
    flex: 0 0 18px;
    margin-right: 10px;
    border: 1.5px solid #58761C;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #FFFFFF;
    box-sizing: border-box;
}

.component-checkbox.checked {
    background: #58761C;
}

.component-checkbox.checked span {
    width: 8px;
    height: 4px;
    margin-top: -2px;
    border-left: 2px solid #FFFFFF;
    border-bottom: 2px solid #FFFFFF;
    transform: rotate(-45deg);
}

</style>