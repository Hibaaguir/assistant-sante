<!--
  DoctorPatientDetail.vue
  Patient details: vital signs, analyses, treatment history, general observation.
-->
<template>
    <section class="mt-8">
        <!-- Retour -->
        <button
            type="button"
            class="inline-flex items-center gap-2 text-[14px] font-medium text-[#2454ff]"
            @click="$emit('back')"
        >
            <IconArrowLeft class="h-[16px] w-[16px]" />
            Back to patient list
        </button>

        <!-- Profil -->
        <div class="mt-7 flex items-start gap-5">
            <div
                class="flex h-[82px] w-[82px] shrink-0 items-center justify-center rounded-[24px] text-[19px] font-bold text-white"
                :style="{ backgroundColor: patient.avatarColor }"
            >
                {{ patient.initials }}
            </div>

            <div>
                <div class="flex items-center gap-3">
                    <h2
                        class="text-[28px] font-bold leading-none text-[#031a46]"
                    >
                        {{ patient.name }}
                    </h2>
                    <span
                        class="h-[13px] w-[13px] rounded-full"
                        :style="{ backgroundColor: patient.dotColor }"
                    />
                </div>

                <div
                    class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[15px] text-[#41506b]"
                >
                    <span>{{ patient.age }} years</span>
                    <span>•</span>
                    <span>{{ patient.gender }}</span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <IconClock class="h-[16px] w-[16px]" />
                        Last updated: {{ patient.lastSeen }}
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <span
                        v-for="tag in patient.detailTags"
                        :key="tag.label"
                        class="inline-flex h-[31px] items-center rounded-full border px-4 text-[14px] font-semibold"
                        :class="tag.class"
                    >
                        {{ tag.label }}
                    </span>
                </div>

                <!-- Observation résumée -->
                <div
                    class="mt-3 rounded-[10px] border border-[#dde3ee] bg-[#f8faff] px-3 py-2"
                >
                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.04em] text-[#5f7190]"
                    >
                        Observation
                    </p>
                    <p class="mt-0.5 text-[13px] leading-5 text-[#1f345c]">
                        {{ observationResume }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Bloc observations par date -->
        <section
            class="mt-6 rounded-[16px] border border-[#d4d9e1] bg-white p-3 shadow-[0_1px_3px_rgba(15,23,42,0.05)]"
        >
            <article class="rounded-[12px] border border-[#d4d9e1] bg-[#fcfdff] p-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <p class="text-[15px] font-bold text-[#041c49]">Observations médicales</p>
                        <span class="rounded-full bg-[#f3f6fb] px-2 py-0.5 text-[11px] text-[#5c6d89]">Interne</span>
                    </div>
                    <button type="button" class="btn-outline" @click="obsOpen = !obsOpen">
                        {{ obsOpen ? "Fermer" : "Ajouter" }}
                    </button>
                </div>

                <!-- Formulaire ajout/édition -->
                <div v-if="obsOpen" class="mt-3 space-y-2.5">
                    <div class="flex items-center gap-3">
                        <label class="text-[13px] font-semibold text-[#374867]">Date</label>
                        <input v-model="obsDate" type="date" class="input-field h-[38px] flex-1" />
                    </div>
                    <textarea
                        v-model.trim="obsText"
                        rows="3"
                        placeholder="Exemple : état général stable, surveillance clinique recommandée."
                        class="input-field"
                        style="height:auto"
                    />
                    <div class="flex flex-wrap justify-end gap-2">
                        <button
                            v-if="obsDateHasEntry"
                            type="button"
                            class="btn-outline text-red-600 border-red-200 hover:border-red-400"
                            @click="supprimerObs"
                        >
                            Supprimer
                        </button>
                        <button type="button" class="btn-primary" @click="enregistrerObs">
                            Enregistrer
                        </button>
                    </div>
                </div>

                <div
                    v-if="obsMessage"
                    class="mt-2.5 rounded-[12px] border px-3 py-2 text-[12px]"
                    :class="obsMessageType === 'success'
                        ? 'border-[#c6ead0] bg-[#f2fcf4] text-[#118445]'
                        : 'border-[#f1d4ae] bg-[#fff8ef] text-[#b46910]'"
                >
                    {{ obsMessage }}
                </div>

                <!-- Liste des observations existantes -->
                <div v-if="localObservations.length" class="mt-3 space-y-2">
                    <div
                        v-for="o in localObservations"
                        :key="o.date"
                        class="rounded-[10px] border border-[#e2e8f0] bg-white px-3 py-2"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-[12px] font-semibold text-[#374867]">{{ formatObsDate(o.date) }}</p>
                                <p class="mt-0.5 text-[13px] leading-5 text-[#1f345c]">{{ o.note }}</p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 text-[11px] text-[#5c6d89] hover:text-[#374867]"
                                @click="chargerObs(o)"
                            >
                                Modifier
                            </button>
                        </div>
                    </div>
                </div>
                <p v-else class="mt-3 text-[13px] text-[#7b8faa]">Aucune observation enregistrée.</p>
            </article>
        </section>

        <!-- Onglets -->
        <nav
            class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-[10px] shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
        >
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="tab in TABS"
                    :key="tab.key"
                    type="button"
                    class="inline-flex h-[50px] items-center gap-2 rounded-[14px] px-5 text-[15px] font-semibold transition"
                    :class="
                        activeTab === tab.key
                            ? 'bg-[#3f49f4] text-white shadow-[0_10px_18px_rgba(63,73,244,0.22)]'
                            : 'text-[#384860]'
                    "
                    @click="activeTab = tab.key"
                >
                    <component :is="tab.icon" class="h-[18px] w-[18px]" />
                    {{ tab.label }}
                </button>
            </div>
        </nav>

        <!-- ── Signes vitaux ───────────────────────────────────────────────────── -->
        <section v-if="activeTab === 'vitals'" class="mt-8 space-y-4">
            <FilterCard
                title="Filtrer les signes vitaux"
                subtitle="Affinez l'historique par date et par type de mesure."
                :show-reset="!!(vitalDate || vitalSign !== 'all')"
                @reset="
                    vitalDate = '';
                    vitalSign = 'all';
                "
            >
                <input v-model="vitalDate" type="date" class="input-field" />
                <select v-model="vitalSign" class="input-field">
                    <option value="all">Tous les signes</option>
                    <option value="heartRate">Rythme cardiaque</option>
                    <option value="bloodPressure">Tension</option>
                    <option value="saturation">Saturation O2</option>
                </select>
            </FilterCard>

            <article
                v-for="entry in filteredVitals"
                :key="entry.isoDate || entry.date"
                class="card"
            >
                <div
                    class="flex items-center gap-2 text-[16px] font-bold text-[#061a45]"
                >
                    <IconeCalendrier class="h-[18px] w-[18px]" />{{
                        entry.date
                    }}
                </div>
                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    <div
                        v-for="card in entry.cards"
                        :key="card.key"
                        class="rounded-[16px] border px-5 py-4"
                        :class="card.class"
                    >
                        <p class="text-[14px] text-[#455572]">
                            {{ card.label }}
                        </p>
                        <p class="mt-2 text-[18px] font-bold text-[#061a45]">
                            {{ card.value }}
                        </p>
                    </div>
                </div>
            </article>

            <EmptyState
                v-if="!filteredVitals.length"
                title="Aucun signe vital ne correspond aux filtres."
                subtitle="Essayez une autre date ou choisissez un autre type de mesure."
            />
        </section>

        <!-- ── Analyses ────────────────────────────────────────────────────────── -->
        <section v-else-if="activeTab === 'analyses'" class="mt-8 space-y-4">
            <FilterCard
                title="Filtrer les analyses"
                subtitle="Affinez les résultats par date et par type d'analyse. Les 7 derniers jours sont affichés par défaut."
                :show-reset="!!(analysisDate || analysisType !== 'all')"
                @reset="
                    analysisDate = '';
                    analysisType = 'all';
                "
            >
                <input v-model="analysisDate" type="date" class="input-field" />
                <select v-model="analysisType" class="input-field">
                    <option value="all">Tous les types</option>
                    <option v-for="t in analysisTypes" :key="t" :value="t">
                        {{ t }}
                    </option>
                </select>
            </FilterCard>

            <article
                v-for="a in filteredAnalyses"
                :key="`${a.name}-${a.isoDate}`"
                class="card px-6 py-5"
            >
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-[18px] font-bold text-[#061a45]">
                        {{ a.name }}
                    </h3>
                    <span
                        class="inline-flex rounded-full px-3 py-1 text-[13px] font-medium"
                        :class="a.badgeClass"
                        >{{ a.status }}</span
                    >
                </div>
                <div
                    class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-3 text-[15px] text-[#455572]"
                >
                    <span class="text-[20px] font-bold text-[#061a45]">{{
                        a.value
                    }}</span>
                    <span>{{ a.range }}</span>
                    <span class="inline-flex items-center gap-2"
                        ><IconeCalendrier class="h-[16px] w-[16px]" />{{
                            a.date
                        }}</span
                    >
                </div>
            </article>

            <EmptyState
                v-if="!filteredAnalyses.length"
                title="Aucune analyse ne correspond aux filtres."
                subtitle="Essayez une autre date ou un autre type d'analyse."
            />
        </section>

        <!-- ── Traitements ─────────────────────────────────────────────────────── -->
        <section v-else class="mt-8 space-y-4">
            <article class="card p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-[22px] font-extrabold text-slate-900">
                            Historique des prises
                        </h3>
                        <p class="mt-1 text-[14px] text-[#5b6b84]">
                            Historique réel des traitements pris par le patient,
                            avec 7 derniers jours affichés par défaut.
                        </p>
                    </div>
                    <span
                        class="rounded-full bg-[#eef4ff] px-3 py-1 text-[12px] font-semibold text-[#3257d6]"
                    >
                        {{ patient.treatmentHistoryRows?.length || 0 }} jour{{
                            (patient.treatmentHistoryRows?.length || 0) > 1
                                ? "s"
                                : ""
                        }}
                    </span>
                </div>

                <!-- Filtres traitements -->
                <article
                    class="mt-6 rounded-[18px] border border-[#dbe2ec] bg-[#fbfcfe] p-5"
                >
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div>
                            <h4 class="text-[17px] font-bold text-[#10254f]">
                                Filtrer l'historique des traitements
                            </h4>
                            <p class="mt-1 text-[14px] text-[#60708b]">
                                Affinez l'affichage par date, traitement ou
                                statut d'observance.
                            </p>
                        </div>
                        <button
                            v-if="
                                treatDate ||
                                treatMed !== 'all' ||
                                treatStatus !== 'all'
                            "
                            type="button"
                            class="btn-outline"
                            @click="
                                treatDate = '';
                                treatMed = 'all';
                                treatStatus = 'all';
                            "
                        >
                            Réinitialiser
                        </button>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="filter-label">Date</label
                            ><input
                                v-model="treatDate"
                                type="date"
                                class="input-field"
                            />
                        </div>
                        <div>
                            <label class="filter-label">Traitement</label>
                            <select v-model="treatMed" class="input-field">
                                <option value="all">
                                    Tous les traitements
                                </option>
                                <option
                                    v-for="m in treatMedOptions"
                                    :key="m"
                                    :value="m"
                                >
                                    {{ m }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="filter-label">Observance</label>
                            <select v-model="treatStatus" class="input-field">
                                <option value="all">Tous les jours</option>
                                <option value="complete">Jours complets</option>
                                <option value="partial">Jours partiels</option>
                            </select>
                        </div>
                    </div>
                </article>

                <!-- Résultats -->
                <div v-if="filteredTreatments.length" class="mt-6 space-y-4">
                    <article
                        v-for="day in filteredTreatments"
                        :key="day.dateKey"
                        class="rounded-[18px] border border-[#dbe2ec] bg-[#fbfcfe] p-5"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div>
                                <h4
                                    class="text-[16px] font-bold text-[#10254f]"
                                >
                                    {{ longDate(day.dateKey) }}
                                </h4>
                                <p class="mt-1 text-[14px] text-[#60708b]">
                                    {{ day.taken }}/{{ day.total }} prises
                                    effectuées
                                </p>
                            </div>
                            <span
                                class="inline-flex h-[34px] items-center rounded-full px-3 text-[12px] font-semibold"
                                :class="
                                    day.isComplete
                                        ? 'bg-[#dff6e7] text-[#15803d]'
                                        : 'bg-[#e9eef7] text-[#42526b]'
                                "
                            >
                                {{
                                    day.isComplete
                                        ? "Jour complet"
                                        : "Jour partiel"
                                }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 lg:grid-cols-2">
                            <article
                                v-for="med in day.meds"
                                :key="`${day.dateKey}-${med.id}`"
                                class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="text-[15px] font-bold text-[#112652]"
                                        >
                                            {{ med.name }}
                                        </p>
                                        <p
                                            class="mt-1 text-[13px] text-[#63758d]"
                                        >
                                            {{ med.dose }}
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex h-[30px] min-w-[52px] items-center justify-center rounded-full px-3 text-[12px] font-semibold"
                                        :class="
                                            med.isComplete
                                                ? 'bg-[#dff6e7] text-[#15803d]'
                                                : 'bg-[#edf2ff] text-[#3257d6]'
                                        "
                                    >
                                        {{ med.taken }}/{{ med.total }}
                                    </span>
                                </div>
                                <div
                                    class="mt-4 h-[8px] rounded-full bg-[#dfe5ee]"
                                >
                                    <div
                                        class="h-[8px] rounded-full transition-all"
                                        :class="
                                            med.isComplete
                                                ? 'bg-[#16a34a]'
                                                : 'bg-[#4f46e5]'
                                        "
                                        :style="{ width: `${med.progress}%` }"
                                    />
                                </div>
                            </article>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="mt-6 rounded-[16px] border border-dashed border-[#d3dbe7] bg-[#fbfcff] px-5 py-8 text-center"
                >
                    <p class="text-[15px] font-semibold text-[#10254f]">
                        Aucun historique de prise disponible.
                    </p>
                    <p class="mt-2 text-[13px] text-[#697892]">
                        Essayez une autre date, un autre traitement ou un autre
                        filtre d'observance.
                    </p>
                </div>
            </article>
        </section>
    </section>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import api from "@/services/api";
import {
    IconArrowLeft,
    IconCalendar,
    IconClock,
    IconHeart,
    IconLink,
    IconWave,
} from "@/components/doctors/DoctorIcons.js";

// Aliases for French names still used in template/config
const IconeCalendrier = IconCalendar;
const IconeCoeur = IconHeart;
const IconeOnde = IconWave;
const IconeLien = IconLink;

// ─── Props / emits ────────────────────────────────────────────────────────────
const props = defineProps({ patient: { type: Object, required: true } });
defineEmits(["back"]);

// ─── Sous-composants locaux ───────────────────────────────────────────────────
const FilterCard = {
    props: ["title", "subtitle", "showReset"],
    emits: ["reset"],
    template: `
    <article class="card p-5">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <h3 class="text-[18px] font-bold text-[#041c49]">{{ title }}</h3>
          <p class="mt-1 text-[14px] text-[#5b6b84]">{{ subtitle }}</p>
        </div>
        <button v-if="showReset" type="button" class="btn-outline" @click="$emit('reset')">Réinitialiser</button>
      </div>
      <div class="mt-5 grid gap-4 md:grid-cols-2"><slot /></div>
    </article>`,
};

const EmptyState = {
    props: ["title", "subtitle"],
    template: `
    <article class="rounded-[20px] border border-dashed border-[#cfd6e2] bg-white px-6 py-8 text-center shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
      <p class="text-[17px] font-semibold text-[#10254f]">{{ title }}</p>
      <p class="mt-2 text-[14px] text-[#5b6b84]">{{ subtitle }}</p>
    </article>`,
};

// ─── Onglets ──────────────────────────────────────────────────────────────────
const TABS = [
    { key: "vitals", label: "Signes vitaux", icon: IconeCoeur },
    { key: "analyses", label: "Analyses", icon: IconeOnde },
    { key: "treatments", label: "Traitements", icon: IconeLien },
];
const activeTab = ref("vitals");

// ─── Filtres ──────────────────────────────────────────────────────────────────
const vitalDate = ref("");
const vitalSign = ref("all");
const analysisDate = ref("");
const analysisType = ref("all");
const treatDate = ref("");
const treatMed = ref("all");
const treatStatus = ref("all");

// ─── Observation ──────────────────────────────────────────────────────────────
const obsText = ref("");
const obsDate = ref(new Date().toISOString().slice(0, 10));
const obsOpen = ref(false);
const obsMessage = ref("");
const obsMessageType = ref("success");
const localObservations = ref([]);

const obsDateHasEntry = computed(() =>
    localObservations.value.some((o) => o.date === obsDate.value),
);

const observationResume = computed(() => {
    const latest = localObservations.value[0];
    if (!latest) return "Aucune observation médicale pour le moment.";
    const t = latest.note;
    return t.length > 140 ? `${t.slice(0, 140).trim()}…` : t;
});

// ─── Signes vitaux filtrés ────────────────────────────────────────────────────
const VITAL_CARDS = [
    {
        key: "heartRate",
        label: "Rythme cardiaque",
        class: "border-[#f4bcc3] bg-[#fff5f6]",
    },
    {
        key: "bloodPressure",
        label: "Tension",
        class: "border-[#aac8ff] bg-[#eff6ff]",
    },
    {
        key: "saturation",
        label: "Saturation O2",
        class: "border-[#dcc5ff] bg-[#faf4ff]",
    },
];

const filteredVitals = computed(() => {
    const entries = props.patient?.vitalsHistory ?? [];
    const pool = vitalDate.value ? entries : entries.slice(0, 7);
    return pool
        .filter((e) => !vitalDate.value || e.isoDate === vitalDate.value)
        .map((e) => ({
            ...e,
            cards: VITAL_CARDS.filter(
                (c) => vitalSign.value === "all" || c.key === vitalSign.value,
            ).map((c) => ({ ...c, value: e[c.key] })),
        }))
        .filter((e) => e.cards.length);
});

// ─── Analyses filtrées ────────────────────────────────────────────────────────
const analysisTypes = computed(() => {
    const analyses = props.patient?.analyses ?? [];
    return [
        ...new Set(
            analyses.map((a) => String(a.type || "").trim()).filter(Boolean),
        ),
    ];
});

const filteredAnalyses = computed(() => {
    const analyses = props.patient?.analyses ?? [];
    const recentKeys = [
        ...new Set(analyses.map((a) => a.isoDate).filter(Boolean)),
    ].slice(0, 7);
    return analyses
        .filter((a) =>
            analysisDate.value
                ? a.isoDate === analysisDate.value
                : recentKeys.includes(a.isoDate),
        )
        .filter(
            (a) =>
                analysisType.value === "all" || a.type === analysisType.value,
        );
});

// ─── Traitements filtrés ──────────────────────────────────────────────────────
const treatMedOptions = computed(() => [
    ...new Set(
        (props.patient?.treatmentHistoryRows ?? [])
            .flatMap((d) => d.meds.map((m) => String(m.name || "").trim()))
            .filter(Boolean),
    ),
]);

const filteredTreatments = computed(() => {
    const rows = props.patient?.treatmentHistoryRows ?? [];
    const pool = treatDate.value ? rows : rows.slice(0, 7);
    return pool
        .filter((d) => !treatDate.value || d.dateKey === treatDate.value)
        .map((d) => {
            const meds = d.meds.filter(
                (m) => treatMed.value === "all" || m.name === treatMed.value,
            );
            const total = meds.reduce((s, m) => s + +(m.total || 0), 0);
            const taken = meds.reduce((s, m) => s + +(m.taken || 0), 0);
            return {
                ...d,
                meds,
                total,
                taken,
                isComplete: total > 0 && taken >= total,
            };
        })
        .filter((d) => d.meds.length)
        .filter(
            (d) =>
                treatStatus.value === "all" ||
                (treatStatus.value === "complete") === d.isComplete,
        );
});

// ─── Formatters ───────────────────────────────────────────────────────────────
function longDate(iso) {
    if (!iso) return "-";
    const d = new Date(`${iso}T00:00:00`);
    return isNaN(d)
        ? iso
        : d.toLocaleDateString("fr-FR", {
              weekday: "long",
              day: "numeric",
              month: "long",
              year: "numeric",
          });
}

function formatObsDate(str) {
    if (!str) return "";
    const d = new Date(`${str}T00:00:00`);
    return isNaN(d)
        ? str
        : d.toLocaleDateString("fr-FR", {
              day: "numeric",
              month: "long",
              year: "numeric",
          });
}

// ─── Observation API ──────────────────────────────────────────────────────────
async function enregistrerObs() {
    const note = obsText.value.trim();
    if (!note) {
        obsMessageType.value = "warning";
        obsMessage.value = "Écrivez une observation avant de l'enregistrer.";
        return;
    }
    try {
        const { data } = await api.put(
            `/doctor-invitations/patients/${props.patient.id}/observations`,
            { observation_date: obsDate.value, note },
        );
        const saved = data?.data;
        const idx = localObservations.value.findIndex((o) => o.date === saved.observation_date);
        const entry = { date: saved.observation_date, note: saved.note, updatedAt: saved.updated_at };
        if (idx >= 0) localObservations.value.splice(idx, 1, entry);
        else localObservations.value.unshift(entry);
        localObservations.value.sort((a, b) => b.date.localeCompare(a.date));
        obsMessageType.value = "success";
        obsMessage.value = "Observation enregistrée.";
        obsOpen.value = false;
        obsText.value = "";
    } catch {
        obsMessageType.value = "warning";
        obsMessage.value = "Impossible d'enregistrer l'observation pour le moment.";
    }
}

async function supprimerObs() {
    try {
        await api.delete(
            `/doctor-invitations/patients/${props.patient.id}/observations/${obsDate.value}`,
        );
        localObservations.value = localObservations.value.filter((o) => o.date !== obsDate.value);
        obsText.value = "";
        obsMessageType.value = "success";
        obsMessage.value = "Observation supprimée.";
        obsOpen.value = false;
    } catch {
        obsMessageType.value = "warning";
        obsMessage.value = "Impossible de supprimer l'observation pour le moment.";
    }
}

function chargerObs(entry) {
    obsDate.value = entry.date;
    obsText.value = entry.note;
    obsOpen.value = true;
    obsMessage.value = "";
}

// ─── Init observations depuis patient ────────────────────────────────────────
watch(
    () => props.patient?.id,
    () => {
        obsMessage.value = "";
        obsText.value = "";
        obsOpen.value = false;
        obsDate.value = new Date().toISOString().slice(0, 10);
        localObservations.value = (props.patient?.observations ?? []).slice();
    },
    { immediate: true },
);

// When date changes in the form, prefill text if an entry exists for that date
watch(obsDate, (date) => {
    const existing = localObservations.value.find((o) => o.date === date);
    obsText.value = existing ? existing.note : "";
    obsMessage.value = "";
});
</script>

<style scoped>
@reference "../../index.css";
.card {
    @apply rounded-[20px] border border-[#d4d9e1] bg-white shadow-[0_1px_4px_rgba(15,23,42,0.05)];
}
.input-field {
    @apply h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5];
}
.btn-outline {
    @apply inline-flex h-[34px] items-center justify-center rounded-[12px] border border-[#d4d9e1] px-3 text-[13px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52];
}
.btn-primary {
    @apply inline-flex h-[34px] items-center justify-center rounded-[12px] bg-[#3f49f4] px-3 text-[13px] font-semibold text-white transition hover:bg-[#3140ef];
}
.filter-label {
    @apply mb-2 block text-[14px] font-semibold text-[#23375f];
}
</style>
