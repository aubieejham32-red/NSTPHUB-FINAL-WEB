<template>
    <div class="logout-page">

        <!-- ====================================================== -->
        <!-- ACCOUNT CARD -->
        <!-- ====================================================== -->

        <div class="account-card">

            <!-- TITLE -->
            <h2>Choose An Account</h2>

            <div class="divider"></div>

            <!-- SAVED ACCOUNTS -->

            <template v-if="localAccounts.length">

                <div
                    v-for="account in localAccounts"
                    :key="`${account.id}-${account.email}`"
                    class="account-item"
                    @click="selectAccount(account)"
                >

                    <!-- Avatar -->
                    <img
                        :src="accountPhotoUrl(account)"
                        @error="handleImageError"
                        class="avatar"
                        alt="Profile"
                    >

                    <!-- Account Info -->
                    <div class="info">

                        <h3>
                            {{ account.name }}
                        </h3>

                        <p>
                            {{ account.email }}
                        </p>

                    </div>

                    <!-- Status -->
                    <span class="status-text">
                        Signed out
                    </span>

                </div>

            </template>

            <!-- EMPTY STATE -->

            <div
                v-else
                class="empty-state"
            >

                <UserRound
                    :size="42"
                    class="empty-icon"
                />

                <p>
                    No saved accounts.
                </p>

            </div>

            <div class="divider"></div>

            <!-- ====================================================== -->
            <!-- BOTTOM ACTIONS -->
            <!-- ====================================================== -->

            <div class="bottom-actions">

                <button
                    class="bottom-link"
                    @click="loginAnother"
                >

                    <UserPlus :size="24"/>

                    <span>
                        Use another account
                    </span>

                </button>

                <button
                    class="bottom-link"
                    @click="showRemoveModal = true"
                >

                    <Trash2 :size="24"/>

                    <span>
                        Remove an account
                    </span>

                </button>

            </div>

        </div>

        <!-- ====================================================== -->
        <!-- PASSWORD MODAL -->
        <!-- ====================================================== -->

        <Transition name="fade">

            <div
                v-if="showModal"
                class="modal-overlay"
            >

                <div class="login-modal">

                    <button
                        class="close-btn"
                        @click="closeModal"
                    >

                        <X :size="20"/>

                    </button>

                    <img
                        :src="accountPhotoUrl(selectedAccount)"
                        @error="handleImageError"
                        class="modal-avatar"
                    >

                    <h3>
                        {{ selectedAccount.name }}
                    </h3>

                    <p class="modal-email">
                        {{ selectedAccount.email }}
                    </p>

                    <div class="password-box">

                        <LockKeyhole
                            :size="18"
                            class="password-icon"
                        />

                        <input
                            :type="showPassword ? 'text' : 'password'"
                            v-model="password"
                            placeholder="Enter your password"
                            @keyup.enter="loginSelectedAccount"
                        >

                        <button
                            class="eye-btn"
                            @click="showPassword = !showPassword"
                        >

                            <Eye
                                v-if="!showPassword"
                                :size="20"
                            />

                            <EyeOff
                                v-else
                                :size="20"
                            />

                        </button>

                    </div>

                    <p
                        v-if="error"
                        class="error-text"
                    >

                        <CircleAlert :size="16"/>

                        {{ error }}

                    </p>

                    <div class="modal-buttons">

                        <button
                            class="cancel-btn"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            class="continue-btn"
                            @click="loginSelectedAccount"
                            :disabled="loading"
                        >

                            <LoaderCircle
                                v-if="loading"
                                class="spin"
                                :size="18"
                            />

                            <LogIn
                                v-else
                                :size="18"
                            />

                            <span>

                                {{
                                    loading
                                        ? "Signing In..."
                                        : "Continue"
                                }}

                            </span>

                        </button>

                    </div>

                </div>

            </div>

        </Transition>

        <!-- ====================================================== -->
        <!-- REMOVE ACCOUNT MODAL -->
        <!-- ====================================================== -->

        <Transition name="fade">

            <div
                v-if="showRemoveModal"
                class="modal-overlay"
            >

                <div class="remove-modal">

                    <button
                        class="close-btn"
                        @click="showRemoveModal = false"
                    >

                        <X :size="20"/>

                    </button>

                    <h2>
                        Remove Account
                    </h2>

                    <div class="divider"></div>

                    <div
                        v-for="account in localAccounts"
                        :key="`${account.id}-${account.email}`"
                        class="account-item"
                        @click="askRemove(account)"
                    >

                        <img
                            :src="accountPhotoUrl(account)"
                            @error="handleImageError"
                            class="avatar"
                        >

                        <div class="info">

                            <h3>
                                {{ account.name }}
                            </h3>

                            <p>
                                {{ account.email }}
                            </p>

                        </div>

                    </div>

                    <div class="modal-buttons">

                        <button
                            class="cancel-btn"
                            @click="showRemoveModal = false"
                        >
                            Cancel
                        </button>

                    </div>

                </div>

            </div>

        </Transition>

        <!-- ====================================================== -->
        <!-- CONFIRM REMOVE -->
        <!-- ====================================================== -->

        <Transition name="fade">

            <div
                v-if="showConfirmModal"
                class="modal-overlay"
            >

                <div class="confirm-modal">

                    <h2>
                        Remove Account
                    </h2>

                    <p>

                        Remove

                        <strong>

                            {{ accountToRemove?.name }}

                        </strong>

                        from this device?

                    </p>

                    <div class="modal-buttons">

                        <button
                            class="cancel-btn"
                            @click="showConfirmModal = false"
                        >

                            Cancel

                        </button>

                        <button
                            class="remove-btn"
                            @click="confirmRemove"
                        >

                            Remove

                        </button>

                    </div>

                </div>

            </div>

        </Transition>

    </div>
