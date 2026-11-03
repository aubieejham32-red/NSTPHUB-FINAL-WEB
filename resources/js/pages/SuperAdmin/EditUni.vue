<template>
    <SuperAdminLayout active="universities">
        <div class="university-view-wrapper">

            <!-- BACK BUTTON -->
            <div class="navigation-row">
                <button class="back-button" @click="goBack">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                        stroke="#54100F" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="loading-wrapper">
                Loading university information...
            </div>

            <!-- Error -->
            <div v-else-if="errorMessage" class="loading-wrapper">
                {{ errorMessage }}
            </div>

            <!-- ===========================
             EDIT UNIVERSITY
        ============================ -->

            <template v-else>

                <header class="university-header">

                    <!-- Logo -->
                    <div class="logo-frame">

                        <img
                            :src="universityLogoUrl"
                            class="university-logo"
                            alt="University Logo"
                            @error="onUniversityLogoError"
                        />

                    </div>

                    <div class="branding-details">

                        <!-- Editable University Name -->
                        <input v-model="university.name" type="text" class="title-input"
                            placeholder="University Name" />

                        <!-- Upload Logo -->
                        <div class="logo-upload-wrapper">
                            <label class="upload-btn">
                                Change Logo
                                <input type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" @change="onLogoChange" hidden>
                            </label>
                        </div>

                        <div class="metadata-row">

                            <!-- Status -->
                            <select v-model="university.status" class="form-select meta-select">
                                <option value="ACTIVE">
                                    ACTIVE
                                </option>

                                <option value="INACTIVE">
                                    INACTIVE
                                </option>
                            </select>

                            <!-- Acronym -->
                            <input v-model="university.acronym" class="form-input meta-input" placeholder="Acronym">

                            <!-- University Type -->
                            <select v-model="university.type" class="form-select meta-select">
                                <option value="Public">
                                    Public
                                </option>

                                <option value="Private">
                                    Private
                                </option>
                            </select>

                            <!-- Campus Type -->
                            <select v-model="university.campus_type" class="form-select meta-select">
                                <option value="Main Campus">
                                    Main Campus
                                </option>

                                <option value="Extension">
                                    Extension
                                </option>
                            </select>

                            <!-- Access Code -->
                            <div class="access-code">
                                Access Code:
                                <strong>{{ university.access_code }}</strong>
                            </div>

                        </div>

                    </div>

                </header>

                <!-- ===========================================
               INSTITUTION INFORMATION
          ============================================ -->

                <section class="info-card">

                    <div class="card-header">
                        <span class="orange-bullet"></span>
                        <h2>Institution Information</h2>
                    </div>

                    <div class="card-grid">

                        <!-- University Name -->

                        <div class="grid-cell col-4">
                            <label>University Name</label>

                            <input v-model="university.name" class="form-input">
                        </div>

                        <!-- Acronym -->

                        <div class="grid-cell col-4">
                            <label>University Acronym</label>

                            <input v-model="university.acronym" class="form-input">
                        </div>

                        <!-- University Type -->

                        <div class="grid-cell col-4">
                            <label>University Type</label>

                            <select v-model="university.type" class="form-select">
                                <option value="Public">
                                    Public
                                </option>

                                <option value="Private">
                                    Private
                                </option>
                            </select>

                        </div>

                        <!-- Campus Type -->

                        <div class="grid-cell col-4">
                            <label>Campus Type</label>

                            <select v-model="university.campus_type" class="form-select">
                                <option value="Main Campus">
                                    Main Campus
                                </option>

                                <option value="Satellite Campus">
                                    Satellite Campus
                                </option>
                            </select>

                        </div>

                        <!-- University Email -->

                        <div class="grid-cell col-4">
                            <label>University Email</label>

                            <input type="email" v-model="university.email" class="form-input">
                        </div>

                        <!-- Contact Number -->

                        <div class="grid-cell col-4">
                            <label>Contact Number</label>

                            <input v-model="university.contact_number" class="form-input">
                        </div>

                        <!-- Website -->

                        <div class="grid-cell col-12 border-bottom-none">
                            <label>Website</label>

                            <input v-model="university.website" class="form-input">
                        </div>

                    </div>

                </section>

                <!-- CONTINUE IN PART 1A.2 -->
                <!-- ===========================================
               ADDRESS INFORMATION
          ============================================ -->

                <section class="info-card">

                    <div class="card-header">
                        <span class="orange-bullet"></span>
                        <h2>Address Information</h2>
                    </div>

                    <div class="card-grid">

                        <!-- Region -->
                        <div class="grid-cell col-4">
                            <label>Region</label>

                            <input v-model="university.region" class="form-input" placeholder="Region" />
                        </div>

                        <!-- Province -->
                        <div class="grid-cell col-4">
                            <label>Province</label>

                            <input v-model="university.province" class="form-input" placeholder="Province" />
                        </div>

                        <!-- City -->
                        <div class="grid-cell col-4">
                            <label>City</label>

                            <input v-model="university.city" class="form-input" placeholder="City" />
                        </div>

                        <!-- Barangay -->
                        <div class="grid-cell col-4">
                            <label>Barangay</label>

                            <input v-model="university.barangay" class="form-input" placeholder="Barangay" />
                        </div>

                        <!-- Zip Code -->
                        <div class="grid-cell col-4">
                            <label>Zip Code</label>

                            <input v-model="university.zip_code" class="form-input" placeholder="Zip Code" />
                        </div>

                        <!-- Complete Address -->
                        <div class="grid-cell col-4 border-bottom-none">
                            <label>Complete Address</label>

                            <textarea v-model="university.complete_address" class="form-textarea" rows="3"></textarea>
                        </div>

                    </div>

                </section>

                <!-- ===========================================
               NSTP CONFIGURATION
          ============================================ -->

                <section class="info-card">

                    <div class="card-header">
                        <span class="orange-bullet"></span>
                        <h2>NSTP Configuration</h2>
                    </div>

                    <div class="card-grid">

                        <!-- Academic Year -->
                        <div class="grid-cell col-4">
                            <label>Academic Year</label>

                            <input v-model="university.academic_year" class="form-input" placeholder="2025-2026" />
                        </div>

                        <!-- Semester -->
                        <div class="grid-cell col-4">
                            <label>Semester</label>

                            <select v-model="university.semester" class="form-select">
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        <!-- Maximum Students -->
                        <div class="grid-cell col-4">
                            <label>Maximum Students</label>

                            <input type="number" min="1" v-model="university.max_students" class="form-input" />
                        </div>

                        <!-- NSTP Components -->
                        <div class="grid-cell col-12 border-bottom-none">
                            <label>NSTP Components</label>

                            <div class="checkbox-group">

                                <label class="checkbox-item">
                                    <input type="checkbox" value="CWTS" v-model="university.components" />
                                    CWTS
                                </label>

                                <label class="checkbox-item">
                                    <input type="checkbox" value="ROTC" v-model="university.components" />
                                    ROTC
                                </label>

                                <label class="checkbox-item">
                                    <input type="checkbox" value="LTS" v-model="university.components" />
                                    LTS
                                </label>

                            </div>
                        </div>

                    </div>

                </section>

                <!-- CONTINUE IN PART 1B (University Administrator + Save/Cancel Buttons) -->
                <!-- ===========================================
               UNIVERSITY ADMINISTRATOR
          ============================================ -->

                <section class="info-card">

                    <div class="card-header">
                        <span class="orange-bullet"></span>
                        <h2>University Administrator</h2>
                    </div>

                    <div class="card-grid">

                        <!-- First Name -->
                        <div class="grid-cell col-4">
                            <label>First Name</label>

                            <input v-model="university.administrator.first_name" class="form-input"
                                placeholder="First Name" />
                        </div>

                        <!-- Middle Name -->
                        <div class="grid-cell col-4">
                            <label>Middle Name</label>

                            <input v-model="university.administrator.middle_name" class="form-input"
                                placeholder="Middle Name" />
                        </div>

                        <!-- Last Name -->
                        <div class="grid-cell col-4">
                            <label>Last Name</label>

                            <input v-model="university.administrator.last_name" class="form-input"
                                placeholder="Last Name" />
                        </div>

                        <!-- Email -->
                        <div class="grid-cell col-6">
                            <label>Email</label>

                            <input type="email" v-model="university.administrator.email" class="form-input"
                                placeholder="Email Address" />
                        </div>

                        <!-- Phone -->
                        <div class="grid-cell col-6">
                            <label>Phone Number</label>

                            <input v-model="university.administrator.phone" class="form-input"
                                placeholder="09XXXXXXXXX" />
                        </div>

                        <!-- Username -->
                        <div class="grid-cell col-12 border-bottom-none">
                            <label>Username</label>

                            <input v-model="university.administrator.username" class="form-input"
                                placeholder="Username" />
                        </div>

                    </div>

                </section>

                <!-- ===========================================
               ACTION BUTTONS
          ============================================ -->

                <div class="action-footer">

                    <button class="btn-edit" @click="updateUniversity">
                        Save Changes
                    </button>

                    <button class="btn-delete" @click="goBack">
                        Cancel
                    </button>

                </div>

            </template>

        </div>
    </SuperAdminLayout>
