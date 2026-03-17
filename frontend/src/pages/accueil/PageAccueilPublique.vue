<template>
  <div
    class="min-h-screen bg-[#eef2f7] text-slate-900"
    style="background-image: radial-gradient(circle at 15% 0%, #f1f8ff 0%, transparent 45%), radial-gradient(circle at 85% 20%, #f8f2ff 0%, transparent 40%);"
  >
    <header class="sticky top-0 z-40 border-b border-slate-200/90 bg-white/90 backdrop-blur-md">
      <div class="mx-auto flex min-h-[74px] w-[92%] max-w-[1120px] items-center justify-between gap-5">
        <RouterLink :to="{ name: 'accueil-publique' }" class="inline-flex items-center gap-2.5 font-extrabold tracking-[0.02em] text-indigo-600 no-underline">
          <span class="inline-flex h-7 w-7 items-center justify-center rounded-[9px] bg-gradient-to-br from-blue-500 to-violet-600 shadow-[0_12px_22px_-16px_#4338ca]" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.7]">
              <path d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6l-7-3Zm-1 6h2v2h2v2h-2v2h-2v-2H9v-2h2V9Z" />
            </svg>
          </span>
          <span>HealthFlow</span>
        </RouterLink>

        <nav class="hidden items-center gap-6 lg:flex">
          <a v-for="item in liensNavigationAccueil" :key="item.label" :href="item.href" class="text-[0.94rem] font-semibold text-slate-700 no-underline transition-colors hover:text-indigo-600">{{ item.label }}</a>
        </nav>

        <div class="flex items-center gap-2.5">
          <RouterLink
            :to="{ name: 'connexion' }"
            class="inline-flex min-h-[46px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-[0.92rem] font-bold text-slate-700 no-underline transition hover:-translate-y-0.5"
          >
            Connexion
          </RouterLink>
          <RouterLink
            :to="{ name: 'inscription' }"
            class="inline-flex min-h-[46px] items-center justify-center rounded-xl bg-gradient-to-r from-blue-500 to-violet-600 px-4 text-[0.92rem] font-bold text-white no-underline shadow-[0_14px_20px_-18px_#4338ca] transition hover:-translate-y-0.5"
          >
            Creer un compte
          </RouterLink>
        </div>
      </div>
    </header>

    <main>
      <section class="mx-auto grid w-[92%] max-w-[1120px] grid-cols-1 items-center gap-7 py-12 lg:grid-cols-2">
        <div data-anime class="translate-y-6 opacity-0 transition-all duration-700">
          <h1 class="m-0 text-[clamp(2rem,4vw,3.25rem)] font-extrabold leading-[1.1]">
            Prenez le controle de votre
            <span class="bg-gradient-to-r from-[#3b82f6] to-[#d946ef] bg-clip-text text-transparent">sante</span>
          </h1>
          <p class="mt-4 max-w-[56ch] leading-7 text-slate-600">
            Votre assistant personnel de sante et bien-etre concu pour vous aider a suivre, comprendre et ameliorer votre sante au fil du temps.
          </p>
          <div class="mt-6 flex flex-wrap gap-3">
            <RouterLink :to="{ name: 'inscription' }" class="inline-flex min-h-[46px] items-center justify-center rounded-xl bg-gradient-to-r from-blue-500 to-violet-600 px-5 text-[0.92rem] font-bold text-white no-underline transition hover:-translate-y-0.5">Commencer gratuitement</RouterLink>
            <RouterLink :to="{ name: 'connexion' }" class="inline-flex min-h-[46px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-[0.92rem] font-bold text-slate-700 no-underline transition hover:-translate-y-0.5">En savoir plus</RouterLink>
          </div>
        </div>

        <figure data-anime class="translate-y-6 overflow-hidden rounded-2xl border border-slate-200 shadow-[0_30px_45px_-35px_#1e293b] opacity-0 transition-all duration-700 [transition-delay:120ms]">
          <img
            src="https://images.pexels.com/photos/4386466/pexels-photo-4386466.jpeg?auto=compress&cs=tinysrgb&w=1200"
            alt="Personne consultant une application de suivi de sante sur smartphone"
            loading="lazy"
            class="block h-[300px] w-full object-cover md:h-[420px]"
          />
        </figure>
      </section>

      <section id="fonctionnalites" class="py-16">
        <div class="mx-auto w-[92%] max-w-[1120px]">
          <h2 class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight text-slate-900">Un suivi complet de votre sante</h2>
          <p class="mt-3 max-w-[60ch] text-slate-600">Tout ce dont vous avez besoin pour surveiller et ameliorer votre bien-etre.</p>

          <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <CarteFonctionnaliteAccueil
              v-for="(fonctionnalite, index) in fonctionnalitesAccueil"
              :key="fonctionnalite.titre"
              :fonctionnalite="fonctionnalite"
              :index="index"
            />
          </div>
        </div>
      </section>

      <section v-for="(section, index) in sectionsNarrativesAccueil" :id="section.identifiant" :key="section.identifiant" class="py-9">
        <div class="mx-auto grid w-[92%] max-w-[1120px] grid-cols-1 items-center gap-7 lg:grid-cols-2">
          <figure
            data-anime
            :class="[
              'translate-y-6 overflow-hidden rounded-2xl border border-slate-200 shadow-[0_28px_42px_-40px_#0f172a] opacity-0 transition-all duration-700',
              section.type === 'image-droite' ? 'lg:order-2' : '',
            ]"
            :style="{ transitionDelay: `${index * 120}ms` }"
          >
            <img :src="section.image" :alt="section.alt" loading="lazy" class="block h-[280px] w-full object-cover md:h-[340px]" />
          </figure>

          <article
            data-anime
            :class="[
              'translate-y-6 opacity-0 transition-all duration-700',
              section.type === 'image-droite' ? 'lg:order-1' : '',
            ]"
            :style="{ transitionDelay: `${80 + index * 120}ms` }"
          >
            <h2 v-if="section.identifiant === 'solutions'" class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight text-slate-900">
              Votre compagnon de sante
              <span class="text-sky-500">quotidien</span>
            </h2>
            <h2 v-else-if="section.identifiant === 'analyses-ia'" class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight text-slate-900">
              Insights de sante
              <span class="bg-gradient-to-r from-[#7c3aed] to-[#ec4899] bg-clip-text text-transparent">intelligents</span>
            </h2>
            <h2 v-else-if="section.identifiant === 'a-propos'" class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight text-slate-900">
              Partagez avec votre
              <span class="bg-gradient-to-r from-[#4f46e5] to-[#7c3aed] bg-clip-text text-transparent">equipe de sante</span>
            </h2>
            <h2 v-else class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight text-slate-900">{{ section.titre }}</h2>
            <p class="my-4 leading-7 text-slate-600">{{ section.texte }}</p>

            <ul v-if="section.identifiant === 'analyses-ia'" class="m-0 grid list-none gap-2.5 p-0">
              <li
                v-for="point in section.points"
                :key="point.titre"
                class="rounded-xl border border-slate-200 bg-white px-3.5 py-3"
              >
                <div class="flex items-center gap-2">
                  <span
                    aria-hidden="true"
                    class="inline-block h-1.5 w-1.5 rounded-full"
                    :style="{ backgroundColor: point.couleur || '#8b5cf6' }"
                  ></span>
                  <p class="m-0 text-[0.92rem] font-bold text-slate-900">{{ point.titre }}</p>
                </div>
                <p class="mt-1.5 text-[0.85rem] text-slate-500">{{ point.description }}</p>
              </li>
            </ul>

            <ul v-else-if="section.identifiant === 'solutions'" class="m-0 grid list-none gap-2.5 p-0">
              <li
                v-for="point in section.points"
                :key="point.titre"
                class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-3"
              >
                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :style="{ backgroundColor: point.couleur || '#8b5cf6' }">
                  <svg v-if="point.icone === 'horloge'" viewBox="0 0 24 24" fill="none" class="h-4 w-4 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"><path d="M12 6v6l4 2M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" class="h-4 w-4 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"><path d="M7 4h10v16H7zM9 8h6M9 12h6" /></svg>
                </span>
                <div>
                  <p class="m-0 text-[0.92rem] font-bold text-slate-900">{{ point.titre }}</p>
                  <p class="mt-1.5 text-[0.85rem] text-slate-500">{{ point.description }}</p>
                </div>
              </li>
            </ul>

            <ul v-else-if="section.identifiant === 'a-propos'" class="m-0 list-none p-0">
              <li class="flex items-start gap-3 rounded-xl border border-[#c7cbff] bg-[#f2f4ff] px-4 py-4">
                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#8b5cf6]">
                  <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"><path d="M12 3 6 6v5c0 4 2 7 6 10 4-3 6-6 6-10V6l-6-3Z" /></svg>
                </span>
                <div>
                  <p class="m-0 text-[0.94rem] font-bold text-slate-900">{{ section.points[0].titre }}</p>
                  <p class="mt-1.5 text-[0.84rem] leading-6 text-slate-600">{{ section.points[0].description }}</p>
                </div>
              </li>
            </ul>

            <ul v-else class="m-0 grid list-none gap-2.5 p-0">
              <li
                v-for="point in section.points"
                :key="point.texte || point"
                class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-3 font-semibold text-slate-800"
              >
                <span
                  aria-hidden="true"
                  class="inline-block h-2 w-2 rounded-full"
                  :style="{ backgroundColor: point.couleur || '#8b5cf6', boxShadow: `0 0 0 4px ${point.couleur || '#8b5cf6'}33` }"
                ></span>
                <span>{{ point.texte || point }}</span>
              </li>
            </ul>
          </article>
        </div>
      </section>

      <section class="mt-10 bg-gradient-to-r from-[#2845d6] via-[#8b2eff] to-[#e11d8f] py-16 text-white">
        <div data-anime class="mx-auto w-[92%] max-w-[1120px] translate-y-6 text-center opacity-0 transition-all duration-700">
          <h2 class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight">Commencez votre parcours de sante aujourd'hui</h2>
          <p class="mx-auto mt-4 max-w-[60ch] leading-7 text-violet-50">Rejoignez des milliers d'utilisateurs qui prennent le controle de leur sante avec clarte et simplicite.</p>
          <div class="mt-6 flex flex-wrap justify-center gap-3">
            <RouterLink :to="{ name: 'inscription' }" class="inline-flex min-h-[46px] items-center justify-center rounded-xl bg-white px-5 text-[0.92rem] font-bold text-blue-700 no-underline transition hover:-translate-y-0.5">Creer votre compte gratuit</RouterLink>
            <RouterLink :to="{ name: 'connexion' }" class="inline-flex min-h-[46px] items-center justify-center rounded-xl border border-violet-200/80 bg-transparent px-5 text-[0.92rem] font-bold text-white no-underline transition hover:-translate-y-0.5">Connexion</RouterLink>
          </div>

          <div class="mt-6 flex flex-wrap justify-center gap-4 text-[0.85rem] font-bold text-violet-100">
            <span class="inline-flex items-center gap-1.5">
              <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 stroke-violet-50 [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"><path d="M12 3 6 6v5c0 4 2 7 6 10 4-3 6-6 6-10V6l-6-3Z" /></svg>
              Donnees securisees
            </span>
            <span class="inline-flex items-center gap-1.5">
              <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 stroke-violet-50 [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.3A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z" /></svg>
              Conforme RGPD
            </span>
            <span class="inline-flex items-center gap-1.5">
              <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 stroke-violet-50 [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"><path d="M12 2v20M4 12h16" /></svg>
              100% Gratuit
            </span>
          </div>
        </div>
      </section>
    </main>

    <footer class="bg-[#101d46] px-5 py-7 text-center text-[#dbe7ff]">
      <p class="m-0 font-extrabold text-[#99a9ff]">HealthFlow</p>
      <p class="my-2">Suivi de sante moderne, calme et premium pour tous.</p>
      <small class="text-[#99a6ca]">Copyright {{ anneeCourante }} HealthFlow. Tous droits reserves.</small>
    </footer>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted } from "vue";
import CarteFonctionnaliteAccueil from "@/components/accueil/CarteFonctionnaliteAccueil.vue";
import {
  fonctionnalitesAccueil,
  liensNavigationAccueil,
  sectionsNarrativesAccueil,
} from "@/pages/accueil/contenuAccueil";

const anneeCourante = new Date().getFullYear();
let observateurAnimations;

function afficherElementsVisibles() {
  const elementsAnimes = document.querySelectorAll("[data-anime]");

  observateurAnimations = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.remove("opacity-0", "translate-y-6");
          entry.target.classList.add("opacity-100", "translate-y-0");
          observateurAnimations.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.2 },
  );

  elementsAnimes.forEach((element) => observateurAnimations.observe(element));
}

onMounted(() => {
  afficherElementsVisibles();
});

onBeforeUnmount(() => {
  if (observateurAnimations) {
    observateurAnimations.disconnect();
  }
});
</script>
