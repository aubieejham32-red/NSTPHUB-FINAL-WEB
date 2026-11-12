<template>
    <SuperAdminLayout active="universities">
        <main class="add-university-page">

            <!-- =========================================================
                 HEADER
            ========================================================== -->
            <header class="page-header">
                <button
                    type="button"
                    class="back-btn"
                    aria-label="Back"
                    @click="cancelWizard"
                >
                    <ArrowLeftIcon />
                </button>

                <h1>Add University</h1>

                <div class="progress-count">
                    <span>{{ currentStepIndex + 1 }}</span>
                    <i></i>
                    <span>{{ steps.length }}</span>
                </div>
            </header>

            <!-- =========================================================
                 STEP PROGRESS
            ========================================================== -->
            <nav class="progress-rail">
                <button
                    v-for="(step, index) in steps"
                    :key="step.label"
                    type="button"
                    class="rail-step"
                    :class="{
                        active: currentStepIndex === index,
                        completed: currentStepIndex > index,
                    }"
                    :disabled="index > currentStepIndex"
                    @click="goToStep(index)"
                >
                    <span class="rail-marker">
                        <CheckIcon
                            v-if="currentStepIndex > index"
                        />

                        <component
                            :is="step.icon"
                            v-else
                        />
                    </span>

                    <span class="rail-label">
                        {{ step.label }}
                    </span>
                </button>
            </nav>

            <!-- =========================================================
                 FORM
            ========================================================== -->
            <section class="form-sheet">
                <form @submit.prevent>

                    <!-- =================================================
                         STEP 1 — INSTITUTION
                    ================================================== -->
                    <section
                        v-if="currentStepIndex === 0"
                        class="form-step"
                    >
                        <div class="section-heading">
                            <span class="section-icon">
                                <BuildingLibraryIcon />
                            </span>

                            <h2>Institution Information</h2>
                        </div>

                        <div class="form-grid">

                            <!-- University Name -->
                            <div class="field span-8">
                                <label>University Name</label>

                                <div class="input-control">
                                    <BuildingOffice2Icon />

                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Surigao del Norte State University"
                                    />
                                </div>
                            </div>

                            <!-- Acronym -->
                            <div class="field span-4">
                                <label>University Acronym</label>

                                <div class="input-control">
                                    <IdentificationIcon />

                                    <input
                                        v-model="form.acronym"
                                        type="text"
                                        placeholder="SNSU"
                                    />
                                </div>
                            </div>

                            <!-- Type -->
                            <div class="field span-12">
                                <label>University Type</label>

                                <div class="type-selector">
                                    <label
                                        class="type-choice"
                                        :class="{
                                            selected: form.type === 'Public',
                                        }"
                                    >
                                        <input
                                            v-model="form.type"
                                            type="radio"
                                            name="university_type"
                                            value="Public"
                                        />

                                        <BuildingLibraryIcon />

                                        <span>Public</span>

                                        <CheckCircleIcon
                                            v-if="form.type === 'Public'"
                                            class="choice-check"
                                        />
                                    </label>

                                    <label
                                        class="type-choice"
                                        :class="{
                                            selected: form.type === 'Private',
                                        }"
                                    >
                                        <input
                                            v-model="form.type"
                                            type="radio"
                                            name="university_type"
                                            value="Private"
                                        />

                                        <AcademicCapIcon />

                                        <span>Private</span>

                                        <CheckCircleIcon
                                            v-if="form.type === 'Private'"
                                            class="choice-check"
                                        />
                                    </label>
                                </div>
                            </div>

                            <!-- Campus -->
                            <div class="field span-6">
                                <label>Campus Type</label>

                                <div class="input-control">
                                    <MapPinIcon />

                                    <select v-model="form.campusType">
                                        <option value="Main Campus">
                                            Main Campus
                                        </option>

                                        <option value="Extension">
                                            Extension
                                        </option>
                                    </select>

                                    <ChevronDownIcon class="select-arrow" />
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="field span-6">
                                <label>University Email</label>

                                <div class="input-control">
                                    <EnvelopeIcon />

                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="university@snsu.edu.ph"
                                    />
                                </div>
                            </div>

                            <!-- Contact -->
                            <div class="field span-6">
                                <label>Contact Number</label>

                                <div class="input-control">
                                    <PhoneIcon />

                                    <input
                                        v-model="form.contactNumber"
                                        type="text"
                                        placeholder="09865789098"
                                    />
                                </div>
                            </div>

                            <!-- Website -->
                            <div class="field span-6">
                                <label>Website</label>

                                <div class="input-control">
                                    <GlobeAltIcon />

                                    <input
                                        v-model="form.website"
                                        type="text"
                                        placeholder="www.snsu.edu.ph"
                                    />
                                </div>
                            </div>

                            <!-- Logo -->
                            <div class="field span-12">
                                <label>University Logo</label>

                                <div
                                    class="logo-upload"
                                    :class="{
                                        dragging: isDragging,
                                        hasLogo: !!form.logoUrl,
                                    }"
                                    @click="triggerLogoInput"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleLogoDrop"
                                >
                                    <input
                                        ref="logoFileInput"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                        class="hidden-file"
                                        @change="handleLogoUpload"
                                    />

                                    <template v-if="!form.logoUrl">
                                        <span class="upload-icon">
                                            <CloudArrowUpIcon />
                                        </span>

                                        <span class="upload-label">
                                            Select University Logo
                                        </span>

                                        <span class="upload-action">
                                            <PlusIcon />
                                        </span>
                                    </template>

                                    <template v-else>
                                        <div class="logo-preview-shell">
                                            <img
                                                :src="form.logoUrl"
                                                alt="University Logo"
                                                class="logo-preview"
                                                @click.stop
                                            />
                                        </div>

                                        <span class="upload-label">
                                            University Logo
                                        </span>

                                        <button
                                            type="button"
                                            class="remove-logo"
                                            aria-label="Remove logo"
                                            @click.stop="removeLogo"
                                        >
                                            <TrashIcon />
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="form-footer footer-end">
                            <button
                                type="button"
                                class="primary-btn"
                                @click="goToNextStep"
                            >
                                Next
                                <ArrowRightIcon />
                            </button>
                        </div>
                    </section>

                    <!-- =================================================
                         STEP 2 — ADDRESS
                    ================================================== -->
                    <section
                        v-if="currentStepIndex === 1"
                        class="form-step"
                    >
                        <div class="section-heading">
                            <span class="section-icon">
                                <MapPinIcon />
                            </span>

                            <h2>Address Information</h2>
                        </div>

                        <div class="form-grid">
                            <div class="field span-6">
                                <label>Region</label>

                                <div class="input-control">
                                    <MapIcon />

                                    <input
                                        v-model="form.region"
                                        type="text"
                                        placeholder="CARAGA"
                                    />
                                </div>
                            </div>

                            <div class="field span-6">
                                <label>Province</label>

                                <div class="input-control">
                                    <MapPinIcon />

                                    <input
                                        v-model="form.province"
                                        type="text"
                                        placeholder="Surigao del Norte"
                                    />
                                </div>
                            </div>

                            <div class="field span-6">
                                <label>City</label>

                                <div class="input-control">
                                    <BuildingOfficeIcon />

                                    <input
                                        v-model="form.city"
                                        type="text"
                                        placeholder="Surigao City"
                                    />
                                </div>
                            </div>

                            <div class="field span-6">
                                <label>Barangay</label>

                                <div class="input-control">
                                    <HomeModernIcon />

                                    <input
                                        v-model="form.barangay"
                                        type="text"
                                        placeholder="Taft"
                                    />
                                </div>
                            </div>

                            <div class="field span-4">
                                <label>ZIP Code</label>

                                <div class="input-control">
                                    <HashtagIcon />

                                    <input
                                        v-model="form.zipCode"
                                        type="text"
                                        placeholder="8400"
                                    />
                                </div>
                            </div>

                            <div class="field span-8">
                                <label>Complete Address</label>

                                <div class="input-control">
                                    <MapPinIcon />

                                    <input
                                        v-model="form.completeAddress"
                                        type="text"
                                        placeholder="Complete campus address"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="form-footer">
                            <button
                                type="button"
                                class="secondary-btn"
                                @click="prevStep"
                            >
                                <ArrowLeftIcon />
                                Back
                            </button>

                            <button
                                type="button"
                                class="primary-btn"
                                @click="goToNextStep"
                            >
                                Next
                                <ArrowRightIcon />
                            </button>
                        </div>
                    </section>

                    <!-- =================================================
                         STEP 3 — CONFIGURATION
                    ================================================== -->
                    <section
                        v-if="currentStepIndex === 2"
                        class="form-step"
                    >
                        <div class="section-heading">
                            <span class="section-icon">
                                <AdjustmentsHorizontalIcon />
                            </span>

                            <h2>NSTP Configuration</h2>
                        </div>

                        <div class="form-grid">
                            <div class="field span-6">
                                <label>Academic Year</label>

                                <div class="input-control">
                                    <CalendarDaysIcon />

                                    <select v-model="form.academicYear">
                                        <option value="2026-2027">
                                            2026-2027
                                        </option>

                                        <option value="2027-2028">
                                            2027-2028
                                        </option>
                                    </select>

                                    <ChevronDownIcon class="select-arrow" />
                                </div>
                            </div>

                            <div class="field span-6">
                                <label>Semester</label>

                                <div class="input-control">
                                    <CalendarIcon />

                                    <select v-model="form.semester">
                                        <option value="1st Semester">
                                            1st Semester
                                        </option>

                                        <option value="2nd Semester">
                                            2nd Semester
                                        </option>
                                    </select>

                                    <ChevronDownIcon class="select-arrow" />
                                </div>
                            </div>

                            <div class="field span-12">
                                <label>NSTP Components</label>

                                <div class="component-selector">
                                    <label
                                        class="component-choice"
                                        :class="{
                                            selected:
                                                form.components.includes('CWTS'),
                                        }"
                                    >
                                        <input
                                            v-model="form.components"
                                            type="checkbox"
                                            value="CWTS"
                                        />

                                        <UserGroupIcon />

                                        <span>CWTS</span>

                                        <CheckIcon
                                            v-if="
                                                form.components.includes('CWTS')
                                            "
                                            class="component-check"
                                        />
                                    </label>

                                    <label
                                        class="component-choice"
                                        :class="{
                                            selected:
                                                form.components.includes('ROTC'),
                                        }"
                                    >
                                        <input
                                            v-model="form.components"
                                            type="checkbox"
                                            value="ROTC"
                                        />

                                        <ShieldCheckIcon />

                                        <span>ROTC</span>

                                        <CheckIcon
                                            v-if="
                                                form.components.includes('ROTC')
                                            "
                                            class="component-check"
                                        />
                                    </label>

                                    <label
                                        class="component-choice"
                                        :class="{
                                            selected:
                                                form.components.includes('LTS'),
                                        }"
                                    >
                                        <input
                                            v-model="form.components"
                                            type="checkbox"
                                            value="LTS"
                                        />

                                        <BookOpenIcon />

                                        <span>LTS</span>

                                        <CheckIcon
                                            v-if="
                                                form.components.includes('LTS')
                                            "
                                            class="component-check"
                                        />
                                    </label>
                                </div>
                            </div>

                            <div class="field span-12">
                                <label>Maximum Students</label>

                                <div class="input-control">
                                    <UsersIcon />

                                    <input
                                        v-model="form.maxStudents"
                                        type="number"
                                        min="1"
                                        placeholder="1500"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="form-footer">
                            <button
                                type="button"
                                class="secondary-btn"
                                @click="prevStep"
                            >
                                <ArrowLeftIcon />
                                Back
                            </button>

                            <button
                                type="button"
                                class="primary-btn"
                                @click="goToNextStep"
                            >
                                Next
                                <ArrowRightIcon />
                            </button>
                        </div>
                    </section>

                    <!-- =================================================
                         STEP 4 — ADMINISTRATOR
                    ================================================== -->
                    <section
                        v-if="currentStepIndex === 3"
                        class="form-step"
                    >
                        <div class="section-heading">
                            <span class="section-icon">
                                <UserCircleIcon />
                            </span>

                            <h2>University Administrator</h2>
                        </div>

                        <div class="form-grid">
                            <div class="field span-4">
                                <label>First Name</label>

                                <div class="input-control">
                                    <UserIcon />

                                    <input
                                        v-model="form.adminFirstName"
                                        type="text"
                                        placeholder="Carmen"
                                    />
                                </div>
                            </div>

                            <div class="field span-4">
                                <label>Middle Name</label>

                                <div class="input-control">
                                    <UserIcon />

                                    <input
                                        v-model="form.adminMiddleName"
                                        type="text"
                                        placeholder="Park"
                                    />
                                </div>
                            </div>

                            <div class="field span-4">
                                <label>Last Name</label>

                                <div class="input-control">
                                    <UserIcon />

                                    <input
                                        v-model="form.adminLastName"
                                        type="text"
                                        placeholder="Yang"
                                    />
                                </div>
                            </div>

                            <div class="field span-7">
                                <label>Email</label>

                                <div class="input-control">
                                    <EnvelopeIcon />

                                    <input
                                        v-model="form.adminEmail"
                                        type="email"
                                        placeholder="carmenyang@snsu.edu.ph"
                                    />
                                </div>
                            </div>

                            <div class="field span-5">
                                <label>Phone Number</label>

                                <div class="input-control">
                                    <PhoneIcon />

                                    <input
                                        v-model="form.adminPhone"
                                        type="text"
                                        placeholder="09973886476"
                                    />
                                </div>
                            </div>

                            <div class="field span-12">
                                <label>Username</label>

                                <div class="input-control">
                                    <AtSymbolIcon />

                                    <input
                                        v-model="form.adminUsername"
                                        type="text"
                                        placeholder="carmenyang"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="form-footer">
                            <button
                                type="button"
                                class="secondary-btn"
                                @click="prevStep"
                            >
                                <ArrowLeftIcon />
                                Back
                            </button>

                            <button
                                type="button"
                                class="primary-btn"
                                @click="goToNextStep"
                            >
                                Next
                                <ArrowRightIcon />
                            </button>
                        </div>
                    </section>

                    <!-- =================================================
                         STEP 5 — REVIEW
                    ================================================== -->
                    <section
                        v-if="currentStepIndex === 4"
                        class="form-step review-step"
                    >
                        <div class="section-heading">
                            <span class="section-icon">
                                <ClipboardDocumentCheckIcon />
                            </span>

                            <h2>Review Information</h2>
                        </div>

                        <div class="review-sections">

                            <!-- Institution -->
                            <article class="review-section">
                                <div class="review-title">
                                    <BuildingLibraryIcon />
                                    <span>Institution</span>
                                </div>

                                <div class="review-grid">
                                    <ReviewItem
                                        label="University Name"
                                        :value="form.name"
                                    />

                                    <ReviewItem
                                        label="Acronym"
                                        :value="form.acronym"
                                    />

                                    <ReviewItem
                                        label="Type"
                                        :value="form.type"
                                    />

                                    <ReviewItem
                                        label="Campus"
                                        :value="form.campusType"
                                    />

                                    <ReviewItem
                                        label="Email"
                                        :value="form.email"
                                    />

                                    <ReviewItem
                                        label="Contact"
                                        :value="form.contactNumber"
                                    />

                                    <ReviewItem
                                        label="Website"
                                        :value="form.website"
                                    />

                                    <div class="review-item">
                                        <span>Logo</span>

                                        <img
                                            v-if="form.logoUrl"
                                            :src="form.logoUrl"
                                            class="review-logo"
                                            alt="University Logo"
                                        />

                                        <strong v-else>—</strong>
                                    </div>
                                </div>
                            </article>

                            <!-- Address -->
                            <article class="review-section">
                                <div class="review-title">
                                    <MapPinIcon />
                                    <span>Address</span>
                                </div>

                                <div class="review-grid">
                                    <ReviewItem
                                        label="Region"
                                        :value="form.region"
                                    />

                                    <ReviewItem
                                        label="Province"
                                        :value="form.province"
                                    />

                                    <ReviewItem
                                        label="City"
                                        :value="form.city"
                                    />

                                    <ReviewItem
                                        label="Barangay"
                                        :value="form.barangay"
                                    />

                                    <ReviewItem
                                        label="ZIP Code"
                                        :value="form.zipCode"
                                    />

                                    <ReviewItem
                                        label="Complete Address"
                                        :value="form.completeAddress"
                                    />
                                </div>
                            </article>

                            <!-- Configuration -->
                            <article class="review-section">
                                <div class="review-title">
                                    <AdjustmentsHorizontalIcon />
                                    <span>Configuration</span>
                                </div>

                                <div class="review-grid">
                                    <ReviewItem
                                        label="Academic Year"
                                        :value="form.academicYear"
                                    />

                                    <ReviewItem
                                        label="Semester"
                                        :value="form.semester"
                                    />

                                    <ReviewItem
                                        label="Components"
                                        :value="form.components.join(', ')"
                                    />

                                    <ReviewItem
                                        label="Maximum Students"
                                        :value="form.maxStudents"
                                    />
                                </div>
                            </article>

                            <!-- Administrator -->
                            <article class="review-section">
                                <div class="review-title">
                                    <UserCircleIcon />
                                    <span>Administrator</span>
                                </div>

                                <div class="review-grid">
                                    <ReviewItem
                                        label="Name"
                                        :value="adminFullName"
                                    />

                                    <ReviewItem
                                        label="Email"
                                        :value="form.adminEmail"
                                    />

                                    <ReviewItem
                                        label="Phone"
                                        :value="form.adminPhone"
                                    />

                                    <ReviewItem
                                        label="Username"
                                        :value="form.adminUsername"
                                    />

                                    <ReviewItem
                                        label="Password"
                                        value="Automatically Generated"
                                    />
                                </div>
                            </article>
                        </div>

                        <div class="form-footer">
                            <button
                                type="button"
                                class="secondary-btn"
                                :disabled="submitting"
                                @click="prevStep"
                            >
                                <ArrowLeftIcon />
                                Back
                            </button>

                            <button
                                type="button"
                                class="create-btn"
                                :disabled="submitting"
                                @click="submitToDatabase"
                            >
                                <ArrowPathIcon
                                    v-if="submitting"
                                    class="spin"
                                />

                                <CheckIcon v-else />

                                {{
                                    submitting
                                        ? "Creating..."
                                        : "Create University"
                                }}
                            </button>
                        </div>
                    </section>

                    <!-- =================================================
                         STEP 6 — GENERATED
                    ================================================== -->
                    <section v-if="currentStepIndex === 5" class="success-step">
                        <div class="success-hero">
                            <span class="success-icon" aria-hidden="true">
                                <CheckIcon />
                            </span>
                            <div class="success-eyebrow">ACCOUNT SETUP COMPLETE</div>
                            <h2>{{ generatedPayload.alreadyExists ? "University Account Found" : "University Created Successfully" }}</h2>
                            <p class="success-description">
                                {{ generatedPayload.alreadyExists
                                    ? "This university is already registered. Its existing account information is shown below."
                                    : "Your university is ready to access NSTP HUB. Review the details and download the confidential credentials document." }}
                            </p>
                        </div>

                        <div class="credentials-panel">
                            <div class="credentials-panel-header">
                                <div class="credentials-panel-heading">
                                    <span class="credentials-panel-icon"><ShieldCheckIcon /></span>
                                    <div>
                                        <div class="credentials-overline">UNIVERSITY ADMINISTRATOR</div>
                                        <h3>Account Details</h3>
                                    </div>
                                </div>
                                <span class="credentials-private-tag"><LockClosedIcon /> Confidential</span>
                            </div>

                            <div class="credentials-grid">
                                <div class="credential-field">
                                    <span class="credential-label"><BuildingLibraryIcon /> University</span>
                                    <strong>{{ form.name || "—" }}</strong>
                                </div>
                                <div class="credential-field">
                                    <span class="credential-label"><KeyIcon /> Access Code</span>
                                    <strong class="access-code">{{ generatedPayload.accessCode || "—" }}</strong>
                                </div>
                                <div class="credential-field">
                                    <span class="credential-label"><EnvelopeIcon /> Administrator Email</span>
                                    <strong>{{ form.adminEmail || "—" }}</strong>
                                </div>
                                <div class="credential-field">
                                    <span class="credential-label"><UserCircleIcon /> Username</span>
                                    <strong>{{ form.adminUsername || "—" }}</strong>
                                </div>
                                <div class="credential-field credential-field-wide">
                                    <span class="credential-label"><LinkIcon /> Sign-In Portal</span>
                                    <a
                                        v-if="generatedPayload.portalUrl"
                                        :href="generatedPayload.portalUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="credential-link"
                                    >{{ generatedPayload.portalUrl }}</a>
                                    <strong v-else>—</strong>
                                </div>
                            </div>

                            <div class="credentials-security-note">
                                <span class="credentials-note-icon"><LockClosedIcon /></span>
                                <p>
                                    <strong>Temporary password is in the PDF.</strong>
                                    For security, the password is not displayed on this page.
                                    Give the downloaded document only to the authorized university administrator.
                                </p>
                            </div>
                        </div>

                        <div class="success-actions">
                            <button
                                type="button"
                                class="download-credentials-btn"
                                @click="downloadPDF"
                            >
                                <DocumentArrowDownIcon />
                                Download Credentials PDF
                            </button>
                            <button
                                type="button"
                                class="outline-btn"
                                :disabled="sendingEmail"
                                @click="sendEmailNotification"
                            >
                                <ArrowPathIcon v-if="sendingEmail" class="spin" />
                                <EnvelopeIcon v-else />
                                {{ sendingEmail ? "Sending..." : "Send Email" }}
                            </button>
                            <button
                                type="button"
                                class="outline-btn"
                                :disabled="regeneratingPassword"
                                @click="resetUniversityAdminPassword"
                                title="Create a new password and download the updated PDF"
                            >
                                <KeyIcon />
                                {{ regeneratingPassword ? "Regenerating..." : "Reset Password & PDF" }}
                            </button>
                            <button type="button" class="finish-credentials-btn" @click="finishWizard">
                                Done <ArrowRightIcon />
                            </button>
                        </div>

                        <p v-if="emailNotice" class="email-result" role="status">
                            {{ emailNotice }}
                        </p>
                        <p class="credentials-footer-note">
                            Keep the PDF in a secure location. Sending an email is optional; creating a university does not depend on email delivery.
                        </p>
                    </section>

                </form>
            </section>
        </main>
    </SuperAdminLayout>
