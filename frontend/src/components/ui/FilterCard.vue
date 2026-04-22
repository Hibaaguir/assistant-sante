<template>
    <article class="card p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-[18px] font-bold text-[#041c49]">{{ title }}</h3>
                <p class="mt-1 text-[14px] text-[#5b6b84]">{{ subtitle }}</p>
            </div>
            <button
                v-if="showReset"
                type="button"
                class="btn-outline px-3 py-1 text-[12px]"
                @click="$emit('reset')"
            >
                Réinitialiser
            </button>
        </div>
        <div class="mt-5 grid auto-cols-fr grid-flow-col gap-4">
            <slot />
        </div>
    </article>
</template>

<script setup>
defineProps({
    title:     { type: String, required: true },
    subtitle:  { type: String, default: "" },
    showReset: { type: Boolean, default: false },
});

defineEmits(["reset"]);
</script>
