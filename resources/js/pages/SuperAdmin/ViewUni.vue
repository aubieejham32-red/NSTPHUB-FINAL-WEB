<template>
  <SuperAdminLayout active="universities">
    <div class="university-view-wrapper">
      <!-- BACK -->
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

      <template v-else>
        <!-- UNIVERSITY HEADER -->
        <header class="university-header">
          <div class="logo-frame">
            <img
              :src="university.logo_url || '/images/nstphub_logo.png'"
              class="university-logo"
              alt="University Logo"
              @error="handleLogoError"
            />
          </div>

          <div class="branding-details">
            <h1 class="university-title">{{ university.name }}</h1>
            <div class="metadata-row">
              <span class="status-badge" :class="university.status === 'ACTIVE' ? 'status-active' : 'status-inactive'">
                {{ university.status }}
              </span>
              <span class="meta-item text-bold">{{ university.acronym }}</span>
              <span class="dot-separator"></span>
              <span class="meta-item">{{ university.type }}</span>
              <span class="dot-separator"></span>
              <span class="meta-item">{{ university.campus_type }}</span>
              <span class="access-code">Access Code: {{ university.access_code }}</span>
            </div>
          </div>
        </header>

        <!-- Institution Information -->
        <section class="info-card">
          <div class="card-header">
            <span class="orange-bullet"></span>
            <h2>Institution Information</h2>
          </div>
          <div class="card-grid">
            <div class="grid-cell col-4">
              <label>University Name</label>
              <div class="cell-value">{{ university.name }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>University Acronym</label>
              <div class="cell-value">{{ university.acronym }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>University Type</label>
              <div class="cell-value">{{ university.type }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>Campus Type</label>
              <div class="cell-value">{{ university.campus_type }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>University Email</label>
              <div class="cell-value">{{ university.email }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>Contact Number</label>
              <div class="cell-value">{{ university.contact_number }}</div>
            </div>
            <div class="grid-cell col-12 border-bottom-none">
              <label>Website</label>
              <div class="cell-value">{{ university.website }}</div>
            </div>
          </div>
        </section>

        <!-- Address Information -->
        <section class="info-card">
          <div class="card-header">
            <span class="orange-bullet"></span>
            <h2>Address Information</h2>
          </div>
          <div class="card-grid">
            <div class="grid-cell col-4">
              <label>Region</label>
              <div class="cell-value">{{ university.region }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>Province</label>
              <div class="cell-value">{{ university.province }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>City</label>
              <div class="cell-value">{{ university.city }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>Barangay</label>
              <div class="cell-value">{{ university.barangay }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>Zip Code</label>
              <div class="cell-value">{{ university.zip_code }}</div>
            </div>
            <div class="grid-cell col-4 border-bottom-none">
              <label>Complete Address</label>
              <div class="cell-value text-wrap">{{ university.complete_address }}</div>
            </div>
          </div>
        </section>

        <!-- NSTP Configuration -->
        <section class="info-card">
          <div class="card-header">
            <span class="orange-bullet"></span>
            <h2>NSTP Configuration</h2>
          </div>
          <div class="card-grid">
            <div class="grid-cell col-4">
              <label>Academic Year</label>
              <div class="cell-value">{{ university.academic_year }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>Semester</label>
              <div class="cell-value">{{ university.semester }}</div>
            </div>
            <div class="grid-cell col-4">
              <label>Maximum Students</label>
              <div class="cell-value">{{ university.max_students }}</div>
            </div>
            <div class="grid-cell col-12 border-bottom-none">
              <label>NSTP Components</label>
              <div class="cell-value">
                <template v-if="Array.isArray(university.components) && university.components.length">
                  {{ university.components.join(", ") }}
                </template>
                <template v-else>None</template>
              </div>
            </div>
          </div>
        </section>

        <!-- University Administrator -->
        <section class="info-card">
          <div class="card-header">
            <span class="orange-bullet"></span>
            <h2>University Administrator</h2>
          </div>

          <div class="card-grid">

            <!-- Name -->
            <div class="grid-cell col-12">
              <label>Name</label>
              <div class="cell-value">
                {{
                  [
                    university.administrator?.first_name,
                    university.administrator?.middle_name,
                    university.administrator?.last_name
                  ]
                    .filter(Boolean)
                    .join(" ")
                }}
              </div>
            </div>

            <!-- Email -->
            <div class="grid-cell col-12">
              <label>Email</label>
              <div class="cell-value">
                {{ university.administrator?.email || "-" }}
              </div>
            </div>

            <!-- Phone -->
            <div class="grid-cell col-12">
              <label>Phone Number</label>
              <div class="cell-value">
                {{ university.administrator?.phone || "-" }}
              </div>
            </div>

            <!-- Username -->
            <div class="grid-cell col-12 border-bottom-none">
              <label>Username</label>
              <div class="cell-value">
                {{ university.administrator?.username || "-" }}
              </div>
            </div>

          </div>
        </section>
      </template>
    </div>

  </SuperAdminLayout>
</template>

<script>
import axios from "axios";
import SuperAdminLayout from "@/layouts/SuperAdminLayout.vue";

export default {
  name: "ViewUni",
  components: { SuperAdminLayout },
  data() {
    return {
      loading: true,
      errorMessage: "",
      university: {
        id: null,
        name: "",
        acronym: "",
        type: "",
        campus_type: "",
        email: "",
        contact_number: "",
        website: "",
        logo: "",
        region: "",
        province: "",
        city: "",
        barangay: "",
        zip_code: "",
        complete_address: "",
        academic_year: "",
        semester: "",
        components: [],
        max_students: "",
        access_code: "",
        status: "",
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
      return this.$route?.params?.id || window.location.pathname.split("/").pop();
    }
  },
  async mounted() {
    await this.fetchUniversity();
  },
  methods: {
    goBack() {
      window.history.back();
    },
    handleLogoError(event) {
      if (!event?.target || event.target.dataset.fallbackApplied === "true") {
        return;
      }

      event.target.dataset.fallbackApplied = "true";
      event.target.src = "/images/nstphub_logo.png";
    },

    async fetchUniversity() {
      this.loading = true;
      this.errorMessage = "";
      try {
        const { data } = await axios.get(`/superadmin/university/${this.universityId}`, {
          headers: { Accept: "application/json" },
          withCredentials: true,
        });
        this.university = data;
      } catch (error) {
        console.error(error);
        this.errorMessage = error.response?.data?.message || "Unable to load university.";
      } finally {
        this.loading = false;
      }
    },

  },
};
</script>

<style scoped>
/* =========================================================
   COLOR VARIABLES
========================================================= */

:root {
  --color-bg-light: #EFEBE2;
  --color-burgundy: #54100F;
  --color-green: #58761C;
  --color-yellow: #D99202;
  --color-dark: #233E47;
  --color-border: #D5DBDC;
  --color-border-dark: #99A1A3;
  --color-white: #FFFFFF;
  --color-gray: #777777;
}

/* =========================================================
   PAGE LAYOUT
========================================================= */


.university-view-wrapper {
  width: 100%;
  max-width: 1300px;
  margin: auto;
  padding: 24px 60px 50px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 28px;
}

/* =========================================================
   BACK BUTTON
========================================================= */

.navigation-row {
  display: flex;
  align-items: center;
}

.back-button {
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: .25s;
}

.back-button:hover {
  transform: translateX(-4px);
}

/* =========================================================
   UNIVERSITY HEADER
========================================================= */

.university-header {
  display: flex;
  align-items: center;
  gap: 32px;
}

.logo-frame {
  width: 150px;
  height: 150px;
  background: white;
  border: 1px solid #CFCFCF;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.university-logo {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.branding-details {
  flex: 1;
}

.university-title {
  margin: 0;
  color: #000D12;
  font-size: 38px;
  font-weight: bold;
}

.metadata-row {
  margin-top: 15px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
}

.status-badge {
  color: white;
  padding: 7px 16px;
  border-radius: 7px;
  font-size: 14px;
  font-weight: bold;
  letter-spacing: .5px;
}

.meta-item {
  color: var(--color-dark);
  font-size: 16px;
}

.text-bold {
  font-weight: bold;
}

.dot-separator {
  width: 6px;
  height: 6px;
  background: #999;
  border-radius: 50%;
}

.access-code {
  margin-left: 15px;
  font-weight: bold;
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
  box-shadow: none;
}

.card-header {
  background: #EFEBE2;
  border-top: 4px solid #54100F;
  border-bottom: 1px solid #A3AFB3;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 20px;
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
  font-size: 30px;
  font-weight: 700;
  font-family: Georgia, "Times New Roman", serif;
}

/* =========================================================
   GRID
========================================================= */

.card-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  width: 100%;
  background: #FFFFFF;
}

.grid-cell {
  display: flex;
  flex-direction: column;
  justify-content: center;

  padding: 10px 14px;

  border-right: 1px solid #A3AFB3;
  border-bottom: 1px solid #A3AFB3;

  min-height: 58px;
  box-sizing: border-box;
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

/* remove last borders */

.grid-cell.col-12 {
  border-right: none;
}

.grid-cell:nth-child(3n) {
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
  margin-bottom: 6px;
  color: #7D878B;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 14px;
  font-weight: 400;
  text-transform: none;
  letter-spacing: 0;
}

/* =========================================================
   VALUES
========================================================= */

.cell-value {
  color: #233E47;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 16px;
  font-weight: 700;
  line-height: 1.4;
  word-break: break-word;
}

.cell-value.email {
  color: #233E47;
}

.cell-value.url {
  color: #233E47;
}

.text-wrap {
  white-space: normal;
  word-break: break-word;
}

.password-mask {
  letter-spacing: 2px;
}

/* =========================================================
   STATUS COLORS
========================================================= */

.status-active {
  background: #58761C;
}

.status-inactive {
  background: #808080;
}

/* =========================================================
   LOADING & ERROR
========================================================= */

.loading-wrapper {
  background: #FFFFFF;
  border: 1px solid #D5DBDC;
  color: #54100F;
  border-radius: 8px;
  padding: 40px;
  text-align: center;
  font-size: 18px;
  font-weight: 600;
}

/* =========================================================
   RESPONSIVE DESIGN
========================================================= */

@media (max-width: 1200px) {

  .university-view-wrapper {
    padding: 30px;
  }

  .university-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .branding-details {
    width: 100%;
  }
}

@media (max-width: 992px) {

  .university-view-wrapper {
    padding: 20px;
  }

  .col-4,
  .col-6 {
    grid-column: span 12;
    border-right: none !important;
  }

  .grid-cell {
    border-right: none;
  }

  .metadata-row {
    gap: 10px;
  }
}

@media (max-width: 768px) {

  .logo-frame {
    width: 120px;
    height: 120px;
  }

  .university-title {
    font-size: 30px;
  }

  .card-header {
    padding: 18px;
  }

  .card-header h2 {
    font-size: 20px;
  }

  .action-footer {
    flex-direction: column;
  }

  .action-footer button {
    width: 100%;
  }
}

@media (max-width: 480px) {

  .university-view-wrapper {
    padding: 15px;
  }

  .university-title {
    font-size: 24px;
  }

  .metadata-row {
    font-size: 14px;
  }

  .card-grid {
    display: block;
  }

  .grid-cell {
    border-right: none;
    border-bottom: 1px solid #DDDDDD;
  }

  .grid-cell:last-child {
    border-bottom: none;
  }

  .action-footer button {
    font-size: 14px;
    padding: 12px 20px;
  }
}

/* =========================================================
   EDIT MODE
========================================================= */

.form-input,
.form-textarea,
.form-select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #A3AFB3;
  border-radius: 6px;
  background: #FFFFFF;
  color: #233E47;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 15px;
  box-sizing: border-box;
  transition: border-color .2s ease, box-shadow .2s ease;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
  outline: none;
  border-color: #54100F;
  box-shadow: 0 0 0 2px rgba(84, 16, 15, .12);
}

.form-textarea {
  resize: vertical;
  min-height: 90px;
}

.title-input {
  font-size: 38px;
  font-weight: bold;
  border: none;
  border-bottom: 2px solid #54100F;
  border-radius: 0;
  background: transparent;
  padding: 4px 0;
  margin-bottom: 10px;
}

.title-input:focus {
  box-shadow: none;
}

.checkbox-group {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
  padding: 8px 0;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #233E47;
}

.checkbox-item input {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.action-footer button {
  min-width: 170px;
}

.form-input:disabled,
.form-textarea:disabled,
.form-select:disabled {
  background: #F5F5F5;
  color: #777;
  cursor: not-allowed;
}

/* Mobile */

@media (max-width: 768px) {

  .title-input {
    font-size: 28px;
  }

  .checkbox-group {
    flex-direction: column;
    gap: 12px;
  }

  .form-input,
  .form-select,
  .form-textarea {
    font-size: 14px;
  }
}
</style>