</template>

<script setup>
import {
    computed,
    defineComponent,
    h,
    reactive,
    ref,
} from "vue";

import axios from "axios";
import { router } from "@inertiajs/vue3";

import {
    AcademicCapIcon,
    AdjustmentsHorizontalIcon,
    ArrowLeftIcon,
    ArrowPathIcon,
    ArrowRightIcon,
    AtSymbolIcon,
    BookOpenIcon,
    BuildingLibraryIcon,
    BuildingOffice2Icon,
    BuildingOfficeIcon,
    CalendarDaysIcon,
    CalendarIcon,
    CheckCircleIcon,
    CheckIcon,
    ChevronDownIcon,
    ClipboardDocumentCheckIcon,
    CloudArrowUpIcon,
    DocumentArrowDownIcon,
    EnvelopeIcon,
    GlobeAltIcon,
    HashtagIcon,
    HomeModernIcon,
    IdentificationIcon,
    KeyIcon,
    LinkIcon,
    LockClosedIcon,
    MapIcon,
    MapPinIcon,
    PhoneIcon,
    PlusIcon,
    ShieldCheckIcon,
    TrashIcon,
    UserCircleIcon,
    UserGroupIcon,
    UserIcon,
    UsersIcon,
} from "@heroicons/vue/24/outline";