</template>

<script>
import axios from "axios";
import SuperAdminLayout from "@/layouts/SuperAdminLayout.vue";
import { router } from "@inertiajs/vue3";



export default {
    name: "EditUniversity",

    components: {
        SuperAdminLayout,
    },

    data() {
        return {
            loading: true,
            errorMessage: "",

            logoFile: null,
            previewLogo: null,

            university: {
                id: null,

                // Institution
                name: "",
                acronym: "",
                type: "",
                campus_type: "",
                email: "",
                contact_number: "",
                website: "",
                logo: "",
                logo_url: "",

                // Address
                region: "",
                province: "",
                city: "",
                barangay: "",
                zip_code: "",
                complete_address: "",

                // NSTP
                academic_year: "",
                semester: "",
                components: [],
                max_students: "",

                // Others
                access_code: "",
                status: "",

                // Administrator
                administrator: {
                    first_name: "",
                    middle_name: "",
                    last_name: "",
                    email: "",
                    phone: "",
                    username: "",
                },
            },
        };
    },

    computed: {
        universityId() {
            // From Inertia
            if (this.$page?.props?.id) {
                return this.$page.props.id;
            }

            // From Vue Router
            if (this.$route?.params?.id) {
                return this.$route.params.id;
            }

            // Fallback:
            /*
            URL:
             /superadmin/universities/10/edit
            
            segments:
            ["","superadmin","universities","10","edit"]
            */
            const segments = window.location.pathname.split("/");

            return segments[segments.length - 2];
        },

        universityLogoUrl() {
            if (this.previewLogo) {
                return this.previewLogo;
            }

            const raw = String(
                this.university.logo_url
                || this.university.logo
                || ""
            ).trim();

            if (!raw) {
                return "/images/nstphub_logo.png";
            }

            if (
                raw.startsWith("http://")
                || raw.startsWith("https://")
                || raw.startsWith("data:")
                || raw.startsWith("blob:")
                || raw.startsWith("/storage/")
                || raw.startsWith("/images/")
                || raw.startsWith("/uploads/")
            ) {
                return raw;
            }

            if (raw.startsWith("storage/")) {
                return `/${raw}`;
            }

            return `/storage/${raw.replace(/^\/+/, "")}`;
        },
    },

    mounted() {
        this.fetchUniversity();
    },

    methods: {
        goBack() {
            window.history.back();
        },

        onLogoChange(event) {
            const file = event.target.files?.[0];

            if (!file) {
                return;
            }

            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp",
            ];

            if (!allowedTypes.includes(file.type)) {
                alert("Please upload a JPG, JPEG, PNG, or WEBP image.");
                event.target.value = "";
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert("University logo must not exceed 2MB.");
                event.target.value = "";
                return;
            }

            if (this.previewLogo?.startsWith("blob:")) {
                URL.revokeObjectURL(this.previewLogo);
            }

            this.logoFile = file;
            this.previewLogo = URL.createObjectURL(file);
        },

        onUniversityLogoError(event) {
            if (!event?.target) {
                return;
            }

            if (event.target.dataset.fallbackApplied === "true") {
                return;
            }

            event.target.dataset.fallbackApplied = "true";
            event.target.src = "/images/nstphub_logo.png";
        },

        async fetchUniversity() {
            this.loading = true;
            this.errorMessage = "";

            try {
                const response = await axios.get(
                    `/superadmin/university/${this.universityId}`,
                    {
                        headers: {
                            Accept: "application/json",
                        },
                        withCredentials: true,
                    }
                );

                const data = response.data;

                this.university = {
                    ...this.university,
                    ...data,
                    administrator: data.administrator || {
                        first_name: "",
                        middle_name: "",
                        last_name: "",
                        email: "",
                        phone: "",
                        username: "",
                    },
                };
            } catch (error) {
                console.error(error);

                this.errorMessage =
                    error.response?.data?.message ||
                    "Unable to load university information.";
            } finally {
                this.loading = false;
            }
        },

        async updateUniversity() {
            try {
                const formData = new FormData();

                // ===========================
                // UNIVERSITY INFORMATION
                // ===========================

                formData.append("name", this.university.name);
                formData.append("acronym", this.university.acronym);
                formData.append("type", this.university.type);
                formData.append("campus_type", this.university.campus_type);

                formData.append("email", this.university.email);
                formData.append("contact_number", this.university.contact_number);
                formData.append("website", this.university.website);

                formData.append("region", this.university.region);
                formData.append("province", this.university.province);
                formData.append("city", this.university.city);
                formData.append("barangay", this.university.barangay);
                formData.append("zip_code", this.university.zip_code);
                formData.append(
                    "complete_address",
                    this.university.complete_address
                );

                formData.append(
                    "academic_year",
                    this.university.academic_year
                );

                formData.append(
                    "semester",
                    this.university.semester
                );

                formData.append(
                    "max_students",
                    this.university.max_students
                );

                formData.append(
                    "status",
                    this.university.status
                );

                // ===========================
                // COMPONENTS (IMPORTANT)
                // ===========================

                this.university.components.forEach(component => {
                    formData.append("components[]", component);
                });

                // ===========================
                // ADMINISTRATOR
                // ===========================

                formData.append(
                    "administrator[first_name]",
                    this.university.administrator.first_name
                );

                formData.append(
                    "administrator[middle_name]",
                    this.university.administrator.middle_name || ""
                );

                formData.append(
                    "administrator[last_name]",
                    this.university.administrator.last_name
                );

                formData.append(
                    "administrator[email]",
                    this.university.administrator.email
                );

                formData.append(
                    "administrator[phone]",
                    this.university.administrator.phone
                );

                formData.append(
                    "administrator[username]",
                    this.university.administrator.username
                );

                // ===========================
                // LOGO
                // ===========================

                if (this.logoFile) {
                    formData.append("logo", this.logoFile);
                }

                // Laravel method spoofing
                formData.append("_method", "PUT");

                const response = await axios.post(
                    `/superadmin/university/${this.universityId}`,
                    formData,
                    {
                        headers: {
                            Accept: "application/json",
                        },
                        withCredentials: true,
                    }
                );

                alert(response.data.message);

                this.logoFile = null;
                this.previewLogo = null;

                // Redirect back to the Universities page
                router.visit("/superadmin/universities");

            } catch (error) {

                console.error(error);

                if (error.response) {

                    console.log("Validation Errors:");
                    console.log(error.response.data);

                    if (error.response.data.errors) {
                        Object.entries(error.response.data.errors).forEach(([key, value]) => {
                            console.log(`${key}: ${value}`);
                        });
                    }

                    alert(error.response.data.message || "Failed to update.");
                } else {
                    alert("Server connection failed.");
                }
            }
        },
    },
};
</script>

