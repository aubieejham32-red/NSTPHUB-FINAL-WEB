<template>
    <div class="profile-page">

        <UAHeader />

        <!-- ==========================================================
             NOTIFICATION
        =========================================================== -->

        <Transition name="toast">
            <div
                v-if="notification.message"
                class="app-notice"
                :class="`app-notice--${notification.type}`"
                role="status"
            >
                <CheckCircle2
                    v-if="notification.type === 'success'"
                    :size="20"
                    :stroke-width="2.3"
                />

                <CircleAlert
                    v-else
                    :size="20"
                    :stroke-width="2.3"
                />

                <span>
                    {{ notification.message }}
                </span>

                <button
                    type="button"
                    class="app-notice__close"
                    aria-label="Close notification"
                    @click="clearNotification"
                >
                    <X
                        :size="17"
                        :stroke-width="2.3"
                    />
                </button>
            </div>
        </Transition>


        <!-- ==========================================================
             MAIN PROFILE
        =========================================================== -->

        <main class="profile-wrapper">

            <!-- ======================================================
                 BACK
            ======================================================= -->

            <div class="back-row">

                <button
                    class="back-btn"
                    type="button"
                    aria-label="Back to dashboard"
                    @click="goBack"
                >
                    <ArrowLeft
                        :size="32"
                        :stroke-width="2.6"
                    />
                </button>

            </div>


            <!-- ======================================================
                 PROFILE HEADER
            ======================================================= -->

            <section class="profile-header">

                <div class="profile-info">

                    <div class="profile-photo-card">

                        <img
                            :src="profilePhoto"
                            class="profile-photo"
                            alt="University Administrator"
                            @error="handleProfilePhotoError"
                        >

                    </div>


                    <div class="profile-text">

                        <span class="profile-kicker">
                            UNIVERSITY ADMINISTRATOR
                        </span>

                        <h1 class="username">
                            {{ admin.username || 'ADMINISTRATOR' }}
                        </h1>

                        <h2 class="fullname">
                            {{ fullName }}
                        </h2>

                        <p class="email">
                            {{ admin.email }}
                        </p>

                        <p class="role">

                            University Administrator

                            <span v-if="university.name">
                                in {{ university.name }}
                            </span>

                        </p>

                    </div>

                </div>

            </section>


            <!-- ======================================================
                 PROFILE ACTIONS
            ======================================================= -->

            <section class="profile-actions">

                <input
                    ref="photoInput"
                    type="file"
                    class="hidden-photo-input"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    @change="handlePhotoSelected"
                >


                <button
                    type="button"
                    class="outline-btn"
                    :disabled="uploadingPhoto"
                    @click="uploadPhoto"
                >

                    <LoaderCircle
                        v-if="uploadingPhoto"
                        class="spin"
                        :size="18"
                        :stroke-width="2.2"
                    />

                    <ImagePlus
                        v-else
                        :size="18"
                        :stroke-width="2.2"
                    />

                    <span>
                        {{ photoButtonText }}
                    </span>

                </button>


                <button
                    type="button"
                    class="outline-btn"
                    @click="changePassword"
                >

                    <KeyRound
                        :size="18"
                        :stroke-width="2.2"
                    />

                    <span>
                        CHANGE PASSWORD
                    </span>

                </button>

            </section>


            <div class="divider"></div>


            <!-- ======================================================
                 UNIVERSITY HEADER
            ======================================================= -->

            <section class="university-header">

                <div
                    class="university-logo-card"
                    :class="{
                        'logo-editable':
                            editingUniversity
                    }"
                    @click="openLogoSelector"
                >

                    <img
                        :src="universityLogoPreview"
                        class="university-logo"
                        alt="University Logo"
                        @error="handleUniversityLogoError"
                    >


                    <div
                        v-if="editingUniversity"
                        class="logo-edit-overlay"
                    >

                        <Pencil
                            :size="28"
                            :stroke-width="2"
                        />

                        <span>
                            CHANGE LOGO
                        </span>

                    </div>

                </div>


                <input
                    ref="universityLogoInput"
                    type="file"
                    class="hidden-photo-input"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    @change="handleUniversityLogoSelected"
                >


                <div class="university-information">

                    <div class="edit-row">

                        <button
                            type="button"
                            class="edit-button"
                            :class="{
                                'cancel-edit-button':
                                    editingUniversity
                            }"
                            :disabled="savingUniversity"
                            @click="toggleUniversityEdit"
                        >

                            <Pencil
                                v-if="!editingUniversity"
                                :size="18"
                                :stroke-width="2.1"
                            />

                            <X
                                v-else
                                :size="18"
                                :stroke-width="2.1"
                            />

                            <span>
                                {{
                                    editingUniversity
                                        ? 'CANCEL EDITING'
                                        : 'EDIT UNIVERSITY INFORMATION'
                                }}
                            </span>

                        </button>

                    </div>


                    <h2 class="university-name">
                        {{
                            editingUniversity
                                ? universityForm.name
                                : university.name
                        }}
                    </h2>


                    <div class="meta-row">

                        <span class="status-pill">
                            {{ statusText }}
                        </span>


                        <span class="meta-item">
                            {{
                                editingUniversity
                                    ? universityForm.acronym
                                    : university.acronym
                            }}
                        </span>


                        <span class="dot">
                            •
                        </span>


                        <span class="meta-item">
                            {{
                                editingUniversity
                                    ? universityForm.type
                                    : university.type
                            }}
                        </span>


                        <span class="dot">
                            •
                        </span>


                        <span class="meta-item">
                            {{
                                editingUniversity
                                    ? universityForm.campus_type
                                    : university.campus_type
                            }}
                        </span>


                        <span class="access-code">

                            Access Code:

                            <strong>
                                {{ university.access_code }}
                            </strong>

                        </span>

                    </div>

                </div>

            </section>


            <!-- ======================================================
                 EDIT NOTICE
            ======================================================= -->

            <div
                v-if="editingUniversity"
                class="editing-notice"
            >

                <Pencil
                    :size="20"
                    :stroke-width="2.2"
                />

                <div>

                    <strong>
                        University Information Editing Mode
                    </strong>

                    <p>
                        Update the logo and university information below,
                        then click Save Changes.
                    </p>

                </div>

            </div>


            <!-- ======================================================
                 INSTITUTION INFORMATION
            ======================================================= -->

            <section
                class="info-card"
                :class="{
                    'editing-card':
                        editingUniversity
                }"
            >

                <div class="card-title">

                    <Building2
                        :size="21"
                        :stroke-width="2.1"
                    />

                    <span>
                        Institution Information
                    </span>

                    <span
                        v-if="editingUniversity"
                        class="editing-badge"
                    >
                        EDITING
                    </span>

                </div>


                <div class="info-grid">

                    <InfoField
                        label="University Name"
                        field="name"
                        type="text"
                        placeholder="University Name"
                        :editing="editingUniversity"
                        :value="university.name"
                        :model-value="universityForm.name"
                        :error="universityErrors.name"
                        @update:model-value="
                            universityForm.name =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'name'
                            )
                        "
                    />


                    <InfoField
                        label="University Acronym"
                        field="acronym"
                        type="text"
                        placeholder="University Acronym"
                        :editing="editingUniversity"
                        :value="university.acronym"
                        :model-value="universityForm.acronym"
                        :error="universityErrors.acronym"
                        @update:model-value="
                            universityForm.acronym =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'acronym'
                            )
                        "
                    />


                    <InfoField
                        label="University Type"
                        field="type"
                        type="text"
                        placeholder="University Type"
                        :editing="editingUniversity"
                        :value="university.type"
                        :model-value="universityForm.type"
                        :error="universityErrors.type"
                        @update:model-value="
                            universityForm.type =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'type'
                            )
                        "
                    />


                    <InfoField
                        label="Campus Type"
                        field="campus_type"
                        type="text"
                        placeholder="Campus Type"
                        :editing="editingUniversity"
                        :value="university.campus_type"
                        :model-value="universityForm.campus_type"
                        :error="universityErrors.campus_type"
                        @update:model-value="
                            universityForm.campus_type =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'campus_type'
                            )
                        "
                    />


                    <InfoField
                        label="University Email"
                        field="email"
                        type="email"
                        placeholder="University Email"
                        :editing="editingUniversity"
                        :value="university.email"
                        :model-value="universityForm.email"
                        :error="universityErrors.email"
                        @update:model-value="
                            universityForm.email =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'email'
                            )
                        "
                    />


                    <InfoField
                        label="Contact Number"
                        field="contact_number"
                        type="text"
                        placeholder="Contact Number"
                        :editing="editingUniversity"
                        :value="university.contact_number"
                        :model-value="universityForm.contact_number"
                        :error="universityErrors.contact_number"
                        @update:model-value="
                            universityForm.contact_number =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'contact_number'
                            )
                        "
                    />


                    <div class="info-cell info-cell--full">

                        <label>
                            Website
                        </label>


                        <input
                            v-if="editingUniversity"
                            v-model="universityForm.website"
                            class="edit-input"
                            type="text"
                            placeholder="www.university.edu.ph"
                            @input="
                                clearUniversityError(
                                    'website'
                                )
                            "
                        >


                        <a
                            v-else-if="university.website"
                            :href="website"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ university.website }}
                        </a>


                        <p v-else>
                            —
                        </p>


                        <span
                            v-if="universityErrors.website"
                            class="field-error"
                        >
                            {{ universityErrors.website }}
                        </span>

                    </div>

                </div>

            </section>


            <!-- ======================================================
                 ADDRESS INFORMATION
            ======================================================= -->

            <section
                class="info-card"
                :class="{
                    'editing-card':
                        editingUniversity
                }"
            >

                <div class="card-title">

                    <MapPin
                        :size="21"
                        :stroke-width="2.1"
                    />

                    <span>
                        Address Information
                    </span>

                    <span
                        v-if="editingUniversity"
                        class="editing-badge"
                    >
                        EDITING
                    </span>

                </div>


                <div class="info-grid">

                    <InfoField
                        label="Region"
                        field="region"
                        type="text"
                        placeholder="Region"
                        :editing="editingUniversity"
                        :value="university.region"
                        :model-value="universityForm.region"
                        :error="universityErrors.region"
                        @update:model-value="
                            universityForm.region =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'region'
                            )
                        "
                    />


                    <InfoField
                        label="Province"
                        field="province"
                        type="text"
                        placeholder="Province"
                        :editing="editingUniversity"
                        :value="university.province"
                        :model-value="universityForm.province"
                        :error="universityErrors.province"
                        @update:model-value="
                            universityForm.province =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'province'
                            )
                        "
                    />


                    <InfoField
                        label="City"
                        field="city"
                        type="text"
                        placeholder="City"
                        :editing="editingUniversity"
                        :value="university.city"
                        :model-value="universityForm.city"
                        :error="universityErrors.city"
                        @update:model-value="
                            universityForm.city =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'city'
                            )
                        "
                    />


                    <InfoField
                        label="Barangay"
                        field="barangay"
                        type="text"
                        placeholder="Barangay"
                        :editing="editingUniversity"
                        :value="university.barangay"
                        :model-value="universityForm.barangay"
                        :error="universityErrors.barangay"
                        @update:model-value="
                            universityForm.barangay =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'barangay'
                            )
                        "
                    />


                    <InfoField
                        label="Zip Code"
                        field="zip_code"
                        type="text"
                        placeholder="Zip Code"
                        :editing="editingUniversity"
                        :value="university.zip_code"
                        :model-value="universityForm.zip_code"
                        :error="universityErrors.zip_code"
                        @update:model-value="
                            universityForm.zip_code =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'zip_code'
                            )
                        "
                    />


                    <div class="info-cell">

                        <label>
                            Complete Address
                        </label>


                        <textarea
                            v-if="editingUniversity"
                            v-model="universityForm.complete_address"
                            class="edit-input edit-textarea"
                            placeholder="Complete Address"
                            @input="
                                clearUniversityError(
                                    'complete_address'
                                )
                            "
                        ></textarea>


                        <p
                            v-else
                            class="multiline"
                        >
                            {{
                                university.complete_address
                                ||
                                '—'
                            }}
                        </p>


                        <span
                            v-if="universityErrors.complete_address"
                            class="field-error"
                        >
                            {{ universityErrors.complete_address }}
                        </span>

                    </div>

                </div>

            </section>


            <!-- ======================================================
                 NSTP CONFIGURATION
            ======================================================= -->

            <section
                class="info-card"
                :class="{
                    'editing-card':
                        editingUniversity
                }"
            >

                <div class="card-title">

                    <GraduationCap
                        :size="22"
                        :stroke-width="2.1"
                    />

                    <span>
                        NSTP Configuration
                    </span>

                    <span
                        v-if="editingUniversity"
                        class="editing-badge"
                    >
                        EDITING
                    </span>

                </div>


                <div class="info-grid">

                    <InfoField
                        label="Academic Year"
                        field="academic_year"
                        type="text"
                        placeholder="2026-2027"
                        :editing="editingUniversity"
                        :value="university.academic_year"
                        :model-value="universityForm.academic_year"
                        :error="universityErrors.academic_year"
                        @update:model-value="
                            universityForm.academic_year =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'academic_year'
                            )
                        "
                    />


                    <InfoField
                        label="Semester"
                        field="semester"
                        type="text"
                        placeholder="1st Semester"
                        :editing="editingUniversity"
                        :value="university.semester"
                        :model-value="universityForm.semester"
                        :error="universityErrors.semester"
                        @update:model-value="
                            universityForm.semester =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'semester'
                            )
                        "
                    />


                    <div class="info-cell">

                        <label>
                            NSTP Components
                        </label>


                        <input
                            v-if="editingUniversity"
                            v-model="universityForm.components"
                            class="edit-input"
                            type="text"
                            placeholder="CWTS, ROTC, LTS"
                            @input="
                                clearUniversityError(
                                    'components'
                                )
                            "
                        >


                        <p v-else>
                            {{
                                formattedComponents
                                ||
                                '—'
                            }}
                        </p>


                        <span
                            v-if="universityErrors.components"
                            class="field-error"
                        >
                            {{ universityErrors.components }}
                        </span>

                    </div>


                    <InfoField
                        label="Maximum Students"
                        field="max_students"
                        type="number"
                        placeholder="Maximum Students"
                        :editing="editingUniversity"
                        :value="university.max_students"
                        :model-value="universityForm.max_students"
                        :error="universityErrors.max_students"
                        @update:model-value="
                            universityForm.max_students =
                                $event
                        "
                        @input="
                            clearUniversityError(
                                'max_students'
                            )
                        "
                    />

                </div>

            </section>


            <!-- ======================================================
                 SAVE UNIVERSITY
            ======================================================= -->

            <div
                v-if="editingUniversity"
                class="university-save-wrapper"
            >

                <button
                    type="button"
                    class="cancel-university-button"
                    :disabled="savingUniversity"
                    @click="cancelUniversityEdit"
                >

                    <X
                        :size="18"
                        :stroke-width="2.2"
                    />

                    <span>
                        CANCEL
                    </span>

                </button>


                <button
                    type="button"
                    class="save-university-button"
                    :disabled="savingUniversity"
                    @click="saveUniversityInformation"
                >

                    <LoaderCircle
                        v-if="savingUniversity"
                        class="spin"
                        :size="18"
                        :stroke-width="2.2"
                    />

                    <Save
                        v-else
                        :size="18"
                        :stroke-width="2.2"
                    />

                    <span>
                        {{
                            savingUniversity
                                ? 'SAVING...'
                                : 'SAVE CHANGES'
                        }}
                    </span>

                </button>

            </div>


            <!-- ======================================================
                 LOGOUT
            ======================================================= -->

            <div
                v-if="!editingUniversity"
                class="logout-wrapper"
            >

                <button
                    type="button"
                    class="logout-btn"
                    :disabled="loggingOut"
                    @click="logout"
                >

                    <LoaderCircle
                        v-if="loggingOut"
                        class="spin"
                        :size="20"
                        :stroke-width="2.3"
                    />

                    <LogOut
                        v-else
                        :size="20"
                        :stroke-width="2.3"
                    />

                    <span>
                        {{
                            loggingOut
                                ? 'LOGGING OUT...'
                                : 'LOGOUT'
                        }}
                    </span>

                </button>

            </div>

        </main>


        <!-- ==========================================================
             PROFESSIONAL LOGOUT CONFIRMATION MODAL
        =========================================================== -->

        <Teleport to="body">

            <Transition name="logout-modal">

                <div
                    v-if="showLogoutModal"
                    class="logout-modal-backdrop"
                    @click.self="closeLogoutModal"
                >

                    <section
                        class="logout-modal-card"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="logout-modal-title"
                        aria-describedby="logout-modal-description"
                    >

                        <div class="logout-modal-accent"></div>


                        <!-- CLOSE -->

                        <button
                            type="button"
                            class="logout-modal-close"
                            aria-label="Close logout confirmation"
                            :disabled="loggingOut"
                            @click="closeLogoutModal"
                        >

                            <X
                                :size="21"
                                :stroke-width="2.3"
                            />

                        </button>


                        <!-- LOGOUT ICON -->

                        <div class="logout-modal-icon">

                            <LogOut
                                :size="34"
                                :stroke-width="2.1"
                            />

                        </div>


                        <span class="logout-modal-eyebrow">
                            SECURE SIGN OUT
                        </span>


                        <h2 id="logout-modal-title">
                            Confirm Logout
                        </h2>


                        <p id="logout-modal-description">
                            Are you sure you want to log out of your University
                            Administrator account?
                        </p>


                        <!-- ACCOUNT -->

                        <div class="logout-modal-account">

                            <div class="logout-modal-account-icon">

                                <ShieldCheck
                                    :size="22"
                                    :stroke-width="2.1"
                                />

                            </div>


                            <div class="logout-modal-account-copy">

                                <strong>
                                    {{
                                        fullName
                                        ||
                                        admin.username
                                        ||
                                        'University Administrator'
                                    }}
                                </strong>


                                <span>
                                    {{
                                        university.name
                                            ? `${university.name} • University Administrator`
                                            : 'University Administrator'
                                    }}
                                </span>

                            </div>

                        </div>


                        <!-- LOGOUT ERROR -->

                        <div
                            v-if="logoutError"
                            class="logout-modal-error"
                            role="alert"
                        >

                            <ShieldAlert
                                :size="19"
                                :stroke-width="2.2"
                            />

                            <span>
                                {{ logoutError }}
                            </span>

                        </div>


                        <!-- ACTIONS -->

                        <div class="logout-modal-actions">

                            <button
                                type="button"
                                class="logout-modal-cancel"
                                :disabled="loggingOut"
                                @click="closeLogoutModal"
                            >

                                <X
                                    :size="18"
                                    :stroke-width="2.2"
                                />

                                <span>
                                    STAY SIGNED IN
                                </span>

                            </button>


                            <button
                                type="button"
                                class="logout-modal-confirm"
                                :disabled="loggingOut"
                                @click="confirmLogout"
                            >

                                <LoaderCircle
                                    v-if="loggingOut"
                                    class="spin"
                                    :size="19"
                                    :stroke-width="2.3"
                                />

                                <LogOut
                                    v-else
                                    :size="19"
                                    :stroke-width="2.3"
                                />

                                <span>
                                    {{
                                        loggingOut
                                            ? 'LOGGING OUT...'
                                            : 'YES, LOGOUT'
                                    }}
                                </span>

                            </button>

                        </div>

                    </section>

                </div>

            </Transition>

        </Teleport>

    </div>
