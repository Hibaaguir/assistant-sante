<template>
    <div class="flex items-center justify-between rounded-xl border-2 border-gray-200 bg-white px-4 py-4">
        <span class="text-sm font-semibold text-gray-700 leading-snug">{{ label }}</span>

        <label class="relative inline-flex cursor-pointer items-center">
            <input
                type="checkbox"
                class="peer sr-only"
                :checked="modelValue"
                @change="$emit('update:modelValue', $event.target.checked)"
            />
            <div class="h-8 w-14 rounded-full bg-gray-300 transition-colors peer-checked:bg-blue-400"></div>
            <div class="absolute left-1 h-6 w-6 rounded-full bg-white shadow transition-transform peer-checked:translate-x-6"></div>
        </label>
    </div>
</template>

<script setup>
defineProps({
    modelValue: Boolean,
    label: String,
});

defineEmits(["update:modelValue"]);
</script>
