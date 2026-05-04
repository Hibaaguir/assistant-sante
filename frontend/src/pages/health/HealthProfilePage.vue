<template>
    <div class="w-full px-4 py-4 sm:px-6 lg:px-8">
        <header class="mb-4 flex items-start gap-3 sm:gap-4">
            <div
                class="mt-1 flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 sm:h-12 sm:w-12"
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
                <Typography tag="h1" variant="h1-style" class="text-black">
                    Profil de santé
                </Typography>
                <Typography tag="h3" variant="h4-style">
                    Gérez vos informations de santé
                </Typography>
            </div>
        </header>

        <Typography v-if="loading" tag="h5" variant="secondary">
            Chargement du profil...
        </Typography>

        <div
            v-else-if="loadError"
            class="rounded-3xl border border-red-200 bg-red-50/90 p-6 text-sm text-red-700"
        >
            {{ loadError }}
        </div>

        <div v-else class="grid gap-4 lg:grid-cols-2">
            <!-- Section: Informations de base -->
            <section
                class="min-h-[250px] rounded-[14px] border-2 border-blue-300 bg-white p-4 sm:p-5 relative overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-blue-400"
            >
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
                        <Typography tag="h3" variant="h3-style">
                            Informations de base
                        </Typography>
                    </div>
                    <BaseButton
                        v-if="!editing.base"
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="startEdit('base')"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                    </BaseButton>
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
                        label="Genre"
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
                        :value="
                            profile.current_weight
                                ? `${profile.current_weight} kg`
                                : '-'
                        "
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
                        <Typography
                            tag="label"
                            variant="secondary"
                            class="mb-1 block"
                            >Nom</Typography
                        >
                        <input
                            :value="user.name || ''"
                            disabled
                            class="h-12 w-full rounded-xl border border-slate-200 bg-slate-100 px-5 text-base text-slate-700 font-medium"
                        />
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <Typography
                                tag="label"
                                variant="secondary"
                                class="mb-1 block"
                                >Genre</Typography
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
                            <Typography
                                tag="label"
                                variant="secondary"
                                class="mb-1 block"
                                >Taille (cm)</Typography
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
                        <Typography
                            tag="label"
                            variant="secondary"
                            class="mb-1 block"
                            >Poids (kg)</Typography
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
                        <BaseButton
                            type="submit"
                            variant="save"
                            size="md"
                            :disabled="savingSection === 'base'"
                        >
                            Enregistrer
                        </BaseButton>
                        <BaseButton
                            type="button"
                            variant="cancel"
                            size="md"
                            @click="cancelEdit('base')"
                        >
                            Annuler
                        </BaseButton>
                    </div>
                </form>
            </section>

            <!-- Section: Santé -->
            <section
                class="min-h-[250px] rounded-[14px] border-2 border-blue-300 bg-white p-4 sm:p-5 relative overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-blue-400"
            >
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
                        <Typography tag="h3" variant="h3-style">
                            Santé
                        </Typography>
                    </div>
                    <BaseButton
                        v-if="!editing.health"
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="startEdit('health')"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                    </BaseButton>
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
                        <Typography
                            tag="label"
                            variant="secondary"
                            class="mb-1 block"
                            >Groupe sanguin</Typography
                        >
                        <select
                            v-model="draft.bloodType"
                            class="h-12 w-full rounded-xl border border-slate-200 bg-slate-100 px-5 text-base font-medium"
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
                        <Typography
                            tag="label"
                            variant="secondary"
                            class="mb-1 block"
                            >Objectifs</Typography
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
                                <BaseButton
                                    v-for="goal in goalOptions"
                                    :key="goal"
                                    type="button"
                                    :variant="
                                        isSelected('goals', goal)
                                            ? 'secondary'
                                            : 'outline'
                                    "
                                    size="sm"
                                    @click="toggleSelected('goals', goal)"
                                >
                                    {{ goal }}
                                </BaseButton>
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
                        <Typography
                            tag="label"
                            variant="secondary"
                            class="mb-1 block"
                            >Allergies</Typography
                        >
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <div class="flex gap-2">
                                <select
                                    v-model="selectedAllergyOption"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-base font-medium"
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
                                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm font-medium text-slate-800"
                                >
                                    {{ item }}
                                    <button
                                        type="button"
                                        class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-red-500 hover:bg-red-200 transition-colors"
                                        @click="toggleSelected('allergies', item)"
                                    >×</button>
                                </span>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <input
                                    v-model="customInputs.allergies"
                                    class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm"
                                    placeholder="Ajouter une allergie si absente..."
                                />
                                <BaseButton
                                    type="button"
                                    variant="add"
                                    size="sm"
                                    @click="
                                        addCustom(
                                            'allergies',
                                            customInputs.allergies,
                                        )
                                    "
                                >
                                    Ajouter
                                </BaseButton>
                            </div>
                        </div>
                    </div>
                    <div>
                        <Typography
                            tag="label"
                            variant="secondary"
                            class="mb-1 block"
                            >Maladies chroniques</Typography
                        >
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3"
                        >
                            <div class="flex gap-2">
                                <select
                                    v-model="selectedDiseaseOption"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-base font-medium"
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
                                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm font-medium text-slate-800"
                                >
                                    {{ item }}
                                    <button
                                        type="button"
                                        class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-red-500 hover:bg-red-200 transition-colors"
                                        @click="toggleSelected('chronicDiseases', item)"
                                    >×</button>
                                </span>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <input
                                    v-model="customInputs.chronicDiseases"
                                    class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm"
                                    placeholder="Ajouter une maladie si absente..."
                                />
                                <BaseButton
                                    type="button"
                                    variant="add"
                                    size="sm"
                                    @click="
                                        addCustom(
                                            'chronicDiseases',
                                            customInputs.chronicDiseases,
                                        )
                                    "
                                >
                                    Ajouter
                                </BaseButton>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <BaseButton
                            type="submit"
                            variant="save"
                            size="md"
                            :disabled="savingSection === 'health'"
                        >
                            Enregistrer
                        </BaseButton>
                        <BaseButton
                            type="button"
                            variant="cancel"
                            size="md"
                            @click="cancelEdit('health')"
                        >
                            Annuler
                        </BaseButton>
                    </div>
                </form>
            </section>

            <!-- Section: Habitudes -->
            <section
                class="min-h-[250px] rounded-[14px] border-2 border-blue-300 bg-white p-4 sm:p-5 relative overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-blue-400"
            >
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
                        <Typography tag="h3" variant="h3-style">
                            Habitudes
                        </Typography>
                    </div>
                    <BaseButton
                        v-if="!editing.habits"
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="startEdit('habits')"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                    </BaseButton>
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
                            <Typography
                                tag="label"
                                variant="secondary"
                                class="text-sm font-semibold"
                                >Fumeur</Typography
                            >
                            <button
                                type="button"
                                :aria-pressed="draft.smoker"
                                class="relative h-8 w-14 rounded-full transition-colors"
                                :class="draft.smoker ? 'bg-blue-500' : 'bg-[#c7d2e0]'"
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
                            <Typography
                                tag="label"
                                variant="secondary"
                                class="text-sm font-semibold"
                                >Alcool</Typography
                            >
                            <button
                                type="button"
                                :aria-pressed="draft.alcoholic"
                                class="relative h-8 w-14 rounded-full transition-colors"
                                :class="draft.alcoholic ? 'bg-blue-500' : 'bg-[#c7d2e0]'"
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
                        class="rounded-xl border border-slate-200 bg-slate-50 p-5 sm:p-6"
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <Typography tag="h3" variant="h4-style">
                                Traitements
                            </Typography>
                            <BaseButton
                                type="button"
                                variant="add"
                                size="md"
                                @click="openTreatmentEditor()"
                            >
                                Ajouter
                            </BaseButton>
                        </div>

                        <div
                            v-if="showTreatmentEditor"
                            class="space-y-4 rounded-lg border border-slate-200 bg-white p-5"
                        >
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Typography
                                        tag="label"
                                        variant="secondary"
                                        class="mb-2 block text-sm font-semibold"
                                        >Type
                                        <span class="text-red-500"
                                            >*</span
                                        ></Typography
                                    >
                                    <button
                                        type="button"
                                        class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-left text-base text-slate-800 font-medium transition hover:bg-slate-100"
                                        @click="
                                            openTreatmentTypeDropdown =
                                                !openTreatmentTypeDropdown
                                        "
                                    >
                                        {{
                                            treatmentDraft.type ||
                                            "Selectionner un type"
                                        }}
                                    </button>
                                    <div
                                        v-if="openTreatmentTypeDropdown"
                                        class="mt-3 rounded-xl border border-slate-200 bg-white shadow-sm"
                                    >
                                        <div
                                            class="border-b border-slate-100 p-3"
                                        >
                                            <input
                                                v-model="queryTreatmentTypes"
                                                class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm"
                                                placeholder="Rechercher un type..."
                                            />
                                        </div>
                                        <div class="max-h-48 overflow-auto p-2">
                                            <button
                                                v-for="type in filteredTreatmentTypes"
                                                :key="`type-opt-${type}`"
                                                type="button"
                                                class="w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-800 hover:bg-slate-100"
                                                @click="
                                                    selectTreatmentType(type)
                                                "
                                            >
                                                {{ type }}
                                            </button>
                                            <p
                                                v-if="
                                                    !filteredTreatmentTypes.length
                                                "
                                                class="px-3 py-2 text-sm text-slate-500"
                                            >
                                                Aucun type disponible.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <input
                                            v-model="customTreatmentInputs.type"
                                            class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm"
                                            placeholder="Ajouter un type si absent"
                                        />
                                        <BaseButton
                                            type="button"
                                            variant="add"
                                            size="sm"
                                            @click="addCustomTreatmentType"
                                        >
                                            Ajouter
                                        </BaseButton>
                                    </div>
                                </div>
                                <div>
                                    <Typography
                                        tag="label"
                                        variant="secondary"
                                        class="mb-2 block text-sm font-semibold"
                                        >Nom
                                        <span class="text-red-500"
                                            >*</span
                                        ></Typography
                                    >
                                    <button
                                        type="button"
                                        :disabled="!treatmentDraft.type"
                                        class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-left text-base font-medium text-slate-800 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:bg-slate-100"
                                        @click="toggleTreatmentNameDropdown"
                                    >
                                        {{
                                            treatmentDraft.name ||
                                            (treatmentDraft.type
                                                ? "Selectionner un nom"
                                                : "Selectionnez d'abord un type")
                                        }}
                                    </button>
                                    <div
                                        v-if="
                                            openTreatmentNameDropdown &&
                                            treatmentDraft.type
                                        "
                                        class="mt-3 rounded-xl border border-slate-200 bg-white shadow-sm"
                                    >
                                        <div
                                            class="border-b border-slate-100 p-3"
                                        >
                                            <input
                                                v-model="queryTreatmentNames"
                                                class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm"
                                                placeholder="Rechercher un nom..."
                                            />
                                        </div>
                                        <div class="max-h-48 overflow-auto p-2">
                                            <button
                                                v-for="name in filteredTreatmentNames"
                                                :key="`name-opt-${name}`"
                                                type="button"
                                                class="w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-800 hover:bg-slate-100"
                                                @click="
                                                    selectTreatmentName(name)
                                                "
                                            >
                                                {{ name }}
                                            </button>
                                            <p
                                                v-if="
                                                    !filteredTreatmentNames.length
                                                "
                                                class="px-3 py-2 text-sm text-slate-500"
                                            >
                                                Aucun nom disponible pour ce
                                                type.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <input
                                            v-model="customTreatmentInputs.name"
                                            :disabled="!treatmentDraft.type"
                                            class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium disabled:cursor-not-allowed disabled:bg-slate-100"
                                            placeholder="Ajouter un nom si absent"
                                        />
                                        <BaseButton
                                            type="button"
                                            :disabled="!treatmentDraft.type"
                                            variant="add"
                                            size="sm"
                                            @click="addCustomTreatmentName"
                                        >
                                            Ajouter
                                        </BaseButton>
                                    </div>
                                </div>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Typography
                                        tag="label"
                                        variant="secondary"
                                        class="mb-2 block text-sm font-semibold"
                                        >Dose
                                        <span class="text-red-500"
                                            >*</span
                                        ></Typography
                                    >
                                    <input
                                        v-model="treatmentDraft.dose"
                                        class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-base font-medium"
                                        placeholder="Ex: 500mg"
                                    />
                                </div>
                                <div>
                                    <Typography
                                        tag="label"
                                        variant="secondary"
                                        class="mb-2 block text-sm font-semibold"
                                        >Fréquence
                                        <span class="text-red-500"
                                            >*</span
                                        ></Typography
                                    >
                                    <div class="grid grid-cols-2 gap-3">
                                        <select
                                            v-model="
                                                treatmentDraft.frequency_unit
                                            "
                                            class="h-12 rounded-lg border border-slate-200 bg-slate-50 px-4 text-base font-medium"
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
                                            class="h-12 rounded-lg border border-slate-200 bg-slate-50 px-4 text-base font-medium"
                                            placeholder="Nb de prises"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Typography
                                        tag="label"
                                        variant="secondary"
                                        class="mb-2 block text-sm font-semibold"
                                        >Début (JJ/MM/AAAA)
                                        <span class="text-red-500"
                                            >*</span
                                        ></Typography
                                    >
                                    <input
                                        :value="treatmentDraft.start_date"
                                        class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-base font-medium"
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
                                    <Typography
                                        tag="label"
                                        variant="secondary"
                                        class="mb-2 block text-sm font-semibold"
                                        >Fin (JJ/MM/AAAA)
                                        <span class="text-red-500"
                                            >*</span
                                        ></Typography
                                    >
                                    <input
                                        :value="treatmentDraft.end_date"
                                        class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-base font-medium"
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
                            <div class="grid grid-cols-2 gap-3">
                                <BaseButton
                                    type="button"
                                    variant="primary"
                                    size="md"
                                    @click="saveTreatmentDraft"
                                >
                                    {{
                                        editingTreatmentIndex > -1
                                            ? "Mettre à jour"
                                            : "Ajouter"
                                    }}
                                </BaseButton>
                                <BaseButton
                                    type="button"
                                    variant="outline"
                                    size="md"
                                    @click="cancelTreatmentEditWithNotice"
                                >
                                    Annuler
                                </BaseButton>
                            </div>
                        </div>

                        <div
                            v-if="draft.treatments.length"
                            class="mt-4 space-y-3"
                        >
                            <div
                                v-for="(item, index) in draft.treatments"
                                :key="`draft-treatment-${index}-${item.type}-${item.name}`"
                                class="flex items-start justify-between rounded-lg border border-slate-200 bg-white px-4 py-3"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-base font-semibold text-slate-900"
                                    >
                                        {{ item.name || "Traitement" }}
                                    </p>
                                    <p
                                        class="mt-1 text-sm text-slate-600 font-medium"
                                    >
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
                                <div class="ml-4 flex items-center gap-3">
                                    <BaseButton
                                        type="button"
                                        variant="update"
                                        size="sm"
                                        @click="openTreatmentEditor(index)"
                                    >
                                        Modifier
                                    </BaseButton>
                                    <BaseButton
                                        type="button"
                                        variant="delete"
                                        size="sm"
                                        @click="requestRemoveTreatment(index)"
                                    >
                                        Retirer
                                    </BaseButton>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-slate-500 font-medium">
                            Aucun traitement ajouté.
                        </p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <BaseButton
                            type="submit"
                            variant="save"
                            size="md"
                            :disabled="savingSection === 'habits'"
                        >
                            Enregistrer
                        </BaseButton>
                        <BaseButton
                            type="button"
                            variant="cancel"
                            size="md"
                            @click="cancelEdit('habits')"
                        >
                            Annuler
                        </BaseButton>
                    </div>
                </form>
            </section>

            <!-- Section: Suivi médecin -->
            <section
                class="min-h-[250px] rounded-[14px] border-2 border-blue-300 bg-white p-4 sm:p-5 relative overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-blue-400"
            >
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-700"
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
                        <Typography tag="h3" variant="h3-style">
                            Suivi médecin
                        </Typography>
                    </div>
                    <BaseButton
                        v-if="!editing.doctor"
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="startEdit('doctor')"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                    </BaseButton>
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
                        <Typography
                            tag="label"
                            variant="secondary"
                            class="mb-1 block"
                            >Partager mon profil avec mon médecin</Typography
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
                        <Typography
                            tag="label"
                            variant="secondary"
                            class="mb-1 block"
                            >Email du médecin</Typography
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
                        <BaseButton
                            type="submit"
                            variant="save"
                            size="md"
                            :disabled="savingSection === 'doctor'"
                        >
                            Enregistrer
                        </BaseButton>
                        <BaseButton
                            type="button"
                            variant="cancel"
                            size="md"
                            @click="cancelEdit('doctor')"
                        >
                            Annuler
                        </BaseButton>
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
import { useHealthProfileStore } from "@/stores/healthProfile";
import HealthFieldRow from "@/components/health/HealthFieldRow.vue";
import { useNotificationsStore } from "@/stores/notifications";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import ConfirmationDialog from "@/components/ui/ConfirmationDialog.vue";

