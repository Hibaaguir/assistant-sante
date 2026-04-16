<template>
    <!--
        ModalField — wraps a form field inside the "add measurement" modal.
        Provides a label, a slot for the input, and a "not measured" checkbox.
    -->
    <div>
        <!-- Field label (ex: "Rythme cardiaque (bpm)") -->
        <label class="mb-3 block text-base font-semibold text-slate-800">
            {{ label }}
        </label>

        <!-- The actual input is injected here from the parent via default slot -->
        <slot />

        <!-- "Not measured today" checkbox — hides/disables the input when checked -->
        <label
            class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-700 transition"
        >
            <input
                type="checkbox"
                class="h-5 w-5 rounded border-slate-400 accent-blue-500"
                :checked="skip"
                @change="$emit('update:skip', $event.target.checked)"
            />
            {{ skipLabel }}
        </label>
    </div>
</template>

<script setup>
// Props
defineProps({
    label: String, // The field title shown above the input
    skip: Boolean, // Whether the "not measured" checkbox is checked
    skipLabel: String, // Text for the checkbox (ex: "Je n'ai pas mesuré aujourd'hui")
});

// Supports v-model:skip syntax from the parent
defineEmits(["update:skip"]);
</script>
