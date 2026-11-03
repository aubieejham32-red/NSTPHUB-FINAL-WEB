<template>
    <div class="page-container">

        <!-- Left Panel -->
        <LeftLogo />

        <!-- Right Panel -->
        <main class="right-panel">

            <div class="content-wrapper">

                <h1 class="title">
                    All Set
                </h1>

                <p class="subtitle">
                    Your password has been reset successfully.
                    Use your new password the next time you log in to NSTP Hub.
                </p>

                <button
                    type="button"
                    class="action-btn"
                    @click="handleContinue"
                >
                    Continue to Log in
                </button>

            </div>

        </main>

    </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import LeftLogo from "@/components/LeftLogo.vue";

const handleContinue = () => {

    router.visit("/university-admin/login");

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

.content-wrapper {
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
    margin-bottom: 16px;
}

.subtitle {
    color: #233E47;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 40px;
}

/* ===========================
   BUTTON
=========================== */

.action-btn {
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

.action-btn:hover {
    background: #C68402;
}

.action-btn:active {
    transform: translateY(1px);
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

    .action-btn {
        font-size: .95rem;
    }

}
</style>