</template>

<script>
import axios from "axios";
import { router } from "@inertiajs/vue3";

const SUPERADMIN_STORAGE_KEY = "savedSuperAdminAccounts";


import {
    Trash2,
    UserRound,
    UserPlus,
    LockKeyhole,
    Eye,
    EyeOff,
    X,
    CircleAlert,
    LoaderCircle,
    LogIn
} from "lucide-vue-next";

export default {

    props: {

        accounts: {
            type: Array,
            default: () => []
        }

    },

    components: {

        Trash2,
        UserRound,
        UserPlus,
        LockKeyhole,
        Eye,
        EyeOff,
        X,
        CircleAlert,
        LoaderCircle,
        LogIn

    },

    data() {

        return {

            // Saved Accounts
            localAccounts: [],

            // Login Modal
            showModal: false,
            selectedAccount: {},
            password: "",
            showPassword: false,
            loading: false,
            error: "",

            // Remove Account Modal
            showRemoveModal: false,

            // Confirmation Modal
            showConfirmModal: false,
            accountToRemove: null,

        };

    },

    mounted() {

        this.loadAccounts();

    },

    methods: {

        /* =======================================================
           NORMALIZE ACCOUNT
        ======================================================= */

        normalizeAccount(account = {}) {

            return {
                ...account,
                id: account.id ?? null,
                name:
                    account.name ??
                    account.full_name ??
                    account.username ??
                    'Super Admin',
                email: account.email ?? '',
                photo:
                    account.photo ??
                    account.profile_photo ??
                    account.avatar ??
                    account.picture ??
                    account.profile_picture ??
                    '',
                account_type: 'superadmin',
            };

        },

        accountPhotoUrl(account = {}) {

            const photo = String(
                this.normalizeAccount(account).photo ?? ''
            ).trim();

            if (!photo) {
                return '/images/default-avatar.png';
            }

            if (
                photo.startsWith('http://') ||
                photo.startsWith('https://') ||
                photo.startsWith('data:') ||
                photo.startsWith('blob:')
            ) {
                return photo;
            }

            if (
                photo.startsWith('/storage/') ||
                photo.startsWith('/images/') ||
                photo.startsWith('/uploads/')
            ) {
                return photo;
            }

            if (photo.startsWith('storage/')) {
                return `/${photo}`;
            }

            return `/storage/${photo.replace(/^\/+/, '')}`;

        },

        handleImageError(event) {

            if (!event?.target) {
                return;
            }

            const fallback = '/images/default-avatar.png';

            if (event.target.getAttribute('src') !== fallback) {
                event.target.src = fallback;
            }

        },

        /* =======================================================
           LOAD ACCOUNTS
        ======================================================= */

        loadAccounts() {

            let saved = [];

            try {
                const raw = localStorage.getItem(SUPERADMIN_STORAGE_KEY);
                saved = raw ? JSON.parse(raw) : [];

                if (!Array.isArray(saved)) {
                    saved = [];
                }
            } catch {
                saved = [];
            }

            const serverAccounts =
                Array.isArray(this.accounts) ? this.accounts : [];

            const merged = new Map();

            [...saved, ...serverAccounts]
                .map(account => this.normalizeAccount(account))
                .forEach(account => {
                    const key =
                        String(account.email || '').toLowerCase() ||
                        `id:${account.id}`;

                    if (key) {
                        merged.set(key, account);
                    }
                });

            this.localAccounts = Array.from(merged.values());

            localStorage.setItem(
                SUPERADMIN_STORAGE_KEY,
                JSON.stringify(this.localAccounts)
            );

        },

        /* =======================================================
           ACCOUNT SELECTION
        ======================================================= */

        selectAccount(account) {

            this.selectedAccount = account;

            this.password = "";

            this.error = "";

            this.showPassword = false;

            this.showModal = true;

        },

        closeModal() {

            this.showModal = false;

            this.password = "";

            this.error = "";

            this.loading = false;

        },

        /* =======================================================
           LOGIN
        ======================================================= */

        async loginSelectedAccount() {

            if (!this.password) {

                this.error = "Please enter your password.";

                return;

            }

            this.loading = true;

            this.error = "";

            try {

                const response = await axios.post(

                    "/superadmin/login",

                    {

                        email: this.selectedAccount.email,
                        password: this.password,
                        remember: true

                    },

                    {

                        headers: {

                            Accept: "application/json"

                        }

                    }

                );

                let accounts = [];

                try {
                    const raw = localStorage.getItem(SUPERADMIN_STORAGE_KEY);
                    accounts = raw ? JSON.parse(raw) : [];

                    if (!Array.isArray(accounts)) {
                        accounts = [];
                    }
                } catch {
                    accounts = [];
                }

                const current =
                    this.normalizeAccount(response.data?.user ?? {});

                const index = accounts.findIndex(
                    account =>
                        Number(account?.id) === Number(current.id) ||
                        String(account?.email ?? '').toLowerCase() ===
                            String(current.email ?? '').toLowerCase()
                );

                if (index === -1) {
                    accounts.push(current);
                } else {
                    accounts[index] = {
                        ...accounts[index],
                        ...current,
                    };
                }

                localStorage.setItem(
                    SUPERADMIN_STORAGE_KEY,
                    JSON.stringify(accounts)
                );

                window.location.href = response.data.redirect;

            }

            catch (error) {

                if (error.response?.data?.errors) {

                    const errors = error.response.data.errors;

                    if (errors.email) {

                        this.error = errors.email[0];

                    }

                    else if (errors.password) {

                        this.error = errors.password[0];

                    }

                    else {

                        this.error = "Unable to sign in.";

                    }

                }

                else if (error.response?.data?.message) {

                    this.error = error.response.data.message;

                }

                else {

                    this.error = "Unable to connect to the server.";

                }

            }

            finally {

                this.loading = false;

            }

        },

        /* =======================================================
           USE ANOTHER ACCOUNT
        ======================================================= */

        loginAnother() {

            router.visit("/superadmin/login");

        },

        /* =======================================================
           REMOVE ACCOUNT
        ======================================================= */

        askRemove(account) {

            this.accountToRemove = account;

            this.showConfirmModal = true;

        },

        confirmRemove() {

            if (!this.accountToRemove) {

                return;

            }

            this.localAccounts =

                this.localAccounts.filter(

                    account =>

                        account.id !== this.accountToRemove.id

                );

            localStorage.setItem(

                SUPERADMIN_STORAGE_KEY,

                JSON.stringify(this.localAccounts)

            );

            this.showConfirmModal = false;

            this.showRemoveModal = false;

            this.accountToRemove = null;

        },

        cancelRemove() {

            this.showConfirmModal = false;

            this.accountToRemove = null;

        }

    }

};
</script>