</template>


<script setup>

import {
    computed,
    defineComponent,
    h,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
    watch,
} from 'vue'

import {
    router,
    usePage,
} from '@inertiajs/vue3'

import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    CircleAlert,
    GraduationCap,
    ImagePlus,
    KeyRound,
    LoaderCircle,
    LogOut,
    MapPin,
    Pencil,
    Save,
    ShieldAlert,
    ShieldCheck,
    X,
} from 'lucide-vue-next'

import UAHeader from '@/components/UAHeader.vue'


/*
|--------------------------------------------------------------------------
| Reusable Information Field
|--------------------------------------------------------------------------
*/

const InfoField =
    defineComponent({

        name:
            'InfoField',

        props: {

            label: {
                type:
                    String,

                required:
                    true,
            },

            field: {
                type:
                    String,

                required:
                    true,
            },

            type: {
                type:
                    String,

                default:
                    'text',
            },

            placeholder: {
                type:
                    String,

                default:
                    '',
            },

            editing: {
                type:
                    Boolean,

                default:
                    false,
            },

            value: {
                type: [
                    String,
                    Number,
                ],

                default:
                    '',
            },

            modelValue: {
                type: [
                    String,
                    Number,
                ],

                default:
                    '',
            },

            error: {
                type:
                    String,

                default:
                    '',
            },

        },

        emits: [
            'update:modelValue',
            'input',
        ],

        setup(
            props,
            {
                emit,
            }
        ) {

            return () =>
                h(
                    'div',
                    {
                        class:
                            'info-cell',
                    },
                    [

                        h(
                            'label',
                            {},
                            props.label
                        ),


                        props.editing

                            ? h(
                                'input',
                                {
                                    class:
                                        'edit-input',

                                    type:
                                        props.type,

                                    value:
                                        props.modelValue
                                        ??
                                        '',

                                    placeholder:
                                        props.placeholder,

                                    min:
                                        props.type ===
                                        'number'
                                            ? '1'
                                            : undefined,

                                    onInput:
                                        (
                                            event
                                        ) => {

                                            emit(
                                                'update:modelValue',
                                                event.target.value
                                            )

                                            emit(
                                                'input'
                                            )

                                        },
                                }
                            )

                            : h(
                                'p',
                                {},
                                (
                                    props.value ===
                                    null
                                    ||
                                    props.value ===
                                    undefined
                                    ||
                                    props.value ===
                                    ''
                                )
                                    ? '—'
                                    : String(
                                        props.value
                                    )
                            ),


                        props.error

                            ? h(
                                'span',
                                {
                                    class:
                                        'field-error',
                                },
                                props.error
                            )

                            : null,

                    ]
                )

        },

    })


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const page =
    usePage()


