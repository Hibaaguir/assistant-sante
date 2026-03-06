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

    <div v-if="showAddButton" class="mt-4 flex justify-end gap-2">
      <button
        v-if="activeTab === 'labs'"
        type="button"
        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-[13px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
        @click="showLabsFilters = !showLabsFilters"
      >
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M3 5h18M7 12h10M10 19h4" stroke-linecap="round" />
        </svg>
        Filtrer
      </button>

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
      <section class="mt-6 grid gap-5 xl:grid-cols-3">
        <article class="min-h-[162px] rounded-2xl border border-[#efc4cc] bg-[#fdf2f5] px-6 py-6">
          <div class="flex items-start justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#f9e3e9] text-[#ff2458]">
              <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                <path d="M20.8 8.2a4.9 4.9 0 0 0-8.8-3.1 4.9 4.9 0 0 0-8.8 3.1c0 5 8.8 10.8 8.8 10.8s8.8-5.8 8.8-10.8z" />
              </svg>
            </div>
            <span class="rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]">Normal</span>
          </div>
          <p class="mt-4 text-[16px] leading-none text-slate-700">Rythme cardiaque</p>
          <p class="mt-3 text-[40px] font-semibold leading-none text-slate-900">{{ latestHeartRate }} <span class="text-[34px] font-medium text-slate-700">bpm</span></p>
        </article>

        <article class="min-h-[162px] rounded-2xl border border-[#a8cdfb] bg-[#ebf6fe] px-6 py-6">
          <div class="flex items-start justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#d5e7fd] text-[#2c67f6]">
              <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                <path d="M3 12h4l2-6 4 12 2-6h6" />
              </svg>
            </div>
            <span class="rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]">Normal</span>
          </div>
          <p class="mt-4 text-[16px] leading-none text-slate-700">Tension artérielle</p>
          <p class="mt-3 text-[40px] font-semibold leading-none text-slate-900">{{ latestPressure }} <span class="text-[34px] font-medium text-slate-700">mmHg</span></p>
        </article>

        <article class="min-h-[162px] rounded-2xl border border-[#dbc6f7] bg-[#f6f0fc] px-6 py-6">
          <div class="flex items-start justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#eee2fc] text-[#8a2cff]">
              <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                <path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z" />
              </svg>
            </div>
            <span class="rounded-full bg-[#dff6e4] px-3 py-1 text-[12px] leading-none text-[#08aa48]">Normal</span>
          </div>
          <p class="mt-4 text-[16px] leading-none text-slate-700">Saturation O₂</p>
          <p class="mt-3 text-[40px] font-semibold leading-none text-slate-900">{{ latestOxygen }} <span class="text-[34px] font-medium text-slate-700">%</span></p>
        </article>
      </section>

      <section class="mt-8 rounded-2xl border border-slate-200 bg-[#f8f9fb] px-8 py-8">
        <h2 class="text-[20px] font-semibold leading-none text-slate-900">Historique des mesures</h2>

        <div class="mt-8 grid gap-4 lg:grid-cols-2">
          <div>
            <label class="mb-3 block text-[14px] font-semibold text-slate-800">Filtrer par date</label>
            <div class="relative">
              <input
                v-model="vitalFilterDate"
                type="date"
                class="h-12 w-full rounded-2xl border border-slate-300 bg-white pl-5 pr-12 text-[16px] text-slate-900 outline-none focus:border-blue-500"
              />
              <svg viewBox="0 0 24 24" class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" />
              </svg>
            </div>
          </div>

          <div>
            <label class="mb-3 block text-[14px] font-semibold text-slate-800">Filtrer par type</label>
            <select
              v-model="vitalFilterType"
              class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-5 text-[16px] text-slate-900 outline-none focus:border-blue-500"
            >
              <option value="all">Tous les signes</option>
              <option value="heart">Rythme cardiaque</option>
              <option value="pressure">Tension artérielle</option>
              <option value="oxygen">Saturation O₂</option>
            </select>
          </div>
        </div>

        <div class="mt-6 space-y-3.5">
          <article
            v-for="day in filteredVitalHistory"
            :key="day.dateKey"
            class="rounded-2xl border border-slate-200 bg-white px-5 py-5"
          >
            <div class="mb-4 flex items-center gap-3 text-slate-900">
              <svg viewBox="0 0 24 24" class="h-6 w-6 text-slate-500" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" />
              </svg>
              <h3 class="text-[22px] font-semibold leading-none">{{ day.longDate }}</h3>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
              <article v-if="vitalFilterType === 'all' || vitalFilterType === 'heart'" class="rounded-xl border border-[#efc4cc] bg-[#fdf2f5] px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#f9e3e9] text-[#ff2458]">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9">
                      <path d="M20.8 8.2a4.9 4.9 0 0 0-8.8-3.1 4.9 4.9 0 0 0-8.8 3.1c0 5 8.8 10.8 8.8 10.8s8.8-5.8 8.8-10.8z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-[13px] leading-none text-slate-700">Rythme cardiaque</p>
                    <p class="mt-2 text-[20px] font-semibold leading-none text-slate-900">{{ day.heartRate }} <span class="text-[18px] font-medium text-slate-700">bpm</span></p>
                    <span class="mt-2 inline-block rounded-full bg-[#dff6e4] px-2.5 py-0.5 text-[12px] leading-none text-[#08aa48]">Normal</span>
                  </div>
                </div>
              </article>

              <article v-if="vitalFilterType === 'all' || vitalFilterType === 'pressure'" class="rounded-xl border border-[#a8cdfb] bg-[#ebf6fe] px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#d5e7fd] text-[#2c67f6]">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9">
                      <path d="M3 12h4l2-6 4 12 2-6h6" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-[13px] leading-none text-slate-700">Tension artérielle</p>
                    <p class="mt-2 text-[20px] font-semibold leading-none text-slate-900">{{ day.pressure }} <span class="text-[18px] font-medium text-slate-700">mmHg</span></p>
                    <span class="mt-2 inline-block rounded-full bg-[#dff6e4] px-2.5 py-0.5 text-[12px] leading-none text-[#08aa48]">Normal</span>
                  </div>
                </div>
              </article>

              <article v-if="vitalFilterType === 'all' || vitalFilterType === 'oxygen'" class="rounded-xl border border-[#dbc6f7] bg-[#f6f0fc] px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eee2fc] text-[#8a2cff]">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9">
                      <path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-[13px] leading-none text-slate-700">Saturation O₂</p>
                    <p class="mt-2 text-[20px] font-semibold leading-none text-slate-900">{{ day.oxygen }} <span class="text-[18px] font-medium text-slate-700">%</span></p>
                    <span class="mt-2 inline-block rounded-full bg-[#dff6e4] px-2.5 py-0.5 text-[12px] leading-none text-[#08aa48]">Normal</span>
                  </div>
                </div>
              </article>
            </div>
          </article>

          <div v-if="!filteredVitalHistory.length" class="rounded-2xl border border-slate-200 bg-white px-6 py-5 text-[14px] text-slate-600">
            Aucune mesure ne correspond aux filtres selectionnes.
          </div>
        </div>
      </section>

    </template>

    <template v-else-if="activeTab === 'labs'">
      <section class="mt-4 space-y-3">
        <div v-if="showLabsFilters" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="grid gap-3 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-[12px] font-semibold text-slate-600">Type</label>
              <select v-model="labsFilterType" class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-[13px] text-slate-800 outline-none focus:border-blue-500">
                <option value="">Tous</option>
                <option v-for="type in labTypeOptions" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-[12px] font-semibold text-slate-600">Date</label>
              <input v-model="labsFilterDate" type="date" class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-[13px] text-slate-800 outline-none focus:border-blue-500" />
            </div>
            <div>
              <label class="mb-1 block text-[12px] font-semibold text-slate-600">Recherche</label>
              <input v-model.trim="labsFilterQuery" type="text" placeholder="Ex: CRP, TSH..." class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-[13px] text-slate-800 outline-none focus:border-blue-500" />
            </div>
          </div>
        </div>

        <article v-for="item in filteredAnalyses" :key="item.id" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="flex items-center gap-3">
                <h3 class="text-[16px] font-semibold leading-none text-slate-900">{{ item.name }}</h3>
                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold leading-none text-emerald-700">Normal</span>
              </div>
              <div class="mt-2 flex items-center gap-4 text-slate-900">
                <p class="text-[22px] font-semibold leading-none">{{ item.value }} {{ item.unit }}</p>
                <div class="inline-flex items-center gap-2 text-[12px] text-slate-600">
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" /></svg>
                  {{ item.date }}
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50"
                @click="ouvrirEditionAnalyse(item)"
              >
                Modifier
              </button>
              <button
                type="button"
                class="rounded-lg border border-rose-200 px-3 py-1.5 text-[11px] font-semibold text-rose-600 hover:bg-rose-50"
                @click="supprimerAnalyse(item)"
              >
                Supprimer
              </button>
            </div>
          </div>
        </article>

        <div v-if="!filteredAnalyses.length" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-600">
          Aucune analyse ne correspond aux filtres.
        </div>
      </section>
    </template>

    <template v-else>
      <section class="mt-4 flex justify-end">
        <button
          type="button"
          class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-[13px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
          @click="showTreatmentHistory = !showTreatmentHistory"
        >
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 12a9 9 0 1 0 3-6.7M3 4v5h5" stroke-linecap="round" />
          </svg>
          {{ showTreatmentHistory ? "Masquer historique" : "Voir historique" }}
        </button>
      </section>

      <section v-if="showTreatmentHistory" class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-[22px] font-semibold leading-none text-slate-900">Historique des prises</h2>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
          <article class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
            <p class="text-[13px] text-slate-700">Taux d'observance</p>
            <p class="mt-1 text-[24px] font-semibold leading-none text-slate-900">{{ treatmentHistoryStats.observance }}%</p>
            <p class="mt-2 text-[12px] text-slate-600">{{ treatmentHistoryStats.completeDays }}/{{ treatmentHistoryStats.totalDays }} jours complets</p>
          </article>
          <article class="rounded-2xl border border-blue-200 bg-blue-50/60 p-4">
            <p class="text-[13px] text-slate-700">Prises totales</p>
            <p class="mt-1 text-[24px] font-semibold leading-none text-slate-900">{{ treatmentHistoryStats.totalTaken }}</p>
            <p class="mt-2 text-[12px] text-slate-600">{{ treatmentHistoryStats.periodSubtitle }}</p>
          </article>
          <article class="rounded-2xl border border-violet-200 bg-violet-50/60 p-4">
            <p class="text-[13px] text-slate-700">Médicaments actifs</p>
            <p class="mt-1 text-[24px] font-semibold leading-none text-slate-900">{{ treatmentHistoryStats.activeMedicines }}</p>
            <p class="mt-2 text-[12px] text-slate-600">Traitements en cours</p>
          </article>
        </div>

        <div class="mt-6">
          <p class="text-[14px] font-semibold text-slate-800">Période</p>
          <div class="mt-3 flex flex-wrap gap-2">
            <button
              v-for="period in treatmentHistoryPeriods"
              :key="period.value"
              type="button"
              class="h-9 rounded-full px-4 text-[13px] font-semibold transition"
              :class="treatmentHistoryPeriod === period.value
                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_6px_14px_rgba(59,130,246,0.32)]'
                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
              @click="treatmentHistoryPeriod = period.value"
            >
              {{ period.label }}
            </button>
          </div>
        </div>

        <div class="mt-5">
          <p class="text-[14px] font-semibold text-slate-800">Médicament</p>
          <div class="mt-3 flex flex-wrap gap-2">
            <button
              v-for="med in treatmentHistoryMedicineOptions"
              :key="med.id"
              type="button"
              class="h-9 rounded-full px-4 text-[13px] font-semibold transition"
              :class="selectedTreatmentHistoryMed === med.id
                ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-[0_6px_14px_rgba(99,102,241,0.32)]'
                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
              @click="selectedTreatmentHistoryMed = med.id"
            >
              {{ med.name }}
            </button>
          </div>
        </div>

        <div class="relative mt-6 space-y-4 pl-7">
          <div class="absolute left-[12px] top-0 h-full w-px bg-blue-200"></div>
          <article
            v-for="day in treatmentHistoryRows"
            :key="`history-${day.dateKey}`"
            class="relative rounded-2xl border p-4 shadow-sm"
            :class="day.isComplete ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-200 bg-white'"
          >
            <span
              class="absolute -left-[22px] top-6 inline-flex h-4 w-4 rounded-full ring-4 ring-white"
              :class="day.isComplete ? 'bg-emerald-500' : 'bg-slate-300'"
            ></span>

            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="text-[16px] font-semibold leading-none text-slate-900">{{ formaterDateHistoriqueTraitement(day.dateKey) }}</h3>
                <p class="mt-2 text-[14px] text-slate-700">{{ day.taken }}/{{ day.total }} prises effectuées</p>
              </div>
              <span
                v-if="day.isComplete"
                class="inline-flex h-8 items-center gap-1 rounded-full bg-emerald-600 px-4 text-[12px] font-semibold text-white"
              >
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Complet
              </span>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-2">
              <article v-for="med in day.meds" :key="`${day.dateKey}-${med.id}`" class="rounded-xl border border-slate-200 bg-white p-3">
                <div class="flex items-center gap-3">
                  <span
                    class="inline-flex h-8 min-w-[32px] items-center justify-center rounded-lg px-2 text-[12px] font-semibold"
                    :class="med.isComplete ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'"
                  >
                    {{ med.isComplete ? "✓" : `${med.taken}/${med.total}` }}
                  </span>
                  <div>
                    <p class="text-[16px] font-semibold leading-none text-slate-900">{{ med.name }}</p>
                    <p class="mt-1 text-[13px] text-slate-600">{{ med.dose }}</p>
                  </div>
                </div>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200">
                  <div
                    class="h-full rounded-full"
                    :class="med.isComplete ? 'bg-emerald-600' : 'bg-blue-600'"
                    :style="{ width: `${med.progress}%` }"
                  ></div>
                </div>
              </article>
            </div>
          </article>

          <div v-if="!treatmentHistoryRows.length" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-600">
            Aucun historique disponible pour ces filtres.
          </div>
        </div>
      </section>

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

        <p v-if="vitalError" class="text-sm font-medium text-rose-600">
          {{ vitalError }}
        </p>

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
          <select
            v-model="analysisForm.category"
            class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-800 outline-none focus:border-blue-500"
            @change="handleAnalysisCategoryChange"
          >
            <option value="">Sélectionnez</option>
            <option v-for="item in analysisCategoryOptions" :key="item" :value="item">{{ item }}</option>
          </select>
        </div>

        <div class="space-y-3">
          <div
            v-for="(result, index) in analysisForm.results"
            :key="`analysis-result-${index}`"
            class="rounded-2xl border border-slate-200 bg-slate-50 p-3"
          >
            <div class="mb-2 flex items-center justify-between">
              <div>
                <p class="text-[13px] font-semibold text-slate-700">Résultat {{ index + 1 }}</p>
                <p v-if="expandedAnalysisResultIndex !== index" class="mt-1 text-xs text-slate-500">
                  {{ getAnalysisResultSummary(result) }}
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  v-if="expandedAnalysisResultIndex !== index"
                  type="button"
                  class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                  @click="expandedAnalysisResultIndex = index"
                >
                  Modifier
                </button>
                <button
                  v-if="analysisForm.results.length > 1 && !editingAnalysisId"
                  type="button"
                  class="text-xs font-semibold text-rose-600 hover:text-rose-700"
                  @click="removeAnalysisResult(index)"
                >
                  Supprimer
                </button>
              </div>
            </div>

            <div v-if="expandedAnalysisResultIndex === index">
            <div>
              <label class="mb-2 block text-[13px] font-semibold text-slate-700">Nom du résultat</label>
              <select
                v-model="result.result"
                :disabled="!analysisForm.category"
                class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] text-slate-800 outline-none focus:border-blue-500 disabled:opacity-60"
                @change="handleAnalysisResultChange(index)"
              >
                <option value="">Sélectionnez</option>
                <option v-for="item in analysisResultOptions" :key="item.label" :value="item.label">{{ item.label }}</option>
              </select>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3">
              <div>
                <label class="mb-2 block text-[13px] font-semibold text-slate-700">Valeur</label>
                <input v-model="result.value" type="text" placeholder="5.2" class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] outline-none focus:border-blue-500" />
              </div>
              <div>
                <label class="mb-2 block text-[13px] font-semibold text-slate-700">Unité</label>
                <input v-model="result.unit" type="text" placeholder="mmol/L" class="h-11 w-full rounded-2xl border border-slate-300 bg-white px-4 text-[15px] outline-none focus:border-blue-500" />
              </div>
            </div>
            </div>
          </div>

          <button
            v-if="!editingAnalysisId"
            type="button"
            class="h-10 rounded-xl border border-slate-300 px-4 text-[13px] font-semibold text-slate-700 hover:bg-slate-100"
            @click="addAnalysisResult"
          >
            + Ajouter un autre résultat
          </button>
        </div>

        <div>
          <label class="mb-2 block text-[13px] font-semibold text-slate-700">Date</label>
          <input v-model="analysisForm.date" type="date" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 text-[15px] outline-none focus:border-blue-500" />
        </div>

        <p v-if="analysisError" class="text-sm font-medium text-rose-600">
          {{ analysisError }}
        </p>

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
const showTreatmentHistory = ref(false);
const selectedTreatmentDayKey = ref(null);
const editingAnalysisId = ref(null);
const expandedAnalysisResultIndex = ref(0);
const vitalError = ref("");
const analysisError = ref("");

