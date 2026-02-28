<template>
  <div class="mx-auto max-w-[1180px] px-5 py-4 sm:px-7">
    <header>
      <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.01em] text-slate-900">Données de santé</h1>
      <p class="mt-1 text-[13px] text-slate-500">Suivez vos indicateurs de santé dans le temps</p>
    </header>

    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-1 shadow-sm">
      <div class="grid grid-cols-3 gap-1">
        <button
          type="button"
          class="h-10 rounded-xl text-[15px] font-semibold transition"
          :class="activeTab === 'vitals' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
          @click="activeTab = 'vitals'"
        >
          Signes vitaux
        </button>
        <button
          type="button"
          class="h-10 rounded-xl text-[15px] font-semibold transition"
          :class="activeTab === 'labs' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
          @click="activeTab = 'labs'"
        >
          Analyses biologiques
        </button>
        <button
          type="button"
          class="h-10 rounded-xl text-[15px] font-semibold transition"
          :class="activeTab === 'treatments' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
          @click="activeTab = 'treatments'"
        >
          Traitements
        </button>
      </div>
    </section>

    <div v-if="showAddButton" class="mt-4 flex justify-end">
      <button
        type="button"
        class="inline-flex h-10 items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 text-[13px] font-semibold text-white shadow-[0_8px_16px_rgba(37,99,235,0.22)]"
        @click="ouvrirModalAjout"
      >
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M12 5v14M5 12h14" stroke-linecap="round" />
        </svg>
        {{ addButtonLabel }}
      </button>
    </div>

    <template v-if="activeTab === 'vitals'">
      <section class="mt-4 grid gap-3 lg:grid-cols-3">
        <article class="min-h-[118px] rounded-2xl border border-rose-200 bg-rose-50/70 p-3.5">
          <div class="mb-2.5 flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M20.8 8.2a4.9 4.9 0 0 0-8.8-3.1 4.9 4.9 0 0 0-8.8 3.1c0 5 8.8 10.8 8.8 10.8s8.8-5.8 8.8-10.8z" />
              </svg>
            </div>
            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold leading-none text-emerald-700">Normal</span>
          </div>
          <p class="text-[13px] font-medium text-slate-600">Rythme cardiaque</p>
          <p class="mt-0.5 text-[30px] font-semibold leading-none text-slate-900">{{ latestHeartRate }} <span class="text-[20px] font-medium text-slate-600">bpm</span></p>
        </article>

        <article class="min-h-[118px] rounded-2xl border border-sky-200 bg-sky-50/70 p-3.5">
          <div class="mb-2.5 flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-600">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M3 12h4l2-6 4 12 2-6h6" />
              </svg>
            </div>
            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold leading-none text-emerald-700">Normal</span>
          </div>
          <p class="text-[13px] font-medium text-slate-600">Tension artérielle</p>
          <p class="mt-0.5 text-[30px] font-semibold leading-none text-slate-900">{{ latestPressure }} <span class="text-[20px] font-medium text-slate-600">mmHg</span></p>
        </article>

        <article class="min-h-[118px] rounded-2xl border border-violet-200 bg-violet-50/70 p-3.5">
          <div class="mb-2.5 flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z" />
              </svg>
            </div>
            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold leading-none text-emerald-700">Normal</span>
          </div>
          <p class="text-[13px] font-medium text-slate-600">Saturation O₂</p>
          <p class="mt-0.5 text-[30px] font-semibold leading-none text-slate-900">{{ latestOxygen }} <span class="text-[20px] font-medium text-slate-600">%</span></p>
        </article>
      </section>

      <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="text-[34px] font-semibold leading-none text-slate-900">Évolution - 7 derniers jours</h2>
          <div class="flex items-center gap-2 text-[12px] font-medium">
            <button type="button" class="rounded-xl border px-3 py-1.5 transition" :class="selectedSeries.rhythm ? 'border-rose-500 bg-white text-rose-600' : 'border-slate-200 bg-slate-100 text-slate-500'" @click="basculerSerie('rhythm')">Rythme</button>
            <button type="button" class="rounded-xl border px-3 py-1.5 transition" :class="selectedSeries.tension ? 'border-blue-500 bg-white text-blue-600' : 'border-slate-200 bg-slate-100 text-slate-500'" @click="basculerSerie('tension')">Tension</button>
            <button type="button" class="rounded-xl border px-3 py-1.5 transition" :class="selectedSeries.saturation ? 'border-violet-500 bg-white text-violet-600' : 'border-slate-200 bg-slate-100 text-slate-500'" @click="basculerSerie('saturation')">Saturation</button>
          </div>
        </div>

        <div ref="chartRef" class="relative overflow-x-auto" @mousemove="gererMouvementGraphique" @mouseleave="gererSortieGraphique">
          <svg :viewBox="`0 0 ${chart.width} ${chart.height}`" class="h-[300px] w-full min-w-[980px]">
            <g stroke="#e2e8f0" stroke-dasharray="4 4">
              <line v-for="tick in yTicks" :key="`h-${tick}`" :x1="chart.left" :y1="convertirYEnPx(tick)" :x2="chart.width - chart.right" :y2="convertirYEnPx(tick)" />
              <line v-for="(label, i) in labels" :key="`v-${label}-${i}`" :x1="convertirXEnPx(i)" :y1="chart.top" :x2="convertirXEnPx(i)" :y2="chart.height - chart.bottom" />
            </g>

            <line v-if="hoverIndex !== null" :x1="convertirXEnPx(hoverIndex)" :y1="chart.top" :x2="convertirXEnPx(hoverIndex)" :y2="chart.height - chart.bottom" stroke="#cbd5e1" stroke-width="1.5" />
            <polyline v-for="series in visibleSeries" :key="`line-${series.key}`" fill="none" :stroke="series.color" stroke-width="3" :points="series.points" />

            <g v-for="series in visibleSeries" :key="`dots-${series.key}`">
              <circle v-for="(point, i) in series.values" :key="`${series.key}-${i}`" :cx="convertirXEnPx(i)" :cy="convertirYEnPx(point)" :r="hoverIndex === i ? 6 : 5" :fill="series.color" />
              <circle v-if="hoverIndex !== null" :cx="convertirXEnPx(hoverIndex)" :cy="convertirYEnPx(series.values[hoverIndex])" r="3.2" fill="white" />
            </g>

            <g fill="#94a3b8" font-size="13">
              <text v-for="tick in yTicks" :key="`y-${tick}`" :x="chart.left - 22" :y="convertirYEnPx(tick) + 4">{{ tick }}</text>
            </g>
            <g fill="#94a3b8" font-size="14">
              <text v-for="(label, i) in labels" :key="`x-${label}-${i}`" :x="convertirXEnPx(i) - 24" :y="chart.height - 8">{{ label }}</text>
            </g>
          </svg>

          <div v-if="hoverIndex !== null" class="pointer-events-none absolute rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-[12px] shadow-lg" :style="{ left: `${tooltipLeft}px`, top: `${tooltipTop}px` }">
            <p class="text-slate-900">{{ labels[hoverIndex] }}</p>
            <p v-if="selectedSeries.rhythm" class="mt-1 text-rose-500">Rythme cardiaque (bpm) : {{ heartRateValues[hoverIndex] }}</p>
            <p v-if="selectedSeries.tension" class="mt-1 text-blue-600">Tension systolique (mmHg) : {{ systolicValues[hoverIndex] }}</p>
            <p v-if="selectedSeries.saturation" class="mt-1 text-violet-600">Saturation O₂ (%) : {{ saturationValues[hoverIndex] }}</p>
          </div>
        </div>

        <div class="mt-1.5 flex items-center justify-center gap-3 text-[12px] font-medium">
          <span v-if="selectedSeries.rhythm" class="inline-flex items-center gap-1 text-rose-500"><span class="h-1.5 w-1.5 rounded-full bg-rose-500" />Rythme cardiaque (bpm)</span>
          <span v-if="selectedSeries.tension" class="inline-flex items-center gap-1 text-blue-500"><span class="h-1.5 w-1.5 rounded-full bg-blue-500" />Tension systolique (mmHg)</span>
          <span v-if="selectedSeries.saturation" class="inline-flex items-center gap-1 text-violet-500"><span class="h-1.5 w-1.5 rounded-full bg-violet-500" />Saturation O₂ (%)</span>
        </div>
      </section>
    </template>

    <template v-else-if="activeTab === 'labs'">
      <section class="mt-4 space-y-3">
        <article v-for="item in analyses" :key="item.id" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="flex items-center gap-3">
                <h3 class="text-[24px] font-semibold leading-none text-slate-900">{{ item.name }}</h3>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-[13px] font-semibold leading-none text-emerald-700">Normal</span>
              </div>
              <div class="mt-3 flex items-center gap-5 text-slate-900">
                <p class="text-[30px] font-semibold leading-none">{{ item.value }} {{ item.unit }}</p>
                <div class="inline-flex items-center gap-2 text-[12px] text-slate-600">
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" /></svg>
                  {{ item.date }}
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="rounded-lg border border-slate-300 px-3 py-1.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50"
                @click="ouvrirEditionAnalyse(item)"
              >
                Modifier
              </button>
              <button
                type="button"
                class="rounded-lg border border-rose-200 px-3 py-1.5 text-[12px] font-semibold text-rose-600 hover:bg-rose-50"
                @click="supprimerAnalyse(item)"
              >
                Supprimer
              </button>
            </div>
          </div>
        </article>
      </section>
    </template>

    <template v-else>
      <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-[20px] font-semibold leading-none text-slate-900">Calendrier des traitements</h2>
        <p class="mt-3 text-[12px] font-normal text-slate-600">Cliquez sur une journée pour gérer vos prises</p>

        <div class="mt-6 grid grid-cols-7 gap-2.5">
          <div v-for="day in treatmentDays" :key="day.key" class="text-center">
            <p class="mb-2 text-[12px] font-normal leading-none text-slate-600">{{ day.shortLabel }}</p>
            <button
              type="button"
              class="h-[92px] w-full rounded-xl border px-2 pb-2 pt-2 transition"
              :class="estJourComplet(day.key) ? 'border-[#08a84a] bg-[#cfddd6]' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
              @click="ouvrirJourTraitement(day)"
            >
              <p class="text-[32px] font-medium leading-none text-slate-900">{{ day.day }}</p>
              <div class="mt-2 flex justify-center">
                <span
                  class="inline-flex h-6 w-6 items-center justify-center rounded-full border"
                  :class="estJourComplet(day.key) ? 'border-[#08a84a] bg-[#08a84a] text-white' : 'border-slate-300 bg-white text-transparent'"
                >
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
              </div>
            </button>
          </div>
        </div>
      </section>

      <section class="mt-6">
        <h3 class="text-[20px] font-semibold leading-none text-slate-900">Traitements actifs</h3>
        <p v-if="!treatmentMedicines.length" class="mt-3 text-[13px] text-slate-500">
          Aucun traitement actif dans votre profil santé.
        </p>
        <div v-else class="mt-3 space-y-4">
          <article v-for="med in treatmentMedicines" :key="med.id" class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <p class="text-[19px] font-semibold leading-none text-slate-900">{{ med.name }}</p>
            <p class="mt-2 text-[12px] font-normal text-slate-700">{{ med.dose }} - {{ med.freq }}</p>
            <p class="mt-1 text-[12px] font-normal text-slate-500">{{ med.note }}</p>
          </article>
        </div>
      </section>
    </template>
  </div>

  <div v-if="showVitalsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 p-4">
    <div class="w-full max-w-[470px] rounded-3xl bg-white p-7 shadow-2xl">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-[24px] font-semibold leading-none text-slate-900">Ajouter une mesure</h3>
        <button type="button" class="text-slate-500 hover:text-slate-700" @click="showVitalsModal = false">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
        </button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Rythme cardiaque (bpm)</label>
          <input
            v-model="vitalForm.heartRate"
            type="number"
            min="20"
            max="260"
            placeholder="72"
            :disabled="vitalForm.skipHeartRate"
            class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
          />
          <label class="mt-2 inline-flex items-center gap-2 text-[14px] text-slate-600">
            <input v-model="vitalForm.skipHeartRate" type="checkbox" class="h-4 w-4 rounded border-slate-400" />
            Je n'ai pas mesuré aujourd'hui
          </label>
        </div>

        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Tension artérielle</label>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <input
                v-model="vitalForm.systolic"
                type="number"
                min="50"
                max="300"
                placeholder="120"
                :disabled="vitalForm.skipPressure"
                class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
              />
              <p class="mt-1 text-[13px] text-slate-500">Systolique</p>
            </div>
            <div>
              <input
                v-model="vitalForm.diastolic"
                type="number"
                min="30"
                max="220"
                placeholder="80"
                :disabled="vitalForm.skipPressure"
                class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
              />
              <p class="mt-1 text-right text-[13px] text-slate-500">Diastolique</p>
            </div>
          </div>
          <label class="mt-2 inline-flex items-center gap-2 text-[14px] text-slate-600">
            <input v-model="vitalForm.skipPressure" type="checkbox" class="h-4 w-4 rounded border-slate-400" />
            Je n'ai pas mesuré aujourd'hui
          </label>
        </div>

        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Saturation O₂ (%)</label>
          <input
            v-model="vitalForm.oxygen"
            type="number"
            min="0"
            max="100"
            step="0.1"
            placeholder="98"
            :disabled="vitalForm.skipOxygen"
            class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none disabled:opacity-60"
          />
          <label class="mt-2 inline-flex items-center gap-2 text-[14px] text-slate-600">
            <input v-model="vitalForm.skipOxygen" type="checkbox" class="h-4 w-4 rounded border-slate-400" />
            Je n'ai pas mesuré aujourd'hui
          </label>
        </div>

        <div>
          <label class="mb-2 block text-[18px] font-semibold text-slate-700">Date</label>
          <input v-model="vitalForm.date" type="date" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[14px] outline-none" />
        </div>

        <button type="button" class="mt-2 h-11 w-full rounded-2xl bg-emerald-600 text-[16px] font-semibold text-white hover:bg-emerald-700" @click="enregistrerMesure">
          Enregistrer
        </button>
      </div>
    </div>
  </div>

  <div v-if="showAnalysisModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 p-4">
    <div class="w-full max-w-[520px] rounded-3xl bg-white p-8 shadow-2xl">
      <div class="mb-5 flex items-center justify-between">
        <h3 class="text-[34px] font-semibold leading-none text-slate-900">{{ analysisModalTitle }}</h3>
        <button type="button" class="text-slate-500 hover:text-slate-700" @click="showAnalysisModal = false">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
        </button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="mb-2 block text-[13px] font-semibold text-slate-700">Type d'analyse</label>
          <select v-model="analysisForm.type" class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-800 outline-none focus:border-blue-500">
            <option value="">Sélectionnez</option>
            <option v-for="item in analysisTypeOptions" :key="item" :value="item">{{ item }}</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-2 block text-[13px] font-semibold text-slate-700">Valeur</label>
            <input v-model="analysisForm.value" type="text" placeholder="5.2" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[15px] outline-none focus:border-blue-500" />
          </div>
          <div>
            <label class="mb-2 block text-[13px] font-semibold text-slate-700">Unité</label>
            <input v-model="analysisForm.unit" type="text" placeholder="mmol/L" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[15px] outline-none focus:border-blue-500" />
          </div>
        </div>

        <div>
          <label class="mb-2 block text-[13px] font-semibold text-slate-700">Date</label>
          <input v-model="analysisForm.date" type="date" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[15px] outline-none focus:border-blue-500" />
        </div>

        <div>
          <label class="mb-2 block text-[13px] font-semibold text-slate-700">Notes (optionnel)</label>
          <textarea v-model="analysisForm.notes" rows="3" placeholder="Commentaires..." class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-[15px] outline-none focus:border-blue-500" />
        </div>

        <button type="button" class="mt-2 h-11 w-full rounded-2xl bg-emerald-600 text-[20px] font-semibold leading-none text-white hover:bg-emerald-700" @click="enregistrerAnalyse">
          {{ analysisSubmitLabel }}
        </button>
      </div>
    </div>
  </div>

  <div v-if="showTreatmentModal && selectedTreatmentDay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/45 p-3 sm:p-4">
    <div class="w-full max-w-[452px] rounded-[17px] bg-white px-8 pb-8 pt-8 shadow-2xl [font-family:Inter,system-ui,-apple-system,'Segoe_UI',Roboto,sans-serif]">
      <div class="mb-7 flex items-center justify-between">
        <h3 class="text-[32px] font-semibold leading-none tracking-[-0.01em] text-slate-900">{{ selectedTreatmentDay.fullLabel }}</h3>
        <button type="button" class="text-slate-500 hover:text-slate-700" @click="showTreatmentModal = false">
          <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
        </button>
      </div>

      <p class="text-[14px] text-slate-600">Marquez vos prises de médicaments</p>

      <div v-if="treatmentMedicines.length" class="mt-6 space-y-3.5">
        <article
          v-for="med in treatmentMedicines"
          :key="med.id"
          class="min-h-[126px] rounded-2xl border px-4 pb-4 pt-4 text-left transition"
          :class="estMedicamentComplet(selectedTreatmentDay.key, med) ? 'border-2 border-[#08a84a] bg-[#cfddd6]' : 'border border-slate-300 bg-slate-50'"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-[24px] font-semibold leading-none text-slate-900">{{ med.name }}</p>
              <p class="mt-1 text-[13px] leading-none text-slate-600">{{ med.dose }}</p>
              <p v-if="obtenirNombrePrises(med) > 1" class="mt-2 text-[13px] text-slate-500">
                {{ compterPrisesCompletees(selectedTreatmentDay.key, med) }}/{{ obtenirNombrePrises(med) }} prises effectuées
              </p>
            </div>
            <svg
              v-if="estMedicamentComplet(selectedTreatmentDay.key, med)"
              viewBox="0 0 24 24"
              class="h-6 w-6 text-emerald-600"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
            ><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
          </div>

          <div class="mt-3 flex flex-wrap gap-2">
            <button
              v-for="doseIndex in obtenirIndexPrises(med)"
              :key="`${med.id}-dose-${doseIndex}`"
              type="button"
              class="inline-flex h-10 items-center gap-2 rounded-xl border px-3.5 text-[14px] font-semibold transition"
              :class="estPriseCochee(selectedTreatmentDay.key, med.id, doseIndex) ? 'border-[#08a84a] bg-white text-slate-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
              @click="basculerPrise(selectedTreatmentDay.key, med, doseIndex)"
            >
              <span
                class="inline-flex h-5 w-5 items-center justify-center rounded-[4px] border"
                :class="estPriseCochee(selectedTreatmentDay.key, med.id, doseIndex) ? 'border-[#08a84a] bg-[#08a84a] text-white' : 'border-slate-400 bg-white text-transparent'"
              >
                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </span>
              <span>Prise {{ doseIndex }}</span>
            </button>
          </div>
        </article>
      </div>
      <p v-else class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-[14px] text-slate-600">
        Aucun traitement actif pour le moment. Ajoutez vos traitements dans la page Profil Santé.
      </p>

      <button type="button" class="mt-7 h-12 w-full rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-[16px] font-semibold leading-none text-white" @click="showTreatmentModal = false">
        Fermer
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import api from "@/services/api";

