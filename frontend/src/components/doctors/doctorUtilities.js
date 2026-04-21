// Convertit une chaîne en objet Date, retourne null si la date est invalide
function parseDate(dateString) {
    if (!dateString) return null;
    const date = new Date(dateString);
    return Number.isNaN(date.getTime()) ? null : date;
}

// Garantit que la valeur est un tableau — retourne [] si ce n'est pas un tableau
function toArray(value) {
    return Array.isArray(value) ? value : [];
}

// Arrondit un nombre brut à l'entier le plus proche (ex: 72.6 → 73)
function toInt(value) {
    return Math.round(Number(value || 0));
}

// Calcule l'âge exact d'une personne à partir de sa date de naissance
export function calculateAge(dateString) {
    const date = parseDate(dateString);
    if (!date) return null;
    const now = new Date();
    let age = now.getFullYear() - date.getFullYear();
    const anniversaireNonPasse =
        now.getMonth() < date.getMonth() ||
        (now.getMonth() === date.getMonth() && now.getDate() < date.getDate());
    if (anniversaireNonPasse) age -= 1;
    return age;
}

// Retourne le temps écoulé depuis une date sous forme lisible (ex: "2h ago", "3 days ago")
export function formatRelativeTime(dateString) {
    const date = parseDate(dateString);
    if (!date) return "-";
    const heures = Math.max(0, Math.round((Date.now() - date.getTime()) / 3_600_000));
    if (heures < 1) return "Less than 1h ago";
    if (heures < 24) return `${heures}h ago`;
    const jours = Math.round(heures / 24);
    return jours === 1 ? "1 day ago" : `${jours} days ago`;
}

// Formate une date en format long lisible (ex: "April 21, 2026")
export function formatLongDate(dateString) {
    const date = parseDate(dateString);
    return date
        ? date.toLocaleDateString("en-US", { day: "numeric", month: "long", year: "numeric" })
        : "-";
}

// Formate une date en format court (ex: "21 Apr")
export function formatShortDate(dateString) {
    const date = parseDate(dateString);
    return date
        ? date.toLocaleDateString("en-US", { day: "numeric", month: "short" }).replace(".", "")
        : "-";
}

// Formate une date en format numérique (ex: "4/21/2026")
export function formatNumericDate(dateString) {
    const date = parseDate(dateString);
    return date ? date.toLocaleDateString("en-US") : "-";
}

// Met la première lettre d'un texte en majuscule (ex: "homme" → "Homme")
export function capitalize(value) {
    const text = String(value || "").trim();
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : "-";
}

// Couleurs et statuts

// Choisit la couleur de l'avatar selon le statut et le nom du patient
export function resolveAvatarColor(status, name) {
    if (status === "critical") return "#f5002d";
    if (status === "watch") return "#ef7b00";
    const palette = ["#3d57f4", "#4955f2", "#3558f0"];
    return palette[String(name || "").length % palette.length];
}

// Retourne la couleur du point de statut (rouge = critique, orange = surveillance, vert = stable)
export function resolveDotColor(status) {
    if (status === "critical") return "#ff5964";
    if (status === "watch") return "#f59d0b";
    return "#08c44e";
}

// Détermine le statut d'un patient selon ses signes vitaux
export function resolvePatientStatus(vitals = {}) {
    const fc  = Number(vitals.heart_rate || 0);
    const sys = Number(vitals.systolic_pressure || 0);
    const o2  = Number(vitals.oxygen_saturation || 0);

    if (fc >= 100 || sys >= 140 || (o2 > 0 && o2 < 93)) return "critical";
    if (fc >= 90  || sys >= 130 || (o2 > 0 && o2 < 95)) return "watch";
    return "stable";
}

// Construction des tags et initiales 

// Construit les tags affichés sur la carte patient dans la liste (max 3)
export function buildListTags(profile = {}) {
    const tags = [
        ...toArray(profile.maladies_chroniques),
        ...toArray(profile.allergies),
    ];
    return tags.length ? tags.slice(0, 3) : ["General checkup"];
}

// Construit les tags colorés affichés dans le détail patient (bleu = maladies, rouge = allergies)
export function buildDetailTags(profile = {}) {
    const tag = (items, cls) => toArray(items).map((label) => ({ label, class: cls }));
    return [
        ...tag(profile.maladies_chroniques, "border-[#b8d1ff] bg-[#eef5ff] text-[#2454ff]"),
        ...tag(profile.allergies,           "border-[#f5c2c5] bg-[#fff4f4] text-[#e05842]"),
    ];
}