/*
|--------------------------------------------------------------------------
| Administrator
|--------------------------------------------------------------------------
*/

const admin =
    computed(
        () =>
            page.props.admin
            ||
            {}
    )


/*
|--------------------------------------------------------------------------
| University
|--------------------------------------------------------------------------
*/

const university =
    computed(
        () =>
            page.props.university
            ||
            {}
    )


/*
|--------------------------------------------------------------------------
| Full Name
|--------------------------------------------------------------------------
*/

const fullName =
    computed(
        () => {

            const parts = [

                admin.value.first_name,

                admin.value.middle_name,

                admin.value.last_name,

            ]
                .filter(
                    Boolean
                )


            return (
                parts.join(
                    ' '
                )
                ||
                admin.value.name
                ||
                admin.value.username
                ||
                'University Administrator'
            )

        }
    )


/*
|--------------------------------------------------------------------------
| Profile Photo State
|--------------------------------------------------------------------------
*/

const photoInput =
    ref(
        null
    )


const uploadingPhoto =
    ref(
        false
    )


/*
|--------------------------------------------------------------------------
| Logout State
|--------------------------------------------------------------------------
*/

const loggingOut =
    ref(
        false
    )


const showLogoutModal =
    ref(
        false
    )


const logoutError =
    ref(
        ''
    )


