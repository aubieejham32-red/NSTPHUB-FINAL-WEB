<template>
    <div class="logout-page">

        <!-- ==========================================================
             ACCOUNT CARD
        =========================================================== -->

        <div class="account-card">

            <!-- ======================================================
                 TITLE
            ======================================================= -->

            <h2>
                Choose An Account
            </h2>

            <div class="divider"></div>


            <!-- ======================================================
                 SAVED ACCOUNTS
            ======================================================= -->

            <template v-if="localAccounts.length">

                <div
                    v-for="account in localAccounts"
                    :key="getAccountKey(account)"
                    class="account-item"
                    @click="selectAccount(account)"
                >

                    <!-- ==================================================
                         PROFILE PHOTO
                    =================================================== -->

                    <img
                        :src="getAccountPhoto(account)"
                        class="avatar"
                        :alt="getAccountName(account)"
                        @error="handleAvatarError"
                    >


                    <!-- ==================================================
                         ACCOUNT INFORMATION
                    =================================================== -->

                    <div class="info">

                        <h3>
                            {{ getAccountName(account) }}
                        </h3>

                        <p>
                            {{ account.email }}
                        </p>

                        <span
                            v-if="getUniversityName(account)"
                            class="university-name"
                        >
                            {{ getUniversityName(account) }}
                        </span>

                    </div>


                    <!-- ==================================================
                         STATUS
                    =================================================== -->

                    <span class="status-text">
                        Signed out
                    </span>

                </div>

            </template>


            <!-- ======================================================
                 EMPTY STATE
            ======================================================= -->

            <div
                v-else
                class="empty-state"
            >

                <UserRound
                    :size="42"
                    class="empty-icon"
                />

                <p>
                    No saved Instructor or Coordinator accounts.
                </p>

            </div>


            <div class="divider"></div>


            <!-- ======================================================
                 BOTTOM ACTIONS
            ======================================================= -->

            <div class="bottom-actions">

                <!-- USE ANOTHER ACCOUNT -->

                <button
                    type="button"
                    class="bottom-link"
                    @click="loginAnother"
                >

                    <UserPlus :size="24" />

                    <span>
                        Use another account
                    </span>

                </button>


                <!-- REMOVE ACCOUNT -->

                <button
                    type="button"
                    class="bottom-link"
                    :disabled="!localAccounts.length"
                    @click="openRemoveModal"
                >

                    <Trash2 :size="24" />

                    <span>
                        Remove an account
                    </span>

                </button>

            </div>

        </div>


        <!-- ==========================================================
             LOGIN PASSWORD MODAL
        =========================================================== -->

        <Transition name="fade">

            <div
                v-if="showLoginModal"
                class="modal-overlay"
                @click.self="closeLoginModal"
            >

                <div class="login-modal">

                    <!-- ==================================================
                         CLOSE
                    =================================================== -->

                    <button
                        type="button"
                        class="close-btn"
                        aria-label="Close"
                        :disabled="loading"
                        @click="closeLoginModal"
                    >
                        <X :size="20" />
                    </button>


                    <!-- ==================================================
                         PROFILE PHOTO
                    =================================================== -->

                    <img
                        :src="getAccountPhoto(selectedAccount)"
                        class="modal-avatar"
                        :alt="getAccountName(selectedAccount)"
                        @error="handleAvatarError"
                    >


                    <!-- ==================================================
                         NAME
                    =================================================== -->

                    <h3>
                        {{ getAccountName(selectedAccount) }}
                    </h3>


                    <!-- ==================================================
                         EMAIL
                    =================================================== -->

                    <p class="modal-email">
                        {{ selectedAccount.email }}
                    </p>


                    <!-- ==================================================
                         UNIVERSITY
                    =================================================== -->

                    <p
                        v-if="getUniversityName(selectedAccount)"
                        class="modal-university"
                    >
                        {{ getUniversityName(selectedAccount) }}
                    </p>


                    <!-- ==================================================
                         PASSWORD
                    =================================================== -->

                    <div
                        class="password-box"
                        :class="{
                            'password-error': error,
                        }"
                    >

                        <LockKeyhole
                            :size="18"
                            class="password-icon"
                        />


                        <input
                            v-model="password"
                            :type="
                                showPassword
                                    ? 'text'
                                    : 'password'
                            "
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            :disabled="loading"
                            @input="error = ''"
                            @keyup.enter="loginSelectedAccount"
                        >


                        <!-- ==============================================
                             SHOW / HIDE
                        =============================================== -->

                        <button
                            type="button"
                            class="eye-btn"
                            :disabled="loading"
                            @click="
                                showPassword =
                                    !showPassword
                            "
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


                    <!-- ==================================================
                         ERROR
                    =================================================== -->

                    <p
                        v-if="error"
                        class="error-text"
                    >

                        <CircleAlert :size="16" />

                        <span>
                            {{ error }}
                        </span>

                    </p>


                    <!-- ==================================================
                         BUTTONS
                    =================================================== -->

                    <div class="modal-buttons">

                        <button
                            type="button"
                            class="cancel-btn"
                            :disabled="loading"
                            @click="closeLoginModal"
                        >
                            Cancel
                        </button>


                        <button
                            type="button"
                            class="continue-btn"
                            :disabled="loading"
                            @click="loginSelectedAccount"
                        >

                            <LoaderCircle
                                v-if="loading"
                                :size="18"
                                class="spin"
                            />

                            <LogIn
                                v-else
                                :size="18"
                            />

                            <span>
                                {{
                                    loading
                                        ? 'Signing In...'
                                        : 'Continue'
                                }}
                            </span>

                        </button>

                    </div>

                </div>

            </div>

        </Transition>


        <!-- ==========================================================
             REMOVE ACCOUNT MODAL
        =========================================================== -->

        <Transition name="fade">

            <div
                v-if="showRemoveModal"
                class="modal-overlay"
                @click.self="closeRemoveModal"
            >

                <div class="remove-modal">

                    <!-- CLOSE -->

                    <button
                        type="button"
                        class="close-btn"
                        aria-label="Close"
                        @click="closeRemoveModal"
                    >
                        <X :size="20" />
                    </button>


                    <h2>
                        Remove Account
                    </h2>


                    <p class="remove-description">
                        Select the Instructor or Coordinator account
                        you want to remove from this device.
                    </p>


                    <div class="divider"></div>


                    <!-- ==================================================
                         SAVED ACCOUNTS
                    =================================================== -->

                    <div
                        v-for="account in localAccounts"
                        :key="getAccountKey(account)"
                        class="account-item remove-account-item"
                        @click="askRemove(account)"
                    >

                        <img
                            :src="getAccountPhoto(account)"
                            class="avatar"
                            :alt="getAccountName(account)"
                            @error="handleAvatarError"
                        >


                        <div class="info">

                            <h3>
                                {{ getAccountName(account) }}
                            </h3>

                            <p>
                                {{ account.email }}
                            </p>

                            <span
                                v-if="getUniversityName(account)"
                                class="university-name"
                            >
                                {{ getUniversityName(account) }}
                            </span>

                        </div>


                        <Trash2
                            :size="21"
                            class="remove-list-icon"
                        />

                    </div>


                    <!-- ==================================================
                         EMPTY
                    =================================================== -->

                    <div
                        v-if="!localAccounts.length"
                        class="empty-state small-empty"
                    >

                        <UserRound
                            :size="36"
                            class="empty-icon"
                        />

                        <p>
                            No saved accounts.
                        </p>

                    </div>


                    <div class="modal-buttons">

                        <button
                            type="button"
                            class="cancel-btn"
                            @click="closeRemoveModal"
                        >
                            Close
                        </button>

                    </div>

                </div>

            </div>

        </Transition>


        <!-- ==========================================================
             CONFIRM REMOVE
        =========================================================== -->

        <Transition name="fade">

            <div
                v-if="showConfirmModal"
                class="modal-overlay confirm-overlay"
                @click.self="cancelRemove"
            >

                <div class="confirm-modal">

                    <div class="confirm-icon">

                        <Trash2 :size="28" />

                    </div>


                    <h2>
                        Remove Account
                    </h2>


                    <p>

                        Remove

                        <strong>
                            {{ getAccountName(accountToRemove) }}
                        </strong>

                        from this device?

                    </p>


                    <small>
                        This only removes the saved account from this
                        browser. It will not delete the Instructor or
                        Coordinator account from the database.
                    </small>


                    <div class="modal-buttons confirm-buttons">

                        <button
                            type="button"
                            class="cancel-btn"
                            @click="cancelRemove"
                        >
                            Cancel
                        </button>


                        <button
                            type="button"
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


