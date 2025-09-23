<script setup lang="ts">
import {computed, onMounted, onUnmounted, onUpdated, ref} from 'vue';
import axios from "axios";
import BetTimer from "./BetTimer.vue";

const props = defineProps<{
    interaction: boolean,
    type: string;
    gameId: number;
    userId: number;
    homeClub: string;
    awayClub: string;
    homeGoals: number | null;
    awayGoals: number | null;
    competition: string;
    season: string;
    matchday: number;
    date: string;
    betHomeGoals: number;
    betAwayGoals: number;
    betStatus: string | null;
    betPoints: number | null;
    popBet: (gameId: number, homeGoals: number, awayGoals: number) => void;
}>();

interface CommunityBetInterface {
    homeGoals: number,
    awayGoals: number,
    numberOfVotes: number,
}

const homeGoals = ref<number>(props.betHomeGoals)
const awayGoals = ref<number>(props.betAwayGoals)
const homeGoalsBuffer = ref<number>(-1)
const awayGoalsBuffer = ref<number>(-1)
const loading = ref<boolean>(false)
const loadingMessage = ref<string>('')
const communityBet = ref<CommunityBetInterface | null>(null)
const showCommunityBet = ref<boolean>(false)

const add = (team: string, points: number) => {
    if (team === 'home') {
        if (homeGoals.value + points < 0) return
        homeGoals.value = homeGoals.value === null ? points : homeGoals.value + points
    } else {
        if (awayGoals.value + points < 0) return
        awayGoals.value = awayGoals.value === null ? points : awayGoals.value + points
    }
}
const submitBet = async () => {
    if (homeGoals.value < 0 || awayGoals.value < 0) return
    if (homeGoals.value === homeGoalsBuffer.value && awayGoals.value === awayGoalsBuffer.value) return

    homeGoalsBuffer.value = homeGoals.value
    awayGoalsBuffer.value = awayGoals.value

    loadingMessage.value = 'Betting...'
    loading.value = true

    try {
        const response = await axios.post('/bet/create', {
            userId: props.userId,
            gameId: props.gameId,
            homeGoals: homeGoals.value,
            awayGoals: awayGoals.value,
        })

        if (response.status === 201) {
            loadingMessage.value = response.data.message
        } else {
            loadingMessage.value = response.status + ' - Error submitting bet'
        }

    } catch (error) {
        loadingMessage.value = 'Error submitting bet'
        console.error('Error submitting bet:', error);
    } finally {
        await new Promise(r => setTimeout(r, 1000));
        loading.value = false
        props.popBet(props.gameId, homeGoals.value, awayGoals.value)
    }

}

const formattedDate = computed(() => {
    const date = new Date(props.date);

    return date.toLocaleDateString('de-DE', {
        weekday: 'short',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    })
})
const matchdayLabel = computed(() => {
    let label = ''
    if (props.competition === 'bl1') {
        label = '1. Bundesliga - ' + props.matchday + '. Spieltag'
    }
    if (props.competition === 'dfb') {
        label = 'DFB-Pokal - ' + props.matchday + '. Runde'
    }
    return label
});

const loadCommunityBet = async () => {

    const response = await axios.get('/game/stats/' + props.gameId)

    if (response.status === 200) {
        communityBet.value = response.data
    }

    showCommunityBet.value = true
}
const fmt = (v: number | null | undefined) => {
    if (v == null) return '-';
    const n = Number(v);
    return Number.isInteger(n) ? n.toString() : n.toFixed(2).replace(/\.?0+$/, '');
};
</script>

<template>
    <div class="bet-container">
        <div v-if="loading" class="loading">{{ loadingMessage }}</div>
        <div class="game-info">
            <div class="date">{{ formattedDate }}</div>
            <BetTimer v-if="interaction" :date="props.date"/>
            <div v-else></div>
            <div class="matchday-label">{{ matchdayLabel }}</div>
        </div>

        <div class="bet-game">
            <div class="home">{{ props.homeClub }}</div>
            <div class="result">
                <span class="number">{{ homeGoals === -1 ? '-' : homeGoals }}</span>
                <span class="separator">:</span>
                <span class="number">{{ awayGoals === -1 ? '-' : awayGoals }}</span>
            </div>
            <div class="away">{{ props.awayClub }}</div>
        </div>

        <div v-if="interaction" class="bet-result">
            <div class="home">
                <span class="addButton" @click="add('home', 1)">+</span>
                <span class="subButton" @click="add('home', -1)">-</span>
            </div>
            <div class="submit">
                <button
                    class="button"
                    :disabled="homeGoals < 0 || awayGoals < 0"
                    @click="submitBet()"
                >Tippen
                </button>
            </div>
            <div class="away">
                <span class="addButton" @click="add('away', 1)">+</span>
                <span class="subButton" @click="add('away', -1)">-</span>
            </div>
        </div>
        <div v-if="!interaction"
             class="bet-community"
        >
            <button v-if="!showCommunityBet" class="button" @click="loadCommunityBet()">Community Bet</button>
            <div class="bet-game" :class="{ 'show': showCommunityBet }">
                <div class="home">Community:</div>
                <div class="result">
                    {{ fmt(communityBet?.homeGoals) }} : {{ fmt(communityBet?.awayGoals) }}
                </div>
                <div class="away"> ( {{ communityBet?.numberOfVotes }} Votes )</div>
            </div>
        </div>
    </div>
</template>