const activeTab = ref("vitals");
const showVitalsModal = ref(false);
const showAnalysisModal = ref(false);
const showTreatmentModal = ref(false);
const selectedTreatmentDayKey = ref(null);
const editingAnalysisId = ref(null);

const showAddButton = computed(() => activeTab.value !== "treatments");
const addButtonLabel = computed(() => (activeTab.value === "labs" ? "Ajouter une analyse" : "Ajouter une mesure"));
const analysisModalTitle = computed(() => (editingAnalysisId.value ? "Modifier une analyse" : "Ajouter une analyse"));
const analysisSubmitLabel = computed(() => (editingAnalysisId.value ? "Mettre à jour" : "Enregistrer"));

const analyses = ref([]);
const latestVital = ref(null);
const labels = ref([]);
const heartRateValues = ref([]);
const systolicValues = ref([]);
const saturationValues = ref([]);

const analysisTypeOptions = [
  "Glucose",
  "Cholestérol total",
  "Cholestérol HDL",
  "Cholestérol LDL",
  "Triglycérides",
  "Vitamine D",
  "Vitamine B12",
  "Ferritine",
  "TSH",
  "Créatinine",
];

const vitalForm = reactive({
  heartRate: "",
  systolic: "",
  diastolic: "",
  oxygen: "",
  skipHeartRate: false,
  skipPressure: false,
  skipOxygen: false,
  date: new Date().toISOString().slice(0, 10),
});