<style scoped>
/* =========================================================
   ROOT VARIABLES
========================================================= */

:root {
    --color-bg-light: #EFEBE2;
    --color-burgundy: #54100F;
    --color-green: #58761C;
    --color-yellow: #D99202;
    --color-dark: #233E47;
    --color-border: #A3AFB3;
    --color-border-light: #D5DBDC;
    --color-white: #FFFFFF;
    --color-gray: #7D878B;
}

/* =========================================================
   GLOBAL
========================================================= */

*,
*::before,
*::after {
    box-sizing: border-box;
}

.dashboard-container {
    min-height: 100vh;
    background: #EFEBE2;
    display: flex;
    flex-direction: column;
}

.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* =========================================================
   MENU
========================================================= */

.menu-outer-wrapper {
    width: 100%;
    display: flex;
    justify-content: flex-end;
    padding: 24px 60px 0;
}

.menu-container {
    width: auto;
}

/* =========================================================
   PAGE WRAPPER
========================================================= */

.university-view-wrapper {
    width: 100%;
    max-width: 1320px;
    margin: 0 auto;
    padding: 24px 60px 60px;
    display: flex;
    flex-direction: column;
    gap: 28px;
}

/* =========================================================
   NAVIGATION
========================================================= */

.navigation-row {
    display: flex;
    align-items: center;
}

