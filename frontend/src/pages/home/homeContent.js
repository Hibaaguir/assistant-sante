// ─── Navigation ───────────────────────────────────────────────

export const homeNavigationLinks = [
    { label: "Fonctionnalités", href: "#features" },
    { label: "Solutions", href: "#solutions" },
    { label: "À propos", href: "#about" },
];

// ─── Features ──────────────────────────────────────────

export const homeFeatures = [
    {
        titre: "Journal quotidien",
        description:
            "Suivez votre sommeil, votre stress, votre énergie, votre nutrition, votre hydratation et votre activité physique avec un résumé quotidien structuré.",
        accent: "#0ea5e9",
        icone: "journal",
    },
    {
        titre: "Profil de santé",
        description:
            "Surveillez vos signes vitaux, vos analyses biologiques et conservez un historique complet de vos mesures.",
        accent: "#22c55e",
        icone: "dossier",
    },
    {
        titre: "Gestion des traitéments",
        description:
            "Calendrier de traitement généré par l'IA avec rappels et confirmations simples de doses.",
        accent: "#8b5cf6",
        icone: "traitements",
    },
    {
        titre: "Analyse IA",
        description:
            "Analyse continue pour détecter les anomalies, identifier les tendances et générer des recommandations personnalisées.",
        accent: "#ec4899",
        icone: "analyses",
    },
    {
        titre: "Suivi des objectifs de santé",
        description:
            "Définissez vos objectifs de santé et recevez des conseils concrets pour les atteindre.",
        accent: "#f59e0b",
        icone: "objectifs",
    },
    {
        titre: "Collaboration avec le médecin",
        description:
            "Invitez votre médecin à accéder à vos données en mode lecture seule pour un meilleur suivi.",
        accent: "#4f46e5",
        icone: "medecin",
    },
];

// ─── Narrative Sections ──────────────────────────────────────

export const homeNarrativeSections = [
    {
        identifiant: "solutions",
        type: "image-gauche",
        titreAvant: "Votre assistant de santé",
        titreAccent: "au quotidien",
        titreAccentClass: "text-sky-500",
        titreApres: "",
        variantePoints: "icone",
        texte: "Remplissez votre journal quotidien structuré couvrant tous les aspects de votre bien-être : qualité du sommeil, niveaux de stress, énergie, nutrition (repas, calories, sucre, caféine, hydratation), habitudes (alcool, tabac) et activité physique.",
        image: "https://images.pexels.com/photos/7659564/pexels-photo-7659564.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Personne prenant des notes pour le suivi quotidien de sa santé",
        points: [
            {
                titre: "Enregistrement rapide quotidien",
                description: "Cela ne prend que quelques minutes par jour.",
                couleur: "#0ea5e9",
                icone: "horloge",
            },
            {
                titre: "Historique complet",
                description:
                    "Toutes les entrées sont entièrement modifiables et suivies dans le temps.",
                couleur: "#4f46e5",
                icone: "fichier",
            },
        ],
    },
    {
        identifiant: "analyses-ia",
        type: "image-droite",
        titreAvant: "Analyses",
        titreAccent: "intelligentes",
        titreAccentClass:
            "bg-gradient-to-r from-[#7c3aed] to-[#ec4899] bg-clip-text text-transparent",
        titreApres: "",
        variantePoints: "badge",
        texte: "Notre système IA analyse continuellement votre profil de santé, vos entrées quotidiennes et vos dossiers de santé pour détecter les anomalies, identifier les tendances et générer des recommandations personnalisées.",
        image: "https://images.pexels.com/photos/590022/pexels-photo-590022.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Tableau de bord médical avec graphiques",
        points: [
            {
                titre: "Alertes calmes et non-alarmantes",
                description:
                    "Recevez des notifications douces sur les schémas de santé importants.",
                couleur: "#ec4899",
            },
            {
                titre: "Recommandations actionables",
                description:
                    "Obtenez des conseils clairs et pratiques pour améliorer votre mode de vie.",
                couleur: "#8b5cf6",
            },
            {
                titre: "Focus préventif",
                description:
                    "Identifiez les problèmes potentiels avant qu'ils ne deviennent des problématiques.",
                couleur: "#f97316",
            },
        ],
    },
    {
        identifiant: "about",
        type: "image-gauche",
        titreAvant: "Partagez avec votre",
        titreAccent: "équipe médicale",
        titreAccentClass:
            "bg-gradient-to-r from-[#4f46e5] to-[#7c3aed] bg-clip-text text-transparent",
        titreApres: "",
        variantePoints: "securite",
        texte: "Invitez votre médecin par email à accéder à vos données de santé en mode lecture seule. Les professionnels de santé peuvent voir les tendances et les alertes clés sans modifier aucune information.",
        image: "https://images.pexels.com/photos/4173251/pexels-photo-4173251.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Médecin au cabinet",
        points: [
            {
                titre: "Sécurisé et confidentiel",
                description:
                    "Vos données restent sous votre contrôle. Les médecins ne peuvent voir que les informations que vous choisissez de partager, et ils ne peuvent pas modifier ou supprimer vos dossiers.",
                couleur: "#8b5cf6",
                icone: "bouclier",
            },
        ],
    },
];
