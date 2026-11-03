<template>
    <div class="profile-page">

        <!-- Header -->
        <TopHeaderSA />

        <main class="main-content">

            <!-- Menu -->
            <div class="menu-wrapper">
                <MenuSA active="profile" />
            </div>

            <!-- Profile Card -->
            <section class="profile-card">

                <!-- Photo -->
                <div class="photo-section">

                    <img :src="previewPhoto || profileImage" class="profile-photo" />

                    <label class="change-photo-btn">

                        {{ administrator.photo ? 'Change Photo' : 'Add Photo' }}

                        <input type="file" accept="image/*" hidden @change="changePhoto" />

                    </label>

                </div>

                <!-- Name -->
                <h1 class="profile-name">
                    {{ administrator.name }}
                </h1>

                <!-- Username -->
                <div class="username-box">
                    {{ administrator.username }}
                </div>

                <!-- Email -->
                <p class="profile-email">
                    {{ administrator.email }}
                </p>

                <!-- Buttons -->

                <button class="password-btn" @click="changePassword">
                    CHANGE PASSWORD
                </button>

                <button class="logout-btn" @click="logout">
                    LOGOUT
                </button>

            </section>

        </main>

    </div>
</template>

<script>
import axios from "axios";
import { router } from "@inertiajs/vue3";
import TopHeaderSA from '@/components/TopHeaderSA.vue'
import MenuSA from '@/components/MenuSA.vue'



export default {

    components: {
        TopHeaderSA,
        MenuSA
    },

    data() {
        return {

            administrator: {
                id: null,
                name: '',
                username: '',
                email: '',
                photo: null,
            },

            previewPhoto: null

        }
    },

    computed: {

        profileImage() {

            if (this.administrator.photo) {
                return "/storage/" + this.administrator.photo
            }

            return "/images/default-avatar.png"

        }

    },

    methods: {

        async changePhoto(event) {

            const file = event.target.files[0]

            if (!file) return

            // Preview immediately
            this.previewPhoto = URL.createObjectURL(file)

            const formData = new FormData()
            formData.append('photo', file)

            try {

                const { data } = await axios.post(
                    '/superadmin/profile/photo',
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                )

                // Update current page
                this.administrator.photo = data.photo

                // Refresh Inertia shared props
                router.reload({
                    only: ['auth']
                })

            } catch (error) {

                console.error(error)

                alert("Unable to upload photo.")

            }

        },

        async loadAdministrator() {

            try {

                const { data } = await axios.get('/superadmin/profile/data')

                this.administrator = data

            }
            catch (error) {

                console.error(error)

            }

        },

        changePassword() {

            router.visit("/superadmin/profile/change-password");

        },

        async logout() {

            try {

                const response = await axios.post(
                    '/superadmin/logout',
                    {},
                    {
                        headers: {
                            Accept: 'application/json'
                        }
                    }
                );

                const account = response.data?.account;

                if (account) {
                    const storageKey = 'savedSuperAdminAccounts';
                    let accounts = [];

                    try {
                        const raw = localStorage.getItem(storageKey);
                        accounts = raw ? JSON.parse(raw) : [];

                        if (!Array.isArray(accounts)) {
                            accounts = [];
                        }
                    } catch {
                        accounts = [];
                    }

                    const normalized = {
                        ...account,
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
                            '',
                        account_type: 'superadmin',
                    };

                    const index = accounts.findIndex(
                        item =>
                            Number(item?.id) === Number(normalized.id) ||
                            String(item?.email ?? '').toLowerCase() ===
                                String(normalized.email ?? '').toLowerCase()
                    );

                    if (index === -1) {
                        accounts.push(normalized);
                    } else {
                        accounts[index] = {
                            ...accounts[index],
                            ...normalized,
                        };
                    }

                    localStorage.setItem(
                        storageKey,
                        JSON.stringify(accounts)
                    );
                }

                window.location.href =
                    response.data?.redirect ?? '/superadmin/logout';

            } catch (error) {

                console.error(error);
                alert('Unable to logout.');

            }

        },

    },

    mounted() {

        this.loadAdministrator()

    }



}
</script>

<style scoped>
.profile-page {

    min-height: 100vh;
    background: #EFEBE2;

}

/* Main */

.main-content {

    padding: 30px;

}

/* Menu */

.menu-wrapper {

    display: flex;
    justify-content: flex-end;
    align-items: center;

    width: 100%;

    margin-bottom: 35px;

    padding-right: 25px;

}

/* Card */

.profile-card {

    width: 900px;
    max-width: 95%;
    margin: auto;

    background: #FFFFFF;

    border: 2px solid #7A7A7A;

    border-radius: 22px;

    padding: 45px;

    display: flex;
    flex-direction: column;
    align-items: center;

}

/* Photo */

.photo-section {

    display: flex;
    flex-direction: column;
    align-items: center;

}

.profile-photo {

    width: 135px;
    height: 135px;

    object-fit: cover;

}

.change-photo-btn {

    margin-top: 15px;

    background: #233E47;

    color: white;

    border: 2px solid #FFBD36;

    border-radius: 30px;

    padding: 8px 25px;

    cursor: pointer;

    font-size: 13px;

}

/* Name */

.profile-name {

    margin-top: 25px;

    color: #54100F;

    font-size: 56px;

    font-weight: 700;

    text-align: center;

}

/* Username */

.username-box {

    margin-top: 18px;

    background: #EFEBE2;

    border: 1px solid #BEBEBE;

    padding: 12px 30px;

    color: #54100F;

    font-size: 24px;

}

/* Email */

.profile-email {

    margin-top: 15px;

    color: #54100F;

    font-style: italic;

    font-size: 28px;

}

/* Password Button */

.password-btn {

    width: 100%;

    margin-top: 60px;

    height: 70px;

    background: white;

    border: 3px solid #54100F;

    color: #54100F;

    font-size: 34px;

    cursor: pointer;

    transition: .2s;

}

.password-btn:hover {

    background: #54100F;

    color: white;

}

/* Logout */

.logout-btn {

    margin-top: 25px;

    width: 100%;

    height: 70px;

    background: #58761C;

    color: white;

    border: none;

    font-size: 36px;

    cursor: pointer;

    transition: .2s;

}

.logout-btn:hover {

    background: #435b13;

}

/* Responsive */

@media(max-width:768px) {

    .profile-name {

        font-size: 38px;

    }

    .profile-email {

        font-size: 18px;

    }

    .password-btn {

        font-size: 24px;

    }

    .logout-btn {

        font-size: 24px;

    }

}
</style>