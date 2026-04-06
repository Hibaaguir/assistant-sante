// ---------------------------------------------------------------------------
// Helper functions
// ---------------------------------------------------------------------------

/** Parse a date and return null if invalid */
function parseDate(dateString) {
    if (!dateString) return null;
    const date = new Date(dateString);
    return Number.isNaN(date.getTime()) ? null : date;
}

/** Filter and normalize an array */
function toArray(value) {
    return Array.isArray(value) ? value : [];
}

/** Round a number from a raw value */
function toInt(value) {
    return Math.round(Number(value || 0));
}

// ---------------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------------

export function calculateAge(dateString) {
    const date = parseDate(dateString);
    if (!date) return null;
    const now = new Date();
    let age = now.getFullYear() - date.getFullYear();
    const sameDayNotReached =
        now.getMonth() < date.getMonth() ||
        (now.getMonth() === date.getMonth() && now.getDate() < date.getDate());
    if (sameDayNotReached) age -= 1;
    return age;
}

export function formatRelativeTime(dateString) {
    const date = parseDate(dateString);
    if (!date) return "-";
    const diffHours = Math.max(
        0,
        Math.round((Date.now() - date.getTime()) / 3_600_000),
    );
    if (diffHours < 1) return "Less than 1h ago";
    if (diffHours < 24) return `${diffHours}h ago`;
    const diffDays = Math.round(diffHours / 24);
    return diffDays === 1 ? "1 day ago" : `${diffDays} days ago`;
}

export function formatLongDate(dateString) {
    const date = parseDate(dateString);
    return date
        ? date.toLocaleDateString("en-US", {
              day: "numeric",
              month: "long",
              year: "numeric",
          })
        : "-";
}

export function formatShortDate(dateString) {
    const date = parseDate(dateString);
    return date
        ? date
              .toLocaleDateString("en-US", { day: "numeric", month: "short" })
              .replace(".", "")
        : "-";
}

export function formatNumericDate(dateString) {
    const date = parseDate(dateString);
    return date ? date.toLocaleDateString("en-US") : "-";
}

export function capitalize(value) {
    const text = String(value || "").trim();
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : "-";
}

// ---------------------------------------------------------------------------
// Color / status resolvers
// ---------------------------------------------------------------------------

const AVATAR_PALETTE = ["#3d57f4", "#4955f2", "#3558f0"];

export function resolveAvatarColor(status, name) {
    if (status === "critical") return "#f5002d";
    if (status === "watch") return "#ef7b00";
    return AVATAR_PALETTE[String(name || "").length % AVATAR_PALETTE.length];
}

export function resolveDotColor(status) {
    return status === "critical"
        ? "#ff5964"
        : status === "watch"
          ? "#f59d0b"
          : "#08c44e";
}

export function resolvePatientStatus(v = {}) {
    const hr = Number(v.heart_rate || 0);
    const sys = Number(v.systolic_pressure || 0);
    const o2 = Number(v.oxygen_saturation || 0);

    if (hr >= 100 || sys >= 140 || (o2 > 0 && o2 < 93)) return "critical";
    if (hr >= 90 || sys >= 130 || (o2 > 0 && o2 < 95)) return "watch";
    return "stable";
}

// ---------------------------------------------------------------------------
// Tag / label builders
// ---------------------------------------------------------------------------

export function buildListTags(profile = {}) {
    const tags = [
        ...toArray(profile.maladies_chroniques),
        ...toArray(profile.allergies),
    ];
    return tags.length ? tags.slice(0, 3) : ["General checkup"];
}

export function buildDetailTags(profile = {}) {
    const map = (items, cls) =>
        toArray(items).map((label) => ({ label, class: cls }));
    return [
        ...map(
            profile.maladies_chroniques,
            "border-[#b8d1ff] bg-[#eef5ff] text-[#2454ff]",
        ),
        ...map(
            profile.allergies,
            "border-[#f5c2c5] bg-[#fff4f4] text-[#e05842]",
        ),
    ];
}

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

