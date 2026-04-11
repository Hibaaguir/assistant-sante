<template>
    
    <div
        class="w-full px-4 py-4 sm:px-6 lg:px-8"
    >
        <header class="mb-4 flex items-start gap-3 sm:gap-4">
            <div
                class="mt-1 flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-400 to-purple-500 shadow-md shadow-purple-300/80 sm:h-12 sm:w-12"
            >
                <svg
                    viewBox="0 0 24 24"
                    class="h-6 w-6 text-white"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z"
                    />
                </svg>
            </div>
            <div>
                <h1
                    class="text-[42px] font-bold leading-none tracking-[-0.01em] text-purple-900 sm:text-[48px]"
                >
                    Health Profile
                </h1>
                <p
                    class="mt-1 text-[12px] font-medium leading-none text-slate-500 sm:text-[13px]"
                >
                    Manage your health information
                </p>
            </div>
        </header>

        <NotificationsOnline />

        <div
            v-if="loading"
            class="rounded-3xl border border-[#d8e6ff] bg-white/85 p-6 text-sm text-slate-600"
        >
            Chargement du profil...
        </div>

        <div
            v-else-if="loadError"
            class="rounded-3xl border border-red-200 bg-red-50/90 p-6 text-sm text-red-700"
        >
            {{ loadError }}
        </div>

        <div v-else class="grid gap-4 lg:grid-cols-2">
            <!-- Section: Informations de base -->
            <section
                class="min-h-[250px] rounded-[14px] border border-purple-200 bg-gradient-to-br from-purple-50 via-white to-purple-100 p-4 shadow-[0_4px_16px_rgba(59,130,246,0.1)] sm:p-5 relative overflow-hidden"
            >
                <div
                    class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-200/20 blur-2xl"
                ></div>
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e2e8f0] text-[#334155]"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" />
                            </svg>
                        </span>
                        <h2
                            class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]"
                        >
                            Informations de base
                        </h2>
                    </div>
                    <button
                        v-if="!editing.base"
                        type="button"
                        class="text-slate-800 transition-colors hover:text-slate-900"
                        @click="startEdit('base')"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                    </button>
                </div>

                <div v-if="!editing.base" class="space-y-2">
                    <HealthFieldRow
                        label="Nom"
                        :value="user.name || '-'"
                        icon="user"
                    />
                    <HealthFieldRow
                        label="Âge"
                        :value="computedAge || '-'"
                        icon="calendar"
                    />
                    <HealthFieldRow
                        label="Sexe"
                        :value="
                            profile.gender === 'male'
                                ? 'Homme'
                                : profile.gender === 'female'
                                  ? 'Femme'
                                  : profile.gender || '-'
                        "
                        icon="users"
                    />
                    <HealthFieldRow
                        label="Taille"
                        :value="profile.height ? `${profile.height} cm` : '-'"
                        icon="ruler"
                    />
                    <HealthFieldRow
                        label="Poids"
                        :value="profile.weight ? `${profile.weight} kg` : '-'"
                        icon="weight"
                    />
                </div>

                <form
                    v-else
                    class="space-y-4"
                    novalidate
                    @submit.prevent="saveSection('base')"
                >
                    <div
                        v-if="sectionErrors.base.form.length"
                        class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700"
                    >
                        <p
                            v-for="(message, idx) in sectionErrors.base.form"
                            :key="`base-form-error-${idx}`"
                        >
                            {{ message }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-slate-900"
                            >Nom</label
                        >
                        <input
                            :value="user.name || ''"
                            disabled
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base text-slate-700"
                        />
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-sm font-semibold text-slate-900"
                                >Genre</label
                            >
                            <select
                                v-model="draft.gender"
                                class="h-11 w-full rounded-xl border bg-slate-100 px-4 text-base"
                                :class="
                                    sectionErrors.base.gender
                                        ? 'border-red-400 focus:border-red-500'
                                        : 'border-slate-200'
                                "
                            >
                                <option value="female">Femme</option>
                                <option value="male">Homme</option>
                            </select>
                            <p
                                v-if="sectionErrors.base.gender"
                                class="mt-1 text-xs font-medium text-red-600"
                            >
                                {{ sectionErrors.base.gender }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-semibold text-slate-900"
                                >Taille (cm)</label
                            >
                            <input
                                v-model="draft.height"
                                type="number"
                                min="80"
                                max="250"
                                class="h-11 w-full rounded-xl border bg-slate-100 px-4 text-base"
                                :class="
                                    sectionErrors.base.height
                                        ? 'border-red-400 focus:border-red-500'
                                        : 'border-slate-200'
                                "
                            />
                            <p
                                v-if="sectionErrors.base.height"
                                class="mt-1 text-xs font-medium text-red-600"
                            >
                                {{ sectionErrors.base.height }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-slate-900"
                            >Poids (kg)</label
                        >
                        <input
                            v-model="draft.weight"
                            type="number"
                            min="35"
                            max="250"
                            class="h-11 w-full rounded-xl border bg-slate-100 px-4 text-base"
                            :class="
                                sectionErrors.base.weight
                                    ? 'border-red-400 focus:border-red-500'
                                    : 'border-slate-200'
                            "
                        />
                        <p
                            v-if="sectionErrors.base.weight"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ sectionErrors.base.weight }}
                        </p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <button
                            type="submit"
                            :disabled="savingSection === 'base'"
                            class="h-11 rounded-xl bg-gradient-to-r from-emerald-300 to-emerald-400 px-5 text-sm font-bold text-emerald-900 disabled:opacity-60 hover:shadow-lg transition-shadow"
                        >
                            Enregistrer
                        </button>
                        <button
                            type="button"
                            class="h-11 rounded-xl border border-orange-300 bg-orange-100 px-5 text-sm font-semibold text-orange-800"
                            @click="cancelEdit('base')"
                        >
                            Annuler
                        </button>
                    </div>
                </form>
            </section>

            <!-- Section: Santé -->
            <section
                class="min-h-[250px] rounded-[14px] border border-purple-200 bg-gradient-to-br from-purple-50 via-white to-purple-100 p-4 shadow-[0_4px_16px_rgba(168,85,247,0.1)] sm:p-5 relative overflow-hidden"
            >
                <div
                    class="pointer-events-none absolute -left-8 -bottom-8 h-28 w-28 rounded-full bg-purple-200/20 blur-2xl"
                ></div>
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M12 21s-6.5-4.5-9-8.5C.7 8.4 3 4 7.3 4c2 0 3.6 1 4.7 2.6C13.1 5 14.7 4 16.7 4 21 4 23.3 8.4 21 12.5 18.5 16.5 12 21 12 21z"
                                />
                            </svg>
                        </span>
                        <h2
                            class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]"
                        >
                            Santé
                        </h2>
                    </div>
                    <button
                        v-if="!editing.health"
                        type="button"
                        class="text-slate-800 transition-colors hover:text-slate-900"
                        @click="startEdit('health')"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                    </button>
                </div>

                <div v-if="!editing.health" class="space-y-2">
                    <HealthFieldRow
                        label="Groupe sanguin"
                        :value="profile.blood_type || '-'"
                        icon="droplet"
                    />
                    <HealthFieldRow
                        label="Objectifs"
                        :value="joinList(profile.goals)"
                        icon="target"
                    />
                    <HealthFieldRow
                        label="Allergies"
                        :value="joinList(profile.allergies)"
                        icon="alert"
                    />
                    <HealthFieldRow
                        label="Maladies chroniques"
                        :value="joinList(profile.chronic_diseases)"
                        icon="shield"
                    />
                </div>

                <form
                    v-else
                    class="space-y-4"
                    novalidate
                    @submit.prevent="saveSection('health')"
                >
                    <div
                        v-if="sectionErrors.health.form.length"
                        class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700"
                    >
                        <p
                            v-for="(message, idx) in sectionErrors.health.form"
                            :key="`health-form-error-${idx}`"
                        >
                            {{ message }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-slate-900"
                            >Groupe sanguin</label
                        >
                        <select
                            v-model="draft.bloodType"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base"
                        >
                            <option value="">-</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-slate-900"
                            >Objectifs</label
                        >
                        <div
                            class="rounded-xl border bg-slate-50 p-3"
                            :class="
                                sectionErrors.health.goals
                                    ? 'border-red-300'
                                    : 'border-slate-200'
                            "
                        >
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="goal in goalOptions"
                                    :key="goal"
                                    type="button"
                                    class="rounded-lg border px-3 py-1.5 text-sm"
                                    :class="
                                        isSelected('goals', goal)
                                            ? 'border-slate-300 bg-slate-100 text-slate-700'
                                            : 'border-slate-200 bg-white text-slate-700'
                                    "
                                    @click="toggleSelected('goals', goal)"
                                >
                                    {{ goal }}
                                </button>
                            </div>
                        </div>
                        <p
                            v-if="sectionErrors.health.goals"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ sectionErrors.health.goals }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-slate-900"
                            >Allergies</label
                        >
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3"
                        >
                            <div class="flex gap-2">
                                <select
                                    v-model="selectedAllergyOption"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm"
                                    @change="
                                        addSelectedOption(
                                            'allergies',
                                            selectedAllergyOption,
                                            'allergy',
                                        )
                                    "
                                >
                                    <option value="">
                                        Selectionner une allergie
                                    </option>
                                    <option
                                        v-for="item in allergyOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                            <div
                                v-if="draft.allergies.length"
                                class="mt-3 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="item in draft.allergies"
                                    :key="`allergy-${item}`"
                                    class="inline-flex items-center gap-2 rounded-lg border border-purple-200 bg-purple-50 px-2.5 py-1 text-xs text-purple-800"
                                >
                                    {{ item }}
                                    <button
                                        type="button"
                                        @click="
                                            toggleSelected('allergies', item)
                                        "
                                    >
                                        x
                                    </button>
                                </span>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <input
                                    v-model="customInputs.allergies"
                                    class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm"
                                    placeholder="Ajouter une allergie si absente..."
                                />
                                <button
                                    type="button"
                                    class="h-10 rounded-lg bg-gradient-to-r from-blue-300 to-blue-400 px-3 text-sm font-medium text-white hover:from-blue-400 hover:to-blue-500"
                                    @click="
                                        addCustom(
                                            'allergies',
                                            customInputs.allergies,
                                        )
                                    "
                                >
                                    Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-slate-900"
                            >Maladies chroniques</label
                        >
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3"
                        >
                            <div class="flex gap-2">
                                <select
                                    v-model="selectedDiseaseOption"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm"
                                    @change="
                                        addSelectedOption(
                                            'chronicDiseases',
                                            selectedDiseaseOption,
                                            'disease',
                                        )
                                    "
                                >
                                    <option value="">
                                        Selectionner une maladie
                                    </option>
                                    <option
                                        v-for="item in diseaseOptions"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                            <div
                                v-if="draft.chronicDiseases.length"
                                class="mt-3 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="item in draft.chronicDiseases"
                                    :key="`disease-${item}`"
                                    class="inline-flex items-center gap-2 rounded-lg border border-purple-200 bg-purple-50 px-2.5 py-1 text-xs text-purple-800"
                                >
                                    {{ item }}
                                    <button
                                        type="button"
                                        @click="
                                            toggleSelected(
                                                'chronicDiseases',
                                                item,
                                            )
                                        "
                                    >
                                        x
                                    </button>
                                </span>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <input
                                    v-model="customInputs.chronicDiseases"
                                    class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm"
                                    placeholder="Ajouter une maladie si absente..."
                                />
                                <button
                                    type="button"
                                    class="h-10 rounded-lg bg-gradient-to-r from-blue-300 to-blue-400 px-3 text-sm font-medium text-white hover:from-blue-400 hover:to-blue-500"
                                    @click="
                                        addCustom(
                                            'chronicDiseases',
                                            customInputs.chronicDiseases,
                                        )
                                    "
                                >
                                    Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <button
                            type="submit"
                            :disabled="savingSection === 'health'"
                            class="h-11 rounded-xl bg-gradient-to-r from-emerald-300 to-emerald-400 px-5 text-sm font-bold text-emerald-900 disabled:opacity-60 hover:shadow-lg transition-shadow"
                        >
                            Enregistrer
                        </button>
                        <button
                            type="button"
                            class="h-11 rounded-xl border border-orange-300 bg-orange-100 px-5 text-sm font-semibold text-orange-800"
                            @click="cancelEdit('health')"
                        >
                            Annuler
                        </button>
                    </div>
                </form>
            </section>

            <!-- Section: Habitudes -->
            <section
                class="min-h-[250px] rounded-[14px] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-100 p-4 shadow-[0_4px_16px_rgba(34,197,94,0.1)] sm:p-5 relative overflow-hidden"
            >
                <div
                    class="pointer-events-none absolute -right-10 -bottom-10 h-32 w-32 rounded-full bg-cyan-200/15 blur-2xl"
                ></div>
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M3 12h4l2-6 4 12 2-6h6" />
                            </svg>
                        </span>
                        <h2
                            class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]"
                        >
                            Habitudes
                        </h2>
                    </div>
                    <button
                        v-if="!editing.habits"
                        type="button"
                        class="text-slate-800 transition-colors hover:text-slate-900"
                        @click="startEdit('habits')"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                    </button>
                </div>

                <div v-if="!editing.habits" class="space-y-2">
                    <HealthFieldRow
                        label="Fumeur"
                        :value="yesNo(profile.smoker)"
                        icon="smoke"
                    />
                    <HealthFieldRow
                        label="Alcool"
                        :value="yesNo(profile.alcoholic)"
                        icon="wine"
                    />
                    <HealthFieldRow
                        label="Traitements"
                        :value="treatmentsSummary(profile.treatments)"
                        icon="pill"
                    />
                </div>

                <form
                    v-else
                    class="space-y-4"
                    novalidate
                    @submit.prevent="saveSection('habits')"
                >
                    <div class="grid grid-cols-2 gap-3">
                        <div
                            class="flex h-14 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3"
                        >
                            <label class="text-sm font-semibold text-slate-900"
                                >Fumeur</label
                            >
                            <button
                                type="button"
                                :aria-pressed="draft.smoker"
                                class="relative h-8 w-14 rounded-full bg-[#c7d2e0] transition-colors"
                                @click="draft.smoker = !draft.smoker"
                            >
                                <span
                                    class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition-transform"
                                    :class="
                                        draft.smoker
                                            ? 'translate-x-6'
                                            : 'translate-x-0'
                                    "
                                />
                            </button>
                        </div>
                        <div
                            class="flex h-14 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3"
                        >
                            <label class="text-sm font-semibold text-slate-900"
                                >Alcool</label
                            >
                            <button
                                type="button"
                                :aria-pressed="draft.alcoholic"
                                class="relative h-8 w-14 rounded-full bg-[#c7d2e0] transition-colors"
                                @click="draft.alcoholic = !draft.alcoholic"
                            >
                                <span
                                    class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition-transform"
                                    :class="
                                        draft.alcoholic
                                            ? 'translate-x-6'
                                            : 'translate-x-0'
                                    "
                                />
                            </button>
                        </div>
                    </div>
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-3 sm:p-4"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Traitements
                            </p>
                            <button
                                type="button"
                                class="h-9 rounded-lg bg-gradient-to-r from-blue-300 to-blue-400 px-3 text-sm font-medium text-white hover:from-blue-400 hover:to-blue-500"
                                @click="openTreatmentEditor()"
                            >
                                Ajouter
                            </button>
                        </div>

                        <div
                            v-if="showTreatmentEditor"
                            class="space-y-3 rounded-lg border border-slate-200 bg-white p-3"
                        >
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-slate-600"
                                        >Type</label
                                    >
                                    <input
                                        v-model="treatmentDraft.type"
                                        list="treatment-types"
                                        class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm"
                                        placeholder="Type de traitement"
                                        @input="handleTreatmentTypeInput"
                                    />
                                    <datalist id="treatment-types">
                                        <option
                                            v-for="type in treatmentTypes"
                                            :key="type"
                                            :value="type"
                                        />
                                    </datalist>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-slate-600"
                                        >Nom</label
                                    >
                                    <input
                                        v-model="treatmentDraft.name"
                                        list="treatment-names"
                                        :disabled="!treatmentDraft.type"
                                        class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm disabled:cursor-not-allowed disabled:bg-slate-100"
                                        :placeholder="
                                            treatmentDraft.type
                                                ? 'Nom du traitement'
                                                : 'Sélectionnez d\'abord un type'
                                        "
                                    />
                                    <datalist id="treatment-names">
                                        <option
                                            v-for="name in treatmentNamesForSelectedType"
                                            :key="name"
                                            :value="name"
                                        />
                                    </datalist>
                                </div>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-slate-600"
                                        >Dose</label
                                    >
                                    <input
                                        v-model="treatmentDraft.dose"
                                        class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm"
                                        placeholder="Ex: 500mg"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-slate-600"
                                        >Fréquence</label
                                    >
                                    <div class="grid grid-cols-2 gap-2">
                                        <select
                                            v-model="
                                                treatmentDraft.frequency_unit
                                            "
                                            class="h-10 rounded-lg border border-slate-200 bg-slate-50 px-2 text-sm"
                                        >
                                            <option value="day">jour</option>
                                            <option value="week">
                                                semaine
                                            </option>
                                            <option value="month">mois</option>
                                        </select>
                                        <input
                                            v-model.number="
                                                treatmentDraft.frequency_count
                                            "
                                            type="number"
                                            min="1"
                                            class="h-10 rounded-lg border border-slate-200 bg-slate-50 px-2 text-sm"
                                            placeholder="Nb de prises"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-slate-600"
                                        >Début (JJ/MM/AAAA)</label
                                    >
                                    <input
                                        :value="treatmentDraft.start_date"
                                        class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm"
                                        maxlength="10"
                                        placeholder="01/03/2026"
                                        @input="
                                            (e) =>
                                                handleTreatmentDateInput(
                                                    e,
                                                    'start_date',
                                                )
                                        "
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-slate-600"
                                        >Fin (JJ/MM/AAAA)</label
                                    >
                                    <input
                                        :value="treatmentDraft.end_date"
                                        class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm"
                                        maxlength="10"
                                        placeholder="30/03/2026"
                                        @input="
                                            (e) =>
                                                handleTreatmentDateInput(
                                                    e,
                                                    'end_date',
                                                )
                                        "
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="h-10 rounded-lg bg-gradient-to-r from-blue-300 to-blue-400 text-sm font-medium text-white hover:from-blue-400 hover:to-blue-500"
                                    @click="saveTreatmentDraft"
                                >
                                    {{
                                        editingTreatmentIndex > -1
                                            ? "Mettre à jour"
                                            : "Ajouter"
                                    }}
                                </button>
                                <button
                                    type="button"
                                    class="h-10 rounded-lg border border-slate-300 bg-white text-sm font-medium text-slate-800"
                                    @click="cancelTreatmentEditWithNotice"
                                >
                                    Annuler
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="draft.treatments.length"
                            class="mt-3 space-y-2"
                        >
                            <div
                                v-for="(item, index) in draft.treatments"
                                :key="`draft-treatment-${index}-${item.type}-${item.name}`"
                                class="flex items-start justify-between rounded-lg border border-slate-200 bg-white px-3 py-2.5"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-medium text-slate-900"
                                    >
                                        {{ item.name || "Traitement" }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ item.type || "-"
                                        }}<span v-if="item.dose">
                                            | {{ item.dose }}</span
                                        ><span
                                            v-if="
                                                item.frequency_count &&
                                                item.frequency_unit
                                            "
                                        >
                                            | {{ item.frequency_count }}/{{
                                                item.frequency_unit
                                            }}</span
                                        >
                                    </p>
                                </div>
                                <div class="ml-3 flex items-center gap-3">
                                    <button
                                        type="button"
                                        class="text-xs font-medium text-purple-700"
                                        @click="openTreatmentEditor(index)"
                                    >
                                        Modifier
                                    </button>
                                    <button
                                        type="button"
                                        class="text-xs font-medium text-red-600"
                                        @click="requestRemoveTreatment(index)"
                                    >
                                        Retirer
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-xs text-slate-500">
                            Aucun traitement ajouté.
                        </p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <button
                            type="submit"
                            :disabled="savingSection === 'habits'"
                            class="h-11 rounded-xl bg-gradient-to-r from-emerald-300 to-emerald-400 px-5 text-sm font-bold text-emerald-900 disabled:opacity-60 hover:shadow-lg transition-shadow"
                        >
                            Enregistrer
                        </button>
                        <button
                            type="button"
                            class="h-11 rounded-xl border border-orange-300 bg-orange-100 px-5 text-sm font-semibold text-orange-800"
                            @click="cancelEdit('habits')"
                        >
                            Annuler
                        </button>
                    </div>
                </form>
            </section>

            <!-- Section: Suivi médecin -->
            <section
                class="min-h-[250px] rounded-[14px] border border-violet-200 bg-gradient-to-br from-violet-50 via-white to-violet-100 p-4 shadow-[0_2px_8px_rgba(15,23,42,0.08)] sm:p-5"
            >
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-700"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M8 4v5a4 4 0 1 0 8 0V4M12 13v3a4 4 0 0 0 8 0v-1a2 2 0 1 0-4 0v1"
                                />
                            </svg>
                        </span>
                        <h2
                            class="text-[20px] font-medium leading-none text-slate-900 sm:text-[23px]"
                        >
                            Suivi médecin
                        </h2>
                    </div>
                    <button
                        v-if="!editing.doctor"
                        type="button"
                        class="text-slate-800 transition-colors hover:text-slate-900"
                        @click="startEdit('doctor')"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                    </button>
                </div>

                <div v-if="!editing.doctor" class="space-y-2">
                    <HealthFieldRow
                        label="Partage du profil avec mon médecin"
                        :value="yesNo(profile.doctor_invited)"
                        icon="shield"
                    />
                    <HealthFieldRow
                        label="Email du médecin"
                        :value="profile.doctor_email || '-'"
                        icon="stetho"
                    />
                </div>

                <form
                    v-else
                    class="space-y-4"
                    novalidate
                    @submit.prevent="saveSection('doctor')"
                >
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-slate-900"
                            >Partager mon profil avec mon médecin</label
                        >
                        <select
                            v-model="draft.doctorCanConsult"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-base"
                            @change="validateDoctorEmail"
                        >
                            <option :value="true">Oui</option>
                            <option :value="false">Non</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-slate-900"
                            >Email du médecin</label
                        >
                        <input
                            v-model.trim="draft.doctorEmail"
                            :disabled="!draft.doctorCanConsult"
                            class="h-11 w-full rounded-xl border bg-slate-100 px-4 text-base disabled:opacity-60"
                            :class="
                                doctorEmailError
                                    ? 'border-red-400 focus:border-red-500'
                                    : 'border-slate-200'
                            "
                            @input="validateDoctorEmail"
                            @blur="validateDoctorEmail"
                        />
                        <p
                            v-if="doctorEmailError"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ doctorEmailError }}
                        </p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <button
                            type="submit"
                            :disabled="savingSection === 'doctor'"
                            class="h-11 rounded-xl bg-gradient-to-r from-emerald-300 to-emerald-400 px-5 text-sm font-bold text-emerald-900 disabled:opacity-60 hover:shadow-lg transition-shadow"
                        >
                            Enregistrer
                        </button>
                        <button
                            type="button"
                            class="h-11 rounded-xl border border-orange-300 bg-orange-100 px-5 text-sm font-semibold text-orange-800"
                            @click="cancelEdit('doctor')"
                        >
                            Annuler
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <ConfirmationDialog
            :open="confirmDeleteTreatmentOpen"
            title="Confirmer la suppression"
            message="Voulez-vous supprimer ce traitement ?"
            confirm-label="Supprimer"
            cancel-label="Annuler"
            @confirm="confirmRemoveTreatment"
            @cancel="cancelRemoveTreatment"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import HealthFieldRow from "@/components/health/HealthFieldRow.vue";
