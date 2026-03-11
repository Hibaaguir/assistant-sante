import { IconeGoutte, IconeCoeur, IconeOnde } from '@/components/doctor/IconesMedecin.js'

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

export function formaterHeureAlerteAbsolue(dateString) {
  if (!dateString) return "Aujourd'hui"
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return "Aujourd'hui"
  return date.toLocaleString('fr-FR', { day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' })
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

export function resoudreStatutPatient(alertsList) {
  if (alertsList.some((alert) => alert.severity === 'critical')) return 'critical'
  if (alertsList.length) return 'watch'
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

export function obtenirDerniereValeurGlucose(alertsSource) {
  const glucoseAlert = (Array.isArray(alertsSource) ? alertsSource : []).find((item) =>
    String(item?.message || '').toLowerCase().includes('glyc')
  )
  if (!glucoseAlert) return ''
  const match = String(glucoseAlert.message).match(/([0-9]+(?:[.,][0-9]+)?\s*[A-Za-z/0-9%]+)/)
  return match ? match[1].replace(',', '.') : ''
}

// ---------------------------------------------------------------------------
// Alert normalizers
// ---------------------------------------------------------------------------

export function normaliserAlertesListe(list, patientName) {
  return (Array.isArray(list) ? list : []).map((alert, index) => {
    const critical = String(alert?.severity || '').toLowerCase() === 'critical'
    return {
      id: `${patientName}-${index + 1}`,
      patient: patientName,
      message: alert?.message || 'Alerte patient.',
      time: formaterHeureAlerteAbsolue(alert?.measured_at),
      isoTime: alert?.measured_at || null,
      rowClass: critical ? 'border-[#ffb9bc] bg-[#fff5f5]' : 'border-[#f2cc4e] bg-[#fffdf3]',
      iconWrapClass: critical ? 'bg-[#ffe5e5]' : 'bg-[#fff1c5]',
      iconClass: critical ? 'text-[#ff2f35]' : 'text-[#ef8a00]',
      severity: critical ? 'critical' : 'warning'
    }
  })
}

export function normaliserAlertesDetail(list) {
  return (Array.isArray(list) ? list : []).map((alert, index) => {
    const isCritical = String(alert?.severity || '').toLowerCase() === 'critical'
    return {
      id: index + 1,
      title: alert?.title || (isCritical ? 'Alerte critique' : 'Alerte'),
      time: formaterHeureAlerteAbsolue(alert?.measured_at),
      message: alert?.message || 'Alerte patient.',
      recommendation: alert?.recommendation || 'Verifier rapidement la situation du patient.',
      containerClass: isCritical ? 'border-[#ff9e9e] bg-[#fff4f4]' : 'border-[#f1c338] bg-[#fffdf0]',
      iconWrapClass: isCritical ? 'bg-[#ffe1e1]' : 'bg-[#fff0bf]',
      iconClass: isCritical ? 'text-[#ff3c3c]' : 'text-[#f0a400]'
    }
  })
}

// ---------------------------------------------------------------------------
// Data mappers – patient list
// ---------------------------------------------------------------------------

export function mapperPatient(item) {
  const patient = item?.patient || {}
  const profile = item?.profile || {}
  const latestVitals = item?.latest_vitals || {}
  const patientAlerts = normaliserAlertesListe(item?.alerts, patient.name)
  const glucose = obtenirDerniereValeurGlucose(item?.alerts)
  const status = resoudreStatutPatient(patientAlerts)

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
    glucose,
    status,
    tags: construireEtiquettesListe(profile),
    avatarColor: resoudreCouleurAvatar(status, patient.name),
    dotColor: resoudreCouleurPoint(status),
    alertCount: patientAlerts.length,
    alertBadgeClass:
      status === 'critical'
        ? 'border-[#f5b1b3] bg-[#fff5f5] text-[#ff2f35]'
        : status === 'watch'
          ? 'border-[#f2cc4e] bg-[#fffdf3] text-[#ef7a00]'
          : '',
    alertLabel: status === 'critical' ? 'Action requise' : '',
    alertLabelClass: 'text-[#ff2f35]',
    alerts: patientAlerts
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
  const vitalsChart = data?.vitals_chart || {}
  const treatments = Array.isArray(data?.treatment_medicines) ? data.treatment_medicines : []
  const treatmentChecks = Array.isArray(data?.treatment_checks) ? data.treatment_checks : []
  const patient = data?.patient || {}

  return {
    ...fallbackPatient,
    name: patient.name || fallbackPatient.name,
    age: calculerAge(patient.date_of_birth) || fallbackPatient.age,
    gender: mettreEnPhrase(profile.sexe || ''),
    lastSeen: formaterTempsRelatif(latestVitals.measured_at || patient.updated_at || patient.created_at),
    detailTags: construireEtiquettesDetail(profile),
    detailAlerts: normaliserAlertesDetail(data?.alerts),
    overviewStats: construireStatistiquesResume(latestVitals, profile),
    vitalsChart: mapperGraphiqueSignesVitaux(vitalsChart),
    vitalsHistory: grouperHistoriqueSignesVitaux(vitals),
    analyses: labs.map(mapperAnalyse),
    treatments: construireTraitements(treatments, treatmentChecks),
    treatmentHistoryRows: construireLignesHistoriqueTraitements(treatments, treatmentChecks)
  }
}

export function construireStatistiquesResume(latestVitals, profile) {
  const heartRate = Number(latestVitals?.heart_rate || 0)
  const systolic = Number(latestVitals?.systolic_pressure || 0)
  const oxygen = Number(latestVitals?.oxygen_saturation || 0)

  return [
    {
      label: 'Rythme cardiaque',
      value: heartRate ? `${Math.round(heartRate)} bpm` : '--',
      badge: heartRate >= 90 ? 'Legerement eleve' : 'Normal',
      badgeClass: heartRate >= 90 ? 'bg-[#ffe6b8] text-[#d47b00]' : 'bg-[#d7f5df] text-[#11a84d]',
      icon: IconeCoeur,
      iconWrapClass: 'bg-[#ffe3e8]',
      iconClass: 'text-[#ff2143]',
      cardClass: 'border-[#f4bac3] bg-[#fff7f8]'
    },
    {
      label: 'Tension',
      value: systolic
        ? `${Math.round(systolic)}/${Math.round(Number(latestVitals?.diastolic_pressure || 0))}`
        : '--',
      badge: systolic >= 135 ? 'Elevee' : 'Normal',
      badgeClass: systolic >= 135 ? 'bg-[#ffe6b8] text-[#d47b00]' : 'bg-[#d7f5df] text-[#11a84d]',
      icon: IconeOnde,
      iconWrapClass: 'bg-[#d8e9ff]',
      iconClass: 'text-[#2454ff]',
      cardClass: 'border-[#aed0ff] bg-[#f4fbff]'
    },
    {
      label: 'Saturation O2',
      value: oxygen ? `${Math.round(oxygen)} %` : '--',
      badge: oxygen >= 95 ? 'Normal' : 'Basse',
      badgeClass: oxygen >= 95 ? 'bg-[#d7f5df] text-[#11a84d]' : 'bg-[#ffe6b8] text-[#d47b00]',
      icon: IconeGoutte,
      iconWrapClass: 'bg-[#efe1ff]',
      iconClass: 'text-[#8c30ff]',
      cardClass: 'border-[#dbc1ff] bg-[#fbf7ff]'
    }
  ]
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

export function mapperGraphiqueSignesVitaux(chartData) {
  const labelSource = Array.isArray(chartData?.labels) ? chartData.labels : []

  return {
    labels: labelSource.map((label) => formaterDateCourte(label)),
    heartRate: propagerSerie(conserverSerieNumerique(chartData?.heart_rate, labelSource.length)),
    systolicPressure: propagerSerie(conserverSerieNumerique(chartData?.systolic_pressure, labelSource.length)),
    diastolicPressure: propagerSerie(conserverSerieNumerique(chartData?.diastolic_pressure, labelSource.length)),
    oxygenSaturation: propagerSerie(conserverSerieNumerique(chartData?.oxygen_saturation, labelSource.length)),
  }
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

function conserverSerieNumerique(values, expectedLength = 0) {
  const source = Array.isArray(values) ? values : []
  const size = Math.max(expectedLength, source.length)

  return Array.from({ length: size }, (_, index) => {
    const value = source[index]
    if (value === null || value === undefined || value === '') return null

    const numeric = Number(value)
    return Number.isFinite(numeric) ? numeric : null
  })
}

function propagerSerie(values) {
  let previousValue = null

  return (Array.isArray(values) ? values : []).map((value) => {
    if (Number.isFinite(value)) {
      previousValue = value
      return value
    }

    return previousValue
  })
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