// ---------------------------------------------------------------------------
// Reusable vital formatters
// ---------------------------------------------------------------------------

function formatHeartRate(v) {
    return v?.heart_rate ? `${toInt(v.heart_rate)} bpm` : "--";
}
function formatBloodPressure(v) {
    return v?.systolic_pressure
        ? `${toInt(v.systolic_pressure)}/${toInt(v.diastolic_pressure || 0)}`
        : "--";
}
function formatSaturation(v) {
    return v?.oxygen_saturation ? `${toInt(v.oxygen_saturation)}%` : "--";
}

// ---------------------------------------------------------------------------
// Data mappers – patient list
// ---------------------------------------------------------------------------

export function mapPatient(item = {}) {
    const patient = item.patient || {};
    const profile = item.profile || {};
    const vitals = item.latest_vitals || {};
    const status = resolvePatientStatus(vitals);
    const dateRef =
        vitals.measured_at || item.accepted_at || patient.created_at;

    return {
        id: patient.id,
        invitationId: item.invitation_id,
        name: patient.name || "Patient",
        initials: buildInitials(patient.name),
        email: patient.email || "",
        age: calculateAge(patient.date_of_birth),
        lastSeen: formatRelativeTime(dateRef),
        nextVisit: formatLongDate(item.accepted_at || patient.created_at),
        heartRate: formatHeartRate(vitals),
        bloodPressure: formatBloodPressure(vitals),
        glucose: "",
        status,
        tags: buildListTags(profile),
        avatarColor: resolveAvatarColor(status, patient.name),
        dotColor: resolveDotColor(status),
    };
}

export function mapInvitation(invitation = {}) {
    const patient = invitation.patient || {};
    return {
        id: invitation.id,
        name: patient.name || "Patient",
        email: patient.email || "",
        age: calculateAge(patient.date_of_birth),
        invitedAt: formatLongDate(invitation.created_at),
        tags: buildListTags(invitation.profile),
    };
}

// ---------------------------------------------------------------------------
// Data mappers – patient detail
// ---------------------------------------------------------------------------

export function mapPatientDetail(data = {}, fallbackPatient = {}) {
    const {
        profile = {},
        latest_vitals: vitals = {},
        patient = {},
    } = data;

    return {
        ...fallbackPatient,
        name: patient.name || fallbackPatient.name,
        age: calculateAge(patient.date_of_birth) || fallbackPatient.age,
        gender: capitalize(profile.gender),
        lastSeen: formatRelativeTime(
            vitals.measured_at || patient.updated_at || patient.created_at,
        ),
        detailTags: buildDetailTags(profile),
        observations: toArray(data.observations).map((o) => ({
            date: o.observation_date,
            note: o.note || "",
            updatedAt: o.updated_at || null,
        })),
        vitalsHistory: groupVitalsHistory(toArray(data.vitals)),
        analyses: toArray(data.lab_results).map(mapAnalysis),
        treatments: buildTreatments(
            toArray(data.treatment_medicines),
            toArray(data.treatment_checks),
        ),
        treatmentHistoryRows: buildTreatmentHistoryRows(
            toArray(data.treatment_medicines),
            toArray(data.treatment_checks),
        ),
    };
}

export function groupVitalsHistory(rows) {
    const seen = new Set();
    return toArray(rows)
        .filter((row) => {
            const key = String(row?.measured_at || "").slice(0, 10);
            if (!key || seen.has(key)) return false;
            seen.add(key);
            return true;
        })
        .map((row) => ({
            isoDate: String(row.measured_at || "").slice(0, 10),
            date: formatShortDate(row.measured_at),
            heartRate: formatHeartRate(row),
            bloodPressure: formatBloodPressure(row),
            saturation: formatSaturation(row),
        }));
}