const showAddButton = computed(() => activeTab.value !== "treatments");
const addButtonLabel = computed(() => (activeTab.value === "labs" ? "Ajouter une analyse" : "Ajouter une mesure"));
const analysisModalTitle = computed(() => (editingAnalysisId.value ? "Modifier une analyse" : "Ajouter une analyse"));
const analysisSubmitLabel = computed(() => (editingAnalysisId.value ? "Mettre à jour" : "Enregistrer"));

const analyses = ref([]);
const latestVital = ref(null);
const labels = ref([]);
const vitalDateKeys = ref([]);
const heartRateValues = ref([]);
const systolicValues = ref([]);
const diastolicValues = ref([]);
const saturationValues = ref([]);
const historyHeartRateValues = ref([]);
const historySystolicValues = ref([]);
const historyDiastolicValues = ref([]);
const historySaturationValues = ref([]);
const vitalFilterDate = ref("");
const vitalFilterType = ref("all");
const treatmentHistoryPeriod = ref("7");
const selectedTreatmentHistoryMed = ref("all");
const showLabsFilters = ref(false);
const labsFilterType = ref("");
const labsFilterDate = ref("");
const labsFilterQuery = ref("");

const analysisCatalog = {
  "Biologie sanguine": [
    { label: "Glycémie", unit: "mmol/L" },
    { label: "Insuline", unit: "µIU/mL" },
    { label: "HbA1c", unit: "%" },
    { label: "CRP", unit: "mg/L" },
    { label: "Ferritine", unit: "ng/mL" },
    { label: "Créatinine", unit: "mg/L" },
    { label: "TSH", unit: "mUI/L" },
  ],
  Hématologie: [
    { label: "Hémoglobine", unit: "g/dL" },
    { label: "Hématocrite", unit: "%" },
    { label: "Globules blancs", unit: "G/L" },
    { label: "Plaquettes", unit: "G/L" },
    { label: "VGM", unit: "fL" },
  ],
  Radiologie: [
    { label: "Radiographie thoracique", unit: "" },
    { label: "Échographie abdominale", unit: "" },
    { label: "IRM cérébrale", unit: "" },
    { label: "Scanner thoracique", unit: "" },
  ],
  Hormonologie: [
    { label: "Cortisol", unit: "nmol/L" },
    { label: "FSH", unit: "UI/L" },
    { label: "LH", unit: "UI/L" },
    { label: "Prolactine", unit: "ng/mL" },
  ],
  Cardiologie: [
    { label: "Troponine", unit: "ng/L" },
    { label: "BNP", unit: "pg/mL" },
    { label: "D-dimères", unit: "mg/L" },
    { label: "CK-MB", unit: "UI/L" },
  ],
  "Fonction rénale": [
    { label: "Urée", unit: "mmol/L" },
    { label: "DFG", unit: "mL/min/1.73m²" },
    { label: "Microalbuminurie", unit: "mg/24h" },
    { label: "Acide urique", unit: "mg/L" },
  ],
  "Fonction hépatique": [
    { label: "ASAT", unit: "UI/L" },
    { label: "ALAT", unit: "UI/L" },
    { label: "Bilirubine totale", unit: "µmol/L" },
    { label: "GGT", unit: "UI/L" },
    { label: "Phosphatases alcalines", unit: "UI/L" },
  ],
  "Bilan lipidique": [
    { label: "Cholestérol total", unit: "mmol/L" },
    { label: "HDL", unit: "mmol/L" },
    { label: "LDL", unit: "mmol/L" },
    { label: "Triglycérides", unit: "mmol/L" },
  ],
  Urines: [
    { label: "Protéinurie", unit: "g/L" },
    { label: "Leucocyturie", unit: "/µL" },
    { label: "Nitrites", unit: "positif/négatif" },
    { label: "Glucosurie", unit: "g/L" },
  ],
  Microbiologie: [
    { label: "Hémoculture", unit: "positif/négatif" },
    { label: "ECBU", unit: "UFC/mL" },
    { label: "PCR virale", unit: "copies/mL" },
  ],
  Immunologie: [
    { label: "IgG", unit: "g/L" },
    { label: "IgA", unit: "g/L" },
    { label: "IgM", unit: "g/L" },
    { label: "Facteur rhumatoïde", unit: "UI/mL" },
    { label: "ANA", unit: "positif/négatif" },
  ],
};
const analysisCategoryOptions = Object.keys(analysisCatalog);
const analysisResultOptions = computed(() => analysisCatalog[analysisForm.category] ?? []);
const labTypeOptions = computed(() => {
  const values = analyses.value.map((item) => item.type).filter(Boolean);
  return [...new Set(values)];
});
const filteredAnalyses = computed(() => {
  const query = labsFilterQuery.value.trim().toLowerCase();
  return analyses.value.filter((item) => {
    const type = item.type;
    const dateIso = convertirDateIso(item.analysisDate);
    const matchType = !labsFilterType.value || type === labsFilterType.value;
    const matchDate = !labsFilterDate.value || dateIso === labsFilterDate.value;
    const haystack = `${item.name} ${item.value} ${item.unit}`.toLowerCase();
    const matchQuery = !query || haystack.includes(query);
    return matchType && matchDate && matchQuery;
  });
});

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
  category: "",
  results: [
    { result: "", value: "", unit: "" },
  ],
  date: new Date().toISOString().slice(0, 10),
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
const filteredVitalHistory = computed(() => {
  const rows = vitalDateKeys.value
    .map((dateKey, index) => {
      const heartRate = historyHeartRateValues.value[index] ?? null;
      const systolic = historySystolicValues.value[index] ?? null;
      const diastolic = historyDiastolicValues.value[index] ?? null;
      const oxygen = historySaturationValues.value[index] ?? null;
      const hasAny = [heartRate, systolic, diastolic, oxygen].some(estValeurMesuree);

      return {
        dateKey,
        longDate: formaterDateLongue(dateKey),
        hasAny,
        heartRate: heartRate ?? "--",
        pressure: estValeurMesuree(systolic) && estValeurMesuree(diastolic)
          ? `${Number(systolic)}/${Number(diastolic)}`
          : "--/--",
        oxygen: oxygen ?? "--",
      };
    })
    .filter((row) => row.hasAny)
    .reverse();

  if (!vitalFilterDate.value) return rows;
  return rows.filter((row) => row.dateKey === vitalFilterDate.value);
});