/*
|--------------------------------------------------------------------------
| Notification State
|--------------------------------------------------------------------------
*/

const notification =
    reactive({

        type:
            'success',

        message:
            '',

    })


let notificationTimer =
    null


/*
|--------------------------------------------------------------------------
| University Edit State
|--------------------------------------------------------------------------
*/

const editingUniversity =
    ref(
        false
    )


const savingUniversity =
    ref(
        false
    )


const universityLogoInput =
    ref(
        null
    )


const selectedUniversityLogo =
    ref(
        null
    )


const selectedUniversityLogoPreview =
    ref(
        null
    )


/*
|--------------------------------------------------------------------------
| University Form
|--------------------------------------------------------------------------
*/

const universityForm =
    reactive({

        name:
            '',

        acronym:
            '',

        type:
            '',

        campus_type:
            '',

        email:
            '',

        contact_number:
            '',

        website:
            '',

        region:
            '',

        province:
            '',

        city:
            '',

        barangay:
            '',

        zip_code:
            '',

        complete_address:
            '',

        academic_year:
            '',

        semester:
            '',

        components:
            '',

        max_students:
            '',

    })


/*
|--------------------------------------------------------------------------
| University Errors
|--------------------------------------------------------------------------
*/

const universityErrors =
    reactive({

        name:
            '',

        acronym:
            '',

        type:
            '',

        campus_type:
            '',

        email:
            '',

        contact_number:
            '',

        website:
            '',

        region:
            '',

        province:
            '',

        city:
            '',

        barangay:
            '',

        zip_code:
            '',

        complete_address:
            '',

        academic_year:
            '',

        semester:
            '',

        components:
            '',

        max_students:
            '',

        logo:
            '',

    })


/*
|--------------------------------------------------------------------------
| Has Profile Photo
|--------------------------------------------------------------------------
*/

const hasProfilePhoto =
    computed(
        () =>
            Boolean(
                admin.value.photo
                &&
                String(
                    admin.value.photo
                )
                    .trim() !==
                    ''
            )
    )


/*
|--------------------------------------------------------------------------
| Profile Photo Button Text
|--------------------------------------------------------------------------
*/

const photoButtonText =
    computed(
        () => {

            if (
                uploadingPhoto.value
            ) {

                return 'UPLOADING...'

            }


            return hasProfilePhoto.value
                ? 'EDIT PHOTO'
                : 'UPLOAD PHOTO'

        }
    )


/*
|--------------------------------------------------------------------------
| Profile Photo
|--------------------------------------------------------------------------
*/

const profilePhoto =
    computed(
        () => {

            if (
                admin.value.photo_url
            ) {

                return admin.value.photo_url

            }


            if (
                admin.value.photo
            ) {

                const photo =
                    String(
                        admin.value.photo
                    )


                if (
                    photo.startsWith(
                        'http://'
                    )
                    ||
                    photo.startsWith(
                        'https://'
                    )
                    ||
                    photo.startsWith(
                        '/storage/'
                    )
                ) {

                    return photo

                }


                return `/storage/${photo}`

            }


            return '/images/default-avatar.png'

        }
    )


/*
|--------------------------------------------------------------------------
| University Logo
|--------------------------------------------------------------------------
*/

const universityLogo =
    computed(
        () => {

            if (
                university.value.logo_url
            ) {

                return university.value.logo_url

            }


            if (
                university.value.logo
            ) {

                const logo =
                    String(
                        university.value.logo
                    )


                if (
                    logo.startsWith(
                        'http://'
                    )
                    ||
                    logo.startsWith(
                        'https://'
                    )
                    ||
                    logo.startsWith(
                        '/storage/'
                    )
                ) {

                    return logo

                }


                return `/storage/${logo}`

            }


            return '/images/default-university.png'

        }
    )


/*
|--------------------------------------------------------------------------
| University Logo Preview
|--------------------------------------------------------------------------
*/

const universityLogoPreview =
    computed(
        () => {

            if (
                editingUniversity.value
                &&
                selectedUniversityLogoPreview.value
            ) {

                return selectedUniversityLogoPreview.value

            }


            return universityLogo.value

        }
    )


/*
|--------------------------------------------------------------------------
| University Status
|--------------------------------------------------------------------------
*/

const statusText =
    computed(
        () =>
            String(
                university.value.status
                ||
                'ACTIVE'
            )
                .toUpperCase()
    )


/*
|--------------------------------------------------------------------------
| NSTP Components
|--------------------------------------------------------------------------
*/

const formattedComponents =
    computed(
        () => {

            const components =
                university.value.components


            if (
                !components
            ) {

                return ''

            }


            return Array.isArray(
                components
            )
                ? components.join(
                    ', '
                )
                : String(
                    components
                )

        }
    )


/*
|--------------------------------------------------------------------------
| Website
|--------------------------------------------------------------------------
*/

const website =
    computed(
        () => {

            if (
                !university.value.website
            ) {

                return '#'

            }


            const value =
                String(
                    university.value.website
                )


            return (
                value.startsWith(
                    'http://'
                )
                ||
                value.startsWith(
                    'https://'
                )
            )
                ? value
                : `https://${value}`

        }
    )


/*
|--------------------------------------------------------------------------
| Show Notification
|--------------------------------------------------------------------------
*/

function notify(
    type,
    message
) {

    notification.type =
        type

    notification.message =
        message


    if (
        notificationTimer
    ) {

        clearTimeout(
            notificationTimer
        )

    }


    notificationTimer =
        window.setTimeout(
            () => {

                notification.message =
                    ''

                notificationTimer =
                    null

            },
            4500
        )

}


/*
|--------------------------------------------------------------------------
| Clear Notification
|--------------------------------------------------------------------------
*/

function clearNotification() {

    notification.message =
        ''


    if (
        notificationTimer
    ) {

        clearTimeout(
            notificationTimer
        )

        notificationTimer =
            null

    }

}


/*
|--------------------------------------------------------------------------
| Populate University Form
|--------------------------------------------------------------------------
*/

function populateUniversityForm() {

    universityForm.name =
        university.value.name
        ||
        ''

    universityForm.acronym =
        university.value.acronym
        ||
        ''

    universityForm.type =
        university.value.type
        ||
        ''

    universityForm.campus_type =
        university.value.campus_type
        ||
        ''

    universityForm.email =
        university.value.email
        ||
        ''

    universityForm.contact_number =
        university.value.contact_number
        ||
        ''

    universityForm.website =
        university.value.website
        ||
        ''

    universityForm.region =
        university.value.region
        ||
        ''

    universityForm.province =
        university.value.province
        ||
        ''

    universityForm.city =
        university.value.city
        ||
        ''

    universityForm.barangay =
        university.value.barangay
        ||
        ''

    universityForm.zip_code =
        university.value.zip_code
        ||
        ''

    universityForm.complete_address =
        university.value.complete_address
        ||
        ''

    universityForm.academic_year =
        university.value.academic_year
        ||
        ''

    universityForm.semester =
        university.value.semester
        ||
        ''


    universityForm.components =
        Array.isArray(
            university.value.components
        )
            ? university.value.components
                .join(
                    ', '
                )
            : (
                university.value.components
                ||
                ''
            )


    universityForm.max_students =
        university.value.max_students
        ||
        ''

}


/*
|--------------------------------------------------------------------------
| Clear University Errors
|--------------------------------------------------------------------------
*/