// ─── Stores & Router ──────────────────────────────────────────────────────────
const router = useRouter();
const authStore = useAuthStore();
const healthProfileStore = useHealthProfileStore();
const notifications = useNotificationsStore();

// ─── Page state ───────────────────────────────────────────────────────────────
const loading = ref(true); // true while the page is loading data
const loadError = ref(""); // shown when the initial API call fails
const savingSection = ref(""); // name of the section currently being saved
const doctorEmailError = ref(""); // error message for the doctor email field

// ─── Profile & user data ──────────────────────────────────────────────────────
// `profile` holds the health data from the API
// `user` holds basic account info (name, birth date)
const profile = reactive({});
const user = reactive({ name: "", dateOfBirth: "" });

// ─── Section edit state ───────────────────────────────────────────────────────
// editing.X = true means the user has clicked the pencil for that section
const editing = reactive({
    base: false,
    health: false,
    habits: false,
    doctor: false,
});

// ─── Field-level error messages ───────────────────────────────────────────────
const sectionErrors = reactive({
    base: { gender: "", height: "", weight: "", form: [] },
    health: { goals: "", form: [] },
});

// ─── Draft (what the user is currently editing) ───────────────────────────────
// Changes stay here until the user hits "Enregistrer"
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

// ─── Dropdown option lists ────────────────────────────────────────────────────
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

