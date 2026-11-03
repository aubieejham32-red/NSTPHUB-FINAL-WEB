<template>
    <div class="page-container">

        <!-- Left Panel -->
        <LeftLogo />

        <!-- Right Panel -->
        <main class="right-panel">

            <div class="form-wrapper">

                <h1 class="title">
                    New Password
                </h1>

                <p class="subtitle">
                    Establish a unique account password known exclusively to you.
                </p>

                <form
                    class="reset-form"
                    @submit.prevent="handleResetPassword"
                >

                    <div class="form-group">

                        <label class="label">
                            New Password
                        </label>

                        <input
                            v-model="newPassword"
                            type="password"
                            class="input-field"
                            placeholder="Enter your new password"
                            required
                        />

                    </div>

                    <div class="form-group">

                        <label class="label">
                            Confirm Password
                        </label>

                        <input
                            v-model="confirmPassword"
                            type="password"
                            class="input-field"
                            placeholder="Re-enter your password"
                            required
                        />

                    </div>

                    <div
                        v-if="errorMessage"
                        class="error-msg"
                    >
                        {{ errorMessage }}
                    </div>

                    <div
                        v-if="successMessage"
                        class="success-msg"
                    >
                        {{ successMessage }}
                    </div>

                    <button
                        type="submit"
                        class="submit-btn"
                        :disabled="loading"
                    >
                        {{ loading ? "Resetting..." : "Reset Password" }}
                    </button>

                </form>

            </div>

        </main>

    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import LeftLogo from "@/components/LeftLogo.vue";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
});

const newPassword = ref("");
const confirmPassword = ref("");

const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

const handleResetPassword = async () => {

    errorMessage.value = "";
    successMessage.value = "";

    // Check password match
    if (newPassword.value !== confirmPassword.value) {
        errorMessage.value = "Passwords do not match.";
        return;
    }

    loading.value = true;

    try {

        await axios.post("/university-admin/reset-password", {

            email: props.email,

            password: newPassword.value,

            password_confirmation: confirmPassword.value,

        });

        successMessage.value = "Password reset successfully.";

        // Redirect to success page
        router.visit("/university-admin/password-reset-success");

    } catch (error) {

        if (error.response?.status === 422) {

            if (error.response.data.errors) {

                errorMessage.value = Object.values(
                    error.response.data.errors
                )
                    .flat()
                    .join(" ");

            } else {

                errorMessage.value =
                    error.response.data.message ??
                    "Unable to reset password.";

            }

        } else if (error.response?.status === 404) {

            errorMessage.value =
                error.response.data.message ??
                "Account not found.";

        } else {

            errorMessage.value =
                "Something went wrong. Please try again.";

        }

    } finally {

        loading.value = false;

    }

};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap");

.page-container {
    display: flex;
    min-height: 100vh;
    width: 100%;
    background: #EFEBE2;
    font-family: "Plus Jakarta Sans", sans-serif;
}

/* ===========================
   RIGHT PANEL
=========================== */

.right-panel {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    background: #EFEBE2;
}

.form-wrapper {
    width: 100%;
    max-width: 520px;
}

/* ===========================
   TYPOGRAPHY
=========================== */

.title {
    color: #54100F;
    font-size: 2.3rem;
    font-weight: 700;
    margin-bottom: 12px;
}

.subtitle {
    color: #233E47;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 35px;
}

/* ===========================
   FORM
=========================== */

.reset-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.label {
    color: #233E47;
    font-size: 1rem;
    font-weight: 600;
}

.input-field {
    width: 100%;
    padding: 15px 18px;
    border-radius: 12px;
    border: 2px solid #D99202;
    background: #EBF1FC;
    color: #000D12;
    font-size: .95rem;
    outline: none;
    box-sizing: border-box;
    transition: .25s;
}

.input-field::placeholder {
    color: #8C98A4;
}

.input-field:focus {
    border-color: #58761C;
    box-shadow: 0 0 0 4px rgba(88,118,28,.15);
}

/* ===========================
   ALERTS
=========================== */

.error-msg {
    background: #FEE2E2;
    color: #B91C1C;
    border: 1px solid #FCA5A5;
    border-radius: 10px;
    padding: 12px;
    text-align: center;
    font-size: .9rem;
    font-weight: 500;
}

.success-msg {
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
    border-radius: 10px;
    padding: 12px;
    text-align: center;
    font-size: .9rem;
    font-weight: 500;
}

/* ===========================
   BUTTON
=========================== */

.submit-btn {
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 40px;
    background: #D99202;
    color: #FFFFFF;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: .25s;
    box-shadow: 0 6px 14px rgba(217,146,2,.25);
}

.submit-btn:hover:not(:disabled) {
    background: #C68402;
}

.submit-btn:active:not(:disabled) {
    transform: translateY(1px);
}

.submit-btn:disabled {
    opacity: .7;
    cursor: not-allowed;
}

/* ===========================
   RESPONSIVE
=========================== */

@media (max-width: 900px) {

    .right-panel {
        padding: 1.5rem;
    }

    .title {
        font-size: 1.9rem;
    }

}

@media (max-width: 600px) {

    .page-container {
        flex-direction: column;
    }

    .right-panel {
        padding: 1.25rem;
    }

    .title {
        font-size: 1.7rem;
    }

    .subtitle {
        font-size: .95rem;
    }

    .input-field {
        font-size: .9rem;
    }

    .submit-btn {
        font-size: .95rem;
    }

}
</style>