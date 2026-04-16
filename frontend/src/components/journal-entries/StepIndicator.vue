<template>
    <div class="flex items-center justify-between gap-3 sm:gap-4">
        <template v-for="(step, index) in steps" :key="step.id">
            <!-- �tape -->
            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold sm:h-11 sm:w-11 sm:text-base"
                    :class="isDone(step.id) ? ACTIVE : INACTIVE"
                >
                    {{ step.id }}
                </div>
                <Typography
                    tag="span"
                    variant="h5-style"
                    class="whitespace-nowrap text-slate-800"
                >
                    {{ step.label }}
                </Typography>
            </div>

            <!-- S�parateur -->
            <div
                v-if="index < steps.length - 1"
                class="hidden h-[2px] max-w-40 flex-1 rounded sm:block"
                :class="current > step.id ? ACTIVE : 'bg-slate-300'"
            />
        </template>
    </div>
</template>

<script setup>
import Typography from "@/components/ui/Typography.vue";

const props = defineProps({
    current: { type: Number, required: true },
    steps: { type: Array, required: true },
});

const ACTIVE = "bg-gradient-to-r from-[#149bd7] to-[#7c3aed] text-white";
const INACTIVE = "bg-slate-200 text-slate-500";

const isDone = (id) => props.current >= id;
</script>
