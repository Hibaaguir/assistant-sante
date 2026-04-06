// ─── Navigation ───────────────────────────────────────────────

export const homeNavigationLinks = [
    { label: "Features", href: "#features" },
    { label: "Solutions", href: "#solutions" },
    { label: "About", href: "#about" },
];

// ─── Features ──────────────────────────────────────────

export const homeFeatures = [
    {
        titre: "Daily Journal",
        description:
            "Track your sleep, stress, energy, nutrition, hydration and physical activity with a structured daily summary.",
        accent: "#0ea5e9",
        icone: "journal",
    },
    {
        titre: "Health Profile",
        description:
            "Monitor your vital signs, biological analyses and keep a complete history of your measurements.",
        accent: "#22c55e",
        icone: "dossier",
    },
    {
        titre: "Treatment Management",
        description:
            "AI-generated treatment calendar with reminders and simple dose confirmation.",
        accent: "#8b5cf6",
        icone: "traitements",
    },
    {
        titre: "AI Analytics",
        description:
            "Continuous analysis to detect anomalies, identify trends and generate personalized recommendations.",
        accent: "#ec4899",
        icone: "analyses",
    },
    {
        titre: "Health Goals Tracking",
        description:
            "Set your health goals and receive concrete advice to achieve them.",
        accent: "#f59e0b",
        icone: "objectifs",
    },
    {
        titre: "Doctor Collaboration",
        description:
            "Invite your doctor to access your data in read-only mode for better follow-up.",
        accent: "#4f46e5",
        icone: "medecin",
    },
];

// ─── Narrative Sections ──────────────────────────────────────

export const homeNarrativeSections = [
    {
        identifiant: "solutions",
        type: "image-gauche",
        titreAvant: "Your health",
        titreAccent: "daily companion",
        titreAccentClass: "text-sky-500",
        titreApres: "",
        variantePoints: "icone",
        texte: "Fill out your structured daily journal covering all aspects of your well-being: sleep quality, stress levels, energy, nutrition (meals, calories, sugar, caffeine, hydration), habits (alcohol, tobacco), and physical activity.",
        image: "https://images.pexels.com/photos/7659564/pexels-photo-7659564.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Person taking notes for daily health tracking",
        points: [
            {
                titre: "Quick daily check-ins",
                description: "Takes only a few minutes each day.",
                couleur: "#0ea5e9",
                icone: "horloge",
            },
            {
                titre: "Complete history",
                description:
                    "All entries are fully editable and tracked over time.",
                couleur: "#4f46e5",
                icone: "fichier",
            },
        ],
    },
    {
        identifiant: "analyses-ia",
        type: "image-droite",
        titreAvant: "Smart health",
        titreAccent: "insights",
        titreAccentClass:
            "bg-gradient-to-r from-[#7c3aed] to-[#ec4899] bg-clip-text text-transparent",
        titreApres: "",
        variantePoints: "badge",
        texte: "Our AI system continuously analyzes your health profile, daily entries and health records to detect anomalies, identify trends and generate personalized recommendations.",
        image: "https://images.pexels.com/photos/590022/pexels-photo-590022.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Medical dashboard with charts",
        points: [
            {
                titre: "Calm, non-alarming alerts",
                description:
                    "Receive gentle notifications about important health patterns.",
                couleur: "#ec4899",
            },
            {
                titre: "Actionable recommendations",
                description:
                    "Get clear and practical advice to improve your lifestyle.",
                couleur: "#8b5cf6",
            },
            {
                titre: "Preventive focus",
                description:
                    "Identify potential issues before they become problems.",
                couleur: "#f97316",
            },
        ],
    },
    {
        identifiant: "about",
        type: "image-gauche",
        titreAvant: "Share with your",
        titreAccent: "health team",
        titreAccentClass:
            "bg-gradient-to-r from-[#4f46e5] to-[#7c3aed] bg-clip-text text-transparent",
        titreApres: "",
        variantePoints: "securite",
        texte: "Invite your doctor by email to access your health data in read-only mode. Healthcare professionals can view trends and key alerts without modifying any information.",
        image: "https://images.pexels.com/photos/4173251/pexels-photo-4173251.jpeg?auto=compress&cs=tinysrgb&w=1200",
        alt: "Doctor in office",
        points: [
            {
                titre: "Secure & Private",
                description:
                    "Your data remains under your control. Doctors can only see information you choose to share, and they cannot modify or delete your records.",
                couleur: "#8b5cf6",
                icone: "bouclier",
            },
        ],
    },
];
