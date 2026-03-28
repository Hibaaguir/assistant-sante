<template>
  <Teleport v-if="!isInlineMainArea" to="body">
    <div class="pointer-events-none fixed left-1/2 top-16 z-[1200] flex w-[min(96vw,1180px)] -translate-x-1/2 flex-col gap-2">
      <TransitionGroup name="toast">
        <article
          v-for="toast in notifications.items"
          :key="toast.id"
          class="pointer-events-auto rounded-xl border px-4 py-3 shadow-sm"
          :class="STYLES[toast.type]?.card ?? STYLES.info.card"
          role="status"
          aria-live="polite"
        >
          <div class="flex items-center gap-3">
            <span class="shrink-0" :class="STYLES[toast.type]?.text ?? STYLES.info.text" aria-hidden="true" v-html="ICONS[toast.type] ?? ICONS.info" />
            <p class="min-w-0 flex-1 text-[16px] font-medium leading-6" :class="STYLES[toast.type]?.text ?? STYLES.info.text">
              {{ toast.message }}
            </p>
            <button
              type="button"
              class="shrink-0 text-slate-400 hover:text-slate-600"
              aria-label="Fermer la notification"
              @click="notifications.retirer(toast.id)"
            >
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
              </svg>
            </button>
          </div>
        </article>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useNotificationsStore } from "@/stores/notifications";

const notifications = useNotificationsStore();
const route = useRoute();

const isInlineMainArea = computed(() => String(route.path ?? "").startsWith("/main"));

const STYLES = {
  success: { card: "border-emerald-300 bg-emerald-50", text: "text-emerald-700" },
  error:   { card: "border-rose-300 bg-rose-50",       text: "text-rose-700"    },
  warning: { card: "border-amber-300 bg-amber-50",     text: "text-amber-700"   },
  info:    { card: "border-blue-300 bg-blue-50",       text: "text-blue-700"    },
};

const ICONS = {
  success: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
  error:   `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round"/></svg>`,
  warning: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 9v4M12 17h.01M10.3 3.6 1.6 18a2 2 0 0 0 1.7 3h17.4a2 2 0 0 0 1.7-3L13.7 3.6a2 2 0 0 0-3.4 0z" stroke-linecap="round"/></svg>`,
  info:    `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/></svg>`,
};
</script>

<style scoped>
.toast-enter-active { transition: all 0.2s ease; }
.toast-leave-active { transition: all 0.18s ease; }
.toast-enter-from,
.toast-leave-to     { opacity: 0; transform: translateY(-8px); }
.toast-move         { transition: transform 0.2s ease; }
</style>