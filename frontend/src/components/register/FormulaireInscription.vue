<!--
  InscriptionPage.vue
  Formulaire d'inscription : nom, email, date de naissance, mot de passe.
-->
<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-8">
    <div class="w-full max-w-2xl">

      <!-- Retour -->
      <button type="button" class="mb-6 ml-6 flex items-center gap-1 text-sm font-medium text-gray-600 transition-colors hover:text-gray-900" @click="$router.back()">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Retour
      </button>

      <div class="rounded-3xl bg-white p-10 shadow-lg">

        <!-- Logo -->
        <div class="mb-8 text-center">
          <div class="mb-6 flex items-center justify-center gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-purple-600">
              <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
              </svg>
            </div>
            <h1 class="text-3xl font-bold text-blue-600">HealthFlow</h1>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Créer un compte</h2>
          <p class="mt-2 text-base text-gray-600">Commencez votre parcours santé gratuitement</p>
        </div>

        <!-- Message serveur -->
        <div v-if="serverMessage" class="mb-6 rounded-lg border px-4 py-3 text-sm"
          :class="messageType === 'success' ? 'border-purple-200 bg-purple-50 text-purple-700' : 'border-red-200 bg-red-50 text-red-700'">
          {{ serverMessage }}
        </div>

        <form @submit.prevent="soumettre" class="space-y-5">

          <!-- Nom -->
          <FormField label="Nom complet" :error="errors.name">
            <template #icon>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </template>
            <input v-model.trim="form.name" type="text" placeholder="Votre nom" autocomplete="name" v-bind="inputProps('name')" />
          </FormField>

          <!-- Email -->
          <FormField label="Email" :error="errors.email">
            <template #icon>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </template>
            <input v-model.trim="form.email" type="email" placeholder="votre@email.com" autocomplete="email" v-bind="inputProps('email')" />
          </FormField>

          <!-- Date de naissance -->
          <FormField label="Date de naissance" :error="errors.date_of_birth">
            <template #icon>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </template>
            <input :value="form.date_of_birth" type="text" placeholder="JJ/MM/AAAA" inputmode="numeric" maxlength="10"
              v-bind="inputProps('date_of_birth')"
              @beforeinput="e => { if (e.data && /[^0-9/]/.test(e.data)) e.preventDefault() }"
              @input="onDateInput" @blur="validerDate" />
          </FormField>

          <!-- Mot de passe -->
          <FormField label="Mot de passe" :error="errors.password">
            <template #icon>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </template>
            <input v-model="form.password" type="password" placeholder="••••••••" autocomplete="new-password"
              v-bind="inputProps('password')" @input="errors.password = ''" />
          </FormField>

          <!-- Critères mot de passe -->
          <div class="space-y-1.5">
            <p v-for="r in passwordRules" :key="r.label" class="text-xs transition-colors" :class="r.ok ? 'font-medium text-purple-600' : 'text-gray-400'">
              {{ r.label }}
            </p>
          </div>

          <!-- Confirmation mot de passe -->
          <FormField label="Confirmer le mot de passe">
            <template #icon>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </template>
            <input v-model="form.password_confirmation" type="password" placeholder="••••••••" autocomplete="new-password" v-bind="inputProps('password')" />
          </FormField>

          <!-- CGU -->
          <label class="flex cursor-pointer items-start gap-3 pt-2">
            <input type="checkbox" class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <span class="text-sm text-gray-700">
              J'accepte les <a href="#" class="font-semibold text-blue-600 hover:text-blue-700">conditions d'utilisation</a>
              et la <a href="#" class="font-semibold text-blue-600 hover:text-blue-700">politique de confidentialité</a>
            </span>
          </label>

          <!-- Soumettre -->
          <button type="submit" :disabled="loading"
            class="mt-6 h-12 w-full rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 text-base font-semibold text-white transition-all hover:from-blue-700 hover:to-purple-700 disabled:cursor-not-allowed disabled:opacity-50">
            {{ loading ? 'Création…' : 'Créer mon compte' }}
          </button>

          <!-- Séparateur -->
          <div class="my-5 flex items-center gap-3">
            <div class="h-px flex-1 bg-gray-200" /><span class="text-sm text-gray-500">ou</span><div class="h-px flex-1 bg-gray-200" />
          </div>

          <!-- Google -->
          <button type="button" class="flex h-12 w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white text-base font-medium text-gray-700 transition-colors hover:border-gray-300">
            <svg class="h-5 w-5" viewBox="0 0 24 24">
              <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="10" fill="currentColor" font-weight="bold">G</text>
            </svg>
            Continuer avec Google
          </button>

          <!-- Lien connexion -->
          <p class="pt-4 text-center text-sm text-gray-600">
            Vous avez déjà un compte ?
            <RouterLink :to="{ name: 'connexion' }" class="font-semibold text-blue-600 transition-colors hover:text-blue-700">Se connecter</RouterLink>
          </p>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

// ─── Sous-composant champ formulaire ─────────────────────────────────────────
const FormField = {
  props: ['label', 'error'],
  template: `
    <div>
      <label class="mb-2 block text-sm font-medium text-gray-700">{{ label }}</label>
      <div class="relative">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <slot name="icon" />
        </svg>
        <slot />
      </div>
      <p v-if="error" class="mt-1.5 text-sm text-red-600">{{ error }}</p>
    </div>`,
}

const router    = useRouter()
const authStore = useAuthStore()

