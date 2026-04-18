<template>
    <div class="min-h-screen bg-white text-slate-900" style="font-family: 'Inter', sans-serif">

        <!-- ── Header ─────────────────────────────────────────────────────────── -->
        <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/95 backdrop-blur-md">
            <div class="flex min-h-[100px] w-full items-center justify-between gap-5 px-8 lg:px-16">

                <RouterLink :to="{ name: 'public-home' }"
                    class="inline-flex items-center gap-3 font-extrabold tracking-tight text-blue-700 no-underline">
                    <span class="inline-flex h-13 w-13 items-center justify-center rounded-[14px] bg-gradient-to-br from-blue-500 to-blue-700 shadow-md shadow-blue-200">
                        <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.7]">
                            <path d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6l-7-3Zm-1 6h2v2h2v2h-2v2h-2v-2H9v-2h2V9Z" />
                        </svg>
                    </span>
                    <span class="text-[1.6rem]">HealthFlow</span>
                </RouterLink>

                <nav class="hidden items-center gap-9 lg:flex">
                    <a v-for="item in homeNavigationLinks" :key="item.label" :href="item.href"
                        class="text-[1rem] font-medium text-slate-500 no-underline transition-colors hover:text-blue-600">
                        {{ item.label }}
                    </a>
                </nav>

                <div class="flex items-center gap-3">
                    <RouterLink :to="{ name: 'login' }"
                        class="inline-flex min-h-[48px] items-center justify-center rounded-xl border border-slate-200 px-7 text-[0.95rem] font-semibold text-slate-600 no-underline transition hover:border-blue-300 hover:text-blue-600">
                        Connexion
                    </RouterLink>
                    <RouterLink :to="{ name: 'register' }"
                        class="inline-flex min-h-[48px] items-center justify-center rounded-xl bg-blue-600 px-7 text-[0.95rem] font-semibold text-white no-underline transition hover:bg-blue-700">
                        Créer un compte
                    </RouterLink>
                </div>
            </div>
        </header>

        <main>

            <!-- ── Hero ───────────────────────────────────────────────────────── -->
            <section class="grid w-full grid-cols-1 items-stretch lg:grid-cols-2 min-h-[520px] max-h-[600px]">

                <!-- Texte -->
                <div class="flex flex-col justify-center px-8 py-16 lg:px-16 xl:px-24">
                    <span data-anime class="translate-y-4 opacity-0 transition-all duration-500 inline-flex w-fit items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3.5 py-1.5 text-[0.78rem] font-semibold text-blue-600 mb-6">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        Plateforme de santé numérique
                    </span>

                    <h1 data-anime class="translate-y-4 opacity-0 transition-all duration-500 [transition-delay:80ms] text-[2.6rem] lg:text-[3.2rem] font-extrabold leading-[1.1] tracking-tight text-slate-900">
                        Prenez le contrôle<br>
                        de votre
                        <span class="bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent"> santé</span>
                    </h1>

                    <p data-anime class="translate-y-4 opacity-0 transition-all duration-500 [transition-delay:160ms] mt-5 text-[1rem] leading-7 text-slate-500 max-w-[48ch]">
                        Votre assistant personnel de santé conçu pour vous aider à suivre,
                        comprendre et améliorer votre bien-être au fil du temps.
                    </p>

                    <div data-anime class="translate-y-4 opacity-0 transition-all duration-500 [transition-delay:240ms] mt-8 flex flex-wrap gap-3">
                        <RouterLink :to="{ name: 'register' }"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-xl bg-blue-600 px-7 text-[0.92rem] font-bold text-white no-underline shadow-lg shadow-blue-200/60 transition hover:bg-blue-700 hover:-translate-y-0.5">
                            Commencer gratuitement
                        </RouterLink>
                        <RouterLink :to="{ name: 'login' }"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-xl border border-slate-200 px-7 text-[0.92rem] font-bold text-slate-700 no-underline transition hover:border-blue-300 hover:text-blue-600 hover:-translate-y-0.5">
                            Se connecter
                        </RouterLink>
                    </div>

                    <div data-anime class="translate-y-4 opacity-0 transition-all duration-500 [transition-delay:320ms] mt-9 flex flex-wrap gap-5">
                        <span v-for="b in TRUST_BADGES" :key="b" class="flex items-center gap-1.5 text-[0.8rem] font-medium text-slate-400">
                            <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 stroke-blue-500 [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:2.5]">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                            {{ b }}
                        </span>
                    </div>
                </div>

                <!-- Image pleine hauteur -->
                <div class="hidden lg:block relative">
                    <img src="https://images.pexels.com/photos/4386466/pexels-photo-4386466.jpeg?auto=compress&cs=tinysrgb&w=1200"
                        alt="Suivi de santé" loading="lazy"
                        class="absolute inset-0 h-full w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent"></div>
                </div>
            </section>

            <!-- ── Stats ──────────────────────────────────────────────────────── -->
            <section class="border-y border-slate-100 bg-slate-50 py-10">
                <div class="grid w-full grid-cols-2 gap-6 px-8 md:grid-cols-4 lg:px-14">
                    <div v-for="stat in STATS" :key="stat.label" class="text-center">
                        <p class="text-3xl font-extrabold text-blue-600">{{ stat.value }}</p>
                        <p class="mt-1 text-[0.82rem] font-medium text-slate-500">{{ stat.label }}</p>
                    </div>
                </div>
            </section>

            <!-- ── Features ───────────────────────────────────────────────────── -->
            <section id="features" class="py-20 px-8 lg:px-14">
                <div class="text-center mb-12">
                    <h2 class="text-[2rem] font-extrabold tracking-tight text-slate-900">Suivi santé complet</h2>
                    <p class="mt-3 text-[0.95rem] text-slate-500 max-w-[55ch] mx-auto">
                        Tout ce dont vous avez besoin pour surveiller et améliorer votre bien-être.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <FeatureCard v-for="(feature, index) in homeFeatures" :key="feature.titre"
                        :fonctionnalite="feature" :index="index" />
                </div>
            </section>

            <!-- ── Narrative Sections ─────────────────────────────────────────── -->
            <section v-for="(section, index) in homeNarrativeSections" :id="section.identifiant"
                :key="section.identifiant" class="py-12 px-8 lg:px-14">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <figure data-anime
                        class="translate-y-6 overflow-hidden rounded-3xl border border-slate-200 shadow-[0_24px_40px_-32px_rgba(30,58,138,0.15)] opacity-0 transition-all duration-700"
                        :class="section.type === 'image-droite' ? 'lg:order-2' : ''"
                        :style="{ transitionDelay: `${index * 100}ms` }">
                        <img :src="section.image" :alt="section.alt" loading="lazy"
                            class="block h-[280px] w-full object-cover md:h-[360px]" />
                    </figure>

                    <article data-anime class="translate-y-6 opacity-0 transition-all duration-700"
                        :class="section.type === 'image-droite' ? 'lg:order-1' : ''"
                        :style="{ transitionDelay: `${80 + index * 100}ms` }">
                        <h2 class="text-[1.8rem] font-extrabold tracking-tight text-slate-900">
                            {{ section.titreAvant }}
                            <span v-if="section.titreAccent" :class="section.titreAccentClass">{{ section.titreAccent }}</span>
                            {{ section.titreApres }}
                        </h2>
                        <p class="my-4 leading-7 text-slate-500">{{ section.texte }}</p>
                        <FeaturesSection :points="section.points" :variant="section.variantePoints" />
                    </article>
                </div>
            </section>

            <!-- ── CTA ────────────────────────────────────────────────────────── -->
            <section class="mt-10 bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 py-20 px-8 lg:px-14 text-white">
                <div data-anime class="translate-y-6 text-center opacity-0 transition-all duration-700">
                    <h2 class="text-[2rem] font-extrabold tracking-tight text-white">
                        Commencez votre parcours santé aujourd'hui
                    </h2>
                    <p class="mx-auto mt-4 max-w-[52ch] leading-7 text-blue-100 text-[0.95rem]">
                        Rejoignez des milliers d'utilisateurs qui prennent le contrôle de leur santé avec clarté et simplicité.
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <RouterLink :to="{ name: 'register' }"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-xl bg-white px-7 text-[0.92rem] font-bold text-blue-700 no-underline shadow-lg transition hover:-translate-y-0.5">
                            Créer un compte gratuit
                        </RouterLink>
                        <RouterLink :to="{ name: 'login' }"
                            class="inline-flex min-h-[48px] items-center justify-center rounded-xl border border-white/30 px-7 text-[0.92rem] font-bold text-white no-underline transition hover:bg-white/10 hover:-translate-y-0.5">
                            Connexion
                        </RouterLink>
                    </div>

                    <div class="mt-8 flex flex-wrap justify-center gap-6 text-[0.8rem] font-medium text-blue-100/80">
                        <span v-for="badge in CTA_BADGES" :key="badge.label" class="inline-flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 stroke-blue-200 [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:2]">
                                <path :d="badge.icon" />
                            </svg>
                            {{ badge.label }}
                        </span>
                    </div>
                </div>
            </section>
        </main>

        <!-- ── Footer ─────────────────────────────────────────────────────────── -->
        <footer class="bg-[#0f1f4a] px-8 py-10 lg:px-14">
            <div class="flex flex-col items-center gap-3 text-center">
                <p class="text-[1rem] font-extrabold text-blue-300">HealthFlow</p>
                <p class="text-[0.85rem] text-blue-200/60">
                    Suivi de santé moderne, calme et premium pour tous.
                </p>
                <small class="text-[0.75rem] text-blue-300/40">
                    Copyright {{ currentYear }} HealthFlow. Tous droits réservés.
                </small>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted } from "vue";
