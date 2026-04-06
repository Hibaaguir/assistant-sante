// ---------------------------------------------------------------------------
// Helpers internes
// ---------------------------------------------------------------------------

/** Parse une date et retourne null si invalide */
function parseDate(dateString) {
  if (!dateString) return null
  const date = new Date(dateString)
  return Number.isNaN(date.getTime()) ? null : date
}

/** Filtre et normalise un tableau */
function toArray(value) {
  return Array.isArray(value) ? value : []
}

/** Arrondit un nombre issu d'une valeur brute */
function toInt(value) {
  return Math.round(Number(value || 0))
}

// ---------------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------------

export function calculerAge(dateString) {
  const date = parseDate(dateString)
  if (!date) return null
  const now = new Date()
  let age = now.getFullYear() - date.getFullYear()
  const sameDayNotReached = now.getMonth() < date.getMonth() ||
    (now.getMonth() === date.getMonth() && now.getDate() < date.getDate())
  if (sameDayNotReached) age -= 1
  return age
}

export function formaterTempsRelatif(dateString) {
  const date = parseDate(dateString)
  if (!date) return '-'
  const diffHours = Math.max(0, Math.round((Date.now() - date.getTime()) / 3_600_000))
  if (diffHours < 1)  return 'Il y a moins de 1h'
  if (diffHours < 24) return `Il y a ${diffHours}h`
  const diffDays = Math.round(diffHours / 24)
  return diffDays === 1 ? 'Il y a 1 jour' : `Il y a ${diffDays} jours`
}

export function formaterDateLongue(dateString) {
  const date = parseDate(dateString)
  return date ? date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) : '-'
}

export function formaterDateCourte(dateString) {
  const date = parseDate(dateString)
  return date ? date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }).replace('.', '') : '-'
}

export function formaterDateNumerique(dateString) {
  const date = parseDate(dateString)
  return date ? date.toLocaleDateString('fr-FR') : '-'
}

export function mettreEnPhrase(value) {
  const text = String(value || '').trim()
  return text ? text.charAt(0).toUpperCase() + text.slice(1) : '-'
}

// ---------------------------------------------------------------------------
// Color / status resolvers
// ---------------------------------------------------------------------------

const AVATAR_PALETTE = ['#3d57f4', '#4955f2', '#3558f0']

export function resoudreCouleurAvatar(status, name) {
  if (status === 'critical') return '#f5002d'
  if (status === 'watch')    return '#ef7b00'
  return AVATAR_PALETTE[String(name || '').length % AVATAR_PALETTE.length]
}

export function resoudreCouleurPoint(status) {
  return status === 'critical' ? '#ff5964' : status === 'watch' ? '#f59d0b' : '#08c44e'
}

export function resoudreStatutPatient(v = {}) {
  const hr  = Number(v.heart_rate || 0)
  const sys = Number(v.systolic_pressure || 0)
  const o2  = Number(v.oxygen_saturation || 0)

  if (hr >= 100 || sys >= 140 || (o2 > 0 && o2 < 93)) return 'critical'
  if (hr >= 90  || sys >= 130 || (o2 > 0 && o2 < 95)) return 'watch'
  return 'stable'
}

// ---------------------------------------------------------------------------
// Tag / label builders
// ---------------------------------------------------------------------------

export function construireEtiquettesListe(profile = {}) {
  const tags = [...toArray(profile.maladies_chroniques), ...toArray(profile.allergies)]
  return tags.length ? tags.slice(0, 3) : ['Suivi general']
}

export function construireEtiquettesDetail(profile = {}) {
  const map = (items, cls) => toArray(items).map(label => ({ label, class: cls }))
  return [
    ...map(profile.maladies_chroniques, 'border-[#b8d1ff] bg-[#eef5ff] text-[#2454ff]'),
    ...map(profile.allergies,           'border-[#f5c2c5] bg-[#fff4f4] text-[#e05842]')
  ]
}

