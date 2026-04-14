<template>
    <RouterLink :to="to" class="block">
        <div
            class="relative flex items-center gap-3 rounded-lg px-4 py-3.5 text-[17px] font-medium transition-all duration-150 cursor-pointer"
            :class="
                active
                    ? 'bg-blue-50 text-blue-600'
                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'
            "
        >
            <!-- Indicateur gauche actif -->
            <span
                v-if="active"
                class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-full bg-blue-600"
            />

            <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center" v-html="icon" />
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
