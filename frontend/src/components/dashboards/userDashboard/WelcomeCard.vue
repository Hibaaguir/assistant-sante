<!--
  WelcomeCard.vue
  Carte de bienvenue personnalisée avec le nom de l'utilisateur connecté
-->
<template>
    <div
        class="mb-6 rounded-3xl bg-gradient-to-r from-purple-100 via-purple-100 to-purple-200 p-6 shadow-lg md:p-8"
    >
        <div class="flex items-center justify-between gap-4">
            <!-- Text content -->
            <div class="flex-1">
                <p class="text-sm font-medium text-purple-800">
                    {{ currentDate }}
                </p>
                <h2
                    class="mt-3 text-3xl font-bold text-purple-900 md:text-4xl leading-tight"
                >
                    Welcome, {{ userName }}!
                </h2>
                <p class="mt-2 text-base font-medium text-purple-800">
                    Continue monitoring your health daily
                </p>
            </div>

            <!-- Illustration -->
            <div class="hidden flex-shrink-0 md:block relative">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-white/10 to-white/5 rounded-2xl blur-xl scale-105"
                ></div>
                <img
                    src="https://img.freepik.com/vecteurs-libre/illustration-concept-abstrait-carte-puce-soins-sante-gerer-identite-patients-securiser-praticiens-pharmaciens-acceder-aux-dossiers-medicaux-ameliorer-communication_335657-884.jpg?semt=ais_hybrid&w=740&q=80"
                    alt="Healthcare illustration"
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

const authStore = useAuthStore();

const userName = computed(() => authStore.userName || "User");

const currentDate = computed(() => {
    const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    };
    return new Date().toLocaleDateString("en-US", options);
});
</script>
