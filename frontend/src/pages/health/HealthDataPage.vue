<template>
    <div class="w-full px-5 py-4 sm:px-7 bg-white">
        <Typography tag="h1" variant="h1-style"> Données de santé </Typography>
        <Typography tag="h4" variant="h4-style">
            Suivez vos indicateurs de santé au fil du temps
        </Typography>
        <!-- Observations du médecin -->
        <section v-if="doctorLatestObservation" class="mt-8">
            <Typography tag="h3" variant="h3-style" class="mb-4 text-slate-900">
                Observations de votre médecin
            </Typography>
            <article
                class="rounded-2xl border-2 border-blue-200 bg-white p-6 shadow-sm transition-all duration-300 hover:border-blue-300 hover:shadow-md"
            >
                <div class="space-y-4">
                    <!-- Date -->
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-200">
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5 text-blue-600 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <span class="text-[13px] font-semibold uppercase tracking-wide text-slate-700">
                            {{ formatLongDate(doctorLatestObservation.date) }}
                        </span>
                    </div>
                    <!-- Observation text -->
                    <Typography tag="p" variant="paragraph" class="text-[15px] leading-relaxed text-slate-800 font-medium">
                        {{ doctorLatestObservation.observation }}
                    </Typography>
                </div>
            </article>
        </section>

        <!-- TabBar replaces 3 repeated button blocks — add tabs here to extend -->
        <TabBar
            v-model="activeTab"
            class="mt-4"
            :tabs="[
                { value: 'vitals', label: 'Signes vitaux' },
                { value: 'labs', label: 'Analyse medical' },
                { value: 'treatments', label: 'Traitements' },
            ]"
        />

        <div v-if="showAddButton" class="mt-4 flex justify-end gap-2">
<BaseButton
                type="button"
                variant="add"
                size="md"
                @click="openAddModal"
            >
                <svg
                    viewBox="0 0 24 24"
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                </svg>
                {{ addButtonLabel }}
            </BaseButton>
        </div>

        <VitalSigns
            v-if="activeTab === 'vitals'"
            ref="vitalsTab"
            :latest-vital="latestVital"
            :history-heart-rate="historyHeartRateValues"
            :history-systolic="historySystolicValues"
            :history-diastolic="historyDiastolicValues"
            :history-saturation="historySaturationValues"
            :vital-date-keys="vitalDateKeys"
            @refresh="loadHealthData"
        />

        <MedicalAnalysis
            v-else-if="activeTab === 'labs'"
            ref="labsTab"
            :analyses="labResults"
            @refresh="loadHealthData"
        />

        <Treatments
            v-else
            :treatment-medicines="treatmentMedicines"
            :treatment-checks="treatmentChecks"
            :treatment-days="treatmentDays"
            @refresh="loadHealthData"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { formatLongDate } from "@/components/doctors/doctorUtilities.js";
import { useHealthDataStore } from "@/stores/healthData";
import VitalSigns from "@/components/health/VitalSigns.vue";
import MedicalAnalysis from "@/components/health/MedicalAnalysis.vue";
import Treatments from "@/components/health/Treatments.vue";
import { useNotificationsStore } from "@/stores/notifications";
import Typography from "@/components/ui/Typography.vue";
import TabBar from "@/components/ui/TabBar.vue";
import BaseButton from "@/components/ui/BaseButton.vue";

const vitalsTab = ref(null);
const labsTab = ref(null);
const notifications = useNotificationsStore();
const healthDataStore = useHealthDataStore();
//ongle actuelle peut être "vitals", "labs" ou "treatments"
const activeTab = ref("vitals");
const getTodayKey = () => new Date().toISOString().slice(0, 10);
const showAddButton = computed(() => activeTab.value !== "treatments");//true if not treatments
const addButtonLabel = computed(() => {
    if (activeTab.value === "labs") return "Ajouter une analyse";
    const hasToday = latestVital.value &&
        String(latestVital.value.measured_at).slice(0, 10) === getTodayKey();
    return hasToday ? "Modifier la dernière mesure" : "Ajouter une mesure";
});

const labResults = ref([]);
const latestVital = ref(null);
//c'est pour construire les graphiques d'historique des signes vitaux 30 derniers jours
const vitalDateKeys = ref([]);
const historyHeartRateValues = ref([]);
const historySystolicValues = ref([]);
const historyDiastolicValues = ref([]);
const historySaturationValues = ref([]);
const treatmentMedicines = ref([]);
const treatmentChecks = reactive({});
const treatmentDays = ref(buildLast7Days());
const doctorLatestObservation = ref(null); // { id, date, observation } | null

function openAddModal() {
    if (activeTab.value === "labs") labsTab.value?.ouvrirModalAjout();
    else vitalsTab.value?.ouvrirModalAjout();
}

