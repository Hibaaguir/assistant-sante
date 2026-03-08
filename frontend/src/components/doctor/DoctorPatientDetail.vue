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
      <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
        <article v-for="item in patient.overviewStats" :key="item.label" class="rounded-[20px] border p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]" :class="item.cardClass">
          <div class="flex h-[40px] w-[40px] items-center justify-center rounded-[14px]" :class="item.iconWrapClass">
            <component :is="item.icon" class="h-[18px] w-[18px]" :class="item.iconClass" />
          </div>
          <p class="mt-4 text-[16px] font-medium text-[#455572]">{{ item.label }}</p>
          <p class="mt-5 text-[22px] font-bold text-[#031a46]">{{ item.value }}</p>
          <span class="mt-3 inline-flex rounded-full px-3 py-1 text-[13px] font-medium" :class="item.badgeClass">{{ item.badge }}</span>
        </article>
      </div>

      <div class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
          <h3 class="text-[18px] font-bold text-[#041c49]">Evolution du rythme cardiaque</h3>
          <svg viewBox="0 0 520 220" class="mt-5 h-[240px] w-full">
            <g stroke="#e5e9f0" stroke-dasharray="4 4">
              <line x1="60" y1="30" x2="60" y2="180" />
              <line x1="60" y1="180" x2="500" y2="180" />
              <line x1="60" y1="60" x2="500" y2="60" />
              <line x1="60" y1="95" x2="500" y2="95" />
              <line x1="60" y1="130" x2="500" y2="130" />
              <line x1="165" y1="30" x2="165" y2="180" />
              <line x1="270" y1="30" x2="270" y2="180" />
              <line x1="375" y1="30" x2="375" y2="180" />
              <line x1="500" y1="30" x2="500" y2="180" />
            </g>
            <polyline fill="none" stroke="#f24864" stroke-width="3" points="60,140 165,130 270,125 375,110 500,82" />
            <g fill="#f24864">
              <circle cx="60" cy="140" r="5" />
              <circle cx="165" cy="130" r="5" />
              <circle cx="270" cy="125" r="5" />
              <circle cx="375" cy="110" r="5" />
              <circle cx="500" cy="82" r="5" />
            </g>
            <g fill="#97a3b6" font-size="13">
              <text x="28" y="183">60</text>
              <text x="28" y="133">70</text>
              <text x="28" y="98">80</text>
              <text x="28" y="63">90</text>
              <text x="20" y="33">100</text>
            </g>
            <g fill="#97a3b6" font-size="13">
              <text x="42" y="198">28 fev</text>
              <text x="150" y="198">1 mar</text>
              <text x="255" y="198">2 mar</text>
              <text x="360" y="198">3 mar</text>
              <text x="470" y="198">4 mar</text>
            </g>
          </svg>
        </article>

        <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
          <h3 class="text-[18px] font-bold text-[#041c49]">Evolution de la tension</h3>
          <svg viewBox="0 0 520 220" class="mt-5 h-[240px] w-full">
            <g stroke="#e5e9f0" stroke-dasharray="4 4">
              <line x1="60" y1="30" x2="60" y2="180" />
              <line x1="60" y1="180" x2="500" y2="180" />
              <line x1="60" y1="55" x2="500" y2="55" />
              <line x1="60" y1="105" x2="500" y2="105" />
              <line x1="60" y1="145" x2="500" y2="145" />
              <line x1="165" y1="30" x2="165" y2="180" />
              <line x1="270" y1="30" x2="270" y2="180" />
              <line x1="375" y1="30" x2="375" y2="180" />
              <line x1="500" y1="30" x2="500" y2="180" />
            </g>
            <polyline fill="none" stroke="#4a80eb" stroke-width="3" points="60,92 165,87 270,79 375,72 500,72" />
            <polyline fill="none" stroke="#1db8d6" stroke-width="3" points="60,137 165,134 270,131 375,127 500,127" />
            <g fill="#4a80eb">
              <circle cx="60" cy="92" r="5" />
              <circle cx="165" cy="87" r="5" />
              <circle cx="270" cy="79" r="5" />
              <circle cx="375" cy="72" r="5" />
              <circle cx="500" cy="72" r="5" />
            </g>
            <g fill="#1db8d6">
              <circle cx="60" cy="137" r="5" />
              <circle cx="165" cy="134" r="5" />
              <circle cx="270" cy="131" r="5" />
              <circle cx="375" cy="127" r="5" />
              <circle cx="500" cy="127" r="5" />
            </g>
            <g fill="#97a3b6" font-size="13">
              <text x="20" y="183">60</text>
              <text x="16" y="148">85</text>
              <text x="16" y="108">110</text>
              <text x="16" y="58">150</text>
            </g>
            <g fill="#97a3b6" font-size="13">
              <text x="42" y="198">28 fev</text>
              <text x="150" y="198">1 mar</text>
              <text x="255" y="198">2 mar</text>
              <text x="360" y="198">3 mar</text>
              <text x="470" y="198">4 mar</text>
            </g>
          </svg>
        </article>
      </div>

      <article class="rounded-[20px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <h3 class="text-[18px] font-bold text-[#041c49]">Dernieres analyses biologiques</h3>
        <div class="mt-6 space-y-3">
          <div v-for="analysis in patient.analyses" :key="analysis.name" class="flex items-center justify-between rounded-[16px] border border-[#dde3eb] bg-[#fbfcfd] px-5 py-5">
            <div>
              <p class="text-[16px] font-bold text-[#061a45]">{{ analysis.name }}</p>
              <p class="mt-2 text-[14px] text-[#56657b]">{{ analysis.range }}</p>
            </div>
            <div class="text-right">
              <p class="text-[20px] font-bold text-[#061a45]">{{ analysis.value }}</p>
              <span class="mt-2 inline-flex rounded-full px-3 py-1 text-[13px] font-medium" :class="analysis.badgeClass">{{ analysis.status }}</span>
            </div>
          </div>
        </div>
      </article>
    </section>

    <section v-else-if="detailTab === 'vitals'" class="mt-8 space-y-4">
      <article v-for="entry in patient.vitalsHistory" :key="entry.date" class="rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex items-center gap-2 text-[16px] font-bold text-[#061a45]">
          <CalendarIcon class="h-[18px] w-[18px]" />
          {{ entry.date }}
        </div>
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
          <div class="rounded-[16px] border border-[#f4bcc3] bg-[#fff5f6] px-5 py-4">
            <p class="text-[14px] text-[#455572]">Rythme cardiaque</p>
            <p class="mt-2 text-[18px] font-bold text-[#061a45]">{{ entry.heartRate }}</p>
          </div>
          <div class="rounded-[16px] border border-[#aac8ff] bg-[#eff6ff] px-5 py-4">
            <p class="text-[14px] text-[#455572]">Tension</p>
            <p class="mt-2 text-[18px] font-bold text-[#061a45]">{{ entry.bloodPressure }}</p>
          </div>
          <div class="rounded-[16px] border border-[#dcc5ff] bg-[#faf4ff] px-5 py-4">
            <p class="text-[14px] text-[#455572]">Saturation O2</p>
            <p class="mt-2 text-[18px] font-bold text-[#061a45]">{{ entry.saturation }}</p>
          </div>
        </div>
      </article>
    </section>

    <section v-else-if="detailTab === 'analyses'" class="mt-8 space-y-4">
      <article v-for="analysis in patient.analyses" :key="`${analysis.name}-full`" class="rounded-[20px] border border-[#d4d9e1] bg-white px-6 py-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
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
    </section>

    <section v-else class="mt-8 space-y-4">
      <article v-for="treatment in patient.treatments" :key="treatment.name" class="rounded-[20px] border border-[#d4d9e1] bg-white px-6 py-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-[18px] font-bold text-[#061a45]">{{ treatment.name }}</h3>
            <p class="mt-2 text-[15px] text-[#455572]">{{ treatment.dose }}</p>
            <p class="mt-2 text-[15px] text-[#455572]">A prendre : {{ treatment.when }}</p>
          </div>
          <div class="text-right">
            <p class="text-[19px] font-bold text-[#061a45]">{{ treatment.adherence }}</p>
            <p class="mt-2 text-[15px] text-[#455572]">Observance</p>
          </div>
        </div>
        <div class="mt-5 h-[12px] rounded-full bg-[#d8dde6]">
          <div class="h-[12px] rounded-full" :class="treatment.barClass" :style="{ width: treatment.adherence }" />
        </div>
      </article>
    </section>
  </section>
</template>

<script setup>
import { ref } from 'vue'
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

defineProps({
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
</script>
