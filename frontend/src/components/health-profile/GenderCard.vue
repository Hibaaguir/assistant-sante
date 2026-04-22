<template>
    <label
        class="relative cursor-pointer rounded-xl border-2 p-4 transition-all hover:shadow-md"
        :class="cardClass"
    >
        <input
            class="sr-only"
            type="radio"
            name="genre"
            :checked="selected"
            @change="$emit('select')"
        />

        <div class="flex flex-col items-center gap-2">
            <svg
                class="h-6 w-6"
                :class="selected ? 'text-blue-500' : 'text-gray-400'"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    v-for="d in iconPaths"
                    :key="d"
                    :d="d"
                    stroke-width="2"
                />
            </svg>

            <span
                class="font-medium"
                :class="selected ? 'text-blue-700' : 'text-gray-700'"
            >
                {{ label }}
            </span>
        </div>
    </label>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    value: String,
    label: String,
    iconPaths: Array,
    selected: Boolean,
    hasError: Boolean,
});

defineEmits(["select"]);

const cardClass = computed(() => {
    if (props.selected) return "border-blue-400 bg-blue-50/50 shadow-sm";
    if (props.hasError) return "border-red-300 bg-white";
    return "border-gray-200 bg-white hover:border-gray-300";
});
</script>