import SuperAdminLayout from "@/layouts/SuperAdminLayout.vue";

axios.defaults.withCredentials = true;

/* =========================================================
   REVIEW COMPONENT
========================================================= */

const ReviewItem = defineComponent({
    name: "ReviewItem",

    props: {
        label: {
            type: String,
            required: true,
        },

        value: {
            type: [String, Number],
            default: "",
        },
    },

    setup(props) {
        return () =>
            h(
                "div",
                {
                    class: "review-item",
                },
                [
                    h(
                        "span",
                        {},
                        props.label
                    ),

                    h(
                        "strong",
                        {},
                        props.value || "—"
                    ),
                ]
            );
    },
});

/* =========================================================
   STATE
========================================================= */

const currentStepIndex = ref(0);
const isDragging = ref(false);
const logoFileInput = ref(null);
const submitting = ref(false);
const regeneratingPassword = ref(false);
const sendingEmail = ref(false);
const emailNotice = ref("");

/* =========================================================
   STEPS
========================================================= */

const steps = [
    {
        label: "Institution",
        icon: BuildingLibraryIcon,
    },
    {
        label: "Address",
        icon: MapPinIcon,
    },
    {
        label: "Configuration",
        icon: AdjustmentsHorizontalIcon,
    },
    {
        label: "Administrator",
        icon: UserCircleIcon,
    },
    {
        label: "Review",
        icon: ClipboardDocumentCheckIcon,
    },
    {
        label: "Generated",
        icon: CheckCircleIcon,
    },
];