// ─── Treatment autocomplete catalog ──────────────────────────────────────────
const treatmentTypes = ref([]); // known treatment types
const treatmentNamesByType = reactive({}); // { type: [name1, name2, ...] }

// ─── Multi-select UI state ────────────────────────────────────────────────────
const customInputs = reactive({ allergies: "", chronicDiseases: "" });
const selectedAllergyOption = ref("");
const selectedDiseaseOption = ref("");
const customTreatmentInputs = reactive({ type: "", name: "" });
const openTreatmentTypeDropdown = ref(false);
const openTreatmentNameDropdown = ref(false);
const queryTreatmentTypes = ref("");
const queryTreatmentNames = ref("");

// ─── Treatment editor state ───────────────────────────────────────────────────
const showTreatmentEditor = ref(false);
const editingTreatmentIndex = ref(-1); // -1 = new, >= 0 = editing existing
const confirmDeleteTreatmentOpen = ref(false);
const pendingDeleteTreatmentIndex = ref(-1);

// Blank form for the treatment editor
const treatmentDraft = reactive({
    type: "",
    name: "",
    dose: "",
    frequency_unit: "day",
    frequency_count: 1,
    start_date: "",
    end_date: "",
});

// ─ Valeurs calculées

// Calculate the user's age from their birth date
const computedAge = computed(() => {
    if (!user.dateOfBirth) return "";

    const dob = new Date(user.dateOfBirth);
    if (isNaN(dob.getTime())) return "";

    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();

    // Has the birthday happened yet this year?
    const birthdayPassed =
        today.getMonth() > dob.getMonth() ||
        (today.getMonth() === dob.getMonth() &&
            today.getDate() >= dob.getDate());

    if (!birthdayPassed) age -= 1;

    return age >= 0 ? `${age} ans` : "";
});