// Génère les initiales d'un nom (ex: "Marie Dupont" → "MD")
export function buildInitials(name) {
    return (
        String(name || "")
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((p) => p[0] || "")
            .join("")
            .toUpperCase() || "PT"
    );
}

// Formatage des signes vitaux 

// Formate la fréquence cardiaque (ex: 72 → "72 bpm")
function formatHeartRate(v) {
    return v?.heart_rate ? `${toInt(v.heart_rate)} bpm` : "--";
}

// Formate la tension artérielle (ex: 120 / 80 → "120/80")
function formatBloodPressure(v) {
    return v?.systolic_pressure
        ? `${toInt(v.systolic_pressure)}/${toInt(v.diastolic_pressure || 0)}`
        : "--";
}

// Formate la saturation en oxygène (ex: 98 → "98%")
function formatSaturation(v) {
    return v?.oxygen_saturation ? `${toInt(v.oxygen_saturation)}%` : "--";
}

// Transformation des données backend → format Vue (liste patients)

// Transforme un patient brut reçu du backend en objet prêt pour le template
export function mapPatient(item = {}) {
    const patient = item.patient || {};
    const profile = item.profile || {};
    const vitals  = item.latest_vitals || {};
    const status  = resolvePatientStatus(vitals);
    const dateRef = vitals.measured_at || item.accepted_at || patient.created_at;

    return {
        id:           patient.id,
        invitationId: item.invitation_id,
        name:         patient.name || "Patient",
        initials:     buildInitials(patient.name),
        email:        patient.email || "",
        age:          calculateAge(patient.date_of_birth),
        lastSeen:     formatRelativeTime(dateRef),
        nextVisit:    formatLongDate(item.accepted_at || patient.created_at),
        heartRate:    formatHeartRate(vitals),
        bloodPressure: formatBloodPressure(vitals),
        glucose:      "",
        status,
        tags:         buildListTags(profile),
        avatarColor:  resolveAvatarColor(status, patient.name),
        dotColor:     resolveDotColor(status),
    };
}

// Transforme une invitation brute du backend en objet prêt pour le template
export function mapInvitation(invitation = {}) {
    const patient = invitation.patient || {};
    return {
        id:        invitation.id,
        name:      patient.name || "Patient",
        email:     patient.email || "",
        age:       calculateAge(patient.date_of_birth),
        invitedAt: formatLongDate(invitation.created_at),
        tags:      buildListTags(invitation.profile),
    };
}

// Transformation des données backend → format Vue 

// Transforme toutes les données détaillées d'un patient en objet complet pour le template
export function mapPatientDetail(data = {}, fallbackPatient = {}) {
    const { profile = {}, latest_vitals: vitals = {}, patient = {} } = data;

    return {
        ...fallbackPatient,
        name:       patient.name || fallbackPatient.name,
        age:        calculateAge(patient.date_of_birth) || fallbackPatient.age,
        gender:     capitalize(profile.gender),
        lastSeen:   formatRelativeTime(vitals.measured_at || patient.updated_at || patient.created_at),
        detailTags: buildDetailTags(profile),
        vitalsHistory:           groupVitalsHistory(toArray(data.vitals)),
        analyses:                toArray(data.lab_results).map(mapAnalysis),
        treatments:              buildTreatments(toArray(data.treatment_medicines), toArray(data.treatment_checks)),
        treatmentHistoryRows:    buildTreatmentHistoryRows(toArray(data.treatment_medicines), toArray(data.treatment_checks)),
        healthDataObservations:  toArray(data.health_data)
            .filter((hd) => hd.doctor_observation)
            .map((hd) => ({
                isoDate:     String(hd.date || "").slice(0, 10),
                date:        formatLongDate(hd.date),
                observation: hd.doctor_observation,
            })),
    };
}

// Groupe l'historique des signes vitaux par date (une entrée par jour)
export function groupVitalsHistory(rows) {
    const vues = new Set();
    return toArray(rows)
        .filter((row) => {
            const jour = String(row?.measured_at || "").slice(0, 10);
            if (!jour || vues.has(jour)) return false;
            vues.add(jour);
            return true;
        })
        .map((row) => ({
            id:            row.id,
            isoDate:       String(row.measured_at || "").slice(0, 10),
            date:          formatShortDate(row.measured_at),
            heartRate:     formatHeartRate(row),
            bloodPressure: formatBloodPressure(row),
            saturation:    formatSaturation(row),
        }));
}

