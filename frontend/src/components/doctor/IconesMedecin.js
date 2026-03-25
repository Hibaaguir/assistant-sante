import { defineComponent, h } from 'vue'

export function creerIcone(nodes, filled = false) {
  return defineComponent({
    name: 'TableauDeBordMedecinIcon',
    inheritAttrs: false,
    setup(_, { attrs }) {
      return () =>
        h(
          'svg',
          {
            viewBox: '0 0 24 24',
            fill: filled ? 'currentColor' : 'none',
            stroke: filled ? 'none' : 'currentColor',
            strokeWidth: filled ? undefined : 1.8,
            strokeLinecap: 'round',
            strokeLinejoin: 'round',
            'aria-hidden': 'true',
            ...attrs
          },
          nodes.map(([tag, tagAttrs]) => h(tag, tagAttrs))
        )
    }
  })
}

export const IconeDeconnexion = creerIcone([
  ['path', { d: 'M10 17 15 12 10 7' }],
  ['path', { d: 'M15 12H4' }],
  ['path', { d: 'M20 20v-3a2 2 0 0 0-2-2h-3' }],
  ['path', { d: 'M20 4v3a2 2 0 0 1-2 2h-3' }]
])

export const IconeOeil = creerIcone([
  ['path', { d: 'M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z' }],
  ['circle', { cx: '12', cy: '12', r: '3' }]
])

export const IconeMail = creerIcone([
  ['path', { d: 'M4 6h16v12H4Z' }],
  ['path', { d: 'm4 7 8 6 8-6' }]
])

export const IconeUtilisateurs = creerIcone([
  ['path', { d: 'M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2' }],
  ['circle', { cx: '9.5', cy: '7', r: '3' }],
  ['path', { d: 'M20 21v-2a4 4 0 0 0-3-3.87' }],
  ['path', { d: 'M16.5 4.13a3 3 0 0 1 0 5.74' }]
])

export const IconeAjoutUtilisateur = creerIcone([
  ['path', { d: 'M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2' }],
  ['circle', { cx: '9.5', cy: '7', r: '3' }],
  ['path', { d: 'M19 8v6' }],
  ['path', { d: 'M16 11h6' }]
])

export const IconeContourUtilisateur = creerIcone([
  ['circle', { cx: '12', cy: '8', r: '3.5' }],
  ['path', { d: 'M6 20a6 6 0 0 1 12 0' }]
])

export const IconePetitUtilisateur = creerIcone([
  ['circle', { cx: '12', cy: '8', r: '3' }],
  ['path', { d: 'M6.5 20a5.5 5.5 0 0 1 11 0' }]
])

export const IconeTriangleAlerte = creerIcone([
  ['path', { d: 'M12 4 3.8 18.5A1 1 0 0 0 4.67 20h14.66a1 1 0 0 0 .87-1.5L12 4Z' }],
  ['path', { d: 'M12 9v4.5' }],
  ['circle', { cx: '12', cy: '16.5', r: '.8', fill: 'currentColor', stroke: 'none' }]
])

export const IconePouls = creerIcone([['path', { d: 'M3 12h4l2.2-5 3.6 10 2.6-6H21' }]])

export const IconeCoeurPlein = creerIcone([['path', { d: 'm12 20.4-1.1-.97C5.14 14.36 2 11.5 2 7.99 2 5.35 4.03 3.3 6.65 3.3c1.48 0 2.9.69 3.82 1.78A5 5 0 0 1 14.3 3.3C16.97 3.3 19 5.35 19 7.99c0 3.51-3.14 6.37-8.9 11.44L12 20.4Z' }]], true)

export const IconeRecherche = creerIcone([
  ['circle', { cx: '11', cy: '11', r: '7' }],
  ['path', { d: 'm20 20-3.5-3.5' }]
])

export const IconeHorloge = creerIcone([
  ['circle', { cx: '12', cy: '12', r: '9' }],
  ['path', { d: 'M12 7v5l3 2' }]
])

export const IconeCalendrier = creerIcone([
  ['path', { d: 'M4 6h16v14H4Z' }],
  ['path', { d: 'M8 3v6' }],
  ['path', { d: 'M16 3v6' }],
  ['path', { d: 'M4 10h16' }]
])

export const IconeCoeur = creerIcone([['path', { d: 'm12 20.3-1.2-1.09C5.3 14.23 2 11.24 2 7.63 2 4.84 4.2 2.7 6.93 2.7c1.54 0 3.02.73 3.97 1.88.95-1.15 2.42-1.88 3.97-1.88C17.8 2.7 20 4.84 20 7.63c0 3.61-3.3 6.6-8.8 11.58L12 20.3Z' }]])

export const IconeOnde = creerIcone([['path', { d: 'M2 12h4l2-5 4 10 2-5h8' }]])

export const IconeGoutte = creerIcone([
  ['path', { d: 'M12 3c2.8 3.3 5 6.1 5 9.1A5 5 0 0 1 7 12.1C7 9.1 9.2 6.3 12 3Z' }],
  ['path', { d: 'M14.5 13.5a2.5 2.5 0 0 1-5 0' }]
])

export const IconeLien = creerIcone([
  ['path', { d: 'M10 13a5 5 0 0 1 0-7l1.5-1.5a5 5 0 0 1 7 7L17 13' }],
  ['path', { d: 'M14 11a5 5 0 0 1 0 7l-1.5 1.5a5 5 0 1 1-7-7L7 11' }]
])

export const IconeChevronBas = creerIcone([['path', { d: 'm6 9 6 6 6-6' }]])

export const IconeFlecheGauche = creerIcone([
  ['path', { d: 'm15 6-6 6 6 6' }],
  ['path', { d: 'M21 12H9' }]
])

export const IconeCercleValide = creerIcone([
  ['circle', { cx: '12', cy: '12', r: '9' }],
  ['path', { d: 'm8.5 12 2.3 2.4 4.7-5' }]
])

export const IconeCercleFerme = creerIcone([
  ['circle', { cx: '12', cy: '12', r: '9' }],
  ['path', { d: 'm9.5 9.5 5 5' }],
  ['path', { d: 'm14.5 9.5-5 5' }]
])
