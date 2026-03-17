<template>
  <div class="min-h-screen bg-[radial-gradient(circle_at_top,_#eef4ff,_#f8fafc_45%,_#eef2f7)] px-4 py-8 sm:px-6 lg:py-12">
    <div class="mx-auto max-w-4xl">
      <div class="rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_24px_70px_rgba(37,99,235,0.12)] backdrop-blur sm:p-8">
        <div class="mx-auto max-w-xl text-center">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[28px] bg-gradient-to-br from-indigo-500 via-blue-600 to-indigo-700 text-white shadow-[0_16px_35px_rgba(59,130,246,0.35)]">
            <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z" />
            </svg>
          </div>

          <h1 class="mt-6 text-2xl font-bold tracking-[-0.03em] text-slate-950 sm:text-3xl">
            {{ titreBienvenue }}
          </h1>
          <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">
            Choisissez l’espace que vous souhaitez ouvrir maintenant. Vous pourrez changer d’espace à tout moment.
          </p>
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-[1fr_auto_1fr] lg:items-center">
          <section class="group flex h-full flex-col rounded-[30px] border border-indigo-100 bg-[linear-gradient(180deg,_#f9fbff,_#ffffff)] p-6 shadow-[0_18px_40px_rgba(15,23,42,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_55px_rgba(59,130,246,0.18)]">
            <div class="flex h-20 w-20 items-center justify-center rounded-[28px] bg-indigo-100 text-indigo-600">
              <svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                <path d="M6.5 2H20v19H6.5A2.5 2.5 0 0 1 4 18.5z" />
                <path d="M8 8h8" />
                <path d="M8 12h6" />
                <path d="M8 16h4" />
              </svg>
            </div>

            <div class="mt-5">
              <h2 class="text-2xl font-bold tracking-[-0.02em] text-slate-950">Espace Médecin</h2>
              <p class="mt-2 text-sm leading-6 text-slate-600">
                Consultez vos patients, traitez leurs invitations et accédez au suivi clinique.
              </p>
            </div>

            <ul class="mt-6 space-y-3 text-sm text-slate-700">
              <li class="flex items-start gap-2">
                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                  <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                Gestion des patients et des invitations
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l3-8 4 16 3-8h4"/></svg>
                </span>
                Suivi des données de santé
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
                Accès professionnel sécurisé
              </li>
            </ul>

            <button
              type="button"
              class="mt-10 inline-flex h-14 items-center justify-center gap-3 rounded-[20px] bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-700 px-6 text-lg font-semibold text-white shadow-[0_18px_35px_rgba(59,130,246,0.35)] transition hover:scale-[1.01] hover:shadow-[0_20px_40px_rgba(59,130,246,0.42)]"
              @click="ouvrirEspaceMedecin"
            >
              Accéder à l’espace médecin
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
            </button>
          </section>

          <div class="hidden h-16 w-16 items-center justify-center rounded-full border border-slate-200 bg-white text-lg font-semibold text-slate-500 shadow-[0_8px_20px_rgba(15,23,42,0.08)] lg:flex">
            ou
          </div>

          <section class="group flex h-full flex-col rounded-[30px] border border-emerald-100 bg-[linear-gradient(180deg,_#f6fffa,_#ffffff)] p-7 shadow-[0_18px_40px_rgba(15,23,42,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_55px_rgba(16,185,129,0.18)]">
            <div class="flex h-24 w-24 items-center justify-center rounded-[28px] bg-emerald-100 text-emerald-600">
              <svg viewBox="0 0 24 24" class="h-11 w-11" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="8" r="4" />
                <path d="M6 20a6 6 0 0 1 12 0" />
              </svg>
            </div>

            <div class="mt-7">
              <h2 class="text-3xl font-bold tracking-[-0.02em] text-slate-950">Espace Personnel</h2>
              <p class="mt-3 text-[17px] leading-8 text-slate-600">
                Gérez votre propre santé, votre journal quotidien et vos recommandations personnalisées.
              </p>
            </div>

            <ul class="mt-8 space-y-4 text-[15px] text-slate-700">
              <li class="flex items-start gap-3">
                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z"/></svg>
                </span>
                Profil de santé personnel
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v19H6.5A2.5 2.5 0 0 1 4 18.5z"/><path d="M8 7h8"/></svg>
                </span>
                Journal quotidien et suivi
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.4 2.8L16 7.2l-2.6 1.4L12 11l-1.4-2.4L8 7.2l2.6-1.4z"/><path d="M5 14l.9 1.8L8 16.8l-2.1 1L5 20l-.9-2.2L2 16.8l2.1-1z"/></svg>
                </span>
                Recommandations bien-être
              </li>
            </ul>

            <button
              type="button"
              class="mt-10 inline-flex h-14 items-center justify-center gap-3 rounded-[20px] bg-gradient-to-r from-emerald-500 via-emerald-600 to-green-600 px-6 text-lg font-semibold text-white shadow-[0_18px_35px_rgba(16,185,129,0.28)] transition hover:scale-[1.01] hover:shadow-[0_20px_40px_rgba(16,185,129,0.36)]"
              @click="ouvrirEspacePersonnel"
            >
              {{ authStore.aProfilSante ? "Accéder à l’espace personnel" : "Créer mon espace personnel" }}
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
            </button>
          </section>
        </div>

        <p class="mt-10 text-center text-sm text-slate-500">
          Vous pourrez changer d’espace à tout moment depuis l’interface.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const titreBienvenue = computed(() => {
  const nom = authStore.nomUtilisateur?.trim?.() || "";
  return nom ? `Bienvenue, ${nom}` : "Choisissez votre espace";
});

function ouvrirEspaceMedecin() {
  authStore.definirEspaceActif("medecin");
  router.push({ name: "tableau-de-bord" });
}

function ouvrirEspacePersonnel() {
  authStore.definirEspaceActif("personnel");
  router.push({ name: authStore.aProfilSante ? "tableau-de-bord" : "profil-sante" });
}
</script>
