<template>
    <!--
        ModalField — wraps a form field inside the "add measurement" modal.
        Provides a label, a slot for the input, and a "not measured" checkbox.
    -->
    <div>
        <!-- Field label (ex: "Rythme cardiaque (bpm)") -->
        <label class="mb-2 block text-[18px] font-semibold text-slate-700">
            {{ label }}
        </label>

        <!-- The actual input is injected here from the parent via default slot -->
        <slot />

        <!-- "Not measured today" checkbox — hides/disables the input when checked -->
        <label
            class="mt-2 inline-flex items-center gap-2 text-[14px] text-slate-600"
        >
            <input
                type="checkbox"
                class="h-4 w-4 rounded border-slate-400"
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
    label: String,     // The field title shown above the input
    skip: Boolean,     // Whether the "not measured" checkbox is checked
    skipLabel: String, // Text for the checkbox (ex: "Je n'ai pas mesuré aujourd'hui")
});

// Supports v-model:skip syntax from the parent
defineEmits(["update:skip"]);
</script>