const analysisForm = reactive({
  type: "",
  value: "",
  unit: "",
  date: new Date().toISOString().slice(0, 10),
  notes: "",
});

const chart = { width: 980, height: 350, left: 70, right: 40, top: 40, bottom: 30, minY: 0, maxY: 140 };
const yTicks = [0, 35, 70, 105, 140];
const selectedSeries = reactive({ rhythm: true, tension: true, saturation: true });
const hoveredIndex = ref(null);
const chartRef = ref(null);

const latestHeartRate = computed(() => latestVital.value?.heart_rate ?? "--");
const latestPressure = computed(() => {
  const s = latestVital.value?.systolic_pressure;
  const d = latestVital.value?.diastolic_pressure;
  return s && d ? `${s}/${d}` : "--/--";
});
const latestOxygen = computed(() => latestVital.value?.oxygen_saturation ?? "--");

const treatmentDays = ref(construire7DerniersJours());
const treatmentMedicines = ref([]);
const treatmentChecks = reactive({});

const plottedSeries = computed(() => [
  { key: "heart", color: "#ef4444", values: heartRateValues.value, points: construirePoints(heartRateValues.value) },
  { key: "sys", color: "#3b82f6", values: systolicValues.value, points: construirePoints(systolicValues.value) },
  { key: "sat", color: "#8b5cf6", values: saturationValues.value, points: construirePoints(saturationValues.value) },
]);
const visibleSeries = computed(() =>
  plottedSeries.value.filter((series) => {
    if (series.key === "heart") return selectedSeries.rhythm;
    if (series.key === "sys") return selectedSeries.tension;
    if (series.key === "sat") return selectedSeries.saturation;
    return false;
  }),
);

