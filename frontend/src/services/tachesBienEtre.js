import api from "@/services/api";

export function recupererTachesBienEtre(params = {}) {
  return api.get("/taches-bien-etre", { params });
}

export function creerTacheBienEtre(payload) {
  return api.post("/taches-bien-etre", payload);
}

export function mettreAJourTacheBienEtre(idTache, payload) {
  return api.put(`/taches-bien-etre/${idTache}`, payload);
}

export function basculerStatutTacheBienEtre(idTache) {
  return api.patch(`/taches-bien-etre/${idTache}/statut`);
}

export function supprimerTacheBienEtre(idTache) {
  return api.delete(`/taches-bien-etre/${idTache}`);
}
