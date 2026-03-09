<template>
  <Teleport v-if="!isInlineHealthPage" to="body">
    <div class="pointer-events-none fixed left-1/2 top-16 z-[1200] flex w-[min(96vw,1180px)] -translate-x-1/2 flex-col gap-2">
      <TransitionGroup name="toast">
        <article
          v-for="toast in notifications.items"
          :key="toast.id"
          class="pointer-events-auto relative rounded-xl border px-4 py-3 shadow-sm"
          :class="tone(toast.type).card"
          role="status"
          aria-live="polite"
        >
          <div class="flex items-center justify-between gap-3">
            <p class="min-w-0 text-[16px] font-medium leading-6" :class="tone(toast.type).text">
              {{ toast.message }}
            </p>
            <button type="button" class="text-slate-400 hover:text-slate-600" @click="notifications.remove(toast.id)" aria-label="Fermer la notification">
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
const isInlineHealthPage = computed(() => route.name === "health");

function tone(type) {
  if (type === "success") {
    return {
      card: "border-emerald-300 bg-emerald-50",
      text: "text-emerald-700",
    };
  }
  if (type === "error") {
    return {
      card: "border-rose-300 bg-rose-50",
      text: "text-rose-700",
    };
  }
  if (type === "warning") {
    return {
      card: "border-amber-300 bg-amber-50",
      text: "text-amber-700",
    };
  }
  return {
    card: "border-blue-300 bg-blue-50",
    text: "text-blue-700",
  };
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 200ms ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