// Available names for the type currently typed in the treatment editor
const treatmentNamesForSelectedType = computed(() =>
    getTreatmentNamesByType(treatmentDraft.type),
);

const filteredTreatmentTypes = computed(() => {
    const q = queryTreatmentTypes.value.trim().toLowerCase();
    const list = Array.isArray(treatmentTypes.value)
        ? treatmentTypes.value
        : [];
    return q ? list.filter((item) => item.toLowerCase().includes(q)) : list;
});

const filteredTreatmentNames = computed(() => {
    const q = queryTreatmentNames.value.trim().toLowerCase();
    const list = treatmentNamesForSelectedType.value;
    return q ? list.filter((item) => item.toLowerCase().includes(q)) : list;
});

// ─ Helpers d'affichage

// Convert a boolean to a human-readable "Oui" / "Non"
function yesNo(value) {
    return value ? "Oui" : "Non";
}

// Join an array into a comma-separated string — returns "-" if empty
function joinList(value) {
    if (!Array.isArray(value) || value.length === 0) return "-";
    return value.filter(Boolean).join(", ");
}

// Keep only non-empty strings from an array
function normalizeList(value) {
    if (!Array.isArray(value)) return [];
    return value.map((item) => String(item || "").trim()).filter(Boolean);
}

// Build a short summary of treatments for the read-only view
function treatmentsSummary(treatments) {
    if (!Array.isArray(treatments) || treatments.length === 0) return "-";
    const names = treatments
        .map((t) => (t && typeof t === "object" ? t.name || t.type || "" : ""))
        .filter(Boolean);
    return names.length
        ? names.join(", ")
        : `${treatments.length} traitement(s)`;
}

// ─ Helpers pour les dates

