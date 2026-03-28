<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="fixed inset-0 z-[1300] flex items-center justify-center bg-slate-900/45 p-4"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        @click.self="$emit('cancel')"
      >
        <div class="w-full max-w-[460px] rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_24px_50px_rgba(15,23,42,0.22)]">
          <div class="flex items-start gap-3">
            <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700" aria-hidden="true">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M3 6h18" stroke-linecap="round" />
                <path d="M8 6V4h8v2" stroke-linecap="round" />
                <path d="M19 6l-1 14H6L5 6" stroke-linecap="round" />
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <h3 :id="titleId" class="text-[18px] font-semibold text-slate-900">{{ title }}</h3>
              <p class="mt-1 text-[14px] text-slate-600">{{ message }}</p>
            </div>
          </div>

          <div class="mt-5 flex items-center justify-end gap-2.5">
            <button
              type="button"
              class="h-10 rounded-xl border border-slate-300 bg-white px-4 text-[13px] font-semibold text-slate-700 hover:bg-slate-50"
              @click="$emit('cancel')"
            >{{ cancelLabel }}</button>
            <button
              ref="confirmBtn"
              type="button"
              class="h-10 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 px-4 text-[13px] font-semibold text-white shadow-[0_8px_18px_rgba(225,29,72,0.28)] hover:from-rose-700 hover:to-red-700"
              @click="$emit('confirm')"
            >{{ confirmLabel }}</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { nextTick, ref, watch } from "vue";

const props = defineProps({
  open:         { type: Boolean, default: false },
  title:        { type: String,  default: "Confirmer la suppression" },
  message:      { type: String,  default: "Voulez-vous vraiment supprimer cet élément ?" },
  confirmLabel: { type: String,  default: "Supprimer" },
  cancelLabel:  { type: String,  default: "Annuler" },
});

const confirmBtn = ref(null);
const titleId    = "dialogue-confirmation-title";

// Focus automatique sur le bouton de confirmation + verrouillage du scroll
watch(() => props.open, async (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = "hidden";
    await nextTick();
    confirmBtn.value?.focus();
  } else {
    document.body.style.overflow = "";
  }
});

// Fermeture sur Escape
function onKeydown(e) { if (e.key === "Escape") emit("cancel"); }
const emit = defineEmits(["confirm", "cancel"]);

watch(() => props.open, (isOpen) => {
  if (isOpen) document.addEventListener("keydown", onKeydown);
  else        document.removeEventListener("keydown", onKeydown);
}, { immediate: true });
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.modal-enter-from, .modal-leave-to      { opacity: 0; transform: scale(0.97); }
</style>