export function mapAnalysis(item = {}) {
    const num = Number(item.value);
    const unit = item.unit ? String(item.unit) : "";
    const value =
        item.value != null ? `${item.value}${unit ? ` ${unit}` : ""}` : "--";

    const status = !Number.isFinite(num)
        ? "Normal"
        : num < 3.9
          ? "Critical"
          : num > 7
            ? "Alert"
            : "Normal";

    const badgeClass =
        status === "Critical"
            ? "bg-[#ffe3e3] text-[#e03535]"
            : status === "Alert"
              ? "bg-[#ffe8b8] text-[#d47b00]"
              : "bg-[#d7f5df] text-[#11a84d]";

    return {
        type: item.analysis_type || "Other",
        result: item.analysis_result || "",
        unit,
        numericValue: Number.isFinite(num) ? num : null,
        name:
            [item.analysis_type, item.analysis_result]
                .filter(Boolean)
                .join(" - ") || "Analysis",
        value,
        range: "Normal range: verify",
        status,
        badgeClass,
        isoDate: String(item.analysis_date || "").slice(0, 10),
        date: formatNumericDate(item.analysis_date),
    };
}

export function buildTreatments(medicines, checks) {
    return toArray(medicines).map((medicine) => {
        const rows = resolveTreatmentChecksForMedicine(checks, medicine.id);
        const taken = rows.filter((r) => r?.taken).length;
        const adherence = rows.length
            ? Math.round((taken / rows.length) * 100)
            : 0;

        return {
            id: medicine.id,
            name: medicine.name || "Treatment",
            dose: `${medicine.dose || "Dose not specified"} - ${medicine.freq || "Not specified"}`,
            when: medicine.note || "As prescribed",
            adherence: `${adherence}%`,
            taken,
            total: rows.length,
            dosesPerDay: Number(medicine.doses_per_day || 1),
            barClass: adherence >= 90 ? "bg-[#0cb342]" : "bg-[#ea7a00]",
        };
    });
}

export function buildTreatmentHistoryRows(medicines, checks) {
    const meds = toArray(medicines);
    const rows = toArray(checks);

    const toDateKey = (val) => String(val || "").slice(0, 10);

    const dateKeys = [
        ...new Set(rows.map((c) => toDateKey(c?.check_date)).filter(Boolean)),
    ].sort((a, b) => b.localeCompare(a));

    return dateKeys
        .map((dateKey) => {
            const dayChecks = rows.filter(
                (c) => toDateKey(c?.check_date) === dateKey,
            );
            let dayTotal = 0,
                dayTaken = 0;

            const dayMeds = meds
                .map((medicine) => {
                    const medRows = resolveTreatmentChecksForMedicine(
                        dayChecks,
                        medicine.id,
                    );
                    const taken = medRows.filter((r) => r?.taken).length;
                    dayTotal += medRows.length;
                    dayTaken += taken;
                    return {
                        id: medicine.id,
                        name: medicine.name || "Treatment",
                        dose: medicine.dose || "Dose not specified",
                        taken,
                        total: medRows.length,
                        progress: medRows.length
                            ? Math.round((taken / medRows.length) * 100)
                            : 0,
                        isComplete:
                            medRows.length > 0 && taken >= medRows.length,
                    };
                })
                .filter((m) => m.total > 0);

            return {
                dateKey,
                meds: dayMeds,
                total: dayTotal,
                taken: dayTaken,
                isComplete: dayTotal > 0 && dayTaken >= dayTotal,
                hasTracked: dayTotal > 0,
            };
        })
        .filter((day) => day.hasTracked);
}

// ---------------------------------------------------------------------------
// Internal helpers
// ---------------------------------------------------------------------------

function resolveTreatmentChecksForMedicine(checks, medicineId) {
    const prefix = `${medicineId}__dose_`;
    return toArray(checks).filter((c) => {
        const key = String(c?.medication_key || "");
        return key === medicineId || key.startsWith(prefix);
    });
}