import { useNotificationsStore } from "@/stores/notifications";
import NotificationsOnline from "@/components/ui/NotificationsOnline.vue";
import ConfirmationDialog from "@/components/ui/ConfirmationDialog.vue";

const router = useRouter();
const authStore = useAuthStore();
const notifications = useNotificationsStore();
const loading = ref(true);
const loadError = ref("");
const doctorEmailError = ref("");
const savingSection = ref("");

// Reactive profile data (English API keys)
const profile = reactive({});
const user = reactive({ name: "", dateOfBirth: "" });

const sectionErrors = reactive({
    base: { gender: "", height: "", weight: "", form: [] },
    health: { goals: "", form: [] },
});

const editing = reactive({
    base: false,
    health: false,
    habits: false,
    doctor: false,
});

// Draft with English keys matching API contract
const draft = reactive({
    gender: "",
    height: "",
    weight: "",
    bloodType: "",
    goals: [],
    allergies: [],
    chronicDiseases: [],
    treatments: [],
    smoker: false,
    alcoholic: false,
    doctorCanConsult: false,
    doctorEmail: "",
});

const goalOptions = [
    "Maintenir mon poids",
    "Perdre du poids",
    "Avoir plus d'energie",
    "Mieux dormir",
    "Reduire mon stress",
    "Suivre ma sante regulierement",
];
const allergyOptions = ref([
    "Pollen",
    "Acariens",
    "Poils d'animaux",
    "Poussiere",
    "Arachides",
    "Fruits de mer",
    "Lait (lactose)",
    "Oeufs",
    "Gluten",
    "Penicilline",
    "Aspirine",
    "Piqures d'insectes",
    "Moisissures",
]);
const diseaseOptions = ref([
    "Diabete",
    "Hypertension arterielle",
    "Asthme",
    "Maladie cardiaque",
    "Maladie renale chronique",
    "Maladie thyroidienne",
    "Arthrite",
    "Epilepsie",
    "Migraine chronique",
    "Maladie pulmonaire chronique",
    "Cholesterol eleve",
    "Depression",
    "Anemie",
]);
const treatmentTypes = ref([]);
const treatmentNamesByType = reactive({});
const customInputs = reactive({ allergies: "", chronicDiseases: "" });
const selectedAllergyOption = ref("");
const selectedDiseaseOption = ref("");
const showTreatmentEditor = ref(false);
const editingTreatmentIndex = ref(-1);
const confirmDeleteTreatmentOpen = ref(false);
const pendingDeleteTreatmentIndex = ref(-1);
const treatmentDraft = reactive({
    type: "",
    name: "",
    dose: "",
    frequency_unit: "day",
    frequency_count: 1,
    start_date: "",
    end_date: "",
});

