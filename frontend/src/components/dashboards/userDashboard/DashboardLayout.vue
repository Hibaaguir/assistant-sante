<!--
  DashboardLayout.vue
  Patient main layout: notifications and health chart.
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

        <HealthChart />
    </div>
</template>

<script setup>
import HealthChart from "./HealthChart.vue";
import NotificationsWidget from "./NotificationsWidget.vue";
import NotificationsOnline from "@/components/ui/NotificationsOnline.vue";
import WelcomeCard from "./WelcomeCard.vue";
</script>
