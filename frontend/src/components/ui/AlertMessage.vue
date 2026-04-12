<!--
  AlertMessage.vue
  ─────────────────────────────────────────────────────────────
  Reusable colored alert box for success, error, warning, and
  info messages.

  Props:
    message (String) – the text to show. When empty the box is hidden.
    type    (String) – 'error' | 'success' | 'warning' | 'info'

  Usage:
    <AlertMessage :message="errorText" type="error" />
    <AlertMessage :message="successText" type="success" />
-->
<template>
    <div
        v-if="message"
        class="mt-4 rounded-2xl border px-4 py-3 text-sm font-medium"
        :class="colorClasses[type]"
    >
        {{ message }}
    </div>
</template>

<script setup>
defineProps({
    message: { type: String, default: "" },
    type: {
        type: String,
        default: "error",
        validator: (v) => ["error", "success", "warning", "info"].includes(v),
    },
});

// Each type maps to a border + background + text color combination
const colorClasses = {
    error:   "border-red-200 bg-red-50 text-red-700",
    success: "border-emerald-200 bg-emerald-50 text-emerald-700",
    warning: "border-orange-200 bg-orange-50 text-orange-700",
    info:    "border-blue-200 bg-blue-50 text-blue-700",
};
</script>
