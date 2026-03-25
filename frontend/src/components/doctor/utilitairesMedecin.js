// ---------------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------------

export function calculerAge(dateString) {
  if (!dateString) return null
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return null
  const now = new Date()
  let age = now.getFullYear() - date.getFullYear()
  const monthDiff = now.getMonth() - date.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < date.getDate())) age -= 1
  return age
}

export function formaterTempsRelatif(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  const diffMs = Date.now() - date.getTime()
  const diffHours = Math.max(0, Math.round(diffMs / 3600000))
  if (diffHours < 1) return 'Il y a moins de 1h'
  if (diffHours < 24) return `Il y a ${diffHours}h`
  const diffDays = Math.round(diffHours / 24)
  return diffDays === 1 ? 'Il y a 1 jour' : `Il y a ${diffDays} jours`
}

export function formaterDateLongue(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

export function formaterDateCourte(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }).replace('.', '')
}

export function formaterDateNumerique(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR')
}

export function mettreEnPhrase(value) {
  const text = String(value || '').trim()
  if (!text) return '-'
  return text.charAt(0).toUpperCase() + text.slice(1)
}

// ---------------------------------------------------------------------------
// Color / status resolvers
// ---------------------------------------------------------------------------

export function resoudreCouleurAvatar(status, name) {
  if (status === 'critical') return '#f5002d'
  if (status === 'watch') return '#ef7b00'
  const palette = ['#3d57f4', '#4955f2', '#3558f0']
  const index = String(name || '').length % palette.length
  return palette[index]
}

export function resoudreCouleurPoint(status) {
  if (status === 'critical') return '#ff5964'
  if (status === 'watch') return '#f59d0b'
  return '#08c44e'
}

export function resoudreStatutPatient(latestVitals) {
  const heartRate = Number(latestVitals?.heart_rate || 0)
  const systolic = Number(latestVitals?.systolic_pressure || 0)
  const oxygen = Number(latestVitals?.oxygen_saturation || 0)

  const critical = heartRate >= 100 || systolic >= 140 || (oxygen > 0 && oxygen < 93)
  if (critical) return 'critical'

  const watch = heartRate >= 90 || systolic >= 130 || (oxygen > 0 && oxygen < 95)
  if (watch) return 'watch'

  return 'stable'
}

// ---------------------------------------------------------------------------
// Tag / label builders
// ---------------------------------------------------------------------------

export function construireEtiquettesListe(profile) {
  const tags = [
    ...(Array.isArray(profile?.maladies_chroniques) ? profile.maladies_chroniques : []),
    ...(Array.isArray(profile?.allergies) ? profile.allergies : [])
  ]
  return tags.length ? tags.slice(0, 3) : ['Suivi general']
}

export function construireEtiquettesDetail(profile) {
  const diseaseTags = (Array.isArray(profile?.maladies_chroniques) ? profile.maladies_chroniques : []).map((label) => ({
    label,
    class: 'border-[#b8d1ff] bg-[#eef5ff] text-[#2454ff]'
  }))
  const allergyTags = (Array.isArray(profile?.allergies) ? profile.allergies : []).map((label) => ({
    label,
    class: 'border-[#f5c2c5] bg-[#fff4f4] text-[#e05842]'
  }))
  return [...diseaseTags, ...allergyTags]
}

export function construireInitiales(name) {
  return (
    String(name || '')
      .trim()
      .split(/\s+/)
      .slice(0, 2)
      .map((part) => part[0] || '')
      .join('')
      .toUpperCase() || 'PT'
  )
}

// ---------------------------------------------------------------------------
// Data mappers – patient list
// ---------------------------------------------------------------------------

