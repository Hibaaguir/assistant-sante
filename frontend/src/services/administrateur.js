import api from '@/services/api'

export async function listerUtilisateursAdministrateur() {
  const reponse = await api.get('/admin/utilisateurs')
  return Array.isArray(reponse?.data?.data) ? reponse.data.data : []
}

export async function modifierUtilisateurAdministrateur(idUtilisateur, donnees) {
  await api.put(`/admin/utilisateurs/${idUtilisateur}`, donnees)
}

export async function supprimerUtilisateurAdministrateur(idUtilisateur) {
  await api.delete(`/admin/utilisateurs/${idUtilisateur}`)
}