/* =========================================================
   FORM
========================================================= */

const form = reactive({
    name: "",
    acronym: "",
    type: "Public",
    campusType: "Main Campus",
    email: "",
    contactNumber: "",
    website: "",

    logo: null,
    logoUrl: "",

    region: "",
    province: "",
    city: "",
    barangay: "",
    zipCode: "",
    completeAddress: "",

    academicYear: "2026-2027",
    semester: "1st Semester",
    components: ["CWTS"],
    maxStudents: "",

    adminFirstName: "",
    adminMiddleName: "",
    adminLastName: "",
    adminEmail: "",
    adminPhone: "",
    adminUsername: "",
});

/* =========================================================
   GENERATED DATA
========================================================= */

const generatedPayload = reactive({
    universityId: null,
    accessCode: "",
    portalUrl: "",
    pdfUrl: "",
    emailSent: false,
    alreadyExists: false,
    warning: "",
});

/* =========================================================
   ADMIN NAME
========================================================= */

const adminFullName = computed(() => {
    return [
        form.adminFirstName,
        form.adminMiddleName,
        form.adminLastName,
    ]
        .filter(Boolean)
        .join(" ")
        .replace(/\s+/g, " ")
        .trim() || "—";
});

/* =========================================================
   NAVIGATION
========================================================= */

const goToStep = (index) => {
    if (index < currentStepIndex.value) {
        currentStepIndex.value = index;
    }
};

const nextStep = () => {
    if (
        currentStepIndex.value <
        steps.length - 1
    ) {
        currentStepIndex.value++;
    }
};

const prevStep = () => {
    if (currentStepIndex.value > 0) {
        currentStepIndex.value--;
    }
};

const cancelWizard = () => {
    router.visit(
        "/superadmin/universities"
    );
};

const finishWizard = () => {
    router.visit(
        "/superadmin/universities"
    );
};

/* =========================================================
   LOGO
========================================================= */

const triggerLogoInput = () => {
    logoFileInput.value?.click();
};

const processFile = (file) => {
    if (!file) {
        return;
    }

    const allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp",
    ];

    if (
        !allowedTypes.includes(
            file.type
        )
    ) {
        window.alert(
            "Please upload a JPG, JPEG, PNG, or WEBP image."
        );

        if (logoFileInput.value) {
            logoFileInput.value.value = "";
        }

        return;
    }

    if (
        file.size >
        2 * 1024 * 1024
    ) {
        window.alert(
            "Logo must not exceed 2MB."
        );

        if (logoFileInput.value) {
            logoFileInput.value.value = "";
        }

        return;
    }

    form.logo = file;

    const reader =
        new FileReader();

    reader.onload = (event) => {
        form.logoUrl =
            event.target?.result ?? "";
    };

    reader.readAsDataURL(file);
};

const handleLogoUpload = (event) => {
    const file =
        event.target?.files?.[0];

    processFile(file);
};

const handleLogoDrop = (event) => {
    isDragging.value = false;

    const file =
        event.dataTransfer?.files?.[0];

    processFile(file);
};

const removeLogo = () => {
    form.logo = null;
    form.logoUrl = "";

    if (logoFileInput.value) {
        logoFileInput.value.value = "";
    }
};

/* =========================================================
   VALIDATION
========================================================= */