.back-button {
    width: 46px;
    height: 46px;
    border: none;
    background: transparent;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: .25s ease;
}

.back-button:hover {
    transform: translateX(-5px);
}

/* =========================================================
   LOADING
========================================================= */

.loading-wrapper {
    background: #FFFFFF;
    border: 1px solid var(--color-border-light);
    border-radius: 8px;
    padding: 50px;
    text-align: center;
    font-size: 18px;
    font-weight: 600;
    color: var(--color-burgundy);
}

/* =========================================================
   UNIVERSITY HEADER
========================================================= */

.university-header {
    display: flex;
    align-items: flex-start;
    gap: 34px;
}

.logo-frame {
    width: 160px;
    height: 160px;
    background: #FFFFFF;
    border: 1px solid #D6D6D6;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    flex-shrink: 0;
}

.university-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.branding-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.title-input {
    width: 100%;
    border: none;
    border-bottom: 2px solid var(--color-burgundy);
    background: transparent;
    color: #000D12;
    font-size: 38px;
    font-weight: 700;
    font-family: Georgia, "Times New Roman", serif;
    padding: 4px 0;
    margin-bottom: 18px;
}

.title-input:focus {
    outline: none;
}

.logo-upload-wrapper {
    margin-bottom: 16px;
}

.upload-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--color-burgundy);
    color: #FFFFFF;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: .25s;
}

