<script setup lang="ts">
import {ref} from 'vue';
import {GameInterface} from "../types/interfaces";
import Bet from "./Bet.vue";

const props = defineProps<{
    games: GameInterface[],
    interaction: boolean,
}>();

const openGames = ref<GameInterface[]>(props.games.filter((game: GameInterface) => game.type === 'openGame'))
const openBets = ref<GameInterface[]>(props.games.filter((game: GameInterface) => game.type === 'openBet'))
const closedBets = ref<GameInterface[]>(props.games.filter((game: GameInterface) => game.type === 'closedBet'))

function moveToOpenBets(gameId: number, homeGoals: number, awayGoals: number) {
    const index = openGames.value.findIndex(g => g.id === gameId)
    if (index !== -1) {
        const [game] = openGames.value.splice(index, 1)
        game.type = 'openBet'
        game.betHomeGoals = homeGoals
        game.betAwayGoals = awayGoals
        openBets.value.unshift(game)
    }
}
</script>

<template>
    <div class="bet-wrapper">
        <h2 v-if="openGames.length > 0" class="bet-header">Offene Wetten <span>({{openGames.length }})</span></h2>
        <div v-for="game in openGames" :key="game.id">
            <Bet
                :pop-bet="moveToOpenBets"
                :interaction="props.interaction"
                :type="game.type"
                :game-id="game.id"
                :user-id="game.userId"
                :home-club="game.homeClub"
                :away-club="game.awayClub"
                :home-goals="game.homeGoals"
                :away-goals="game.awayGoals"
                :competition="game.competition"
                :season="game.season"
                :matchday="game.matchday"
                :date="game.date.date"
                :bet-home-goals="game.betHomeGoals"
                :bet-away-goals="game.betAwayGoals"
                :bet-status="game.betStatus"
                :bet-points="game.betPoints"
            />
        </div>
        <h2 v-if="openBets.length > 0" class="bet-header">Aktive Wetten <span>({{openBets.length }})</span></h2>
        <div v-for="game in openBets" :key="game.id">
            <Bet
                :pop-bet="moveToOpenBets"
                :interaction="false"
                :type="game.type"
                :game-id="game.id"
                :user-id="game.userId"
                :home-club="game.homeClub"
                :away-club="game.awayClub"
                :home-goals="game.homeGoals"
                :away-goals="game.awayGoals"
                :competition="game.competition"
                :season="game.season"
                :matchday="game.matchday"
                :date="game.date.date"
                :bet-home-goals="game.betHomeGoals"
                :bet-away-goals="game.betAwayGoals"
                :bet-status="game.betStatus"
                :bet-points="game.betPoints"
            />
        </div>
        <h2 v-if="closedBets.length > 0" class="bet-header">Vergangene Wetten <span>({{closedBets.length }})</span></h2>
        <div v-for="game in closedBets" :key="game.id">
            <Bet
                :pop-bet="moveToOpenBets"
                :interaction="props.interaction"
                :type="game.type"
                :game-id="game.id"
                :user-id="game.userId"
                :home-club="game.homeClub"
                :away-club="game.awayClub"
                :home-goals="game.homeGoals"
                :away-goals="game.awayGoals"
                :competition="game.competition"
                :season="game.season"
                :matchday="game.matchday"
                :date="game.date.date"
                :bet-home-goals="game.betHomeGoals"
                :bet-away-goals="game.betAwayGoals"
                :bet-status="game.betStatus"
                :bet-points="game.betPoints"
            />
        </div>
    </div>
</template>