const treatmentDays = ref(construire7DerniersJours());
const treatmentMedicines = ref([]);
const treatmentChecks = reactive({});
const treatmentHistoryPeriods = [
  { value: "7", label: "7 derniers jours" },
  { value: "30", label: "30 derniers jours" },
  { value: "all", label: "Tout l'historique" },
];
const treatmentHistoryMedicineOptions = computed(() => [
  { id: "all", name: "Tous" },
  ...treatmentMedicines.value.map((med) => ({ id: med.id, name: med.name })),
]);
const treatmentHistoryRows = computed(() => {
  const periodDays = treatmentHistoryPeriod.value === "all"
    ? 90
    : Number(treatmentHistoryPeriod.value || 7);

  const keys = Array.from({ length: periodDays }).map((_, idx) => {
    const date = new Date();
    date.setDate(date.getDate() - idx);
    return date.toISOString().slice(0, 10);
  });

  return keys
    .map((dateKey) => {
      const meds = treatmentMedicines.value
        .filter((med) => selectedTreatmentHistoryMed.value === "all" || med.id === selectedTreatmentHistoryMed.value)
        .map((med) => {
          const total = obtenirNombrePrises(med);
          const taken = compterPrisesCompletees(dateKey, med);
          const progress = total > 0 ? Math.round((taken / total) * 100) : 0;

          return {
            id: med.id,
            name: med.name,
            dose: med.dose,
            taken,
            total,
            progress,
            isComplete: total > 0 && taken >= total,
          };
        });

      const total = meds.reduce((sum, med) => sum + med.total, 0);
      const taken = meds.reduce((sum, med) => sum + med.taken, 0);
      const hasTracked = total > 0;

      return {
        dateKey,
        meds,
        total,
        taken,
        hasTracked,
        isComplete: hasTracked && taken >= total,
      };
    })
    .filter((day) => day.hasTracked)
    .sort((a, b) => (a.dateKey < b.dateKey ? 1 : -1));
});
const treatmentHistoryStats = computed(() => {
  const rows = treatmentHistoryRows.value;
  const totalDays = rows.length;
  const completeDays = rows.filter((day) => day.isComplete).length;
  const totalTaken = rows.reduce((sum, day) => sum + day.taken, 0);
  const observance = totalDays > 0 ? Math.round((completeDays / totalDays) * 100) : 0;
  const periodSubtitle = treatmentHistoryPeriod.value === "all"
    ? "Sur tout l'historique"
    : `Sur les ${treatmentHistoryPeriod.value} derniers jours`;

  return {
    totalDays,
    completeDays,
    totalTaken,
    observance,
    periodSubtitle,
    activeMedicines: treatmentMedicines.value.length,
  };
});

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
    reinitialiserFormulaireVital();
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