// ─── Catalog helpers ──────────────────────────────────────────────────────────

function normalizeTreatmentText(value) {
    return String(value || "")
        .trim()
        .replace(/\s+/g, " ");
}

function appendUniqueCatalogOption(targetRef, value) {
    const normalized = normalizeTreatmentText(value);
    if (!normalized) return;
    const exists = targetRef.value.some(
        (item) =>
            item.localeCompare(normalized, "fr", { sensitivity: "base" }) === 0,
    );
    if (exists) return;
    targetRef.value = [...targetRef.value, normalized].sort((a, b) =>
        a.localeCompare(b, "fr", { sensitivity: "base" }),
    );
}

function ensureTreatmentType(type) {
    const normalized = normalizeTreatmentText(type);
    if (!normalized) return "";
    if (!treatmentTypes.value.includes(normalized)) {
        treatmentTypes.value = [...treatmentTypes.value, normalized].sort(
            (a, b) => a.localeCompare(b, "fr", { sensitivity: "base" }),
        );
    }
    if (!Array.isArray(treatmentNamesByType[normalized])) {
        treatmentNamesByType[normalized] = [];
    }
    return normalized;
}

function mergeTreatmentCatalogEntry(type, name = "") {
    const normalizedType = ensureTreatmentType(type);
    if (!normalizedType) return;
    const normalizedName = normalizeTreatmentText(name);
    if (!normalizedName) return;
    const names = treatmentNamesByType[normalizedType];
    if (!names.includes(normalizedName)) {
        names.push(normalizedName);
        names.sort((a, b) => a.localeCompare(b, "fr", { sensitivity: "base" }));
    }
}

