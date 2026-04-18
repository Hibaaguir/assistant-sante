<template>
    <div class="space-y-10">
        <div class="text-center max-w-2xl mx-auto">
            <Typography tag="h2" variant="h1-style">
                Informations de santé
            </Typography>
            <Typography tag="p" variant="paragraph">
                Ces informations nous aident a personnaliser tes recommandations
                et a mieux suivre ton evolution
            </Typography>
        </div>

        <div class="space-y-8">
            <section class="space-y-4">
                <label
                    class="text-lg font-semibold text-gray-800 flex items-center gap-2"
                >
                    <svg
                        class="h-5 w-5 text-red-500"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M12 3v18M3 12h18"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                    Groupe sanguin <span class="text-red-600">*</span>
                </label>

                <div class="relative max-w-md w-full">
                    <select
                        v-model="form.groupe_sanguin"
                        class="h-11 text-lg rounded-xl border-2 border-gray-300 w-full bg-white px-4 pr-10 outline-none focus:border-blue-400 appearance-none cursor-pointer"
                        style="font-size: 1.1rem"
                    >
                        <option value="">Selectionner votre groupe sanguin</option>
                        <option
                            v-for="group in bloodGroups"
                            :key="group"
                            :value="group"
                        >
                            {{ group }}
                        </option>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </section>

            <section class="space-y-4">
                <label
                    class="text-lg font-semibold text-gray-800 flex items-center gap-2"
                >
                    <svg
                        class="h-5 w-5 text-orange-500"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                            stroke="currentColor"
                            stroke-width="2"
                        />
                        <path
                            d="M12 8v5M12 16h.01"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                    Allergies
                </label>

                <button
                    type="button"
                    class="h-11 w-full rounded-xl border-2 border-gray-300 bg-white px-4 text-left flex items-center justify-between outline-none focus:border-blue-400 cursor-pointer"
                    style="font-size: 1.1rem"
                    @click="openAllergies = !openAllergies"
                >
                    <span class="text-gray-700">{{
                        form.allergies.length
                            ? `${form.allergies.length} selectionne(s)`
                            : "Selectionner vos allergies"
                    }}</span>
                    <svg
                        class="h-4 w-4 text-gray-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M8 10l4 4 4-4"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>

                <div v-if="form.allergies.length" class="flex flex-wrap gap-2">
                    <span
                        v-for="item in form.allergies"
                        :key="`allergy-chip-${item}`"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 bg-orange-50 px-3 py-1 text-sm text-orange-700"
                    >
                        {{ item }}
                        <button
                            type="button"
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-red-500 hover:bg-red-200 transition-colors"
                            @click="removeSelected('allergies', item)"
                        >
                            ×
                        </button>
                    </span>
                </div>

                <div
                    v-if="openAllergies"
                    class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden"
                >
                    <div class="p-3 border-b">
                        <input
                            v-model="queryAllergies"
                            type="text"
                            placeholder="Rechercher..."
                            class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
                        />
                    </div>

                    <div class="max-h-56 overflow-auto p-2">
                        <button
                            v-for="item in filteredAllergies"
                            :key="`allergy-opt-${item}`"
                            type="button"
                            class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800 flex items-center justify-between"
                            :class="
                                isSelected('allergies', item)
                                    ? 'bg-slate-100'
                                    : ''
                            "
                            @click="toggleSelected('allergies', item)"
                        >
                            <span>{{ item }}</span>
                            <svg
                                v-if="isSelected('allergies', item)"
                                class="h-4 w-4 text-blue-500"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M20 6L9 17l-5-5"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    </div>

                    <div class="p-3 border-t bg-slate-50 flex gap-2">
                        <input
                            v-model="customAllergy"
                            type="text"
                            placeholder="Ajouter..."
                            class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
                            @keydown.enter.prevent="
                                addCustom('allergies', customAllergy)
                            "
                        />
                        <BaseButton
                            type="button"
                            variant="primary"
                            size="sm"
                            :disabled="!customAllergy.trim()"
                            @click="addCustom('allergies', customAllergy)"
                        >
                            Ajouter
                        </BaseButton>
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <label class="text-lg font-semibold text-gray-800">
                    Maladies chroniques
                </label>

                <button
                    type="button"
                    class="h-11 w-full rounded-xl border-2 border-gray-300 bg-white px-4 text-left flex items-center justify-between outline-none focus:border-blue-400 cursor-pointer"
                    style="font-size: 1.1rem"
                    @click="openDiseases = !openDiseases"
                >
                    <span class="text-gray-700">{{
                        form.maladies_chroniques.length
                            ? `${form.maladies_chroniques.length} selectionne(s)`
                            : "Selectionner vos maladies chroniques"
                    }}</span>
                    <svg
                        class="h-4 w-4 text-gray-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M8 10l4 4 4-4"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>

                <div
                    v-if="form.maladies_chroniques.length"
                    class="flex flex-wrap gap-2"
                >
                    <span
                        v-for="item in form.maladies_chroniques"
                        :key="`disease-chip-${item}`"
                        class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-sm text-blue-700"
                    >
                        {{ item }}
                        <button
                            type="button"
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-sm font-bold text-red-500 hover:bg-red-200 transition-colors"
                            @click="removeSelected('maladies_chroniques', item)"
                        >×</button>
                    </span>
                </div>

                <div
                    v-if="openDiseases"
                    class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden"
                >
                    <div class="p-3 border-b">
                        <input
                            v-model="queryDiseases"
                            type="text"
                            placeholder="Rechercher..."
                            class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
                        />
                    </div>

                    <div class="max-h-56 overflow-auto p-2">
                        <button
                            v-for="item in filteredDiseases"
                            :key="`disease-opt-${item}`"
                            type="button"
                            class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800 flex items-center justify-between"
                            :class="
                                isSelected('maladies_chroniques', item)
                                    ? 'bg-slate-100'
                                    : ''
                            "
                            @click="toggleSelected('maladies_chroniques', item)"
                        >
                            <span>{{ item }}</span>
                            <svg
                                v-if="isSelected('maladies_chroniques', item)"
                                class="h-4 w-4 text-blue-500"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M20 6L9 17l-5-5"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    </div>

                    <div class="p-3 border-t bg-slate-50 flex gap-2">
                        <input
                            v-model="customDisease"
                            type="text"
                            placeholder="Ajouter..."
                            class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
                            @keydown.enter.prevent="
                                addCustom('maladies_chroniques', customDisease)
                            "
                        />
                        <BaseButton
                            type="button"
                            variant="primary"
                            size="sm"
                            :disabled="!customDisease.trim()"
                            @click="
                                addCustom('maladies_chroniques', customDisease)
                            "
                        >
                            Ajouter
                        </BaseButton>
                    </div>
                </div>
            </section>

            <section
                class="bg-gradient-to-br from-blue-50 to-blue-50 rounded-2xl border-2 border-blue-100 p-6 space-y-4"
            >
                <div class="flex items-start gap-4">
                    <div class="bg-blue-100 p-3 rounded-xl">
                        <svg
                            class="h-6 w-6 text-blue-500"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M10 14l-2 2a3 3 0 1 1-4-4l2-2m8-2 2-2a3 3 0 1 1 4 4l-2 2m-8 2 4-4"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3
                            class="text-base font-medium text-gray-900 block mb-1"
                        >
                            Traitements en cours
                        </h3>
                        <p class="text-sm text-gray-600">
                            Ajoute tes traitements pour creer un calendrier de
                            rappels
                        </p>
                    </div>
                </div>

                <div v-if="!showTreatmentForm">
                    <BaseButton
                        type="button"
                        variant="outline"
                        size="md"
                        @click="showTreatmentForm = true"
                    >
                        + Ajouter un traitement
                    </BaseButton>
                </div>

                <div
                    v-else
                    class="bg-white rounded-xl border-2 border-blue-200 p-6 space-y-4"
                >
                    <h4 class="font-medium text-gray-900 mb-2">
                        Nouveau traitement
                    </h4>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-900"
                            >Type de traitement
                            <span class="text-red-500">*</span></label
                        >
                        <button
                            type="button"
                            class="h-12 w-full rounded-lg border px-4 bg-white outline-none focus:border-blue-400 text-left text-sm flex items-center justify-between"
                            :class="
                                treatmentErrors.type
                                    ? 'border-red-300'
                                    : 'border-gray-200'
                            "
                            @click="openTreatmentTypes = !openTreatmentTypes"
                        >
                            <span>{{
                                treatment.type || "Selectionner un type"
                            }}</span>
                            <svg
                                class="h-4 w-4 text-gray-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M8 10l4 4 4-4"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <p
                            v-if="treatmentErrors.type"
                            class="text-sm text-red-600"
                        >
                            {{ treatmentErrors.type }}
                        </p>

                        <div
                            v-if="openTreatmentTypes"
                            class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden"
                        >
                            <div class="p-3 border-b">
                                <input
                                    v-model="queryTreatmentTypes"
                                    type="text"
                                    placeholder="Rechercher..."
                                    class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
                                />
                            </div>
                            <div class="max-h-40 overflow-auto p-2">
                                <button
                                    v-for="item in filteredTreatmentTypes"
                                    :key="`type-${item}`"
                                    type="button"
                                    class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800"
                                    @click="selectTreatmentType(item)"
                                >
                                    {{ item }}
                                </button>
                                <p
                                    v-if="!filteredTreatmentTypes.length"
                                    class="px-3 py-2 text-sm text-slate-500"
                                >
                                    Aucun type disponible.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-900"
                            >Nom du traitement
                            <span class="text-red-500">*</span></label
                        >
                        <button
                            type="button"
                            class="h-12 w-full rounded-lg border px-4 bg-white outline-none focus:border-blue-400 text-left text-sm flex items-center justify-between"
                            :disabled="!treatment.type.trim()"
                            :class="
                                treatmentErrors.name
                                    ? 'border-red-300'
                                    : 'border-gray-200'
                            "
                            @click="toggleTreatmentNames"
                        >
                            <span>{{
                                treatment.name ||
                                (treatment.type
                                    ? "Selectionner ou ajouter un traitement"
                                    : "Selectionner d'abord un type")
                            }}</span>
                            <svg
                                class="h-4 w-4 text-gray-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M8 10l4 4 4-4"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <p
                            v-if="treatmentErrors.name"
                            class="text-sm text-red-600"
                        >
                            {{ treatmentErrors.name }}
                        </p>

                        <div
                            v-if="openTreatmentNames"
                            class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden"
                        >
                            <div class="p-3 border-b">
                                <input
                                    v-model="queryTreatmentNames"
                                    type="text"
                                    placeholder="Rechercher..."
                                    class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
                                />
                            </div>
                            <div class="max-h-40 overflow-auto p-2">
                                <button
                                    v-for="item in filteredTreatmentNames"
                                    :key="`name-${item}`"
                                    type="button"
                                    class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800"
                                    @click="selectTreatmentName(item)"
                                >
                                    {{ item }}
                                </button>
                                <p
                                    v-if="!filteredTreatmentNames.length"
                                    class="px-3 py-2 text-sm text-slate-500"
                                >
                                    Aucun traitement propose pour ce type.
                                </p>
                            </div>
                            <div class="p-3 border-t bg-slate-50 flex gap-2">
                                <input
                                    v-model="customTreatmentName"
                                    type="text"
                                    placeholder="Ajouter..."
                                    class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
                                    @keydown.enter.prevent="
                                        addCustomTreatmentName
                                    "
                                />
                                <button
                                    type="button"
                                    class="h-11 rounded-lg bg-blue-400 px-4 text-white font-semibold text-sm disabled:opacity-50"
                                    :disabled="!customTreatmentName.trim()"
                                    @click="addCustomTreatmentName"
                                >
                                    Ajouter
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-900"
                            >Dose <span class="text-red-500">*</span></label
                        >
                        <input
                            v-model.trim="treatment.dose"
                            type="text"
                            placeholder="Ex: 500mg, 1 comprime..."
                            class="h-10 rounded-lg w-full border bg-slate-50 px-4 text-sm outline-none placeholder:text-sm focus:border-blue-400"
                            :class="
                                treatmentErrors.dose
                                    ? 'border-red-300'
                                    : 'border-gray-200'
                            "
                            @input="treatmentErrors.dose = ''"
                        />
                        <p
                            v-if="treatmentErrors.dose"
                            class="text-sm text-red-600"
                        >
                            {{ treatmentErrors.dose }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-900"
                            >Frequence
                            <span class="text-red-500">*</span></label
                        >
                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative">
                                <select
                                    v-model="treatment.frequency_unit"
                                    class="h-10 rounded-lg w-full border px-4 pr-9 bg-white outline-none focus:border-blue-400 text-sm font-medium text-gray-800 appearance-none cursor-pointer"
                                    :class="
                                        treatmentErrors.frequency_unit
                                            ? 'border-red-300'
                                            : 'border-gray-200'
                                    "
                                    @change="treatmentErrors.frequency_unit = ''"
                                >
                                    <option value="day">Par jour</option>
                                    <option value="week">Par semaine</option>
                                    <option value="month">Par mois</option>
                                </select>
                                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M8 10l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div
                                class="flex h-12 w-full items-center justify-between rounded-lg border border-gray-200 bg-slate-50 px-4 text-sm text-slate-900"
                            >
                                <input
                                    v-model.number="treatment.frequency_count"
                                    type="number"
                                    min="1"
                                    class="no-spinner min-w-[4.5rem] bg-transparent text-sm outline-none"
                                    placeholder="Ex: 3"
                                    @input="
                                        treatmentErrors.frequency_count = ''
                                    "
                                />
                                <span
                                    class="whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{
                                        Number(treatment.frequency_count || 0) >
                                        1
                                            ? "prises"
                                            : "prise"
                                    }}
                                </span>
                            </div>
                        </div>
                        <p
                            v-if="treatmentErrors.frequency_unit"
                            class="text-sm text-red-600"
                        >
                            {{ treatmentErrors.frequency_unit }}
                        </p>
                        <p
                            v-if="treatmentErrors.frequency_count"
                            class="text-sm text-red-600"
                        >
                            {{ treatmentErrors.frequency_count }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-900"
                            >Debut de traitement
                            <span class="text-red-500">*</span>
                            <span class="text-gray-400 font-normal"
                                >(JJ/MM/AAAA)</span
                            ></label
                        >
                        <input
                            :value="treatment.start_date"
                            type="text"
                            placeholder="Ex: 01/03/2026"
                            maxlength="10"
                            class="h-10 rounded-lg w-full border bg-slate-50 px-4 text-sm outline-none placeholder:text-sm focus:border-blue-400"
                            :class="
                                treatmentErrors.start_date
                                    ? 'border-red-300'
                                    : 'border-gray-200'
                            "
                            @input="
                                (event) =>
                                    handleTreatmentDateInput(
                                        event,
                                        'start_date',
                                    )
                            "
                        />
                        <p
                            v-if="treatmentErrors.start_date"
                            class="text-sm text-red-600"
                        >
                            {{ treatmentErrors.start_date }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-900"
                            >Fin de traitement
                            <span class="text-red-500">*</span>
                            <span class="text-gray-400 font-normal"
                                >(JJ/MM/AAAA)</span
                            ></label
                        >
                        <input
                            :value="treatment.end_date"
                            type="text"
                            placeholder="Ex: 30/03/2026"
                            maxlength="10"
                            class="h-12 rounded-lg w-full border bg-slate-50 px-4 outline-none placeholder:text-sm focus:border-blue-400"
                            :class="
                                treatmentErrors.end_date
                                    ? 'border-red-300'
                                    : 'border-gray-200'
                            "
                            @input="
                                (event) =>
                                    handleTreatmentDateInput(event, 'end_date')
                            "
                        />
                        <p
                            v-if="treatmentErrors.end_date"
                            class="text-sm text-red-600"
                        >
                            {{ treatmentErrors.end_date }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <BaseButton
                            type="button"
                            variant="add"
                            size="md"
                            @click="addTreatment"
                        >
                            + Ajouter
                        </BaseButton>
                        <BaseButton
                            type="button"
                            variant="cancel"
                            size="md"
                            @click="cancelTreatment"
                        >
                            Annuler
                        </BaseButton>
                    </div>
                </div>

                <div
                    v-if="form.traitements.length"
                    class="bg-white rounded-xl border border-blue-100 p-4 space-y-3"
                >
                    <h4 class="font-medium text-gray-900">
                        Traitements ajoutes ({{ form.traitements.length }})
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="(item, index) in form.traitements"
                            :key="`treatment-${index}-${item.type}-${item.name}`"
                            class="rounded-lg border border-gray-200 bg-slate-50 px-4 py-3 flex items-start justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ item.name || "Traitement sans nom" }}
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ item.type }}
                                    <span v-if="item.dose">
                                        | {{ item.dose }}</span
                                    >
                                    <span
                                        v-if="
                                            item.frequency_count &&
                                            item.frequency_unit
                                        "
                                    >
                                        | {{ item.frequency_count }} prise{{
                                            Number(item.frequency_count) > 1
                                                ? "s"
                                                : ""
                                        }}
                                        / {{ item.frequency_unit }}</span
                                    >
                                    <span
                                        v-if="item.start_date && item.end_date"
                                    >
                                        | {{ item.start_date }} -
                                        {{ item.end_date }}</span
                                    >
                                    <span v-else-if="item.duration">
                                        | {{ item.duration }}</span
                                    >
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-red-600 hover:text-red-700"
                                    @click="removeTreatment(index)"
                                >
                                    Retirer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
/*
  Etape 2 du profil sante.
  L'utilisateur renseigne groupe sanguin, allergies, maladies chroniques et traitements.
  Le composant modifie directement l'objet "form" recu en prop pour rester simple.
*/

import { computed, reactive, ref, onMounted } from "vue";
import axios from "axios";
import Typography from "@/components/ui/Typography.vue";
import BaseButton from "@/components/ui/BaseButton.vue";

const props = defineProps({
    form: { type: Object, required: true },
});

const form = props.form;
if (!Array.isArray(form.allergies)) form.allergies = [];
if (!Array.isArray(form.maladies_chroniques)) form.maladies_chroniques = [];
if (!Array.isArray(form.traitements)) form.traitements = [];

const bloodGroups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
const allergyOptions = [
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
];
const diseaseOptions = [
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
];

// Types chargés depuis la DB via l'API
const treatmentTypes = ref([]);
// Noms chargés depuis la DB au moment où l'utilisateur choisit un type
const treatmentNamesByType = reactive({});

onMounted(async () => {
    try {
        const res = await axios.get("/api/treatment-catalogs/medication-types");
        treatmentTypes.value = res.data.filter((x) => x && x.trim() !== "");
    } catch (e) {
        treatmentTypes.value = [];
    }
});

const openAllergies = ref(false);
const openDiseases = ref(false);
const queryAllergies = ref("");
const queryDiseases = ref("");
const customAllergy = ref("");
const customDisease = ref("");
const showTreatmentForm = ref(false);

const openTreatmentTypes = ref(false);
const queryTreatmentTypes = ref("");
const openTreatmentNames = ref(false);
const queryTreatmentNames = ref("");
const customTreatmentName = ref("");

const treatment = reactive({
    type: "",
    name: "",
    dose: "",
    frequency_unit: "day",
    frequency_count: 1,
    start_date: "",
    end_date: "",
});
const treatmentErrors = reactive({
    type: "",
    name: "",
    dose: "",
    frequency_unit: "",
    frequency_count: "",
    start_date: "",
    end_date: "",
});

const filteredAllergies = computed(() => {
    const q = queryAllergies.value.trim().toLowerCase();
    return q
        ? allergyOptions.filter((item) => item.toLowerCase().includes(q))
        : allergyOptions;
});

const filteredDiseases = computed(() => {
    const q = queryDiseases.value.trim().toLowerCase();
    return q
        ? diseaseOptions.filter((item) => item.toLowerCase().includes(q))
        : diseaseOptions;
});

const filteredTreatmentTypes = computed(() => {
    const q = queryTreatmentTypes.value.trim().toLowerCase();
    return q
        ? treatmentTypes.value.filter((item) => item.toLowerCase().includes(q))
        : treatmentTypes.value;
});

const filteredTreatmentNames = computed(() => {
    const selectedType = treatment.type.trim();
    const options = selectedType
        ? treatmentNamesByType[selectedType] || []
        : [];
    const q = queryTreatmentNames.value.trim().toLowerCase();
    return q
        ? options.filter((item) => item.toLowerCase().includes(q))
        : options;
});

// Helpers communs pour les listes multi-selection (allergies/maladies).
function isSelected(key, value) {
    return form[key].includes(value);
}

function toggleSelected(key, value) {
    if (form[key].includes(value))
        form[key] = form[key].filter((item) => item !== value);
    else form[key] = [...form[key], value];
}

function removeSelected(key, value) {
    form[key] = form[key].filter((item) => item !== value);
}

function addCustom(key, value) {
    const normalized = String(value || "").trim();
    if (!normalized) return;
    if (!Array.isArray(form[key])) form[key] = [];
    if (!form[key].includes(normalized)) form[key] = [...form[key], normalized];
    if (key === "allergies") customAllergy.value = "";
    if (key === "maladies_chroniques") customDisease.value = "";
}

// Gestion des options de traitement (type/nom) avec ajout personnalise.
async function selectTreatmentType(value) {
    const previousType = treatment.type;
    treatment.type = value;
    if (previousType !== value) {
        treatment.name = "";
    }
    treatmentErrors.type = "";
    treatmentErrors.name = "";
    queryTreatmentTypes.value = "";
    queryTreatmentNames.value = "";
    customTreatmentName.value = "";
    openTreatmentNames.value = false;
    openTreatmentTypes.value = false;

    if (
        !Array.isArray(treatmentNamesByType[value]) ||
        treatmentNamesByType[value].length === 0
    ) {
        try {
            const res = await axios.get(
                "/api/treatment-catalogs/medication-names",
                { params: { type: value } },
            );
            treatmentNamesByType[value] = res.data;
        } catch (e) {
            if (!Array.isArray(treatmentNamesByType[value]))
                treatmentNamesByType[value] = [];
        }
    }
}

// Suppression de l'ajout dynamique de type : les types viennent uniquement de l'API

function toggleTreatmentNames() {
    if (!treatment.type.trim()) {
        treatmentErrors.type = "Choisissez d'abord un type de traitement.";
        return;
    }
    treatmentErrors.type = "";
    openTreatmentNames.value = !openTreatmentNames.value;
}

function selectTreatmentName(value) {
    treatment.name = value;
    treatmentErrors.name = "";
    openTreatmentNames.value = false;
}

function addCustomTreatmentName() {
    const selectedType = treatment.type.trim();
    if (!selectedType) {
        treatmentErrors.type = "Choisissez d'abord un type de traitement.";
        return;
    }

    const value = customTreatmentName.value.trim();
    if (!value) return;

    if (!Array.isArray(treatmentNamesByType[selectedType])) {
        treatmentNamesByType[selectedType] = [];
    }
    if (!treatmentNamesByType[selectedType].includes(value)) {
        treatmentNamesByType[selectedType].push(value);
    }

    treatment.name = value;
    treatmentErrors.name = "";
    customTreatmentName.value = "";
    openTreatmentNames.value = false;
}

// Formatage date "JJ/MM/AAAA" au fur et a mesure de la saisie.
function formatDateWithSlashes(value) {
    const digits = String(value || "")
        .replace(/\D/g, "")
        .slice(0, 8);
    if (digits.length <= 2) return digits;
    if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
}

function handleTreatmentDateInput(event, key) {
    const raw = event?.target?.value ?? "";
    treatment[key] = formatDateWithSlashes(raw);
    treatmentErrors[key] = "";
}

function buildTreatmentDuration() {
    if (treatment.start_date && treatment.end_date)
        return `${treatment.start_date} - ${treatment.end_date}`;
    return null;
}

function clearTreatmentErrors() {
    treatmentErrors.type = "";
    treatmentErrors.name = "";
    treatmentErrors.dose = "";
    treatmentErrors.frequency_unit = "";
    treatmentErrors.frequency_count = "";
    treatmentErrors.start_date = "";
    treatmentErrors.end_date = "";
}

function parseFrDate(value) {
    const match = String(value || "").match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) return null;

    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    const date = new Date(year, month - 1, day);

    const isValid =
        date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day;

    return isValid ? date : null;
}

