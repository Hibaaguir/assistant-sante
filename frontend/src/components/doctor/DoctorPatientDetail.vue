<template>
  <section class="mt-8">
    <button type="button" class="inline-flex items-center gap-2 text-[14px] font-medium text-[#2454ff]" @click="$emit('back')">
      <ArrowLeftIcon class="h-[16px] w-[16px]" />
      Retour a la liste des patients
    </button>

    <div class="mt-7">
      <div class="flex items-start gap-5">
        <div class="flex h-[82px] w-[82px] shrink-0 items-center justify-center rounded-[24px] text-[19px] font-bold text-white" :style="{ backgroundColor: patient.avatarColor }">
          {{ patient.initials }}
        </div>

        <div>
          <div class="flex items-center gap-3">
            <h2 class="text-[28px] font-bold leading-none text-[#031a46]">{{ patient.name }}</h2>
            <span class="h-[13px] w-[13px] rounded-full" :style="{ backgroundColor: patient.dotColor }" />
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[15px] text-[#41506b]">
            <span>{{ patient.age }} ans</span>
            <span>•</span>
            <span>{{ patient.gender }}</span>
            <span>•</span>
            <span class="inline-flex items-center gap-1.5">
              <ClockIcon class="h-[16px] w-[16px]" />
              Derniere mise a jour : {{ patient.lastSeen }}
            </span>
          </div>

          <div class="mt-4 flex flex-wrap gap-3">
            <span v-for="tag in patient.detailTags" :key="tag.label" class="inline-flex h-[31px] items-center rounded-full border px-4 text-[14px] font-semibold" :class="tag.class">
              {{ tag.label }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-8 space-y-4">
      <article
        v-for="alert in patient.detailAlerts"
        :key="alert.id"
        class="rounded-[22px] border p-6"
        :class="alert.containerClass"
      >
        <div class="flex flex-col gap-4 md:flex-row md:items-start">
          <div class="flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[14px]" :class="alert.iconWrapClass">
            <AlertTriangleIcon class="h-[22px] w-[22px]" :class="alert.iconClass" />
          </div>
          <div class="w-full">
            <div class="flex flex-wrap items-center gap-3">
              <h3 class="text-[18px] font-bold text-[#0a1737]">{{ alert.title }}</h3>
              <span class="text-[14px] font-medium text-[#4e5c73]">{{ alert.time }}</span>
            </div>
            <p class="mt-3 text-[16px] text-[#14264c]">{{ alert.message }}</p>

            <div class="mt-4 rounded-[14px] border border-[#d7dce6] bg-white px-4 py-3">
              <p class="text-[15px] font-semibold text-[#263b67]">Recommandation :</p>
              <p class="mt-1 text-[15px] text-[#31405e]">{{ alert.recommendation }}</p>
            </div>
          </div>
        </div>
      </article>
    </div>

    <section class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-[10px] shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="tab in detailTabs"
          :key="tab.key"
          type="button"
          class="inline-flex h-[50px] items-center gap-2 rounded-[14px] px-5 text-[15px] font-semibold transition"
          :class="detailTab === tab.key ? 'bg-[#3f49f4] text-white shadow-[0_10px_18px_rgba(63,73,244,0.22)]' : 'text-[#384860]'"
          @click="detailTab = tab.key"
        >
          <component :is="tab.icon" class="h-[18px] w-[18px]" />
          {{ tab.label }}
        </button>
      </div>
    </section>

    <section v-if="detailTab === 'overview'" class="mt-8 space-y-6">
      <div class="grid gap-4 lg:grid-cols-3">
        <article v-for="item in patient.overviewStats" :key="item.label" class="rounded-[20px] border p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]" :class="item.cardClass">
          <div class="flex items-start justify-between gap-4">
            <div class="flex h-[40px] w-[40px] items-center justify-center rounded-[14px]" :class="item.iconWrapClass">
              <component :is="item.icon" class="h-[18px] w-[18px]" :class="item.iconClass" />
            </div>
            <span class="inline-flex rounded-full px-3 py-1 text-[13px] font-medium" :class="item.badgeClass">{{ item.badge }}</span>
          </div>
          <p class="mt-4 text-[22px] font-bold text-[#031a46]">{{ item.value }}</p>
          <p class="mt-4 text-[16px] font-medium text-[#455572]">{{ item.label }}</p>
        </article>
      </div>

      <div class="grid gap-6 xl:grid-cols-3">
        <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
          <h3 class="text-[18px] font-bold text-[#041c49]">Evolution du rythme cardiaque</h3>
          <svg viewBox="0 0 520 220" class="mt-5 h-[240px] w-full">
            <g stroke="#e5e9f0" stroke-dasharray="4 4">
              <line v-for="tick in heartRateChart.ticks" :key="`heart-y-${tick.value}`" :x1="chartFrame.left" :y1="tick.y" :x2="chartFrame.width - chartFrame.right" :y2="tick.y" />
              <line v-for="(x, index) in heartRateChart.xPositions" :key="`heart-x-${index}`" :x1="x" :y1="chartFrame.top" :x2="x" :y2="chartFrame.height - chartFrame.bottom" />
            </g>
            <polyline
              v-if="heartRateChart.series[0]?.points"
              fill="none"
              stroke="#f24864"
              stroke-width="3"
              :points="heartRateChart.series[0].points"
            />
            <g v-if="heartRateChart.series[0]" fill="#f24864">
              <circle
                v-for="dot in heartRateChart.series[0].dots"
                :key="`heart-dot-${dot.index}`"
                :cx="dot.x"
                :cy="dot.y"
                r="5"
              />
            </g>
            <g fill="#97a3b6" font-size="13">
              <text
                v-for="tick in heartRateChart.ticks"
                :key="`heart-label-${tick.value}`"
                :x="chartFrame.left - 18"
                :y="tick.y + 4"
                text-anchor="end"
              >
                {{ formatChartTick(tick.value) }}
              </text>
            </g>
            <g fill="#97a3b6" font-size="13">
              <text
                v-for="(label, index) in heartRateChart.labels"
                :key="`heart-date-${label}-${index}`"
                :x="heartRateChart.xPositions[index]"
                :y="chartFrame.height - 12"
                text-anchor="middle"
              >
                {{ label }}
              </text>
            </g>
          </svg>
          <p v-if="!heartRateChart.hasData" class="mt-3 text-[14px] text-[#6a7891]">Aucune mesure de rythme cardiaque disponible sur la periode.</p>
        </article>

        <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
          <h3 class="text-[18px] font-bold text-[#041c49]">Evolution de la tension</h3>
          <svg viewBox="0 0 520 220" class="mt-5 h-[240px] w-full">
            <g stroke="#e5e9f0" stroke-dasharray="4 4">
              <line v-for="tick in pressureChart.ticks" :key="`pressure-y-${tick.value}`" :x1="chartFrame.left" :y1="tick.y" :x2="chartFrame.width - chartFrame.right" :y2="tick.y" />
              <line v-for="(x, index) in pressureChart.xPositions" :key="`pressure-x-${index}`" :x1="x" :y1="chartFrame.top" :x2="x" :y2="chartFrame.height - chartFrame.bottom" />
            </g>
            <template v-for="series in pressureChart.series" :key="`pressure-line-${series.key}`">
              <polyline
                v-if="series.points"
                fill="none"
                :stroke="series.color"
                stroke-width="3"
                :points="series.points"
              />
            </template>
            <g v-for="series in pressureChart.series" :key="`pressure-dots-${series.key}`" :fill="series.color">
              <circle
                v-for="dot in series.dots"
                :key="`pressure-dot-${series.key}-${dot.index}`"
                :cx="dot.x"
                :cy="dot.y"
                r="5"
              />
            </g>
            <g fill="#97a3b6" font-size="13">
              <text
                v-for="tick in pressureChart.ticks"
                :key="`pressure-label-${tick.value}`"
                :x="chartFrame.left - 18"
                :y="tick.y + 4"
                text-anchor="end"
              >
                {{ formatChartTick(tick.value) }}
              </text>
            </g>
            <g fill="#97a3b6" font-size="13">
              <text
                v-for="(label, index) in pressureChart.labels"
                :key="`pressure-date-${label}-${index}`"
                :x="pressureChart.xPositions[index]"
                :y="chartFrame.height - 12"
                text-anchor="middle"
              >
                {{ label }}
              </text>
            </g>
          </svg>
          <div class="mt-3 flex flex-wrap items-center gap-4 text-[13px] font-medium">
            <span class="inline-flex items-center gap-2 text-[#4a80eb]">
              <span class="h-2.5 w-2.5 rounded-full bg-[#4a80eb]" />
              Systolique
            </span>
            <span class="inline-flex items-center gap-2 text-[#1db8d6]">
              <span class="h-2.5 w-2.5 rounded-full bg-[#1db8d6]" />
              Diastolique
            </span>
          </div>
          <p v-if="!pressureChart.hasData" class="mt-3 text-[14px] text-[#6a7891]">Aucune mesure de tension disponible sur la periode.</p>
        </article>

        <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
          <h3 class="text-[18px] font-bold text-[#041c49]">Evolution de la saturation O2</h3>
          <svg viewBox="0 0 520 220" class="mt-5 h-[240px] w-full">
            <g stroke="#e5e9f0" stroke-dasharray="4 4">
              <line v-for="tick in oxygenChart.ticks" :key="`oxygen-y-${tick.value}`" :x1="chartFrame.left" :y1="tick.y" :x2="chartFrame.width - chartFrame.right" :y2="tick.y" />
              <line v-for="(x, index) in oxygenChart.xPositions" :key="`oxygen-x-${index}`" :x1="x" :y1="chartFrame.top" :x2="x" :y2="chartFrame.height - chartFrame.bottom" />
            </g>
            <polyline
              v-if="oxygenChart.series[0]?.points"
              fill="none"
              stroke="#8c30ff"
              stroke-width="3"
              :points="oxygenChart.series[0].points"
            />
            <g v-if="oxygenChart.series[0]" fill="#8c30ff">
              <circle
                v-for="dot in oxygenChart.series[0].dots"
                :key="`oxygen-dot-${dot.index}`"
                :cx="dot.x"
                :cy="dot.y"
                r="5"
              />
            </g>
            <g fill="#97a3b6" font-size="13">
              <text
                v-for="tick in oxygenChart.ticks"
                :key="`oxygen-label-${tick.value}`"
                :x="chartFrame.left - 18"
                :y="tick.y + 4"
                text-anchor="end"
              >
                {{ formatChartTick(tick.value) }}
              </text>
            </g>
            <g fill="#97a3b6" font-size="13">
              <text
                v-for="(label, index) in oxygenChart.labels"
                :key="`oxygen-date-${label}-${index}`"
                :x="oxygenChart.xPositions[index]"
                :y="chartFrame.height - 12"
                text-anchor="middle"
              >
                {{ label }}
              </text>
            </g>
          </svg>
          <div class="mt-3 flex flex-wrap items-center gap-4 text-[13px] font-medium">
            <span class="inline-flex items-center gap-2 text-[#8c30ff]">
              <span class="h-2.5 w-2.5 rounded-full bg-[#8c30ff]" />
              Saturation O2
            </span>
          </div>
          <p v-if="!oxygenChart.hasData" class="mt-3 text-[14px] text-[#6a7891]">Aucune mesure de saturation O2 disponible sur la periode.</p>
        </article>
      </div>

      <ObservanceTraitementChart :patient="patient" />

      <HistogrammeAnalysesChart :patient="patient" />

      <article class="overflow-hidden rounded-[20px] border border-[#d6e3ea] bg-[linear-gradient(135deg,#f8fcff_0%,#ffffff_65%,#f9fcfb_100%)] shadow-[0_8px_18px_rgba(35,84,126,0.06)]">
        <div class="border-b border-[#e2ebf0] bg-[linear-gradient(90deg,rgba(46,144,250,0.05)_0%,rgba(22,163,74,0.03)_100%)] px-5 py-4">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
              <div class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-[linear-gradient(135deg,#2f80ed_0%,#27b3a7_100%)] text-white shadow-[0_8px_18px_rgba(47,128,237,0.18)]">
                <svg viewBox="0 0 24 24" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M8 4h8" />
                  <path d="M9 4v3" />
                  <path d="M15 4v3" />
                  <path d="M6 8h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" />
                  <path d="M8 12h8" />
                  <path d="M8 16h5" />
                </svg>
              </div>

              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="text-[17px] font-bold text-[#082b4b]">Observation generale</h3>
                  <span class="rounded-full border border-[#d7e6ed] bg-white/85 px-2.5 py-0.5 text-[11px] font-semibold text-[#5d7890]">Interne</span>
                </div>
                <p class="mt-1 text-[12px] text-[#688299]">Synthese clinique du medecin sur les analyses biologiques.</p>
                <p v-if="analysisObservationSavedAt" class="mt-1.5 text-[11px] font-medium text-[#75899a]">Sauvegardee le {{ analysisObservationSavedAt }}</p>
              </div>
            </div>

            <button
              type="button"
              class="inline-flex h-[36px] items-center justify-center rounded-[12px] border border-[#c4dbe7] bg-white/90 px-3.5 text-[13px] font-semibold text-[#1e567a] transition hover:border-[#a7cadc] hover:bg-white"
              @click="detailTab = 'analyses'"
            >
              Modifier dans Analyses
            </button>
          </div>
        </div>

        <div class="px-5 py-4">
          <div v-if="analysisObservation" class="rounded-[16px] border border-[#deebf1] bg-white/92 px-4 py-3.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]">
            <div class="mb-2 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#3a7a86]">
              <span class="h-2 w-2 rounded-full bg-[#2bb3a8]" />
              Observation enregistree
            </div>
            <p class="text-[14px] leading-7 text-[#23415d]">{{ analysisObservation }}</p>
          </div>

          <div v-else class="rounded-[16px] border border-dashed border-[#d7e4eb] bg-white/70 px-4 py-4 text-[13px] text-[#648097]">
            Aucune observation generale n'a encore ete saisie pour les analyses de ce patient.
          </div>
        </div>
      </article>
    </section>

    <section v-else-if="detailTab === 'vitals'" class="mt-8 space-y-4">
      <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h3 class="text-[18px] font-bold text-[#041c49]">Filtrer les signes vitaux</h3>
            <p class="mt-1 text-[14px] text-[#5b6b84]">Affinez l'historique par date et par type de mesure.</p>
          </div>

          <button
            v-if="vitalDateFilter || vitalSignFilter !== 'all'"
            type="button"
            class="inline-flex h-[42px] items-center justify-center rounded-[14px] border border-[#d4d9e1] px-4 text-[14px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
            @click="resetVitalFilters"
          >
            Reinitialiser
          </button>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Date</label>
            <input
              v-model="vitalDateFilter"
              type="date"
              class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
            />
          </div>

          <div>
            <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Signe vital</label>
            <select
              v-model="vitalSignFilter"
              class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
            >
              <option value="all">Tous les signes</option>
              <option value="heartRate">Rythme cardiaque</option>
              <option value="bloodPressure">Tension</option>
              <option value="saturation">Saturation O2</option>
            </select>
          </div>
        </div>
      </article>

      <article v-for="entry in filteredVitalsHistory" :key="entry.isoDate || entry.date" class="rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex items-center gap-2 text-[16px] font-bold text-[#061a45]">
          <CalendarIcon class="h-[18px] w-[18px]" />
          {{ entry.date }}
        </div>
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
          <div
            v-for="card in entry.cards"
            :key="card.key"
            class="rounded-[16px] border px-5 py-4"
            :class="card.class"
          >
            <p class="text-[14px] text-[#455572]">{{ card.label }}</p>
            <p class="mt-2 text-[18px] font-bold text-[#061a45]">{{ card.value }}</p>
          </div>
        </div>
      </article>

      <article
        v-if="!filteredVitalsHistory.length"
        class="rounded-[20px] border border-dashed border-[#cfd6e2] bg-white px-6 py-8 text-center shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
      >
        <p class="text-[17px] font-semibold text-[#10254f]">Aucun signe vital ne correspond aux filtres.</p>
        <p class="mt-2 text-[14px] text-[#5b6b84]">Essayez une autre date ou choisissez un autre type de mesure.</p>
      </article>
    </section>

    <section v-else-if="detailTab === 'analyses'" class="mt-8 space-y-4">
      <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h3 class="text-[18px] font-bold text-[#041c49]">Filtrer les analyses</h3>
            <p class="mt-1 text-[14px] text-[#5b6b84]">Affinez les resultats par date et par type d'analyse. Les 7 derniers jours sont affiches par defaut.</p>
          </div>

          <button
            v-if="analysisDateFilter || analysisTypeFilter !== 'all'"
            type="button"
            class="inline-flex h-[42px] items-center justify-center rounded-[14px] border border-[#d4d9e1] px-4 text-[14px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
            @click="resetAnalysisFilters"
          >
            Reinitialiser
          </button>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Date</label>
            <input
              v-model="analysisDateFilter"
              type="date"
              class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
            />
          </div>

          <div>
            <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Type d'analyse</label>
            <select
              v-model="analysisTypeFilter"
              class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
            >
              <option value="all">Tous les types</option>
              <option v-for="type in analysisTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
        </div>
      </article>

      <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-4 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <div class="flex items-center gap-2">
              <p class="text-[16px] font-bold text-[#041c49]">Observation generale</p>
              <span class="rounded-full bg-[#f3f6fb] px-2.5 py-1 text-[12px] text-[#5c6d89]">Interne</span>
            </div>
            <p v-if="analysisObservationSavedAt" class="mt-1 text-[12px] text-[#6a7891]">Sauvegardee le {{ analysisObservationSavedAt }}</p>
          </div>

          <button
            type="button"
            class="inline-flex h-[40px] items-center justify-center rounded-[14px] border border-[#d4d9e1] px-4 text-[14px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
            @click="analysisObservationOpen = !analysisObservationOpen"
          >
            {{ analysisObservationOpen ? "Fermer" : "Ajouter une observation" }}
          </button>
        </div>

        <div v-if="analysisObservationOpen" class="mt-4 space-y-3">
          <textarea
            v-model.trim="analysisObservation"
            rows="3"
            placeholder="Exemple : bilan rassurant, surveillance conseillee."
            class="w-full rounded-[16px] border border-[#d7dce6] bg-[#fbfcfd] px-4 py-3 text-[14px] leading-6 text-[#061a45] outline-none transition placeholder:text-[#9aa5ba] focus:border-[#4a55f5]"
          />

          <div class="flex flex-wrap justify-end gap-3">
            <button
              type="button"
              class="inline-flex h-[40px] items-center justify-center rounded-[14px] border border-[#d4d9e1] px-4 text-[14px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
              @click="clearAnalysisObservation"
            >
              Effacer
            </button>
            <button
              type="button"
              class="inline-flex h-[40px] items-center justify-center rounded-[14px] bg-[#3f49f4] px-4 text-[14px] font-semibold text-white transition hover:bg-[#3140ef]"
              @click="saveAnalysisObservation"
            >
              Enregistrer
            </button>
          </div>
        </div>

        <div
          v-if="analysisObservationMessage"
          class="mt-3 rounded-[14px] border px-3 py-2 text-[13px]"
          :class="analysisObservationMessageType === 'success' ? 'border-[#c6ead0] bg-[#f2fcf4] text-[#118445]' : 'border-[#f1d4ae] bg-[#fff8ef] text-[#b46910]'"
        >
          {{ analysisObservationMessage }}
        </div>
      </article>

      <article v-for="analysis in filteredAnalyses" :key="`${analysis.name}-${analysis.isoDate || analysis.date}`" class="rounded-[20px] border border-[#d4d9e1] bg-white px-6 py-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex flex-wrap items-center gap-3">
          <h3 class="text-[18px] font-bold text-[#061a45]">{{ analysis.name }}</h3>
          <span class="inline-flex rounded-full px-3 py-1 text-[13px] font-medium" :class="analysis.badgeClass">{{ analysis.status }}</span>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-3 text-[15px] text-[#455572]">
          <span class="text-[20px] font-bold text-[#061a45]">{{ analysis.value }}</span>
          <span>{{ analysis.range }}</span>
          <span class="inline-flex items-center gap-2">
            <CalendarIcon class="h-[16px] w-[16px]" />
            {{ analysis.date }}
          </span>
        </div>
      </article>

      <article
        v-if="!filteredAnalyses.length"
        class="rounded-[20px] border border-dashed border-[#cfd6e2] bg-white px-6 py-8 text-center shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
      >
        <p class="text-[17px] font-semibold text-[#10254f]">Aucune analyse ne correspond aux filtres.</p>
        <p class="mt-2 text-[14px] text-[#5b6b84]">Essayez une autre date ou un autre type d'analyse.</p>
      </article>
    </section>

    <section v-else class="mt-8 space-y-4">
      <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="text-[18px] font-bold text-[#041c49]">Historique des prises</h3>
            <p class="mt-1 text-[14px] text-[#5b6b84]">Historique reel des traitements pris par le patient, avec 7 derniers jours affiches par defaut.</p>
          </div>
          <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-[12px] font-semibold text-[#3257d6]">
            {{ patient.treatmentHistoryRows?.length || 0 }} jour<span v-if="(patient.treatmentHistoryRows?.length || 0) > 1">s</span>
          </span>
        </div>

        <article class="mt-6 rounded-[18px] border border-[#dbe2ec] bg-[#fbfcfe] p-5">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <h4 class="text-[17px] font-bold text-[#10254f]">Filtrer l'historique des traitements</h4>
              <p class="mt-1 text-[14px] text-[#60708b]">Affinez l'affichage par date, traitement ou statut d'observance.</p>
            </div>

            <button
              v-if="treatmentDateFilter || treatmentMedicineFilter !== 'all' || treatmentStatusFilter !== 'all'"
              type="button"
              class="inline-flex h-[42px] items-center justify-center rounded-[14px] border border-[#d4d9e1] px-4 text-[14px] font-semibold text-[#40506a] transition hover:border-[#aeb9ca] hover:text-[#1a2c52]"
              @click="resetTreatmentFilters"
            >
              Reinitialiser
            </button>
          </div>

          <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div>
              <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Date</label>
              <input
                v-model="treatmentDateFilter"
                type="date"
                class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-white px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
              />
            </div>

            <div>
              <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Traitement</label>
              <select
                v-model="treatmentMedicineFilter"
                class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-white px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
              >
                <option value="all">Tous les traitements</option>
                <option v-for="medicine in treatmentMedicineOptions" :key="medicine" :value="medicine">{{ medicine }}</option>
              </select>
            </div>

            <div>
              <label class="mb-2 block text-[14px] font-semibold text-[#23375f]">Observance</label>
              <select
                v-model="treatmentStatusFilter"
                class="h-[52px] w-full rounded-[16px] border border-[#d7dce6] bg-white px-4 text-[15px] text-[#061a45] outline-none transition focus:border-[#4a55f5]"
              >
                <option value="all">Tous les jours</option>
                <option value="complete">Jours complets</option>
                <option value="partial">Jours partiels</option>
              </select>
            </div>
          </div>
        </article>

        <div v-if="filteredTreatmentHistoryRows.length" class="mt-6 space-y-4">
          <article
            v-for="day in filteredTreatmentHistoryRows"
            :key="`doctor-treatment-history-${day.dateKey}`"
            class="rounded-[18px] border border-[#dbe2ec] bg-[#fbfcfe] p-5"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h4 class="text-[16px] font-bold text-[#10254f]">{{ formatTreatmentHistoryDate(day.dateKey) }}</h4>
                <p class="mt-1 text-[14px] text-[#60708b]">{{ day.taken }}/{{ day.total }} prises effectuees</p>
              </div>
              <span
                class="inline-flex h-[34px] items-center rounded-full px-3 text-[12px] font-semibold"
                :class="day.isComplete ? 'bg-[#dff6e7] text-[#15803d]' : 'bg-[#e9eef7] text-[#42526b]'"
              >
                {{ day.isComplete ? 'Jour complet' : 'Jour partiel' }}
              </span>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-2">
              <article
                v-for="med in day.meds"
                :key="`${day.dateKey}-${med.id}`"
                class="rounded-[16px] border border-[#e2e8f0] bg-white px-4 py-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-[15px] font-bold text-[#112652]">{{ med.name }}</p>
                    <p class="mt-1 text-[13px] text-[#63758d]">{{ med.dose }}</p>
                  </div>
                  <span
                    class="inline-flex h-[30px] min-w-[52px] items-center justify-center rounded-full px-3 text-[12px] font-semibold"
                    :class="med.isComplete ? 'bg-[#dff6e7] text-[#15803d]' : 'bg-[#edf2ff] text-[#3257d6]'"
                  >
                    {{ med.taken }}/{{ med.total }}
                  </span>
                </div>

                <div class="mt-4 h-[8px] rounded-full bg-[#dfe5ee]">
                  <div
                    class="h-[8px] rounded-full transition-all"
                    :class="med.isComplete ? 'bg-[#16a34a]' : 'bg-[#4f46e5]'"
                    :style="{ width: `${med.progress}%` }"
                  />
                </div>
              </article>
            </div>
          </article>
        </div>

        <div v-else class="mt-6 rounded-[16px] border border-dashed border-[#d3dbe7] bg-[#fbfcff] px-5 py-8 text-center">
          <p class="text-[15px] font-semibold text-[#10254f]">Aucun historique de prise disponible.</p>
          <p class="mt-2 text-[13px] text-[#697892]">Essayez une autre date, un autre traitement ou un autre filtre d'observance.</p>
        </div>
      </article>
    </section>
  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import {
  AlertTriangleIcon,
  ArrowLeftIcon,
  CalendarIcon,
  ClockIcon,
  EyeIcon,
  HeartIcon,
  LinkIcon,
  WaveIcon
} from '@/components/doctor/DoctorIcons.js'
import ObservanceTraitementChart from '@/components/dashboards/doctorDashboard/ObservanceTraitementChart.vue'
import HistogrammeAnalysesChart from '@/components/dashboards/doctorDashboard/HistogrammeAnalysesChart.vue'

const props = defineProps({
  patient: {
    type: Object,
    required: true
  }
})

defineEmits(['back'])

const detailTab = ref('overview')

const detailTabs = [
  { key: 'overview', label: "Vue d'ensemble", icon: EyeIcon },
  { key: 'vitals', label: 'Signes vitaux', icon: HeartIcon },
  { key: 'analyses', label: 'Analyses', icon: WaveIcon },
  { key: 'treatments', label: 'Traitements', icon: LinkIcon }
]

const vitalDateFilter = ref('')
const vitalSignFilter = ref('all')
const analysisDateFilter = ref('')
const analysisTypeFilter = ref('all')
const treatmentDateFilter = ref('')
const treatmentMedicineFilter = ref('all')
const treatmentStatusFilter = ref('all')
const analysisObservation = ref('')
const analysisObservationOpen = ref(false)
const analysisObservationSavedAt = ref('')
const analysisObservationMessage = ref('')
const analysisObservationMessageType = ref('success')
const chartFrame = {
  width: 520,
  height: 220,
  left: 60,
  right: 20,
  top: 30,
  bottom: 40,
}

function buildVitalCards(entry) {
  return [
    {
      key: 'heartRate',
      label: 'Rythme cardiaque',
      value: entry.heartRate,
      class: 'border-[#f4bcc3] bg-[#fff5f6]'
    },
    {
      key: 'bloodPressure',
      label: 'Tension',
      value: entry.bloodPressure,
      class: 'border-[#aac8ff] bg-[#eff6ff]'
    },
    {
      key: 'saturation',
      label: 'Saturation O2',
      value: entry.saturation,
      class: 'border-[#dcc5ff] bg-[#faf4ff]'
    }
  ]
}

const filteredVitalsHistory = computed(() => {
  const entries = Array.isArray(props.patient?.vitalsHistory) ? props.patient.vitalsHistory : []
  const visibleEntries = vitalDateFilter.value ? entries : entries.slice(0, 7)

  return visibleEntries
    .filter((entry) => !vitalDateFilter.value || entry.isoDate === vitalDateFilter.value)
    .map((entry) => {
      const cards = buildVitalCards(entry).filter((card) => vitalSignFilter.value === 'all' || card.key === vitalSignFilter.value)
      return { ...entry, cards }
    })
    .filter((entry) => entry.cards.length > 0)
})

const analysisTypes = computed(() => {
  const analyses = Array.isArray(props.patient?.analyses) ? props.patient.analyses : []
  return [...new Set(analyses.map((analysis) => String(analysis.type || '').trim()).filter(Boolean))]
})

const analysisVisibleDateKeys = computed(() => {
  const analyses = Array.isArray(props.patient?.analyses) ? props.patient.analyses : []
  return [...new Set(analyses.map((analysis) => analysis.isoDate).filter(Boolean))].slice(0, 7)
})

const filteredAnalyses = computed(() => {
  const analyses = Array.isArray(props.patient?.analyses) ? props.patient.analyses : []
  const visibleDateKeys = analysisVisibleDateKeys.value
  const visibleAnalyses = analysisDateFilter.value
    ? analyses
    : analyses.filter((analysis) => analysis.isoDate && visibleDateKeys.includes(analysis.isoDate))

  return visibleAnalyses.filter((analysis) => {
    const dateOk = !analysisDateFilter.value || analysis.isoDate === analysisDateFilter.value
    const typeOk = analysisTypeFilter.value === 'all' || analysis.type === analysisTypeFilter.value
    return dateOk && typeOk
  })
})

const treatmentMedicineOptions = computed(() => {
  const rows = Array.isArray(props.patient?.treatmentHistoryRows) ? props.patient.treatmentHistoryRows : []
  return [...new Set(rows.flatMap((day) => day.meds.map((med) => String(med.name || '').trim())).filter(Boolean))]
})

const filteredTreatmentHistoryRows = computed(() => {
  const rows = Array.isArray(props.patient?.treatmentHistoryRows) ? props.patient.treatmentHistoryRows : []
  const visibleRows = treatmentDateFilter.value ? rows : rows.slice(0, 7)

  return visibleRows
    .filter((day) => !treatmentDateFilter.value || day.dateKey === treatmentDateFilter.value)
    .map((day) => {
      const meds = day.meds.filter((med) => treatmentMedicineFilter.value === 'all' || med.name === treatmentMedicineFilter.value)
      const total = meds.reduce((sum, med) => sum + Number(med.total || 0), 0)
      const taken = meds.reduce((sum, med) => sum + Number(med.taken || 0), 0)
      const isComplete = total > 0 && taken >= total

      return {
        ...day,
        meds,
        total,
        taken,
        isComplete,
      }
    })
    .filter((day) => day.meds.length > 0)
    .filter((day) => {
      if (treatmentStatusFilter.value === 'complete') return day.isComplete
      if (treatmentStatusFilter.value === 'partial') return !day.isComplete
      return true
    })
})

const heartRateChart = computed(() =>
  buildTrendChart(
    props.patient?.vitalsChart?.labels,
    [
      {
        key: 'heart-rate',
        color: '#f24864',
        values: props.patient?.vitalsChart?.heartRate,
      },
    ],
    { fallbackMin: 60, fallbackMax: 100, minSpread: 12, padding: 6 }
  )
)

const pressureChart = computed(() =>
  buildTrendChart(
    props.patient?.vitalsChart?.labels,
    [
      {
        key: 'systolic',
        color: '#4a80eb',
        values: props.patient?.vitalsChart?.systolicPressure,
      },
      {
        key: 'diastolic',
        color: '#1db8d6',
        values: props.patient?.vitalsChart?.diastolicPressure,
      },
    ],
    { fallbackMin: 60, fallbackMax: 150, minSpread: 20, padding: 10 }
  )
)

const oxygenChart = computed(() =>
  buildTrendChart(
    props.patient?.vitalsChart?.labels,
    [
      {
        key: 'oxygen',
        color: '#8c30ff',
        values: props.patient?.vitalsChart?.oxygenSaturation,
      },
    ],
    { fallbackMin: 88, fallbackMax: 100, minSpread: 8, padding: 3 }
  )
)

const analysisObservationStorageKey = computed(() => {
  const patientId = props.patient?.id ?? 'unknown'
  return `doctor-analysis-observation-${patientId}`
})

function loadAnalysisObservation() {
  analysisObservationMessage.value = ''
  if (typeof window === 'undefined') return

  const raw = window.localStorage.getItem(analysisObservationStorageKey.value)
  if (!raw) {
    analysisObservation.value = ''
    analysisObservationSavedAt.value = ''
    return
  }

  try {
    const parsed = JSON.parse(raw)
    analysisObservation.value = String(parsed?.text || '')
    analysisObservationSavedAt.value = String(parsed?.savedAtLabel || '')
    analysisObservationOpen.value = false
  } catch {
    analysisObservation.value = ''
    analysisObservationSavedAt.value = ''
    analysisObservationOpen.value = false
  }
}

function buildTrendChart(labelsInput, seriesInput, options = {}) {
  const labels = Array.isArray(labelsInput) ? labelsInput : []
  const xPositions = buildXPositions(labels.length)
  const series = Array.isArray(seriesInput) ? seriesInput : []
  const numericValues = series.flatMap((serie) => (Array.isArray(serie.values) ? serie.values : [])).filter((value) => Number.isFinite(value))
  const hasData = numericValues.length > 0

  let minValue = options.fallbackMin ?? 0
  let maxValue = options.fallbackMax ?? 100

  if (hasData) {
    const rawMin = Math.min(...numericValues)
    const rawMax = Math.max(...numericValues)
    const padding = Math.max(options.padding ?? 5, (rawMax - rawMin) * 0.15)
    minValue = Math.floor(rawMin - padding)
    maxValue = Math.ceil(rawMax + padding)
  }

  if (maxValue - minValue < (options.minSpread ?? 10)) {
    const diff = options.minSpread ?? 10
    const center = (maxValue + minValue) / 2
    minValue = Math.floor(center - diff / 2)
    maxValue = Math.ceil(center + diff / 2)
  }

  const ticks = buildTickValues(minValue, maxValue, 4).map((value) => ({
    value,
    y: convertValueToY(value, minValue, maxValue),
  }))

  return {
    labels,
    xPositions,
    ticks,
    hasData,
    series: series.map((serie) => buildSeriesGeometry(serie, xPositions, minValue, maxValue)),
  }
}

function buildSeriesGeometry(serie, xPositions, minValue, maxValue) {
  const source = Array.isArray(serie?.values) ? serie.values : []
  const dots = source
    .map((value, index) => {
      if (!Number.isFinite(value) || xPositions[index] === undefined) return null
      return {
        index,
        x: xPositions[index],
        y: convertValueToY(value, minValue, maxValue),
      }
    })
    .filter(Boolean)

  return {
    key: serie?.key || 'series',
    color: serie?.color || '#031a46',
    dots,
    points: dots.map((dot) => `${dot.x},${dot.y}`).join(' '),
  }
}

function buildXPositions(length) {
  if (length <= 0) return []
  if (length === 1) return [chartFrame.left]

  const usableWidth = chartFrame.width - chartFrame.left - chartFrame.right
  const step = usableWidth / (length - 1)
  return Array.from({ length }, (_, index) => chartFrame.left + step * index)
}

function convertValueToY(value, minValue, maxValue) {
  if (!Number.isFinite(value)) return chartFrame.height - chartFrame.bottom

  const usableHeight = chartFrame.height - chartFrame.top - chartFrame.bottom
  const ratio = (value - minValue) / Math.max(maxValue - minValue, 1)
  return chartFrame.height - chartFrame.bottom - ratio * usableHeight
}

function buildTickValues(minValue, maxValue, tickCount) {
  if (tickCount <= 1) return [minValue, maxValue]
  const step = (maxValue - minValue) / (tickCount - 1)
  return Array.from({ length: tickCount }, (_, index) => Number((minValue + step * index).toFixed(1)))
}

function formatChartTick(value) {
  return Number.isInteger(value) ? value : value.toFixed(1)
}

function formatTreatmentHistoryDate(dateIso) {
  if (!dateIso) return '-'
  const date = new Date(`${dateIso}T00:00:00`)
  if (Number.isNaN(date.getTime())) return dateIso
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function resetVitalFilters() {
  vitalDateFilter.value = ''
  vitalSignFilter.value = 'all'
}

function resetAnalysisFilters() {
  analysisDateFilter.value = ''
  analysisTypeFilter.value = 'all'
}

function resetTreatmentFilters() {
  treatmentDateFilter.value = ''
  treatmentMedicineFilter.value = 'all'
  treatmentStatusFilter.value = 'all'
}

function saveAnalysisObservation() {
  const text = String(analysisObservation.value || '').trim()

  if (!text) {
    analysisObservationMessageType.value = 'warning'
    analysisObservationMessage.value = "Ecrivez une observation avant de l'enregistrer."
    return
  }

  const now = new Date()
  const savedAtLabel = now.toLocaleString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })

  if (typeof window !== 'undefined') {
    window.localStorage.setItem(
      analysisObservationStorageKey.value,
      JSON.stringify({
        text,
        savedAt: now.toISOString(),
        savedAtLabel
      })
    )
  }

  analysisObservation.value = text
  analysisObservationSavedAt.value = savedAtLabel
  analysisObservationMessageType.value = 'success'
  analysisObservationMessage.value = 'Observation generale enregistree.'
  analysisObservationOpen.value = false
}

function clearAnalysisObservation() {
  analysisObservation.value = ''
  analysisObservationSavedAt.value = ''

  if (typeof window !== 'undefined') {
    window.localStorage.removeItem(analysisObservationStorageKey.value)
  }

  analysisObservationMessageType.value = 'success'
  analysisObservationMessage.value = 'Observation effacee.'
  analysisObservationOpen.value = false
}

watch(
  () => props.patient?.id,
  () => {
    loadAnalysisObservation()
  },
  { immediate: true }
)
</script>
