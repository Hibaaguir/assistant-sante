<template>
    <div>
        <label class="mb-3 block text-base font-semibold text-slate-800">
            {{ label }}
        </label>

        <slot />

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
defineProps({
    label: String,
    skip: Boolean,
    skipLabel: String,
});

defineEmits(["update:skip"]);
</script>
