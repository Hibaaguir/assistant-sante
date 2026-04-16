<template>
    <button
        type="submit"
        :disabled="loading || disabled"
        class="h-12 w-full rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 font-semibold text-[15px] text-white transition hover:shadow-lg hover:from-blue-600 hover:to-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>

<script setup>
defineProps({
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});
</script>
