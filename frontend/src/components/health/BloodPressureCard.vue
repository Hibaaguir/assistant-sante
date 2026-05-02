<template>
    <article
        :class="[
            'min-h-[162px] rounded-2xl border-2 border-slate-200 px-6 py-6 transition-all duration-300 hover:border-slate-300 hover:shadow-lg',
            bg,
        ]"
    >
        <!-- Header: Icon -->
        <div class="flex items-start justify-between">
            <div
                :class="[
                    'flex h-12 w-12 items-center justify-center rounded-xl',
                    iconBg,
                ]"
                v-html="icon"
            />
        </div>

        <!-- Label -->
        <p class="mt-4 text-[16px] leading-none text-slate-700">
            {{ label }}
        </p>

        <!-- Valeurs -->
        <div class="mt-3 flex items-baseline gap-2">
            <span class="text-[36px] font-bold leading-none text-slate-900">
                {{ systolic !== "" && systolic != null ? Number(systolic) : "--" }}
            </span>
            <span class="text-[36px] font-bold leading-none text-slate-500">/</span>
            <span class="text-[36px] font-bold leading-none text-slate-900">
                {{ diastolic !== "" && diastolic != null ? Number(diastolic) : "--" }}
            </span>
            <span class="text-[16px] font-semibold text-slate-700 ml-1">{{ unit }}</span>
        </div>
    </article>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    unit: { type: String, default: "mmHg" },
    accent: { type: String, required: true },
    bg: { type: String, required: true },
    border: { type: String, required: true },
    iconBg: { type: String, required: true },
    icon: { type: String, required: true },
    systolic: { type: [String, Number], default: "" },
    diastolic: { type: [String, Number], default: "" },
});
</script>
