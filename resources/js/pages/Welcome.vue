<script setup lang="ts">
import { ref } from 'vue';

const greeting = ref('Welcome to Your Vue.js + TypeScript App');

</script>

<template>
    <h1 class="text-3xl font-bold underline">
        {{greeting}}
    </h1>
</template>