<script setup>

import {
    onMounted,
    ref,
} from 'vue';

import {
    router,
} from '@inertiajs/vue3';

import {
    CircleAlert,
    Eye,
    EyeOff,
    LoaderCircle,
    LockKeyhole,
    LogIn,
    Trash2,
    UserPlus,
    UserRound,
    X,
} from 'lucide-vue-next';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    accounts: {
        type: Array,

        default: () => [],
    },

});


/*
|--------------------------------------------------------------------------
| Storage
|--------------------------------------------------------------------------
*/

const storageKey =
    'savedInstructorCoordinatorAccounts';


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const localAccounts =
    ref(
        []
    );


const showLoginModal =
    ref(
        false
    );


const selectedAccount =
    ref(
        {}
    );


const password =
    ref(
        ''
    );


const showPassword =
    ref(
        false
    );


const loading =
    ref(
        false
    );


const error =
    ref(
        ''
    );


const showRemoveModal =
    ref(
        false
    );


const showConfirmModal =
    ref(
        false
    );


const accountToRemove =
    ref(
        null
    );


/*
|--------------------------------------------------------------------------
| Default Avatar
|--------------------------------------------------------------------------
*/

const defaultAvatar =
    '/images/default-avatar.png';


/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {

    loadAccounts();

});


/*
|--------------------------------------------------------------------------
| Normalize Account Type
|--------------------------------------------------------------------------
*/

