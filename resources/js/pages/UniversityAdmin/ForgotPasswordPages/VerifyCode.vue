<template>
    <div class="page-container">

        <!-- Left Panel -->
        <LeftLogo />

        <!-- Right Panel -->
        <main class="right-panel">

            <div class="form-wrapper">

                <h1 class="title">
                    Check your inbox
                </h1>

                <p class="subtitle">
                    We sent a 6-digit code to
                    <strong>{{ email }}</strong>.
                    Enter it below.
                </p>

                <form
                    class="verify-form"
                    @submit.prevent="handleVerify"
                >

                    <!-- OTP BOXES -->
                    <div class="otp-container">

                        <input
                            v-for="(digit, index) in digits"
                            :key="index"
                            :ref="el => inputRefs[index] = el"
                            v-model="digits[index]"
                            type="text"
                            maxlength="1"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            class="otp-box"
                            @input="onInput(index, $event)"
                            @keydown="onKeyDown(index, $event)"
                            @paste="onPaste"
                        />

                    </div>

                    <!-- ERROR -->
                    <div
                        v-if="error"
                        class="error-message"
                    >
                        {{ error }}
                    </div>

                    <!-- SUCCESS -->
                    <div
                        v-if="success"
                        class="success-message"
                    >
                        {{ success }}
                    </div>

                    <!-- RESEND -->
                    <div class="resend-row">

                        <span class="didnt-get">
                            Didn't receive the code?
                        </span>

                        <button
                            type="button"
                            class="resend-btn"
                            :disabled="resendLoading"
                            @click="handleResend"
                        >
                            {{ resendLoading ? 'Sending...' : 'Resend Code' }}
                        </button>

                    </div>

                    <!-- VERIFY BUTTON -->
                    <button
                        type="submit"
                        class="submit-btn"
                        :disabled="!isCodeComplete || loading"
                    >
                        {{ loading ? 'Verifying...' : 'Verify Code' }}
                    </button>

                </form>

            </div>

        </main>

    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import LeftLogo from "@/components/LeftLogo.vue";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
});

const digits = ref(["", "", "", "", "", ""]);
const inputRefs = ref([]);

const loading = ref(false);
const resendLoading = ref(false);

const error = ref("");
const success = ref("");

const isCodeComplete = computed(() => {
    return digits.value.every((digit) => digit !== "");
});

/**
 * Move cursor automatically
 */
const onInput = (index, event) => {

    const value = event.target.value.replace(/\D/g, "");

    digits.value[index] = value;

    if (value && index < 5) {
        inputRefs.value[index + 1]?.focus();
    }

};

/**
 * Handle Backspace
 */
const onKeyDown = (index, event) => {

    if (event.key === "Backspace") {

        if (digits.value[index] !== "") {

            digits.value[index] = "";

        } else if (index > 0) {

            inputRefs.value[index - 1]?.focus();

        }

    }

};

/**
 * Paste 6-digit code
 */
const onPaste = (event) => {

    event.preventDefault();

    const pasted = event.clipboardData
        .getData("text")
        .replace(/\D/g, "")
        .slice(0, 6);

    pasted.split("").forEach((digit, index) => {
        digits.value[index] = digit;
    });

    const next = Math.min(pasted.length, 5);

    inputRefs.value[next]?.focus();

};

/**
 * Verify OTP
 */
const handleVerify = async () => {

    loading.value = true;
    error.value = "";
    success.value = "";

    try {

        await axios.post("/university-admin/verify-code", {

            email: props.email,

            code: digits.value.join(""),

        });

        success.value = "Verification successful.";

        router.visit("/university-admin/reset-password", {

            method: "get",

            data: {

                email: props.email,

            },

        });

    } catch (err) {

        if (err.response?.status === 422) {

            error.value =
                err.response.data.message ??
                "Invalid verification code.";

        } else if (err.response?.status === 404) {

            error.value =
                err.response.data.message ??
                "Verification code not found.";

        } else {

            error.value =
                "Unable to verify the code. Please try again.";

        }

    } finally {

        loading.value = false;

    }

};

/**
 * Resend OTP
 */
const handleResend = async () => {

    resendLoading.value = true;
    error.value = "";
    success.value = "";

    try {

        await axios.post("/university-admin/resend-code", {

            email: props.email,

        });

        success.value = "A new verification code has been sent.";

        digits.value = ["", "", "", "", "", ""];

        inputRefs.value[0]?.focus();

    } catch (err) {

        if (err.response) {

            error.value =
                err.response.data.message ??
                "Unable to resend verification code.";

        } else {

            error.value =
                "Network error. Please try again.";

        }

    } finally {

        resendLoading.value = false;

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

.subtitle strong {
    color: #54100F;
    font-weight: 700;
}

/* ===========================
   OTP BOXES
=========================== */

.verify-form {
    display: flex;
    flex-direction: column;
}

.otp-container {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 25px;
}

.otp-box {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    border: 2px solid #54100F;
    background: #FFFFFF;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #54100F;
    outline: none;
    transition: .25s;
}

.otp-box:focus {
    border-color: #D99202;
    box-shadow: 0 0 0 4px rgba(217,146,2,.15);
}

/* ===========================
   ALERTS
=========================== */

.error-message {
    background: #FEE2E2;
    color: #B91C1C;
    border: 1px solid #FCA5A5;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    font-size: .9rem;
}

.success-message {
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    font-size: .9rem;
}

/* ===========================
   RESEND
=========================== */

.resend-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.didnt-get {
    color: #233E47;
    font-size: .95rem;
}

.resend-btn {
    border: none;
    background: transparent;
    color: #D99202;
    font-size: .95rem;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
}

.resend-btn:hover {
    color: #54100F;
}

.resend-btn:disabled {
    opacity: .6;
    cursor: not-allowed;
}

/* ===========================
   BUTTON
=========================== */

.submit-btn {
    width: 100%;
    border: none;
    border-radius: 40px;
    padding: 16px;
    background: #D99202;
    color: white;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
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

    .otp-container {
        gap: 8px;
    }

    .otp-box {
        width: 48px;
        height: 48px;
        font-size: 1.2rem;
    }

}

@media (max-width: 480px) {

    .otp-box {
        width: 42px;
        height: 42px;
        font-size: 1rem;
    }

    .resend-row {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

}
</style>