export function mapperPatient(item) {
  const patient = item?.patient || {}
  const profile = item?.profile || {}
  const latestVitals = item?.latest_vitals || {}
  const status = resoudreStatutPatient(latestVitals)

  return {
    id: patient.id,
    invitationId: item?.invitation_id,
    name: patient.name || 'Patient',
    initials: construireInitiales(patient.name),
    email: patient.email || '',
    age: calculerAge(patient.date_of_birth),
    lastSeen: formaterTempsRelatif(latestVitals.measured_at || item?.accepted_at || patient.created_at),
    nextVisit: formaterDateLongue(item?.accepted_at || patient.created_at),
    heartRate: latestVitals?.heart_rate ? `${Math.round(Number(latestVitals.heart_rate))} bpm` : '--',
    bloodPressure: latestVitals?.systolic_pressure
      ? `${Math.round(Number(latestVitals.systolic_pressure))}/${Math.round(Number(latestVitals.diastolic_pressure || 0))}`
      : '--',
    glucose: '',
    status,
    tags: construireEtiquettesListe(profile),
    avatarColor: resoudreCouleurAvatar(status, patient.name),
    dotColor: resoudreCouleurPoint(status)
  }
}

export function mapperInvitation(invitation) {
  const patient = invitation?.patient || {}
  const profile = invitation?.profile || {}

  return {
    id: invitation?.id,
    name: patient.name || 'Patient',
    email: patient.email || '',
    age: calculerAge(patient.date_of_birth),
    invitedAt: formaterDateLongue(invitation?.created_at),
    tags: construireEtiquettesListe(profile)
  }
}

// ---------------------------------------------------------------------------
// Data mappers – patient detail
// ---------------------------------------------------------------------------

export function mapperDetailPatient(data, fallbackPatient) {
  const profile = data?.profile || {}
  const latestVitals = data?.latest_vitals || {}
  const labs = Array.isArray(data?.lab_results) ? data.lab_results : []
  const vitals = Array.isArray(data?.vitals) ? data.vitals : []
  const treatments = Array.isArray(data?.treatment_medicines) ? data.treatment_medicines : []
  const treatmentChecks = Array.isArray(data?.treatment_checks) ? data.treatment_checks : []
  const patient = data?.patient || {}
  const generalObservation = data?.general_observation || {}

  return {
    ...fallbackPatient,
    name: patient.name || fallbackPatient.name,
    age: calculerAge(patient.date_of_birth) || fallbackPatient.age,
    gender: mettreEnPhrase(profile.sexe || ''),
    lastSeen: formaterTempsRelatif(latestVitals.measured_at || patient.updated_at || patient.created_at),
    detailTags: construireEtiquettesDetail(profile),
    generalObservation: {
      text: String(generalObservation?.text || ''),
      updatedAt: generalObservation?.updated_at || null,
    },
    vitalsHistory: grouperHistoriqueSignesVitaux(vitals),
    analyses: labs.map(mapperAnalyse),
    treatments: construireTraitements(treatments, treatmentChecks),
    treatmentHistoryRows: construireLignesHistoriqueTraitements(treatments, treatmentChecks)
  }
}

export function grouperHistoriqueSignesVitaux(rows) {
  const seen = new Set()
  return (Array.isArray(rows) ? rows : [])
    .filter((row) => {
      const key = String(row?.measured_at || '').slice(0, 10)
      if (!key || seen.has(key)) return false
      seen.add(key)
      return true
    })
    .map((row) => ({
      isoDate: String(row?.measured_at || '').slice(0, 10),
      date: formaterDateCourte(row?.measured_at),
      heartRate: row?.heart_rate ? `${Math.round(Number(row.heart_rate))} bpm` : '--',
      bloodPressure: row?.systolic_pressure
        ? `${Math.round(Number(row.systolic_pressure))}/${Math.round(Number(row.diastolic_pressure || 0))}`
        : '--',
      saturation: row?.oxygen_saturation ? `${Math.round(Number(row.oxygen_saturation))}%` : '--'
    }))
}

