<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('health_lab_results')) {
            return;
        }

        $legacyStatusValues = ['normal', 'eleve', 'bas', 'Normal', 'Eleve', 'Bas', 'Élevé', 'élevé'];

        $mapping = [
            'Glycemie a jeun' => ['type' => 'Biologie sanguine', 'result' => 'Glycémie', 'unit' => 'mmol/L'],
            'HbA1c' => ['type' => 'Biologie sanguine', 'result' => 'HbA1c', 'unit' => '%'],
            'Cholesterol total' => ['type' => 'Bilan lipidique', 'result' => 'Cholestérol total', 'unit' => 'mmol/L'],
            'LDL cholesterol' => ['type' => 'Bilan lipidique', 'result' => 'LDL', 'unit' => 'mmol/L'],
            'HDL cholesterol' => ['type' => 'Bilan lipidique', 'result' => 'HDL', 'unit' => 'mmol/L'],
            'Triglycerides' => ['type' => 'Bilan lipidique', 'result' => 'Triglycérides', 'unit' => 'mmol/L'],
            'Creatinine' => ['type' => 'Biologie sanguine', 'result' => 'Créatinine', 'unit' => 'mg/L'],
            'Hemoglobine' => ['type' => 'Hématologie', 'result' => 'Hémoglobine', 'unit' => 'g/dL'],
            'Vitamine D' => ['type' => 'Biologie sanguine', 'result' => 'Ferritine', 'unit' => 'ng/mL'],
        ];

        foreach ($mapping as $oldType => $next) {
            DB::table('health_lab_results')
                ->where('analysis_type', $oldType)
                ->whereIn('analysis_result', $legacyStatusValues)
                ->update([
                    'analysis_type' => $next['type'],
                    'analysis_result' => $next['result'],
                    'unit' => $next['unit'],
                ]);
        }
    }

    public function down(): void
    {
        // No-op: this data normalization is intentionally irreversible.
    }
};