function clearAllUniversityErrors() {

    Object.keys(
        universityErrors
    )
        .forEach(
            (
                key
            ) => {

                universityErrors[
                    key
                ] = ''

            }
        )

}


/*
|--------------------------------------------------------------------------
| Clear One University Error
|--------------------------------------------------------------------------
*/

function clearUniversityError(
    field
) {

    if (
        Object.prototype
            .hasOwnProperty
            .call(
                universityErrors,
                field
            )
    ) {

        universityErrors[
            field
        ] = ''

    }

}


/*
|--------------------------------------------------------------------------
| Toggle University Edit
|--------------------------------------------------------------------------
*/

function toggleUniversityEdit() {

    if (
        savingUniversity.value
    ) {

        return

    }


    if (
        editingUniversity.value
    ) {

        cancelUniversityEdit()

        return

    }


    populateUniversityForm()

    clearAllUniversityErrors()

    selectedUniversityLogo.value =
        null

    selectedUniversityLogoPreview.value =
        null

    editingUniversity.value =
        true

}


/*
|--------------------------------------------------------------------------
| Cancel University Edit
|--------------------------------------------------------------------------
*/

function cancelUniversityEdit() {

    if (
        savingUniversity.value
    ) {

        return

    }


    editingUniversity.value =
        false

    populateUniversityForm()

    clearAllUniversityErrors()

    selectedUniversityLogo.value =
        null


    if (
        selectedUniversityLogoPreview.value
    ) {

        URL.revokeObjectURL(
            selectedUniversityLogoPreview.value
        )

    }


    selectedUniversityLogoPreview.value =
        null


    if (
        universityLogoInput.value
    ) {

        universityLogoInput.value.value =
            ''

    }

}


/*
|--------------------------------------------------------------------------
| Open University Logo Selector
|--------------------------------------------------------------------------
*/

function openLogoSelector() {

    if (
        !editingUniversity.value
    ) {

        return

    }


    universityLogoInput.value
        ?.click()

}


/*
|--------------------------------------------------------------------------
| Select University Logo
|--------------------------------------------------------------------------
*/

function handleUniversityLogoSelected(
    event
) {

    const input =
        event.target


    const file =
        input.files?.[
            0
        ]


    if (
        !file
    ) {

        return

    }


    const allowedTypes = [

        'image/jpeg',

        'image/png',

        'image/webp',

    ]


    const maximumSize =
        2
        *
        1024
        *
        1024


    if (
        !allowedTypes.includes(
            file.type
        )
    ) {

        notify(
            'error',
            'Please select a JPG, JPEG, PNG, or WEBP university logo.'
        )

        input.value =
            ''

        return

    }


    if (
        file.size >
        maximumSize
    ) {

        notify(
            'error',
            'The university logo must not be larger than 2MB.'
        )

        input.value =
            ''

        return

    }


    if (
        selectedUniversityLogoPreview.value
    ) {

        URL.revokeObjectURL(
            selectedUniversityLogoPreview.value
        )

    }


    selectedUniversityLogo.value =
        file


    selectedUniversityLogoPreview.value =
        URL.createObjectURL(
            file
        )


    universityErrors.logo =
        ''

}


/*
|--------------------------------------------------------------------------
| Save University Information
|--------------------------------------------------------------------------
*/

function saveUniversityInformation() {

    if (
        savingUniversity.value
    ) {

        return

    }


    clearAllUniversityErrors()


    const formData =
        new FormData()


    Object.entries(
        universityForm
    )
        .forEach(
            (
                [
                    key,
                    value,
                ]
            ) => {

                formData.append(
                    key,
                    value
                    ??
                    ''
                )

            }
        )


    if (
        selectedUniversityLogo.value
    ) {

        formData.append(
            'logo',
            selectedUniversityLogo.value
        )

    }


    savingUniversity.value =
        true


    router.post(
        '/university-admin/uniadminprofile/university',
        formData,
        {
            forceFormData:
                true,

            preserveScroll:
                true,

            preserveState:
                false,


            onSuccess: () => {

                editingUniversity.value =
                    false

                selectedUniversityLogo.value =
                    null


                if (
                    selectedUniversityLogoPreview.value
                ) {

                    URL.revokeObjectURL(
                        selectedUniversityLogoPreview.value
                    )

                }


                selectedUniversityLogoPreview.value =
                    null


                if (
                    universityLogoInput.value
                ) {

                    universityLogoInput.value.value =
                        ''

                }


                notify(
                    'success',
                    'University information updated successfully.'
                )

            },


            onError: (
                errors
            ) => {

                Object.keys(
                    universityErrors
                )
                    .forEach(
                        (
                            field
                        ) => {

                            if (
                                errors[
                                    field
                                ]
                            ) {

                                universityErrors[
                                    field
                                ] =
                                    Array.isArray(
                                        errors[
                                            field
                                        ]
                                    )
                                        ? errors[
                                            field
                                        ][0]
                                        : errors[
                                            field
                                        ]

                            }

                        }
                    )


                notify(
                    'error',
                    'Please correct the highlighted university information.'
                )

            },


            onFinish: () => {

                savingUniversity.value =
                    false

            },
        }
    )

}


/*
|--------------------------------------------------------------------------
| Profile Photo Error
|--------------------------------------------------------------------------
*/

function handleProfilePhotoError(
    event
) {

    if (
        event.target.src
            .includes(
                '/images/default-avatar.png'
            )
    ) {

        return

    }


    event.target.src =
        '/images/default-avatar.png'

}


/*
|--------------------------------------------------------------------------
| University Logo Error
|--------------------------------------------------------------------------
*/

function handleUniversityLogoError(
    event
) {

    if (
        event.target.src
            .includes(
                '/images/default-university.png'
            )
    ) {

        return

    }


    event.target.src =
        '/images/default-university.png'

}


/*
|--------------------------------------------------------------------------
| Back
|--------------------------------------------------------------------------
*/

function goBack() {

    router.visit(
        '/university-admin/dashboard'
    )

}


/*
|--------------------------------------------------------------------------
| Upload Profile Photo
|--------------------------------------------------------------------------
*/

function uploadPhoto() {

    if (
        uploadingPhoto.value
    ) {

        return

    }


    photoInput.value
        ?.click()

}


/*
|--------------------------------------------------------------------------
| Profile Photo Selected
|--------------------------------------------------------------------------
*/

function handlePhotoSelected(
    event
) {

    const input =
        event.target


    const file =
        input.files?.[
            0
        ]


    if (
        !file
    ) {

        return

    }


    const allowedTypes = [

        'image/jpeg',

        'image/png',

        'image/webp',

    ]


    const maximumSize =
        2
        *
        1024
        *
        1024


    if (
        !allowedTypes.includes(
            file.type
        )
    ) {

        notify(
            'error',
            'Please select a JPG, JPEG, PNG, or WEBP image.'
        )

        input.value =
            ''

        return

    }


    if (
        file.size >
        maximumSize
    ) {

        notify(
            'error',
            'The profile photo must not be larger than 2MB.'
        )

        input.value =
            ''

        return

    }


    const formData =
        new FormData()


    formData.append(
        'photo',
        file
    )


    uploadingPhoto.value =
        true


    router.post(
        '/university-admin/uniadminprofile/photo',
        formData,
        {
            forceFormData:
                true,

            preserveScroll:
                true,

            preserveState:
                false,


            onSuccess: () => {

                notify(
                    'success',
                    'Profile photo updated successfully.'
                )

            },


            onError: (
                errors
            ) => {

                const message =
                    errors.photo

                        ? (
                            Array.isArray(
                                errors.photo
                            )

                                ? errors.photo[
                                    0
                                ]

                                : errors.photo
                        )

                        : 'Unable to upload the profile photo.'


                notify(
                    'error',
                    message
                )

            },


            onFinish: () => {

                uploadingPhoto.value =
                    false


                if (
                    photoInput.value
                ) {

                    photoInput.value.value =
                        ''

                }

            },
        }
    )

}