// Transforme un résultat d'analyse biologique en objet avec statut et couleur de badge
export function mapAnalysis(item = {}) {
    const num   = Number(item.value);
    const unit  = item.unit ? String(item.unit) : "";
    const value = item.value != null ? `${item.value}${unit ? ` ${unit}` : ""}` : "--";

    const status =
        !Number.isFinite(num) ? "Normal"
        : num < 3.9            ? "Critical"
        : num > 7              ? "Alert"
        :                        "Normal";

    const badgeClass =
        status === "Critical" ? "bg-[#ffe3e3] text-[#e03535]"
        : status === "Alert"  ? "bg-[#ffe8b8] text-[#d47b00]"
        :                       "bg-[#d7f5df] text-[#11a84d]";

    return {
        id:           item.id,
        type:         item.analysis_type || "Other",
        result:       item.result_name || "",
        unit,
        numericValue: Number.isFinite(num) ? num : null,
        name:         [item.analysis_type, item.result_name].filter(Boolean).join(" - ") || "Analysis",
        value,
        range:        "Normal range: verify",
        status,
        badgeClass,
        isoDate:      String(item.analysis_date || "").slice(0, 10),
        date:         formatNumericDate(item.analysis_date),
    };
}

// Construit la liste des traitements avec le taux d'adhérence (% de doses prises)
export function buildTreatments(medicines, checks) {
    return toArray(medicines).map((medicine) => {
        const rows      = resolveTreatmentChecksForMedicine(checks, medicine.id);
        const prises    = rows.filter((r) => r?.taken).length;
        const adherence = rows.length ? Math.round((prises / rows.length) * 100) : 0;

        return {
            id:          medicine.id,
            name:        medicine.name || "Treatment",
            dose:        `${medicine.dose || "Dose not specified"} - ${medicine.freq || "Not specified"}`,
            when:        medicine.note || "As prescribed",
            adherence:   `${adherence}%`,
            taken:       prises,
            total:       rows.length,
            dosesPerDay: Number(medicine.doses_per_day || 1),
            barClass:    adherence >= 90 ? "bg-[#0cb342]" : "bg-[#ea7a00]",
        };
    });
}

// Construit l'historique de prise des traitements jour par jour
export function buildTreatmentHistoryRows(medicines, checks) {
    const meds = toArray(medicines);
    const rows = toArray(checks);
    const toDateKey = (val) => String(val || "").slice(0, 10);

    const joursUniques = [
        ...new Set(rows.map((c) => toDateKey(c?.check_date)).filter(Boolean)),
    ].sort((a, b) => b.localeCompare(a));

    return joursUniques
        .map((jour) => {
            const checksJour = rows.filter((c) => toDateKey(c?.check_date) === jour);
            let totalJour = 0, prisesJour = 0;

            const medsJour = meds
                .map((medicine) => {
                    const medRows = resolveTreatmentChecksForMedicine(checksJour, medicine.id);
                    const prises  = medRows.filter((r) => r?.taken).length;
                    totalJour  += medRows.length;
                    prisesJour += prises;
                    return {
                        id:         medicine.id,
                        name:       medicine.name || "Treatment",
                        dose:       medicine.dose || "Dose not specified",
                        taken:      prises,
                        total:      medRows.length,
                        progress:   medRows.length ? Math.round((prises / medRows.length) * 100) : 0,
                        isComplete: medRows.length > 0 && prises >= medRows.length,
                    };
                })
                .filter((m) => m.total > 0);

            return {
                dateKey:    jour,
                meds:       medsJour,
                total:      totalJour,
                taken:      prisesJour,
                isComplete: totalJour > 0 && prisesJour >= totalJour,
                hasTracked: totalJour > 0,
            };
        })
        .filter((jour) => jour.hasTracked);
}

// Fonction interne : trouve les vérifications de traitement pour un médicament

// Filtre les checks appartenant à un médicament donné (par son ID ou préfixe de clé)
function resolveTreatmentChecksForMedicine(checks, medicineId) {
    const prefix = `${medicineId}__dose_`;
    return toArray(checks).filter((c) => {
        const key = String(c?.medication_key || "");
        return key === medicineId || key.startsWith(prefix);
    });
}