const validateStep = () => {
    switch (currentStepIndex.value) {

        case 0:
            if (
                !form.name.trim() ||
                !form.acronym.trim() ||
                !form.email.trim() ||
                !form.contactNumber.trim()
            ) {
                window.alert(
                    "Please complete all required Institution Information."
                );

                return false;
            }

            break;

        case 1:
            if (
                !form.region.trim() ||
                !form.province.trim() ||
                !form.city.trim() ||
                !form.completeAddress.trim()
            ) {
                window.alert(
                    "Please complete the Address Information."
                );

                return false;
            }

            break;

        case 2:
            if (
                form.components.length === 0 ||
                !form.maxStudents
            ) {
                window.alert(
                    "Please complete the NSTP Configuration."
                );

                return false;
            }

            break;

        case 3:
            if (
                !form.adminFirstName.trim() ||
                !form.adminLastName.trim() ||
                !form.adminEmail.trim() ||
                !form.adminUsername.trim()
            ) {
                window.alert(
                    "Please complete the Administrator Information."
                );

                return false;
            }

            break;
    }

    return true;
};

const goToNextStep = () => {
    if (validateStep()) {
        nextStep();
    }
};

/* =========================================================
   SUBMIT
========================================================= */

const submitToDatabase = async () => {
    if (submitting.value) {
        return;
    }

    submitting.value = true;

    try {
        await axios.get(
            "/sanctum/csrf-cookie"
        );

        const data =
            new FormData();

        data.append(
            "name",
            form.name
        );

        data.append(
            "acronym",
            form.acronym
        );

        data.append(
            "type",
            form.type
        );

        data.append(
            "campus_type",
            form.campusType
        );

        data.append(
            "email",
            form.email
        );

        data.append(
            "contact_number",
            form.contactNumber
        );

        data.append(
            "website",
            form.website
        );

        if (form.logo) {
            data.append(
                "logo",
                form.logo
            );
        }

        data.append(
            "region",
            form.region
        );

        data.append(
            "province",
            form.province
        );

        data.append(
            "city",
            form.city
        );

        data.append(
            "barangay",
            form.barangay
        );

        data.append(
            "zip_code",
            form.zipCode
        );

        data.append(
            "complete_address",
            form.completeAddress
        );

        data.append(
            "academic_year",
            form.academicYear
        );

        data.append(
            "semester",
            form.semester
        );

        data.append(
            "components",
            JSON.stringify(
                form.components
            )
        );

        data.append(
            "max_students",
            form.maxStudents
        );

        data.append(
            "admin_first_name",
            form.adminFirstName
        );

        data.append(
            "admin_middle_name",
            form.adminMiddleName
        );

        data.append(
            "admin_last_name",
            form.adminLastName
        );

        data.append(
            "admin_email",
            form.adminEmail
        );

        data.append(
            "admin_phone",
            form.adminPhone
        );

        data.append(
            "admin_username",
            form.adminUsername
        );

        const response =
            await axios.post(
                "/superadmin/university",
                data,
                {
                    headers: {
                        Accept:
                            "application/json",
                    },
                }
            );

        generatedPayload.universityId =
            response.data?.university_id ??
            null;

        generatedPayload.accessCode =
            response.data?.access_code ??
            "";

        generatedPayload.portalUrl =
            response.data?.portal_url ??
            "";

        generatedPayload.pdfUrl =
            response.data?.pdf_url ??
            "";

        generatedPayload.emailSent =
            Boolean(response.data?.email_sent);

        generatedPayload.alreadyExists =
            Boolean(response.data?.already_exists);

        // Email delivery errors are logged server-side and never interrupt the
        // university-created screen. A failed email is not treated as sent.
        generatedPayload.warning = "";
        emailNotice.value = "";

        // The database request completed successfully. Move immediately
        // from Review (index 4) to Generated (index 5), even when the
        // optional credentials email could not be sent.
        currentStepIndex.value = 5;
    }
    catch (error) {
        console.error(
            "University creation failed:",
            error
        );

        if (
            error.response?.data?.errors
        ) {
            const errors =
                Object.values(
                    error.response.data.errors
                )
                    .flat()
                    .join("\n");

            window.alert(errors);
        }
        else {
            const message =
                error.response?.data?.message ||
                "Failed to create university.";

            const backendError =
                error.response?.data?.error;

            window.alert(
                backendError
                    ? `${message}\n\n${backendError}`
                    : message
            );
        }
    }
    finally {
        submitting.value = false;
    }
};

/* =========================================================
   EMAIL
========================================================= */

const sendEmailNotification = async () => {
    if (!generatedPayload.universityId || sendingEmail.value) return;

    sendingEmail.value = true;
    emailNotice.value = "";

    try {
        const response = await axios.post(
            `/superadmin/university/email/${generatedPayload.universityId}`
        );

        if (response.data?.success === false) {
            throw new Error("Email was not sent.");
        }

        generatedPayload.emailSent = true;
        emailNotice.value = "Credentials email sent successfully.";
    } catch (error) {
        // Do not show raw SMTP/provider errors or interrupt the successful
        // creation flow. Never claim delivery when the request fails.
        console.error("Unable to deliver credentials email:", error);
        generatedPayload.emailSent = false;
        emailNotice.value = "Please use Download Credentials PDF to provide account access details securely.";
    } finally {
        sendingEmail.value = false;
    }
};

/* =========================================================
   RESET ADMIN PASSWORD AND DOWNLOAD UPDATED PDF
========================================================= */

const resetUniversityAdminPassword = async () => {
    const id = generatedPayload.universityId;
    if (!id || regeneratingPassword.value) return;

    if (!window.confirm(
        'Regenerate the University Administrator password? The old password will stop working. A new credentials PDF will be downloaded.'
    )) return;

    regeneratingPassword.value = true;
    try {
        const response = await axios.post(
            `/superadmin/university/${id}/credentials/reset`,
            {},
            { headers: { Accept: 'application/json' } }
        );

        generatedPayload.pdfUrl = response.data?.pdf_url
            ?? `/superadmin/university/pdf/${id}`;
        generatedPayload.warning = '';
        emailNotice.value = 'The password was regenerated. The previous password is no longer valid; use the new PDF.';
        generatedPayload.emailSent = false;

        // Server generates the PDF on request; it is not stored in public storage.
        window.location.assign(generatedPayload.pdfUrl);
    } catch (error) {
        window.alert(error.response?.data?.message ?? 'Unable to reset administrator password.');
    } finally {
        regeneratingPassword.value = false;
    }
};

/* =========================================================
   PDF
========================================================= */

const downloadPDF = () => {
    if (
        !generatedPayload.pdfUrl
    ) {
        window.alert(
            "PDF not available."
        );

        return;
    }

    const link =
        document.createElement("a");

    link.href =
        generatedPayload.pdfUrl;

    link.target = "_blank";
    link.rel = "noopener";

    document.body.appendChild(link);

    link.click();

    link.remove();
};
</script>

<style scoped>
@import url(
    "https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
);

* {
    box-sizing: border-box;
}

/* =========================================================
   PAGE
========================================================= */

.add-university-page {
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 34px 34px 48px;

    font-family:
        "Plus Jakarta Sans",
        sans-serif;

    color: #0D171B;
}

/* =========================================================
   HEADER
========================================================= */

.page-header {
    min-height: 66px;

    margin-bottom: 26px;

    display: grid;

    grid-template-columns:
        46px
        1fr
        auto;

    align-items: center;

    gap: 18px;
}

