<template>
  <div class="min-h-screen bg-gradient-to-b from-[#0B4EA2] via-[#0A3F8A] to-[#072E66] px-4 py-10">
    <!-- Header -->
    <div class="max-w-5xl mx-auto text-center">
      <div class="flex items-center justify-center gap-3 mb-2">
        <!-- petit icon spark -->
        <div class="text-cyan-200">
          <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 2l1.2 4.2L17.4 8 13.2 9.2 12 13.4 10.8 9.2 6.6 8l4.2-1.8L12 2Z"
              fill="currentColor"
              opacity="0.9"
            />
            <path
              d="M19 10l.8 2.8 2.2 1.2-2.2 1.2L19 18l-.8-2.8L16 14l2.2-1.2L19 10Z"
              fill="currentColor"
              opacity="0.7"
            />
          </svg>
        </div>

        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white">
          Mon Profil Santé
        </h1>
      </div>

      <p class="text-white/70 text-sm sm:text-base mb-8">
        Créez votre profil personnalisé en quelques étapes
      </p>
    </div>

    <!-- Progress line (Étape 1 sur 2, 50%) -->
    <div class="max-w-4xl mx-auto mb-8">
      <div class="flex items-center justify-between text-white/80 text-sm mb-2">
        <span>Étape {{ etape }} sur 2</span>
        <span>{{ etape === 1 ? "50%" : "100%" }}</span>
      </div>
      <div class="h-3 w-full rounded-full bg-white/15 overflow-hidden">
        <div
          class="h-full rounded-full bg-gradient-to-r from-cyan-300 to-emerald-300 transition-all duration-700"
          :style="{ width: etape === 1 ? '50%' : '100%' }"
        />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="max-w-4xl mx-auto">
      <div class="bg-white/10 border border-white/10 rounded-3xl p-10 text-center text-white">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 mb-4">
          <svg class="w-6 h-6 animate-spin" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 3a9 9 0 1 0 9 9"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
        <div class="font-semibold text-lg">Chargement...</div>
        <div class="text-white/70 text-sm">Veuillez patienter</div>
      </div>
    </div>

    <!-- Main content -->
    <div v-else class="max-w-4xl mx-auto">
      <div class="bg-white/10 border border-white/15 rounded-[28px] p-6 sm:p-8 shadow-[0_20px_80px_rgba(0,0,0,0.35)] backdrop-blur-xl">
        <component
          :is="etape === 1 ? Etape1 : Etape2"
          :form="form"
          @suivant="etape = 2"
          @retour="etape = 1"
          @enregistrer="enregistrer"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import axios from "axios";
import Etape1 from "./ProfilSanteEtape1.vue";
import Etape2 from "./ProfilSanteEtape2.vue";

const etape = ref(1);
const loading = ref(true);

const form = reactive({
  age: "",
  sexe: "",
  taille: "",
  poids: "",
  groupe_sanguin: "",
  objectif: "",
  allergies: [],
  maladies_chroniques: [],
  traitements: [],
  prend_medicament: false,
  nom_medicament: "",
  fumeur: false,
  alcool: false,
});

onMounted(async () => {
  const token = localStorage.getItem("auth_token");
  if (!token) {
    window.location.href = "/register";
    return;
  }

  try {
    const response = await axios.get("/api/profil-sante");
    if (response.data.data) {
      Object.assign(form, response.data.data);
    }
  } catch (error) {
    if (error.response?.status === 401) {
      localStorage.removeItem("auth_token");
      window.location.href = "/register";
      return;
    }
    console.log("Erreur lors du chargement du profil:", error);
  } finally {
    loading.value = false;
  }
});

async function enregistrer() {
  try {
    await axios.post("/api/profil-sante", form);
    alert("Profil enregistré avec succès");
  } catch (e) {
    if (e.response?.status === 401) {
      localStorage.removeItem("auth_token");
      window.location.href = "/register";
      return;
    }
    console.log(e);
    alert("Erreur lors de l'enregistrement");
  }
}
</script>
