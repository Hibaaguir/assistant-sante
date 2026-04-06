<!-- components/accueil/FeaturesSection.vue -->
<template>
    <!-- Variant: icon (solutions) -->
    <ul v-if="variant === 'icone'" class="m-0 grid list-none gap-2.5 p-0">
        <li
            v-for="point in points"
            :key="point.titre"
            class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-3"
        >
            <span
                class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                :style="{ backgroundColor: point.couleur }"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    class="h-4 w-4 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"
                >
                    <path
                        v-if="point.icone === 'horloge'"
                        d="M12 6v6l4 2M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"
                    />
                    <path v-else d="M7 4h10v16H7zM9 8h6M9 12h6" />
                </svg>
            </span>
            <div>
                <p class="m-0 text-[0.92rem] font-bold text-slate-900">
                    {{ point.titre }}
                </p>
                <p class="mt-1.5 text-[0.85rem] text-slate-500">
                    {{ point.description }}
                </p>
            </div>
        </li>
    </ul>

    <!-- Variant: badge (analytics) -->
    <ul v-else-if="variant === 'badge'" class="m-0 grid list-none gap-2.5 p-0">
        <li
            v-for="point in points"
            :key="point.titre"
            class="rounded-xl border border-slate-200 bg-white px-3.5 py-3"
        >
            <div class="flex items-center gap-2">
                <span
                    class="inline-block h-1.5 w-1.5 rounded-full"
                    :style="{ backgroundColor: point.couleur }"
                    aria-hidden="true"
                />
                <p class="m-0 text-[0.92rem] font-bold text-slate-900">
                    {{ point.titre }}
                </p>
            </div>
            <p class="mt-1.5 text-[0.85rem] text-slate-500">
                {{ point.description }}
            </p>
        </li>
    </ul>

    <!-- Variant: security (about) -->
    <ul v-else-if="variant === 'securite'" class="m-0 list-none p-0">
        <li
            class="flex items-start gap-3 rounded-xl border border-[#c7cbff] bg-[#f2f4ff] px-4 py-4"
        >
            <span
                class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#8b5cf6]"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    class="h-4 w-4 stroke-white [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]"
                >
                    <path d="M12 3 6 6v5c0 4 2 7 6 10 4-3 6-6 6-10V6l-6-3Z" />
                </svg>
            </span>
            <div>
                <p class="m-0 text-[0.94rem] font-bold text-slate-900">
                    {{ points[0].titre }}
                </p>
                <p class="mt-1.5 text-[0.84rem] leading-6 text-slate-600">
                    {{ points[0].description }}
                </p>
            </div>
        </li>
    </ul>
</template>

<script setup>
defineProps({
    points: { type: Array, required: true },
    variant: { type: String, default: "badge" }, // 'icone' | 'badge' | 'securite'
});
</script>
