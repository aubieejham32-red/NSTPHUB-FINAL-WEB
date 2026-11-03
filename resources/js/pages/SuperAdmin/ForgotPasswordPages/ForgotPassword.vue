<template>
    <div class="page-container">

        <!-- Left Panel -->
        <SuperAdminAuthBrandPanel />

        <!-- Right Panel -->
        <main class="right-panel">

            <div class="form-wrapper">

                <h1 class="title">
                    Forgot your password?
                </h1>

                <p class="subtitle">
                    Enter the email on your account.
                    We'll send a 6-digit code to verify it's you.
                </p>

                <form
                    class="forgot-form"
                    @submit.prevent="handleSubmit"
                >

                    <!-- Email -->
                    <div class="form-group">

                        <label class="label">
                            Email
                        </label>

                        <input
                            v-model="email"
                            type="email"
                            class="input-field"
                            placeholder="Enter your email"
                            required
                        >

                    </div>

                    <!-- Error -->
                    <p
                        v-if="error"
                        class="error-message"
                    >
                        {{ error }}
                    </p>

                    <!-- Success -->
                    <p
                        v-if="success"
                        class="success-message"
                    >
                        {{ success }}
                    </p>

                    <!-- Button -->
                    <button
                        type="submit"
                        class="submit-btn"
                        :disabled="loading"
                    >
                        {{ loading ? "Sending..." : "Send Verification Code" }}
                    </button>

                </form>

                <div class="footer-text">

                    Remembered it after all?

                    <a
                        href="#"
                        class="back-link"
                        @click.prevent="goToSignIn"
                    >
                        Back to sign in
                    </a>

                </div>

            </div>

        </main>

    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import SuperAdminAuthBrandPanel from "@/components/SuperAdminAuthBrandPanel.vue";

const email = ref("");

const loading = ref(false);

const error = ref("");

const success = ref("");

const handleSubmit = async () => {

    error.value = "";

    success.value = "";

    loading.value = true;

    try {

        await axios.post("/superadmin/forgot-password", {

            email: email.value,

        });

        success.value = "Verification code sent successfully.";

        router.visit("/superadmin/forgot-password/verify-code", {

            method: "get",

            data: {

                email: email.value,

            },

        });

    } catch (err) {

        console.error(err);

        if (err.response) {

            if (err.response.status === 404) {

                error.value = err.response.data.message;

            } else if (err.response.status === 422) {

                if (err.response.data.errors) {

                    error.value = Object.values(err.response.data.errors)
                        .flat()
                        .join(" ");

                } else {

                    error.value = err.response.data.message;

                }

            } else {

                error.value = err.response.data.message ??
                    "Something went wrong.";

            }

        } else {

            error.value = "Unable to connect to the server.";

        }

    } finally {

        loading.value = false;

    }

};

const goToSignIn = () => {

    router.visit("/superadmin/login");

};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap");

.page-container{
    display:flex;
    min-height:100vh;
    background:#EFEBE2;
    font-family:"Plus Jakarta Sans",sans-serif;
}

.right-panel{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:2rem;
}

.form-wrapper{
    width:100%;
    max-width:520px;
}

.title{
    color:#54100F;
    font-size:2.2rem;
    font-weight:700;
    margin-bottom:10px;
}

.subtitle{
    color:#233E47;
    margin-bottom:35px;
    line-height:1.6;
}

.forgot-form{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.form-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.label{
    font-weight:600;
    color:#233E47;
}

.input-field{
    padding:15px;
    border-radius:12px;
    border:1.5px solid #D99202;
    background:#EBF1FC;
    outline:none;
}

.input-field:focus{
    border-color:#58761C;
}

.error-message{
    color:#dc2626;
    font-size:14px;
    font-weight:600;
}

.success-message{
    color:#15803d;
    font-size:14px;
    font-weight:600;
}

.submit-btn{
    width:100%;
    padding:15px;
    border:none;
    border-radius:30px;
    background:#D99202;
    color:white;
    font-size:18px;
    cursor:pointer;
    transition:.2s;
}

.submit-btn:hover:not(:disabled){
    background:#bf8100;
}

.submit-btn:disabled{
    opacity:.7;
    cursor:not-allowed;
}

.footer-text{
    margin-top:30px;
    text-align:center;
    font-size:14px;
}

.back-link{
    color:#D99202;
    text-decoration:underline;
    cursor:pointer;
    margin-left:5px;
}

.back-link:hover{
    color:#54100F;
}
</style>