export function mapperAnalyse(item) {
  const numericValue = Number(item?.value)
  const unit = item?.unit ? String(item.unit) : ''
  const value =
    item?.value !== null && item?.value !== undefined
      ? `${item.value}${unit ? ` ${unit}` : ''}`
      : '--'
  const status = Number.isFinite(numericValue)
    ? numericValue < 3.9 ? 'Critique' : numericValue > 7 ? 'Attention' : 'Normal'
    : 'Normal'
  const badgeClass =
    status === 'Critique'
      ? 'bg-[#ffe3e3] text-[#e03535]'
      : status === 'Attention'
        ? 'bg-[#ffe8b8] text-[#d47b00]'
        : 'bg-[#d7f5df] text-[#11a84d]'

  return {
    type: item?.analysis_type || 'Autre',
    result: item?.analysis_result || '',
    unit,
    numericValue: Number.isFinite(numericValue) ? numericValue : null,
    name: [item?.analysis_type, item?.analysis_result].filter(Boolean).join(' - ') || 'Analyse',
    value,
    range: 'Plage normale : a verifier',
    status,
    badgeClass,
    isoDate: String(item?.analysis_date || '').slice(0, 10),
    date: formaterDateNumerique(item?.analysis_date)
  }
}

export function construireTraitements(medicines, checks) {
  return (Array.isArray(medicines) ? medicines : []).map((medicine) => {
    const rows = resolveTreatmentChecksForMedicine(checks, medicine.id)
    const total = rows.length
    const taken = rows.filter((row) => row?.taken).length
    const adherenceValue = total > 0 ? Math.round((taken / total) * 100) : 0

    return {
      id: medicine.id,
      name: medicine.name || 'Traitement',
      dose: `${medicine.dose || 'Dose non precisee'} - ${medicine.freq || 'Non precise'}`,
      when: medicine.note || 'Selon prescription',
      adherence: `${adherenceValue}%`,
      taken,
      total,
      dosesPerDay: Number(medicine.doses_per_day || 1),
      barClass: adherenceValue >= 90 ? 'bg-[#0cb342]' : 'bg-[#ea7a00]'
    }
  })
}

export function construireLignesHistoriqueTraitements(medicines, checks) {
  const meds = Array.isArray(medicines) ? medicines : []
  const rows = Array.isArray(checks) ? checks : []
  const daysMap = new Map()

  rows.forEach((check) => {
    const dateKey = String(check?.check_date || '')
    if (!dateKey) return

    if (!daysMap.has(dateKey)) {
      daysMap.set(dateKey, {
        dateKey,
        meds: [],
        total: 0,
        taken: 0,
      })
    }
  })

  const sortedDays = [...daysMap.keys()].sort((a, b) => (a < b ? 1 : -1))

  return sortedDays.map((dateKey) => {
    let dayTotal = 0
    let dayTaken = 0

    const dayMeds = meds.map((medicine) => {
      const medicineRows = resolveTreatmentChecksForMedicine(
        rows.filter((check) => String(check?.check_date || '') === dateKey),
        medicine.id
      )
      const total = medicineRows.length
      const taken = medicineRows.filter((row) => row?.taken).length

      dayTotal += total
      dayTaken += taken

      return {
        id: medicine.id,
        name: medicine.name || 'Traitement',
        dose: medicine.dose || 'Dose non precisee',
        taken,
        total,
        progress: total > 0 ? Math.round((taken / total) * 100) : 0,
        isComplete: total > 0 && taken >= total,
      }
    }).filter((medicine) => medicine.total > 0)

    return {
      dateKey,
      meds: dayMeds,
      total: dayTotal,
      taken: dayTaken,
      isComplete: dayTotal > 0 && dayTaken >= dayTotal,
      hasTracked: dayTotal > 0,
    }
  }).filter((day) => day.hasTracked)
}

function resolveTreatmentChecksForMedicine(checks, medicineId) {
  const prefix = `${medicineId}__dose_`
  return (Array.isArray(checks) ? checks : []).filter((check) => {
    const key = String(check?.medication_key || '')
    return key === medicineId || key.startsWith(prefix)
  })
}