function normalizeAccountType(
    account
) {

    const accountType =
        String(
            account?.account_type ??
            account?.accountType ??
            ''
        )
            .trim()
            .toLowerCase();


    if (
        accountType ===
        'instructor'
    ) {

        return 'instructor';

    }


    if (
        accountType ===
        'coordinator'
    ) {

        return 'coordinator';

    }


    /*
    |--------------------------------------------------------------------------
    | Resolve From Role
    |--------------------------------------------------------------------------
    */

    const role =
        String(
            account?.role ??
            ''
        )
            .trim()
            .toLowerCase();


    if (
        role ===
        'instructor'
    ) {

        return 'instructor';

    }


    if (
        role.startsWith(
            'coordinator-'
        )
    ) {

        return 'coordinator';

    }


    return '';

}


/*
|--------------------------------------------------------------------------
| Account Key
|--------------------------------------------------------------------------
*/

function getAccountKey(
    account
) {

    if (
        !account
    ) {

        return '';

    }


    return `${
        normalizeAccountType(
            account
        )
    }-${account.id ?? 'unknown'}`;

}


/*
|--------------------------------------------------------------------------
| Account Name
|--------------------------------------------------------------------------
*/

function getAccountName(
    account
) {

    return (
        account?.full_name ||
        account?.name ||
        account?.username ||
        account?.email ||
        'NSTP Staff'
    );

}


/*
|--------------------------------------------------------------------------
| University
|--------------------------------------------------------------------------
*/

function getUniversityName(
    account
) {

    return (
        account?.university_name ||
        account?.university?.name ||
        account?.university_acronym ||
        account?.university?.acronym ||
        ''
    );

}


/*
|--------------------------------------------------------------------------
| Load Accounts
|--------------------------------------------------------------------------
*/

