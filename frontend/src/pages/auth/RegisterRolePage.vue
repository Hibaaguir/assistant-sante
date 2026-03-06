<template>
  <div class="min-h-screen bg-[#f4f7fb] px-4 py-10">
    <div class="mx-auto max-w-3xl">
      <div class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-[0_18px_45px_rgba(15,23,42,0.06)] md:p-10">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-700">Inscription</p>
        <h1 class="mt-3 text-3xl font-semibold text-slate-950">Choisissez votre espace</h1>
        <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
          Selectionnez le type de compte a creer. Chaque parcours reste separe pour distinguer clairement un utilisateur et un medecin.
        </p>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
          <RouterLink
            :to="{ name: 'user-register' }"
            class="rounded-[24px] border border-slate-200 bg-slate-50 p-6 transition hover:border-teal-400 hover:bg-white"
          >
            <p class="text-sm font-semibold text-slate-900">Compte utilisateur</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">Pour utiliser l'application en tant que patient et gerer votre espace sante.</p>
            <span class="mt-6 inline-flex text-sm font-semibold text-teal-700">Continuer</span>
          </RouterLink>

          <RouterLink
            :to="doctorLink"
            class="rounded-[24px] border border-slate-200 bg-slate-50 p-6 transition hover:border-sky-400 hover:bg-white"
          >
            <p class="text-sm font-semibold text-slate-900">Compte medecin</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">Pour acceder aux invitations patients et gerer votre espace professionnel.</p>
            <span class="mt-6 inline-flex text-sm font-semibold text-sky-700">Continuer</span>
          </RouterLink>
        </div>

        <p class="mt-8 text-sm text-slate-600">
          Vous avez deja un compte ?
          <RouterLink :to="{ name: 'login' }" class="font-semibold text-slate-900 hover:underline">Se connecter</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();

const doctorLink = computed(() => ({
  name: "doctor-register",
  query: route.query.email ? { email: String(route.query.email) } : {},
}));
</script>
