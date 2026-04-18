<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed right-4 top-[72px] z-[1200] flex flex-col gap-2 w-[320px]"
        >
            <TransitionGroup name="toast">
                <div
                    v-for="toast in notifications.items"
                    :key="toast.id"
                    class="pointer-events-auto relative flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg"
                    role="status"
                    aria-live="polite"
                >
                    <!-- Icône colorée -->
                    <span
                        class="mt-0.5 shrink-0"
                        :class="STYLES[toast.type]?.icon ?? STYLES.info.icon"
                        aria-hidden="true"
                        v-html="ICONS[toast.type] ?? ICONS.info"
                    />

                    <!-- Message -->
                    <p
                        class="flex-1 text-sm font-medium text-slate-700 leading-5"
                    >
                        {{ toast.message }}
                    </p>

                    <!-- Fermer -->
                    <BaseButton
                        type="button"
                        variant="outline"
                        size="sm"
                        aria-label="Fermer"
                        @click="notifications.remove(toast.id)"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                d="m6 6 12 12M18 6 6 18"
                                stroke-linecap="round"
                            />
                        </svg>
                    </BaseButton>

                    <!-- Barre colorée en bas -->
                    <div
                        class="absolute bottom-0 left-0 h-[3px] rounded-b-xl w-full"
                        :class="STYLES[toast.type]?.bar ?? STYLES.info.bar"
                    />
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { useNotificationsStore } from "@/stores/notifications";
import BaseButton from "@/components/ui/BaseButton.vue";

const notifications = useNotificationsStore();

const STYLES = {
    success: { icon: "text-emerald-500", bar: "bg-emerald-500" },
    error: { icon: "text-rose-500", bar: "bg-rose-500" },
    warning: { icon: "text-amber-500", bar: "bg-amber-500" },
    info: { icon: "text-blue-500", bar: "bg-blue-500" },
};

const ICONS = {
    success: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    error: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round"/></svg>`,
    warning: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v4M12 17h.01M10.3 3.6 1.6 18a2 2 0 0 0 1.7 3h17.4a2 2 0 0 0 1.7-3L13.7 3.6a2 2 0 0 0-3.4 0z" stroke-linecap="round"/></svg>`,
    info: `<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/></svg>`,
};
</script>

<style scoped>
.toast-enter-active {
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-leave-active {
    transition: all 0.18s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
.toast-move {
    transition: transform 0.2s ease;
}
</style>
