<template>
    <RouterLink :to="to" class="block">
        <div
            class="relative flex items-center gap-3 rounded-lg px-4 py-3 text-[20px] font-bold transition-all duration-300 cursor-pointer"
            :class="
                active
                    ? 'text-slate-900 border-2 border-blue-700 animate-border-pulse'
                    : 'text-slate-900 hover:bg-slate-100 hover:text-slate-900'
            "
        >
            <span
                class="inline-flex h-6 w-6 shrink-0 items-center justify-center"
                v-html="icon"
            />
            <span>{{ label }}</span>
        </div>
    </RouterLink>
</template>

<script setup>
defineProps({
    to: { type: Object, required: true },
    label: { type: String, required: true },
    icon: { type: String, required: true },
    active: { type: Boolean, default: false },
});
</script>

<style scoped>
@keyframes border-pulse {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(29, 78, 216, 0.7);
    }
    50% {
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.3);
    }
}

.animate-border-pulse {
    animation: border-pulse 2s infinite;
}
</style>
