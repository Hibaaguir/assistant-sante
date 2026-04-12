<template>
    <!-- Goal selection card — user clicks to select or deselect a goal -->
    <div
        class="cursor-pointer rounded-xl border-2 p-6 transition-all hover:shadow-md"
        :class="
            selected
                ? 'border-purple-400 bg-purple-50/50 shadow-sm'
                : 'border-gray-200 bg-white hover:border-gray-300'
        "
        @click="$emit('click')"
    >
        <div class="flex items-center gap-4">
            <!-- Icon container with goal-specific color classes -->
            <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl border"
                :class="color"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <!-- Single SVG path for the goal icon -->
                    <path :d="icon" stroke-width="2" />
                </svg>
            </div>

            <!-- Goal label text -->
            <p class="flex-1 font-medium text-gray-900">{{ label }}</p>
        </div>
    </div>
</template>

<script setup>
// Props passed from the parent component
defineProps({
    value: String,    // The goal value (sent to backend)
    label: String,    // Display text (ex: "Bien-être général")
    color: String,    // Tailwind color classes for the icon background
    icon: String,     // SVG path "d" attribute for the goal icon
    selected: Boolean, // Whether this goal is currently selected
});

// Emit a click event when the user selects or deselects this goal
defineEmits(["click"]);
</script>
