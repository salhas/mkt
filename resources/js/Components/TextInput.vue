<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-brand-500 dark:focus:border-brand-500 focus:ring-brand-500 dark:focus:ring-brand-500 text-sm py-2 px-3 transition-colors duration-150"
        v-model="model"
        ref="input"
    />
</template>
