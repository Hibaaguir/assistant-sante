<!--
  DashboardLayout.vue
  Patient main layout: notifications and health chart.
-->
<template>
    <div class="w-full px-4 py-4 sm:px-6 lg:px-8">
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

        <!-- Row 1 (3 cols): treatment pie · activity distribution · top 5 activities -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <TreatmentPieChart />
            <ActivityDistributionChart />
            <Top3ActivitiesChart />
        </div>

        <!-- Row 2 (2 cols): vital signs evolution · vital signs comparison -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <HealthChart />
            <VitalSignsComparisonChart />
        </div>

        <!-- Row 3 (3 cols): hydration · treatment distribution · sleep trends -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <HydrationChart />
            <TreatmentMonthlyChart />
            <SleepTrendsChart />
        </div>
    </div>
</template>

<script setup>
import HealthChart from "./HealthChart.vue";
import TreatmentPieChart from "./TreatmentPieChart.vue";
import ActivityDistributionChart from "./ActivityDistributionChart.vue";
import Top3ActivitiesChart from "./Top3ActivitiesChart.vue";
import HydrationChart from "./HydrationChart.vue";
import TreatmentMonthlyChart from "./TreatmentMonthlyChart.vue";
import VitalSignsComparisonChart from "./VitalSignsComparisonChart.vue";
import SleepTrendsChart from "./SleepTrendsChart.vue";
import NotificationsWidget from "./NotificationsWidget.vue";
import NotificationsOnline from "@/components/ui/NotificationsOnline.vue";
import WelcomeCard from "./WelcomeCard.vue";
</script>
