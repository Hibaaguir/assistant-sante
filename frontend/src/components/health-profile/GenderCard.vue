<template>
    <!-- Gender selection card — click to choose Male or Female -->
    <label
        class="relative cursor-pointer rounded-xl border-2 p-4 transition-all hover:shadow-md"
        :class="cardClass"
    >
        <!-- Hidden radio input for accessibility (screen readers) -->
        <input
            class="sr-only"
            type="radio"
            name="sex"
            :checked="selected"
            @change="$emit('select')"
        />

        <div class="flex flex-col items-center gap-2">
            <!-- Gender icon (SVG paths are passed as an array from parent) -->
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

            <!-- Label text (ex: "Homme" or "Femme") -->
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

// Props passed from the parent component (HealthProfileStep1)
const props = defineProps({
    value: String,       // The value of this gender option (ex: "male", "female")
    label: String,       // The display text (ex: "Homme", "Femme")
    iconPaths: Array,    // Array of SVG path "d" strings for the icon
    selected: Boolean,   // Whether this card is currently selected
    hasError: Boolean,   // Whether the field has a validation error
});

// Tell Vue this component can emit a "select" event
defineEmits(["select"]);

// Compute the border/background color based on the current state
const cardClass = computed(() => {
    if (props.selected) {
        // Selected: purple border and light purple background
        return "border-blue-400 bg-blue-50/50 shadow-sm";
    }
    if (props.hasError) {
        // Error state: red border
        return "border-red-300 bg-white";
    }
    // Default state: gray border
    return "border-gray-200 bg-white hover:border-gray-300";
});
</script>
