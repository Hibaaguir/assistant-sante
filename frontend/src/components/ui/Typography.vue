<template>
    <component :is="as" class="app-typography font-sans">
        <slot />
    </component>
</template>

<script setup>
defineProps({
    as: {
        type: String,
        default: "section",
    },
});
</script>

<style scoped>
.app-typography {
    font-family: "Montserrat", sans-serif !important;
}

.app-typography :deep(p) {
    font-family: "Montserrat", sans-serif !important;
    font-size: 1.05rem !important;
    line-height: 1.85 !important;
    font-weight: 400 !important;
    color: #000000 !important;
    margin: 0 0 1rem;
}

.app-typography :deep(h1),
.app-typography :deep(h2),
.app-typography :deep(h3),
.app-typography :deep(h4),
.app-typography :deep(h5),
.app-typography :deep(h6) {
    font-family: "Montserrat", sans-serif !important;
    letter-spacing: -0.01em !important;
    line-height: 1.2 !important;
    margin: 0 0 0.65em;
    text-wrap: balance;
}

.app-typography :deep(h1) {
    font-size: 3rem !important;
    font-weight: 800 !important;
    color: #000000 !important;
}

.app-typography :deep(h2) {
    font-size: 2.35rem !important;
    font-weight: 700 !important;
    color: #000000 !important;
}

.app-typography :deep(h3) {
    font-size: 1.95rem !important;
    font-weight: 700 !important;
    color: var(--color-primary) !important;
}

.app-typography :deep(h4) {
    font-size: 1.55rem !important;
    font-weight: 600 !important;
    color: #000000 !important;
}

.app-typography :deep(h5) {
    font-size: 1.3rem !important;
    font-weight: 600 !important;
    color: #000000 !important;
}

.app-typography :deep(h6) {
    font-size: 1.12rem !important;
    font-weight: 600 !important;
    color: #000000 !important;
}

@media (max-width: 768px) {
    .app-typography :deep(h1) {
        font-size: 2.4rem !important;
    }

    .app-typography :deep(h2) {
        font-size: 2rem !important;
    }

    .app-typography :deep(h3) {
        font-size: 1.7rem !important;
    }
}
</style>
