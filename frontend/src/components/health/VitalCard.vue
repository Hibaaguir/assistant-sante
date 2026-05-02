<template>
    <article
        class="min-h-[162px] rounded-2xl border-2 border-slate-200 px-6 py-6 transition-all duration-300 hover:border-slate-300 hover:shadow-lg"
        :class="bg"
    >
        <div class="flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl"
                :class="iconBg"
                v-html="icon"
            ></div>
        </div>

        <p class="mt-4 text-[16px] leading-none text-slate-700">{{ label }}</p>

        <div class="mt-3 flex items-baseline gap-2">
            <slot name="value" />
        </div>
    </article>
</template>

<script setup>
defineProps({
    label: String,
    accent: String,
    bg: String,
    border: String,
    iconBg: String,
    icon: String,
});
</script>