.page-header h1 {
    margin: 0;

    color: #54100F;

    font-size: 38px;
    line-height: 1;

    font-weight: 800;

    letter-spacing: -1.2px;
}

.back-btn {
    width: 44px;
    height: 44px;

    padding: 0;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.14
        );

    border-radius: 13px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #FFFFFF;

    color: #54100F;

    cursor: pointer;

    transition:
        background-color 0.18s ease,
        color 0.18s ease,
        border-color 0.18s ease,
        transform 0.18s ease;
}

.back-btn:hover {
    border-color: #54100F;

    background: #54100F;

    color: #FFFFFF;

    transform:
        translateX(-2px);
}

.back-btn svg {
    width: 21px;
    height: 21px;
}

.progress-count {
    display: flex;

    align-items: center;

    gap: 9px;

    color: #233E47;

    font-size: 13px;

    font-weight: 800;
}

.progress-count i {
    width: 32px;
    height: 1px;

    display: block;

    background: #BEBEBE;
}

/* =========================================================
   PROGRESS RAIL
========================================================= */

.progress-rail {
    position: relative;

    width: 100%;

    margin-bottom: 26px;

    padding:
        0
        12px;

    display: grid;

    grid-template-columns:
        repeat(
            6,
            minmax(
                0,
                1fr
            )
        );
}

.progress-rail::before {
    content: "";

    position: absolute;

    top: 22px;

    left:
        calc(
            8.333%
            +
            10px
        );

    right:
        calc(
            8.333%
            +
            10px
        );

    height: 1px;

    background:
        rgba(
            35,
            62,
            71,
            0.16
        );
}

.rail-step {
    position: relative;

    z-index: 1;

    min-height: 78px;

    padding:
        0
        6px;

    border: 0;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: flex-start;

    gap: 9px;

    background: transparent;

    color: #969C9E;

    font-family: inherit;

    cursor: default;
}

.rail-marker {
    width: 44px;
    height: 44px;

    flex:
        0
        0
        44px;

    border:
        1px solid
        #BEBEBE;

    border-radius: 50%;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #FFFFFF;

    color: #7D8588;

    transition:
        border-color 0.2s ease,
        background-color 0.2s ease,
        color 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;
}

.rail-marker svg {
    width: 19px;
    height: 19px;
}

.rail-label {
    color: inherit;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;
}

/* completed */

.rail-step.completed {
    color: #58761C;

    cursor: pointer;
}

.rail-step.completed
.rail-marker {
    border-color: #58761C;

    background: #58761C;

    color: #FFFFFF;
}

/* active */

.rail-step.active {
    color: #54100F;
}

.rail-step.active
.rail-marker {
    border:
        2px solid
        #54100F;

    background: #FFFFFF;

    color: #54100F;

    box-shadow:
        0
        0
        0
        6px
        rgba(
            84,
            16,
            15,
            0.055
        );

    transform:
        scale(1.05);
}

.rail-step.active::after {
    content: "";

    width: 24px;
    height: 3px;

    margin-top: -1px;

    border-radius: 999px;

    background: #D99202;
}

/* =========================================================
   FORM SHEET
========================================================= */

.form-sheet {
    border:
        1px solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    border-radius: 22px;

    overflow: hidden;

    background: #FFFFFF;

    box-shadow:
        0
        18px
        48px
        rgba(
            13,
            23,
            27,
            0.06
        );
}

.form-step {
    min-height: 610px;

    padding: 38px 42px;

    display: flex;

    flex-direction: column;
}

/* =========================================================
   SECTION HEADING
========================================================= */

.section-heading {
    margin-bottom: 32px;

    display: flex;

    align-items: center;

    gap: 12px;
}

.section-heading::after {
    content: "";

    flex: 1;

    height: 1px;

    margin-left: 10px;

    background:
        rgba(
            35,
            62,
            71,
            0.10
        );
}

.section-icon {
    width: 44px;
    height: 44px;

    flex:
        0
        0
        44px;

    border-radius: 12px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #EFEBE2;

    color: #58761C;
}

.section-icon svg {
    width: 22px;
    height: 22px;
}

.section-heading h2 {
    margin: 0;

    color: #54100F;

    font-size: 23px;

    font-weight: 800;
}

/* =========================================================
   GRID
========================================================= */

.form-grid {
    display: grid;

    grid-template-columns:
        repeat(
            12,
            minmax(
                0,
                1fr
            )
        );

    gap:
        25px
        22px;
}

.span-12 {
    grid-column: span 12;
}

.span-8 {
    grid-column: span 8;
}

.span-7 {
    grid-column: span 7;
}

.span-6 {
    grid-column: span 6;
}

.span-5 {
    grid-column: span 5;
}

.span-4 {
    grid-column: span 4;
}

/* =========================================================
   FIELDS
========================================================= */

.field {
    min-width: 0;

    display: flex;

    flex-direction: column;

    gap: 9px;
}

.field > label {
    color: #233E47;

    font-size: 14px;

    font-weight: 700;
}

.input-control {
    position: relative;
}

.input-control > svg:first-child {
    position: absolute;

    top: 50%;
    left: 15px;

    z-index: 2;

    width: 19px;
    height: 19px;

    color: #58761C;

    transform:
        translateY(-50%);

    pointer-events: none;
}

.input-control input,
.input-control select {
    width: 100%;
    height: 54px;

    padding:
        0
        46px;

    border:
        1px solid
        rgba(
            35,
            62,
            71,
            0.20
        );

    border-radius: 12px;

    outline: none;

    background: #FFFFFF;

    color: #0D171B;

    font-family: inherit;

    font-size: 15px;

    font-weight: 500;

    transition:
        border-color 0.18s ease,
        box-shadow 0.18s ease,
        background-color 0.18s ease;
}

.input-control input::placeholder {
    color: #959B9D;

    font-weight: 400;
}

.input-control input:focus,
.input-control select:focus {
    border-color: #58761C;

    box-shadow:
        0
        0
        0
        3px
        rgba(
            88,
            118,
            28,
            0.09
        );
}

.input-control select {
    appearance: none;

    cursor: pointer;
}

.select-arrow {
    position: absolute !important;

    top: 50% !important;

    right: 15px !important;
    left: auto !important;

    width: 18px !important;
    height: 18px !important;

    color: #233E47 !important;

    transform:
        translateY(-50%) !important;
}

/* =========================================================
   UNIVERSITY TYPE
========================================================= */

.type-selector {
    width: 100%;
    max-width: 500px;

    display: grid;

    grid-template-columns:
        1fr
        1fr;

    gap: 10px;
}

.type-choice {
    position: relative;

    min-height: 58px;

    padding:
        0
        16px;

    border:
        1px solid
        #BEBEBE;

    border-radius: 12px;

    display: flex;

    align-items: center;

    gap: 10px;

    background: #FFFFFF;

    color: #233E47;

    cursor: pointer;

    transition:
        border-color 0.18s ease,
        background-color 0.18s ease;
}

.type-choice input {
    position: absolute;

    opacity: 0;
}

.type-choice > svg {
    width: 21px;
    height: 21px;

    color: #233E47;
}

.type-choice span {
    font-size: 14px;

    font-weight: 700;
}

.type-choice.selected {
    border:
        2px solid
        #58761C;

    background:
        rgba(
            88,
            118,
            28,
            0.05
        );

    color: #58761C;
}