.upload-btn:hover {
    background: #6A1817;
}

/* =========================================================
   HEADER META
========================================================= */

.metadata-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
}

.meta-input {
    width: 130px;
}

.meta-select {
    min-width: 160px;
}

.access-code {
    margin-left: auto;
    font-size: 16px;
    font-weight: 700;
    color: var(--color-dark);
}

/* =========================================================
   INFORMATION SECTIONS
========================================================= */

.info-sections-container {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

/* =========================================================
   CARD
========================================================= */

.info-card {
    background: #FFFFFF;
    border: 1px solid #A3AFB3;
    border-top: none;
    overflow: hidden;
}

/* =========================================================
   CARD HEADER
========================================================= */

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 18px 22px;

    background: #EFEBE2;

    border-top: 4px solid #54100F;
    border-bottom: 1px solid #A3AFB3;
}

.orange-bullet {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #D99202;
    flex-shrink: 0;
}

.card-header h2 {
    margin: 0;

    color: #D99202;

    font-family: Georgia, "Times New Roman", serif;
    font-size: 30px;
    font-weight: 700;
    line-height: 1.2;
}

/* =========================================================
   GRID CONTAINER
========================================================= */

.card-grid {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    width: 100%;
    background: #FFFFFF;
}

/* =========================================================
   GRID CELL
========================================================= */

