<!--
  TabBar.vue
  ─────────────────────────────────────────────────────────────
  Reusable tab navigation bar.
  Uses v-model to tell the parent which tab is active.

  Props:
    tabs       – Array of { value: string, label: string }
    modelValue – the currently selected tab value (v-model)

  Emits:
    update:modelValue – fires when user clicks a tab

  Usage:
    <TabBar v-model="activeTab" :tabs="[
      { value: 'vitals',     label: 'Signes vitaux' },
      { value: 'labs',       label: 'Analyse médicale' },
      { value: 'treatments', label: 'Traitements' },
    ]" />
-->
<template>
    <section class="border-b-2 border-slate-200 bg-transparent">
        <div class="flex gap-0">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                @click="$emit('update:modelValue', tab.value)"
                :class="[
                    'flex-1 px-4 py-3 text-center font-bold text-lg text-black transition-all',
                    modelValue === tab.value
                        ? 'border-b-4 border-blue-500 text-blue-900 bg-blue-50'
                        : 'border-b-4 border-transparent text-black hover:bg-gray-50',
                ]"
            >
                {{ tab.label }}
            </button>
        </div>
    </section>
</template>

<script setup>
defineProps({
    tabs: { type: Array, required: true },
    modelValue: { type: String, required: true },
});

defineEmits(["update:modelValue"]);
</script>