<style scoped>

@import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

/* ==========================================================
   GLOBAL
========================================================== */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

.logout-page{
    min-height:100vh;
    background:#EFEBE2;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:60px 20px;
    font-family:'Plus Jakarta Sans',sans-serif;
}

/* ==========================================================
   ACCOUNT CARD
========================================================== */

.account-card{
    width:100%;
    max-width:620px;
    background:#EFEBE2;
    border:2px solid #D99202;
    padding:28px 34px;
}

.account-card h2{
    text-align:center;
    font-family:'Lora',serif;
    font-size:42px;
    font-weight:500;
    color:#54100F;
    margin-bottom:24px;
}

/* ==========================================================
   DIVIDER
========================================================== */

.divider{
    width:100%;
    height:2px;
    background:#D99202;
    margin:18px 0;
}

/* ==========================================================
   ACCOUNT LIST
========================================================== */

.account-item{

    display:flex;
    align-items:center;
    gap:18px;

    padding:22px 8px;

    cursor:pointer;

    border-bottom:2px solid #D99202;

    transition:
        background .2s,
        transform .15s;

}

.account-item:last-child{

    border-bottom:none;

}

.account-item:hover{

    background:rgba(217,146,2,.06);

}

.account-item:active{

    transform:scale(.99);

}

/* ==========================================================
   AVATAR
========================================================== */

