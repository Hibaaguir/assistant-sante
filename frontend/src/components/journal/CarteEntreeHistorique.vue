<template>
  <article class="rounded-2xl border border-slate-300 bg-slate-50 px-4 py-4 shadow-[0_1px_0_rgba(15,23,42,0.04)] sm:px-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[12px] text-slate-600">
          <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M8 2v4M16 2v4M4 9h16M5 5h14v15H5z" />
            </svg>
            {{ entree.dateLabel }}
          </span>

          <template v-if="filterType === 'all'">
            <span>Sommeil: <b class="text-slate-900">{{ libellerSommeil(entree.sleep) }}</b></span>
            <span>Stress: <b class="text-slate-900">{{ libellerStress(entree.stress) }}</b></span>
            <span>Energie: <b class="text-slate-900">{{ libellerEnergie(entree.energy) }}</b></span>
            <span>Repas: <b class="text-slate-900">{{ entree.meals.length }} repas</b></span>
            <span>Hydratation: <b class="text-slate-900">{{ entree.hydration }}L</b></span>
            <span>Activite: <b class="text-slate-900">{{ entree.activityType }} {{ entree.activityDuration }}min</b></span>
            <span>Tabac: <b class="text-slate-900">{{ resumerTabac(entree) }}</b></span>
          </template>
          <span v-else-if="filterType === 'sleep'">Sommeil: <b class="text-slate-900">{{ libellerSommeil(entree.sleep) }}</b></span>
          <span v-else-if="filterType === 'stress'">Stress: <b class="text-slate-900">{{ libellerStress(entree.stress) }}</b></span>
          <span v-else-if="filterType === 'energy'">Energie: <b class="text-slate-900">{{ libellerEnergie(entree.energy) }}</b></span>
          <span v-else-if="filterType === 'nutrition'">Repas: <b class="text-slate-900">{{ entree.meals.length }} repas</b></span>
          <span v-else-if="filterType === 'hydration'">Hydratation: <b class="text-slate-900">{{ entree.hydration }}L</b></span>
          <span v-else-if="filterType === 'activity'">Activite: <b class="text-slate-900">{{ entree.activityType }} {{ entree.activityDuration }}min</b></span>
          <span v-else-if="filterType === 'date'">Date: <b class="text-slate-900">{{ entree.dateIso }}</b></span>
          <span v-else-if="filterType === 'month'">Mois: <b class="text-slate-900">{{ libellerMois(entree.dateIso) }}</b></span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button type="button" class="rounded-md p-1 text-slate-500 transition-colors hover:bg-slate-200 hover:text-slate-700" @click="$emit('edit')">
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z" />
          </svg>
        </button>
        <button type="button" class="rounded-md p-1 text-pink-500 transition-colors hover:bg-pink-50 hover:text-pink-700" @click="$emit('request-delete')">
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 6h18" />
            <path d="M8 6V4h8v2" />
            <path d="M19 6l-1 14H6L5 6" />
          </svg>
        </button>
      </div>
    </div>
  </article>
</template>

<script setup>
/*
  Carte d'une entree dans l'historique du journal.
  Elle adapte les informations affichees selon le filtre actif.
  Les actions utilisateur sont remontees au parent via emits.
*/

defineProps({
  entree: {
    type: Object,
    required: true
  },
  filterType: {
    type: String,
    default: 'all'
  }
})

defineEmits(['edit', 'request-delete'])

// Cette fonction transforme les heures de sommeil en texte simple.
const libellerSommeil = (hours) => {
  const h = Math.floor(hours)
  const m = Math.round((hours - h) * 60)
  return m ? `${h}h ${m}min` : `${h}h`
}

// Cette fonction retourne un niveau de stress lisible.
const libellerStress = (value) => {
  if (value >= 8) return 'Eleve'
  if (value <= 3) return 'Faible'
  return 'Modere'
}

// Cette fonction retourne un niveau d'energie lisible.
const libellerEnergie = (value) => {
  if (value >= 8) return 'Excellente'
  if (value <= 4) return 'Faible'
  return 'Bonne'
}

// Cette fonction formate la date ISO pour afficher le mois et l'annee.
const libellerMois = (iso) => {
  return new Date(`${iso}T00:00:00`).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
}

// Cette fonction construit un resume simple de la consommation de tabac.
const resumerTabac = (entree) => {
  if (!entree.tobacco) return 'Non'
  const parts = []
  if (entree.tobaccoTypes?.cigarette && typeof entree.cigarettesPerDay === 'number') {
    parts.push(`Cigarette • ${entree.cigarettesPerDay}/j`)
  }
  if (entree.tobaccoTypes?.vape && entree.vapeFrequency && typeof entree.vapeLiquidMl === 'number') {
    parts.push(`Vape • ${entree.vapeFrequency} • ${entree.vapeLiquidMl} liquide`)
  }
  if (!parts.length) return 'Oui'
  return parts.join(', ')
}
</script>
