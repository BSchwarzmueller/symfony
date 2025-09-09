<script setup>
const props = defineProps({
    name: String,
    profilePath: String,
    loginPath: String,
})
</script>

<template>
    <div class="main-nav__profile">
        <div v-if="props.name === null && props.profilePath === null">
            <a :href="props.loginPath">Login</a>
        </div>
        <div v-else>
            <a :href="props.profilePath">{{props.name}}</a>
        </div>
    </div>
</template>

<style scoped>

</style>
