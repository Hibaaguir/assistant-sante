<!-- components/FormField.vue -->
<template>
    <div>
        <!-- le style de input de page de login et creer compte -->
        <label class="block text-base font-semibold text-gray-800 mb-2">{{
            label
        }}</label>
        <div class="relative">
            <!-- Icône gauche via slot -->
            <svg
                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <slot name="icon" />
            </svg>
            <!-- Input ou contenu -->
            <slot />
        </div>
        <p v-if="error" class="mt-1.5 text-sm text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    error: { type: String, default: "" },
});
</script>