.grid-cell {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;

    min-height: 82px;

    padding: 16px 18px;

    border-right: 1px solid #A3AFB3;
    border-bottom: 1px solid #A3AFB3;

    background: #FFFFFF;
}

/* =========================================================
   GRID WIDTHS
========================================================= */

.col-4 {
    grid-column: span 4;
}

.col-6 {
    grid-column: span 6;
}

.col-12 {
    grid-column: span 12;
}

/* =========================================================
   REMOVE EXTRA BORDERS
========================================================= */

.grid-cell.col-12 {
    border-right: none;
}

.col-4:nth-child(3n) {
    border-right: none;
}

.col-6:nth-child(even) {
    border-right: none;
}

.border-bottom-none {
    border-bottom: none !important;
}

/* =========================================================
   LABELS
========================================================= */

.grid-cell label {
    margin-bottom: 8px;

    color: #7D878B;

    font-family: Georgia, "Times New Roman", serif;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.2;
}

/* =========================================================
   VIEW VALUES
========================================================= */

.cell-value {
    width: 100%;

    color: #233E47;

    font-family: Georgia, "Times New Roman", serif;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.45;

    word-break: break-word;
}

.text-wrap {
    white-space: normal;
    word-break: break-word;
}

/* =========================================================
   EDITABLE FORM CONTROLS
========================================================= */

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #A3AFB3;
    border-radius: 6px;
    background: #FFFFFF;
    color: #233E47;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 15px;
    line-height: 1.4;
    transition: all .2s ease;
    box-sizing: border-box;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #54100F;
    box-shadow: 0 0 0 3px rgba(84, 16, 15, .12);
}

.form-input:hover,
.form-select:hover,
.form-textarea:hover {
    border-color: #54100F;
}

.form-input::placeholder,
.form-textarea::placeholder {
    color: #9DA5A8;
}

.form-input:disabled,
.form-select:disabled,
.form-textarea:disabled {
    background: #F6F6F6;
    color: #8E9598;
    cursor: not-allowed;
}

.form-textarea {
    resize: vertical;
    min-height: 90px;
}

/* =========================================================
   SELECT
========================================================= */

.form-select {
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    background-image:
        linear-gradient(45deg, transparent 50%, #54100F 50%),
        linear-gradient(135deg, #54100F 50%, transparent 50%);

    background-position:
        calc(100% - 18px) calc(50% - 3px),
        calc(100% - 12px) calc(50% - 3px);

    background-size: 6px 6px;
    background-repeat: no-repeat;

    padding-right: 34px;
}

/* =========================================================
   CHECKBOXES
========================================================= */

.checkbox-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
    margin-top: 6px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 10px;

    color: #233E47;
    font-size: 15px;
    font-weight: 600;

    cursor: pointer;
}

.checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* =========================================================
   STATUS
========================================================= */

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 110px;

    padding: 8px 16px;

    border-radius: 6px;

    color: #FFFFFF;

    font-size: 14px;
    font-weight: 700;

    letter-spacing: .4px;
}

.status-active {
    background: #58761C;
}

.status-inactive {
    background: #7F7F7F;
}

/* =========================================================
   BUTTONS
========================================================= */

.action-footer {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    margin-top: 32px;
}

.btn-edit,
.btn-delete {
    min-width: 180px;

    padding: 14px 28px;

    border: none;
    border-radius: 8px;

    font-size: 15px;
    font-weight: 700;

    cursor: pointer;

    transition: all .25s ease;
}