/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

function changePassword() {

    router.visit(
        '/university-admin/change-password'
    )

}


/*
|--------------------------------------------------------------------------
| Open Logout Confirmation
|--------------------------------------------------------------------------
|
| No window.confirm().
| No browser alert.
|
*/

function logout() {

    if (
        loggingOut.value
    ) {

        return

    }


    logoutError.value =
        ''


    showLogoutModal.value =
        true

}


/*
|--------------------------------------------------------------------------
| Close Logout Modal
|--------------------------------------------------------------------------
*/

function closeLogoutModal() {

    if (
        loggingOut.value
    ) {

        return

    }


    showLogoutModal.value =
        false


    logoutError.value =
        ''

}


/*
|--------------------------------------------------------------------------
| Confirm Logout
|--------------------------------------------------------------------------
*/

function confirmLogout() {

    if (
        loggingOut.value
    ) {

        return

    }


    loggingOut.value =
        true


    logoutError.value =
        ''


    router.post(
        '/university-admin/logout',
        {},
        {
            preserveState:
                false,

            preserveScroll:
                false,


            onError: () => {

                logoutError.value =
                    'Unable to log out right now. Please try again.'

            },


            onFinish: () => {

                loggingOut.value =
                    false

            },
        }
    )

}


/*
|--------------------------------------------------------------------------
| Escape Key
|--------------------------------------------------------------------------
*/

function handleLogoutModalKeydown(
    event
) {

    if (
        event.key ===
        'Escape'
        &&
        showLogoutModal.value
        &&
        !loggingOut.value
    ) {

        closeLogoutModal()

    }

}


/*
|--------------------------------------------------------------------------
| Lock Body Scroll
|--------------------------------------------------------------------------
*/

watch(
    showLogoutModal,
    (
        isOpen
    ) => {

        if (
            typeof document ===
            'undefined'
        ) {

            return

        }


        document.body.style.overflow =
            isOpen
                ? 'hidden'
                : ''

    }
)


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(
    () => {

        if (
            typeof window !==
            'undefined'
        ) {

            window.addEventListener(
                'keydown',
                handleLogoutModalKeydown
            )

        }

    }
)


onBeforeUnmount(
    () => {

        if (
            typeof window !==
            'undefined'
        ) {

            window.removeEventListener(
                'keydown',
                handleLogoutModalKeydown
            )

        }


        if (
            typeof document !==
            'undefined'
        ) {

            document.body.style.overflow =
                ''

        }


        if (
            notificationTimer
        ) {

            clearTimeout(
                notificationTimer
            )

        }


        if (
            selectedUniversityLogoPreview.value
        ) {

            URL.revokeObjectURL(
                selectedUniversityLogoPreview.value
            )

        }

    }
)

</script>


<style scoped>

@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.profile-page {

    --cream: #EFEBE2;
    --maroon: #54100F;
    --green: #58761C;
    --yellow: #FFBD36;
    --orange: #D99202;
    --teal: #233E47;
    --black: #000D12;
    --white: #FFFFFF;
    --gray: #BEBEBE;
    --dark: #0D171B;

    min-height: 100vh;

    background:
        var(--cream);

    color:
        var(--dark);

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

}


.profile-wrapper {

    width:
        min(
            100%,
            1400px
        );

    margin:
        0
        auto;

    padding:
        36px
        48px
        72px;

    box-sizing:
        border-box;

}


/*
|--------------------------------------------------------------------------
| BACK BUTTON
|--------------------------------------------------------------------------
*/

.back-row {

    margin-bottom:
        24px;

}


.back-btn {

    width:
        48px;

    height:
        48px;

    border:
        1px
        solid
        transparent;

    border-radius:
        50%;

    background:
        transparent;

    color:
        var(--maroon);

    display:
        grid;

    place-items:
        center;

    cursor:
        pointer;

    transition:
        .2s
        ease;

}


.back-btn:hover {

    border-color:
        rgba(
            84,
            16,
            15,
            .14
        );

    background:
        var(--white);

    transform:
        translateX(
            -3px
        );

}


/*
|--------------------------------------------------------------------------
| PROFILE HEADER
|--------------------------------------------------------------------------
*/

.profile-header {

    margin-bottom:
        26px;

}


.profile-info {

    display:
        flex;

    align-items:
        center;

    gap:
        28px;

}


.profile-photo-card,
.university-logo-card {

    background:
        var(--white);

    box-shadow:
        0
        10px
        24px
        rgba(
            0,
            13,
            18,
            .10
        );

}


.profile-photo-card {

    width:
        156px;

    height:
        156px;

    padding:
        6px;

    border-radius:
        18px;

    box-sizing:
        border-box;

    flex:
        0
        0
        156px;

}


.profile-photo,
.university-logo {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

    border-radius:
        14px;

}


.profile-kicker {

    color:
        var(--orange);

    font-size:
        12px;

    font-weight:
        800;

    letter-spacing:
        1.2px;

}


.username {

    margin:
        6px
        0
        0;

    color:
        var(--maroon);

    font-size:
        clamp(
            30px,
            4vw,
            42px
        );

    font-weight:
        800;

    line-height:
        1;

    text-transform:
        uppercase;

}


.fullname {

    margin:
        14px
        0
        7px;

    color:
        var(--teal);

    font-size:
        22px;

    font-weight:
        700;

}


.email {

    margin:
        0;

    color:
        #666666;

    font-size:
        15px;

}


.role {

    margin:
        14px
        0
        0;

    color:
        var(--green);

    font-size:
        15px;

    font-weight:
        700;

}


/*
|--------------------------------------------------------------------------
| PROFILE ACTIONS
|--------------------------------------------------------------------------
*/

.profile-actions {

    display:
        flex;

    justify-content:
        center;

    gap:
        18px;

    margin-bottom:
        34px;

}


.outline-btn,
.edit-button,
.cancel-university-button,
.save-university-button,
.logout-btn,
.logout-modal-cancel,
.logout-modal-confirm {

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        9px;

    font-weight:
        800;

    cursor:
        pointer;

    transition:
        .2s
        ease;

}


.outline-btn {

    width:
        280px;

    min-height:
        52px;

    border:
        2px
        solid
        var(--maroon);

    border-radius:
        10px;

    background:
        var(--white);

    color:
        var(--maroon);

    font-size:
        14px;

}


.outline-btn:hover:not(:disabled) {

    background:
        var(--maroon);

    color:
        var(--white);

}


button:disabled {

    opacity:
        .6;

    cursor:
        not-allowed;

}


/*
|--------------------------------------------------------------------------
| DIVIDER
|--------------------------------------------------------------------------
*/

.divider {

    height:
        2px;

    margin-bottom:
        42px;

    background:
        var(--gray);

}


/*
|--------------------------------------------------------------------------
| UNIVERSITY HEADER
|--------------------------------------------------------------------------
*/

.university-header {

    display:
        flex;

    align-items:
        flex-start;

    gap:
        30px;

    margin-bottom:
        34px;

}


.university-logo-card {

    position:
        relative;

    width:
        150px;

    height:
        150px;

    border-radius:
        18px;

    overflow:
        hidden;

    flex:
        0
        0
        150px;

}


.logo-editable {

    cursor:
        pointer;

    outline:
        3px
        solid
        var(--green);

    outline-offset:
        4px;

}


.logo-edit-overlay {

    position:
        absolute;

    inset:
        0;

    background:
        rgba(
            13,
            23,
            27,
            .70
        );

    color:
        var(--white);

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    font-size:
        11px;

    font-weight:
        800;

}


.university-information {

    min-width:
        0;

    flex:
        1;

}


.edit-row {

    display:
        flex;

    justify-content:
        flex-end;

    margin-bottom:
        20px;

}


