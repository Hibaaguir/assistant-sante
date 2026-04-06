<!--
  DashboardLayout.vue
  Patient main layout: notifications, doctor observation, health chart.
-->
<template>
    <div
        class="mx-auto max-w-[1320px] rounded-3xl border border-slate-200 bg-gradient-to-br from-[#f8f9fa] via-[#fafbfc] to-[#f5f7f9] p-4 sm:p-6 lg:p-8"
    >
        <header class="mb-8">
            <h1 class="text-[42px] font-bold leading-tight text-purple-900">
                Dashboard
            </h1>
            <p class="mt-3 text-base text-slate-600 font-medium">
                Overview of your health
            </p>
        </header>

        <WelcomeCard />

        <NotificationsOnline />
        <NotificationsWidget />

        <!-- Observations médecin -->
        <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-[22px] font-bold text-purple-900">Doctor's Observations</h2>

            <p v-if="!observations.length" class="mt-3 text-base font-medium text-slate-500">
                No doctor observation available at this time.
            </p>

            <div v-else class="mt-3 space-y-3">
                <article
                    v-for="o in observations"
                    :key="o.observation_date"
                    class="rounded-xl border border-purple-100 bg-gradient-to-br from-purple-50 to-white p-4"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100 text-[13px] font-bold text-purple-700">
                                {{ (o.doctor_name || 'Dr')[0].toUpperCase() }}
                            </div>
                            <span class="text-[14px] font-semibold text-purple-900">{{ o.doctor_name || 'Your doctor' }}</span>
                        </div>
                        <span class="text-[12px] text-slate-400">{{ formatDate(o.observation_date) }}</span>
                    </div>
                    <p class="mt-3 text-[14px] leading-6 text-slate-700">{{ o.note }}</p>
                </article>
            </div>
        </section>

        <HealthChart />
    </div>
</template>

<script setup>
import api from "@/services/api";
import HealthChart from "./HealthChart.vue";
import NotificationsWidget from "./NotificationsWidget.vue";
import NotificationsOnline from "@/components/ui/NotificationsOnline.vue";
import WelcomeCard from "./WelcomeCard.vue";

import { onMounted, ref } from "vue";

const observations = ref([]);

function formatDate(str) {
    if (!str) return "";
    const d = new Date(`${str}T00:00:00`);
    return isNaN(d)
        ? str
        : d.toLocaleDateString("fr-FR", { day: "numeric", month: "long", year: "numeric" });
}

async function loadObservation() {
    try {
        const { data } = await api.get("/health-data/overview", { params: { days: 7 } });
        observations.value = Array.isArray(data?.data?.doctor_observations)
            ? data.data.doctor_observations
            : [];
    } catch {
        observations.value = [];
    }
}

onMounted(loadObservation);
</script>
