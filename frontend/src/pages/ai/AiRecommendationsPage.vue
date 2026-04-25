<template>
    <div class="w-full px-4 py-8 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <Typography tag="h1" variant="h1-style">Recommandations IA</Typography>
                <Typography tag="h4" variant="h5-style">
                    Analyse intelligente de vos données de santé
                </Typography>
            </div>
            <button
                v-if="report"
                @click="load"
                :disabled="loading"
                class="shrink-0 flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:opacity-50"
            >
                <svg class="h-4 w-4" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Réanalyser
            </button>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="space-y-4">
            <div class="h-28 w-full animate-pulse rounded-2xl bg-slate-100"></div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="h-20 animate-pulse rounded-2xl bg-slate-100"></div>
                <div class="h-20 animate-pulse rounded-2xl bg-slate-100"></div>
                <div class="h-20 animate-pulse rounded-2xl bg-slate-100"></div>
            </div>
            <div class="h-48 animate-pulse rounded-2xl bg-slate-100"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="h-40 animate-pulse rounded-2xl bg-slate-100"></div>
                <div class="h-40 animate-pulse rounded-2xl bg-slate-100"></div>
            </div>
        </div>

        <!-- Error -->
        <div
            v-else-if="error"
            class="flex flex-col items-center gap-4 rounded-2xl border border-red-200 bg-red-50 p-10 text-center"
        >
            <svg class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <p class="font-semibold text-red-700">{{ error }}</p>
            <button @click="load" class="rounded-xl bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700">
                Réessayer
            </button>
        </div>

        <!-- Results -->
        <div v-else-if="report" class="space-y-6">

            <!-- Risk banner -->
            <div class="relative overflow-hidden rounded-2xl p-6 shadow-sm" :class="riskStyle.bg">
                <div class="flex items-center gap-5">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl shadow-inner ring-1" :class="riskStyle.iconBg">
                        <component :is="riskStyle.icon" class="h-8 w-8" :class="riskStyle.iconClass" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest" :class="riskStyle.label">Niveau de risque global</p>
                        <p class="text-2xl font-bold" :class="riskStyle.text">{{ riskLabel }}</p>
                        <p class="mt-1 text-sm leading-relaxed" :class="riskStyle.sub">{{ report.risk_summary }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats row -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-red-500">{{ report.alerts?.length ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500 uppercase tracking-wide">Alertes</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-orange-500">{{ report.anomalies?.length ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500 uppercase tracking-wide">Anomalies</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-blue-500">{{ report.global_recommendations?.length ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500 uppercase tracking-wide">Recommandations</p>
                </div>
            </div>

            <!-- No data message -->
            <p v-if="hasNoData" class="text-center text-lg font-bold text-slate-700">
                Données insuffisantes pour l'analyse
            </p>

            <!-- Alerts -->
            <section v-if="report.alerts?.length">
                <SectionTitle
                    :icon="IconAlert"
                    title="Alertes urgentes"
                    icon-wrap-class="border-red-200 bg-red-50"
                    icon-class="text-red-600"
                />
                <div class="space-y-3">
                    <div
                        v-for="(alert, i) in report.alerts"
                        :key="i"
                        class="flex gap-4 rounded-2xl border border-red-200 bg-red-50 p-4"
                    >
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-red-200 bg-red-100 shadow-sm">
                            <IconAlert class="h-5 w-5 text-red-600" />
                        </span>
                        <div>
                            <p class="font-semibold text-red-800">{{ alert.message }}</p>
                            <p class="mt-1 text-sm text-red-600">→ {{ alert.suggested_action }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Anomalies -->
            <section v-if="sortedAnomalies.length">
                <SectionTitle
                    :icon="IconPulse"
                    title="Anomalies détectées"
                    icon-wrap-class="border-amber-200 bg-amber-50"
                    icon-class="text-amber-600"
                />
                <div class="space-y-3">
                    <div
                        v-for="(a, i) in sortedAnomalies"
                        :key="i"
                        class="flex items-start gap-3 rounded-2xl border bg-white p-4 shadow-sm"
                        :class="severityBorder(a.severity)"
                    >
                        <span class="shrink-0 rounded-full px-3 py-1 text-sm font-bold uppercase mt-0.5" :class="severityBadge(a.severity)">
                            {{ severityFr(a.severity) }}
                        </span>
                        <p class="text-base font-medium leading-relaxed text-slate-700">{{ a.message }}</p>
                    </div>
                </div>
            </section>

            <!-- Recommandations prioritaires -->
            <section v-if="report.global_recommendations?.length">
                <SectionTitle
                    :icon="IconCheckCircle"
                    title="Recommandations prioritaires"
                    icon-wrap-class="border-emerald-200 bg-emerald-50"
                    icon-class="text-emerald-600"
                />
                <div class="space-y-3">
                    <div
                        v-for="rec in report.global_recommendations"
                        :key="rec.priority"
                        class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow">
                            {{ rec.priority }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800">{{ rec.action }}</p>
                            <p class="mt-1 text-sm text-slate-500">
                                <span class="inline-block rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 mr-1">
                                    {{ domainLabel(rec.domain) }}
                                </span>
                                {{ rec.impact }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Domain analyses toggle -->
            <div v-if="activeDomains.length" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white shadow-sm">
                            <IconChartBars class="h-5 w-5 text-slate-600" />
                        </span>
                        <div>
                            <p class="font-semibold text-slate-800">Rapport clinique détaillé</p>
                            <p class="text-sm text-slate-500">{{ activeDomains.length }} domaine{{ activeDomains.length > 1 ? 's' : '' }} analysé{{ activeDomains.length > 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <button
                        @click="showDomains = !showDomains"
                        class="shrink-0 flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm border border-slate-200 transition hover:bg-slate-100"
                    >
                        <span>{{ showDomains ? 'Masquer' : 'Consulter' }}</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': showDomains }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                <!-- Expandable domain list -->
                <div v-if="showDomains" class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <div
                        v-for="[domain, data] in activeDomains"
                        :key="domain"
                        class="px-5 py-5"
                    >
                        <!-- Domain header -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border shadow-sm"
                                    :class="domainIconStyle(domain).wrap"
                                >
                                    <component :is="domainIcon(domain)" class="h-4 w-4" :class="domainIconStyle(domain).icon" />
                                </span>
                                <p class="font-semibold text-slate-800">{{ domainLabel(domain) }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="severityBadge(data.severity)">
                                {{ severityFr(data.severity) }}
                            </span>
                        </div>

                        <!-- Analysis text -->
                        <p class="text-sm leading-relaxed text-slate-600">{{ data.analysis }}</p>

                        <!-- Issues -->
                        <ul v-if="data.issues?.length" class="mt-3 space-y-1.5 border-t border-slate-100 pt-3">
                            <li
                                v-for="(issue, i) in data.issues"
                                :key="i"
                                class="flex gap-2 text-sm text-slate-600"
                            >
                                <span class="shrink-0 mt-1 h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                <span>{{ issue }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";
import Typography from "@/components/ui/Typography.vue";
import {
    IconAlert,
    IconChartBars,
    IconCheckCircle,
    IconClock,
    IconClipboardList,
    IconFlask,
    IconHeart,
    IconLeaf,
    IconPill,
    IconPulse,
    IconRun,
    IconSmoke,
    IconWineGlass,
} from "@/components/doctors/DoctorIcons.js";

// Inline section title helper
const SectionTitle = {
    props: ["icon", "title", "iconWrapClass", "iconClass"],
    template: `<div class="mb-3 flex items-center gap-2">
        <span class="grid h-10 w-10 place-items-center rounded-xl border shadow-sm"
              :class="iconWrapClass || 'border-slate-200 bg-white'">
            <component :is="icon" class="h-5 w-5" :class="iconClass || 'text-slate-600'" />
        </span>
        <h2 class="text-xl font-bold text-slate-800">{{ title }}</h2>
    </div>`,
};

const loading     = ref(false);
const report      = ref(null);
const error       = ref("");
const showDomains = ref(false);

async function load() {
    loading.value = true;
    error.value   = "";
    try {
        const { data } = await api.get("/ai/analysis");
        report.value = data;
    } catch (e) {
        error.value = e?.response?.data?.error || "L'analyse a échoué. Veuillez réessayer.";
    } finally {
        loading.value = false;
    }
}

onMounted(load);

// Risk styling
const riskStyle = computed(() => {
    const level = report.value?.risk_level;
    if (level === "high") {
        return {
            bg: "bg-red-50 border border-red-200",
            text: "text-red-800",
            sub: "text-red-600",
            label: "text-red-400",
            icon: IconAlert,
            iconBg: "bg-red-100 ring-red-200",
            iconClass: "text-red-600",
        };
    }
    if (level === "medium") {
        return {
            bg: "bg-orange-50 border border-orange-200",
            text: "text-orange-800",
            sub: "text-orange-600",
            label: "text-orange-400",
            icon: IconPulse,
            iconBg: "bg-orange-100 ring-orange-200",
            iconClass: "text-orange-600",
        };
    }
    return {
        bg: "bg-green-50 border border-green-200",
        text: "text-green-800",
        sub: "text-green-600",
        label: "text-green-400",
        icon: IconCheckCircle,
        iconBg: "bg-green-100 ring-green-200",
        iconClass: "text-green-600",
    };
});

const riskLabel = computed(() => {
    return { high: "Risque élevé", medium: "Risque modéré", low: "Risque faible", unknown: "Inconnu" }[report.value?.risk_level] ?? "";
});

// Anomalies sorted by severity: critical → high → medium → low
const SEVERITY_ORDER = { critical: 0, high: 1, medium: 2, low: 3, none: 4 };
const sortedAnomalies = computed(() =>
    [...(report.value?.anomalies ?? [])].sort(
        (a, b) => (SEVERITY_ORDER[a.severity] ?? 4) - (SEVERITY_ORDER[b.severity] ?? 4)
    )
);

// Only show domains that have a real analysis (regardless of severity)
const activeDomains = computed(() => {
    if (!report.value?.domains) return [];
    return Object.entries(report.value.domains).filter(
        ([, d]) => d.analysis && !d.analysis.startsWith("No data")
    );
});

// Detect when report exists but contains no exploitable data
const hasNoData = computed(() => {
    if (!report.value) return false;
    return (
        !report.value.alerts?.length &&
        !report.value.anomalies?.length &&
        !report.value.global_recommendations?.length &&
        activeDomains.value.length === 0
    );
});

// Severity helpers
function severityBorder(s) {
    return { critical: "border-l-4 border-l-red-400", high: "border-l-4 border-l-red-300", medium: "border-l-4 border-l-amber-300", low: "border-l-4 border-l-slate-300", none: "border-slate-200" }[s] ?? "border-slate-200";
}
function severityBadge(s) {
    return { critical: "bg-red-50 text-red-600", high: "bg-red-50 text-red-500", medium: "bg-amber-50 text-amber-600", low: "bg-slate-100 text-slate-500", none: "bg-slate-100 text-slate-400" }[s] ?? "bg-slate-100 text-slate-400";
}
function severityFr(s) {
    return { critical: "Critique", high: "Élevé", medium: "Modéré", low: "Faible", none: "Aucun" }[s] ?? s;
}

const DOMAIN_LABELS = {
    sleep: "Sommeil", nutrition: "Nutrition", activity: "Activité physique",
    smoking: "Tabac", alcohol: "Alcool", vital_signs: "Signes vitaux",
    lab_results: "Analyses biologiques", treatments: "Traitements",
};
const DOMAIN_ICONS = {
    sleep: IconClock,
    nutrition: IconLeaf,
    activity: IconRun,
    smoking: IconSmoke,
    alcohol: IconWineGlass,
    vital_signs: IconHeart,
    lab_results: IconFlask,
    treatments: IconPill,
};
const DOMAIN_ICON_STYLES = {
    sleep: { wrap: "border-indigo-200 bg-indigo-50", icon: "text-indigo-600" },
    nutrition: { wrap: "border-emerald-200 bg-emerald-50", icon: "text-emerald-600" },
    activity: { wrap: "border-sky-200 bg-sky-50", icon: "text-sky-600" },
    smoking: { wrap: "border-slate-300 bg-slate-100", icon: "text-slate-700" },
    alcohol: { wrap: "border-amber-200 bg-amber-50", icon: "text-amber-600" },
    vital_signs: { wrap: "border-rose-200 bg-rose-50", icon: "text-rose-600" },
    lab_results: { wrap: "border-violet-200 bg-violet-50", icon: "text-violet-600" },
    treatments: { wrap: "border-cyan-200 bg-cyan-50", icon: "text-cyan-700" },
};
function domainLabel(d) { return DOMAIN_LABELS[d] ?? d; }
function domainIcon(d)  { return DOMAIN_ICONS[d] ?? IconClipboardList; }
function domainIconStyle(d) {
    return (
        DOMAIN_ICON_STYLES[d] ?? {
            wrap: "border-slate-200 bg-slate-50",
            icon: "text-slate-600",
        }
    );
}
</script>