.edit-button {

    min-height:
        46px;

    padding:
        0
        20px;

    border:
        0;

    border-radius:
        10px;

    background:
        var(--green);

    color:
        var(--white);

    font-size:
        13px;

}


.edit-button:hover:not(:disabled) {

    background:
        #476017;

}


.cancel-edit-button {

    background:
        var(--maroon);

}


.cancel-edit-button:hover:not(:disabled) {

    background:
        #3D0C0B;

}


.university-name {

    margin:
        0;

    color:
        var(--maroon);

    font-size:
        clamp(
            27px,
            3vw,
            34px
        );

    font-weight:
        800;

    line-height:
        1.2;

    text-transform:
        uppercase;

}


.meta-row {

    margin-top:
        16px;

    display:
        flex;

    flex-wrap:
        wrap;

    align-items:
        center;

    gap:
        11px;

}


.status-pill {

    padding:
        6px
        15px;

    border-radius:
        999px;

    background:
        var(--green);

    color:
        var(--white);

    font-size:
        12px;

    font-weight:
        800;

}


.meta-item {

    color:
        var(--teal);

    font-size:
        14px;

    font-weight:
        700;

}


.dot {

    color:
        var(--gray);

}


.access-code {

    margin-left:
        10px;

    color:
        var(--maroon);

    font-size:
        14px;

    font-weight:
        700;

}


.access-code strong {

    color:
        var(--orange);

}


/*
|--------------------------------------------------------------------------
| EDITING NOTICE
|--------------------------------------------------------------------------
*/

.editing-notice {

    margin-bottom:
        26px;

    padding:
        17px
        20px;

    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            .32
        );

    border-radius:
        14px;

    background:
        rgba(
            88,
            118,
            28,
            .08
        );

    color:
        var(--green);

    display:
        flex;

    align-items:
        flex-start;

    gap:
        12px;

}


.editing-notice strong {

    color:
        var(--maroon);

    font-size:
        14px;

}


.editing-notice p {

    margin:
        4px
        0
        0;

    color:
        var(--teal);

    font-size:
        12px;

}


/*
|--------------------------------------------------------------------------
| INFORMATION CARDS
|--------------------------------------------------------------------------
*/

.info-card {

    margin-bottom:
        30px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            190,
            190,
            190,
            .50
        );

    border-radius:
        18px;

    background:
        var(--white);

    box-shadow:
        0
        6px
        18px
        rgba(
            0,
            13,
            18,
            .05
        );

}


.editing-card {

    border:
        2px
        solid
        rgba(
            88,
            118,
            28,
            .50
        );

}


.card-title {

    min-height:
        62px;

    padding:
        0
        26px;

    border-bottom:
        1px
        solid
        #E7E7E7;

    background:
        #F8F6F1;

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    font-size:
        19px;

    font-weight:
        800;

}


.editing-badge {

    margin-left:
        auto;

    padding:
        5px
        11px;

    border-radius:
        999px;

    background:
        var(--green);

    color:
        var(--white);

    font-size:
        10px;

    letter-spacing:
        .5px;

}


/*
|--------------------------------------------------------------------------
| INFORMATION GRID
|--------------------------------------------------------------------------
*/

.info-grid {

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

}


.info-cell {

    min-height:
        110px;

    padding:
        20px
        22px;

    box-sizing:
        border-box;

    border-right:
        1px
        solid
        #ECECEC;

    border-bottom:
        1px
        solid
        #ECECEC;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

}


.info-cell:nth-child(3n) {

    border-right:
        none;

}


.info-cell--full {

    grid-column:
        1
        /
        -1;

    border-right:
        none;

}


.info-cell :deep(label) {

    margin-bottom:
        9px;

    color:
        var(--teal);

    font-size:
        12px;

    font-weight:
        800;

    text-transform:
        uppercase;

}


.info-cell :deep(p),
.info-cell :deep(a) {

    margin:
        0;

    color:
        var(--black);

    font-size:
        16px;

    font-weight:
        600;

    line-height:
        1.55;

}


.info-cell :deep(a) {

    color:
        var(--green);

    text-decoration:
        none;

    font-weight:
        700;

}


.info-cell :deep(a:hover) {

    text-decoration:
        underline;

}


/*
|--------------------------------------------------------------------------
| EDIT INPUTS
|--------------------------------------------------------------------------
*/

:deep(.edit-input) {

    width:
        100%;

    min-height:
        45px;

    padding:
        10px
        13px;

    box-sizing:
        border-box;

    border:
        1.8px
        solid
        var(--gray);

    border-radius:
        9px;

    outline:
        none;

    background:
        var(--white);

    color:
        var(--black);

    font:
        600
        14px
        'Plus Jakarta Sans',
        sans-serif;

    transition:
        .2s
        ease;

}


:deep(.edit-input:focus) {

    border-color:
        var(--green);

    box-shadow:
        0
        0
        0
        3px
        rgba(
            88,
            118,
            28,
            .12
        );

}


:deep(.edit-textarea) {

    min-height:
        85px;

    resize:
        vertical;

}


:deep(.field-error) {

    margin-top:
        7px;

    color:
        var(--maroon);

    font-size:
        11px;

    font-weight:
        700;

}


.multiline {

    white-space:
        pre-line;

}


/*
|--------------------------------------------------------------------------
| SAVE / LOGOUT BUTTONS
|--------------------------------------------------------------------------
*/

.university-save-wrapper,
.logout-wrapper {

    display:
        flex;

    justify-content:
        flex-end;

}


.university-save-wrapper {

    gap:
        14px;

    margin:
        8px
        0
        38px;

}


.cancel-university-button,
.save-university-button,
.logout-btn {

    width:
        220px;

    min-height:
        52px;

    border-radius:
        10px;

    font-size:
        13px;

}


.cancel-university-button {

    border:
        2px
        solid
        var(--maroon);

    background:
        var(--white);

    color:
        var(--maroon);

}


.cancel-university-button:hover:not(:disabled) {

    background:
        var(--maroon);

    color:
        var(--white);

}


.save-university-button {

    border:
        0;

    background:
        var(--green);

    color:
        var(--white);

}


.save-university-button:hover:not(:disabled) {

    background:
        #476017;

}


.logout-wrapper {

    margin-top:
        16px;

    padding-bottom:
        30px;

}


.logout-btn {

    border:
        0;

    background:
        var(--maroon);

    color:
        var(--white);

    font-size:
        15px;

    box-shadow:
        0
        8px
        20px
        rgba(
            84,
            16,
            15,
            .18
        );

}


.logout-btn:hover:not(:disabled) {

    background:
        #3D0C0B;

    transform:
        translateY(
            -2px
        );

}


.hidden-photo-input {

    display:
        none;

}


/*
|--------------------------------------------------------------------------
| NOTIFICATION
|--------------------------------------------------------------------------
*/

.app-notice {

    position:
        fixed;

    z-index:
        100000;

    top:
        22px;

    right:
        22px;

    width:
        min(
            calc(
                100%
                -
                44px
            ),
            430px
        );

    min-height:
        54px;

    padding:
        12px
        12px
        12px
        15px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .15
        );

    border-radius:
        14px;

    background:
        var(--white);

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    box-shadow:
        0
        16px
        36px
        rgba(
            0,
            13,
            18,
            .16
        );

    font-size:
        13px;

    font-weight:
        700;

}


.app-notice--success {

    color:
        var(--green);

}


.app-notice--error {

    color:
        var(--maroon);

}


.app-notice__close {

    margin-left:
        auto;

    width:
        34px;

    height:
        34px;

    border:
        0;

    border-radius:
        9px;

    background:
        transparent;

    color:
        inherit;

    display:
        grid;

    place-items:
        center;

    cursor:
        pointer;

}


/*
|--------------------------------------------------------------------------
| LOGOUT MODAL
|--------------------------------------------------------------------------
*/

