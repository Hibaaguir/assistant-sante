<template>
  <div v-if="notifications.items.length" :class="classesConteneur">
    <TransitionGroup
      name="toast"
      tag="div"
      :class="classesListe"
    >
      <article
        v-for="toast in notifications.items"
        :key="toast.id"
        class="rounded-xl border px-4 py-3 shadow-[0_10px_26px_rgba(15,23,42,0.14)] backdrop-blur-sm"
        :class="[mode === 'fixed' ? 'pointer-events-auto' : '', tonNotification(toast.type).card]"
      >
        <div class="flex items-start justify-between gap-3">
          <p class="min-w-0 text-[15px] font-medium leading-6" :class="tonNotification(toast.type).text">
            {{ toast.message }}
          </p>
          <button type="button" class="mt-0.5 text-slate-400 transition hover:text-slate-600" @click="notifications.retirer(toast.id)" aria-label="Fermer la notification">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
            </svg>
          </button>
        </div>
      </article>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useNotificationsStore } from "@/stores/notifications";

const notifications = useNotificationsStore();
const props = defineProps({
  mode: {
    type: String,
    default: "fixed",
  },
});

const mode = computed(() => (props.mode === "inline" ? "inline" : "fixed"));

const classesConteneur = computed(() =>
  mode.value === "inline"
    ? "w-full"
    : "pointer-events-none fixed inset-x-0 top-4 z-[100] px-3 sm:px-4"
);

const classesListe = computed(() =>
  mode.value === "inline"
    ? "w-full max-w-[560px] space-y-2.5"
    : "mx-auto w-full max-w-[520px] space-y-2.5"
);

function tonNotification(type) {
  if (type === "success") return { card: "border-emerald-200/90 bg-emerald-50/95", text: "text-emerald-800" };
  if (type === "error") return { card: "border-rose-200/90 bg-rose-50/95", text: "text-rose-800" };
  if (type === "warning") return { card: "border-amber-200/90 bg-amber-50/95", text: "text-amber-800" };
  return { card: "border-blue-200/90 bg-blue-50/95", text: "text-blue-800" };
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 220ms ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.98);
}

.toast-move {
  transition: transform 220ms ease;
}
</style>
