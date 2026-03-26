<template>
  <article
    data-anime
    :style="styleAnimation"
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_18px_30px_-30px_#0f172a] opacity-0 translate-y-6 transition-all duration-700"
  >
    <div
      class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl"
      :style="{ backgroundColor: fonctionnalite.accent }"
    >
      <svg
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true"
        class="h-5 w-5 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.7]"
      >
        <path :d="cheminIcone" />
      </svg>
    </div>

    <h3 class="mb-2 text-[1.05rem] font-extrabold leading-[1.4] text-slate-900">
      {{ fonctionnalite.titre }}
    </h3>
    <p class="m-0 text-[0.9rem] leading-[1.65] text-slate-600">
      {{ fonctionnalite.description }}
    </p>
  </article>
</template>

<script setup>
import { computed } from "vue";

const CHEMINS_ICONES = {
  journal:      "M8 5h8M8 10h8M8 15h5M6 3h12a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z",
  dossier:      "M7 19h10a2 2 0 0 0 2-2V9H5v8a2 2 0 0 0 2 2ZM5 9l2-4h10l2 4M12 12v5M9.5 14.5h5",
  traitements:  "M8 3v3M16 3v3M5 9h14M7 6h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2ZM12 12v4M10 14h4",
  analyses:     "M5 18h14M7 16V9m5 7V6m5 10v-4",
  objectifs:    "M6 16l4-4 3 3 5-6M19 9V5h-4",
  default:      "M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 1c-4 0-7 2-7 5h14c0-3-3-5-7-5ZM18.5 9.5h3M20 8v3",
};

const props = defineProps({
  fonctionnalite: {
    type: Object,
    required: true,
  },
  index: {
    type: Number,
    default: 0,
  },
});

const styleAnimation = computed(() => ({
  transitionDelay: `${props.index * 90}ms`,
}));

const cheminIcone = computed(() =>
  CHEMINS_ICONES[props.fonctionnalite?.icone] ?? CHEMINS_ICONES.default
);
</script>