// Auto-format digits as DD/MM/YYYY while the user types
function formatDateWithSlashes(value) {
    const digits = String(value || "")
        .replace(/\D/g, "")
        .slice(0, 8);
    if (digits.length <= 2) return digits;
    if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

// Convert DD/MM/YYYY → YYYY-MM-DD for the API — returns null if invalid
function frenchDateToIso(value) {
    const text = String(value || "").trim();
    // La date doit être au format JJ/MM/AAAA (ex: 25/03/2026)
    const parts = text.split("/");
    if (parts.length !== 3) return null;

    const day = Number(parts[0]);
    const month = Number(parts[1]);
    const year = Number(parts[2]);

    // Vérifier que les parties sont des chiffres valides
    if (isNaN(day) || isNaN(month) || isNaN(year)) return null;

    // Vérifier que la date est réelle (ex: 31/02 est impossible)
    const date = new Date(year, month - 1, day);
    const isValid =
        date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day;

    if (!isValid) return null;

    // Retourner au format AAAA-MM-JJ (format attendu par l'API)
    const dd = String(day).padStart(2, "0");
    const mm = String(month).padStart(2, "0");
    return `${year}-${mm}-${dd}`;
}

// Convert YYYY-MM-DD → DD/MM/YYYY for display in forms
function isoDateToFrench(value) {
    if (!value) return "";
    const match = String(value)
        .trim()
        .match(/^(\d{4})-(\d{2})-(\d{2})/);
    return match ? `${match[3]}/${match[2]}/${match[1]}` : "";
}

// Called on every keystroke in a date field — applies the slashes automatically
function handleTreatmentDateInput(event, key) {
    treatmentDraft[key] = formatDateWithSlashes(event?.target?.value ?? "");
}

// ─ Catalogue des traitements

// Trim and collapse extra whitespace from a text value
function normalizeTreatmentText(value) {
    return String(value || "")
        .trim()
        .replace(/\s+/g, " ");
}

// Add a value to a list ref only if it doesn't already exist (case-insensitive)
function appendUniqueCatalogOption(listRef, value) {
    const text = normalizeTreatmentText(value);
    if (!text) return;
    const textLower = text.toLowerCase();
    const alreadyExists = listRef.value.some(
        (item) => item.toLowerCase() === textLower,
    );
    if (alreadyExists) return;
    listRef.value = [...listRef.value, text].sort((a, b) => a.localeCompare(b));
}

// Register a treatment type in the local catalog (create its names list if missing)
function ensureTreatmentType(type) {
    const text = normalizeTreatmentText(type);
    if (!text) return "";
    if (!treatmentTypes.value.includes(text)) {
        treatmentTypes.value = [...treatmentTypes.value, text].sort((a, b) =>
            a.localeCompare(b, "fr", { sensitivity: "base" }),
        );
    }
    if (!Array.isArray(treatmentNamesByType[text]))
        treatmentNamesByType[text] = [];
    return text;
}

// Add a treatment name to the catalog under its type
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

// Merge the full catalog received from the API into our local lists
function applyTreatmentCatalog(catalog) {
    const types = Array.isArray(catalog?.types) ? catalog.types : [];
    types.forEach((type) => ensureTreatmentType(type));
    const namesByType =
        typeof catalog?.names_by_type === "object" ? catalog.names_by_type : {};
    Object.entries(namesByType).forEach(([type, names]) => {
        ensureTreatmentType(type);
        (Array.isArray(names) ? names : []).forEach((name) =>
            mergeTreatmentCatalogEntry(type, name),
        );
    });
}

// Fetch the treatment catalog from the server (fails silently — it's optional)
async function loadTreatmentCatalog() {
    try {
        const response = await api.get("/treatment-catalog");
        applyTreatmentCatalog(response?.data?.data || {});
    } catch (_) {
        // Catalog not available — the form still works without it
    }
}

// Get the list of known names for a given treatment type
function getTreatmentNamesByType(type) {
    const normalized = ensureTreatmentType(type);
    if (!normalized) return [];
    return treatmentNamesByType[normalized];
}

// Persist a new type/name to the shared catalog on the server
async function persistTreatmentCatalogEntry(type, name = "") {
    const normalizedType = normalizeTreatmentText(type);
    if (!normalizedType) return;
    await api.post("/treatment-catalog", {
        type: normalizedType,
        name: normalizeTreatmentText(name) || null,
    });
}

// ─ Helpers pour les sélections multiples

// Check if a value is already in a draft list field
function isSelected(key, value) {
    return Array.isArray(draft[key]) && draft[key].includes(value);
}

// Add or remove a value from a draft list field
function toggleSelected(key, value) {
    if (!Array.isArray(draft[key])) draft[key] = [];
    if (draft[key].includes(value)) {
        draft[key] = draft[key].filter((item) => item !== value);
    } else {
        draft[key] = [...draft[key], value];
    }
}

// Add a custom value typed by the user (not from the dropdown)
async function addCustom(key, value) {
    const text = String(value || "").trim();
    if (!text) return;
    if (!Array.isArray(draft[key])) draft[key] = [];
    if (!draft[key].includes(text)) draft[key] = [...draft[key], text];
    // Keep the dropdown list up to date for future use
    if (key === "allergies") appendUniqueCatalogOption(allergyOptions, text);
    if (key === "chronicDiseases")
        appendUniqueCatalogOption(diseaseOptions, text);
    customInputs[key] = "";
}

// Add a value chosen from a dropdown
function addSelectedOption(key, value, kind) {
    const text = String(value || "").trim();
    if (!text) return;
    if (!Array.isArray(draft[key])) draft[key] = [];
    if (!draft[key].includes(text)) draft[key] = [...draft[key], text];
    // Reset the dropdown after selection
    if (kind === "allergy") selectedAllergyOption.value = "";
    if (kind === "disease") selectedDiseaseOption.value = "";
}

// ─ Éditeur de traitement

// When the type field changes, clear the name if it no longer belongs to the new type
function handleTreatmentTypeInput() {
    const text = normalizeTreatmentText(treatmentDraft.type);
    if (!text) {
        treatmentDraft.name = "";
        openTreatmentNameDropdown.value = false;
        queryTreatmentNames.value = "";
        return;
    }
    treatmentDraft.type = ensureTreatmentType(text);
    const available = getTreatmentNamesByType(treatmentDraft.type);
    if (treatmentDraft.name && !available.includes(treatmentDraft.name)) {
        treatmentDraft.name = "";
    }
    queryTreatmentNames.value = "";
}

function selectTreatmentType(type) {
    treatmentDraft.type = normalizeTreatmentText(type);
    handleTreatmentTypeInput();
    openTreatmentTypeDropdown.value = false;
    openTreatmentNameDropdown.value = false;
    queryTreatmentTypes.value = "";
}

function toggleTreatmentNameDropdown() {
    if (!treatmentDraft.type) return;
    openTreatmentNameDropdown.value = !openTreatmentNameDropdown.value;
}

function selectTreatmentName(name) {
    treatmentDraft.name = normalizeTreatmentText(name);
    openTreatmentNameDropdown.value = false;
    queryTreatmentNames.value = "";
}

function addCustomTreatmentType() {
    const text = normalizeTreatmentText(customTreatmentInputs.type);
    if (!text) return;
    treatmentDraft.type = ensureTreatmentType(text);
    customTreatmentInputs.type = "";
    handleTreatmentTypeInput();
    openTreatmentTypeDropdown.value = false;
}

function addCustomTreatmentName() {
    const name = normalizeTreatmentText(customTreatmentInputs.name);
    const type = normalizeTreatmentText(treatmentDraft.type);
    if (!type || !name) return;
    mergeTreatmentCatalogEntry(type, name);
    treatmentDraft.name = name;
    customTreatmentInputs.name = "";
    openTreatmentNameDropdown.value = false;
    queryTreatmentNames.value = "";
}

// Clear all treatment editor fields
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
    customTreatmentInputs.type = "";
    customTreatmentInputs.name = "";
    openTreatmentTypeDropdown.value = false;
    openTreatmentNameDropdown.value = false;
    queryTreatmentTypes.value = "";
    queryTreatmentNames.value = "";
}

