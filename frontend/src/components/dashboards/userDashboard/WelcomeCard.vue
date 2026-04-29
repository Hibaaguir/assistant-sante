<!-- Carte de bienvenue : affiche le nom de l'utilisateur et la date du jour -->
<template>
    <div class="mb-6 rounded-3xl bg-white border-2 border-blue-300 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-400 md:p-8">
        <div class="flex items-center justify-between gap-4">

            <!-- Texte : date + message de bienvenue -->
            <div class="flex-1">
                <Typography tag="h2" variant="h3-style">{{ currentDate }}</Typography>
                <Typography tag="h1" variant="h3-style" class="mt-3">Bienvenue, {{ userName }} !</Typography>
                <Typography tag="h2" variant="h3-style" class="mt-2">Continuez à surveiller votre santé au quotidien</Typography>
            </div>

            <!-- Illustration décorative (masquée sur mobile) -->
            <div class="hidden flex-shrink-0 md:block relative">
                <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-white/5 rounded-2xl blur-xl scale-105"></div>
                <img
                    src="https://img.freepik.com/vecteurs-libre/illustration-concept-abstrait-carte-puce-soins-sante-gerer-identite-patients-securiser-praticiens-pharmaciens-acceder-aux-dossiers-medicaux-ameliorer-communication_335657-884.jpg?semt=ais_hybrid&w=740&q=80"
                    alt="Illustration de santé"
                    class="relative h-48 w-48 object-cover rounded-2xl shadow-lg border border-purple-200"
                    loading="lazy"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import Typography from "@/components/ui/Typography.vue";

const authStore = useAuthStore();

// Nom de l'utilisateur connecté (ou "User" si non disponible)
const userName = computed(() => authStore.userName || "User");

// Date du jour formatée en français : "lundi 29 avril 2026"
const currentDate = computed(() => {
    return new Date().toLocaleDateString("fr-FR", {
        weekday: "long", year: "numeric", month: "long", day: "numeric",
    });
});
</script>
