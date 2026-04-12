<template>
    <!--
        VitalCard — shows a single vital sign (heart rate, oxygen, etc.)
        - Displays the current value in read-only mode via the #value slot
        - Switches to an input field when isEditing is true
        - The parent controls editing state; we just emit events
    -->
    <article
        class="min-h-[162px] rounded-2xl border px-6 py-6 cursor-pointer transition"
        :class="[border, bg, canEdit ? 'hover:shadow-md' : '']"
    >
        <!-- Top row: icon on the left, status badge on the right -->
        <div class="flex items-start justify-between">
            <!-- Icon rendered from an SVG string (set by the parent via VITAL_META) -->
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl"
                :class="iconBg"
                v-html="icon"
            ></div>

            <!-- Status badge (always "Normal" for now) -->
            <span
                class="rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]"
            >
                Normal
            </span>
        </div>

        <!-- Vital sign name (ex: "Rythme cardiaque") -->
        <p class="mt-4 text-[16px] leading-none text-slate-700">{{ label }}</p>

        <!-- Value area: click to edit, or show the slot content -->
        <div
            class="mt-3 flex items-baseline gap-2 cursor-pointer"
            @click="canEdit && $emit('edit')"
        >
            <!-- Editing mode: show a number input -->
            <input
                v-if="isEditing"
                :value="localValue"
                type="number"
                class="h-12 w-32 rounded-xl border-2 border-purple-500 bg-white px-3 text-[32px] font-semibold outline-none [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                autofocus
                @input="handleInput"
                @blur="$emit('blur')"
            />

            <!-- Read-only mode: display value from parent slot -->
            <slot v-else name="value" />
        </div>
    </article>
</template>

<script setup>
import { ref, watch } from "vue";

// Props coming from the parent (spread from VITAL_META + extra props)
const props = defineProps({
    label: String,               // Display name (ex: "Rythme cardiaque")
    accent: String,              // Accent color hex (not used in template directly)
    bg: String,                  // Tailwind background class
    border: String,              // Tailwind border class
    iconBg: String,              // Tailwind class for icon container
    icon: String,                // Raw SVG string for the icon
    displayValue: [String, Number], // Current value from the parent's draft
    unit: String,                // Unit label (ex: "bpm", "%")
    canEdit: Boolean,            // Whether inline editing is allowed
    isEditing: Boolean,          // Whether this card is currently in edit mode
});

// Events this component can emit to the parent
const emit = defineEmits(["edit", "blur", "update:value"]);

// Local copy of the value for the input field
const localValue = ref(props.displayValue ?? "");

// When the parent changes displayValue (e.g. after save), sync localValue
watch(
    () => props.displayValue,
    (newVal) => {
        localValue.value = newVal ?? "";
    },
);

// Called on every keystroke inside the input
function handleInput(event) {
    localValue.value = event.target.value;
    // Tell the parent about the new value
    emit("update:value", event.target.value);
}
</script>