// ─── État ─────────────────────────────────────────────────────────────────────
const form = reactive({ name: '', email: '', date_of_birth: '', password: '', password_confirmation: '' })
const errors = reactive({ name: '', email: '', date_of_birth: '', password: '' })
const loading       = ref(false)
const serverMessage = ref('')
const messageType   = ref('success')

// ─── Classes input dynamiques ─────────────────────────────────────────────────
const inputProps = field => ({
  class: [
    'w-full h-12 pl-12 pr-4 rounded-lg border bg-gray-50 text-base text-gray-900 placeholder:text-gray-400 outline-none transition-colors',
    errors[field] ? 'border-red-300 focus:border-red-500 focus:bg-white' : 'border-gray-200 focus:border-blue-500 focus:bg-white',
  ],
})

// ─── Critères mot de passe ────────────────────────────────────────────────────
const passwordRules = computed(() => [
  { label: 'Au moins 8 caractères', ok: form.password.length >= 8 },
  { label: 'Au moins une lettre',   ok: /[a-zA-Z]/.test(form.password) },
  { label: 'Au moins un chiffre',   ok: /[0-9]/.test(form.password) },
])

const pwdError = pwd => {
  if (pwd.length < 8)         return 'Le mot de passe est trop court (min 8 caractères).'
  if (!/[a-zA-Z]/.test(pwd))  return 'Le mot de passe doit contenir au moins une lettre.'
  if (!/[0-9]/.test(pwd))     return 'Le mot de passe doit contenir au moins un chiffre.'
  return ''
}

// ─── Date de naissance ────────────────────────────────────────────────────────
function onDateInput(e) {
  const digits = (e.target.value ?? '').replace(/\D/g, '').slice(0, 8)
  form.date_of_birth = digits.length <= 2 ? digits
    : digits.length <= 4 ? `${digits.slice(0,2)}/${digits.slice(2)}`
    : `${digits.slice(0,2)}/${digits.slice(2,4)}/${digits.slice(4,8)}`
}

function validerDate() {
  const m = form.date_of_birth.match(/^(\d{2})\/(\d{2})\/(\d{4})$/)
  if (!form.date_of_birth) { errors.date_of_birth = ''; return }
  if (!m) { errors.date_of_birth = 'Format invalide. Utilisez JJ/MM/AAAA.'; return }
  const [, d, mo, y] = m.map(Number)
  const date = new Date(y, mo - 1, d)
  errors.date_of_birth = (mo < 1 || mo > 12 || d < 1 || d > 31 || date.getFullYear() !== y || date.getMonth() !== mo - 1 || date.getDate() !== d)
    ? 'Date invalide. Utilisez JJ/MM/AAAA.' : ''
}

function isoDate(str) {
  const m = str.match(/^(\d{2})\/(\d{2})\/(\d{4})$/)
  return m ? `${m[3]}-${m[2]}-${m[1]}` : str
}

// ─── Soumission ───────────────────────────────────────────────────────────────
async function soumettre() {
  serverMessage.value = ''
  messageType.value   = 'success'
  Object.keys(errors).forEach(k => (errors[k] = ''))

  // Validation champs obligatoires
  if (!form.name)          errors.name          = "Le nom d'utilisateur est obligatoire."
  if (!form.email)         errors.email         = "L'adresse email est obligatoire."
  if (!form.date_of_birth) errors.date_of_birth = 'La date de naissance est obligatoire.'
  if (!form.password)      errors.password      = 'Le mot de passe est obligatoire.'

  // Validation format
  if (form.name.length > 0 && form.name.length < 3) errors.name = "Le nom doit contenir au moins 3 caractères."
  if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errors.email = "Format d'email invalide."
  if (form.password) { const e = pwdError(form.password); if (e) errors.password = e }
  if (form.password && form.password !== form.password_confirmation) errors.password = 'Les mots de passe ne correspondent pas.'
  validerDate()

  if (Object.values(errors).some(Boolean)) return

  loading.value = true
  try {
    const { data } = await api.post('/auth/register', {
      name: form.name, email: form.email,
      date_of_birth: isoDate(form.date_of_birth),
      password: form.password, password_confirmation: form.password_confirmation,
    })
    authStore.appliquerAuthentification(data, 'personnel')
    serverMessage.value = data?.message || 'Compte créé avec succès.'
    setTimeout(() => router.push(data?.redirect_to || '/profil-sante'), 900)
  } catch (err) {
    messageType.value = 'error'
    const status = err?.response?.status
    const data   = err?.response?.data ?? {}
    const first  = v => (Array.isArray(v) ? String(v[0] || '') : String(v || ''))

    if (!err?.response)          { serverMessage.value = 'Problème réseau. Réessayez.'; return }
    if (status === 422 && data.errors) {
      errors.name          = first(data.errors.name)
      errors.email         = first(data.errors.email)
      errors.date_of_birth = first(data.errors.date_of_birth)
      errors.password      = first(data.errors.password)
      serverMessage.value  = 'Veuillez corriger les erreurs du formulaire.'
      return
    }
    if (status === 409 && data.errors?.email) {
      errors.email        = first(data.errors.email)
      serverMessage.value = data?.message || errors.email || 'Cet email est déjà utilisé.'
      return
    }
    serverMessage.value = data?.message || 'Erreur lors de la création du compte.'
  } finally {
    loading.value = false
  }
}
</script>