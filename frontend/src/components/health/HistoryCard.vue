<template>
    <!--
        HistoryCard — compact display of a single vital sign from history.
        Used inside the "Historique des mesures" section of TabSignesVitaux.
    -->
    <article class="rounded-xl border px-4 py-3" :class="[border, bg]">
        <div class="flex items-center gap-3">
            <!-- Icon rendered from an SVG string (set via VITAL_META) -->
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl"
                :class="iconBg"
                v-html="icon"
            ></div>

            <div>
                <!-- Vital sign name -->
                <p class="text-[13px] leading-none text-slate-700">
                    {{ label }}
                </p>

                <!-- Measured value with unit -->
                <p
                    class="mt-2 text-[20px] font-semibold leading-none text-slate-900"
                >
                    {{ value }}
                    <span class="text-[18px] font-medium text-slate-700">
                        {{ unit }}
                    </span>
                </p>

                <!-- Status badge -->
                <span
                    class="mt-2 inline-block rounded-full bg-[#dff6e4] px-2.5 py-0.5 text-[12px] leading-none text-[#08aa48]"
                >
                    Normal
                </span>
            </div>
        </div>
    </article>
</template>

<script setup>
// Props spread from VITAL_META plus the measured value
defineProps({
    label: String,            // Display name (ex: "Rythme cardiaque")
    accent: String,           // Accent color (not used in template)
    bg: String,               // Tailwind background class
    border: String,           // Tailwind border class
    iconBg: String,           // Tailwind class for icon container
    icon: String,             // Raw SVG string for the icon
    value: [String, Number],  // The measured value to display
    unit: String,             // Unit label (ex: "bpm", "mmHg")
});
</script>