const selectedTreatmentDay = computed(() =>
  treatmentDays.value.find((day) => day.key === selectedTreatmentDayKey.value) ?? null,
);

const hoverIndex = computed(() => hoveredIndex.value);
const tooltipTop = 84;
const tooltipLeft = computed(() => {
  if (hoverIndex.value === null) return 0;
  const x = convertirXEnPx(hoverIndex.value);
  return Math.min(Math.max(x + 10, chart.left + 8), chart.width - 360);
});

// Cette fonction ouvre la bonne modale selon l'onglet actif.
function ouvrirModalAjout() {
  if (activeTab.value === "labs") {
    editingAnalysisId.value = null;
    reinitialiserFormulaireAnalyse();
    showAnalysisModal.value = true;
    return;
  }
  if (activeTab.value === "vitals") {
    showVitalsModal.value = true;
  }
}

// Cette fonction convertit une valeur en nombre ou renvoie null.
function convertirNombreOuNull(value) {
  if (value === null || value === undefined || value === "") return null;
  const n = Number(value);
  return Number.isFinite(n) ? n : null;
}

// Cette fonction convertit une date en format ISO (YYYY-MM-DD).
function convertirDateIso(dateValue) {
  if (!dateValue) return new Date().toISOString().slice(0, 10);
  return String(dateValue).slice(0, 10);
}

