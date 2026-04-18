<template>
    <div class="space-y-10">
        <div class="text-center max-w-2xl mx-auto">
            <Typography tag="h2" variant="h1-style"> Suivi médical </Typography>
            <Typography tag="p" variant="paragraph">
                Invite ton medecin a suivre ton evolution en toute securite
            </Typography>
        </div>

        <div class="space-y-8">
            <div class="space-y-4">
                <p class="text-lg font-semibold text-gray-800">
                    Souhaitez-vous inviter votre médecin à accéder à vos données
                    de santé ?
                </p>

                <div class="grid grid-cols-2 gap-4 max-w-lg">
                    <label
                        class="relative cursor-pointer rounded-2xl border-2 p-6 transition-all hover:shadow-md"
                        :class="
                            hasDoctor === 'yes'
                                ? 'border-blue-400 bg-blue-50/50 shadow-sm'
                                : 'border-gray-200 bg-white hover:border-gray-300'
                        "
                    >
                        <input
                            class="sr-only"
                            type="radio"
                            name="hasDoctor"
                            value="yes"
                            :checked="hasDoctor === 'yes'"
                            @change="setDoctor('yes')"
                        />
                        <div class="flex flex-col items-center gap-3">
                            <svg
                                class="h-8 w-8"
                                :class="hasDoctor === 'yes' ? 'text-blue-500' : 'text-gray-400'"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" stroke-width="2" />
                            </svg>
                            <span class="font-medium" :class="hasDoctor === 'yes' ? 'text-blue-700' : 'text-gray-700'">Oui</span>
                        </div>
                    </label>

                    <label
                        class="relative cursor-pointer rounded-2xl border-2 p-6 transition-all hover:shadow-md"
                        :class="
                            hasDoctor === 'no'
                                ? 'border-blue-400 bg-blue-50/50 shadow-sm'
                                : 'border-gray-200 bg-white hover:border-gray-300'
                        "
                    >
                        <input
                            class="sr-only"
                            type="radio"
                            name="hasDoctor"
                            value="no"
                            :checked="hasDoctor === 'no'"
                            @change="setDoctor('no')"
                        />
                        <div class="flex flex-col items-center gap-3">
                            <svg
                                class="h-8 w-8"
                                :class="hasDoctor === 'no' ? 'text-blue-500' : 'text-gray-400'"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h5" stroke-width="2" />
                            </svg>
                            <span class="font-medium" :class="hasDoctor === 'no' ? 'text-blue-700' : 'text-gray-700'">Non</span>
                        </div>
                    </label>
                </div>
            </div>

            <div v-if="hasDoctor === 'yes'" class="space-y-4 max-w-2xl">
                <p class="text-lg font-semibold text-gray-800">
                    Email du medecin
                </p>

                <div class="relative">
                    <svg
                        class="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke-width="2" />
                    </svg>
                    <input
                        type="email"
                        placeholder="medecin@exemple.com"
                        v-model.trim="form.medecin_email"
                        class="h-14 pl-14 text-lg rounded-xl border-2 border-gray-200 focus:border-blue-400 focus:ring-blue-400 w-full outline-none"
                    />
                </div>

                <div
                    class="bg-blue-50 border border-blue-200 rounded-xl p-5 flex gap-4"
                >
                    <div
                        class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center"
                    >
                        <svg
                            class="h-5 w-5 text-blue-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"
                                stroke-width="2"
                            />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-blue-900 mb-1">
                            Invitation securisee
                        </p>
                        <p class="text-sm text-blue-700">
                            Si ton medecin a deja un compte, l'invitation
                            apparaitra dans son espace medecin. Sinon, il
                            recevra un email.
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="hasDoctor === 'no'"
                class="bg-gray-50 border border-gray-200 rounded-xl p-6"
            >
                <p class="text-sm text-gray-600">
                    Aucun souci ! Tu pourras inviter un medecin plus tard depuis
                    les parametres de ton profil.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
/*
  Etape 3 du profil sante: lien avec le medecin.
  Ce composant pilote un choix binaire (oui/non) et l'email du medecin.
  Il met a jour l'objet "form" partage pour rester coherent avec l'assistant multi-etapes.
*/

import { computed } from "vue";
import Typography from "@/components/ui/Typography.vue";

const props = defineProps({
    form: { type: Object, required: true },
});

const form = props.form;

// Etat d'affichage derive des booleens du formulaire.
const hasDoctor = computed(() => (form.consulte_medecin ? "yes" : "no"));

// Synchronise les champs lies quand l'utilisateur change de choix.
function setDoctor(value) {
    const yes = value === "yes";
    form.consulte_medecin = yes;
    form.medecin_peut_consulter = yes;
    if (!yes) form.medecin_email = "";
}
</script>
