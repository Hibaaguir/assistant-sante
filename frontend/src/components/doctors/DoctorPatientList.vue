<template>
    <div>
        <!-- Statistics cards -->
        <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="card in statisticsCards"
                :key="card.key"
                class="rounded-[18px] border bg-white px-5 py-6 shadow-[0_1px_3px_rgba(15,23,42,0.05)]"
                :class="card.borderClass"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-lg font-semibold text-black">
                            {{ card.label }}
                        </p>
                        <p
                            class="mt-4 text-4xl font-bold leading-none text-black"
                        >
                            {{ card.value }}
                        </p>
                    </div>
                    <div
                     class="grid place-items-center h-12 w-12 shrink-0 rounded-[15px]"
                        :class="card.iconWrapClass"
                    >
                        <component
                            :is="card.icon"
                            class="size-6"
                            :class="card.iconClass"
                        />
                    </div>
                </div>
            </article>
        </section>

        <!-- Search & filters -->
        <section
            class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-4 shadow-[0_1px_4px_rgba(15,23,42,0.05)] sm:p-6"
        >
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center">
                <label class="relative block xl:flex-1">
                    <IconSearch
                        class="pointer-events-none absolute left-5 top-1/2 size-5 -translate-y-1/2 text-[#9aa5b7]"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher un patient..."
                        class="h-[50px] w-full rounded-[16px] border border-[#d1d7e1] bg-[#fdfdfd] pl-13 pr-4 text-[15px] text-[#1a2640] outline-none placeholder:text-[#96a2b4]"
                    />
                </label>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:w-auto">
                    <button
                        v-for="tab in PATIENT_TABS"
                        :key="tab.key"
                        type="button"
                        class="h-[50px] rounded-[15px] px-5 text-[15px] font-semibold transition"
                        :class="
                            activeTab === tab.key
                                ? tab.activeClass
                                : 'bg-[#f1f2f4] text-[#243657]'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Patient list -->
        <section class="mt-6 space-y-4">
            <article
                v-for="patient in filteredPatients"
                :key="patient.id"
                class="cursor-pointer rounded-[18px] border border-[#d4d9e1] bg-white px-6 py-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)] transition hover:border-[#c7d5f5] hover:shadow-[0_8px_18px_rgba(15,23,42,0.08)]"
                @click="$emit('open-patient', patient)"
            >
                <div class="flex items-start gap-4">
                    <!-- Avatar -->
                    <div
                        class="grid place-items-center h-[58px] w-[58px] shrink-0 rounded-full text-[18px] font-bold text-white"
                        :style="{ backgroundColor: patient.avatarColor }"
                    >
                        {{ patient.initials }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <!-- Name + status -->
                        <div class="flex items-center gap-3">
                            <Typography
                                tag="h3"
                                variant="h3-style"
                                class="text-[18px]"
                            >
                                {{ patient.name }}
                            </Typography>
                        </div>

                        <!-- Quick info -->
                        <div
                            class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[14px] text-[#3f4d66]"
                        >
                            <span>{{ patient.age }} ans</span>
                            <span class="text-[#9aa5b7]">•</span>
                            <span class="inline-flex items-center gap-1.5">
                                <IconClock class="size-4" />{{
                                    patient.lastSeen
                                }}
                            </span>
                            <span class="text-[#9aa5b7]">•</span>
                            <span>RDV : {{ patient.nextVisit }}</span>
                        </div>

                        <!-- Vital metrics -->
                        <div class="mt-4 flex flex-wrap gap-3">
                            <VitalBadge
                                :icon="IconHeart"
                                iconClass="text-[#ff2143]"
                                border="border-[#f4bcc3]"
                                bg="bg-[#fff5f6]"
                            >
                                {{ patient.heartRate }}
                            </VitalBadge>
                            <VitalBadge
                                :icon="IconWave"
                                iconClass="text-[#1454ff]"
                                border="border-[#aac8ff]"
                                bg="bg-[#eff6ff]"
                            >
                                {{ patient.bloodPressure }}
                            </VitalBadge>
                            <VitalBadge
                                v-if="patient.glucose"
                                :icon="IconDrop"
                                iconClass="text-[#ff3b30]"
                                border="border-[#f4bcc3]"
                                bg="bg-[#fff5f6]"
                            >
                                {{ patient.glucose }}
                            </VitalBadge>
                        </div>

                        <!-- Tags -->
                        <div
                            v-if="patient.tags?.length"
                            class="mt-4 flex flex-wrap gap-2"
                        >
                            <span
                                v-for="tag in patient.tags"
                                :key="tag"
                                class="inline-flex h-[26px] items-center rounded-full bg-[#f0f1f4] px-3 text-[14px] font-medium text-[#495972]"
                                >{{ tag }}</span
                            >
                        </div>
                    </div>
                </div>
            </article>

            <p
                v-if="!filteredPatients.length"
                class="rounded-[16px] border border-[#d4d9e1] bg-white px-5 py-5 text-[15px] text-[#5a6881]"
            >
                Aucun patient trouvé.
            </p>
        </section>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import {
    IconAlert,
    IconClock,
    IconDrop,
    IconHeart,
    IconHeartFilled,
    IconPulse,
    IconWave,
    IconSearch,
    IconUserOutline,
} from "@/components/doctors/DoctorIcons.js";

// ── Vital badge micro-component ──────────────────────────────────────────────
const VitalBadge = {
    props: ["icon", "iconClass", "border", "bg"],
    template: `
    <span class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border px-3 text-[14px] font-semibold text-[#102241]"
          :class="[border, bg]">
      <component :is="icon" class="size-4" :class="iconClass" />
      <slot />
    </span>
  `,
};

// ── Constants ────────────────────────────────────────────────────────────────
const PATIENT_TABS = [
    {
        key: "all",
        label: "Tous",
        activeClass:
            "bg-[#1454ff] text-white shadow-[0_6px_16px_rgba(20,84,255,0.25)]",
    },
    {
        key: "critical",
        label: "Critiques",
        activeClass:
            "bg-[#f80000] text-white shadow-[0_6px_16px_rgba(248,0,0,0.18)]",
    },
    {
        key: "watch",
        label: "Surveillance",
        activeClass:
            "bg-[#eb7b00] text-white shadow-[0_6px_16px_rgba(235,123,0,0.2)]",
    },
    {
        key: "stable",
        label: "Stables",
        activeClass:
            "bg-[#08b33b] text-white shadow-[0_6px_16px_rgba(8,179,59,0.2)]",
    },
];

const STAT_CONFIG = [
    {
        key: "total",
        label: "Total patients",
        valueClass: "text-[#031a46]",
        borderClass: "border-2 border-blue-500",
        icon: IconUserOutline,
        iconWrapClass: "bg-[#dbe9ff]",
        iconClass: "text-[#1454ff]",
    },
    {
        key: "critical",
        label: "Critiques",
        valueClass: "text-[#ff1f2d]",
        borderClass: "border-[#f3b8bb]",
        icon: IconAlert,
        iconWrapClass: "bg-[#fee3e5]",
        iconClass: "text-[#ff1f2d]",
    },
    {
        key: "watch",
        label: "Surveillance",
        valueClass: "text-[#ef7a00]",
        borderClass: "border-[#f0cb58]",
        icon: IconPulse,
        iconWrapClass: "bg-[#fff0c8]",
        iconClass: "text-[#ef7a00]",
    },
    {
        key: "stable",
        label: "Stables",
        valueClass: "text-[#07b33f]",
        borderClass: "border-[#b5e6c6]",
        icon: IconHeartFilled,
        iconWrapClass: "bg-[#d2f3de]",
        iconClass: "text-[#07b33f]",
    },
];

// ── Props / emits ─────────────────────────────────────────────────────────────
const props = defineProps({
    patients: { type: Array, default: () => [] },
});
defineEmits(["open-patient"]);

// ── Local state ───────────────────────────────────────────────────────────────
const search = ref("");
const activeTab = ref("all");

// ── Computed ──────────────────────────────────────────────────────────────────
const statisticsCards = computed(() => {
    const counts = {
        total: props.patients.length,
        critical: props.patients.filter((p) => p.status === "critical").length,
        watch: props.patients.filter((p) => p.status === "watch").length,
        stable: props.patients.filter((p) => p.status === "stable").length,
    };

    return STAT_CONFIG.map((cfg) => ({ ...cfg, value: counts[cfg.key] }));
});

const filteredPatients = computed(() => {
    const term = search.value.trim().toLowerCase();
    return props.patients.filter((p) => {
        const matchTab =
            activeTab.value === "all" || p.status === activeTab.value;
        const matchSearch =
            !term ||
            p.name.toLowerCase().includes(term) ||
            p.tags?.some((t) => t.toLowerCase().includes(term));
        return matchTab && matchSearch;
    });
});
</script>
