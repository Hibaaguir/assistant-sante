<template>
  <div>
    <!-- Trigger -->
    <button
      type="button"
      class="h-14 w-full rounded-xl border-2 border-gray-300 bg-white px-4 text-left text-base font-semibold flex items-center justify-between"
      @click="open = !open"
    >
      <span>{{ selected.length ? `${selected.length} sélectionné(s)` : placeholder }}</span>
      <ChevronIcon />
    </button>

    <!-- Chips -->
    <div v-if="selected.length" class="flex flex-wrap gap-2">
      <span
        v-for="item in selected"
        :key="item"
        class="inline-flex items-center gap-2 rounded-lg border px-3 py-1 text-sm"
        :class="chipClass"
      >
        {{ item }}
        <button type="button" @click="$emit('remove', item)">×</button>
      </span>
    </div>

    <!-- Dropdown -->
    <div v-if="open" class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden">
      <div class="p-3 border-b">
        <input v-model="query" type="text" placeholder="Rechercher..." class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none" />
      </div>
      <div class="max-h-56 overflow-auto p-2">
        <button
          v-for="item in filtered"
          :key="item"
          type="button"
          class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800 flex items-center justify-between"
          :class="selected.includes(item) ? 'bg-slate-100' : ''"
          @click="$emit('toggle', item)"
        >
          <span>{{ item }}</span>
          <CheckIcon v-if="selected.includes(item)" />
        </button>
      </div>
      <div class="p-3 border-t bg-slate-50 flex gap-2">
        <input
          v-model="custom"
          type="text"
          placeholder="Ajouter..."
          class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
          @keydown.enter.prevent="submitCustom"
        />
        <button
          type="button"
          class="h-11 rounded-lg bg-[#72C9C0] px-4 text-white font-semibold text-sm disabled:opacity-50"
          :disabled="!custom.trim()"
          @click="submitCustom"
        >
          Ajouter
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  selected:    { type: Array,  required: true },
  options:     { type: Array,  required: true },
  placeholder: { type: String, default: 'Sélectionner...' },
  chipClass:   { type: String, default: '' },
})

const emit = defineEmits(['toggle', 'remove', 'add-custom'])

const open   = ref(false)
const query  = ref('')
const custom = ref('')

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase()
  return q ? props.options.filter(i => i.toLowerCase().includes(q)) : props.options
})

function submitCustom() {
  emit('add-custom', custom.value)
  custom.value = ''
}
</script>