export function construireInitiales(name) {
  return String(name || '').trim().split(/\s+/).slice(0, 2).map(p => p[0] || '').join('').toUpperCase() || 'PT'
}

// ---------------------------------------------------------------------------
// Formatters vitaux réutilisables
// ---------------------------------------------------------------------------

function formatHeartRate(v)      { return v?.heart_rate        ? `${toInt(v.heart_rate)} bpm`                                                 : '--' }
function formatBloodPressure(v)  { return v?.systolic_pressure ? `${toInt(v.systolic_pressure)}/${toInt(v.diastolic_pressure || 0)}`           : '--' }
function formatSaturation(v)     { return v?.oxygen_saturation ? `${toInt(v.oxygen_saturation)}%`                                              : '--' }

// ---------------------------------------------------------------------------
// Data mappers – patient list
// ---------------------------------------------------------------------------

export function mapperPatient(item = {}) {
  const patient     = item.patient      || {}
  const profile     = item.profile      || {}
  const vitals      = item.latest_vitals || {}
  const status      = resoudreStatutPatient(vitals)
  const dateRef     = vitals.measured_at || item.accepted_at || patient.created_at

  return {
    id:            patient.id,
    invitationId:  item.invitation_id,
    name:          patient.name || 'Patient',
    initials:      construireInitiales(patient.name),
    email:         patient.email || '',
    age:           calculerAge(patient.date_of_birth),
    lastSeen:      formaterTempsRelatif(dateRef),
    nextVisit:     formaterDateLongue(item.accepted_at || patient.created_at),
    heartRate:     formatHeartRate(vitals),
    bloodPressure: formatBloodPressure(vitals),
    glucose:       '',
    status,
    tags:          construireEtiquettesListe(profile),
    avatarColor:   resoudreCouleurAvatar(status, patient.name),
    dotColor:      resoudreCouleurPoint(status)
  }
}

export function mapperInvitation(invitation = {}) {
  const patient = invitation.patient || {}
  return {
    id:        invitation.id,
    name:      patient.name || 'Patient',
    email:     patient.email || '',
    age:       calculerAge(patient.date_of_birth),
    invitedAt: formaterDateLongue(invitation.created_at),
    tags:      construireEtiquettesListe(invitation.profile)
  }
}

// ---------------------------------------------------------------------------
// Data mappers – patient detail
// ---------------------------------------------------------------------------

export function mapperDetailPatient(data = {}, fallbackPatient = {}) {
  const { profile = {}, latest_vitals: vitals = {}, patient = {}, general_observation: obs = {} } = data

  return {
    ...fallbackPatient,
    name:               patient.name || fallbackPatient.name,
    age:                calculerAge(patient.date_of_birth) || fallbackPatient.age,
    gender:             mettreEnPhrase(profile.sexe),
    lastSeen:           formaterTempsRelatif(vitals.measured_at || patient.updated_at || patient.created_at),
    detailTags:         construireEtiquettesDetail(profile),
    generalObservation: { text: String(obs.text || ''), updatedAt: obs.updated_at || null },
    vitalsHistory:      grouperHistoriqueSignesVitaux(toArray(data.vitals)),
    analyses:           toArray(data.lab_results).map(mapperAnalyse),
    treatments:         construireTraitements(toArray(data.treatment_medicines), toArray(data.treatment_checks)),
    treatmentHistoryRows: construireLignesHistoriqueTraitements(toArray(data.treatment_medicines), toArray(data.treatment_checks))
  }
}

export function grouperHistoriqueSignesVitaux(rows) {
  const seen = new Set()
  return toArray(rows)
    .filter(row => {
      const key = String(row?.measured_at || '').slice(0, 10)
      if (!key || seen.has(key)) return false
      seen.add(key)
      return true
    })
    .map(row => ({
      isoDate:       String(row.measured_at || '').slice(0, 10),
      date:          formaterDateCourte(row.measured_at),
      heartRate:     formatHeartRate(row),
      bloodPressure: formatBloodPressure(row),
      saturation:    formatSaturation(row)
    }))
}

