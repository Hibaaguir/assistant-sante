<template>
    <div
        class="cursor-pointer rounded-xl border-2 p-4 transition-all hover:shadow-md"
        :class="
            selected
                ? 'border-blue-400 bg-blue-50/50 shadow-sm'
                : 'border-gray-200 bg-white hover:border-gray-300'
        "
        @click="$emit('click')"
    >
        <div class="flex items-center gap-3">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-2xl border"
                :class="color"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path :d="icon" stroke-width="2" />
                </svg>
            </div>

            <p class="flex-1 text-sm font-semibold text-gray-700 leading-snug">{{ label }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    value: String,
    label: String,
    color: String,
    icon: String,
    selected: Boolean,
});

defineEmits(["click"]);
</script>
