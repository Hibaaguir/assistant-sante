<template>
    <!-- Modale de confirmation réutilisable -->
    <Teleport to="body">
        <Transition name="modal">
            <!-- Fond sombre + centrage de la modale -->
            <div
                v-if="open"
                class="fixed inset-0 z-[1300] flex items-center justify-center bg-slate-900/45 backdrop-blur-sm p-4"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="titleId"
                @click.self="$emit('cancel')"
            >
                <!-- Contenu de la modale -->
                <div :class="boxClass">
                    <!-- Titre et message -->
                    <h3 :id="titleId" :class="titleClass">
                        {{ title }}
                    </h3>
                    <p class="mt-1 text-base text-black">
                        {{ message }}
                    </p>
                    <!-- Boutons d'action -->
                    <div class="mt-5 flex items-center justify-end gap-2.5">
                        <BaseButton
                            type="button"
                            variant="cancel"
                            size="lg"
                            @click="$emit('cancel')"
                        >
                            {{ cancelLabel }}
                        </BaseButton>
                        <BaseButton
                            ref="confirmBtn"
                            type="button"
                            :variant="isDanger ? 'delete' : 'primary'"
                            size="lg"
                            @click="$emit('confirm')"
                        >
                            {{ confirmLabel }}
                        </BaseButton>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
// Import du bouton de base et des outils Vue
import BaseButton from "./BaseButton.vue";
import { nextTick, ref, watch, computed } from "vue";

// Définition des props de la modale
const props = defineProps({
    open: { type: Boolean, default: false }, // Affichage de la modale
    isDanger: { type: Boolean, default: true }, // Style "danger" (rouge) ou normal (bleu)
    title: { type: String, default: "Confirmer la suppression" }, // Titre
    message: {
        type: String,
        default: "Voulez-vous vraiment supprimer cet élément ?",
    }, // Message
    confirmLabel: { type: String, default: "Supprimer" }, // Texte bouton confirmer
    cancelLabel: { type: String, default: "Annuler" }, // Texte bouton annuler
});

// Référence pour le bouton de confirmation (focus automatique)
const confirmBtn = ref(null);
const titleId = "dialogue-confirmation-title";

// Classes dynamiques pour la boîte et le titre
const boxClass = computed(() => [
    "w-full max-w-[470px] rounded-2xl bg-white p-6 shadow-2xl",
    props.isDanger ? "border-2 border-red-400" : "border border-slate-200",
]);
const titleClass = computed(() => [
    "text-[28px] font-bold",
    props.isDanger ? "text-red-600" : "text-blue-600",
]);

// Blocage du scroll quand la modale est ouverte
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = "";
        }
    },
);

// Fermeture de la modale avec la touche Escape
const emit = defineEmits(["confirm", "cancel"]);
function onKeydown(e) {
    if (e.key === "Escape") emit("cancel");
}
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) document.addEventListener("keydown", onKeydown);
        else document.removeEventListener("keydown", onKeydown);
    },
    { immediate: true },
);
</script>

<style scoped>
/* Animation d'apparition/disparition de la modale */
.modal-enter-active,
.modal-leave-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
    transform: scale(0.97);
}
</style>
