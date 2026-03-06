<template>
  <div class="mx-auto max-w-[1260px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div v-if="errorMessage" class="mb-4 rounded-[16px] border border-[#f3b8bb] bg-[#fff5f5] px-4 py-3 text-[14px] font-medium text-[#c63a3f]">
      {{ errorMessage }}
    </div>

    <header class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <h1 class="text-[28px] font-bold leading-none tracking-[-0.03em] text-[#001b44] sm:text-[33px]">Espace Medecin</h1>
        <p class="mt-3 text-[15px] font-medium text-[#5a6881]">Suivi en temps reel de vos patients</p>
      </div>

      <div class="flex items-center gap-3 self-start">
        <button
          type="button"
          class="relative flex h-[40px] w-[50px] items-center justify-center rounded-[14px] border border-[#d7dce3] bg-[#f6f6f7] text-[#4b5568]"
          @click="showAlerts = !showAlerts"
        >
          <BellIcon class="h-[18px] w-[18px]" />
          <span class="absolute right-[-6px] top-[-7px] flex h-[22px] min-w-[22px] items-center justify-center rounded-full bg-[#ef0808] px-1 text-[12px] font-bold leading-none text-white">{{ alerts.length }}</span>
        </button>
        <button
          type="button"
          class="inline-flex h-[40px] items-center gap-2 rounded-[14px] border border-[#b9d4ff] bg-[#edf4ff] px-5 text-[16px] font-medium text-[#1454ff]"
          @click="logout"
        >
          <LogoutIcon class="h-[17px] w-[17px]" />
          <span>Deconnexion</span>
        </button>
      </div>
    </header>

    <section class="mt-10 rounded-[18px] bg-[#eef0f3] p-[6px]">
      <div class="grid grid-cols-2 gap-3 md:flex md:items-center md:gap-0">
        <button
          v-for="tab in headerTabs"
          :key="tab.key"
          type="button"
          class="inline-flex h-[48px] items-center justify-center gap-3 rounded-[14px] px-6 text-[16px] font-semibold transition"
          :class="activeHeaderTab === tab.key ? 'bg-white text-[#0a244f] shadow-[0_3px_10px_rgba(15,23,42,0.10)]' : 'text-[#4c5d7a]'"
          @click="activeHeaderTab = tab.key"
        >
          <component :is="tab.icon" class="h-[18px] w-[18px]" />
          <span>{{ tab.label }}</span>
          <span class="inline-flex h-[22px] min-w-[22px] items-center justify-center rounded-full px-2 text-[13px] font-semibold" :class="activeHeaderTab === tab.key ? 'bg-[#dbe9ff] text-[#3f6ed8]' : 'bg-[#dde2ea] text-[#6d7b93]'">
            {{ tab.count }}
          </span>
        </button>
      </div>
    </section>

    <template v-if="activeHeaderTab === 'patients'">
      <template v-if="!selectedPatient">
        <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <article
            v-for="card in statCards"
            :key="card.key"
            class="rounded-[18px] border bg-white px-5 py-6 shadow-[0_1px_3px_rgba(15,23,42,0.05)]"
            :class="card.borderClass"
          >
            <div class="flex items-center justify-between gap-4">
              <div>
                <p class="text-[15px] font-medium text-[#455572]">{{ card.label }}</p>
                <p class="mt-4 text-[18px] font-semibold leading-none" :class="card.valueClass">{{ card.value }}</p>
              </div>
              <div class="flex h-[48px] w-[48px] items-center justify-center rounded-[15px]" :class="card.iconWrapClass">
                <component :is="card.icon" class="h-[24px] w-[24px]" :class="card.iconClass" />
              </div>
            </div>
          </article>
        </section>

        <section v-if="showAlerts" class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
          <div class="flex items-center justify-between gap-3">
            <h2 class="text-[18px] font-bold text-[#06214d]">Alertes actives ({{ alerts.length }})</h2>
            <button type="button" class="text-[14px] font-medium text-[#30466e]" @click="showAlerts = false">Masquer</button>
          </div>

          <div class="mt-6 space-y-4">
            <article
              v-for="alert in alerts"
              :key="alert.id"
              class="flex flex-col gap-4 rounded-[17px] border px-4 py-4 md:flex-row md:items-start md:justify-between md:px-5"
              :class="alert.rowClass"
            >
              <div class="flex items-start gap-4">
                <div class="mt-[2px] flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-full" :class="alert.iconWrapClass">
                  <AlertTriangleIcon class="h-[20px] w-[20px]" :class="alert.iconClass" />
                </div>
                <div>
                  <p class="text-[16px] font-semibold text-[#031a46]">{{ alert.patient }}</p>
                  <p class="mt-1 text-[14px] text-[#31405e]">{{ alert.message }}</p>
                </div>
              </div>
              <p class="shrink-0 pt-1 text-[14px] font-medium text-[#5f6d85] md:text-right">{{ alert.time }}</p>
            </article>
          </div>
        </section>

        <section class="mt-8 rounded-[18px] border border-[#d4d9e1] bg-white p-4 shadow-[0_1px_4px_rgba(15,23,42,0.05)] sm:p-6">
          <div class="flex flex-col gap-4 xl:flex-row xl:items-center">
            <label class="relative block xl:flex-1">
              <SearchIcon class="pointer-events-none absolute left-5 top-1/2 h-[20px] w-[20px] -translate-y-1/2 text-[#9aa5b7]" />
              <input
                v-model="search"
                type="text"
                placeholder="Rechercher un patient..."
                class="h-[50px] w-full rounded-[16px] border border-[#d1d7e1] bg-[#fdfdfd] pl-13 pr-4 text-[15px] text-[#1a2640] outline-none placeholder:text-[#96a2b4]"
              />
            </label>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:w-auto">
              <button
                v-for="tab in patientTabs"
                :key="tab.key"
                type="button"
                class="h-[50px] rounded-[15px] px-5 text-[15px] font-semibold transition"
                :class="activePatientTab === tab.key ? tab.activeClass : 'bg-[#f1f2f4] text-[#243657]'"
                @click="activePatientTab = tab.key"
              >
                {{ tab.label }}
              </button>
            </div>
          </div>
        </section>

        <section class="mt-6 space-y-4">
          <article
            v-for="patient in filteredPatients"
            :key="patient.id"
            class="cursor-pointer rounded-[18px] border border-[#d4d9e1] bg-white px-6 py-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)] transition hover:border-[#c7d5f5] hover:shadow-[0_8px_18px_rgba(15,23,42,0.08)]"
            @click="openPatient(patient)"
          >
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
              <div class="flex items-start gap-4">
                <div class="flex h-[58px] w-[58px] shrink-0 items-center justify-center rounded-full text-[18px] font-bold text-white" :style="{ backgroundColor: patient.avatarColor }">
                  {{ patient.initials }}
                </div>

                <div>
                  <div class="flex items-center gap-3">
                    <h3 class="text-[20px] font-bold text-[#031a46]">{{ patient.name }}</h3>
                    <span class="h-[12px] w-[12px] rounded-full" :style="{ backgroundColor: patient.dotColor }" />
                  </div>

                  <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[14px] text-[#3f4d66]">
                    <span>{{ patient.age }} ans</span>
                    <span class="text-[#9aa5b7]">•</span>
                    <span class="inline-flex items-center gap-1.5">
                      <ClockIcon class="h-[16px] w-[16px]" />
                      {{ patient.lastSeen }}
                    </span>
                    <span class="text-[#9aa5b7]">•</span>
                    <span>RDV : {{ patient.nextVisit }}</span>
                  </div>

                  <div class="mt-4 flex flex-wrap gap-3">
                    <span class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#f4bcc3] bg-[#fff5f6] px-3 text-[14px] font-semibold text-[#102241]">
                      <HeartIcon class="h-[16px] w-[16px] text-[#ff2143]" />
                      {{ patient.heartRate }}
                    </span>
                    <span class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#aac8ff] bg-[#eff6ff] px-3 text-[14px] font-semibold text-[#102241]">
                      <WaveIcon class="h-[16px] w-[16px] text-[#1454ff]" />
                      {{ patient.bloodPressure }}
                    </span>
                    <span
                      v-if="patient.glucose"
                      class="inline-flex h-[34px] items-center gap-2 rounded-[10px] border border-[#f4bcc3] bg-[#fff5f6] px-3 text-[14px] font-semibold text-[#102241]"
                    >
                      <DropIcon class="h-[16px] w-[16px] text-[#ff3b30]" />
                      {{ patient.glucose }}
                    </span>
                  </div>

                  <div class="mt-4 flex flex-wrap gap-2">
                    <span
                      v-for="tag in patient.tags"
                      :key="tag"
                      class="inline-flex h-[26px] items-center rounded-full bg-[#f0f1f4] px-3 text-[14px] font-medium text-[#495972]"
                    >
                      {{ tag }}
                    </span>
                  </div>
                </div>
              </div>

              <div v-if="patient.alertCount" class="flex shrink-0 flex-col items-end gap-2 xl:min-w-[160px]">
                <div class="inline-flex h-[40px] items-center gap-2 rounded-[12px] border px-4 text-[14px] font-semibold" :class="patient.alertBadgeClass">
                  <AlertTriangleIcon class="h-[16px] w-[16px]" />
                  {{ patient.alertCount }} alerte<span v-if="patient.alertCount > 1">s</span>
                </div>
                <p v-if="patient.alertLabel" class="text-[14px] font-medium" :class="patient.alertLabelClass">{{ patient.alertLabel }}</p>
              </div>
            </div>
          </article>
        </section>
      </template>

      <section v-else class="mt-8">
        <button type="button" class="inline-flex items-center gap-2 text-[14px] font-medium text-[#2454ff]" @click="backToPatientList">
          <ArrowLeftIcon class="h-[16px] w-[16px]" />
          Retour a la liste des patients
        </button>

        <div class="mt-7">
          <div class="flex items-start gap-5">
            <div class="flex h-[82px] w-[82px] shrink-0 items-center justify-center rounded-[24px] text-[19px] font-bold text-white" :style="{ backgroundColor: selectedPatient.avatarColor }">
              {{ selectedPatient.initials }}
            </div>

            <div>
              <div class="flex items-center gap-3">
                <h2 class="text-[28px] font-bold leading-none text-[#031a46]">{{ selectedPatient.name }}</h2>
                <span class="h-[13px] w-[13px] rounded-full" :style="{ backgroundColor: selectedPatient.dotColor }" />
              </div>

              <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[15px] text-[#41506b]">
                <span>{{ selectedPatient.age }} ans</span>
                <span>•</span>
                <span>{{ selectedPatient.gender }}</span>
                <span>•</span>
                <span class="inline-flex items-center gap-1.5">
                  <ClockIcon class="h-[16px] w-[16px]" />
                  Derniere mise a jour : {{ selectedPatient.lastSeen }}
                </span>
              </div>

              <div class="mt-4 flex flex-wrap gap-3">
                <span v-for="tag in selectedPatient.detailTags" :key="tag.label" class="inline-flex h-[31px] items-center rounded-full border px-4 text-[14px] font-semibold" :class="tag.class">
                  {{ tag.label }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 space-y-4">
          <article
            v-for="alert in selectedPatient.detailAlerts"
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
            <article v-for="item in selectedPatient.overviewStats" :key="item.label" class="rounded-[20px] border p-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]" :class="item.cardClass">
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
              <div v-for="analysis in selectedPatient.analyses" :key="analysis.name" class="flex items-center justify-between rounded-[16px] border border-[#dde3eb] bg-[#fbfcfd] px-5 py-5">
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
          <article v-for="entry in selectedPatient.vitalsHistory" :key="entry.date" class="rounded-[20px] border border-[#d4d9e1] bg-white p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
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
          <article v-for="analysis in selectedPatient.analyses" :key="`${analysis.name}-full`" class="rounded-[20px] border border-[#d4d9e1] bg-white px-6 py-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
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
          <article v-for="treatment in selectedPatient.treatments" :key="treatment.name" class="rounded-[20px] border border-[#d4d9e1] bg-white px-6 py-6 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
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

    <section v-else class="mt-10">
      <div class="flex items-start gap-4">
        <div class="flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[14px] bg-[#dbe4ff] text-[#4a45ff]">
          <UserPlusIcon class="h-[24px] w-[24px]" />
        </div>
        <div>
          <h2 class="text-[25px] font-bold leading-none text-[#041c49]">Invitations de patients</h2>
          <p class="mt-2 text-[15px] font-medium text-[#5a6881]">{{ invitations.length }} invitations en attente</p>
        </div>
      </div>

      <div class="mt-7 space-y-4">
        <article
          v-for="invitation in invitations"
          :key="invitation.id"
          class="overflow-hidden rounded-[18px] border border-[#d4ddf4] bg-white shadow-[0_4px_16px_rgba(15,23,42,0.08)]"
        >
          <div class="px-6 py-6">
            <div class="flex items-start gap-4">
              <div class="flex h-[48px] w-[48px] shrink-0 items-center justify-center rounded-[14px] bg-[#eef2f8] text-[#4a45ff]">
                <UserOutlineIcon class="h-[24px] w-[24px]" />
              </div>

              <div class="min-w-0">
                <h3 class="text-[18px] font-bold leading-none text-[#031a46]">{{ invitation.name }}</h3>

                <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-[14px] font-medium text-[#41506b]">
                  <span class="inline-flex items-center gap-2">
                    <MailIcon class="h-[16px] w-[16px]" />
                    {{ invitation.email }}
                  </span>
                  <span class="inline-flex items-center gap-2">
                    <UserSmallIcon class="h-[16px] w-[16px]" />
                    {{ invitation.age }} ans
                  </span>
                  <span class="inline-flex items-center gap-2">
                    <CalendarIcon class="h-[16px] w-[16px]" />
                    Invite le {{ invitation.invitedAt }}
                  </span>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                  <span
                    v-for="tag in invitation.tags"
                    :key="tag"
                    class="inline-flex h-[30px] items-center rounded-full bg-[#fff2dc] px-4 text-[14px] font-semibold text-[#df6b00]"
                  >
                    {{ tag }}
                  </span>
                </div>

                <button type="button" class="mt-5 inline-flex items-center gap-2 text-[14px] font-semibold text-[#4a45ff]">
                  <ChevronDownIcon class="h-[16px] w-[16px]" />
                  Voir le message du patient
                </button>
              </div>
            </div>
          </div>

          <div class="grid gap-3 border-t border-[#e7ebf2] bg-[#fafbfc] px-6 py-4 md:grid-cols-2">
            <button
              type="button"
              class="inline-flex h-[46px] items-center justify-center gap-2 rounded-[15px] bg-[#06af46] px-6 text-[16px] font-bold text-white shadow-[0_8px_16px_rgba(6,175,70,0.22)]"
              :disabled="actionInvitationId === invitation.id"
              @click="acceptInvitation(invitation.id)"
            >
              <CheckCircleIcon class="h-[18px] w-[18px]" />
              Accepter
            </button>
            <button
              type="button"
              class="inline-flex h-[46px] items-center justify-center gap-2 rounded-[15px] border border-[#c8d0dc] bg-white px-6 text-[16px] font-semibold text-[#243657]"
              :disabled="actionInvitationId === invitation.id"
              @click="rejectInvitation(invitation.id)"
            >
              <XCircleIcon class="h-[18px] w-[18px]" />
              Refuser
            </button>
          </div>
        </article>

        <p v-if="!invitations.length" class="rounded-[18px] border border-[#d4d9e1] bg-white px-6 py-6 text-[15px] text-[#5a6881] shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
          Aucune invitation en attente.
        </p>
      </div>

      <div class="mt-8">
        <h3 class="text-[17px] font-bold text-[#041c49]">Invitations traitees</h3>

        <div class="mt-4 space-y-3">
          <article
            v-for="invitation in processedInvitations"
            :key="invitation.id"
            class="flex items-center justify-between gap-4 rounded-[16px] border border-[#d9e0ea] bg-white px-5 py-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
          >
            <div class="flex items-center gap-3">
              <div class="flex h-[34px] w-[34px] items-center justify-center rounded-[12px] bg-[#e7f6ec] text-[#06af46]">
                <CheckCircleIcon class="h-[18px] w-[18px]" />
              </div>
              <div>
                <p class="text-[15px] font-bold text-[#031a46]">{{ invitation.name }}</p>
                <p class="mt-1 text-[14px] text-[#41506b]">{{ invitation.email }}</p>
              </div>
            </div>

            <span class="inline-flex h-[34px] items-center rounded-[12px] bg-[#eef8f1] px-4 text-[14px] font-semibold text-[#0a9f43]">
              Acceptee
            </span>
          </article>

          <p v-if="!processedInvitations.length" class="rounded-[16px] border border-[#d9e0ea] bg-white px-5 py-5 text-[15px] text-[#5a6881] shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
            Aucune invitation traitee.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import api from '@/services/api'
import { computed, defineComponent, h, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

function createIcon(nodes, filled = false) {
  return defineComponent({
    name: 'DoctorDashboardIcon',
    inheritAttrs: false,
    setup(_, { attrs }) {
      return () =>
        h(
          'svg',
          {
            viewBox: '0 0 24 24',
            fill: filled ? 'currentColor' : 'none',
            stroke: filled ? 'none' : 'currentColor',
            strokeWidth: filled ? undefined : 1.8,
            strokeLinecap: 'round',
            strokeLinejoin: 'round',
            'aria-hidden': 'true',
            ...attrs
          },
          nodes.map(([tag, tagAttrs]) => h(tag, tagAttrs))
        )
    }
  })
}

const BellIcon = createIcon([
  ['path', { d: 'M15 17h5l-1.4-1.4c-.38-.38-.6-.9-.6-1.44V11a6 6 0 1 0-12 0v3.16c0 .54-.21 1.06-.59 1.44L4 17h5' }],
  ['path', { d: 'M10 19a2 2 0 0 0 4 0' }]
])

const LogoutIcon = createIcon([
  ['path', { d: 'M10 17 15 12 10 7' }],
  ['path', { d: 'M15 12H4' }],
  ['path', { d: 'M20 20v-3a2 2 0 0 0-2-2h-3' }],
  ['path', { d: 'M20 4v3a2 2 0 0 1-2 2h-3' }]
])

const EyeIcon = createIcon([
  ['path', { d: 'M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z' }],
  ['circle', { cx: '12', cy: '12', r: '3' }]
])

const MailIcon = createIcon([
  ['path', { d: 'M4 6h16v12H4Z' }],
  ['path', { d: 'm4 7 8 6 8-6' }]
])

const UsersIcon = createIcon([
  ['path', { d: 'M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2' }],
  ['circle', { cx: '9.5', cy: '7', r: '3' }],
  ['path', { d: 'M20 21v-2a4 4 0 0 0-3-3.87' }],
  ['path', { d: 'M16.5 4.13a3 3 0 0 1 0 5.74' }]
])

const UserPlusIcon = createIcon([
  ['path', { d: 'M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2' }],
  ['circle', { cx: '9.5', cy: '7', r: '3' }],
  ['path', { d: 'M19 8v6' }],
  ['path', { d: 'M16 11h6' }]
])

const UserOutlineIcon = createIcon([
  ['circle', { cx: '12', cy: '8', r: '3.5' }],
  ['path', { d: 'M6 20a6 6 0 0 1 12 0' }]
])

const UserSmallIcon = createIcon([
  ['circle', { cx: '12', cy: '8', r: '3' }],
  ['path', { d: 'M6.5 20a5.5 5.5 0 0 1 11 0' }]
])

const AlertTriangleIcon = createIcon([
  ['path', { d: 'M12 4 3.8 18.5A1 1 0 0 0 4.67 20h14.66a1 1 0 0 0 .87-1.5L12 4Z' }],
  ['path', { d: 'M12 9v4.5' }],
  ['circle', { cx: '12', cy: '16.5', r: '.8', fill: 'currentColor', stroke: 'none' }]
])

const PulseIcon = createIcon([['path', { d: 'M3 12h4l2.2-5 3.6 10 2.6-6H21' }]])

const HeartSolidIcon = createIcon([['path', { d: 'm12 20.4-1.1-.97C5.14 14.36 2 11.5 2 7.99 2 5.35 4.03 3.3 6.65 3.3c1.48 0 2.9.69 3.82 1.78A5 5 0 0 1 14.3 3.3C16.97 3.3 19 5.35 19 7.99c0 3.51-3.14 6.37-8.9 11.44L12 20.4Z' }]], true)

const SearchIcon = createIcon([
  ['circle', { cx: '11', cy: '11', r: '7' }],
  ['path', { d: 'm20 20-3.5-3.5' }]
])

const ClockIcon = createIcon([
  ['circle', { cx: '12', cy: '12', r: '9' }],
  ['path', { d: 'M12 7v5l3 2' }]
])

const CalendarIcon = createIcon([
  ['path', { d: 'M4 6h16v14H4Z' }],
  ['path', { d: 'M8 3v6' }],
  ['path', { d: 'M16 3v6' }],
  ['path', { d: 'M4 10h16' }]
])

const HeartIcon = createIcon([['path', { d: 'm12 20.3-1.2-1.09C5.3 14.23 2 11.24 2 7.63 2 4.84 4.2 2.7 6.93 2.7c1.54 0 3.02.73 3.97 1.88.95-1.15 2.42-1.88 3.97-1.88C17.8 2.7 20 4.84 20 7.63c0 3.61-3.3 6.6-8.8 11.58L12 20.3Z' }]])

const WaveIcon = createIcon([['path', { d: 'M2 12h4l2-5 4 10 2-5h8' }]])

const DropIcon = createIcon([
  ['path', { d: 'M12 3c2.8 3.3 5 6.1 5 9.1A5 5 0 0 1 7 12.1C7 9.1 9.2 6.3 12 3Z' }],
  ['path', { d: 'M14.5 13.5a2.5 2.5 0 0 1-5 0' }]
])

const LinkIcon = createIcon([
  ['path', { d: 'M10 13a5 5 0 0 1 0-7l1.5-1.5a5 5 0 0 1 7 7L17 13' }],
  ['path', { d: 'M14 11a5 5 0 0 1 0 7l-1.5 1.5a5 5 0 1 1-7-7L7 11' }]
])

const TrendUpIcon = createIcon([
  ['path', { d: 'M4 16 10 10l4 4 6-6' }],
  ['path', { d: 'M14 8h6v6' }]
])

const ChevronDownIcon = createIcon([['path', { d: 'm6 9 6 6 6-6' }]])
const ArrowLeftIcon = createIcon([['path', { d: 'm15 6-6 6 6 6' }], ['path', { d: 'M21 12H9' }]])

const CheckCircleIcon = createIcon([
  ['circle', { cx: '12', cy: '12', r: '9' }],
  ['path', { d: 'm8.5 12 2.3 2.4 4.7-5' }]
])

const XCircleIcon = createIcon([
  ['circle', { cx: '12', cy: '12', r: '9' }],
  ['path', { d: 'm9.5 9.5 5 5' }],
  ['path', { d: 'm14.5 9.5-5 5' }]
])

const router = useRouter()
const activeHeaderTab = ref('patients')
const activePatientTab = ref('all')
const search = ref('')
const showAlerts = ref(true)
const selectedPatient = ref(null)
const detailTab = ref('overview')
const errorMessage = ref('')
const patients = ref([])
const invitations = ref([])
const processedInvitations = ref([])
const actionInvitationId = ref(null)
const detailCache = ref({})

const headerTabs = computed(() => [
  { key: 'patients', label: 'Mes Patients', count: patients.value.length, icon: UsersIcon },
  { key: 'invitations', label: 'Invitations', count: invitations.value.length, icon: UserPlusIcon }
])

const alerts = computed(() =>
  patients.value
    .flatMap((patient) => (Array.isArray(patient.alerts) ? patient.alerts : []).map((alert) => ({
      ...alert,
      patient: patient.name
    })))
    .sort((a, b) => new Date(b.isoTime || 0).getTime() - new Date(a.isoTime || 0).getTime())
)

const statCards = computed(() => {
  const counts = {
    total: patients.value.length,
    critical: patients.value.filter((item) => item.status === 'critical').length,
    watch: patients.value.filter((item) => item.status === 'watch').length,
    stable: patients.value.filter((item) => item.status === 'stable').length
  }

  return [
    {
      key: 'total',
      label: 'Total patients',
      value: counts.total,
      valueClass: 'text-[#031a46]',
      borderClass: 'border-[#d7dce3]',
      icon: UserOutlineIcon,
      iconWrapClass: 'bg-[#dbe9ff]',
      iconClass: 'text-[#1454ff]'
    },
    {
      key: 'critical',
      label: 'Critiques',
      value: counts.critical,
      valueClass: 'text-[#ff1f2d]',
      borderClass: 'border-[#f3b8bb]',
      icon: AlertTriangleIcon,
      iconWrapClass: 'bg-[#fee3e5]',
      iconClass: 'text-[#ff1f2d]'
    },
    {
      key: 'watch',
      label: 'Surveillance',
      value: counts.watch,
      valueClass: 'text-[#ef7a00]',
      borderClass: 'border-[#f0cb58]',
      icon: PulseIcon,
      iconWrapClass: 'bg-[#fff0c8]',
      iconClass: 'text-[#ef7a00]'
    },
    {
      key: 'stable',
      label: 'Stables',
      value: counts.stable,
      valueClass: 'text-[#07b33f]',
      borderClass: 'border-[#b5e6c6]',
      icon: HeartSolidIcon,
      iconWrapClass: 'bg-[#d2f3de]',
      iconClass: 'text-[#07b33f]'
    }
  ]
})

const patientTabs = [
  { key: 'all', label: 'Tous', activeClass: 'bg-[#1454ff] text-white shadow-[0_6px_16px_rgba(20,84,255,0.25)]' },
  { key: 'critical', label: 'Critiques', activeClass: 'bg-[#f80000] text-white shadow-[0_6px_16px_rgba(248,0,0,0.18)]' },
  { key: 'watch', label: 'Surveillance', activeClass: 'bg-[#eb7b00] text-white shadow-[0_6px_16px_rgba(235,123,0,0.2)]' },
  { key: 'stable', label: 'Stables', activeClass: 'bg-[#08b33b] text-white shadow-[0_6px_16px_rgba(8,179,59,0.2)]' }
]

const detailTabs = [
  { key: 'overview', label: "Vue d'ensemble", icon: EyeIcon },
  { key: 'vitals', label: 'Signes vitaux', icon: HeartIcon },
  { key: 'analyses', label: 'Analyses', icon: WaveIcon },
  { key: 'treatments', label: 'Traitements', icon: LinkIcon }
]

const filteredPatients = computed(() => {
  const term = search.value.trim().toLowerCase()

  return patients.value.filter((patient) => {
    const matchesTab = activePatientTab.value === 'all' || patient.status === activePatientTab.value
    const matchesSearch =
      !term ||
      patient.name.toLowerCase().includes(term) ||
      patient.tags.some((tag) => tag.toLowerCase().includes(term))

    return matchesTab && matchesSearch
  })
})

async function openPatient(patient) {
  detailTab.value = 'overview'
  errorMessage.value = ''

  if (detailCache.value[patient.id]) {
    selectedPatient.value = detailCache.value[patient.id]
    return
  }

  try {
    const res = await api.get(`/doctor-invitations/patients/${patient.id}`)
    const detail = mapPatientDetailResponse(res?.data?.data, patient)
    detailCache.value = {
      ...detailCache.value,
      [patient.id]: detail
    }
    selectedPatient.value = detail
  } catch (_) {
    errorMessage.value = "Impossible de charger le detail du patient pour le moment."
  }
}

function backToPatientList() {
  selectedPatient.value = null
  detailTab.value = 'overview'
}

function mapPatientDetailResponse(data, fallbackPatient) {
  const profile = data?.profile || {}
  const latestVitals = data?.latest_vitals || {}
  const chart = data?.vitals_chart || {}
  const labs = Array.isArray(data?.lab_results) ? data.lab_results : []
  const vitals = Array.isArray(data?.vitals) ? data.vitals : []
  const treatments = Array.isArray(data?.treatment_medicines) ? data.treatment_medicines : []
  const treatmentChecks = Array.isArray(data?.treatment_checks) ? data.treatment_checks : []
  const patient = data?.patient || {}

  return {
    ...fallbackPatient,
    name: patient.name || fallbackPatient.name,
    age: computeAge(patient.date_of_birth) || fallbackPatient.age,
    gender: toSentenceCase(profile.sexe || ''),
    lastSeen: formatRelativeTime(latestVitals.measured_at || patient.updated_at || patient.created_at),
    detailTags: buildDetailTags(profile),
    detailAlerts: normalizeDetailAlerts(data?.alerts),
    overviewStats: buildOverviewStats(latestVitals, profile),
    vitalsHistory: groupVitalsHistory(vitals),
    analyses: labs.map(mapAnalysis),
    treatments: buildTreatments(treatments, treatmentChecks)
  }
}

function normalizeDetailAlerts(list) {
  return (Array.isArray(list) ? list : []).map((alert, index) => {
    const isCritical = String(alert?.severity || '').toLowerCase() === 'critical'
    return {
      id: index + 1,
      title: alert?.title || (isCritical ? 'Alerte critique' : 'Alerte'),
      time: formatAbsoluteAlertTime(alert?.measured_at),
      message: alert?.message || 'Alerte patient.',
      recommendation: alert?.recommendation || 'Verifier rapidement la situation du patient.',
      containerClass: isCritical ? 'border-[#ff9e9e] bg-[#fff4f4]' : 'border-[#f1c338] bg-[#fffdf0]',
      iconWrapClass: isCritical ? 'bg-[#ffe1e1]' : 'bg-[#fff0bf]',
      iconClass: isCritical ? 'text-[#ff3c3c]' : 'text-[#f0a400]'
    }
  })
}

function mapInvitation(invitation) {
  const patient = invitation?.patient || {}
  const profile = invitation?.profile || {}

  return {
    id: invitation?.id,
    name: patient.name || 'Patient',
    email: patient.email || '',
    age: computeAge(patient.date_of_birth),
    invitedAt: formatDateLong(invitation?.created_at),
    tags: buildListTags(profile)
  }
}

function mapPatient(item) {
  const patient = item?.patient || {}
  const profile = item?.profile || {}
  const latestVitals = item?.latest_vitals || {}
  const patientAlerts = normalizeListAlerts(item?.alerts, patient.name)
  const glucose = getLatestGlucoseValue(item?.alerts, item?.lab_results)
  const status = resolvePatientStatus(patientAlerts)

  return {
    id: patient.id,
    invitationId: item?.invitation_id,
    name: patient.name || 'Patient',
    initials: buildInitials(patient.name),
    email: patient.email || '',
    age: computeAge(patient.date_of_birth),
    lastSeen: formatRelativeTime(latestVitals.measured_at || item?.accepted_at || patient.created_at),
    nextVisit: formatDateLong(item?.accepted_at || patient.created_at),
    heartRate: latestVitals?.heart_rate ? `${Math.round(Number(latestVitals.heart_rate))} bpm` : '--',
    bloodPressure: latestVitals?.systolic_pressure ? `${Math.round(Number(latestVitals.systolic_pressure))}/${Math.round(Number(latestVitals.diastolic_pressure || 0))}` : '--',
    glucose,
    status,
    tags: buildListTags(profile),
    avatarColor: resolveAvatarColor(status, patient.name),
    dotColor: resolveDotColor(status),
    alertCount: patientAlerts.length,
    alertBadgeClass: status === 'critical' ? 'border-[#f5b1b3] bg-[#fff5f5] text-[#ff2f35]' : status === 'watch' ? 'border-[#f2cc4e] bg-[#fffdf3] text-[#ef7a00]' : '',
    alertLabel: status === 'critical' ? 'Action requise' : '',
    alertLabelClass: 'text-[#ff2f35]',
    alerts: patientAlerts
  }
}

function normalizeListAlerts(list, patientName) {
  return (Array.isArray(list) ? list : []).map((alert, index) => {
    const critical = String(alert?.severity || '').toLowerCase() === 'critical'
    return {
      id: `${patientName}-${index + 1}`,
      patient: patientName,
      message: alert?.message || 'Alerte patient.',
      time: formatAbsoluteAlertTime(alert?.measured_at),
      isoTime: alert?.measured_at || null,
      rowClass: critical ? 'border-[#ffb9bc] bg-[#fff5f5]' : 'border-[#f2cc4e] bg-[#fffdf3]',
      iconWrapClass: critical ? 'bg-[#ffe5e5]' : 'bg-[#fff1c5]',
      iconClass: critical ? 'text-[#ff2f35]' : 'text-[#ef8a00]',
      severity: critical ? 'critical' : 'warning'
    }
  })
}

function resolvePatientStatus(alertsList) {
  if (alertsList.some((alert) => alert.severity === 'critical')) return 'critical'
  if (alertsList.length) return 'watch'
  return 'stable'
}

function buildOverviewStats(latestVitals, profile) {
  const heightCm = Number(profile?.taille || 0)
  const weightKg = Number(profile?.poids || 0)
  const bmi = heightCm > 0 ? weightKg / ((heightCm / 100) * (heightCm / 100)) : null
  const heartRate = Number(latestVitals?.heart_rate || 0)
  const systolic = Number(latestVitals?.systolic_pressure || 0)
  const oxygen = Number(latestVitals?.oxygen_saturation || 0)

  return [
    {
      label: 'Rythme cardiaque',
      value: heartRate ? `${Math.round(heartRate)} bpm` : '--',
      badge: heartRate >= 90 ? 'Legerement eleve' : 'Normal',
      badgeClass: heartRate >= 90 ? 'bg-[#ffe6b8] text-[#d47b00]' : 'bg-[#d7f5df] text-[#11a84d]',
      icon: HeartIcon,
      iconWrapClass: 'bg-[#ffe3e8]',
      iconClass: 'text-[#ff2143]',
      cardClass: 'border-[#f4bac3] bg-[#fff7f8]'
    },
    {
      label: 'Tension',
      value: systolic ? `${Math.round(systolic)}/${Math.round(Number(latestVitals?.diastolic_pressure || 0))}` : '--',
      badge: systolic >= 135 ? 'Elevee' : 'Normal',
      badgeClass: systolic >= 135 ? 'bg-[#ffe6b8] text-[#d47b00]' : 'bg-[#d7f5df] text-[#11a84d]',
      icon: WaveIcon,
      iconWrapClass: 'bg-[#d8e9ff]',
      iconClass: 'text-[#2454ff]',
      cardClass: 'border-[#aed0ff] bg-[#f4fbff]'
    },
    {
      label: 'Saturation O2',
      value: oxygen ? `${Math.round(oxygen)} %` : '--',
      badge: oxygen >= 95 ? 'Normal' : 'Basse',
      badgeClass: oxygen >= 95 ? 'bg-[#d7f5df] text-[#11a84d]' : 'bg-[#ffe6b8] text-[#d47b00]',
      icon: DropIcon,
      iconWrapClass: 'bg-[#efe1ff]',
      iconClass: 'text-[#8c30ff]',
      cardClass: 'border-[#dbc1ff] bg-[#fbf7ff]'
    },
    {
      label: 'IMC',
      value: bmi ? bmi.toFixed(1) : '--',
      badge: bmi && bmi >= 25 ? 'Attention' : 'Normal',
      badgeClass: bmi && bmi >= 25 ? 'bg-[#ffe6b8] text-[#d47b00]' : 'bg-[#d7f5df] text-[#11a84d]',
      icon: TrendUpIcon,
      iconWrapClass: 'bg-[#d7f3e3]',
      iconClass: 'text-[#0da84c]',
      cardClass: 'border-[#aee9c2] bg-[#f3fff7]'
    }
  ]
}

function groupVitalsHistory(rows) {
  const seen = new Set()
  return (Array.isArray(rows) ? rows : [])
    .filter((row) => {
      const key = String(row?.measured_at || '').slice(0, 10)
      if (!key || seen.has(key)) return false
      seen.add(key)
      return true
    })
    .map((row) => ({
      date: formatDateShort(row?.measured_at),
      heartRate: row?.heart_rate ? `${Math.round(Number(row.heart_rate))} bpm` : '--',
      bloodPressure: row?.systolic_pressure ? `${Math.round(Number(row.systolic_pressure))}/${Math.round(Number(row.diastolic_pressure || 0))}` : '--',
      saturation: row?.oxygen_saturation ? `${Math.round(Number(row.oxygen_saturation))}%` : '--'
    }))
}

function mapAnalysis(item) {
  const value = item?.value !== null && item?.value !== undefined ? `${item.value}${item?.unit ? ` ${item.unit}` : ''}` : '--'
  const numericValue = Number(item?.value)
  const status = Number.isFinite(numericValue) && numericValue < 3.9 ? 'Critique' : Number.isFinite(numericValue) && numericValue > 7 ? 'Attention' : 'Normal'
  const badgeClass = status === 'Critique' ? 'bg-[#ffe3e3] text-[#e03535]' : status === 'Attention' ? 'bg-[#ffe8b8] text-[#d47b00]' : 'bg-[#d7f5df] text-[#11a84d]'

  return {
    name: [item?.analysis_type, item?.analysis_result].filter(Boolean).join(' - ') || 'Analyse',
    value,
    range: 'Plage normale : a verifier',
    status,
    badgeClass,
    date: formatDateNumeric(item?.analysis_date)
  }
}

function buildTreatments(medicines, checks) {
  const groupedChecks = {}
  ;(Array.isArray(checks) ? checks : []).forEach((check) => {
    const key = String(check?.medication_key || '')
    if (!groupedChecks[key]) groupedChecks[key] = []
    groupedChecks[key].push(check)
  })

  return (Array.isArray(medicines) ? medicines : []).map((medicine) => {
    const rows = groupedChecks[medicine.id] || []
    const total = rows.length || 0
    const taken = rows.filter((row) => row?.taken).length
    const adherenceValue = total > 0 ? Math.round((taken / total) * 100) : 0

    return {
      name: medicine.name || 'Traitement',
      dose: `${medicine.dose || 'Dose non precisee'} - ${medicine.freq || 'Non precise'}`,
      when: medicine.note || 'Selon prescription',
      adherence: `${adherenceValue}%`,
      barClass: adherenceValue >= 90 ? 'bg-[#0cb342]' : 'bg-[#ea7a00]'
    }
  })
}

async function loadDoctorData() {
  errorMessage.value = ''

  try {
    const [invitationsRes, patientsRes] = await Promise.all([
      api.get('/doctor-invitations'),
      api.get('/doctor-invitations/patients')
    ])

    const invitationRows = Array.isArray(invitationsRes?.data?.data) ? invitationsRes.data.data : []
    invitations.value = invitationRows.filter((item) => item.status === 'pending').map(mapInvitation)
    processedInvitations.value = invitationRows.filter((item) => item.status === 'accepted').map(mapInvitation)
    patients.value = (Array.isArray(patientsRes?.data?.data) ? patientsRes.data.data : []).map(mapPatient)
  } catch (_) {
    errorMessage.value = "Impossible de charger les donnees medecin pour le moment."
    invitations.value = []
    processedInvitations.value = []
    patients.value = []
  }
}

async function acceptInvitation(invitationId) {
  actionInvitationId.value = invitationId
  errorMessage.value = ''
  try {
    await api.post(`/doctor-invitations/${invitationId}/accept`)
    await loadDoctorData()
  } catch (_) {
    errorMessage.value = "Impossible d'accepter cette invitation pour le moment."
  } finally {
    actionInvitationId.value = null
  }
}

async function rejectInvitation(invitationId) {
  actionInvitationId.value = invitationId
  errorMessage.value = ''
  try {
    await api.post(`/doctor-invitations/${invitationId}/reject`)
    await loadDoctorData()
  } catch (_) {
    errorMessage.value = "Impossible de refuser cette invitation pour le moment."
  } finally {
    actionInvitationId.value = null
  }
}

function buildListTags(profile) {
  const tags = [
    ...(Array.isArray(profile?.maladies_chroniques) ? profile.maladies_chroniques : []),
    ...(Array.isArray(profile?.allergies) ? profile.allergies : [])
  ]
  return tags.length ? tags.slice(0, 3) : ['Suivi general']
}

function buildDetailTags(profile) {
  const diseaseTags = (Array.isArray(profile?.maladies_chroniques) ? profile.maladies_chroniques : []).map((label) => ({
    label,
    class: 'border-[#b8d1ff] bg-[#eef5ff] text-[#2454ff]'
  }))
  const allergyTags = (Array.isArray(profile?.allergies) ? profile.allergies : []).map((label) => ({
    label,
    class: 'border-[#f5c2c5] bg-[#fff4f4] text-[#e05842]'
  }))
  return [...diseaseTags, ...allergyTags]
}

function getLatestGlucoseValue(alertsSource) {
  const glucoseAlert = (Array.isArray(alertsSource) ? alertsSource : []).find((item) => String(item?.message || '').toLowerCase().includes('glyc'))
  if (!glucoseAlert) return ''
  const match = String(glucoseAlert.message).match(/([0-9]+(?:[.,][0-9]+)?\s*[A-Za-z/0-9%]+)/)
  return match ? match[1].replace(',', '.') : ''
}

function buildInitials(name) {
  return String(name || '')
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0] || '')
    .join('')
    .toUpperCase() || 'PT'
}

function computeAge(dateString) {
  if (!dateString) return null
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return null
  const now = new Date()
  let age = now.getFullYear() - date.getFullYear()
  const monthDiff = now.getMonth() - date.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < date.getDate())) age -= 1
  return age
}

function formatRelativeTime(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  const diffMs = Date.now() - date.getTime()
  const diffHours = Math.max(0, Math.round(diffMs / 3600000))
  if (diffHours < 1) return 'Il y a moins de 1h'
  if (diffHours < 24) return `Il y a ${diffHours}h`
  const diffDays = Math.round(diffHours / 24)
  return diffDays <= 1 ? 'Il y a 1 jour' : `Il y a ${diffDays} jours`
}

function formatDateLong(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

function formatDateShort(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }).replace('.', '')
}

function formatDateNumeric(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR')
}

function formatAbsoluteAlertTime(dateString) {
  if (!dateString) return "Aujourd'hui"
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return "Aujourd'hui"
  return date.toLocaleString('fr-FR', { day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' })
}

function resolveAvatarColor(status, name) {
  if (status === 'critical') return '#f5002d'
  if (status === 'watch') return '#ef7b00'
  const palette = ['#3d57f4', '#4955f2', '#3558f0']
  const index = String(name || '').length % palette.length
  return palette[index]
}

function resolveDotColor(status) {
  if (status === 'critical') return '#ff5964'
  if (status === 'watch') return '#f59d0b'
  return '#08c44e'
}

function toSentenceCase(value) {
  const text = String(value || '').trim()
  if (!text) return '-'
  return text.charAt(0).toUpperCase() + text.slice(1)
}

onMounted(async () => {
  await loadDoctorData()
})

async function logout() {
  try {
    await api.post('/auth/logout')
  } catch (_) {
    // La deconnexion locale doit rester possible meme si l'API echoue.
  } finally {
    localStorage.removeItem('auth_token')
    if (api.defaults.headers.common.Authorization) {
      delete api.defaults.headers.common.Authorization
    }
    router.push({ name: 'doctor-login' })
  }
}
</script>