function applyTreatmentCatalog(catalog) {
    const types = Array.isArray(catalog?.types) ? catalog.types : [];
    types.forEach((type) => ensureTreatmentType(type));
    const namesByType =
        catalog?.names_by_type && typeof catalog.names_by_type === "object"
            ? catalog.names_by_type
            : {};
    Object.entries(namesByType).forEach(([type, names]) => {
        ensureTreatmentType(type);
        (Array.isArray(names) ? names : []).forEach((name) =>
            mergeTreatmentCatalogEntry(type, name),
        );
    });
}

async function loadTreatmentCatalog() {
    try {
        const response = await api.get("/treatment-catalog");
        applyTreatmentCatalog(response?.data?.data || {});
    } catch (_) {
        // Catalog is optional; profile remains editable without it.
    }
}

function getTreatmentNamesByType(type) {
    const normalized = ensureTreatmentType(type);
    if (!normalized) return [];
    return treatmentNamesByType[normalized];
}

const treatmentNamesForSelectedType = computed(() =>
    getTreatmentNamesByType(treatmentDraft.type),
);

async function persistTreatmentCatalogEntry(type, name = "") {
    const normalizedType = normalizeTreatmentText(type);
    if (!normalizedType) return;
    await api.post("/treatment-catalog", {
        type: normalizedType,
        name: normalizeTreatmentText(name) || null,
    });
}

