export const liensNavigationAccueil = [
    { label: "Fonctionnalites", href: "#fonctionnalites" },
    { label: "Solutions", href: "#solutions" },
    { label: "A propos", href: "#a-propos" },
];

export const fonctionnalitesAccueil = [
    {
        titre: "Journal Quotidien",
        description:
            "Suivez votre sommeil, stress, energie, nutrition, hydratation et activite physique avec un bilan quotidien structure",
        accent: "#0ea5e9",
        icone: "journal",
    },
    {
        titre: "Dossier Sante",
        description:
            "Surveillez vos signes vitaux, analyses biologiques et conservez un historique complet de vos mesures.",
        accent: "#22c55e",
        icone: "dossier",
    },
    {
        titre: "Gestion des Traitements",
        description:
            "Calendrier de traitements genere par IA avec rappels et confirmation simple des doses",
        accent: "#8b5cf6",
        icone: "traitements",
    },
    {
        titre: "Analyses IA",
        description:
            "Analyse continue pour detecter les anomalies, identifier les tendances et generer des recommandations personnalisees",
        accent: "#ec4899",
        icone: "analyses",
    },
    {
        titre: "Suivi d'Objectifs",
        description:
            "Definissez vos objectifs de sante et recevez des conseils concrets pour les atteindre",
        accent: "#f59e0b",
        icone: "objectifs",
    },
];

export const sectionsNarrativesAccueil = [
    {
        identifiant: "solutions",
        type: "image-gauche",
        titre: "Votre compagnon de sante quotidien",
        texte: "Remplissez votre journal quotidien structure couvrant tous les aspects de votre bien-etre : qualite du sommeil, niveaux de stress, energie, nutrition (repas, calories, sucre, cafeine, hydratation), habitudes (alcool, tabac), et activite physique.",
        image: "https://images.pexels.com/photos/7659564/pexels-photo-7659564.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Personne prenant des notes pour son suivi quotidien de sante",
        points: [
            {
                titre: "Controles quotidiens rapides",
                description: "Prend seulement quelques minutes chaque jour",
                couleur: "#0ea5e9",
                icone: "horloge",
            },
            {
                titre: "Historique complet",
                description:
                    "Toutes les entrees sont pleinement modifiables et suivies au fil du temps",
                couleur: "#4f46e5",
                icone: "fichier",
            },
        ],
    },
    {
        identifiant: "analyses-ia",
        type: "image-droite",
        titre: "Insights de sante intelligents",
        texte: "Notre systeme IA analyse continuellement votre profil de sante, vos entrees quotidiennes et vos dossiers de sante pour detecter les anomalies, identifier les tendances et generer des recommandations personnalisees.",
        image: "https://images.pexels.com/photos/590022/pexels-photo-590022.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Tableau de bord medical avec graphiques",
        points: [
            {
                titre: "Alertes calmes, non alarmantes",
                description:
                    "Recevez des notifications douces sur des modeles de sante importants",
                couleur: "#ec4899",
            },
            {
                titre: "Recommandations actionnables",
                description:
                    "Obtenez des conseils clairs et pratiques pour ameliorer votre mode de vie",
                couleur: "#8b5cf6",
            },
            {
                titre: "Focus preventif",
                description:
                    "Identifiez les problemes potentiels avant qu'ils ne deviennent des problemes",
                couleur: "#f97316",
            },
        ],
    },
    {
        identifiant: "a-propos",
        type: "image-gauche",
        titre: "Votre espace de sante prive",
        texte: "Tous vos donnees de sante sont stockees de facon securisee et privee. Vous avez un controle total sur vos informations et pouvez les consulter a tout moment depuis un seul endroit.",
        image: "https://images.pexels.com/photos/4173251/pexels-photo-4173251.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Professionnel regardant les donnees de sante",
        points: [
            {
                titre: "Securise & Prive",
                description:
                    "Vos donnees restent entierement sous votre controle. Vous decidez qui a acces a vos informations et vous pouvez les consulter a tout moment de maniere confidentielle.",
                couleur: "#8b5cf6",
                icone: "bouclier",
            },
        ],
    },
];