// Open the treatment editor — pre-fill if editing an existing one
function openTreatmentEditor(index = -1) {
    showTreatmentEditor.value = true;
    editingTreatmentIndex.value = index;

    // No valid index → blank form for a new treatment
    if (index < 0 || !draft.treatments[index]) {
        resetTreatmentDraft();
        return;
    }

    // Pre-fill with the existing treatment's data
    const item = draft.treatments[index];
    treatmentDraft.type = item.type || "";
    treatmentDraft.name = item.name || "";
    treatmentDraft.dose = item.dose || "";
    treatmentDraft.frequency_unit = item.frequency_unit || "day";
    treatmentDraft.frequency_count = Number(item.frequency_count ?? 1);
    treatmentDraft.start_date = item.start_date || "";
    treatmentDraft.end_date = item.end_date || "";
    handleTreatmentTypeInput();
}

// Close the editor and reset all its fields
function cancelTreatmentEdit() {
    showTreatmentEditor.value = false;
    editingTreatmentIndex.value = -1;
    resetTreatmentDraft();
}

// Close the editor and notify the user the action was cancelled
function cancelTreatmentEditWithNotice() {
    cancelTreatmentEdit();
    notifications.actionCancelled();
}

// Save (add or update) a treatment from the editor form
async function saveTreatmentDraft() {
    const type = normalizeTreatmentText(treatmentDraft.type);
    const name = normalizeTreatmentText(treatmentDraft.name);
    const dose = normalizeTreatmentText(treatmentDraft.dose);
    const frequencyUnit = normalizeTreatmentText(treatmentDraft.frequency_unit);
    const frequencyCount = Number(treatmentDraft.frequency_count);

    if (
        !type ||
        !name ||
        !dose ||
        !frequencyUnit ||
        !Number.isFinite(frequencyCount) ||
        frequencyCount < 1
    ) {
        notifications.warning(
            "Tous les champs du traitement sont obligatoires.",
        );
        return;
    }

    // Both dates are required and must be valid
    const isoStart = frenchDateToIso(treatmentDraft.start_date);
    const isoEnd = frenchDateToIso(treatmentDraft.end_date);
    if (!isoStart || !isoEnd) {
        notifications.warning(
            "Veuillez renseigner les dates de début et fin du traitement (format: JJ/MM/AAAA).",
        );
        return;
    }
    if (isoEnd <= isoStart) {
        notifications.warning(
            "La date de fin doit être après la date de début.",
        );
        return;
    }

    // Build the treatment object (dates stay in DD/MM/YYYY for the draft)
    const treatment = {
        type,
        name,
        dose,
        frequency_unit: frequencyUnit,
        frequency_count: Math.round(frequencyCount),
        start_date: treatmentDraft.start_date,
        end_date: treatmentDraft.end_date,
    };

    if (!Array.isArray(draft.treatments)) draft.treatments = [];

    const isUpdate = editingTreatmentIndex.value > -1;
    if (isUpdate) {
        draft.treatments.splice(editingTreatmentIndex.value, 1, treatment);
    } else {
        draft.treatments.push(treatment);
    }

    // Update the local autocomplete catalog
    ensureTreatmentType(type);
    mergeTreatmentCatalogEntry(type, name);

    // Try to save the new entry to the shared server catalog
    try {
        await persistTreatmentCatalogEntry(type, name);
    } catch {
        notifications.warning(
            "Traitement ajoute au profil local, mais la mise a jour du catalogue partage a echoue.",
        );
    }

    cancelTreatmentEdit();
    if (isUpdate) notifications.itemUpdated();
    else notifications.itemAdded();
}

// Remove a treatment from the draft list by index
function removeTreatment(index) {
    if (!Array.isArray(draft.treatments)) return;
    draft.treatments.splice(index, 1);
    notifications.itemDeleted();
}

// Ask the user to confirm before deleting a treatment
function requestRemoveTreatment(index) {
    pendingDeleteTreatmentIndex.value = index;
    confirmDeleteTreatmentOpen.value = true;
}