// ─── Computed ─────────────────────────────────────────────────────────────────

const computedAge = computed(() => {
    if (!user.dateOfBirth) return "";
    const dob = new Date(user.dateOfBirth);
    if (Number.isNaN(dob.getTime())) return "";
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate()))
        age -= 1;
    return age >= 0 ? `${age} ans` : "";
});

// ─── Display helpers ──────────────────────────────────────────────────────────

function yesNo(value) {
    return value ? "Oui" : "Non";
}

function joinList(value) {
    if (!Array.isArray(value) || value.length === 0) return "-";
    return value.filter(Boolean).join(", ");
}

function normalizeList(value) {
    if (!Array.isArray(value)) return [];
    return value.map((item) => String(item || "").trim()).filter(Boolean);
}

function treatmentsSummary(value) {
    if (!Array.isArray(value) || value.length === 0) return "-";
    const labels = value
        .map((item) =>
            item && typeof item === "object"
                ? item.name || item.type || ""
                : "",
        )
        .filter(Boolean);
    return labels.length ? labels.join(", ") : `${value.length} traitement(s)`;
}

// ─── Multi-select helpers ─────────────────────────────────────────────────────

function isSelected(key, value) {
    return Array.isArray(draft[key]) && draft[key].includes(value);
}

function toggleSelected(key, value) {
    if (!Array.isArray(draft[key])) draft[key] = [];
    if (draft[key].includes(value)) {
        draft[key] = draft[key].filter((item) => item !== value);
        return;
    }
    draft[key] = [...draft[key], value];
}

