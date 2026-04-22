<!--
  DoctorPatientDetail.vue
  Patient details: vital signs, analyses, treatment history, and a unified
  "Observations" tab where the doctor writes one note per health-data day.
-->
<template>
    <section class="mt-8">
        <!-- Retour -->
        <button
            type="button"
            class="inline-flex items-center gap-2 text-[17px] font-semibold text-[#2454ff]"
            @click="$emit('back')"
        >
            <IconArrowLeft class="h-[18px] w-[18px]" />
            Retour à la liste des patients
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
                    <h2 class="text-[24px] font-bold leading-none text-black">
                        {{ patient.name }}
                    </h2>
                </div>

                <div
                    class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[15px] text-[#41506b]"
                >
                    <span>{{ patient.age }} ans</span>
                    <span>•</span>
                    <span>{{ patient.gender }}</span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <IconClock class="h-[16px] w-[16px]" />
                        Dernière mise à jour : {{ patient.lastSeen }}
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
            </div>
        </div>

        <!-- Ajouter une observation -->
        <div
            class="mt-8 rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
        >
            <h3 class="text-[17px] font-bold text-[#041c49]">
                Ajouter une observation
            </h3>
            <AlertMessage :message="obsError" type="error" class="mb-3 mt-4" />
            <textarea
                v-model="obsText"
                rows="2"
                class="input-field-textarea"
                :class="obsError ? 'mt-1' : 'mt-4'"
                placeholder="Rédigez votre observation médicale…"
            />
            <div class="mt-3 flex items-center gap-3">
                <BaseButton
                    type="button"
                    variant="primary"
                    size="md"
                    :disabled="obsSaving"
                    @click="saveObservation"
                >
                    {{ obsSaving ? "Enregistrement…" : "Enregistrer" }}
                </BaseButton>
                <span
                    v-if="obsMsg"
                    class="text-[13px]"
                    :class="obsMsg.ok ? 'text-[#118445]' : 'text-[#b46910]'"
                >
                    {{ obsMsg.text }}
                </span>
            </div>

            <!-- Observations déjà enregistrées -->
            <div
                v-if="observationHistory.length"
                class="mt-5 space-y-2 border-t border-[#edf0f5] pt-4"
            >
                <div class="flex items-center justify-between gap-3">
                    <p
                        class="text-[14px] font-semibold uppercase tracking-wide text-black"
                    >
                        {{
                            showObservationHistory
                                ? "Historique des observations"
                                : "3 dernières observations"
                        }}
                    </p>
                    <button
                        v-if="hasMoreObservations"
                        type="button"
                        class="text-[17px] font-semibold text-[#2454ff] transition hover:text-[#1a3fa8]"
                        @click="
                            showObservationHistory = !showObservationHistory
                        "
                    >
                        {{
                            showObservationHistory
                                ? "Masquer l'historique"
                                : "Consulter l'historique complet"
                        }}
                        →
                    </button>
                </div>
                <div
                    v-for="obs in displayedObservations"
                    :key="obs.isoDate"
                    class="rounded-[14px] border border-[#e4ebf8] bg-[#f5f8ff] px-4 py-3"
                >
                    <p class="text-[14px] font-semibold text-[#3f57c4]">
                        {{ obs.date }}
                    </p>
                    <p class="mt-1 text-[15px] leading-5 text-[#2d3f5e]">
                        {{ obs.observation }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <nav class="border-b-2 border-slate-200 bg-transparent">
            <div class="flex gap-0">
                <button
                    v-for="tab in TABS"
                    :key="tab.key"
                    type="button"
                    class="flex-1 px-4 py-3 text-center font-bold text-lg transition-all inline-flex items-center justify-center gap-2"
                    :class="
                        activeTab === tab.key
                            ? 'border-b-4 border-blue-500 text-blue-900 bg-blue-50'
                            : 'border-b-4 border-transparent text-black hover:bg-gray-50'
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
                title="Signes vitaux"
                subtitle="Filtrez par date ou par type."
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
                        <p class="text-[14px] text-[#2d3f5e]">
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
                title="Analyses"
                subtitle="Filtrez par date ou par type."
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
                :key="`${a.id}-${a.isoDate}`"
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
                    class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-3 text-[15px] text-[#2d3f5e]"
                >
                    <span class="text-[20px] font-bold text-[#061a45]">{{
                        a.value
                    }}</span>
                    <span>{{ a.range }}</span>
                    <span class="inline-flex items-center gap-2">
                        <IconeCalendrier class="h-[16px] w-[16px]" />{{
                            a.date
                        }}
                    </span>
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
            <div>

                <!-- Filtres -->
                <FilterCard
                    title="Traitements"
                    subtitle="Filtrez par date, médicament ou observance."
                    :show-reset="!!(treatDate || treatMed !== 'all' || treatStatus !== 'all')"
                    @reset="treatDate = ''; treatMed = 'all'; treatStatus = 'all';"
                    class="mb-5"
                >
                    <input v-model="treatDate" type="date" class="input-field" />
                    <select v-model="treatMed" class="input-field">
                        <option value="all">Tous les médicaments</option>
                        <option v-for="m in treatMedOptions" :key="m" :value="m">{{ m }}</option>
                    </select>
                    <select v-model="treatStatus" class="input-field">
                        <option value="all">Toute observance</option>
                        <option value="complete">Complet</option>
                        <option value="partial">Partiel</option>
                    </select>
                </FilterCard>

                <!-- Tableau -->
                <div v-if="filteredTreatments.length" class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-left text-[14px] font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Médicament</th>
                                <th class="px-6 py-4">Dose</th>
                                <th class="px-6 py-4 text-center">Prises</th>
                                <th class="px-6 py-4 text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template v-for="day in filteredTreatments" :key="day.dateKey">
                                <tr
                                    v-for="(med, idx) in day.meds"
                                    :key="`${day.dateKey}-${med.id}`"
                                    class="bg-white hover:bg-slate-50 transition-colors"
                                >
                                    <td class="px-6 py-4 text-[15px] text-slate-700 font-medium whitespace-nowrap">
                                        {{ idx === 0 ? longDate(day.dateKey) : '' }}
                                    </td>
                                    <td class="px-6 py-4 text-[15px] font-semibold text-slate-800">{{ med.name }}</td>
                                    <td class="px-6 py-4 text-[15px] text-slate-500">{{ med.dose }}</td>
                                    <td class="px-6 py-4 text-center text-[15px] font-semibold"
                                        :class="med.isComplete ? 'text-green-600' : 'text-blue-500'">
                                        {{ med.taken }}/{{ med.total }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            v-if="idx === 0"
                                            class="rounded-full px-3 py-1 text-[13px] font-semibold"
                                            :class="day.isComplete ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500'"
                                        >
                                            {{ day.isComplete ? '✓ Complet' : 'Partiel' }}
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div v-else class="py-10 text-center text-[14px] text-slate-400">
                    Aucune donnée pour ces filtres.
                </div>

            </div>
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
import BaseButton from "@/components/ui/BaseButton.vue";
import AlertMessage from "@/components/ui/AlertMessage.vue";
import FilterCard from "@/components/ui/FilterCard.vue";

const IconeCalendrier = IconCalendar;
const IconeCoeur = IconHeart;
const IconeOnde = IconWave;
const IconeLien = IconLink;

// ─── Props / emits ────────────────────────────────────────────────────────────
const props = defineProps({ patient: { type: Object, required: true } });
defineEmits(["back"]);

// ─── Sous-composants locaux ───────────────────────────────────────────────────

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

// ─── Observation form ─────────────────────────────────────────────────────────
const obsText = ref("");
const obsSaving = ref(false);
const obsMsg = ref(null);
const obsError = ref("");
const showObservationHistory = ref(false);

const localObservations = ref(props.patient?.healthDataObservations ?? []);
const observationHistory = computed(() => localObservations.value);

const hasMoreObservations = computed(() => observationHistory.value.length > 3);

const displayedObservations = computed(() =>
    showObservationHistory.value
        ? observationHistory.value
        : observationHistory.value.slice(0, 3),
);

// ─── Signes vitaux filtrés ────────────────────────────────────────────────────

// Configuration des 3 types de signes vitaux affichés dans les cartes
const VITAL_CARDS = [
    { key: "heartRate",    label: "Rythme cardiaque", class: "border-[#f4bcc3] bg-[#fff5f6]" },
    { key: "bloodPressure", label: "Tension",          class: "border-[#aac8ff] bg-[#eff6ff]" },
    { key: "saturation",   label: "Saturation O2",    class: "border-[#dcc5ff] bg-[#faf4ff]" },
];

// Retourne les entrées de signes vitaux filtrées par date et type sélectionnés
const filteredVitals = computed(() => {
    const toutesLesEntrees = props.patient?.vitalsHistory ?? [];

    // Si aucune date choisie → afficher seulement les 7 dernières entrées
    const entreesFiltrees = vitalDate.value
        ? toutesLesEntrees.filter((e) => e.isoDate === vitalDate.value)
        : toutesLesEntrees.slice(0, 7);

    // Pour chaque entrée, on construit les cartes à afficher selon le type choisi
    return entreesFiltrees.map((entree) => {
        const cartesFiltrees = VITAL_CARDS
            .filter((carte) => vitalSign.value === "all" || carte.key === vitalSign.value)
            .map((carte) => ({ ...carte, value: entree[carte.key] }));
        return { ...entree, cards: cartesFiltrees };
    }).filter((entree) => entree.cards.length > 0);
});

// ─── Analyses filtrées ────────────────────────────────────────────────────────

// Retourne la liste unique des types d'analyses disponibles (pour le select)
const analysisTypes = computed(() => {
    const analyses = props.patient?.analyses ?? [];
    const types = analyses.map((a) => String(a.type || "").trim()).filter(Boolean);
    return [...new Set(types)]; // Set supprime les doublons
});

// Retourne les analyses filtrées par date et type sélectionnés
const filteredAnalyses = computed(() => {
    const analyses = props.patient?.analyses ?? [];

    // Si aucune date choisie → garder seulement les 7 dates les plus récentes
    const datesRecentes = [...new Set(analyses.map((a) => a.isoDate).filter(Boolean))].slice(0, 7);

    return analyses.filter((a) => {
        const matchDate = analysisDate.value ? a.isoDate === analysisDate.value : datesRecentes.includes(a.isoDate);
        const matchType = analysisType.value === "all" || a.type === analysisType.value;
        return matchDate && matchType;
    });
});

// ─── Traitements filtrés ──────────────────────────────────────────────────────

// Retourne la liste unique des noms de médicaments (pour le select de filtre)
const treatMedOptions = computed(() => {
    const tousLesMeds = (props.patient?.treatmentHistoryRows ?? [])
        .flatMap((jour) => jour.meds.map((m) => String(m.name || "").trim()))
        .filter(Boolean);
    return [...new Set(tousLesMeds)]; // Set supprime les doublons
});

// Retourne les lignes de traitements filtrées par date, médicament et statut
const filteredTreatments = computed(() => {
    const tousLesJours = props.patient?.treatmentHistoryRows ?? [];

    // Si aucune date → seulement les 7 derniers jours
    const joursFiltres = treatDate.value
        ? tousLesJours.filter((j) => j.dateKey === treatDate.value)
        : tousLesJours.slice(0, 7);

    return joursFiltres
        .map((jour) => {
            // Filtre les médicaments du jour selon le médicament choisi
            const meds = jour.meds.filter(
                (m) => treatMed.value === "all" || m.name === treatMed.value,
            );
            // Recalcule le total des doses et des prises pour le jour filtré
            const total = meds.reduce((somme, m) => somme + Number(m.total || 0), 0);
            const pris  = meds.reduce((somme, m) => somme + Number(m.taken || 0), 0);
            return { ...jour, meds, total, taken: pris, isComplete: total > 0 && pris >= total };
        })
        .filter((jour) => jour.meds.length > 0) // supprimer les jours sans médicaments
        .filter((jour) => {
            if (treatStatus.value === "all") return true;
            return treatStatus.value === "complete" ? jour.isComplete : !jour.isComplete;
        });
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

// ─── API — save observation ───────────────────────────────────────────────────
const OBS_MAX = 1000;

async function saveObservation() {
    obsError.value = "";
    if (!obsText.value.trim()) {
        obsError.value = "Le champ observation ne peut pas être vide.";
        return;
    }
    if (obsText.value.trim().length > OBS_MAX) {
        obsError.value = `L'observation ne peut pas dépasser ${OBS_MAX} caractères (actuellement ${obsText.value.trim().length}).`;
        return;
    }
    obsSaving.value = true;
    obsMsg.value = null;
    try {
        const text = obsText.value.trim();
        await api.post(
            `/doctor-invitations/patients/${props.patient.id}/observations`,
            { date: new Date().toISOString().slice(0, 10), observation: text },
        );
        localObservations.value = [
            {
                isoDate: new Date().toISOString().slice(0, 10),
                date: new Date().toLocaleDateString("fr-FR", { year: "numeric", month: "long", day: "numeric" }),
                observation: text,
            },
            ...localObservations.value,
        ];
        obsText.value = "";
        obsMsg.value = { ok: true, text: "Observation enregistrée." };
    } catch {
        obsMsg.value = { ok: false, text: "Erreur lors de l'enregistrement." };
    } finally {
        obsSaving.value = false;
        setTimeout(() => {
            obsMsg.value = null;
        }, 3000);
    }
}

// ─── Reset form when patient changes ─────────────────────────────────────────
watch(
    () => props.patient?.id,
    () => {
        obsText.value = "";
        obsMsg.value = null;
        obsError.value = "";
        showObservationHistory.value = false;
    },
);
</script>

<style scoped>
@reference "../../index.css";
.card {
    @apply rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)];
}
.input-field-textarea {
    @apply w-full rounded-[12px] border border-[#d7dce6] bg-[#fbfcfd] px-4 py-3 text-[14px] text-[#061a45] outline-none transition focus:border-[#4a55f5];
    resize: vertical;
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