// User cancelled the delete dialog
function cancelRemoveTreatment() {
    confirmDeleteTreatmentOpen.value = false;
    pendingDeleteTreatmentIndex.value = -1;
    notifications.actionCancelled();
}

// User confirmed the delete — remove the treatment
function confirmRemoveTreatment() {
    const index = pendingDeleteTreatmentIndex.value;
    confirmDeleteTreatmentOpen.value = false;
    pendingDeleteTreatmentIndex.value = -1;
    const isValid =
        index >= 0 &&
        Array.isArray(draft.treatments) &&
        index < draft.treatments.length;
    if (!isValid) return;
    removeTreatment(index);
}

// ─ Validation de l'email du médecin

// Validate the doctor's email — only required when sharing is enabled
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

// ─ Synchronisation brouillon ↔ profil

function syncDraftFromProfile() {
    // Basic info
    draft.gender = profile.gender || "";
    draft.height = profile.height ?? "";
    draft.weight = profile.current_weight ?? "";
    draft.bloodType = profile.blood_type || "";

    // Health lists
    draft.goals = normalizeList(profile.goals);
    draft.allergies = normalizeList(profile.allergies);
    draft.chronicDiseases = normalizeList(profile.chronic_diseases);

    // Add the user's existing items to the dropdowns for future use
    draft.allergies.forEach((item) =>
        appendUniqueCatalogOption(allergyOptions, item),
    );
    draft.chronicDiseases.forEach((item) =>
        appendUniqueCatalogOption(diseaseOptions, item),
    );

    // Treatments — convert dates from YYYY-MM-DD (API) to DD/MM/YYYY (form)
    draft.treatments = (
        Array.isArray(profile.treatments) ? profile.treatments : []
    ).map((t) => ({
        type: t.type || "",
        name: t.name || "",
        dose: t.dose || null,
        frequency_unit: t.frequency_unit || "day",
        frequency_count: t.frequency_count || 1,
        start_date: isoDateToFrench(t.start_date) || "",
        end_date: isoDateToFrench(t.end_date) || "",
    }));

    // Register treatments in the autocomplete catalog
    draft.treatments.forEach((item) => {
        const type = normalizeTreatmentText(item.type || "");
        const name = normalizeTreatmentText(item.name || "");
        if (type) ensureTreatmentType(type);
        if (type && name) mergeTreatmentCatalogEntry(type, name);
    });

    // Habits & doctor sharing
    draft.smoker = Boolean(profile.smoker);
    draft.alcoholic = Boolean(profile.alcoholic);
    draft.doctorCanConsult = Boolean(profile.doctor_invited);
    draft.doctorEmail = profile.doctor_email || "";

    // Reset all temporary UI state
    doctorEmailError.value = "";
    customInputs.allergies = "";
    customInputs.chronicDiseases = "";
    selectedAllergyOption.value = "";
    selectedDiseaseOption.value = "";
    customTreatmentInputs.type = "";
    customTreatmentInputs.name = "";
    openTreatmentTypeDropdown.value = false;
    openTreatmentNameDropdown.value = false;
    queryTreatmentTypes.value = "";
    queryTreatmentNames.value = "";
    cancelTreatmentEdit();
}

// ─ Contrôles d'édition des sections

// Turn off editing mode for every section
function resetEditFlags() {
    editing.base = editing.health = editing.habits = editing.doctor = false;
}

// Open a section for editing
function startEdit(section) {
    syncDraftFromProfile();
    clearSectionErrors();
    resetEditFlags();
    editing[section] = true;
}

// Close a section and discard unsaved changes
function cancelEdit(section) {
    syncDraftFromProfile();
    clearSectionErrors(section);
    editing[section] = false;
    notifications.actionCancelled();
}

// ─ Validation

// Clear error messages — passing null clears all sections
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

// Validate the "Informations de base" section
function validateBaseSection() {
    clearSectionErrors("base");

    if (!draft.gender) {
        sectionErrors.base.gender = "Veuillez selectionner le genre.";
    }

    if (draft.height === "" || draft.height === null) {
        sectionErrors.base.height = "La taille est obligatoire.";
    } else {
        const height = Number(draft.height);
        if (height < 80 || height > 250) {
            sectionErrors.base.height =
                "La taille doit etre une valeur entre 80 et 250 cm.";
        }
    }

    if (draft.weight === "" || draft.weight === null) {
        sectionErrors.base.weight = "Le poids est obligatoire.";
    } else {
        const weight = Number(draft.weight);
        if (weight < 35 || weight > 250) {
            sectionErrors.base.weight =
                "Le poids doit etre une valeur entre 35 et 250 kg.";
        }
    }

    return (
        !sectionErrors.base.gender &&
        !sectionErrors.base.height &&
        !sectionErrors.base.weight
    );
}

// Validate the "Santé" section — at least one goal must be selected
function validateHealthSection() {
    clearSectionErrors("health");
    if (!draft.goals.length) {
        sectionErrors.health.goals =
            "Veuillez selectionner au moins un objectif.";
    }
    return !sectionErrors.health.goals;
}

function validateTreatmentsSection() {
    const treatments = Array.isArray(draft.treatments) ? draft.treatments : [];

    for (const item of treatments) {
        const type = normalizeTreatmentText(item?.type);
        const name = normalizeTreatmentText(item?.name);
        const dose = normalizeTreatmentText(item?.dose);
        const frequencyUnit = normalizeTreatmentText(item?.frequency_unit);
        const frequencyCount = Number(item?.frequency_count);
        const startDateIso = frenchDateToIso(item?.start_date);
        const endDateIso = frenchDateToIso(item?.end_date);

        const hasMissingField =
            !type ||
            !name ||
            !dose ||
            !frequencyUnit ||
            !Number.isFinite(frequencyCount) ||
            frequencyCount < 1 ||
            !startDateIso ||
            !endDateIso;

        if (hasMissingField) {
            notifications.warning(
                "Tous les champs du traitement sont obligatoires.",
            );
            return false;
        }

        if (endDateIso <= startDateIso) {
            notifications.warning(
                "La date de fin doit être après la date de début.",
            );
            return false;
        }
    }

    return true;
}

