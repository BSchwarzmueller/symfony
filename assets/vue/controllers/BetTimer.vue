<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{ date: string }>();

const remainingMs = ref(0);
let timer: number | undefined;

const parseTarget = () => new Date(props.date).getTime();

const tick = () => {
  const now = Date.now();
  const target = parseTarget();
  remainingMs.value = target - now;
  if (remainingMs.value <= 0 && timer) {
    remainingMs.value = 0;
    clearInterval(timer);
    timer = undefined;
  }
};

onMounted(() => {
    tick();
    timer = window.setInterval(tick, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

watch(() => props.date, () => {
    tick();
    if (!timer) timer = window.setInterval(tick, 1000);
});

const days = computed(() => Math.floor(Math.max(0, remainingMs.value) / 86400000));
const hours = computed(() => Math.floor((Math.max(0, remainingMs.value) % 86400000) / 3600000));
const minutes = computed(() => Math.floor((Math.max(0, remainingMs.value) % 3600000) / 60000));
const seconds = computed(() => Math.floor((Math.max(0, remainingMs.value) % 60000) / 1000));

const pad = (n: number) => n.toString().padStart(2, '0');

const formatted = computed(() => {
  if (remainingMs.value <= 0) return 'abgelaufen';
  if (days.value > 0) return `${days.value}d ${pad(hours.value)}:${pad(minutes.value)}:${pad(seconds.value)}`;
  return `${pad(hours.value)}:${pad(minutes.value)}:${pad(seconds.value)}`;
});

const classes = computed(() => {
  const ms = remainingMs.value;
  if (ms <= 0) return ['bet-timer', 'error'];
  if (ms <= 3600000) return ['bet-timer', 'warning'];
  return ['bet-timer', 'active'];
});
</script>

<template>
    <div :class="classes" :title="new Date(props.date).toLocaleString('de-DE')">
        {{ formatted }}
    </div>
</template>
