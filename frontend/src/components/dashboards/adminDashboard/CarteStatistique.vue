<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md"
    >
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600">
                    {{ label }}
                </p>
                <p class="mt-2 text-4xl font-bold text-slate-900">
                    {{ valeur }}
                </p>
            </div>
            <div
                :class="[
                    'flex h-10 w-10 items-center justify-center rounded-lg',
                    couleurIcone[couleur]?.bg,
                    couleurIcone[couleur]?.text,
                ]"
            >
                <svg
                    v-if="icone"
                    :viewBox="icone.viewBox"
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    v-html="icone.path"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    label: { type: String, required: true },
    valeur: { type: Number, required: true },
    couleur: {
        type: String,
        default: "blue",
        validator: (v) =>
            ["blue", "emerald", "indigo", "orange", "rose"].includes(v),
    },
    pourcentage: { type: Number, default: null },
    icone: { type: Object, default: null },
});

const couleurIcone = {
    blue: { bg: "bg-blue-50", text: "text-blue-600" },
    emerald: { bg: "bg-emerald-50", text: "text-emerald-600" },
    indigo: { bg: "bg-indigo-50", text: "text-indigo-600" },
    orange: { bg: "bg-orange-50", text: "text-orange-600" },
    rose: { bg: "bg-rose-50", text: "text-rose-600" },
};
</script>
