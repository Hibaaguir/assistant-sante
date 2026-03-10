<template>
    <div
        class="flex items-start gap-3 rounded-xl px-2.5 py-2 transition-colors hover:bg-slate-100/70 sm:px-3 sm:py-2.5"
    >
        <div class="mt-1">
            <HealthIcon :name="icon" />
        </div>
        <div>
            <dt class="text-[13px] font-normal text-slate-500 sm:text-[14px]">
                {{ label }}
            </dt>
            <dd
                class="text-[18px] font-medium leading-none text-slate-900 sm:text-[20px]"
            >
                {{ value }}
            </dd>
        </div>
    </div>
</template>

<script setup>
import HealthIcon from "./HealthIcon.vue";

defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    icon: { type: String, required: true },
});
</script>