// Cette fonction verifie qu'une mesure est reellement presente (et pas null/vide).
function estValeurMesuree(value) {
  if (value === null || value === undefined || value === "") return false;
  return Number.isFinite(Number(value));
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

// Cette fonction formate une date longue avec jour de semaine (ex: jeudi 26 février 2026).
function formaterDateLongue(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  return date.toLocaleDateString("fr-FR", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

// Cette fonction formate la date pour l'historique des prises (sans annee).
function formaterDateHistoriqueTraitement(dateIso) {
  if (!dateIso) return "";
  const date = new Date(`${dateIso}T00:00:00`);
  return date.toLocaleDateString("fr-FR", {
    weekday: "long",
    day: "numeric",
    month: "long",
  });
}

// Cette fonction retourne l'option d'analyse pour une categorie + resultat.
function trouverOptionAnalyse(category, resultLabel) {
  const options = analysisCatalog[category] ?? [];
  return options.find((item) => item.label === resultLabel) ?? null;
}

// Cette fonction cree une ligne vide pour les resultats multiples.
function creerLigneResultatAnalyse() {
  return { result: "", value: "", unit: "" };
}

// Cette fonction ajoute une ligne de resultat.
function addAnalysisResult() {
  analysisForm.results.push(creerLigneResultatAnalyse());
  expandedAnalysisResultIndex.value = analysisForm.results.length - 1;
}

// Cette fonction supprime une ligne de resultat.
function removeAnalysisResult(index) {
  if (analysisForm.results.length <= 1) return;
  analysisForm.results.splice(index, 1);
  if (expandedAnalysisResultIndex.value >= analysisForm.results.length) {
    expandedAnalysisResultIndex.value = analysisForm.results.length - 1;
  }
}

// Cette fonction gere le changement de type d'analyse.
function handleAnalysisCategoryChange() {
  analysisForm.results = [creerLigneResultatAnalyse()];
  expandedAnalysisResultIndex.value = 0;
}

// Cette fonction applique l'unite par defaut selon le resultat selectionne.
function handleAnalysisResultChange(index) {
  const row = analysisForm.results[index];
  if (!row) return;

  const selected = trouverOptionAnalyse(analysisForm.category, row.result);
  if (selected?.unit) {
    row.unit = selected.unit;
  }
}

// Cette fonction remet a zero les champs du formulaire d'analyse.
function reinitialiserFormulaireAnalyse() {
  analysisError.value = "";
  analysisForm.category = "";
  analysisForm.results = [creerLigneResultatAnalyse()];
  expandedAnalysisResultIndex.value = 0;
  analysisForm.date = new Date().toISOString().slice(0, 10);
}

// Cette fonction genere un resume court d'un resultat pour l'affichage replie.
function getAnalysisResultSummary(result) {
  const resultName = result?.result?.trim();
  const value = String(result?.value ?? "").trim();
  const unit = String(result?.unit ?? "").trim();
  const left = resultName || "Resultat non renseigne";
  const right = value ? `${value}${unit ? ` ${unit}` : ""}` : "Valeur non renseignee";
  return `${left} - ${right}`;
}

// Cette fonction initialise le formulaire des signes vitaux avec des valeurs visibles.
function reinitialiserFormulaireVital() {
  vitalError.value = "";
  vitalForm.heartRate = String(latestVital.value?.heart_rate ?? 72);
  vitalForm.systolic = String(latestVital.value?.systolic_pressure ?? 120);
  vitalForm.diastolic = String(latestVital.value?.diastolic_pressure ?? 80);
  vitalForm.oxygen = String(latestVital.value?.oxygen_saturation ?? 98);
  vitalForm.skipHeartRate = false;
  vitalForm.skipPressure = false;
  vitalForm.skipOxygen = false;
  vitalForm.date = new Date().toISOString().slice(0, 10);
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
  const treatmentHistoryRes = await api.get("/health-data/treatment-checks", { params: { days: 90 } });
  const treatmentHistoryData = Array.isArray(treatmentHistoryRes?.data?.data) ? treatmentHistoryRes.data.data : [];

  latestVital.value = data.latest_vitals ?? null;
  analyses.value = Array.isArray(data.lab_results)
    ? data.lab_results.map((item) => ({
        id: item.id,
        type: item.analysis_type ?? "",
        result: item.analysis_result ?? "",
        name: `${item.analysis_type ?? ""} - ${item.analysis_result ?? ""}`.replace(/ - $/, ""),
        value: item.value,
        unit: item.unit ?? "",
        date: formaterDate(item.analysis_date),
        analysisDate: item.analysis_date,
      }))
    : [];

  const chartData = data.vitals_chart ?? {};
  const labelSource = Array.isArray(chartData.labels) && chartData.labels.length > 0
    ? chartData.labels
    : treatmentDays.value.map((day) => day.key);

  const keepRaw = (values = []) =>
    labelSource.map((_, index) => {
      const value = values[index];
      return value === null || value === undefined || value === "" ? null : value;
    });

  vitalDateKeys.value = [...labelSource];
  historyHeartRateValues.value = keepRaw(chartData.heart_rate);
  historySystolicValues.value = keepRaw(chartData.systolic_pressure);
  historyDiastolicValues.value = keepRaw(chartData.diastolic_pressure);
  historySaturationValues.value = keepRaw(chartData.oxygen_saturation);
  labels.value = labelSource.map(formaterLibelle);
  heartRateValues.value = normaliserSerie(chartData.heart_rate, 70);
  systolicValues.value = normaliserSerie(chartData.systolic_pressure, 120);
  diastolicValues.value = normaliserSerie(chartData.diastolic_pressure, 80);
  saturationValues.value = normaliserSerie(chartData.oxygen_saturation, 98);
  treatmentMedicines.value = Array.isArray(data.treatment_medicines) ? data.treatment_medicines : [];

  for (const day of treatmentDays.value) assurerSuiviJour(day.key);

  const allTreatmentChecks = [
    ...(Array.isArray(data.treatment_checks) ? data.treatment_checks : []),
    ...treatmentHistoryData,
  ];

  if (allTreatmentChecks.length) {
    for (const item of allTreatmentChecks) {
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
  vitalError.value = "";
  const measuredAt = convertirDateIso(vitalForm.date);
  const heartRate = vitalForm.skipHeartRate ? null : convertirNombreOuNull(vitalForm.heartRate);
  const systolic = vitalForm.skipPressure ? null : convertirNombreOuNull(vitalForm.systolic);
  const diastolic = vitalForm.skipPressure ? null : convertirNombreOuNull(vitalForm.diastolic);
  const oxygen = vitalForm.skipOxygen ? null : convertirNombreOuNull(vitalForm.oxygen);

  if (heartRate === null && systolic === null && diastolic === null && oxygen === null) {
    vitalError.value = "Veuillez saisir au moins une mesure ou cocher les options de non-mesure.";
    return;
  }

  if ((systolic === null) !== (diastolic === null)) {
    vitalError.value = "Veuillez remplir les deux champs de tension (systolique et diastolique).";
    return;
  }

  await api.post("/health-data/vitals", {
    measured_at: measuredAt,
    heart_rate: heartRate,
    systolic_pressure: systolic,
    diastolic_pressure: diastolic,
    oxygen_saturation: oxygen,
  });
  reinitialiserFormulaireVital();
  showVitalsModal.value = false;
  await chargerDonneesSante();
}

// Cette fonction enregistre une analyse avec validation simple des champs.
async function enregistrerAnalyse() {
  analysisError.value = "";
  if (!analysisForm.category) {
    analysisError.value = "Veuillez choisir un type d'analyse.";
    return;
  }

  const validRows = [];
  for (const row of analysisForm.results) {
    const analysisType = String(analysisForm.category ?? "").trim();
    const analysisResult = String(row.result ?? "").trim();
    const numericValue = convertirNombreOuNull(row.value);

    if (!analysisType) {
      analysisError.value = "Le type d'analyse est obligatoire.";
      return;
    }
    if (!analysisResult) {
      analysisError.value = "Chaque resultat doit etre selectionne dans la liste.";
      return;
    }
    if (numericValue === null) {
      analysisError.value = "Chaque resultat doit avoir une valeur numerique valide.";
      return;
    }

    validRows.push({
      analysis_type: analysisType,
      analysis_result: analysisResult,
      value: numericValue,
      unit: row.unit || null,
      analysis_date: convertirDateIso(analysisForm.date),
    });
  }

  if (!validRows.length) {
    analysisError.value = "Ajoutez au moins un resultat.";
    return;
  }

  if (editingAnalysisId.value) {
    await api.put(`/health-data/labs/${editingAnalysisId.value}`, validRows[0]);
  } else {
    await Promise.all(validRows.map((payload) => api.post("/health-data/labs", payload)));
    // On reset les filtres pour afficher immediatement les nouvelles analyses ajoutees.
    labsFilterType.value = "";
    labsFilterDate.value = "";
    labsFilterQuery.value = "";
  }

  editingAnalysisId.value = null;
  reinitialiserFormulaireAnalyse();
  showAnalysisModal.value = false;
  await chargerDonneesSante();
}

// Cette fonction pre-remplit le formulaire pour modifier une analyse.
function ouvrirEditionAnalyse(item) {
  editingAnalysisId.value = item.id;
  analysisError.value = "";
  analysisForm.category = item.type ?? "";
  analysisForm.results = [
    {
      result: item.result ?? "",
      value: String(item.value ?? ""),
      unit: item.unit ?? "",
    },
  ];
  analysisForm.date = item.analysisDate ?? new Date().toISOString().slice(0, 10);
  expandedAnalysisResultIndex.value = 0;
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
