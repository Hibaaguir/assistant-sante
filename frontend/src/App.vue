<template>
  <!--
    Composant racine de l'application.
    Il delegue tout l'affichage au routeur.
    Cette structure minimale simplifie l'organisation globale.
  -->
  <router-view />
  <AppToasts />
</template>

<script setup>
import AppToasts from "@/components/ui/AppToasts.vue";
/*
  Aucun state local ici.
  Le routeur pilote l'ensemble des pages.
  Ce fichier reste volontairement minimal.
*/
</script>
