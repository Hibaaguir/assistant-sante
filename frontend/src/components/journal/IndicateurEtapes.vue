<template>
  <div class="flex items-center justify-between gap-3 sm:gap-4">
    <template v-for="(step, index) in steps" :key="step.id">
      <div class="flex min-w-0 items-center gap-2 sm:gap-3">
        <div
          class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-bold sm:h-9 sm:w-9 sm:text-xs"
          :class="current >= step.id ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'"
        >
          <span>{{ step.id }}</span>
        </div>
        <p class="whitespace-nowrap text-xs font-medium text-slate-700 sm:text-sm">{{ step.label }}</p>
      </div>
      <div
        v-if="index < steps.length - 1"
        class="hidden h-[2px] max-w-40 flex-1 rounded sm:block"
        :class="current > step.id ? 'bg-blue-600' : 'bg-slate-300'"
      />
    </template>
  </div>
</template>

<script setup>
defineProps({
  current: {
    type: Number,
    required: true
  },
  steps: {
    type: Array,
    required: true
  }
})
</script>
