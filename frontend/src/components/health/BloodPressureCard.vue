<template>
    <article
        :class="[
            'min-h-[162px] rounded-2xl border px-6 py-6 transition',
            border,
            bg,
            canEdit ? 'cursor-pointer hover:shadow-md' : '',
        ]"
    >
        <!-- Header: Icon + Status -->
        <div class="flex items-start justify-between">
            <div
                :class="[
                    'flex h-12 w-12 items-center justify-center rounded-xl',
                    iconBg,
                ]"
                v-html="icon"
            />
            <span
                class="rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]"
            >
                Normal
            </span>
        </div>

        <!-- Label -->
        <p class="mt-4 text-[16px] leading-none text-slate-700">
            {{ label }}
        </p>

        <!-- Display / Edit Mode -->
        <div class="mt-3 flex items-baseline gap-2">
            <template v-if="isEditing">
                <!-- Edit Mode: Two separate number inputs -->
                <input
                    ref="systolicInput"
                    v-model.number="localSystolic"
                    type="number"
                    :min="systolicMin"
                    :max="systolicMax"
                    class="h-12 w-20 rounded-xl border-2 border-purple-500 bg-white px-2 text-[28px] font-semibold outline-none [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&]:[-moz-appearance:textfield]"
                    @input="updateSystolic"
                    @blur="handleBlur"
                    @keydown.enter="handleEnter"
                    @keydown.escape="cancelEdit"
                />

                <span class="text-[28px] font-semibold text-slate-500">/</span>

                <input
                    ref="diastolicInput"
                    v-model.number="localDiastolic"
                    type="number"
                    :min="diastolicMin"
                    :max="diastolicMax"
                    class="h-12 w-20 rounded-xl border-2 border-purple-500 bg-white px-2 text-[28px] font-semibold outline-none [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&]:[-moz-appearance:textfield]"
                    @input="updateDiastolic"
                    @blur="handleBlur"
                    @keydown.enter="handleEnter"
                    @keydown.escape="cancelEdit"
                />

                <span class="text-[14px] font-medium text-slate-400 ml-2">{{
                    unit
                }}</span>
            </template>

            <template v-else>
                <!-- Display Mode: separate clickable areas per value -->
                <span
                    class="text-[36px] font-bold leading-none text-slate-900"
                    :class="canEdit ? 'cursor-pointer' : ''"
                    @click="canEdit && focusSystolic()"
                >{{ systolic !== '' ? Number(systolic) : '--' }}</span>
                <span class="text-[36px] font-bold leading-none text-slate-500">/</span>
                <span
                    class="text-[36px] font-bold leading-none text-slate-900"
                    :class="canEdit ? 'cursor-pointer' : ''"
                    @click="canEdit && focusDiastolic()"
                >{{ diastolic !== '' ? Number(diastolic) : '--' }}</span>
                <span class="text-[14px] font-medium text-slate-400 ml-2">{{
                    unit
                }}</span>
            </template>
        </div>
    </article>
</template>

<script setup>
import { computed, ref, watch } from "vue";

const props = defineProps({
    label: { type: String, required: true },
    unit: { type: String, default: "mmHg" },
    accent: { type: String, required: true },
    bg: { type: String, required: true },
    border: { type: String, required: true },
    iconBg: { type: String, required: true },
    icon: { type: String, required: true },
    systolic: { type: [String, Number], default: "" },
    diastolic: { type: [String, Number], default: "" },
    canEdit: { type: Boolean, default: true },
    // Validation ranges
    systolicMin: { type: Number, default: 50 },
    systolicMax: { type: Number, default: 300 },
    diastolicMin: { type: Number, default: 30 },
    diastolicMax: { type: Number, default: 220 },
});

const emit = defineEmits(["update:systolic", "update:diastolic"]);

// ─── Local State ──────────────────────────────────────────────────────────
const isEditing = ref(false);
const localSystolic = ref(props.systolic !== "" ? Number(props.systolic) : "");
const localDiastolic = ref(
    props.diastolic !== "" ? Number(props.diastolic) : "",
);
const systolicInput = ref(null);
const diastolicInput = ref(null);

// ─── Watchers: Sync props to local state ──────────────────────────────────
watch(
    () => props.systolic,
    (newVal) => {
        if (!isEditing.value) {
            localSystolic.value = newVal !== "" ? Number(newVal) : "";
        }
    },
);

watch(
    () => props.diastolic,
    (newVal) => {
        if (!isEditing.value) {
            localDiastolic.value = newVal !== "" ? Number(newVal) : "";
        }
    },
);

// ─── Computed ─────────────────────────────────────────────────────────────
const isValid = computed(() => {
    return (
        Number.isFinite(localSystolic.value) &&
        Number.isFinite(localDiastolic.value) &&
        localSystolic.value >= props.systolicMin &&
        localSystolic.value <= props.systolicMax &&
        localDiastolic.value >= props.diastolicMin &&
        localDiastolic.value <= props.diastolicMax
    );
});

// ─── Methods ──────────────────────────────────────────────────────────────
function focusSystolic() {
    if (!isEditing.value) {
        isEditing.value = true;
        setTimeout(() => systolicInput.value?.focus?.(), 0);
    }
}

function focusDiastolic() {
    if (!isEditing.value) {
        isEditing.value = true;
        setTimeout(() => diastolicInput.value?.focus?.(), 0);
    }
}

function updateSystolic() {
    // Allow empty, numeric values only
    if (localSystolic.value === "" || Number.isFinite(localSystolic.value)) {
        // Don't emit if empty
        if (localSystolic.value !== "") {
            emit("update:systolic", localSystolic.value);
        }
    }
}

function updateDiastolic() {
    // Allow empty, numeric values only
    if (localDiastolic.value === "" || Number.isFinite(localDiastolic.value)) {
        // Don't emit if empty
        if (localDiastolic.value !== "") {
            emit("update:diastolic", localDiastolic.value);
        }
    }
}

function handleEnter() {
    if (isValid.value) {
        exitEdit();
    }
}

function handleBlur() {
    // Exit edit mode if both values are valid
    if (isEditing.value && isValid.value) {
        setTimeout(() => exitEdit(), 50);
    }
}

function exitEdit() {
    if (isValid.value) {
        isEditing.value = false;
    }
}

function cancelEdit() {
    // Reset local state to props values
    localSystolic.value = props.systolic !== "" ? Number(props.systolic) : "";
    localDiastolic.value =
        props.diastolic !== "" ? Number(props.diastolic) : "";
    isEditing.value = false;
}
</script>