export function mapperAnalyse(item = {}) {
  const num   = Number(item.value)
  const unit  = item.unit ? String(item.unit) : ''
  const value = item.value != null ? `${item.value}${unit ? ` ${unit}` : ''}` : '--'

  const status = !Number.isFinite(num) ? 'Normal'
    : num < 3.9 ? 'Critique'
    : num > 7   ? 'Attention'
    : 'Normal'

  const badgeClass = status === 'Critique' ? 'bg-[#ffe3e3] text-[#e03535]'
    : status === 'Attention'               ? 'bg-[#ffe8b8] text-[#d47b00]'
    :                                        'bg-[#d7f5df] text-[#11a84d]'

  return {
    type:         item.analysis_type  || 'Autre',
    result:       item.analysis_result || '',
    unit,
    numericValue: Number.isFinite(num) ? num : null,
    name:         [item.analysis_type, item.analysis_result].filter(Boolean).join(' - ') || 'Analyse',
    value,
    range:        'Plage normale : a verifier',
    status,
    badgeClass,
    isoDate:      String(item.analysis_date || '').slice(0, 10),
    date:         formaterDateNumerique(item.analysis_date)
  }
}

export function construireTraitements(medicines, checks) {
  return toArray(medicines).map(medicine => {
    const rows       = resolveTreatmentChecksForMedicine(checks, medicine.id)
    const taken      = rows.filter(r => r?.taken).length
    const adherence  = rows.length ? Math.round((taken / rows.length) * 100) : 0

    return {
      id:          medicine.id,
      name:        medicine.name  || 'Traitement',
      dose:        `${medicine.dose || 'Dose non precisee'} - ${medicine.freq || 'Non precise'}`,
      when:        medicine.note  || 'Selon prescription',
      adherence:   `${adherence}%`,
      taken,
      total:       rows.length,
      dosesPerDay: Number(medicine.doses_per_day || 1),
      barClass:    adherence >= 90 ? 'bg-[#0cb342]' : 'bg-[#ea7a00]'
    }
  })
}

export function construireLignesHistoriqueTraitements(medicines, checks) {
  const meds = toArray(medicines)
  const rows = toArray(checks)

  const dateKeys = [...new Set(rows.map(c => String(c?.check_date || '')).filter(Boolean))].sort((a, b) => b.localeCompare(a))

  return dateKeys.map(dateKey => {
    const dayChecks = rows.filter(c => String(c?.check_date || '') === dateKey)
    let dayTotal = 0, dayTaken = 0

    const dayMeds = meds.map(medicine => {
      const medRows = resolveTreatmentChecksForMedicine(dayChecks, medicine.id)
      const taken   = medRows.filter(r => r?.taken).length
      dayTotal += medRows.length
      dayTaken += taken
      return {
        id:         medicine.id,
        name:       medicine.name || 'Traitement',
        dose:       medicine.dose || 'Dose non precisee',
        taken,
        total:      medRows.length,
        progress:   medRows.length ? Math.round((taken / medRows.length) * 100) : 0,
        isComplete: medRows.length > 0 && taken >= medRows.length
      }
    }).filter(m => m.total > 0)

    return { dateKey, meds: dayMeds, total: dayTotal, taken: dayTaken, isComplete: dayTotal > 0 && dayTaken >= dayTotal, hasTracked: dayTotal > 0 }
  }).filter(day => day.hasTracked)
}

// ---------------------------------------------------------------------------
// Helpers internes
// ---------------------------------------------------------------------------

function resolveTreatmentChecksForMedicine(checks, medicineId) {
  const prefix = `${medicineId}__dose_`
  return toArray(checks).filter(c => {
    const key = String(c?.medication_key || '')
    return key === medicineId || key.startsWith(prefix)
  })
}