.avatar{

    width:62px;
    height:62px;

    border-radius:50%;

    object-fit:cover;

    border:2px solid #FFBD36;

    flex-shrink:0;

}

/* ==========================================================
   ACCOUNT INFO
========================================================== */

.info{

    flex:1;
    min-width:0;

}

.info h3{

    font-family:'Lora',serif;
    font-size:23px;
    font-weight:600;
    color:#54100F;

    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;

}

.info p{

    margin-top:5px;

    font-size:14px;

    color:#666;

    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;

}

/* ==========================================================
   STATUS
========================================================== */

.status-text{

    font-size:13px;
    color:#9A9A9A;
    white-space:nowrap;

}

/* ==========================================================
   EMPTY STATE
========================================================== */

.empty-state{

    padding:70px 20px;

    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;

    color:#777;

}

.empty-icon{

    color:#D0D0D0;

    margin-bottom:14px;

}

.empty-state p{

    font-size:17px;

}

/* ==========================================================
   BOTTOM ACTIONS
========================================================== */

.bottom-actions{

    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-top:14px;

    gap:18px;

}

.bottom-link{

    flex:1;

    display:flex;
    justify-content:center;
    align-items:center;

    gap:10px;

    background:none;
    border:none;

    cursor:pointer;

    padding:14px 10px;

    color:#54100F;

    font-size:17px;
    font-weight:500;

    transition:.2s;

}

.bottom-link:hover{

    color:#D99202;

}

.bottom-link svg{

    flex-shrink:0;

}

/* ==========================================================
   BUTTON DEFAULTS
========================================================== */

button{

    font-family:'Plus Jakarta Sans',sans-serif;

}

button:focus-visible,
input:focus-visible{

    outline:3px solid rgba(217,146,2,.25);

    outline-offset:2px;

}
/* ==========================================================
   MODAL OVERLAY
========================================================== */

.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    backdrop-filter:blur(5px);
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

/* ==========================================================
   PASSWORD MODAL
========================================================== */

.login-modal{
    width:460px;
    max-width:95%;
    background:#FFFFFF;
    border-radius:24px;
    padding:36px;
    position:relative;
    box-shadow:0 22px 60px rgba(0,0,0,.20);
}

/* ==========================================================
   CLOSE BUTTON
========================================================== */

.close-btn{
    position:absolute;
    top:18px;
    right:18px;

    width:40px;
    height:40px;

    border:none;
    border-radius:50%;

    background:#F3F3F3;

    display:flex;
    justify-content:center;
    align-items:center;

    cursor:pointer;

    transition:.2s;
}

.close-btn:hover{
    background:#E6E6E6;
}

/* ==========================================================
   MODAL PROFILE
========================================================== */

.modal-avatar{

    width:94px;
    height:94px;

    border-radius:50%;
    object-fit:cover;

    display:block;
    margin:0 auto;

    border:3px solid #FFBD36;

}

