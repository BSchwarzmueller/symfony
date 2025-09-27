<script setup lang="ts">
const props = defineProps<{
  homePath: string,
  userBetsPath: string,
}>()
console.log(props.userBetsPath)
</script>

<template>
  <a class="logo" :href="props.homePath">WomBet</a>
  <ul>
    <li v-if="props.userBetsPath !== null">
      <a :href="props.userBetsPath">
        <button class="button">Meine Wetten</button>
      </a>
    </li>
  </ul>
</template>
