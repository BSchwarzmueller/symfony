<script setup lang="ts">
import {computed, ref} from 'vue'

interface GameInterface {
    id: number
    day: string
    time: string
    homeClub: { name: string }
    awayClub: { name: string }
    homeGoals: number | null
    awayGoals: number | null
}

const props = defineProps<{
    games: GameInterface[]
    matchday: number
    interaction: boolean
}>()

type GameGroup = { day: string; time: string; games: GameInterface[] }

const gameGroups = computed<GameGroup[]>(() => {
    const groups: GameGroup[] = []
    const indexByKey = new Map<string, number>()

    for (const g of props.games) {
        const key = `${g.day}|${g.time}`
        const idx = indexByKey.get(key)
        if (idx === undefined) {
            indexByKey.set(key, groups.length)
            groups.push({day: g.day, time: g.time, games: [g]})
        } else {
            groups[idx].games.push(g)
        }
    }
    return groups
})

interface Result {
    id: number
    homeGoals: number
    awayGoals: number
}

const results = ref<Result[]>(props.games.map((game: GameInterface): Result => ({
    id: game.id,
    homeGoals: 0,
    awayGoals: 0,
})))


const addHomeGoals = (amount: number, id: number) => {
    const game = results.value.find((g: Result) => g.id === id)
    if (!game) return
    game.homeGoals = Math.max(0, game.homeGoals + amount)
}

const addAwayGoals = (amount: number, id: number) => {
    const game = results.value.find((g: Result) => g.id === id)
    if (!game) return
    game.awayGoals = Math.max(0, game.awayGoals + amount)
}

const submit = () => {
    // todo: submit results via api save userId, results, etc.
}
</script>

<template>
    <div class="matchday-table">
        <div class="label">{{ props.matchday }}. Spieltag</div>

        <div
            v-for="group in gameGroups"
            :key="group.day + '|' + group.time"
            class="datetime-group"
        >
            <div class="date">
                {{ group.day }} {{ group.time }}
            </div>

            <div v-for="game in group.games" :key="game.id" class="game">
                <div class="wrapper">
                    <!-- Home -->
                    <div class="home">
                        <div v-if="props.interaction" class="bet-home">
                            <span @click="addHomeGoals(1, game.id)">+</span>
                            <span @click="addHomeGoals(-1, game.id)">-</span>
                        </div>
                        <div class="home-club">{{ game.homeClub.name }}</div>
                        <div class="home-goals">
                            {{
                                props.interaction
                                    ? results.find((r: Result) => r.id === game.id)?.homeGoals
                                    : game.homeGoals
                            }}
                        </div>
                    </div>

                    <div>:</div>

                    <!-- Away -->
                    <div class="away">
                        <div class="away-goals">
                            {{
                                props.interaction
                                    ? results.find((r: Result) => r.id === game.id)?.awayGoals
                                    : game.awayGoals
                            }}
                        </div>
                        <div class="away-club">{{ game.awayClub.name }}</div>
                        <div v-if="props.interaction" class="bet-away">
                            <span @click="addAwayGoals(1, game.id)">+</span>
                            <span @click="addAwayGoals(-1, game.id)">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="props.interaction" class="submit-group">
            <button @click="submit">Submit</button>
        </div>
    </div>
</template>