// Cette fonction formate une date courte pour l'axe du graphique.
function formaterLibelle(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  return date.toLocaleDateString("fr-FR", { day: "2-digit", month: "short" });
}

// Cette fonction formate une date en affichage francais.
function formaterDate(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  return date.toLocaleDateString("fr-FR");
}

// Cette fonction remet a zero les champs du formulaire d'analyse.
function reinitialiserFormulaireAnalyse() {
  analysisForm.type = "";
  analysisForm.value = "";
  analysisForm.unit = "";
  analysisForm.date = new Date().toISOString().slice(0, 10);
  analysisForm.notes = "";
}

// Cette fonction remplace les valeurs invalides par la derniere valeur valide.
function normaliserSerie(values, fallback = 0) {
  let last = fallback;
  return (Array.isArray(values) ? values : []).map((v) => {
    const n = Number(v);
    if (Number.isFinite(n)) {
      last = n;
      return n;
    }
    return last;
  });
}

// Cette fonction construit la liste des 7 derniers jours.
function construire7DerniersJours() {
  return Array.from({ length: 7 }).map((_, idx) => {
    const date = new Date();
    date.setDate(date.getDate() - (6 - idx));
    const key = date.toISOString().slice(0, 10);
    return {
      key,
      shortLabel: date.toLocaleDateString("fr-FR", { weekday: "short" }).replace(".", ""),
      fullLabel: date.toLocaleDateString("fr-FR", { weekday: "long", day: "numeric", month: "long" }),
      day: date.getDate(),
    };
  });
}

