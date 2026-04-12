<template>
    <!-- Toggle switch card — used for habits like smoking or alcohol -->
    <div
        class="flex items-center justify-between rounded-xl border-2 border-gray-200 bg-white px-4 py-4"
    >
        <!-- Label text (ex: "Fumeur", "Consommation d'alcool") -->
        <span class="text-sm font-medium text-gray-800">{{ label }}</span>

        <!-- Toggle switch (on/off) -->
        <label class="relative inline-flex cursor-pointer items-center">
            <!-- Hidden checkbox — drives the toggle state -->
            <input
                type="checkbox"
                class="peer sr-only"
                :checked="modelValue"
                @change="$emit('update:modelValue', $event.target.checked)"
            />

            <!-- Background track (gray when off, purple when on) -->
            <div
                class="h-8 w-14 rounded-full bg-gray-300 transition-colors peer-checked:bg-purple-400"
            ></div>

            <!-- Sliding knob (moves right when checked) -->
            <div
                class="absolute left-1 h-6 w-6 rounded-full bg-white shadow transition-transform peer-checked:translate-x-6"
            ></div>
        </label>
    </div>
</template>

<script setup>
// Props — this component supports v-model (modelValue + update:modelValue)
defineProps({
    modelValue: Boolean, // Current on/off state (from v-model)
    label: String,       // Display text for the habit
});

// Emit the v-model update event when the user toggles the switch
defineEmits(["update:modelValue"]);
</script>
