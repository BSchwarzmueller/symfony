<script setup lang="ts">

import {onMounted, ref} from "vue";
import axios from "axios";

interface LeaderBoardDataInterface {
  name: string,
  points: number
}

interface LeaderBoardInterface {
  apiUrl: string,
}

const props = defineProps<LeaderBoardInterface>()

const loading = ref<boolean>(true)
const stats = ref<LeaderBoardDataInterface[]>([])
const displayMessage = ref<string>('loading')

onMounted(async () => {
  try {
    const response = await axios.get(props.apiUrl)
    if (response.status === 200) {
      displayMessage.value = ''
      stats.value = response.data
    } else {
      displayMessage.value = 'Unsuccessfull API call. Response: ' + response.status + response.statusText
    }
  } catch ($e) {
    displayMessage.value = 'Something went wrong... ' + $e
  } finally {
    loading.value = false
  }
})

</script>

<template>
  <section class="content leaderboard">
    <h2>Leaderboard (Top 20)</h2>
    <table class="bl-table" v-if="!loading">
      <thead>
      <tr>
        <td>Spieler</td>
        <td>Punkte</td>
      </tr>
      </thead>
      <tbody v-for="stat in stats" :key="stat.name">
      <tr>
        <td> {{ stat.name }}</td>
        <td> {{ stat.points }}</td>
      </tr>
      </tbody>
    </table>
    <div v-else>
      <h3>{ displayMessage }}</h3>
    </div><br>
    <small>    <a href="">Alle anzeigen</a></small>
  </section>
</template>

<style scoped>

</style>