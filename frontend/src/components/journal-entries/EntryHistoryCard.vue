<!-- Composant de carte d'historique d'entrée du journal, affichant les détails d'une entrée spécifique avec des actions pour éditer ou supprimer l'entrée. Les champs affichés sont filtrés en fonction du type de filtre sélectionné  -->
<template>
    <article
        class="rounded-2xl border-2 border-blue-300 bg-white px-5 py-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-blue-400"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div
                class="flex flex-wrap items-center gap-x-4 gap-y-2 text-base font-medium text-slate-800 min-w-0 flex-1"
            >
                <!-- Date -->
                <span
                    class="inline-flex items-center gap-1.5 font-semibold text-slate-900"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-4 w-4 text-slate-600"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M8 2v4M16 2v4M4 9h16M5 5h14v15H5z" />
                    </svg>
                    {{ entree.dateLabel }}
                </span>

                <!-- Champs filtrés -->
                <span
                    v-for="champ in champsVisibles"
                    :key="champ.label"
                    class="text-slate-700 font-medium"
                >
                    <span class="text-slate-600">{{ champ.label }}:</span>
                    <b class="text-slate-900 ml-1">{{ champ.valeur }}</b>
                </span>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <BaseButton
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="$emit('edit')"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z" />
                    </svg>
                </BaseButton>
                <BaseButton
                    type="button"
                    variant="danger"
                    size="sm"
                    @click="$emit('request-delete')"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M3 6h18" />
                        <path d="M8 6V4h8v2" />
                        <path d="M19 6l-1 14H6L5 6" />
                    </svg>
                </BaseButton>
            </div>
        </div>
    </article>
</template>

<script setup>
import { computed } from "vue";
import BaseButton from "@/components/ui/BaseButton.vue";

const props = defineProps({
    entree: { type: Object, required: true },
    filterType: { type: String, default: "all" },
});

defineEmits(["edit", "request-delete"]);

// Formate la durée de sommeil en heures et minutes
const fmtSommeil = (h) => {
    const hh = Math.floor(h),
        mm = Math.round((h - hh) * 60);
    return mm ? `${hh}h ${mm}min` : `${hh}h`;
};
const fmtStress = (v) => (v >= 8 ? "Élevé" : v <= 3 ? "Faible" : "Modéré");
const fmtEnergie = (v) => {
    if (!v && v !== 0) return "—";
    if (v >= 9) return "Optimale";
    if (v >= 7) return "Satisfaisante";
    if (v >= 5) return "Modérée";
    if (v >= 3) return "Insuffisante";
    return "Altérée";
};
// Formate une date ISO en mois + année (ex: avril 2026)
const fmtMois = (iso) =>
    new Date(`${iso}T00:00:00`).toLocaleDateString("fr-FR", {
        month: "long",
        year: "numeric",
    });

// Formatage simple des données liées au tabac pour l’affichage
const fmtTabac = ({ tobacco, tobaccoTypes, cigarettesPerDay, vapeLiquidMl }) => {
    if (!tobacco) return "Non";
    const parts = [];
    if (tobaccoTypes?.cigarette && cigarettesPerDay != null) {
        parts.push(`Cigarette • ${cigarettesPerDay}/j`);
    }
    if (tobaccoTypes?.vape && vapeLiquidMl != null) {
        parts.push(`Vape • ${vapeLiquidMl} taffes/j`);
    }
    return parts.length > 0 ? parts.join(", ") : "Oui";
};

// Table de correspondance filtre → champ
const CHAMPS = {
    sleep: (e) => ({ label: "Sommeil", valeur: fmtSommeil(e.sleep) }),
    stress: (e) => ({ label: "Stress", valeur: fmtStress(e.stress) }),
    energy: (e) => ({ label: "Énergie", valeur: fmtEnergie(e.energy) }),
    nutrition: (e) => ({ label: "Repas", valeur: `${e.meals.length} repas` }),
    hydration: (e) => ({ label: "Hydratation", valeur: `${e.hydration} L` }),
    activity: (e) => ({
        label: "Activité",
        valeur: e.activities?.length
            ? e.activities.map((a) => a.type).join(", ")
            : null,
    }),
    tobacco: (e) => ({ label: "Tabac", valeur: fmtTabac(e) }),
};
// si date ou all ou month on affiche tous les champs, sinon on affiche uniquement le champ correspondant au filtre sélectionné (sommeil, stress, énergie, etc.) et on formate les valeurs pour les rendre plus lisibles (ex: 7.5h de sommeil devient "7h 30min", un niveau de stress de 9 devient "Élevé", etc.)
const champsVisibles = computed(() => {
    const e = props.entree;
    const showAll = ["date", "month", "all"].includes(props.filterType);
    if (showAll) {
        return Object.values(CHAMPS)
            .map((fn) => fn(e))
            .filter((c) => c?.valeur);
    }
    // Si un filtre spécifique est sélectionné, on affiche uniquement le champ correspondant
    const fn = CHAMPS[props.filterType];
    if (!fn) return [];
    const champ = fn(e);
    return champ?.valeur ? [champ] : [];
});
// CHAMPS : dictionnaire de fonctions qui définit comment calculer et formater chaque champ
// champ : résultat obtenu après exécution d’une fonction de CHAMPS sur une entrée (données prêtes à afficher)
// champsVisibles : liste des champs filtrés et formatés à afficher selon le type de filtre sélectionné
// c : représente chaque élément de la liste des champs après transformation (résultat de map)
</script>
