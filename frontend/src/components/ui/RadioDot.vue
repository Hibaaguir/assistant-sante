<!-- Composant de point radio personnalisé pour indiquer la sélection dans les options de filtrage du journal -->
<template>
    <div class="flex items-center justify-center">
        <div
            :class="[
                'h-5 w-5 rounded-full border-2 transition-all',
                active
                    ? 'border-violet-500 bg-violet-500'
                    : 'border-slate-300 bg-white',
            ]"
        >
            <div
                v-if="active"
                class="h-full w-full flex items-center justify-center rounded-full"
            >
                <div class="h-2 w-2 rounded-full bg-white" />
            </div>
        </div>
    </div>
</template>
<script setup>
defineProps({
    active: { type: Boolean, default: false },
});
</script>
