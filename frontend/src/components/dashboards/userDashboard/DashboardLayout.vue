<!--
  DashboardLayout.vue
  Patient main layout: notifications and health chart.
-->
<template>
    <div class="user-dashboard-scope w-full px-4 py-4 sm:px-6 lg:px-8 bg-white">
        <header
            class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <Typography tag="h1" variant="h1-style">
                    Tableau de bord
                </Typography>
                <Typography tag="h2" variant="h4-style">
                    Aperçu de votre santé
                </Typography>
            </div>

            <button
                type="button"
                class="inline-flex h-10 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="exportingPdf"
                @click="downloadDashboardPdf"
            >
                {{ exportingPdf ? "Generation..." : "Telecharger PDF" }}
            </button>
        </header>

        <WelcomeCard />

        <NotificationsWidget />

        <div ref="pdfTargetRef" class="space-y-5">
            <div class="flex justify-start">
                <WeightComparisonChart />
            </div>

            <!-- Row 1 (3 cols): treatment pie · activity distribution · top 5 activities -->
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <TreatmentPieChart />
                <ActivityDistributionChart />
                <Top3ActivitiesChart />
            </div>

            <!-- Row 2 (2 cols): vital signs evolution · vital signs comparison -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <HealthChart />
                <VitalSignsComparisonChart />
            </div>

            <!-- Progressive line + Labs distribution -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <VitalSignsProgressiveLine />
                <LabsDistributionChart />
            </div>

            <!-- Row 3 (3 cols): hydration · treatment distribution · sleep trends -->
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
import WeightComparisonChart from "./WeightComparisonChart.vue";
import Typography from "../../ui/Typography.vue";

const pdfTargetRef = ref(null);
const exportingPdf = ref(false);

async function downloadDashboardPdf() {
    if (!pdfTargetRef.value || exportingPdf.value) return;

    exportingPdf.value = true;
    try {
        await nextTick();

        const dataUrl = await toPng(pdfTargetRef.value, {
            cacheBust: true,
            pixelRatio: 2,
            backgroundColor: "#ffffff",
        });

        const image = new Image();
        await new Promise((resolve, reject) => {
            image.onload = resolve;
            image.onerror = reject;
            image.src = dataUrl;
        });

        const pdf = new jsPDF({
            orientation: "portrait",
            unit: "mm",
            format: "a4",
        });
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();

        const margin = 10;
        const usableWidth = pageWidth - margin * 2;
        const usableHeight = pageHeight - margin * 2;
        const imageHeight = (image.height * usableWidth) / image.width;

        let offset = 0;
        while (offset < imageHeight) {
            if (offset > 0) pdf.addPage();
            pdf.addImage(
                dataUrl,
                "PNG",
                margin,
                margin - offset,
                usableWidth,
                imageHeight,
                undefined,
                "FAST",
            );
            offset += usableHeight;
        }

        const stamp = new Date().toISOString().slice(0, 10);
        pdf.save(`dashboard-${stamp}.pdf`);
    } catch (error) {
        console.error("PDF export failed:", error);
    } finally {
        exportingPdf.value = false;
    }
}
</script>

<style scoped>
.user-dashboard-scope :deep(h1) {
    font-size: 2.15rem !important;
    line-height: 1.2 !important;
}

.user-dashboard-scope :deep(h2) {
    font-size: 1.35rem !important;
    line-height: 1.25 !important;
}

.user-dashboard-scope :deep(h3) {
    font-size: 1.2rem !important;
    line-height: 1.3 !important;
}

.user-dashboard-scope :deep(h4) {
    font-size: 1.05rem !important;
}

.user-dashboard-scope :deep(h5) {
    font-size: 0.95rem !important;
}

.user-dashboard-scope :deep(h6) {
    font-size: 0.9rem !important;
}

@media (max-width: 768px) {
    .user-dashboard-scope :deep(h1) {
        font-size: 1.85rem !important;
    }

    .user-dashboard-scope :deep(h2) {
        font-size: 1.2rem !important;
    }
}
</style>
