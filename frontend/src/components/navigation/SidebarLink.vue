<template>
    <RouterLink :to="to" class="block">
        <div
            class="relative flex items-center gap-3 rounded-lg px-4 py-3 text-[15px] font-medium transition-all duration-150 cursor-pointer"
            :class="
                active
                    ? 'bg-blue-600 text-white shadow-sm shadow-blue-200'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
            "
        >
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
