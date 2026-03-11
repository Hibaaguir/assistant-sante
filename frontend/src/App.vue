<template>
  <!--
    Composant racine de l'application.
    Il delegue tout l'affichage au routeur.
    Cette structure minimale simplifie l'organisation globale.
  -->
  <router-view />
  <ToastsApplication />
</template>

<script setup>
import ToastsApplication from "@/components/ui/ToastsApplication.vue";
/*
  Aucun state local ici.
  Le routeur pilote l'ensemble des pages.
  Ce fichier reste volontairement minimal.
*/
</script>
