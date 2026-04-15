<template>
    <Typography as="div" class="min-h-full">
        <router-view />
        <AppToasts />
    </Typography>
</template>

<script setup>
import AppToasts from "@/components/ui/AppToasts.vue";
import Typography from "@/components/ui/Typography.vue";
</script>