.type-choice.selected > svg {
    color: #58761C;
}

.choice-check {
    margin-left: auto;

    color: #58761C !important;
}

/* =========================================================
   LOGO UPLOAD
========================================================= */

.logo-upload {
    min-height: 116px;

    padding:
        18px
        20px;

    border:
        1.5px dashed
        #BEBEBE;

    border-radius: 14px;

    display: flex;

    align-items: center;

    gap: 14px;

    background: #EFEBE2;

    color: #233E47;

    cursor: pointer;

    transition:
        border-color 0.18s ease,
        background-color 0.18s ease;
}

.logo-upload:hover,
.logo-upload.dragging {
    border-color: #58761C;

    background:
        rgba(
            88,
            118,
            28,
            0.055
        );
}

.hidden-file {
    display: none;
}

.upload-icon {
    width: 48px;
    height: 48px;

    flex:
        0
        0
        48px;

    border-radius: 50%;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #FFFFFF;

    color: #58761C;
}

.upload-icon svg {
    width: 25px;
    height: 25px;
}

.upload-label {
    flex: 1;

    color: #233E47;

    font-size: 14px;

    font-weight: 700;
}

.upload-action {
    width: 36px;
    height: 36px;

    border-radius: 10px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #FFFFFF;

    color: #54100F;
}

.upload-action svg {
    width: 18px;
    height: 18px;
}

.logo-preview-shell {
    width: 72px;
    height: 72px;

    flex:
        0
        0
        72px;

    padding: 4px;

    border-radius: 12px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #FFFFFF;
}

.logo-preview {
    width: 100%;
    height: 100%;

    object-fit: contain;

    border-radius: 8px;
}

.remove-logo {
    width: 38px;
    height: 38px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.14
        );

    border-radius: 10px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #FFFFFF;

    color: #54100F;

    cursor: pointer;
}

.remove-logo svg {
    width: 18px;
    height: 18px;
}

/* =========================================================
   NSTP COMPONENTS
========================================================= */

.component-selector {
    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(
                0,
                1fr
            )
        );

    gap: 12px;
}

.component-choice {
    position: relative;

    min-height: 82px;

    padding:
        0
        17px;

    border:
        1px solid
        #BEBEBE;

    border-radius: 13px;

    display: flex;

    align-items: center;

    gap: 12px;

    background: #FFFFFF;

    color: #233E47;

    cursor: pointer;

    transition:
        border-color 0.18s ease,
        background-color 0.18s ease,
        color 0.18s ease;
}

.component-choice input {
    position: absolute;

    opacity: 0;
}

.component-choice > svg {
    width: 24px;
    height: 24px;
}

.component-choice span {
    font-size: 15px;

    font-weight: 800;
}

.component-choice.selected {
    border:
        2px solid
        #58761C;

    background: #58761C;

    color: #FFFFFF;
}

.component-check {
    width: 18px !important;
    height: 18px !important;

    margin-left: auto;
}

/* =========================================================
   BUTTONS
========================================================= */

.form-footer {
    margin-top: auto;

    padding-top: 36px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 12px;
}

.footer-end {
    justify-content: flex-end;
}

.primary-btn,
.secondary-btn,
.create-btn,
.outline-btn {
    min-height: 48px;

    padding:
        0
        20px;

    border-radius: 11px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    font-family: inherit;

    font-size: 14px;

    font-weight: 800;

    cursor: pointer;

    transition:
        background-color 0.18s ease,
        color 0.18s ease,
        border-color 0.18s ease,
        transform 0.18s ease;
}

.primary-btn {
    border: 0;

    background: #58761C;

    color: #FFFFFF;
}

.primary-btn:hover {
    background: #4A6418;

    transform:
        translateY(-1px);
}

.secondary-btn {
    border:
        1px solid
        #BEBEBE;

    background: #FFFFFF;

    color: #233E47;
}

.secondary-btn:hover {
    border-color: #233E47;
}

.create-btn {
    border: 0;

    background: #54100F;

    color: #FFFFFF;
}

.create-btn:hover:not(:disabled) {
    background: #6A1715;
}

.create-btn:disabled,
.secondary-btn:disabled {
    cursor: not-allowed;

    opacity: 0.55;
}

.outline-btn {
    border:
        1px solid
        #58761C;

    background: #FFFFFF;

    color: #58761C;
}

.outline-btn:hover {
    background:
        rgba(
            88,
            118,
            28,
            0.06
        );
}

.primary-btn svg,
.secondary-btn svg,
.create-btn svg,
.outline-btn svg {
    width: 18px;
    height: 18px;
}

/* =========================================================
   REVIEW
========================================================= */

.review-sections {
    display: flex;

    flex-direction: column;

    gap: 24px;
}

.review-section {
    padding-top: 20px;

    border-top:
        1px solid
        rgba(
            35,
            62,
            71,
            0.14
        );

    display: grid;

    grid-template-columns:
        180px
        1fr;

    gap: 18px;
}

.review-title {
    display: flex;

    align-items: flex-start;

    gap: 9px;

    color: #54100F;
}

.review-title svg {
    width: 20px;
    height: 20px;

    color: #58761C;
}

.review-title span {
    font-size: 14px;

    font-weight: 800;
}

.review-grid {
    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(
                0,
                1fr
            )
        );

    gap:
        20px
        22px;
}

.review-item {
    min-width: 0;

    display: flex;

    flex-direction: column;

    gap: 5px;
}

.review-item span {
    color: #72797B;

    font-size: 12px;

    font-weight: 600;
}

.review-item strong {
    overflow-wrap: anywhere;

    color: #0D171B;

    font-size: 14px;

    line-height: 1.5;

    font-weight: 700;
}

.review-logo {
    width: 56px;
    height: 56px;

    object-fit: contain;
}

/* =========================================================
   SUCCESS — CLEAN CREDENTIALS SCREEN
========================================================= */

.success-step {
    width: 100%;
    min-height: 610px;
    padding: 48px 42px 38px;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: radial-gradient(circle at 50% 0%, #f4f7ed 0, #ffffff 38%);
}

.success-hero {
    width: 100%;
    max-width: 760px;
    text-align: center;
    margin-bottom: 28px;
}

.success-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 66px;
    height: 66px;
    margin-bottom: 15px;
    border-radius: 50%;
    background: #e7f0d8;
    border: 1px solid #cfddbb;
    color: #58761c;
    box-shadow: 0 8px 24px rgba(88, 118, 28, 0.12);
}

.success-icon svg { width: 33px; height: 33px; stroke-width: 2.5; }

.success-eyebrow {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    color: #58761c;
    margin-bottom: 8px;
}

.success-step h2 {
    margin: 0 0 10px;
    color: #54100f;
    font-size: clamp(25px, 3vw, 32px);
    line-height: 1.2;
    font-weight: 800;
}

.success-description {
    max-width: 560px;
    margin: 0 auto;
    color: #617078;
    font-size: 13.5px;
    line-height: 1.7;
}

.credentials-panel {
    width: 100%;
    max-width: 845px;
    overflow: hidden;
    border: 1px solid #e3e6dd;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 15px 42px rgba(32, 49, 39, 0.075);
}

.credentials-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    padding: 22px 25px;
    background: linear-gradient(100deg, #f7f8f2, #fff);
    border-bottom: 1px solid #e6e9df;
}

