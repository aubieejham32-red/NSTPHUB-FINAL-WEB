<template>
    <div class="layout">

        <TopHeaderSA />

        <main class="main-content">

            <div class="navigation">
                <MenuSA :active="active" />
            </div>

            <div class="page-content">
                <slot />
            </div>

        </main>

    </div>
</template>

<script setup>
import TopHeaderSA from '@/components/TopHeaderSA.vue'
import MenuSA from '@/components/MenuSA.vue'

defineProps({
    active: String
})
</script>

<style scoped>
.layout{
    min-height:100vh;
    background:#EFEBE2;
    font-family:'Plus Jakarta Sans', sans-serif;
}

.main-content{
    width:100%;
}

.navigation{
    display:flex;
    justify-content:flex-end;
    padding:30px 80px 0;
}

.page-content{
    padding:40px 80px 80px;
}

@media (max-width:768px){

.navigation{
    justify-content:center;
    padding:25px;
}

.page-content{
    padding:25px;
}

}
</style>