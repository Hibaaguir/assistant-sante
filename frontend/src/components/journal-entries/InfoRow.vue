<!-- Composant réutilisable pour afficher une ligne d'information dans les entrées du journal, avec une icône, un label et soit un badge coloré, soit un texte simple. Les props permettent de personnaliser l'apparence et le contenu de la ligne en fonction des besoins spécifiques de chaque entrée du journal. -->
<template>
    <div
        class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3 last:border-0 last:pb-0"
    >
        <span class="inline-flex items-center gap-2">
            <svg
                viewBox="0 0 20 20"
                class="h-4 w-4 shrink-0"
                :class="color"
                fill="currentColor"
                aria-hidden="true"
            >
                <path :d="icon" />
            </svg>
            {{ label }}
        </span>

        <!-- Badge coloré ou texte simple -->
        <span
            v-if="badge"
            class="inline-flex items-center rounded-full border px-2.5 py-1 text-sm font-semibold"
            :class="badge"
        >
            {{ texte }}
        </span>
        <span class="text-right text-base font-semibold text-slate-900">
            {{ texte }}
        </span>
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    icon: { type: String, required: true },
    color: { type: String, required: true },
    texte: { type: String, required: true },
    badge: { type: String, default: null }, // null = texte simple, string = classes du badge
});
</script>
