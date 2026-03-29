<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="ouvert"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-titre"
                class="fixed inset-0 z-[1300] flex items-center justify-center bg-slate-900/45 backdrop-blur-sm p-4"
                @click.self="$emit('annuler')"
            >
                <div
                    class="w-full max-w-[470px] rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-red-100 flex-shrink-0"
                            aria-hidden="true"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5 text-red-600"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3
                                id="modal-titre"
                                class="text-[28px] font-bold text-slate-900"
                            >
                                Êtes-vous sûr ?
                            </h3>
                            <p class="mt-1 text-[14px] text-slate-600">
                                Cette action est irréversible. Cela supprimera
                                définitivement le compte de l'utilisateur.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            class="h-10 rounded-xl border border-slate-300 bg-white px-4 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50"
                            @click="$emit('annuler')"
                        >
                            Annuler
                        </button>
                        <button
                            type="button"
                            class="h-10 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 px-4 text-[13px] font-semibold text-white shadow-[0_8px_16px_rgba(249,115,22,0.3)] transition hover:from-orange-600 hover:to-red-600"
                            @click="$emit('confirmer')"
                        >
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
defineProps({
    ouvert: { type: Boolean, default: false },
});

defineEmits(["annuler", "confirmer"]);
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