function loadAccounts() {

    let savedAccounts =
        [];


    /*
    |--------------------------------------------------------------------------
    | Local Storage
    |--------------------------------------------------------------------------
    */

    try {

        const saved =
            localStorage.getItem(
                storageKey
            );


        if (
            saved
        ) {

            const parsed =
                JSON.parse(
                    saved
                );


            if (
                Array.isArray(
                    parsed
                )
            ) {

                savedAccounts =
                    parsed;

            }

        }

    } catch (
        storageError
    ) {

        console.error(
            'Unable to read saved Instructor / Coordinator accounts.',
            storageError
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Merge Laravel Account
    |--------------------------------------------------------------------------
    */

    props.accounts.forEach(
        (
            account
        ) => {

            const type =
                normalizeAccountType(
                    account
                );


            if (
                !type
            ) {

                return;

            }


            const incomingKey =
                getAccountKey(
                    account
                );


            const index =
                savedAccounts.findIndex(
                    (
                        savedAccount
                    ) => {

                        return (
                            getAccountKey(
                                savedAccount
                            ) ===
                            incomingKey
                        );

                    }
                );


            /*
            |--------------------------------------------------------------------------
            | New
            |--------------------------------------------------------------------------
            */

            if (
                index ===
                -1
            ) {

                savedAccounts.push(
                    account
                );


                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Refresh Existing
            |--------------------------------------------------------------------------
            */

            savedAccounts[index] = {

                ...savedAccounts[index],

                ...account,

            };

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Remove Invalid
    |--------------------------------------------------------------------------
    */

    savedAccounts =
        savedAccounts.filter(
            (
                account
            ) => {

                return (
                    account?.id &&
                    account?.email &&
                    normalizeAccountType(
                        account
                    )
                );

            }
        );


    localAccounts.value =
        savedAccounts;


    saveAccounts();

}


/*
|--------------------------------------------------------------------------
| Save Accounts
|--------------------------------------------------------------------------
*/

function saveAccounts() {

    try {

        localStorage.setItem(
            storageKey,
            JSON.stringify(
                localAccounts.value
            )
        );

    } catch (
        storageError
    ) {

        console.error(
            'Unable to save Instructor / Coordinator accounts.',
            storageError
        );

    }

}


/*
|--------------------------------------------------------------------------
| Account Photo
|--------------------------------------------------------------------------
*/

function getAccountPhoto(
    account
) {

    if (
        !account
    ) {

        return defaultAvatar;

    }


    const accountPhoto =
        account.profile_photo ||
        account.photo;


    if (
        !accountPhoto
    ) {

        return defaultAvatar;

    }


    const photo =
        String(
            accountPhoto
        );


    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    */

    if (
        photo.startsWith(
            'http://'
        ) ||
        photo.startsWith(
            'https://'
        ) ||
        photo.startsWith(
            'data:'
        )
    ) {

        return photo;

    }


    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    */

    if (
        photo.startsWith(
            '/storage/'
        )
    ) {

        return photo;

    }


    /*
    |--------------------------------------------------------------------------
    | Public Path
    |--------------------------------------------------------------------------
    */

    if (
        photo.startsWith(
            '/'
        )
    ) {

        return photo;

    }


    return `/storage/${photo}`;

}


/*
|--------------------------------------------------------------------------
| Avatar Error
|--------------------------------------------------------------------------
*/

function handleAvatarError(
    event
) {

    event.target.onerror =
        null;


    event.target.src =
        defaultAvatar;

}


/*
|--------------------------------------------------------------------------
| Select Existing Account
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| Existing saved accounts now open PASSWORD LOGIN.
|
| They DO NOT go back to AccessCode.vue.
|
*/

function selectAccount(
    account
) {

    selectedAccount.value =
        account;


    password.value =
        '';


    error.value =
        '';


    showPassword.value =
        false;


    loading.value =
        false;


    showLoginModal.value =
        true;

}


/*
|--------------------------------------------------------------------------
| Close Login Modal
|--------------------------------------------------------------------------
*/

function closeLoginModal() {

    if (
        loading.value
    ) {

        return;

    }


    showLoginModal.value =
        false;


    selectedAccount.value =
        {};


    password.value =
        '';


    error.value =
        '';


    showPassword.value =
        false;

}


/*
|--------------------------------------------------------------------------
| Login Existing Account
|--------------------------------------------------------------------------
|
| POST:
|
| /instructor-coordinator/logout-page/login
|
*/

function loginSelectedAccount() {

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Submission
    |--------------------------------------------------------------------------
    */

    if (
        loading.value
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Selected Account
    |--------------------------------------------------------------------------
    */

    if (
        !selectedAccount.value?.id ||
        !selectedAccount.value?.email
    ) {

        error.value =
            'Unable to identify the selected account.';


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Account Type
    |--------------------------------------------------------------------------
    */

    const accountType =
        normalizeAccountType(
            selectedAccount.value
        );


    if (
        !accountType
    ) {

        error.value =
            'Unable to identify the selected account type.';


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Password Required
    |--------------------------------------------------------------------------
    */

    if (
        !password.value
    ) {

        error.value =
            'Please enter your password.';


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Start Loading
    |--------------------------------------------------------------------------
    */

    loading.value =
        true;


    error.value =
        '';


    /*
    |--------------------------------------------------------------------------
    | Login Request
    |--------------------------------------------------------------------------
    */

    router.post(
        '/instructor-coordinator/logout-page/login',
        {
            id:
                selectedAccount.value.id,

            account_type:
                accountType,

            email:
                selectedAccount.value.email,

            password:
                password.value,

            remember:
                true,
        },
        {
            preserveScroll:
                true,


            /*
            |--------------------------------------------------------------------------
            | Error
            |--------------------------------------------------------------------------
            */

            onError: (
                errors
            ) => {

                if (
                    errors.login
                ) {

                    error.value =
                        getErrorMessage(
                            errors.login
                        );


                    return;

                }


                if (
                    errors.password
                ) {

                    error.value =
                        getErrorMessage(
                            errors.password
                        );


                    return;

                }


                if (
                    errors.email
                ) {

                    error.value =
                        getErrorMessage(
                            errors.email
                        );


                    return;

                }


                if (
                    errors.account_type
                ) {

                    error.value =
                        getErrorMessage(
                            errors.account_type
                        );


                    return;

                }


                if (
                    errors.id
                ) {

                    error.value =
                        getErrorMessage(
                            errors.id
                        );


                    return;

                }


                error.value =
                    'Invalid email or password.';

            },


            /*
            |--------------------------------------------------------------------------
            | Finished
            |--------------------------------------------------------------------------
            */

            onFinish: () => {

                loading.value =
                    false;

            },
        }
    );

}


/*
|--------------------------------------------------------------------------
| Error Helper
|--------------------------------------------------------------------------
*/

function getErrorMessage(
    value
) {

    if (
        Array.isArray(
            value
        )
    ) {

        return (
            value[0] ||
            ''
        );

    }


    return (
        value ||
        ''
    );

}


/*
|--------------------------------------------------------------------------
| Use Another Account
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| This now routes directly to LoginPage.vue.
|
*/

function loginAnother() {

    router.visit(
        '/instructor-coordinator/login'
    );

}


/*
|--------------------------------------------------------------------------
| Open Remove Modal
|--------------------------------------------------------------------------
*/

function openRemoveModal() {

    if (
        !localAccounts.value.length
    ) {

        return;

    }


    showRemoveModal.value =
        true;

}


/*
|--------------------------------------------------------------------------
| Close Remove Modal
|--------------------------------------------------------------------------
*/

function closeRemoveModal() {

    showRemoveModal.value =
        false;


    showConfirmModal.value =
        false;


    accountToRemove.value =
        null;

}


/*
|--------------------------------------------------------------------------
| Ask Remove
|--------------------------------------------------------------------------
*/

function askRemove(
    account
) {

    accountToRemove.value =
        account;


    showConfirmModal.value =
        true;

}


/*
|--------------------------------------------------------------------------
| Confirm Remove
|--------------------------------------------------------------------------
*/

function confirmRemove() {

    if (
        !accountToRemove.value
    ) {

        return;

    }


    const removeKey =
        getAccountKey(
            accountToRemove.value
        );


    /*
    |--------------------------------------------------------------------------
    | Remove
    |--------------------------------------------------------------------------
    */

    localAccounts.value =
        localAccounts.value.filter(
            (
                account
            ) => {

                return (
                    getAccountKey(
                        account
                    ) !==
                    removeKey
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    saveAccounts();


    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    showConfirmModal.value =
        false;


    accountToRemove.value =
        null;


    /*
    |--------------------------------------------------------------------------
    | Close If Empty
    |--------------------------------------------------------------------------
    */

    if (
        !localAccounts.value.length
    ) {

        showRemoveModal.value =
            false;

    }

}


/*
|--------------------------------------------------------------------------
| Cancel Remove
|--------------------------------------------------------------------------
*/

function cancelRemove() {

    showConfirmModal.value =
        false;


    accountToRemove.value =
        null;

}

</script>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap'
);


/* ==========================================================
   GLOBAL
========================================================== */

* {
    box-sizing: border-box;
}


.logout-page {

    min-height: 100vh;

    background: #EFEBE2;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 60px 20px;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

}


/* ==========================================================
   ACCOUNT CARD
========================================================== */

.account-card {

    width: 100%;

    max-width: 620px;

    background: #EFEBE2;

    border:
        2px
        solid
        #D99202;

    padding:
        28px
        34px;

    border-radius: 4px;

    transition:
        box-shadow
        0.25s
        ease;

}


.account-card:hover {

    box-shadow:
        0
        12px
        35px
        rgba(
            84,
            16,
            15,
            0.08
        );

}


.account-card h2 {

    margin:
        0
        0
        24px;

    text-align: center;

    font-family:
        'Lora',
        serif;

    font-size: 42px;

    font-weight: 500;

    color: #54100F;

}


/* ==========================================================
   DIVIDER
========================================================== */

.divider {

    width: 100%;

    height: 2px;

    background: #D99202;

    margin:
        18px
        0;

}


/* ==========================================================
   ACCOUNT
========================================================== */

.account-item {

    display: flex;

    align-items: center;

    gap: 18px;

    padding:
        22px
        8px;

    cursor: pointer;

    border-bottom:
        2px
        solid
        #D99202;

    transition:
        background-color
        0.2s
        ease,
        transform
        0.15s
        ease;

}


.account-item:last-child {

    border-bottom: none;

}


.account-item:hover {

    background:
        rgba(
            217,
            146,
            2,
            0.06
        );

}


.account-item:active {

    transform:
        scale(
            0.99
        );

}


/* ==========================================================
   AVATAR
========================================================== */

.avatar {

    width: 62px;

    height: 62px;

    border-radius: 50%;

    object-fit: cover;

    border:
        2px
        solid
        #FFBD36;

    flex-shrink: 0;

    user-select: none;

    -webkit-user-drag: none;

}


/* ==========================================================
   INFORMATION
========================================================== */

.info {

    flex: 1;

    min-width: 0;

}


.info h3 {

    margin: 0;

    font-family:
        'Lora',
        serif;

    font-size: 23px;

    font-weight: 600;

    color: #54100F;

    overflow: hidden;

    white-space: nowrap;

    text-overflow: ellipsis;

}


.info p {

    margin:
        5px
        0
        0;

    font-size: 14px;

    color: #666666;

    overflow: hidden;

    white-space: nowrap;

    text-overflow: ellipsis;

}


/* ==========================================================
   UNIVERSITY
========================================================== */

.university-name {

    display: block;

    margin-top: 5px;

    color: #58761C;

    font-size: 11px;

    font-weight: 700;

    overflow: hidden;

    white-space: nowrap;

    text-overflow: ellipsis;

}


/* ==========================================================
   STATUS
========================================================== */

.status-text {

    font-size: 13px;

    color: #9A9A9A;

    white-space: nowrap;

}


/* ==========================================================
   EMPTY
========================================================== */

.empty-state {

    padding:
        70px
        20px;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    color: #777777;

    text-align: center;

}


.empty-icon {

    color: #BEBEBE;

    margin-bottom: 14px;

}


.empty-state p {

    margin: 0;

    font-size: 16px;

}


.small-empty {

    padding:
        35px
        15px;

}


/* ==========================================================
   BOTTOM ACTIONS
========================================================== */

.bottom-actions {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-top: 14px;

    gap: 18px;

}


.bottom-link {

    flex: 1;

    min-height: 52px;

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 10px;

    background: none;

    border: none;

    cursor: pointer;

    padding:
        14px
        10px;

    color: #54100F;

    font-size: 17px;

    font-weight: 500;

    border-radius: 10px;

}


.bottom-link:hover:not(:disabled) {

    color: #D99202;

    background:
        rgba(
            217,
            146,
            2,
            0.05
        );

}


.bottom-link:disabled {

    opacity: 0.4;

    cursor: not-allowed;

}


/* ==========================================================
   MODAL OVERLAY
========================================================== */

.modal-overlay {

    position: fixed;

    inset: 0;

    background:
        rgba(
            0,
            0,
            0,
            0.45
        );

    backdrop-filter:
        blur(
            5px
        );

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 20px;

    z-index: 9999;

}


.confirm-overlay {

    z-index: 10000;

}


/* ==========================================================
   MODALS
========================================================== */

.login-modal,
.remove-modal,
.confirm-modal {

    position: relative;

    background: #FFFFFF;

    box-shadow:
        0
        22px
        60px
        rgba(
            0,
            0,
            0,
            0.20
        );

    animation:
        popup
        0.25s
        ease;

}


.login-modal {

    width: 460px;

    max-width: 100%;

    border-radius: 24px;

    padding: 36px;

}


.remove-modal {

    width: 560px;

    max-width: 100%;

    max-height: 85vh;

    overflow-y: auto;

    border-radius: 24px;

    padding: 30px;

}


.confirm-modal {

    width: 440px;

    max-width: 100%;

    border-radius: 22px;

    padding: 32px;

    text-align: center;

}


/* ==========================================================
   CLOSE
========================================================== */

.close-btn {

    position: absolute;

    top: 18px;

    right: 18px;

    width: 40px;

    height: 40px;

    border: none;

    border-radius: 50%;

    background: #F3F3F3;

    color: #233E47;

    display: flex;

    justify-content: center;

    align-items: center;

    cursor: pointer;

}


.close-btn:hover:not(:disabled) {

    background: #E6E6E6;

}


.close-btn:disabled {

    opacity: 0.6;

    cursor: not-allowed;

}


/* ==========================================================
   LOGIN PROFILE
========================================================== */

.modal-avatar {

    width: 94px;

    height: 94px;

    border-radius: 50%;

    object-fit: cover;

    display: block;

    margin:
        0
        auto;

    border:
        3px
        solid
        #FFBD36;

    user-select: none;

    -webkit-user-drag: none;

}


.login-modal h3 {

    margin:
        18px
        0
        0;

    text-align: center;

    font-family:
        'Lora',
        serif;

    font-size: 28px;

    font-weight: 600;

    color: #54100F;

}


.modal-email {

    text-align: center;

    color: #777777;

    margin:
        6px
        0
        0;

    font-size: 15px;

}


.modal-university {

    text-align: center;

    color: #58761C;

    margin:
        7px
        0
        25px;

    font-size: 12px;

    font-weight: 700;

}


/* ==========================================================
   PASSWORD
========================================================== */

.password-box {

    display: flex;

    align-items: center;

    height: 58px;

    border:
        2px
        solid
        #D5D5D5;

    border-radius: 12px;

    padding:
        0
        16px;

    transition:
        border-color
        0.2s
        ease,
        box-shadow
        0.2s
        ease;

}


.password-box:focus-within {

    border-color: #D99202;

    box-shadow:
        0
        0
        0
        3px
        rgba(
            217,
            146,
            2,
            0.12
        );

}


.password-box.password-error {

    border-color: #54100F;

}


.password-icon {

    color: #777777;

    flex-shrink: 0;

}


.password-box input {

    flex: 1;

    min-width: 0;

    border: none;

    outline: none;

    background: transparent;

    padding:
        0
        12px;

    color: #000D12;

    font-size: 16px;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

}


.password-box input::placeholder {

    color: #A6A6A6;

}


/* ==========================================================
   EYE
========================================================== */

.eye-btn {

    width: 40px;

    height: 40px;

    border: none;

    background: none;

    display: flex;

    justify-content: center;

    align-items: center;

    cursor: pointer;

    color: #666666;

}


.eye-btn:hover:not(:disabled) {

    color: #D99202;

}


/* ==========================================================
   ERROR
========================================================== */

.error-text {

    margin:
        16px
        0
        0;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    color: #54100F;

    font-size: 14px;

    font-weight: 600;

}


/* ==========================================================
   BUTTONS
========================================================== */

.modal-buttons {

    margin-top: 28px;

    display: flex;

    justify-content: flex-end;

    gap: 12px;

}


.cancel-btn {

    width: 120px;

    height: 46px;

    border:
        1px
        solid
        #D5D5D5;

    background: #FFFFFF;

    border-radius: 10px;

    cursor: pointer;

    color: #233E47;

    font-weight: 600;

}


.cancel-btn:hover:not(:disabled) {

    background: #F5F5F5;

}


.continue-btn {

    width: 170px;

    height: 46px;

    border: none;

    border-radius: 10px;

    background: #58761C;

    color: #FFFFFF;

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 8px;

    cursor: pointer;

    font-weight: 600;

}


.continue-btn:hover:not(:disabled) {

    background: #496218;

}


.continue-btn:disabled,
.cancel-btn:disabled,
.eye-btn:disabled {

    opacity: 0.6;

    cursor: not-allowed;

}


/* ==========================================================
   REMOVE
========================================================== */

.remove-modal h2 {

    text-align: center;

    font-family:
        'Lora',
        serif;

    font-size: 32px;

    font-weight: 600;

    color: #54100F;

    margin:
        0
        0
        10px;

}


.remove-description {

    margin: 0;

    text-align: center;

    color: #666666;

    font-size: 13px;

    line-height: 1.6;

}


.remove-account-item {

    padding:
        18px
        4px;

}


.remove-list-icon {

    color: #54100F;

    flex-shrink: 0;

}


/* ==========================================================
   CONFIRM
========================================================== */

.confirm-icon {

    width: 58px;

    height: 58px;

    margin:
        0
        auto
        18px;

    border-radius: 50%;

    background:
        rgba(
            84,
            16,
            15,
            0.08
        );

    color: #54100F;

    display: flex;

    justify-content: center;

    align-items: center;

}


.confirm-modal h2 {

    margin:
        0
        0
        16px;

    font-family:
        'Lora',
        serif;

    font-size: 28px;

    color: #54100F;

}


.confirm-modal p {

    color: #666666;

    font-size: 16px;

    line-height: 1.7;

    margin:
        0
        0
        12px;

}


.confirm-modal small {

    display: block;

    color: #888888;

    font-size: 11px;

    line-height: 1.6;

}


.confirm-buttons {

    justify-content: center;

}


.remove-btn {

    width: 140px;

    height: 46px;

    border: none;

    border-radius: 10px;

    background: #54100F;

    color: #FFFFFF;

    cursor: pointer;

    font-weight: 600;

}


.remove-btn:hover {

    background: #3D0C0B;

}


/* ==========================================================
   SPINNER
========================================================== */

.spin {

    animation:
        spin
        0.8s
        linear
        infinite;

}


@keyframes spin {

    from {

        transform:
            rotate(
                0deg
            );

    }


    to {

        transform:
            rotate(
                360deg
            );

    }

}


/* ==========================================================
   POPUP
========================================================== */

@keyframes popup {

    from {

        opacity: 0;

        transform:
            translateY(
                12px
            )
            scale(
                0.96
            );

    }


    to {

        opacity: 1;

        transform:
            translateY(
                0
            )
            scale(
                1
            );

    }

}


/* ==========================================================
   FADE
========================================================== */

.fade-enter-active,
.fade-leave-active {

    transition:
        opacity
        0.25s
        ease;

}


.fade-enter-from,
.fade-leave-to {

    opacity: 0;

}


/* ==========================================================
   DEFAULT BUTTONS
========================================================== */

button {

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

}


button,
.account-item {

    transition:
        background-color
        0.2s
        ease,
        color
        0.2s
        ease,
        transform
        0.2s
        ease,
        border-color
        0.2s
        ease,
        box-shadow
        0.2s
        ease;

}


button:active:not(:disabled) {

    transform:
        scale(
            0.98
        );

}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (
    max-width: 768px
) {

    .logout-page {

        padding:
            25px
            16px;

    }


    .account-card {

        padding: 20px;

    }


    .account-card h2 {

        font-size: 30px;

    }


    .status-text {

        display: none;

    }


    .bottom-actions {

        flex-direction: column;

        gap: 8px;

    }


    .bottom-link {

        width: 100%;

        justify-content: flex-start;

        padding: 16px;

    }


    .modal-buttons {

        flex-direction: column;

    }


    .cancel-btn,
    .continue-btn,
    .remove-btn {

        width: 100%;

    }

}


@media (
    max-width: 480px
) {

    .account-card {

        padding: 16px;

    }


    .account-card h2 {

        font-size: 26px;

    }


    .avatar {

        width: 50px;

        height: 50px;

    }


    .info h3 {

        font-size: 17px;

    }


    .login-modal,
    .remove-modal,
    .confirm-modal {

        padding:
            25px
            20px;

    }

}

</style>