function buildLast7Days() {
    const today = new Date();//Date actuelle
    const todayKey = getTodayKey();//Clé de la date d'aujourd'hui au format YYYY-MM-DD pour comparaison future
    const monday = new Date(today);//copie de la date d'aujourd'hui pour calculer le lundi de la semaine
    // On veut que Lundi soit le premier jour (offset = 0)
    const dayOfWeek = today.getDay();//0 (Dimanche) à 6 (Samedi)
    //Si aujourd'hui est Dimanche (0), on recule de 6 jours pour arriver au Lundi précédent. Sinon, on recule de (dayOfWeek - 1) jours.
    const dayOffset = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
    monday.setDate(today.getDate() - dayOffset); // Recule jusqu'au lundi de la semaine actuelle

    return Array.from({ length: 7 }).map((_, idx) => {
        const date = new Date(monday);
        date.setDate(monday.getDate() + idx);
        const key = date.toISOString().slice(0, 10);
        return {
            key,//"YYYY-MM-DD"
            shortLabel: date //L
                .toLocaleDateString("fr-FR", { weekday: "short" })
                .replace(".", ""),
            fullLabel: date.toLocaleDateString("fr-FR", {//Lundi 1 janvier
                weekday: "long",
                day: "numeric",
                month: "long",
            }),
            day: date.getDate(),
            isFuture: key > todayKey,
        };
    });
}

function buildDoseKey(medId, doseIndex) {
    return `${medId}__dose_${doseIndex}`;
}

function ensureDayTracking(dayKey) {
    treatmentChecks[dayKey] ??= {};
    for (const med of treatmentMedicines.value) {
        //Pour chaque médicament actif on calcule le nombre de prises minimum 1, maximum 12.
        const doses = Math.max(
            1,
            Math.min(Number(med?.frequency_count ?? 1), 12),
        );
        for (let i = 1; i <= doses; i += 1) {
            const key = buildDoseKey(med.id, i);
            if (typeof treatmentChecks[dayKey][key] !== "boolean") {
                treatmentChecks[dayKey][key] = false;
            }
        }
    }
}
// Traite les données du store et met à jour les refs locaux
function processHealthData() {
    const data       = healthDataStore.overview ?? {};
    const vitalsRaw  = healthDataStore.vitals;
    const historyData = healthDataStore.treatmentChecks;

        latestVital.value = data.latest_vitals ?? null;
        // On transforme les résultats de analyses pour les adapter à l'affichage
        labResults.value = Array.isArray(data.lab_results)
            ? data.lab_results.map((item) => ({
                  id: item.id,
                  type: item.analysis_type ?? "",
                  result: item.result_name ?? "",
                  name: `${item.analysis_type ?? ""} - ${item.result_name ?? ""}`.replace(
                      / - $/,
                      "",
                  ),
                  value: item.value,
                  unit: item.unit ?? "",
                  date: formatLongDate(item.analysis_date),
                  analysisDate: item.analysis_date,
              }))
            : [];

        //Trie l'historique des vitaux par date croissante
        vitalsRaw.sort((a, b) =>
            String(a.measured_at).localeCompare(String(b.measured_at)),
        );
        // On extrait les dates uniques pour construire les carte d'historique
        const historyDateKeys = [...new Set(
            vitalsRaw.map((v) => String(v.measured_at).slice(0, 10)),
        )];
        vitalDateKeys.value = historyDateKeys;
        
        const pickField = (field) =>
            historyDateKeys.map((dk) => {
                const v = vitalsRaw.find((r) => String(r.measured_at).slice(0, 10) === dk);
                return v?.[field] ?? null;
            });

        historyHeartRateValues.value  = pickField("heart_rate");
        historySystolicValues.value   = pickField("systolic_pressure");
        historyDiastolicValues.value  = pickField("diastolic_pressure");
        historySaturationValues.value = pickField("oxygen_saturation");
        treatmentMedicines.value = Array.isArray(data.treatment_medicines)
            ? data.treatment_medicines
            : [];
        // On s'assure que pour chaque jour de la semaine, on a une entrée dans treatmentChecks même si aucune prise n'est cochée
        for (const day of treatmentDays.value) ensureDayTracking(day.key);
        // On combine les checks actuels et historiques pour avoir une vue complète
        const allChecks = [
            ...(Array.isArray(data.treatment_checks)
                ? data.treatment_checks
                : []),
            ...historyData,
        ];
        if (allChecks.length) {
            for (const item of allChecks) {
                ensureDayTracking(item.check_date);
                treatmentChecks[item.check_date][item.medication_key] = Boolean(
                    item.taken,
                );
                if (
                    item.medication_key &&
                    !String(item.medication_key).includes("__dose_")
                ) {
                    treatmentChecks[item.check_date][
                        buildDoseKey(item.medication_key, 1)
                    ] = Boolean(item.taken);
                }
            }
        }

        const latestObservation = Array.isArray(data.doctor_observations)
            ? data.doctor_observations.find((o) => o?.doctor_observation)
            : null;

        doctorLatestObservation.value = latestObservation
            ? {
                  id: latestObservation.id,
                  date: latestObservation.date,
                  observation: latestObservation.doctor_observation,
              }
            : null;
}

// Appelé par @refresh depuis les composants enfants après un enregistrement
async function loadHealthData() {
    try {
        healthDataStore.invalidate();
        await healthDataStore.initialize();
        processHealthData();
    } catch (error) {
        const message =
            error?.response?.data?.message ||
            "Erreur lors du chargement des donnees de sante.";
        notifications.error(message);
    }
}

onMounted(async () => {
    try {
        await healthDataStore.initialize();
        processHealthData();
    } catch (error) {
        notifications.error("Erreur lors du chargement des donnees de sante.");
    }
});
</script>
