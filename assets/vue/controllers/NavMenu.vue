<script setup lang="ts">
const props = defineProps<{ homePath: string, }>()
</script>

<template>
    <a class="logo" :href="props.homePath">LOGO</a>
        <ul>
            <li><a :href="props.homePath">LEADERBOARD</a></li>
            <li><a :href="props.homePath">...</a></li>
        </ul>
</template>