import FeatureCard from "@/components/home/FeatureCard.vue";
import FeaturesSection from "@/components/home/FeaturesSection.vue";
import { homeFeatures, homeNavigationLinks, homeNarrativeSections } from "@/pages/home/homeContent";

const currentYear = new Date().getFullYear();

const TRUST_BADGES = ["100% Gratuit", "Données sécurisées", "Conforme au RGPD"];

const STATS = [
    { value: "10k+", label: "Utilisateurs actifs" },
    { value: "99%",  label: "Satisfaction" },
    { value: "24/7", label: "Disponibilité" },
    { value: "100%", label: "Sécurisé" },
];

const CTA_BADGES = [
    { label: "Données sécurisées", icon: "M12 3 6 6v5c0 4 2 7 6 10 4-3 6-6 6-10V6l-6-3Z" },
    { label: "Conforme au RGPD",   icon: "M20 6 9 17l-5-5" },
    { label: "100% Gratuit",       icon: "M12 2v20M4 12h16" },
];

let observer;
onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.remove("opacity-0", "translate-y-6", "translate-y-4");
                entry.target.classList.add("opacity-100", "translate-y-0");
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.15 },
    );
    document.querySelectorAll("[data-anime]").forEach((el) => observer.observe(el));
});
onBeforeUnmount(() => observer?.disconnect());
</script>
