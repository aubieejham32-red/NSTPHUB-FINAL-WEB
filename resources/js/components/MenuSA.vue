<template>
  <div class="menu-wrapper">

    <!-- DASHBOARD -->
    <Link
      href="/superadmin/dashboard"
      class="menu-item"
      :class="{ active: active === 'dashboard' }"
    >
      <svg class="icon icon-grid" viewBox="0 0 24 24" fill="none">
        <rect x="3" y="3" width="7" height="7" rx="2" fill="currentColor"/>
        <rect x="14" y="3" width="7" height="7" rx="2" fill="currentColor"/>
        <rect x="3" y="14" width="7" height="7" rx="2" fill="currentColor"/>
        <rect x="14" y="14" width="7" height="7" rx="2" fill="currentColor"/>
      </svg>

      <span class="menu-label">
        DASHBOARD
      </span>
    </Link>

    <!-- UNIVERSITIES -->
    <Link
      href="/superadmin/universities"
      class="menu-item"
      :class="{ active: active === 'universities' }"
    >
      <svg class="icon" viewBox="0 0 24 24" fill="none">
        <path
          d="M12 3L1 9L12 15L21 10.09V17H23V9L12 3Z"
          fill="currentColor"
        />

        <path
          d="M5 12.18V17.18L12 21L19 17.18V12.18L12 16L5 12.18Z"
          fill="currentColor"
        />
      </svg>

      <span class="menu-label">
        UNIVERSITIES
      </span>
    </Link>

    <!-- USERS -->
    <Link
      href="/superadmin/university-administrators"
      class="menu-item"
      :class="{ active: active === 'users' }"
    >
      <svg class="icon" viewBox="0 0 24 24" fill="none">
        <path
          d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"
          fill="currentColor"
        />
      </svg>

      <span class="menu-label">
        USERS
      </span>
    </Link>

    <!-- PROFILE -->
    <Link
      href="/superadmin/profile"
      class="profile-upload-container"
      :class="{ active: active === 'profile' }"
    >
      <div class="profile-avatar-wrapper">

        <img
          :src="profileImage"
          alt="Profile"
          class="profile-avatar"
        />

        <div class="avatar-hover-overlay">
          <span>PROFILE</span>
        </div>

      </div>
    </Link>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

defineProps({
    active: String
})

const page = usePage()

const profileImage = computed(() => {

    const photo = page.props.auth?.administrator?.photo

    return photo
        ? `/storage/${photo}`
        : '/images/default-avatar.png'

})
</script>

<style scoped>

.menu-wrapper{
  display:flex;
  align-items:center;
  gap:60px;
}

/* MENU */

.menu-item{
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:12px;

  color:#233E47;
  text-decoration:none;

  transition:.2s;
}

.menu-item:hover{
  transform:translateY(-2px);
}

.menu-item.active{
  color:#D99202;
}

.icon{
  width:50px;
  height:50px;
}

.menu-label{
  font-family:'Plus Jakarta Sans',sans-serif;
  font-size:.85rem;
  font-weight:700;
  letter-spacing:1px;
}

/* PROFILE */

.profile-upload-container{
  text-decoration:none;
}

.profile-avatar-wrapper{
  position: relative;
  width: 82px;
  height: 82px;

  border-radius: 50%;
  overflow: hidden;

  border: 3px solid #233E47;
  transition: .2s;
}

.profile-avatar-wrapper:hover{
    transform: scale(1.05);
    border-color: #D99202;
}

.profile-avatar{

  width:100%;
  height:100%;

  object-fit:cover;

}

.profile-upload-container.active .profile-avatar-wrapper{
    border-color: #D99202;
}

.avatar-hover-overlay{

  position:absolute;

  inset:0;

  display:flex;
  align-items:center;
  justify-content:center;

  background:rgba(0,0,0,.45);

  opacity:0;

  transition:.2s;

}

.profile-avatar-wrapper:hover .avatar-hover-overlay{

  opacity:1;

}

.avatar-hover-overlay span{

  color:#fff;

  font-size:11px;

  font-weight:700;

}

@media(max-width:768px){

.menu-wrapper{

  gap:32px;

}

}

</style>