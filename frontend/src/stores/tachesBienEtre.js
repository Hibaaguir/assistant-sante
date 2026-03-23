import { defineStore } from "pinia";
import { ref } from "vue";
import {
  basculerStatutTacheBienEtre,
  creerTacheBienEtre,
  mettreAJourTacheBienEtre,
  recupererTachesBienEtre,
  supprimerTacheBienEtre,
} from "@/services/tachesBienEtre";

export const useTachesBienEtreStore = defineStore("taches-bien-etre", () => {
  const chargement = ref(false);
  const taches = ref([]);
  const resume = ref({
    total: 0,
    completes: 0,
    categories: {
      "bien-etre": 0,
      sante: 0,
      fitness: 0,
      nutrition: 0,
    },
  });

  function appliquerReponse(reponse) {
    taches.value = Array.isArray(reponse?.data?.data) ? reponse.data.data : [];
    resume.value = {
      total: Number(reponse?.data?.meta?.total || 0),
      completes: Number(reponse?.data?.meta?.completes || 0),
      categories: {
        "bien-etre": Number(reponse?.data?.meta?.categories?.["bien-etre"] || 0),
        sante: Number(reponse?.data?.meta?.categories?.sante || 0),
        fitness: Number(reponse?.data?.meta?.categories?.fitness || 0),
        nutrition: Number(reponse?.data?.meta?.categories?.nutrition || 0),
      },
    };
  }

  async function charger() {
    chargement.value = true;
    try {
      const reponse = await recupererTachesBienEtre();
      appliquerReponse(reponse);
      return reponse;
    } finally {
      chargement.value = false;
    }
  }

  async function ajouter(payload) {
    const reponse = await creerTacheBienEtre(payload);
    await charger();
    return reponse?.data?.data;
  }

  async function mettreAJour(idTache, payload) {
    const reponse = await mettreAJourTacheBienEtre(idTache, payload);
    await charger();
    return reponse?.data?.data;
  }

  async function basculer(idTache) {
    const reponse = await basculerStatutTacheBienEtre(idTache);
    await charger();
    return reponse?.data?.data;
  }

  async function supprimer(idTache) {
    await supprimerTacheBienEtre(idTache);
    await charger();
  }

  return {
    chargement,
    taches,
    resume,
    charger,
    ajouter,
    mettreAJour,
    basculer,
    supprimer,
  };
});