async function addCustom(key, value) {
    const normalized = String(value || "").trim();
    if (!normalized) return;
    if (!Array.isArray(draft[key])) draft[key] = [];
    if (!draft[key].includes(normalized))
        draft[key] = [...draft[key], normalized];
    if (key === "allergies")
        appendUniqueCatalogOption(allergyOptions, normalized);
    if (key === "chronicDiseases")
        appendUniqueCatalogOption(diseaseOptions, normalized);
    customInputs[key] = "";
}

function addSelectedOption(key, value, kind) {
    const normalized = String(value || "").trim();
    if (!normalized) return;
    if (!Array.isArray(draft[key])) draft[key] = [];
    if (!draft[key].includes(normalized))
        draft[key] = [...draft[key], normalized];
    if (kind === "allergy") selectedAllergyOption.value = "";
    if (kind === "disease") selectedDiseaseOption.value = "";
}

// ─── Date helpers ─────────────────────────────────────────────────────────────

function formatDateWithSlashes(value) {
    const digits = String(value || "")
        .replace(/\D/g, "")
        .slice(0, 8);
    if (digits.length <= 2) return digits;
    if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

// Convert DD/MM/YYYY → YYYY-MM-DD for the API, rejecting invalid dates (e.g. 02/50/2026)
function frenchDateToIso(value) {
    const match = String(value || "").trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) return null;
    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    const date = new Date(year, month - 1, day);
    const isValid =
        date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day;
    return isValid ? `${match[3]}-${match[2]}-${match[1]}` : null;
}

// Convert YYYY-MM-DD → DD/MM/YYYY for display
function isoDateToFrench(value) {
    if (!value) return "";
    const match = String(value).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!match) return "";
    const frenchDate = `${match[3]}/${match[2]}/${match[1]}`;
    return frenchDate;
}

function handleTreatmentDateInput(event, key) {
    treatmentDraft[key] = formatDateWithSlashes(event?.target?.value ?? "");
}

function handleTreatmentTypeInput() {
    const normalized = normalizeTreatmentText(treatmentDraft.type);
    if (!normalized) {
        treatmentDraft.name = "";
        return;
    }
    treatmentDraft.type = ensureTreatmentType(normalized);
    const available = getTreatmentNamesByType(treatmentDraft.type);
    if (treatmentDraft.name && !available.includes(treatmentDraft.name)) {
        treatmentDraft.name = "";
    }
}

// ─── Doctor email validation ──────────────────────────────────────────────────

function validateDoctorEmail() {
    if (!draft.doctorCanConsult) {
        doctorEmailError.value = "";
        return true;
    }
    const email = String(draft.doctorEmail || "").trim();
    if (!email) {
        doctorEmailError.value = "Veuillez renseigner l'email du medecin.";
        return false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        doctorEmailError.value =
            "Format invalide: utilisez un email de type nom@domaine.com.";
        return false;
    }
    doctorEmailError.value = "";
    return true;
}

// ─── Treatment editor ─────────────────────────────────────────────────────────

function resetTreatmentDraft() {
    Object.assign(treatmentDraft, {
        type: "",
        name: "",
        dose: "",
        frequency_unit: "day",
        frequency_count: 1,
        start_date: "",
        end_date: "",
    });
}

function openTreatmentEditor(index = -1) {
    showTreatmentEditor.value = true;
    editingTreatmentIndex.value = index;
    if (index < 0) {
        resetTreatmentDraft();
        return;
    }
    const item = draft.treatments[index];
    if (!item || typeof item !== "object") {
        resetTreatmentDraft();
        return;
    }
    treatmentDraft.type = item.type || "";
    treatmentDraft.name = item.name || "";
    treatmentDraft.dose = item.dose || "";
    treatmentDraft.frequency_unit = item.frequency_unit || "day";
    treatmentDraft.frequency_count = Number(item.frequency_count ?? 1);
    // draft.treatments already stores dates in DD/MM/YYYY format
    treatmentDraft.start_date = item.start_date || "";
    treatmentDraft.end_date = item.end_date || "";
    handleTreatmentTypeInput();
}

function cancelTreatmentEdit() {
    showTreatmentEditor.value = false;
    editingTreatmentIndex.value = -1;
    resetTreatmentDraft();
}

function cancelTreatmentEditWithNotice() {
    cancelTreatmentEdit();
    notifications.actionCancelled();
}

