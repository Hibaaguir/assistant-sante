<template>
    <div
        class="min-h-screen bg-[#eef2f7] text-slate-900"
        style="
            background-image:
                radial-gradient(circle at 15% 0%, #f1f8ff 0%, transparent 45%),
                radial-gradient(circle at 85% 20%, #f8f2ff 0%, transparent 40%);
        "
    >
        <!-- Header -->
        <header
            class="sticky top-0 z-40 border-b border-slate-200/90 bg-white/90 backdrop-blur-md"
        >
            <div
                class="mx-auto flex min-h-[74px] w-[92%] max-w-[1120px] items-center justify-between gap-5"
            >
                <RouterLink
                    :to="{ name: 'public-home' }"
                    class="inline-flex items-center gap-2.5 font-extrabold tracking-[0.02em] text-purple-600 no-underline"
                >
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-[9px] bg-gradient-to-br from-purple-400 to-purple-500 shadow-[0_12px_22px_-16px_rgba(168,85,247,0.4)]"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="h-4 w-4 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.7]"
                        >
                            <path
                                d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6l-7-3Zm-1 6h2v2h2v2h-2v2h-2v-2H9v-2h2V9Z"
                            />
                        </svg>
                    </span>
                    <span>HealthFlow</span>
                </RouterLink>

                <nav class="hidden items-center gap-6 lg:flex">
                    <a
                        v-for="item in homeNavigationLinks"
                        :key="item.label"
                        :href="item.href"
                        class="text-[0.94rem] font-semibold text-slate-700 no-underline transition-colors hover:text-purple-600"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div class="flex items-center gap-2.5">
                    <RouterLink
                        :to="{ name: 'login' }"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-[0.92rem] font-bold text-slate-700 no-underline transition hover:-translate-y-0.5"
                    >
                        Connexion
                    </RouterLink>
                    <RouterLink
                        :to="{ name: 'register' }"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-xl bg-gradient-to-r from-purple-400 to-purple-500 px-4 text-[0.92rem] font-bold text-white no-underline shadow-[0_14px_20px_-18px_rgba(168,85,247,0.3)] transition hover:-translate-y-0.5"
                    >
                        Créer un compte
                    </RouterLink>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero -->
            <section
                class="mx-auto grid w-[92%] max-w-[1120px] grid-cols-1 items-center gap-7 py-12 lg:grid-cols-2"
            >
                <div
                    data-anime
                    class="translate-y-6 opacity-0 transition-all duration-700"
                >
                    <h1
                        class="m-0 text-[clamp(2rem,4vw,3.25rem)] font-extrabold leading-[1.1]"
                    >
                        Prenez le contrôle de votre
                        <span
                            class="bg-gradient-to-r from-[#3b82f6] to-[#d946ef] bg-clip-text text-transparent"
                            >santé</span
                        >
                    </h1>
                    <p class="mt-4 max-w-[56ch] leading-7 text-slate-600">
                        Votre assistant personnel de santé et bien-être conçu
                        pour vous aider à suivre, comprendre et améliorer votre
                        santé au fil du temps.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <RouterLink
                            :to="{ name: 'register' }"
                            class="inline-flex min-h-[46px] items-center justify-center rounded-xl bg-gradient-to-r from-purple-400 to-purple-500 px-5 text-[0.92rem] font-bold text-white no-underline transition hover:-translate-y-0.5"
                        >
                            Commencer gratuitement
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'login' }"
                            class="inline-flex min-h-[46px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-[0.92rem] font-bold text-slate-700 no-underline transition hover:-translate-y-0.5"
                        >
                            En savoir plus
                        </RouterLink>
                    </div>
                </div>

                <figure
                    data-anime
                    class="translate-y-6 overflow-hidden rounded-2xl border border-slate-200 shadow-[0_30px_45px_-35px_#1e293b] opacity-0 transition-all duration-700 [transition-delay:120ms]"
                >
                    <img
                        src="https://images.pexels.com/photos/4386466/pexels-photo-4386466.jpeg?auto=compress&cs=tinysrgb&w=1200"
                        alt="Person consulting a health tracking app on smartphone"
                        loading="lazy"
                        class="block h-[300px] w-full object-cover md:h-[420px]"
                    />
                </figure>
            </section>

            <!-- Features -->
            <section id="features" class="py-16">
                <div class="mx-auto w-[92%] max-w-[1120px]">
                    <h2
                        class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight text-slate-900"
                    >
                        Suivi santé complet
                    </h2>
                    <p class="mt-3 max-w-[60ch] text-slate-600">
                        Tout ce dont vous avez besoin pour surveiller et
                        améliorer votre bien-être.
                    </p>
                    <div
                        class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
                    >
                        <FeatureCard
                            v-for="(feature, index) in homeFeatures"
                            :key="feature.titre"
                            :fonctionnalite="feature"
                            :index="index"
                        />
                    </div>
                </div>
            </section>

            <!-- Narrative Sections -->
            <section
                v-for="(section, index) in homeNarrativeSections"
                :id="section.identifiant"
                :key="section.identifiant"
                class="py-9"
            >
                <div
                    class="mx-auto grid w-[92%] max-w-[1120px] grid-cols-1 items-center gap-7 lg:grid-cols-2"
                >
                    <figure
                        data-anime
                        class="translate-y-6 overflow-hidden rounded-2xl border border-slate-200 shadow-[0_28px_42px_-40px_#0f172a] opacity-0 transition-all duration-700"
                        :class="
                            section.type === 'image-droite' ? 'lg:order-2' : ''
                        "
                        :style="{ transitionDelay: `${index * 120}ms` }"
                    >
                        <img
                            :src="section.image"
                            :alt="section.alt"
                            loading="lazy"
                            class="block h-[280px] w-full object-cover md:h-[340px]"
                        />
                    </figure>

                    <article
                        data-anime
                        class="translate-y-6 opacity-0 transition-all duration-700"
                        :class="
                            section.type === 'image-droite' ? 'lg:order-1' : ''
                        "
                        :style="{ transitionDelay: `${80 + index * 120}ms` }"
                    >
                        <!-- Title with optional highlight -->
                        <h2
                            class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight text-slate-900"
                        >
                            {{ section.titreAvant }}
                            <span
                                v-if="section.titreAccent"
                                :class="section.titreAccentClass"
                                >{{ section.titreAccent }}</span
                            >
                            {{ section.titreApres }}
                        </h2>

                        <p class="my-4 leading-7 text-slate-600">
                            {{ section.texte }}
                        </p>

                        <!-- Points -->
                        <FeaturesSection
                            :points="section.points"
                            :variant="section.variantePoints"
                        />
                    </article>
                </div>
            </section>

            <!-- CTA -->
            <section
                class="mt-10 bg-gradient-to-r from-[#2845d6] via-[#8b2eff] to-[#e11d8f] py-16 text-white"
            >
                <div
                    data-anime
                    class="mx-auto w-[92%] max-w-[1120px] translate-y-6 text-center opacity-0 transition-all duration-700"
                >
                    <h2
                        class="m-0 text-[clamp(1.8rem,3.5vw,2.75rem)] font-extrabold leading-tight"
                    >
                        Start your health journey today
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-[60ch] leading-7 text-violet-50"
                    >
                        Join thousands of users who are taking control of their
                        health with clarity and simplicity.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <RouterLink
                            :to="{ name: 'register' }"
                            class="inline-flex min-h-[46px] items-center justify-center rounded-xl bg-white px-5 text-[0.92rem] font-bold text-blue-700 no-underline transition hover:-translate-y-0.5"
                        >
                            Create Free Account
                        </RouterLink>
                        <RouterLink
                            :to="{ name: 'login' }"
                            class="inline-flex min-h-[46px] items-center justify-center rounded-xl border border-violet-200/80 bg-transparent px-5 text-[0.92rem] font-bold text-white no-underline transition hover:-translate-y-0.5"
                        >
                            Login
                        </RouterLink>
                    </div>

                    <div
                        class="mt-6 flex flex-wrap justify-center gap-4 text-[0.85rem] font-bold text-violet-100"
                    >
                        <span
                            v-for="badge in CTA_BADGES"
                            :key="badge.label"
                            class="inline-flex items-center gap-1.5"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                class="h-3.5 w-3.5 stroke-violet-50 [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"
                            >
                                <path :d="badge.icon" />
                            </svg>
                            {{ badge.label }}
                        </span>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-[#101d46] px-5 py-7 text-center text-[#dbe7ff]">
            <p class="m-0 font-extrabold text-[#99a9ff]">HealthFlow</p>
            <p class="my-2">
                Modern, calm and premium health tracking for everyone.
            </p>
            <small class="text-[#99a6ca]"
                >Copyright {{ currentYear }} HealthFlow. All rights
                reserved.</small
            >
        </footer>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted } from "vue";
import FeatureCard from "@/components/home/FeatureCard.vue";
import FeaturesSection from "@/pages/home/FeaturesSection.vue";
import {
    homeFeatures,
    homeNavigationLinks,
    homeNarrativeSections,
} from "@/pages/home/homeContent";

const currentYear = new Date().getFullYear();

// ─── Constants ───────────────────────────────────────────────

const CTA_BADGES = [
    {
        label: "Secure Data",
        icon: "M12 3 6 6v5c0 4 2 7 6 10 4-3 6-6 6-10V6l-6-3Z",
    },
    {
        label: "GDPR Compliant",
        icon: "M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.3A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z",
    },
    { label: "100% Free", icon: "M12 2v20M4 12h16" },
];

// ─── Animations IntersectionObserver ─────────────────────────

let observer;

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.remove("opacity-0", "translate-y-6");
                entry.target.classList.add("opacity-100", "translate-y-0");
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.2 },
    );
    document
        .querySelectorAll("[data-anime]")
        .forEach((el) => observer.observe(el));
});

onBeforeUnmount(() => observer?.disconnect());
</script>