.btn-edit {
    background: #54100F;
    color: #FFFFFF;
}

.btn-edit:hover {
    background: #6C1817;
    transform: translateY(-2px);
}

.btn-delete {
    background: #9FA8AC;
    color: #FFFFFF;
}

.btn-delete:hover {
    background: #879296;
    transform: translateY(-2px);
}

/* =========================================================
   UPLOAD BUTTON
========================================================= */

.upload-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 10px 18px;

    background: #54100F;
    color: #FFFFFF;

    border-radius: 6px;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;

    transition: .25s ease;
}

.upload-btn:hover {
    background: #6C1817;
}

.logo-upload-wrapper {
    margin-bottom: 16px;
}

/* =========================================================
   INPUT SIZES
========================================================= */

.meta-input {
    width: 120px;
}

.meta-select {
    min-width: 150px;
}

.title-input {
    width: 100%;
}

/* =========================================================
   TRANSITIONS
========================================================= */

input,
select,
textarea,
button {
    transition: .2s ease;
}

/* =========================================================
   RESPONSIVE DESIGN
========================================================= */

@media (max-width: 1200px) {

    .menu-outer-wrapper {
        padding: 24px 30px 0;
    }

    .university-view-wrapper {
        padding: 24px 30px 50px;
    }

    .university-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
    }

    .branding-details {
        width: 100%;
    }

    .title-input {
        font-size: 32px;
    }

    .metadata-row {
        gap: 12px;
    }

    .access-code {
        margin-left: 0;
        width: 100%;
    }

}

@media (max-width: 992px) {

    .menu-outer-wrapper {
        padding: 20px;
    }

    .university-view-wrapper {
        padding: 20px;
    }

    .col-4,
    .col-6 {
        grid-column: span 12;
    }

    .grid-cell {
        border-right: none !important;
    }

    .card-grid {
        grid-template-columns: 1fr;
    }

    .metadata-row {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .meta-input,
    .meta-select {
        width: 100%;
    }

    .access-code {
        margin-left: 0;
        width: 100%;
    }

    .checkbox-group {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

}

@media (max-width: 768px) {

    .logo-frame {
        width: 130px;
        height: 130px;
    }

    .title-input {
        font-size: 28px;
    }

    .card-header {
        padding: 16px 18px;
    }

    .card-header h2 {
        font-size: 22px;
    }

    .grid-cell {
        padding: 14px;
        min-height: auto;
    }

    .form-input,
    .form-select,
    .form-textarea {
        font-size: 14px;
    }

    .action-footer {
        flex-direction: column;
        gap: 12px;
    }

    .btn-edit,
    .btn-delete {
        width: 100%;
        min-width: unset;
    }

}

@media (max-width: 576px) {

    .menu-outer-wrapper {
        padding: 16px;
    }

    .university-view-wrapper {
        padding: 16px;
        gap: 20px;
    }

    .logo-frame {
        width: 110px;
        height: 110px;
    }

    .title-input {
        font-size: 24px;
    }

    .card-header h2 {
        font-size: 20px;
    }

    .grid-cell {
        padding: 12px;
    }

    .metadata-row {
        gap: 10px;
    }

    .checkbox-item {
        font-size: 14px;
    }

}

@media (max-width: 480px) {

    .dashboard-container {
        overflow-x: hidden;
    }

    .university-header {
        gap: 18px;
    }

    .logo-frame {
        width: 100px;
        height: 100px;
    }

    .title-input {
        font-size: 22px;
    }

    .card-header {
        padding: 14px 16px;
    }

    .card-header h2 {
        font-size: 18px;
    }

    .grid-cell label {
        font-size: 13px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        font-size: 13px;
        padding: 9px 10px;
    }

    .btn-edit,
    .btn-delete {
        font-size: 14px;
        padding: 12px;
    }

}
</style>