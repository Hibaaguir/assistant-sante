<template>
    <component :is="tag" class="base-title" :class="variant">
        <slot />
    </component>
</template>

<script setup>
defineProps({
    tag: {
        type: String,
        default: "h2",
    },
    variant: {
        type: String,
        default: "default",
        validator: (v) =>
            [
                "default",
                "primary",
                "secondary",
                "h1-style",
                "h2-style",
                "h3-style",
                "h4-style",
                "h5-style",
                "paragraph",
                "mauve",
            ].includes(v),
    },
});
</script>

<style lang="scss" scoped>
.base-title {
    font-family: "Montserrat", sans-serif;
    font-weight: 600;
    margin-bottom: 1rem;
    letter-spacing: -0.01em;
    line-height: 1.2;
    text-wrap: balance;

    &.default {
        font-size: 1.5rem;
        color: #333;
    }

    &.primary {
        font-size: 3rem;
        font-weight: 800;
        color: #0066cc;

        @media (max-width: 1024px) {
            font-size: 2.5rem;
        }

        @media (max-width: 768px) {
            font-size: 2rem;
        }
    }

    &.secondary {
        font-size: 1.25rem;
        color: #666;
        font-weight: 500;
    }

    &.h1-style {
        font-size: 3rem;
        font-weight: 800;
        color: #1a1a1a;
        @media (max-width: 768px) {
            font-size: 2.4rem;
        }
    }

    &.h2-style {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1a1a1a;

        @media (max-width: 768px) {
            font-size: 1.875rem;
        }
    }

    &.h3-style {
        font-size: 1.95rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    &.h4-style {
        font-size: 1.55rem;
        font-weight: 500;
        color: #000;
    }

    &.h5-style {
        font-size: 1.25rem;
        font-weight: 600;
        color: #000000;
    }

    &.paragraph {
        font-size: 1.05rem;
        line-height: 1.85;
        font-weight: 400;
        color: #000;
        margin: 0 0 1rem;
    }

    &.mauve {
        font-size: 1.75rem;
        font-weight: 700;
        color: #a855f7;

        @media (max-width: 768px) {
            font-size: 1.5rem;
        }
    }
}
</style>
