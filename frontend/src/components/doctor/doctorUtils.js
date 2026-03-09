import { DropIcon, HeartIcon, TrendUpIcon, WaveIcon } from '@/components/doctor/DoctorIcons.js'

// ---------------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------------

export function computeAge(dateString) {
  if (!dateString) return null
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return null
  const now = new Date()
  let age = now.getFullYear() - date.getFullYear()
  const monthDiff = now.getMonth() - date.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < date.getDate())) age -= 1
  return age
}

export function formatRelativeTime(dateString) {
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

export function formatDateLong(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

export function formatDateShort(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }).replace('.', '')
}

export function formatDateNumeric(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('fr-FR')
}

export function formatAbsoluteAlertTime(dateString) {
  if (!dateString) return "Aujourd'hui"
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return "Aujourd'hui"
  return date.toLocaleString('fr-FR', { day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' })
}

export function toSentenceCase(value) {
  const text = String(value || '').trim()
  if (!text) return '-'
  return text.charAt(0).toUpperCase() + text.slice(1)
}

// ---------------------------------------------------------------------------
// Color / status resolvers
// ---------------------------------------------------------------------------

export function resolveAvatarColor(status, name) {
  if (status === 'critical') return '#f5002d'
  if (status === 'watch') return '#ef7b00'
  const palette = ['#3d57f4', '#4955f2', '#3558f0']
  const index = String(name || '').length % palette.length
  return palette[index]
}

export function resolveDotColor(status) {
  if (status === 'critical') return '#ff5964'
  if (status === 'watch') return '#f59d0b'
  return '#08c44e'
}

export function resolvePatientStatus(alertsList) {
  if (alertsList.some((alert) => alert.severity === 'critical')) return 'critical'
  if (alertsList.length) return 'watch'
  return 'stable'
}

// ---------------------------------------------------------------------------
// Tag / label builders
// ---------------------------------------------------------------------------

export function buildListTags(profile) {
  const tags = [
    ...(Array.isArray(profile?.maladies_chroniques) ? profile.maladies_chroniques : []),
    ...(Array.isArray(profile?.allergies) ? profile.allergies : [])
  ]
  return tags.length ? tags.slice(0, 3) : ['Suivi general']
}

export function buildDetailTags(profile) {
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

export function buildInitials(name) {
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

export function getLatestGlucoseValue(alertsSource) {
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

export function normalizeListAlerts(list, patientName) {
  return (Array.isArray(list) ? list : []).map((alert, index) => {
    const critical = String(alert?.severity || '').toLowerCase() === 'critical'
    return {
      id: `${patientName}-${index + 1}`,
      patient: patientName,
      message: alert?.message || 'Alerte patient.',
      time: formatAbsoluteAlertTime(alert?.measured_at),
      isoTime: alert?.measured_at || null,
      rowClass: critical ? 'border-[#ffb9bc] bg-[#fff5f5]' : 'border-[#f2cc4e] bg-[#fffdf3]',
      iconWrapClass: critical ? 'bg-[#ffe5e5]' : 'bg-[#fff1c5]',
      iconClass: critical ? 'text-[#ff2f35]' : 'text-[#ef8a00]',
      severity: critical ? 'critical' : 'warning'
    }
  })
}

export function normalizeDetailAlerts(list) {
  return (Array.isArray(list) ? list : []).map((alert, index) => {
    const isCritical = String(alert?.severity || '').toLowerCase() === 'critical'
    return {
      id: index + 1,
      title: alert?.title || (isCritical ? 'Alerte critique' : 'Alerte'),
      time: formatAbsoluteAlertTime(alert?.measured_at),
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

export function mapPatient(item) {
  const patient = item?.patient || {}
  const profile = item?.profile || {}
  const latestVitals = item?.latest_vitals || {}
  const patientAlerts = normalizeListAlerts(item?.alerts, patient.name)
  const glucose = getLatestGlucoseValue(item?.alerts)
  const status = resolvePatientStatus(patientAlerts)

  return {
    id: patient.id,
    invitationId: item?.invitation_id,
    name: patient.name || 'Patient',
    initials: buildInitials(patient.name),
    email: patient.email || '',
    age: computeAge(patient.date_of_birth),
    lastSeen: formatRelativeTime(latestVitals.measured_at || item?.accepted_at || patient.created_at),
    nextVisit: formatDateLong(item?.accepted_at || patient.created_at),
    heartRate: latestVitals?.heart_rate ? `${Math.round(Number(latestVitals.heart_rate))} bpm` : '--',
    bloodPressure: latestVitals?.systolic_pressure
      ? `${Math.round(Number(latestVitals.systolic_pressure))}/${Math.round(Number(latestVitals.diastolic_pressure || 0))}`
      : '--',
    glucose,
    status,
    tags: buildListTags(profile),
    avatarColor: resolveAvatarColor(status, patient.name),
    dotColor: resolveDotColor(status),
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

export function mapInvitation(invitation) {
  const patient = invitation?.patient || {}
  const profile = invitation?.profile || {}

  return {
    id: invitation?.id,
    name: patient.name || 'Patient',
    email: patient.email || '',
    age: computeAge(patient.date_of_birth),
    invitedAt: formatDateLong(invitation?.created_at),
    tags: buildListTags(profile)
  }
}

// ---------------------------------------------------------------------------
// Data mappers – patient detail
// ---------------------------------------------------------------------------

export function mapPatientDetailResponse(data, fallbackPatient) {
  const profile = data?.profile || {}
  const latestVitals = data?.latest_vitals || {}
  const labs = Array.isArray(data?.lab_results) ? data.lab_results : []
  const vitals = Array.isArray(data?.vitals) ? data.vitals : []
  const treatments = Array.isArray(data?.treatment_medicines) ? data.treatment_medicines : []
  const treatmentChecks = Array.isArray(data?.treatment_checks) ? data.treatment_checks : []
  const patient = data?.patient || {}

  return {
    ...fallbackPatient,
    name: patient.name || fallbackPatient.name,
    age: computeAge(patient.date_of_birth) || fallbackPatient.age,
    gender: toSentenceCase(profile.sexe || ''),
    lastSeen: formatRelativeTime(latestVitals.measured_at || patient.updated_at || patient.created_at),
    detailTags: buildDetailTags(profile),
    detailAlerts: normalizeDetailAlerts(data?.alerts),
    overviewStats: buildOverviewStats(latestVitals, profile),
    vitalsHistory: groupVitalsHistory(vitals),
    analyses: labs.map(mapAnalysis),
    treatments: buildTreatments(treatments, treatmentChecks)
  }
}

export function buildOverviewStats(latestVitals, profile) {
  const heightCm = Number(profile?.taille || 0)
  const weightKg = Number(profile?.poids || 0)
  const bmi = heightCm > 0 ? weightKg / (heightCm / 100) ** 2 : null
  const heartRate = Number(latestVitals?.heart_rate || 0)
  const systolic = Number(latestVitals?.systolic_pressure || 0)
  const oxygen = Number(latestVitals?.oxygen_saturation || 0)

  return [
    {
      label: 'Rythme cardiaque',
      value: heartRate ? `${Math.round(heartRate)} bpm` : '--',
      badge: heartRate >= 90 ? 'Legerement eleve' : 'Normal',
      badgeClass: heartRate >= 90 ? 'bg-[#ffe6b8] text-[#d47b00]' : 'bg-[#d7f5df] text-[#11a84d]',
      icon: HeartIcon,
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
      icon: WaveIcon,
      iconWrapClass: 'bg-[#d8e9ff]',
      iconClass: 'text-[#2454ff]',
      cardClass: 'border-[#aed0ff] bg-[#f4fbff]'
    },
    {
      label: 'Saturation O2',
      value: oxygen ? `${Math.round(oxygen)} %` : '--',
      badge: oxygen >= 95 ? 'Normal' : 'Basse',
      badgeClass: oxygen >= 95 ? 'bg-[#d7f5df] text-[#11a84d]' : 'bg-[#ffe6b8] text-[#d47b00]',
      icon: DropIcon,
      iconWrapClass: 'bg-[#efe1ff]',
      iconClass: 'text-[#8c30ff]',
      cardClass: 'border-[#dbc1ff] bg-[#fbf7ff]'
    },
    {
      label: 'IMC',
      value: bmi ? bmi.toFixed(1) : '--',
      badge: bmi && bmi >= 25 ? 'Attention' : 'Normal',
      badgeClass: bmi && bmi >= 25 ? 'bg-[#ffe6b8] text-[#d47b00]' : 'bg-[#d7f5df] text-[#11a84d]',
      icon: TrendUpIcon,
      iconWrapClass: 'bg-[#d7f3e3]',
      iconClass: 'text-[#0da84c]',
      cardClass: 'border-[#aee9c2] bg-[#f3fff7]'
    }
  ]
}

export function groupVitalsHistory(rows) {
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
      date: formatDateShort(row?.measured_at),
      heartRate: row?.heart_rate ? `${Math.round(Number(row.heart_rate))} bpm` : '--',
      bloodPressure: row?.systolic_pressure
        ? `${Math.round(Number(row.systolic_pressure))}/${Math.round(Number(row.diastolic_pressure || 0))}`
        : '--',
      saturation: row?.oxygen_saturation ? `${Math.round(Number(row.oxygen_saturation))}%` : '--'
    }))
}

export function mapAnalysis(item) {
  const value =
    item?.value !== null && item?.value !== undefined
      ? `${item.value}${item?.unit ? ` ${item.unit}` : ''}`
      : '--'
  const numericValue = Number(item?.value)
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
    name: [item?.analysis_type, item?.analysis_result].filter(Boolean).join(' - ') || 'Analyse',
    value,
    range: 'Plage normale : a verifier',
    status,
    badgeClass,
    isoDate: String(item?.analysis_date || '').slice(0, 10),
    date: formatDateNumeric(item?.analysis_date)
  }
}

export function buildTreatments(medicines, checks) {
  const groupedChecks = {}
  ;(Array.isArray(checks) ? checks : []).forEach((check) => {
    const key = String(check?.medication_key || '')
    if (!groupedChecks[key]) groupedChecks[key] = []
    groupedChecks[key].push(check)
  })

  return (Array.isArray(medicines) ? medicines : []).map((medicine) => {
    const rows = groupedChecks[medicine.id] || []
    const total = rows.length
    const taken = rows.filter((row) => row?.taken).length
    const adherenceValue = total > 0 ? Math.round((taken / total) * 100) : 0

    return {
      name: medicine.name || 'Traitement',
      dose: `${medicine.dose || 'Dose non precisee'} - ${medicine.freq || 'Non precise'}`,
      when: medicine.note || 'Selon prescription',
      adherence: `${adherenceValue}%`,
      barClass: adherenceValue >= 90 ? 'bg-[#0cb342]' : 'bg-[#ea7a00]'
    }
  })
}
