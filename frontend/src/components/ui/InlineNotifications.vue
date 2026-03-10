<template>
  <div v-if="notifications.items.length" class="mb-4 space-y-2">
    <article
      v-for="toast in notifications.items"
      :key="toast.id"
      class="rounded-xl border px-4 py-3 shadow-sm"
      :class="tone(toast.type).card"
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
  </div>
</template>

<script setup>
import { useNotificationsStore } from "@/stores/notifications";

const notifications = useNotificationsStore();

function tone(type) {
  if (type === "success") return { card: "border-emerald-300 bg-emerald-50", text: "text-emerald-700" };
  if (type === "error") return { card: "border-rose-300 bg-rose-50", text: "text-rose-700" };
  if (type === "warning") return { card: "border-amber-300 bg-amber-50", text: "text-amber-700" };
  return { card: "border-blue-300 bg-blue-50", text: "text-blue-700" };
}
</script>