function validateTreatment() {
    clearTreatmentErrors();
    let isValid = true;

    if (!treatment.type.trim()) {
        treatmentErrors.type = "Le type de traitement est obligatoire.";
        isValid = false;
    }
    if (!treatment.name.trim()) {
        treatmentErrors.name = "Le nom du traitement est obligatoire.";
        isValid = false;
    }
    if (!treatment.dose.trim()) {
        treatmentErrors.dose = "La dose est obligatoire.";
        isValid = false;
    }

    if (!String(treatment.frequency_unit || "").trim()) {
        treatmentErrors.frequency_unit =
            "L'unité de fréquence est obligatoire.";
        isValid = false;
    }

    const frequencyCount = Number(treatment.frequency_count);
    if (!Number.isFinite(frequencyCount) || frequencyCount < 1) {
        treatmentErrors.frequency_count =
            "La fréquence doit être au minimum de 1 prise.";
        isValid = false;
    }

    if (!treatment.start_date.trim()) {
        treatmentErrors.start_date = "La date de début est obligatoire.";
        isValid = false;
    }
    if (!treatment.end_date.trim()) {
        treatmentErrors.end_date = "La date de fin est obligatoire.";
        isValid = false;
    }

    const startDate = parseFrDate(treatment.start_date);
    const endDate = parseFrDate(treatment.end_date);

    if (treatment.start_date && !startDate) {
        treatmentErrors.start_date = "Format invalide. Utilisez JJ/MM/AAAA.";
        isValid = false;
    }
    if (treatment.end_date && !endDate) {
        treatmentErrors.end_date = "Format invalide. Utilisez JJ/MM/AAAA.";
        isValid = false;
    }
    if (startDate && endDate && endDate <= startDate) {
        treatmentErrors.end_date =
            "La date de fin doit être strictement après la date de début.";
        isValid = false;
    }

    return isValid;
}

function addTreatment() {
    if (!validateTreatment()) return;

    form.traitements.push({
        type: treatment.type,
        name: treatment.name,
        dose: treatment.dose || null,
        frequency_unit: treatment.frequency_unit,
        frequency_count: Math.max(1, Number(treatment.frequency_count || 1)),
        start_date: treatment.start_date,
        end_date: treatment.end_date,
        duration: buildTreatmentDuration(),
    });

    cancelTreatment();
}

// Reinitialise le sous-formulaire de traitement.
function cancelTreatment() {
    clearTreatmentErrors();
    showTreatmentForm.value = false;
    openTreatmentTypes.value = false;
    openTreatmentNames.value = false;
    queryTreatmentTypes.value = "";
    queryTreatmentNames.value = "";
    customTreatmentName.value = "";

    treatment.type = "";
    treatment.name = "";
    treatment.dose = "";
    treatment.frequency_unit = "day";
    treatment.frequency_count = 1;
    treatment.start_date = "";
    treatment.end_date = "";
}

function removeTreatment(index) {
    form.traitements.splice(index, 1);
}
</script>

<style scoped>
.no-spinner::-webkit-outer-spin-button,
.no-spinner::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.no-spinner[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}
</style>
