<template>
    <BaseButton
        type="submit"
        variant="primary"
        size="lg"
        fullWidth
        :disabled="loading || disabled"
        :loading="loading"
    >
        <slot />
    </BaseButton>
</template>

<script setup>
import BaseButton from "@/components/ui/BaseButton.vue";

defineProps({
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});
</script>