.login-modal h3{

    margin-top:18px;

    text-align:center;

    font-family:'Lora',serif;

    font-size:28px;
    font-weight:600;

    color:#54100F;

}

.modal-email{

    text-align:center;

    color:#777;

    margin-top:6px;
    margin-bottom:28px;

    font-size:15px;

}

/* ==========================================================
   PASSWORD INPUT
========================================================== */

.password-box{

    display:flex;
    align-items:center;

    height:58px;

    border:2px solid #D5D5D5;
    border-radius:12px;

    padding:0 16px;

    transition:.2s;

}

.password-box:focus-within{

    border-color:#D99202;

}

.password-icon{

    color:#777;

}

.password-box input{

    flex:1;

    border:none;
    outline:none;
    background:transparent;

    padding:0 12px;

    font-size:16px;

    font-family:'Plus Jakarta Sans',sans-serif;

}

.password-box input::placeholder{

    color:#A6A6A6;

}

.eye-btn{

    width:40px;
    height:40px;

    border:none;
    background:none;

    display:flex;
    justify-content:center;
    align-items:center;

    cursor:pointer;

    color:#666;

    transition:.2s;

}

.eye-btn:hover{

    color:#D99202;

}

/* ==========================================================
   ERROR MESSAGE
========================================================== */

.error-text{

    margin-top:16px;

    display:flex;
    align-items:center;
    justify-content:center;

    gap:8px;

    color:#D92D20;

    font-size:14px;
    font-weight:500;

}

/* ==========================================================
   MODAL BUTTONS
========================================================== */

.modal-buttons{

    margin-top:28px;

    display:flex;
    justify-content:flex-end;

    gap:12px;

}

.cancel-btn{

    width:120px;
    height:46px;

    border:1px solid #D5D5D5;

    background:#FFFFFF;

    border-radius:10px;

    cursor:pointer;

    font-weight:600;

    transition:.2s;

}

.cancel-btn:hover{

    background:#F5F5F5;

}

.continue-btn{

    width:170px;
    height:46px;

    border:none;
    border-radius:10px;

    background:#58761C;

    color:#FFFFFF;

    display:flex;
    justify-content:center;
    align-items:center;

    gap:8px;

    cursor:pointer;

    font-weight:600;

    transition:.2s;

}

.continue-btn:hover{

    background:#496218;

}

.continue-btn:disabled{

    opacity:.7;

    cursor:not-allowed;

}

/* ==========================================================
   REMOVE ACCOUNT MODAL
========================================================== */

.remove-modal{

    width:560px;
    max-width:95%;

    background:#FFFFFF;

    border-radius:24px;

    padding:30px;

    position:relative;

    box-shadow:0 22px 60px rgba(0,0,0,.22);

}

.remove-modal h2{

    text-align:center;

    font-family:'Lora',serif;

    font-size:32px;
    font-weight:600;

    color:#54100F;

    margin-bottom:18px;

}

.remove-modal .account-item{

    padding:18px 4px;

}

/* ==========================================================
   CONFIRM REMOVE MODAL
========================================================== */

.confirm-modal{

    width:420px;
    max-width:92%;

    background:#FFFFFF;

    border-radius:22px;

    padding:30px;

    text-align:center;

    box-shadow:0 22px 60px rgba(0,0,0,.20);

}

.confirm-modal h2{

    font-family:'Lora',serif;

    font-size:28px;

    color:#54100F;

    margin-bottom:18px;

}

.confirm-modal p{

    color:#666;

    font-size:16px;

    line-height:1.7;

    margin-bottom:28px;

}

.remove-btn{

    width:140px;
    height:46px;

    border:none;
    border-radius:10px;

    background:#C62828;

    color:#FFFFFF;

    cursor:pointer;

    font-weight:600;

    transition:.2s;

}

.remove-btn:hover{

    background:#B71C1C;

}

/* ==========================================================
   LOADING ICON
========================================================== */

.spin{

    animation:spin .8s linear infinite;

}
/* ==========================================================
   ANIMATIONS
========================================================== */

@keyframes spin{
    from{
        transform:rotate(0deg);
    }
    to{
        transform:rotate(360deg);
    }
}

