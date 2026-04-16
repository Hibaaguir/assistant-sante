<template>
    <div
        class="flex items-start gap-4 rounded-xl px-3 py-3 transition-colors hover:bg-slate-100/70 sm:px-4 sm:py-4"
    >
        <div class="mt-1">
            <IconeSante :name="icon" />
        </div>
        <div class="flex-1">
            <dt class="text-base font-semibold text-slate-600 sm:text-lg mb-1">
                {{ label }}
            </dt>
            <dd
                class="text-lg font-semibold leading-none text-slate-900 sm:text-xl"
            >
                {{ value }}
            </dd>
        </div>
    </div>
</template>

<script setup>
import IconeSante from "./HealthIcon.vue";

defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    icon: { type: String, required: true },
});
</script>