async function saveTreatmentDraft() {
    const normalizedType = normalizeTreatmentText(treatmentDraft.type);
    const normalizedName = normalizeTreatmentText(treatmentDraft.name);
    if (!normalizedType || !normalizedName) return;

    // Validate dates (must be in DD/MM/YYYY format)
    const isoStartDate = frenchDateToIso(treatmentDraft.start_date);
    const isoEndDate = frenchDateToIso(treatmentDraft.end_date);

    if (!isoStartDate || !isoEndDate) {
        notifications.warning(
            "Veuillez renseigner les dates de début et fin du traitement (format: JJ/MM/AAAA).",
        );
        return;
    }

    if (isoEndDate <= isoStartDate) {
        notifications.warning(
            "La date de fin doit être après la date de début.",
        );
        return;
    }

    const isUpdate = editingTreatmentIndex.value > -1;
    const nextTreatment = {
        type: normalizedType,
        name: normalizedName,
        dose: treatmentDraft.dose || null,
        frequency_unit: treatmentDraft.frequency_unit || "day",
        frequency_count: Number(treatmentDraft.frequency_count || 1),
        // Store as DD/MM/YYYY for consistency in draft
        start_date: treatmentDraft.start_date,
        end_date: treatmentDraft.end_date,
    };

    if (!Array.isArray(draft.treatments)) draft.treatments = [];
    if (isUpdate) {
        draft.treatments.splice(editingTreatmentIndex.value, 1, nextTreatment);
    } else {
        draft.treatments.push(nextTreatment);
    }

    ensureTreatmentType(normalizedType);
    mergeTreatmentCatalogEntry(normalizedType, normalizedName);

    try {
        await persistTreatmentCatalogEntry(normalizedType, normalizedName);
    } catch {
        notifications.warning(
            "Traitement ajoute au profil local, mais la mise a jour du catalogue partage a echoue.",
        );
    }

    cancelTreatmentEdit();
    if (isUpdate) notifications.itemUpdated();
    else notifications.itemAdded();
}

function removeTreatment(index) {
    if (!Array.isArray(draft.treatments)) return;
    draft.treatments.splice(index, 1);
    notifications.itemDeleted();
}

function requestRemoveTreatment(index) {
    pendingDeleteTreatmentIndex.value = index;
    confirmDeleteTreatmentOpen.value = true;
}

function cancelRemoveTreatment() {
    confirmDeleteTreatmentOpen.value = false;
    pendingDeleteTreatmentIndex.value = -1;
    notifications.actionCancelled();
}

function confirmRemoveTreatment() {
    const index = pendingDeleteTreatmentIndex.value;
    confirmDeleteTreatmentOpen.value = false;
    pendingDeleteTreatmentIndex.value = -1;
    if (
        index < 0 ||
        !Array.isArray(draft.treatments) ||
        index >= draft.treatments.length
    )
        return;
    removeTreatment(index);
}

// ─── Sync draft from profile (all English keys) ───────────────────────────────

function syncDraftFromProfile() {
    draft.gender = profile.gender || "";
    draft.height = profile.height ?? "";
    draft.weight = profile.weight ?? "";
    draft.bloodType = profile.blood_type || "";
    draft.goals = normalizeList(profile.goals);
    draft.allergies = normalizeList(profile.allergies);
    draft.chronicDiseases = normalizeList(profile.chronic_diseases);

    draft.allergies.forEach((item) =>
        appendUniqueCatalogOption(allergyOptions, item),
    );
    draft.chronicDiseases.forEach((item) =>
        appendUniqueCatalogOption(diseaseOptions, item),
    );

    const rawTreatments = Array.isArray(profile.treatments)
        ? profile.treatments
        : [];
    draft.treatments = rawTreatments.map((t) => ({
        type: t.type || "",
        name: t.name || "",
        dose: t.dose || null,
        frequency_unit: t.frequency_unit || "day",
        frequency_count: t.frequency_count || 1,
        // Convert YYYY-MM-DD to DD/MM/YYYY for display
        start_date: isoDateToFrench(t.start_date) || "",
        end_date: isoDateToFrench(t.end_date) || "",
    }));

    draft.treatments.forEach((item) => {
        if (!item || typeof item !== "object") return;
        const type = normalizeTreatmentText(item.type || "");
        const name = normalizeTreatmentText(item.name || "");
        if (type) ensureTreatmentType(type);
        if (type && name) mergeTreatmentCatalogEntry(type, name);
    });

    draft.smoker = Boolean(profile.smoker);
    draft.alcoholic = Boolean(profile.alcoholic);
    draft.doctorCanConsult = Boolean(profile.doctor_invited);
    draft.doctorEmail = profile.doctor_email || "";

    doctorEmailError.value = "";
    customInputs.allergies = "";
    customInputs.chronicDiseases = "";
    selectedAllergyOption.value = "";
    selectedDiseaseOption.value = "";
    cancelTreatmentEdit();
}

// ─── Section editing ──────────────────────────────────────────────────────────

function resetEditFlags() {
    editing.base = false;
    editing.health = false;
    editing.habits = false;
    editing.doctor = false;
}

function startEdit(section) {
    syncDraftFromProfile();
    clearSectionErrors();
    resetEditFlags();
    editing[section] = true;
}

function cancelEdit(section) {
    syncDraftFromProfile();
    clearSectionErrors(section);
    editing[section] = false;
    notifications.actionCancelled();
}

function clearSectionErrors(section = null) {
    if (section === null || section === "base") {
        sectionErrors.base.gender = "";
        sectionErrors.base.height = "";
        sectionErrors.base.weight = "";
        sectionErrors.base.form = [];
    }
    if (section === null || section === "health") {
        sectionErrors.health.goals = "";
        sectionErrors.health.form = [];
    }
}

function validateBaseSection() {
    clearSectionErrors("base");
    if (!draft.gender)
        sectionErrors.base.gender = "Veuillez selectionner le sexe.";
    if (draft.height === "" || draft.height === null)
        sectionErrors.base.height = "La taille est obligatoire.";
    if (draft.weight === "" || draft.weight === null)
        sectionErrors.base.weight = "Le poids est obligatoire.";
    const height = Number(draft.height);
    if (
        sectionErrors.base.height === "" &&
        (!Number.isFinite(height) || height < 80 || height > 250)
    )
        sectionErrors.base.height =
            "La taille doit etre une valeur entre 80 et 250 cm.";
    const weight = Number(draft.weight);
    if (
        sectionErrors.base.weight === "" &&
        (!Number.isFinite(weight) || weight < 35 || weight > 250)
    )
        sectionErrors.base.weight =
            "Le poids doit etre une valeur entre 35 et 250 kg.";
    return (
        !sectionErrors.base.gender &&
        !sectionErrors.base.height &&
        !sectionErrors.base.weight
    );
}

function validateHealthSection() {
    clearSectionErrors("health");
    if (!Array.isArray(draft.goals) || draft.goals.length === 0)
        sectionErrors.health.goals =
            "Veuillez selectionner au moins un objectif.";
    return !sectionErrors.health.goals;
}