.credentials-panel-heading { display: flex; align-items: center; gap: 14px; min-width: 0; }
.credentials-panel-icon {
    width: 46px; height: 46px; flex: 0 0 46px; display: inline-flex;
    align-items: center; justify-content: center; border-radius: 12px;
    background: #54100f; color: #fff;
}
.credentials-panel-icon svg { width: 23px; height: 23px; }
.credentials-overline { font-size: 9px; letter-spacing: 1.25px; font-weight: 800; color: #667d45; margin-bottom: 3px; }
.credentials-panel h3 { font-size: 19px; line-height: 1.2; font-weight: 800; color: #231d1a; margin: 0; }
.credentials-private-tag {
    display: inline-flex; align-items: center; gap: 6px; flex: none;
    padding: 7px 10px; border-radius: 100px; border: 1px solid #ead8c0;
    color: #795928; background: #fff7e9; font-size: 11px; font-weight: 800;
}
.credentials-private-tag svg { width: 14px; height: 14px; }

.credentials-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 19px 24px;
    padding: 26px 28px;
}

.credential-field { min-width: 0; display: flex; flex-direction: column; gap: 8px; }
.credential-field-wide { grid-column: 1 / -1; }
.credential-label {
    display: inline-flex; align-items: center; gap: 7px;
    color: #78818a; font-size: 11px; font-weight: 700;
}
.credential-label svg { width: 16px; height: 16px; color: #58761c; }
.credential-field strong {
    min-width: 0; overflow-wrap: anywhere; font-size: 13.5px;
    line-height: 1.5; color: #233e47; font-weight: 800;
}
.credential-field .access-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    color: #54100f; font-size: 17px; letter-spacing: 0.2px;
}
.credential-link {
    width: fit-content; max-width: 100%; overflow-wrap: anywhere;
    color: #3c611d; font-size: 13px; font-weight: 750; text-decoration: none;
}
.credential-link:hover { text-decoration: underline; }

.credentials-security-note {
    display: flex; align-items: flex-start; gap: 12px;
    margin: 0 25px 23px; padding: 14px 15px;
    border-radius: 11px; background: #f7f5ee; border: 1px solid #eee8da;
}
.credentials-note-icon { flex: 0 0 19px; color: #876316; }
.credentials-note-icon svg { display: block; width: 18px; height: 18px; }
.credentials-security-note p { margin: 0; color: #626a6e; font-size: 11.5px; line-height: 1.65; }
.credentials-security-note strong { color: #54100f; }

.success-actions {
    width: 100%; max-width: 845px; display: flex; flex-wrap: wrap;
    align-items: center; justify-content: center; gap: 10px; margin-top: 23px;
}
.success-actions button { min-height: 45px; font-size: 12px; padding: 0 15px; white-space: nowrap; }
.success-actions button:disabled { opacity: .58; cursor: not-allowed; }
.success-actions svg { width: 17px; height: 17px; }
.download-credentials-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    min-height: 45px; padding: 0 18px; border: 1px solid #58761c;
    border-radius: 11px; background: #58761c; color: #fff;
    font-family: inherit; font-weight: 800; cursor: pointer;
    transition: background .18s, transform .18s;
}
.download-credentials-btn:hover { background: #476414; transform: translateY(-1px); }
.finish-credentials-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 0 19px; border: 1px solid #54100f;
    border-radius: 11px; background: #54100f; color: #fff;
    font-family: inherit; font-weight: 800; cursor: pointer;
}
.finish-credentials-btn:hover { background: #701916; }
.email-result { margin: 14px 0 0; font-size: 12px; font-weight: 700; color: #58761c; text-align: center; }
.credentials-footer-note {
    max-width: 720px; margin: 15px auto 0; color: #88908b; text-align: center;
    font-size: 11px; line-height: 1.6;
}

/* =========================================================
   SPINNER
========================================================= */

.spin {
    animation:
        spin
        0.8s
        linear
        infinite;
}

@keyframes spin {
    to {
        transform:
            rotate(360deg);
    }
}

/* =========================================================
   FOCUS
========================================================= */

button:focus-visible,
input:focus-visible,
select:focus-visible {
    outline:
        3px solid
        rgba(
            217,
            146,
            2,
            0.30
        );

    outline-offset: 2px;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {
    .progress-rail {
        grid-template-columns:
            repeat(
                3,
                minmax(
                    0,
                    1fr
                )
            );

        row-gap: 18px;
    }

    .progress-rail::before {
        display: none;
    }

    .review-section {
        grid-template-columns: 1fr;
    }

    .review-grid {
        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );
    }
}

@media (max-width: 760px) {
    .add-university-page {
        padding:
            22px
            16px
            36px;
    }

    .page-header {
        grid-template-columns:
            44px
            1fr;
    }

    .page-header h1 {
        font-size: 29px;
    }

    .progress-count {
        display: none;
    }

    .progress-rail {
        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );
    }

    .form-step {
        min-height: auto;

        padding:
            27px
            20px;
    }

    .span-8,
    .span-7,
    .span-6,
    .span-5,
    .span-4 {
        grid-column: span 12;
    }

    .component-selector {
        grid-template-columns: 1fr;
    }

    .review-grid {
        grid-template-columns: 1fr;
    }

    .credentials-grid { grid-template-columns: 1fr; }
    .credential-field-wide { grid-column: auto; }
    .credentials-panel-header { padding: 18px; }
    .credentials-grid { padding: 20px; }
    .credentials-security-note { margin: 0 18px 18px; }
    .credentials-private-tag { font-size: 10px; }
}

@media (max-width: 520px) {
    .page-header {
        margin-bottom: 18px;
    }

    .page-header h1 {
        font-size: 27px;
    }

    .progress-rail {
        display: block;

        padding: 0;
    }

    .rail-step {
        display: none;
    }

    .rail-step.active {
        width: 100%;

        min-height: 66px;

        display: flex;

        flex-direction: row;

        justify-content: flex-start;

        padding:
            10px
            14px;

        border:
            1px solid
            rgba(
                84,
                16,
                15,
                0.12
            );

        border-radius: 14px;

        background: #FFFFFF;
    }

    .rail-step.active::after {
        display: none;
    }

    .rail-marker {
        width: 40px;
        height: 40px;

        flex-basis: 40px;
    }

    .rail-label {
        font-size: 14px;
    }

    .section-heading h2 {
        font-size: 20px;
    }

    .type-selector {
        max-width: none;

        grid-template-columns: 1fr;
    }

    .form-footer {
        align-items: stretch;

        flex-direction: column-reverse;
    }

    .form-footer button {
        width: 100%;
    }

    .success-step {
        padding:
            36px
            18px;
    }

    .success-step h2 { font-size: 24px; }
    .credentials-panel-heading { gap: 9px; }
    .credentials-panel-icon { width: 38px; height: 38px; flex-basis: 38px; }
    .credentials-panel h3 { font-size: 17px; }
    .credentials-overline { font-size: 8px; letter-spacing: .7px; }
    .credentials-private-tag { padding: 6px; }
    .credentials-private-tag svg { display: none; }

    .success-actions {
        width: 100%;

        flex-direction: column;
    }

    .success-actions button {
        width: 100%;
    }
}
</style>