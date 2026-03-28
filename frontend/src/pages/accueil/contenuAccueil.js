// ─── Navigation ───────────────────────────────────────────────

export const liensNavigationAccueil = [
  { label: 'Fonctionnalités', href: '#fonctionnalites' },
  { label: 'Solutions',       href: '#solutions' },
  { label: 'À propos',        href: '#a-propos' },
]

// ─── Fonctionnalités ──────────────────────────────────────────

export const fonctionnalitesAccueil = [
  {
    titre:       'Journal Quotidien',
    description: 'Suivez votre sommeil, stress, énergie, nutrition, hydratation et activité physique avec un bilan quotidien structuré.',
    accent:      '#0ea5e9',
    icone:       'journal',
  },
  {
    titre:       'Dossier Santé',
    description: 'Surveillez vos signes vitaux, analyses biologiques et conservez un historique complet de vos mesures.',
    accent:      '#22c55e',
    icone:       'dossier',
  },
  {
    titre:       'Gestion des Traitements',
    description: 'Calendrier de traitements généré par IA avec rappels et confirmation simple des doses.',
    accent:      '#8b5cf6',
    icone:       'traitements',
  },
  {
    titre:       'Analyses IA',
    description: 'Analyse continue pour détecter les anomalies, identifier les tendances et générer des recommandations personnalisées.',
    accent:      '#ec4899',
    icone:       'analyses',
  },
  {
    titre:       "Suivi d'Objectifs",
    description: 'Définissez vos objectifs de santé et recevez des conseils concrets pour les atteindre.',
    accent:      '#f59e0b',
    icone:       'objectifs',
  },
  {
    titre:       'Collaboration Médecin',
    description: 'Invitez votre médecin à accéder à vos données en lecture seule pour un meilleur suivi.',
    accent:      '#4f46e5',
    icone:       'medecin',
  },
]

// ─── Sections narratives ──────────────────────────────────────

export const sectionsNarrativesAccueil = [
  {
    identifiant:      'solutions',
    type:             'image-gauche',
    titreAvant:       'Votre compagnon de santé',
    titreAccent:      'quotidien',
    titreAccentClass: 'text-sky-500',
    titreApres:       '',
    variantePoints:   'icone',
    texte:            "Remplissez votre journal quotidien structuré couvrant tous les aspects de votre bien-être : qualité du sommeil, niveaux de stress, énergie, nutrition (repas, calories, sucre, caféine, hydratation), habitudes (alcool, tabac), et activité physique.",
    image:            'https://images.pexels.com/photos/7659564/pexels-photo-7659564.jpeg?auto=compress&cs=tinysrgb&w=1200',
    alt:              'Personne prenant des notes pour son suivi quotidien de santé',
    points: [
      {
        titre:       'Contrôles quotidiens rapides',
        description: 'Prend seulement quelques minutes chaque jour.',
        couleur:     '#0ea5e9',
        icone:       'horloge',
      },
      {
        titre:       'Historique complet',
        description: 'Toutes les entrées sont pleinement modifiables et suivies au fil du temps.',
        couleur:     '#4f46e5',
        icone:       'fichier',
      },
    ],
  },
  {
    identifiant:      'analyses-ia',
    type:             'image-droite',
    titreAvant:       'Insights de santé',
    titreAccent:      'intelligents',
    titreAccentClass: 'bg-gradient-to-r from-[#7c3aed] to-[#ec4899] bg-clip-text text-transparent',
    titreApres:       '',
    variantePoints:   'badge',
    texte:            'Notre système IA analyse continuellement votre profil de santé, vos entrées quotidiennes et vos dossiers de santé pour détecter les anomalies, identifier les tendances et générer des recommandations personnalisées.',
    image:            'https://images.pexels.com/photos/590022/pexels-photo-590022.jpeg?auto=compress&cs=tinysrgb&w=1200',
    alt:              'Tableau de bord médical avec graphiques',
    points: [
      {
        titre:       'Alertes calmes, non alarmantes',
        description: 'Recevez des notifications douces sur des modèles de santé importants.',
        couleur:     '#ec4899',
      },
      {
        titre:       'Recommandations actionnables',
        description: 'Obtenez des conseils clairs et pratiques pour améliorer votre mode de vie.',
        couleur:     '#8b5cf6',
      },
      {
        titre:       'Focus préventif',
        description: "Identifiez les problèmes potentiels avant qu'ils ne deviennent des problèmes.",
        couleur:     '#f97316',
      },
    ],
  },
  {
    identifiant:      'a-propos',
    type:             'image-gauche',
    titreAvant:       'Partagez avec votre',
    titreAccent:      'équipe de santé',
    titreAccentClass: 'bg-gradient-to-r from-[#4f46e5] to-[#7c3aed] bg-clip-text text-transparent',
    titreApres:       '',
    variantePoints:   'securite',
    texte:            "Invitez votre médecin par email à accéder à vos données de santé en mode lecture seule. Les professionnels de santé peuvent visualiser les tendances et les alertes clés sans modifier aucune information.",
    image:            'https://images.pexels.com/photos/4173251/pexels-photo-4173251.jpeg?auto=compress&cs=tinysrgb&w=1200',
    alt:              'Médecin dans son cabinet',
    points: [
      {
        titre:       'Sécurisé & Privé',
        description: 'Vos données restent sous votre contrôle. Les médecins ne peuvent voir que les informations que vous choisissez de partager, et ils ne peuvent pas modifier ou supprimer vos dossiers.',
        couleur:     '#8b5cf6',
        icone:       'bouclier',
      },
    ],
  },
]