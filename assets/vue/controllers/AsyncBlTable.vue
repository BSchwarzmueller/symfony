<script setup lang="ts">
import {ref, onMounted} from 'vue'
import axios from 'axios'

interface BlTableInterface {
    id: number,
    teamName: string,
    matches: number,
    won: number,
    draw: number,
    lost: number,
    goalDiff: number,
}

const props = defineProps<{
    apiUrl: string,
}>()

const loading = ref<boolean>(true)
const msg = ref<string>('')
const items = ref<BlTableInterface[]>([])

onMounted(async () => {
    try {
        const response = await axios.get(props.apiUrl)

        if (response.status === 200) {
            items.value = response.data
        } else {
            msg.value = 'Unsuccessfull API call. Response: ' + response.status
        }
    } catch (error) {
        msg.value = 'Error: ' + error
    } finally {
        loading.value = false
    }
})

</script>

<template>
  <h2>Tabelle</h2>
    <div v-if="loading">Loading...</div>
    <div v-else>
        {{ msg }}
        <div v-if="items.length > 0" class="table">
            <table class="bl-table">
                <thead>
                <tr>
                    <th></th>
                    <th class="td-number">S</th>
                    <th class="td-number">U</th>
                    <th class="td-number">N</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in items" :key="item.id">
                    <td>{{ item.teamName }}</td>
                    <td class="td-number">{{ item.won }}</td>
                    <td class="td-number">{{ item.draw }}</td>
                    <td class="td-number">{{ item.lost }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>

</style>
