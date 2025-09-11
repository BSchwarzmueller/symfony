<script setup lang="ts">
interface GameInterface {
    id: number,
    day: string,
    time: string,
    homeClub: {
        name: string,
    },
    awayClub: {
        name: string,
    },
    homeGoals: number,
    awayGoals: number,
}

const props = defineProps<{
    games: GameInterface[],
}>()
</script>

<template>
    <div class="matchday-table">
        <div v-for="game in props.games" :key="game.id" class="game">
            <div class="date">
                {{ game.day }} {{ game.time }}
            </div>
            <div class="wrapper">
                <div class="home">
                    <div class="bet-home">+ -</div>
                    <div class="home-club">{{ game.homeClub.name }}</div>
                    <div class="home-goals">{{ game.homeGoals ?? '-'}}</div>
                </div>
                <div>vs</div>
                <div class="away">
                    <div class="away-goals">{{ game.awayGoals ?? '-' }}</div>
                    <div class="away-club">{{ game.awayClub.name }}</div>
                    <div class="bet-away">+ -</div>
                </div>
            </div>
        </div>
    </div>
</template>


<style scoped>

</style>