// ─ Construction de la charge API

// Convert a single treatment from draft format to the API format
function buildTreatmentForApi(t) {
    return {
        type: t?.type ?? null,
        name: t?.name ?? null,
        dose: t?.dose ?? null,
        frequency_unit: t?.frequency_unit ?? null,
        frequency_count: t?.frequency_count ? Number(t.frequency_count) : null,
        start_date: frenchDateToIso(t?.start_date) ?? null, // DD/MM/YYYY → YYYY-MM-DD
        end_date: frenchDateToIso(t?.end_date) ?? null,
    };
}

// Build the full payload to send to the API
function buildPayload() {
    const sharingWithDoctor = Boolean(draft.doctorCanConsult);
    return {
        gender: String(draft.gender || "")
            .toLowerCase()
            .trim(),
        height: draft.height === "" ? null : Number(draft.height),
        current_weight: draft.weight === "" ? null : Number(draft.weight),
        blood_type: draft.bloodType || null,
        goals: normalizeList(draft.goals),
        allergies: normalizeList(draft.allergies),
        chronic_diseases: normalizeList(draft.chronicDiseases),
        treatments: (draft.treatments ?? [])
            .map(buildTreatmentForApi)
            .filter((t) => t.type),
        smoker: Boolean(draft.smoker),
        alcoholic: Boolean(draft.alcoholic),
        doctor_invited: sharingWithDoctor,
        doctor_email: sharingWithDoctor ? draft.doctorEmail || null : null,
    };
}

// ─ Gestion des erreurs API

// Map API validation errors back to the UI error fields
function handleValidationErrors(section, backendErrors) {
    clearSectionErrors();
    const messages = [];

    if (backendErrors.gender) {
        sectionErrors.base.gender = "Veuillez selectionner le genre.";
        messages.push(sectionErrors.base.gender);
    }
    if (backendErrors.height) {
        sectionErrors.base.height =
            "La taille doit etre une valeur entre 80 et 250 cm.";
        messages.push(sectionErrors.base.height);
    }
    if (backendErrors.current_weight) {
        sectionErrors.base.weight =
            "Le poids doit etre une valeur entre 35 et 250 kg.";
        messages.push(sectionErrors.base.weight);
    }
    if (backendErrors.goals) {
        sectionErrors.health.goals =
            "Veuillez selectionner au moins un objectif.";
        messages.push(sectionErrors.health.goals);
    }

    // If no known fields matched, fall back to showing the raw API messages
    const finalMessages = messages.length
        ? [...new Set(messages)]
        : Object.values(backendErrors).flat().filter(Boolean).map(String);

    if (sectionErrors[section]) {
        sectionErrors[section].form = finalMessages.length
            ? finalMessages
            : ["Validation invalide."];
    }

    // If errors belong to a different section than the one currently open, switch to it
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

    notifications.warning("Veuillez corriger les champs en erreur.");
}

// ─ Sauvegarde des sections

// Validate and save the given section to the API
async function saveSection(section) {
    // Run the appropriate validation first
    if (section === "base" && !validateBaseSection()) {
        notifications.warning("Veuillez corriger les champs en erreur.");
        return;
    }
    if (section === "health" && !validateHealthSection()) {
        notifications.warning("Veuillez corriger les champs en erreur.");
        return;
    }
    if (section === "habits" && !validateTreatmentsSection()) return;
    if (section === "doctor" && !validateDoctorEmail()) return;

    savingSection.value = section;
    clearSectionErrors(section);

    try {
        const response = await api.post("/health-profile", buildPayload());

        // Update the local profile with fresh data from the server
        Object.assign(profile, response?.data?.data || {});
        const apiUser = response?.data?.user || {};
        user.name = apiUser.name || user.name;
        user.dateOfBirth = apiUser.date_of_birth || user.dateOfBirth;

        authStore.setHealthProfile(true);
        healthProfileStore.invalidate();
        syncDraftFromProfile();
        editing[section] = false;
        notifications.itemUpdated();
    } catch (err) {
        const status = err?.response?.status;

        if (status === 401) {
            // Session expired — log out and redirect
            authStore.removeToken();
            router.replace({ name: "register" });
            return;
        }

        if (status === 422 && err?.response?.data?.errors) {
            const errors = err.response.data.errors;

            // Special case: doctor email error from the server
            if (
                section === "doctor" &&
                (errors?.doctor_email || errors?.medecin_email)
            ) {
                const emailError = errors.doctor_email || errors.medecin_email;
                doctorEmailError.value = Array.isArray(emailError)
                    ? emailError[0]
                    : "Email medecin invalide.";
                notifications.warning(doctorEmailError.value);
                return;
            }

            // Map all other 422 errors to the right UI fields
            handleValidationErrors(section, errors);
            return;
        }

        notifications.error("Erreur lors de la sauvegarde du profil.");
    } finally {
        savingSection.value = "";
    }
}

// ─ Chargement du profil au démarrage

onMounted(async () => {
    try {
        await healthProfileStore.initialize();

        applyTreatmentCatalog(healthProfileStore.treatmentCatalog);

        Object.assign(profile, healthProfileStore.profileData || {});

        user.name        = healthProfileStore.userInfo?.name           || "";
        user.dateOfBirth = healthProfileStore.userInfo?.date_of_birth  || "";

        authStore.setHealthProfile(Boolean(healthProfileStore.profileData));

        syncDraftFromProfile();
    } catch (err) {
        if (err?.response?.status === 401) {
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
