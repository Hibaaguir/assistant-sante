<!--
  DashboardLayout.vue
  Composant de coordination qui combine HealthChart et NotificationsWidget.
  Responsable: Orchestrer les composants enfants et gérer le layout global.
-->
<template>
  <div class="mx-auto max-w-[1320px] p-4 sm:p-6 lg:p-8">
    <header class="flex items-start justify-between gap-3">
      <div>
        <h1 class="text-[34px] font-semibold leading-none text-slate-900">Dashboard</h1>
        <p class="mt-2 text-sm text-slate-600">Vue d'ensemble de votre santé</p>
      </div>
    </header>

    <!-- Composant de notifications temporaires (toast) -->
    <NotificationsEnLigne />

    <!-- Widget des notifications de traitements -->
    <NotificationsWidget />

    <!-- Graphique des signes vitaux -->
    <HealthChart />

    <!-- StatsWidget sera ajouté ici plus tard -->
    <!-- <StatsWidget /> -->
  </div>
</template>

<script setup>
import HealthChart from './HealthChart.vue'
import NotificationsWidget from './NotificationsWidget.vue'
import NotificationsEnLigne from '@/components/ui/NotificationsEnLigne.vue'
</script>
