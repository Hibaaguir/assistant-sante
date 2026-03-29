<template>
    <RouterLink :to="to" class="block">
        <div
            class="flex items-center gap-3 rounded-[14px] px-5 py-3.5 text-[16px] font-semibold transition"
            :class="
                active
                    ? 'bg-white bg-opacity-80 text-purple-600 shadow-md shadow-white/40 rounded-r-3xl'
                    : 'text-purple-600 hover:bg-purple-200/40'
            "
        >
            <span
                class="inline-flex h-5 w-5 items-center justify-center"
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
