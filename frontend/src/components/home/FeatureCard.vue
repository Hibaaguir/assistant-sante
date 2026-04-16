<template>
    <article
        data-anime
        class="translate-y-6 opacity-0 transition-all duration-700 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_4px_16px_rgba(15,23,42,0.06)]"
        :style="{ transitionDelay: `${index * 80}ms` }"
    >
        <div
            class="inline-flex h-11 w-11 items-center justify-center rounded-[14px]"
            :style="{ backgroundColor: `${fonctionnalite.accent}18` }"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                class="h-5 w-5 [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"
                :style="{ stroke: fonctionnalite.accent }"
            >
                <path :d="iconPath" />
            </svg>
        </div>

        <div>
            <Typography tag="h3" variant="h3-style">
                {{ fonctionnalite.titre }}
            </Typography>
            <Typography tag="p" variant="paragraph" class="mt-2">
                {{ fonctionnalite.description }}
            </Typography>
        </div>
    </article>
</template>

<script setup>
import { computed } from "vue";
import Typography from "@/components/ui/Typography.vue";

const props = defineProps({
    fonctionnalite: { type: Object, required: true },
    index: { type: Number, default: 0 },
});

const ICONS = {
    journal:
        "M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5zM3 12h4M12 3v4",
    dossier:
        "M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z",
    traitements: "M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01",
    analyses:
        "M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z",
    objectifs: "M13 10V3L4 14h7v7l9-11h-7z",
    medecin:
        "M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm8-2v6m3-3h-6",
};

const iconPath = computed(
    () => ICONS[props.fonctionnalite.icone] ?? ICONS.journal,
);
</script>
