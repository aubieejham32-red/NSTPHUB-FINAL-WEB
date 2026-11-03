<template>
    <div class="change-password-page">

        <!-- Header -->
        <TopHeaderSA />

        <main class="main-content">

            <!-- Menu -->
            <div class="menu-wrapper">
                <MenuSA active="profile" />
            </div>

            <section class="password-card">

                <!-- Close -->
                <button class="close-btn" @click="goBack">
                    <X :size="42" />
                </button>

                <h1>Change Password</h1>

                <p class="subtitle">
                    Update your password to keep your account secure.
                </p>

                <!-- Current Password -->

                <div class="form-group">

                    <label>Current Password</label>

                    <div class="input-wrapper">

                        <input
                            :type="showCurrent ? 'text' : 'password'"
                            v-model="form.current_password"
                            placeholder="enter current password"
                        >

                        <button
                            class="icon-btn"
                            type="button"
                            @click="showCurrent=!showCurrent"
                        >

                            <EyeOff v-if="!showCurrent" :size="20" />

                            <Eye v-else :size="20" />

                        </button>

                    </div>

                </div>

                <!-- New Password -->

                <div class="form-group">

                    <label>New Password</label>

                    <div class="input-wrapper">

                        <input
                            :type="showNew ? 'text' : 'password'"
                            v-model="form.password"
                            placeholder="enter new password"
                        >

                        <button
                            class="icon-btn"
                            type="button"
                            @click="showNew=!showNew"
                        >

                            <EyeOff v-if="!showNew" :size="20" />

                            <Eye v-else :size="20" />

                        </button>

                    </div>

                </div>

                <!-- Confirm Password -->

                <div class="form-group">

                    <label>Confirm Password</label>

                    <div class="input-wrapper">

                        <input
                            :type="showConfirm ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            placeholder="enter confirm password"
                        >

                        <button
                            class="icon-btn"
                            type="button"
                            @click="showConfirm=!showConfirm"
                        >

                            <EyeOff v-if="!showConfirm" :size="20" />

                            <Eye v-else :size="20" />

                        </button>

                    </div>

                </div>

                <div class="button-row">

                    <button
                        class="save-btn"
                        @click="savePassword"
                    >
                        Save Changes
                    </button>

                </div>

            </section>

        </main>

    </div>
</template>

<script>
import axios from "axios";
import { router } from "@inertiajs/vue3";

import TopHeaderSA from "@/components/TopHeaderSA.vue";
import MenuSA from "@/components/MenuSA.vue";

import {
    Eye,
    EyeOff,
    X
} from "lucide-vue-next";

export default {

    components: {

        TopHeaderSA,
        MenuSA,

        Eye,
        EyeOff,
        X

    },

    data(){

        return{

            form:{

                current_password:"",
                password:"",
                password_confirmation:""

            },

            showCurrent:false,
            showNew:false,
            showConfirm:false

        }

    },

    methods:{

        goBack(){

            router.visit("/superadmin/profile");

        },

        async savePassword(){

            try{

                await axios.put(
                    "/superadmin/profile/password",
                    this.form
                );

                alert("Password changed successfully.");

                router.visit("/superadmin/profile");

            }
            catch(error){

                if(error.response){

                    alert(error.response.data.message);

                }else{

                    alert("Unable to change password.");

                }

            }

        }

    }

}
</script>

<style scoped>

.change-password-page{

    min-height:100vh;
    background:#EFEBE2;

}

.main-content{

    padding:30px;

}

.menu-wrapper{

    display:flex;
    justify-content:flex-end;

    padding-right:25px;
    margin-bottom:35px;

}

.password-card{

    width:920px;
    max-width:95%;

    margin:auto;

    background:#fff;

    border:2px solid #7d7d7d;

    border-radius:18px;

    padding:55px;

    position:relative;

}

.close-btn{

    position:absolute;

    top:28px;
    right:28px;

    background:none;
    border:none;

    cursor:pointer;

    color:#54100F;

}

h1{

    color:#54100F;

    font-size:46px;

    font-weight:700;

    margin-bottom:12px;

}

.subtitle{

    color:#4D6572;

    font-size:22px;

    margin-bottom:45px;

}

.form-group{

    margin-bottom:28px;

}

label{

    display:block;

    color:#54100F;

    font-size:24px;

    margin-bottom:10px;

}

.input-wrapper{

    display:flex;
    align-items:center;

    border:2px solid #294553;

    border-radius:35px;

    height:60px;

    overflow:hidden;

}

.input-wrapper input{

    flex:1;

    border:none;

    outline:none;

    padding:0 22px;

    font-size:16px;

    background:white;

}

.icon-btn{

    width:60px;

    border:none;

    background:none;

    cursor:pointer;

    color:#294553;

    display:flex;
    align-items:center;
    justify-content:center;

}

.button-row{

    display:flex;
    justify-content:flex-end;

    margin-top:30px;

}

.save-btn{

    width:230px;

    height:55px;

    background:#58761C;

    color:white;

    border:none;

    border-radius:8px;

    font-size:20px;

    cursor:pointer;

    transition:.2s;

}

.save-btn:hover{

    background:#465f15;

}

@media(max-width:768px){

    .password-card{

        padding:30px;

    }

    h1{

        font-size:34px;

    }

    .subtitle{

        font-size:16px;

    }

}

</style>