// Cette fonction initialise les cases de suivi pour un jour donne.
function assurerSuiviJour(dayKey) {
  if (!treatmentChecks[dayKey]) treatmentChecks[dayKey] = {};
  for (const med of treatmentMedicines.value) {
    const doses = obtenirNombrePrises(med);
    for (let i = 1; i <= doses; i += 1) {
      const key = construireClePrise(med.id, i);
      if (typeof treatmentChecks[dayKey][key] !== "boolean") {
        treatmentChecks[dayKey][key] = false;
      }
    }
  }
}

// Cette fonction charge les donnees de sante depuis l'API.
async function chargerDonneesSante() {
  const res = await api.get("/health-data/overview", { params: { days: 7 } });
  const data = res?.data?.data ?? {};

  latestVital.value = data.latest_vitals ?? null;
  analyses.value = Array.isArray(data.lab_results)
    ? data.lab_results.map((item) => ({
        id: item.id,
        name: item.analysis_type,
        value: item.value,
        unit: item.unit ?? "",
        date: formaterDate(item.analysis_date),
        analysisDate: item.analysis_date,
        notes: item.notes ?? "",
      }))
    : [];

  const chartData = data.vitals_chart ?? {};
  const labelSource = Array.isArray(chartData.labels) && chartData.labels.length > 0
    ? chartData.labels
    : treatmentDays.value.map((day) => day.key);

  labels.value = labelSource.map(formaterLibelle);
  heartRateValues.value = normaliserSerie(chartData.heart_rate, 70);
  systolicValues.value = normaliserSerie(chartData.systolic_pressure, 120);
  saturationValues.value = normaliserSerie(chartData.oxygen_saturation, 98);
  treatmentMedicines.value = Array.isArray(data.treatment_medicines) ? data.treatment_medicines : [];

  for (const day of treatmentDays.value) assurerSuiviJour(day.key);

  if (Array.isArray(data.treatment_checks)) {
    for (const item of data.treatment_checks) {
      assurerSuiviJour(item.check_date);
      treatmentChecks[item.check_date][item.medication_key] = Boolean(item.taken);
      if (item.medication_key && !String(item.medication_key).includes("__dose_")) {
        treatmentChecks[item.check_date][construireClePrise(item.medication_key, 1)] = Boolean(item.taken);
      }
    }
  }
}

