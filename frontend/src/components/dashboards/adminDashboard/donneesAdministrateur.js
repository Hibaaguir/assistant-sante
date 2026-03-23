// Donnees de depart pour simuler la gestion des comptes utilisateur dans le dashboard administrateur.
export function creerUtilisateursDepart() {
  return [
    {
      id: 1,
      nom: 'Marie Dubois',
      email: 'marie.dubois@email.com',
      type: 'Patient',
      specialite: '',
      statut: 'Actif',
      inscription: '15/01/2024',
      derniereActivite: '10/03/2024',
    },
    {
      id: 3,
      nom: 'Sophie Laurent',
      email: 'sophie.laurent@email.com',
      type: 'Patient',
      specialite: '',
      statut: 'Inactif',
      inscription: '05/12/2023',
      derniereActivite: '20/02/2024',
    },
    {
      id: 5,
      nom: 'Pierre Lefebvre',
      email: 'pierre.lefebvre@email.com',
      type: 'Patient',
      specialite: '',
      statut: 'Actif',
      inscription: '20/02/2024',
      derniereActivite: '14/03/2024',
    },
  ]
}