// ─── Build API payload (all English keys) ────────────────────────────────────

function buildPayload() {
    const goals = normalizeList(draft.goals);
    const allergies = normalizeList(draft.allergies);
    const chronicDiseases = normalizeList(draft.chronicDiseases);
    const treatments = Array.isArray(draft.treatments) ? draft.treatments : [];
    const doctorCanConsult = Boolean(draft.doctorCanConsult);

    return {
        gender:
            typeof draft.gender === "string"
                ? draft.gender.toLowerCase().trim()
                : draft.gender,
        height: draft.height === "" ? null : Number(draft.height),
        weight: draft.weight === "" ? null : Number(draft.weight),
        blood_type: draft.bloodType || null,
        goals,
        allergies,
        chronic_diseases: chronicDiseases,
        treatments: treatments
            .map((t) => ({
                type: t?.type ?? null,
                name: t?.name ?? null,
                dose: t?.dose ?? null,
                frequency_unit: t?.frequency_unit ?? null,
                frequency_count: t?.frequency_count
                    ? Number(t.frequency_count)
                    : null,
                // Convert DD/MM/YYYY to YYYY-MM-DD for the API
                start_date: frenchDateToIso(t?.start_date) ?? null,
                end_date: frenchDateToIso(t?.end_date) ?? null,
            }))
            .filter((t) => t.type),
        smoker: Boolean(draft.smoker),
        alcoholic: Boolean(draft.alcoholic),
        doctor_invited: doctorCanConsult,
        doctor_email: doctorCanConsult ? draft.doctorEmail || null : null,
    };
}

// ─── Save section ─────────────────────────────────────────────────────────────

async function saveSection(section) {
    if (section === "base" && !validateBaseSection()) {
        notifications.warning("Veuillez corriger les champs en erreur.");
        return;
    }
    if (section === "health" && !validateHealthSection()) {
        notifications.warning("Veuillez corriger les champs en erreur.");
        return;
    }
    if (section === "doctor" && !validateDoctorEmail()) return;

    savingSection.value = section;
    clearSectionErrors(section);
    try {
        const response = await api.post("/health-profile", buildPayload());
        Object.assign(profile, response?.data?.data || {});

        // Backend returns response.data.user with `name` and `date_of_birth`
        const apiUser = response?.data?.user || {};
        user.name = apiUser.name || user.name;
        user.dateOfBirth = apiUser.date_of_birth || user.dateOfBirth;

        authStore.setHealthProfile(true);
        syncDraftFromProfile();
        editing[section] = false;
        notifications.itemUpdated();
    } catch (e) {
        if (e?.response?.status === 401) {
            authStore.removeToken();
            router.replace({ name: "register" });
            return;
        }
        if (e?.response?.status === 422 && e?.response?.data?.errors) {
            if (
                section === "doctor" &&
                (e.response.data.errors?.doctor_email ||
                    e.response.data.errors?.medecin_email)
            ) {
                const doctorFieldError =
                    e.response.data.errors.doctor_email ||
                    e.response.data.errors.medecin_email;
                doctorEmailError.value = Array.isArray(doctorFieldError)
                    ? doctorFieldError[0]
                    : "Email medecin invalide.";
                notifications.warning(doctorEmailError.value);
                return;
            }
            clearSectionErrors();
            const backendErrors = e.response.data.errors || {};
            const mappedMessages = [];
            if (backendErrors.gender) {
                sectionErrors.base.gender = "Veuillez selectionner le sexe.";
                mappedMessages.push(sectionErrors.base.gender);
            }
            if (backendErrors.height) {
                sectionErrors.base.height =
                    "La taille doit etre une valeur entre 80 et 250 cm.";
                mappedMessages.push(sectionErrors.base.height);
            }
            if (backendErrors.weight) {
                sectionErrors.base.weight =
                    "Le poids doit etre une valeur entre 35 et 250 kg.";
                mappedMessages.push(sectionErrors.base.weight);
            }
            if (backendErrors.goals) {
                sectionErrors.health.goals =
                    "Veuillez selectionner au moins un objectif.";
                mappedMessages.push(sectionErrors.health.goals);
            }
            const fallbackMessages = Object.values(backendErrors)
                .flatMap((entry) => (Array.isArray(entry) ? entry : [entry]))
                .filter(Boolean)
                .map(String);
            const finalMessages = mappedMessages.length
                ? [...new Set(mappedMessages)]
                : fallbackMessages;
            if (sectionErrors[section]) {
                sectionErrors[section].form = finalMessages.length
                    ? finalMessages
                    : ["Validation invalide."];
            }
            const hasBaseErrors = Boolean(
                sectionErrors.base.gender ||
                sectionErrors.base.height ||
                sectionErrors.base.weight,
            );
            const hasHealthErrors = Boolean(sectionErrors.health.goals);
            if (hasBaseErrors && !editing.base) {
                resetEditFlags();
                editing.base = true;
            } else if (hasHealthErrors && !editing.health) {
                resetEditFlags();
                editing.health = true;
            }
            notifications.warning(
                "Veuillez corriger les champs en erreur.",
            );
        } else {
            notifications.error("Erreur lors de la sauvegarde du profil.");
        }
    } finally {
        savingSection.value = "";
    }
}

// ─── Mount ────────────────────────────────────────────────────────────────────

onMounted(async () => {
    try {
        await loadTreatmentCatalog();
        const response = await api.get("/health-profile");
        Object.assign(profile, response?.data?.data || {});

        // Backend returns response.data.user with English fields: name, date_of_birth
        const apiUser = response?.data?.user || {};

        user.name = apiUser.name || "";
        user.dateOfBirth = apiUser.date_of_birth || "";

        authStore.setHealthProfile(Boolean(response?.data?.data));
        syncDraftFromProfile();
    } catch (e) {
        if (e?.response?.status === 401) {
            authStore.removeToken();
            router.replace({ name: "register" });
            return;
        }
        loadError.value = "Impossible de charger les données du profil.";
    } finally {
        loading.value = false;
    }
});
</script>
