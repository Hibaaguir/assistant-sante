<template>
    <RouterLink :to="to" class="block">
        <div
            class="flex items-center gap-3 rounded-lg px-4 py-3.5 text-[15px] font-600 transition-all duration-200 cursor-pointer"
            :class="
                active
                    ? 'bg-blue-600 text-white shadow-md'
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
            "
        >
            <span
                class="inline-flex h-6 w-6 items-center justify-center"
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