// Cette fonction enregistre une mesure vitale puis recharge les donnees.
async function enregistrerMesure() {
  const measuredAt = convertirDateIso(vitalForm.date);

  await api.post("/health-data/vitals", {
    measured_at: measuredAt,
    heart_rate: vitalForm.skipHeartRate ? null : convertirNombreOuNull(vitalForm.heartRate),
    systolic_pressure: vitalForm.skipPressure ? null : convertirNombreOuNull(vitalForm.systolic),
    diastolic_pressure: vitalForm.skipPressure ? null : convertirNombreOuNull(vitalForm.diastolic),
    oxygen_saturation: vitalForm.skipOxygen ? null : convertirNombreOuNull(vitalForm.oxygen),
  });
  vitalForm.heartRate = "";
  vitalForm.systolic = "";
  vitalForm.diastolic = "";
  vitalForm.oxygen = "";
  vitalForm.skipHeartRate = false;
  vitalForm.skipPressure = false;
  vitalForm.skipOxygen = false;
  vitalForm.date = new Date().toISOString().slice(0, 10);
  showVitalsModal.value = false;
  await chargerDonneesSante();
}

// Cette fonction enregistre une analyse avec validation simple des champs.
async function enregistrerAnalyse() {
  const payload = {
    analysis_type: analysisForm.type,
    value: convertirNombreOuNull(analysisForm.value),
    unit: analysisForm.unit || null,
    analysis_date: convertirDateIso(analysisForm.date),
    notes: analysisForm.notes || null,
  };

  if (editingAnalysisId.value) {
    await api.put(`/health-data/labs/${editingAnalysisId.value}`, payload);
  } else {
    await api.post("/health-data/labs", payload);
  }

  editingAnalysisId.value = null;
  reinitialiserFormulaireAnalyse();
  showAnalysisModal.value = false;
  await chargerDonneesSante();
}

// Cette fonction pre-remplit le formulaire pour modifier une analyse.
function ouvrirEditionAnalyse(item) {
  editingAnalysisId.value = item.id;
  analysisForm.type = item.name ?? "";
  analysisForm.value = String(item.value ?? "");
  analysisForm.unit = item.unit ?? "";
  analysisForm.date = item.analysisDate ?? new Date().toISOString().slice(0, 10);
  analysisForm.notes = item.notes ?? "";
  showAnalysisModal.value = true;
}

// Cette fonction supprime une analyse apres confirmation utilisateur.
async function supprimerAnalyse(item) {
  const ok = window.confirm(`Supprimer l'analyse "${item.name}" ?`);
  if (!ok) return;
  await api.delete(`/health-data/labs/${item.id}`);
  await chargerDonneesSante();
}

// Cette fonction convertit un index de point en position X du graphique.
function convertirXEnPx(index) {
  if (labels.value.length <= 1) return chart.left;
  const usable = chart.width - chart.left - chart.right;
  const step = usable / (labels.value.length - 1);
  return chart.left + index * step;
}

// Cette fonction convertit une valeur en position Y du graphique.
function convertirYEnPx(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return chart.height - chart.bottom;
  const usable = chart.height - chart.top - chart.bottom;
  const ratio = (n - chart.minY) / (chart.maxY - chart.minY);
  return chart.height - chart.bottom - ratio * usable;
}

