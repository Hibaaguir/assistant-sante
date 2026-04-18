<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="open"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
                class="fixed inset-0 z-[1300] flex items-center justify-center bg-slate-900/45 backdrop-blur-sm p-4"
                @click.self="$emit('cancel')"
            >
                <div
                    class="w-full max-w-[470px] rounded-2xl border-2 border-red-400 bg-white p-6 shadow-2xl"
                >
                    <div class="hidden" aria-hidden="true"></div>
                    <div class="min-w-0 flex-1">
                        <h3
                            id="modal-title"
                            class="text-[28px] font-bold text-red-600"
                        >
                            Êtes-vous sûr?
                        </h3>
                        <p class="mt-1 text-base text-black">
                            Cette action ne peut pas être annulée. Elle
                            supprimera définitivement le compte utilisateur.
                        </p>
                    </div>

                    <div class="mt-5 flex items-center justify-end gap-2.5">
                        <BaseButton
                            type="button"
                            variant="cancel"
                            size="lg"
                            @click="$emit('cancel')"
                        >
                            Annuler
                        </BaseButton>
                        <BaseButton
                            type="button"
                            variant="delete"
                            size="lg"
                            @click="$emit('confirm')"
                        >
                            Supprimer
                        </BaseButton>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import BaseButton from "@/components/ui/BaseButton.vue";

defineProps({
    open: { type: Boolean, default: false },
});

defineEmits(["cancel", "confirm"]);
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-to,
.modal-leave-from {
    opacity: 1;
}
</style>
