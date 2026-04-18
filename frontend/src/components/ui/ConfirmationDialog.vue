<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="open"
                class="fixed inset-0 z-[1300] flex items-center justify-center bg-slate-900/45 backdrop-blur-sm p-4"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="titleId"
                @click.self="$emit('cancel')"
            >
                <div
                    :class="[
                        'w-full max-w-[470px] rounded-2xl bg-white p-6 shadow-2xl',
                        isDanger
                            ? 'border-2 border-red-400'
                            : 'border border-slate-200',
                    ]"
                >
                    <div class="flex items-start gap-3">
                        <div class="hidden" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <h3
                                :id="titleId"
                                :class="[
                                    'text-[28px] font-bold',
                                    isDanger
                                        ? 'text-red-600'
                                        : 'text-purple-600',
                                ]"
                            >
                                {{ title }}
                            </h3>
                            <p class="mt-1 text-base text-black">
                                {{ message }}
                            </p>
                        </div>
                    </div>

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
                            :variant="isDanger ? 'delete' : 'secondary'"
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
import BaseButton from "./BaseButton.vue";
import { nextTick, ref, watch } from "vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    isDanger: { type: Boolean, default: true },
    title: { type: String, default: "Confirmer la suppression" },
    message: {
        type: String,
        default: "Voulez-vous vraiment supprimer cet élément ?",
    },
    confirmLabel: { type: String, default: "Supprimer" },
    cancelLabel: { type: String, default: "Annuler" },
});

const confirmBtn = ref(null);
const titleId = "dialogue-confirmation-title";

// Focus automatique sur le bouton de confirmation + verrouillage du scroll
watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = "hidden";
            await nextTick();
            confirmBtn.value?.focus();
        } else {
            document.body.style.overflow = "";
        }
    },
);

// Fermeture sur Escape
function onKeydown(e) {
    if (e.key === "Escape") emit("cancel");
}
const emit = defineEmits(["confirm", "cancel"]);

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
