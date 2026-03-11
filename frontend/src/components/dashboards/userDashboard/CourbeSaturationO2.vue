<!--
  CourbeSaturationO2.vue
  Composant SVG qui trace la courbe de la saturation en oxygène (%) à
  l'intérieur du graphique patient. Reçoit les valeurs, la config du graphique
  et l'index survolé en props ; dessine la polyline et les points interactifs.
-->
<template>
  <g>
    <polyline fill="none" stroke="#8b5cf6" stroke-width="3" :points="points" />
    <circle
      v-for="(point, i) in values"
      :key="i"
      :cx="convertirXEnPx(i)"
      :cy="convertirYEnPx(point)"
      :r="hoverIndex === i ? 6 : 5"
      fill="#8b5cf6"
    />
    <circle
      v-if="hoverIndex !== null"
      :cx="convertirXEnPx(hoverIndex)"
      :cy="convertirYEnPx(values[hoverIndex])"
      r="3.2"
      fill="white"
    />
  </g>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  values: { type: Array, required: true },
  hoverIndex: { type: Number, default: null },
  chart: { type: Object, required: true }
})

function convertirXEnPx(index) {
  if (props.values.length <= 1) return props.chart.left
  const usable = props.chart.width - props.chart.left - props.chart.right
  const step = usable / (props.values.length - 1)
  return props.chart.left + index * step
}

function convertirYEnPx(value) {
  const n = Number(value)
  if (!Number.isFinite(n)) return props.chart.height - props.chart.bottom
  const usable = props.chart.height - props.chart.top - props.chart.bottom
  const ratio = (n - props.chart.minY) / (props.chart.maxY - props.chart.minY)
  return props.chart.height - props.chart.bottom - ratio * usable
}

const points = computed(() =>
  props.values.map((v, i) => `${convertirXEnPx(i)},${convertirYEnPx(v)}`).join(' ')
)
</script>
