<template>
    <!-- Bouton qui bascule entre œil ouvert (mot de passe visible) et œil barré (masqué) -->
    <button
        type="button"
        @click="$emit('toggle')"
        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors cursor-pointer"
    >
        <!-- Œil ouvert : mot de passe masqué -->
        <svg v-if="!visible" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <!-- Œil barré : mot de passe visible -->
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m4.753-4.753L3 3m9.621 9.621L3 21m12.621-12.621l4.243-4.243m0 0a9 9 0 10-12.728 12.728m12.728-12.728L21 3" />
        </svg>
    </button>
</template>

<script setup>
// visible : true = mot de passe en clair, false = masqué
// toggle  : événement émis au clic, le parent inverse la valeur de visible
defineProps(["visible"]);
defineEmits(["toggle"]);
</script>
