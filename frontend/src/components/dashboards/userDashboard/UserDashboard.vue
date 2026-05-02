<template>
    <div class="user-dashboard-scope w-full px-4 py-4 sm:px-6 lg:px-8 bg-white">

        <!-- En-tête avec titre et bouton d'export PDF -->
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <Typography tag="h1" variant="h1-style">Tableau de bord</Typography>
                <Typography tag="h2" variant="h4-style">Aperçu de votre santé</Typography>
            </div>

            <!-- Bouton désactivé pendant la génération du fichier PDF -->
            <button
                type="button"
                class="inline-flex h-10 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="exportingPdf"
                @click="downloadDashboardPdf"
            >
                {{ exportingPdf ? "Generation..." : "Exporter statistiques" }}
            </button>
        </header>

        <!-- Message de bienvenue personnalisé -->
        <WelcomeCard />

        <!-- Notifications de santé de l'utilisateur -->
        <NotificationsWidget />

        <!-- Zone capturée pour l'export PDF (ref utilisée par la fonction downloadDashboardPdf) -->
        <div ref="pdfTargetRef" class="space-y-5 pt-4">

            <!-- Résumé rapide : 5 indicateurs clés (rythme, tension, O₂, poids, activité) -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                <LastVitalsRow class="contents" />
            </div>

            <!-- Ligne 1 : répartition des traitements · activité physique · top 3 activités -->
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <TreatmentPieChart />
                <ActivityDistributionChart />
                <Top3ActivitiesChart />
            </div>

            <!-- Ligne 2 : évolution des signes vitaux · graphe de comparaison -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <HealthChart />
                <VitalSignsComparisonChart />
            </div>

            <!-- Ligne 3 : courbe progressive des signes vitaux · analyses biologiques -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <VitalSignsProgressiveLine />
                <LabsDistributionChart />
            </div>

            <!-- Ligne 4 : hydratation · suivi des médicaments · tendances du sommeil -->
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <HydrationChart />
                <TreatmentMonthlyChart />
                <SleepTrendsChart />
            </div>

        </div>
    </div>
</template>

<script setup>
import { nextTick, ref } from "vue";
import { toPng } from "html-to-image";
import jsPDF from "jspdf";
import HealthChart from "./HealthChart.vue";
import TreatmentPieChart from "./TreatmentPieChart.vue";
import ActivityDistributionChart from "./ActivityDistributionChart.vue";
import Top3ActivitiesChart from "./Top3ActivitiesChart.vue";
import HydrationChart from "./HydrationChart.vue";
import TreatmentMonthlyChart from "./TreatmentMonthlyChart.vue";
import VitalSignsComparisonChart from "./VitalSignsComparisonChart.vue";
import SleepTrendsChart from "./SleepTrendsChart.vue";
import NotificationsWidget from "./NotificationsWidget.vue";
import WelcomeCard from "./WelcomeCard.vue";
import VitalSignsProgressiveLine from "./VitalSignsProgressiveLine.vue";
import LabsDistributionChart from "./LabsDistributionChart.vue";
import LastVitalsRow from "./LastVitalsRow.vue";
import Typography from "../../ui/Typography.vue";

// Référence vers le bloc HTML à capturer pour le PDF
const pdfTargetRef = ref(null);

// Indicateur vrai pendant la génération (bloque le bouton pour éviter les doubles clics)
const exportingPdf = ref(false);

// Capture le dashboard en image puis génère un fichier PDF téléchargeable
async function downloadDashboardPdf() {
    if (!pdfTargetRef.value || exportingPdf.value) return;

    exportingPdf.value = true;
    try {
        // Attendre que le DOM soit totalement rendu avant la capture
        await nextTick();

        // Convertir le bloc HTML en image PNG (résolution x2 pour la qualité)
        const dataUrl = await toPng(pdfTargetRef.value, {
            cacheBust: true,
            pixelRatio: 2,
            backgroundColor: "#ffffff",
        });

        // Charger l'image pour lire sa hauteur réelle
        const image = new Image();
        await new Promise((resolve, reject) => {
            image.onload = resolve;
            image.onerror = reject;
            image.src = dataUrl;
        });

        // Créer le document PDF au format A4 portrait
        const pdf         = new jsPDF({ orientation: "portrait", unit: "mm", format: "a4" });
        const margin      = 10;
        const usableWidth  = pdf.internal.pageSize.getWidth() - margin * 2;
        const usableHeight = pdf.internal.pageSize.getHeight() - margin * 2;
        const imageHeight  = (image.height * usableWidth) / image.width;

        // Découper l'image sur plusieurs pages A4 si nécessaire
        let offset = 0;
        while (offset < imageHeight) {
            if (offset > 0) pdf.addPage();
            pdf.addImage(dataUrl, "PNG", margin, margin - offset, usableWidth, imageHeight, undefined, "FAST");
            offset += usableHeight;
        }

        // Télécharger le PDF avec la date du jour dans le nom
        const today = new Date().toISOString().slice(0, 10);
        pdf.save(`dashboard-${today}.pdf`);

    } catch (error) {
        console.error("Erreur export PDF :", error);
    } finally {
        exportingPdf.value = false;
    }
}
</script>

<style scoped>
/* Tailles de titres personnalisées pour le dashboard patient */
.user-dashboard-scope :deep(h1) { font-size: 2.15rem !important; line-height: 1.2 !important; }
.user-dashboard-scope :deep(h2) { font-size: 1.35rem !important; line-height: 1.25 !important; }
.user-dashboard-scope :deep(h3) { font-size: 1.2rem !important;  line-height: 1.3 !important; }
.user-dashboard-scope :deep(h4) { font-size: 1.05rem !important; }
.user-dashboard-scope :deep(h5) { font-size: 0.95rem !important; }
.user-dashboard-scope :deep(h6) { font-size: 0.9rem !important; }

/* Ajustement des titres sur mobile */
@media (max-width: 768px) {
    .user-dashboard-scope :deep(h1) { font-size: 1.85rem !important; }
    .user-dashboard-scope :deep(h2) { font-size: 1.2rem !important; }
}
</style>
