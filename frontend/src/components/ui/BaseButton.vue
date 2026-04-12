<!--
  BaseButton.vue
  ─────────────────────────────────────────────────────────────
  Reusable button with three visual variants and three sizes.
  Replaces repeated inline button class strings across pages.

  Props:
    variant  – 'primary'   blue/purple gradient (default)
               'secondary' white with border
               'danger'    red icon-style button
    size     – 'sm' | 'md' | 'lg'
    type     – HTML button type ('button' by default, 'submit', 'reset')
    disabled – grays out the button and blocks clicks

  Usage:
    <BaseButton @click="save">Enregistrer</BaseButton>

    <BaseButton variant="secondary" @click="cancel">
      Annuler
    </BaseButton>

    <BaseButton variant="primary" size="lg" type="submit" :disabled="saving">
      {{ saving ? 'Chargement...' : 'Confirmer' }}
    </BaseButton>
-->
<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition"
        :class="[variantStyles[variant], sizeStyles[size], disabled ? 'cursor-not-allowed opacity-50' : '']"
    >
        <slot />
    </button>
</template>

<script setup>
defineProps({
    variant: {
        type: String,
        default: "primary",
        validator: (v) => ["primary", "secondary", "danger"].includes(v),
    },
    size: {
        type: String,
        default: "sm",
        validator: (v) => ["sm", "md", "lg"].includes(v),
    },
    type: { type: String, default: "button" },
    disabled: { type: Boolean, default: false },
});

// Visual styles for each variant
const variantStyles = {
    primary:
        "bg-gradient-to-r from-[#2563eb] to-[#7c3aed] text-white shadow-md shadow-indigo-500/25 hover:-translate-y-0.5",
    secondary:
        "border border-slate-300 bg-white text-slate-700 hover:bg-slate-50",
    danger:
        "text-red-500 hover:text-red-700",
};

// Padding and font size for each size
const sizeStyles = {
    sm: "px-4 py-2 text-xs",
    md: "px-5 py-3 text-sm",
    lg: "px-8 py-3.5 text-base",
};
</script>
