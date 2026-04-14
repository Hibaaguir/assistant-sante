<!--
  DashboardLayout.vue
  Patient main layout: notifications and health chart.
-->
<template>
    <div class="w-full px-4 py-4 sm:px-6 lg:px-8 bg-white">
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-[42px] font-bold leading-tight text-blue-600">
                    Tableau de bord
                </h1>
                <p class="mt-3 text-base text-slate-600 font-medium">
                    Aperçu de votre santé
                </p>
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

        const pdf = new jsPDF({ orientation: "portrait", unit: "mm", format: "a4" });
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