.logout-modal-backdrop {

    --cream: #EFEBE2;
    --maroon: #54100F;
    --green: #58761C;
    --yellow: #FFBD36;
    --orange: #D99202;
    --teal: #233E47;
    --black: #000D12;
    --white: #FFFFFF;
    --gray: #BEBEBE;
    --dark: #0D171B;

    position:
        fixed;

    z-index:
        99999;

    inset:
        0;

    padding:
        24px;

    box-sizing:
        border-box;

    background:
        rgba(
            0,
            13,
            18,
            .68
        );

    backdrop-filter:
        blur(
            7px
        );

    -webkit-backdrop-filter:
        blur(
            7px
        );

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

}


.logout-modal-card {

    position:
        relative;

    width:
        min(
            100%,
            540px
        );

    overflow:
        hidden;

    padding:
        34px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(
            190,
            190,
            190,
            .65
        );

    border-radius:
        24px;

    background:
        var(--white);

    color:
        var(--dark);

    box-shadow:
        0
        28px
        70px
        rgba(
            0,
            13,
            18,
            .28
        );

}


.logout-modal-accent {

    position:
        absolute;

    top:
        0;

    right:
        0;

    left:
        0;

    height:
        6px;

    background:
        linear-gradient(
            90deg,
            var(--maroon),
            var(--orange),
            var(--yellow)
        );

}


.logout-modal-close {

    position:
        absolute;

    top:
        18px;

    right:
        18px;

    width:
        40px;

    height:
        40px;

    padding:
        0;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .16
        );

    border-radius:
        11px;

    background:
        var(--white);

    color:
        var(--teal);

    display:
        grid;

    place-items:
        center;

    cursor:
        pointer;

    transition:
        .2s
        ease;

}


.logout-modal-close:hover:not(:disabled) {

    border-color:
        var(--maroon);

    background:
        var(--cream);

    color:
        var(--maroon);

}


.logout-modal-icon {

    width:
        70px;

    height:
        70px;

    margin:
        8px
        auto
        18px;

    border-radius:
        20px;

    background:
        rgba(
            84,
            16,
            15,
            .08
        );

    color:
        var(--maroon);

    display:
        grid;

    place-items:
        center;

}


.logout-modal-eyebrow {

    display:
        block;

    margin-bottom:
        8px;

    color:
        var(--orange);

    font-size:
        12px;

    font-weight:
        800;

    letter-spacing:
        1.3px;

    text-align:
        center;

}


.logout-modal-card h2 {

    margin:
        0;

    color:
        var(--maroon);

    font-size:
        30px;

    font-weight:
        800;

    text-align:
        center;

}


.logout-modal-card > p {

    max-width:
        430px;

    margin:
        12px
        auto
        0;

    color:
        var(--teal);

    font-size:
        15px;

    line-height:
        1.65;

    text-align:
        center;

}


.logout-modal-account {

    margin-top:
        24px;

    padding:
        15px
        16px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .13
        );

    border-radius:
        15px;

    background:
        var(--cream);

    display:
        flex;

    align-items:
        center;

    gap:
        13px;

}


.logout-modal-account-icon {

    width:
        44px;

    height:
        44px;

    flex:
        0
        0
        44px;

    border-radius:
        12px;

    background:
        var(--teal);

    color:
        var(--white);

    display:
        grid;

    place-items:
        center;

}


.logout-modal-account-copy {

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    gap:
        3px;

}


.logout-modal-account-copy strong,
.logout-modal-account-copy span {

    overflow:
        hidden;

    text-overflow:
        ellipsis;

    white-space:
        nowrap;

}


.logout-modal-account-copy strong {

    color:
        var(--dark);

    font-size:
        14px;

    font-weight:
        800;

}


.logout-modal-account-copy span {

    color:
        var(--green);

    font-size:
        12px;

    font-weight:
        700;

}


.logout-modal-error {

    margin-top:
        14px;

    padding:
        12px
        14px;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            .20
        );

    border-radius:
        12px;

    background:
        rgba(
            84,
            16,
            15,
            .06
        );

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        flex-start;

    gap:
        9px;

    font-size:
        13px;

    font-weight:
        700;

}


.logout-modal-actions {

    margin-top:
        24px;

    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        12px;

}


.logout-modal-cancel,
.logout-modal-confirm {

    min-height:
        50px;

    padding:
        0
        16px;

    border-radius:
        12px;

    font-size:
        12px;

}


.logout-modal-cancel {

    border:
        1.5px
        solid
        var(--gray);

    background:
        var(--white);

    color:
        var(--teal);

}


.logout-modal-cancel:hover:not(:disabled) {

    border-color:
        var(--teal);

    background:
        var(--cream);

}


.logout-modal-confirm {

    border:
        1.5px
        solid
        var(--maroon);

    background:
        var(--maroon);

    color:
        var(--white);

    box-shadow:
        0
        8px
        18px
        rgba(
            84,
            16,
            15,
            .18
        );

}


.logout-modal-confirm:hover:not(:disabled) {

    background:
        #3D0C0B;

    transform:
        translateY(
            -1px
        );

}


/*
|--------------------------------------------------------------------------
| LOADING
|--------------------------------------------------------------------------
*/

.spin {

    animation:
        spin
        .8s
        linear
        infinite;

}


@keyframes spin {

    to {

        transform:
            rotate(
                360deg
            );

    }

}


/*
|--------------------------------------------------------------------------
| TRANSITIONS
|--------------------------------------------------------------------------
*/

.logout-modal-enter-active,
.logout-modal-leave-active,
.toast-enter-active,
.toast-leave-active {

    transition:
        opacity
        .2s
        ease;

}


.logout-modal-enter-from,
.logout-modal-leave-to,
.toast-enter-from,
.toast-leave-to {

    opacity:
        0;

}


.logout-modal-enter-active
.logout-modal-card,
.logout-modal-leave-active
.logout-modal-card {

    transition:
        transform
        .22s
        ease,
        opacity
        .22s
        ease;

}


.logout-modal-enter-from
.logout-modal-card,
.logout-modal-leave-to
.logout-modal-card {

    opacity:
        0;

    transform:
        translateY(
            14px
        )
        scale(
            .985
        );

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (
    max-width:
        1000px
) {

    .info-grid {

        grid-template-columns:
            1fr
            1fr;

    }


    .info-cell:nth-child(3n) {

        border-right:
            1px
            solid
            #ECECEC;

    }


    .info-cell:nth-child(2n) {

        border-right:
            none;

    }


    .university-header {

        flex-direction:
            column;

    }


    .edit-row {

        justify-content:
            flex-start;

    }

}


@media (
    max-width:
        768px
) {

    .profile-wrapper {

        padding:
            24px;

    }


    .profile-info {

        flex-direction:
            column;

        align-items:
            flex-start;

    }


    .profile-actions,
    .university-save-wrapper {

        flex-direction:
            column;

    }


    .outline-btn,
    .cancel-university-button,
    .save-university-button,
    .logout-btn {

        width:
            100%;

    }


    .logout-wrapper {

        justify-content:
            stretch;

    }

}


@media (
    max-width:
        600px
) {

    .profile-wrapper {

        padding:
            18px;

    }


    .info-grid {

        grid-template-columns:
            1fr;

    }


    .info-cell,
    .info-cell:nth-child(2n),
    .info-cell:nth-child(3n) {

        border-right:
            none;

    }


    .card-title {

        padding:
            0
            17px;

        font-size:
            16px;

    }


    .logout-modal-backdrop {

        padding:
            14px;

    }


    .logout-modal-card {

        padding:
            30px
            20px
            22px;

        border-radius:
            20px;

    }


    .logout-modal-card h2 {

        font-size:
            25px;

    }


    .logout-modal-actions {

        grid-template-columns:
            1fr;

    }

}

</style>