// Cette fonction construit la chaine de points SVG pour une courbe.
function construirePoints(values) {
  return values.map((v, i) => `${convertirXEnPx(i)},${convertirYEnPx(v)}`).join(" ");
}

// Cette fonction met a jour le point survole dans le graphique.
function gererMouvementGraphique(event) {
  if (!chartRef.value || labels.value.length === 0) return;
  const rect = chartRef.value.getBoundingClientRect();
  const localX = ((event.clientX - rect.left) / rect.width) * chart.width;
  const usable = chart.width - chart.left - chart.right;
  const step = labels.value.length > 1 ? usable / (labels.value.length - 1) : usable;
  const nearest = Math.round((localX - chart.left) / step);
  hoveredIndex.value = Math.min(Math.max(nearest, 0), labels.value.length - 1);
}

// Cette fonction retire le survol quand la souris sort du graphique.
function gererSortieGraphique() {
  hoveredIndex.value = null;
}

// Cette fonction active ou desactive une serie du graphique.
function basculerSerie(key) {
  const activeCount = [selectedSeries.rhythm, selectedSeries.tension, selectedSeries.saturation].filter(Boolean).length;
  if (selectedSeries[key] && activeCount === 1) return;
  selectedSeries[key] = !selectedSeries[key];
}

// Cette fonction ouvre la modale de suivi pour un jour precis.
function ouvrirJourTraitement(day) {
  assurerSuiviJour(day.key);
  selectedTreatmentDayKey.value = day.key;
  showTreatmentModal.value = true;
}

// Cette fonction retourne le nombre de prises quotidien pour un medicament.
function obtenirNombrePrises(med) {
  const count = Number(med?.doses_per_day ?? 1);
  if (!Number.isFinite(count)) return 1;
  return Math.max(1, Math.min(Math.round(count), 12));
}

// Cette fonction genere la liste des numeros de prises.
function obtenirIndexPrises(med) {
  return Array.from({ length: obtenirNombrePrises(med) }, (_, idx) => idx + 1);
}

// Cette fonction cree une cle unique pour une prise de medicament.
function construireClePrise(medId, doseIndex) {
  return `${medId}__dose_${doseIndex}`;
}

// Cette fonction verifie si une prise est marquee comme effectuee.
function estPriseCochee(dayKey, medId, doseIndex) {
  return Boolean(treatmentChecks[dayKey]?.[construireClePrise(medId, doseIndex)]);
}

// Cette fonction compte le nombre de prises cochees pour un jour.
function compterPrisesCompletees(dayKey, med) {
  const doses = obtenirNombrePrises(med);
  let completed = 0;
  for (let i = 1; i <= doses; i += 1) {
    if (estPriseCochee(dayKey, med.id, i)) completed += 1;
  }
  return completed;
}

// Cette fonction verifie si toutes les prises du medicament sont faites.
function estMedicamentComplet(dayKey, med) {
  return compterPrisesCompletees(dayKey, med) >= obtenirNombrePrises(med);
}

// Cette fonction envoie l'etat des prises de traitement au serveur.
async function synchroniserSuiviTraitements() {
  if (!treatmentMedicines.value.length) return;

  const checks = [];
  for (const day of treatmentDays.value) {
    assurerSuiviJour(day.key);
    for (const med of treatmentMedicines.value) {
      const doses = obtenirNombrePrises(med);
      for (let i = 1; i <= doses; i += 1) {
        const doseKey = construireClePrise(med.id, i);
        checks.push({
          check_date: day.key,
          medication_key: doseKey,
          medication_name: med.name,
          dose: med.dose,
          taken: Boolean(treatmentChecks[day.key][doseKey]),
        });
      }
    }
  }
  await api.post("/health-data/treatment-checks/sync", { checks });
}

// Cette fonction coche ou decoche une prise puis synchronise le suivi.
async function basculerPrise(dayKey, med, doseIndex) {
  assurerSuiviJour(dayKey);
  const key = construireClePrise(med.id, doseIndex);
  treatmentChecks[dayKey][key] = !treatmentChecks[dayKey][key];
  await synchroniserSuiviTraitements();
}

// Cette fonction verifie si tous les medicaments du jour sont complets.
function estJourComplet(dayKey) {
  const dayChecks = treatmentChecks[dayKey];
  if (!dayChecks) return false;
  if (!treatmentMedicines.value.length) return false;
  return treatmentMedicines.value.every((med) => estMedicamentComplet(dayKey, med));
}

onMounted(async () => {
  await chargerDonneesSante();
});
</script>