@keyframes popup{

    from{
        opacity:0;
        transform:translateY(12px) scale(.96);
    }

    to{
        opacity:1;
        transform:translateY(0) scale(1);
    }

}

.fade-enter-active,
.fade-leave-active{

    transition:opacity .25s ease;

}

.fade-enter-from,
.fade-leave-to{

    opacity:0;

}

.login-modal,
.remove-modal,
.confirm-modal{

    animation:popup .25s ease;

}

/* ==========================================================
   RESPONSIVE
========================================================== */

@media (max-width:992px){

    .logout-page{

        padding:40px 20px;

    }

    .account-card{

        max-width:100%;

        padding:24px;

    }

    .account-card h2{

        font-size:34px;

    }

    .info h3{

        font-size:20px;

    }

    .bottom-actions{

        flex-direction:column;

        gap:8px;

    }

    .bottom-link{

        width:100%;
        justify-content:flex-start;

        border-radius:10px;

        padding:16px;

    }

    .modal-buttons{

        flex-direction:column;

    }

    .cancel-btn,
    .continue-btn,
    .remove-btn{

        width:100%;

    }

}

@media (max-width:768px){

    .logout-page{

        padding:25px 16px;

    }

    .account-card{

        padding:20px;

    }

    .account-card h2{

        font-size:30px;

        margin-bottom:18px;

    }

    .account-item{

        gap:14px;

        padding:18px 4px;

    }

    .avatar{

        width:54px;
        height:54px;

    }

    .info h3{

        font-size:18px;

    }

    .info p{

        font-size:13px;

    }

    .status-text{

        display:none;

    }

    .login-modal,
    .remove-modal,
    .confirm-modal{

        width:100%;

        max-width:100%;

        border-radius:18px;

    }

}

@media (max-width:480px){

    .logout-page{

        padding:16px;

    }

    .account-card{

        padding:16px;

    }

    .account-card h2{

        font-size:26px;

    }

    .avatar{

        width:48px;
        height:48px;

    }

    .info h3{

        font-size:16px;

    }

    .info p{

        font-size:12px;

    }

    .bottom-link{

        font-size:15px;

        padding:14px;

    }

    .modal-avatar{

        width:80px;
        height:80px;

    }

    .login-modal h3{

        font-size:24px;

    }

    .modal-email{

        font-size:14px;

    }

    .password-box{

        height:52px;

    }

    .password-box input{

        font-size:15px;

    }

    .confirm-modal h2{

        font-size:24px;

    }

    .confirm-modal p{

        font-size:14px;

    }

}

/* ==========================================================
   SCROLLBAR
========================================================== */

.remove-modal{

    max-height:85vh;

    overflow-y:auto;

}

.remove-modal::-webkit-scrollbar{

    width:8px;

}

.remove-modal::-webkit-scrollbar-track{

    background:#EFEFEF;

    border-radius:20px;

}

.remove-modal::-webkit-scrollbar-thumb{

    background:#C8C8C8;

    border-radius:20px;

}

.remove-modal::-webkit-scrollbar-thumb:hover{

    background:#AFAFAF;

}

/* ==========================================================
   IMAGE PROTECTION
========================================================== */

.logo,
.avatar,
.modal-avatar{

    user-select:none;
    -webkit-user-drag:none;

}

/* ==========================================================
   SMOOTH TRANSITIONS
========================================================== */

button,
.account-item,
.bottom-link,
.cancel-btn,
.continue-btn,
.remove-btn,
.close-btn,
.eye-btn{

    transition:
        background-color .2s ease,
        color .2s ease,
        transform .2s ease,
        border-color .2s ease,
        box-shadow .2s ease;

}

button:active{

    transform:scale(.98);

}

.account-item:active{

    transform:scale(.99);

}

/* ==========================================================
   HOVER EFFECTS
========================================================== */

.account-card{

    transition:box-shadow .25s ease;

}

.account-card:hover{

    box-shadow:0 12px 35px rgba(84,16,15,.08);

}

.bottom-link:hover svg{

    transform:scale(1.1);

}

.eye-btn:hover{

    transform:scale(1.1);

}

/* ==========================================================
   END
========================================================== */

</style>