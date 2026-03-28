<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('journal_entries')) {
            return;
        }

        DB::table('journal_entries')
            ->select(['id', 'meals', 'calories'])
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $meals = is_array($row->meals)
                        ? $row->meals
                        : json_decode((string) ($row->meals ?? ''), true);

                    if (! is_array($meals)) {
                        continue;
                    }

                    $updatedMeals = [];
                    $hasChanges = false;
                    $totalCalories = 0;

                    foreach ($meals as $meal) {
                        if (! is_array($meal)) {
                            $updatedMeals[] = $meal;
                            continue;
                        }

                        $rawCalories = $meal['calories'] ?? null;
                        if ($rawCalories !== null && $rawCalories !== '' && is_numeric($rawCalories)) {
                            $totalCalories += max(0, min((int) round((float) $rawCalories), 65535));
                            if ($totalCalories > 65535) {
                                $totalCalories = 65535;
                            }
                        }

                        if (array_key_exists('calories', $meal)) {
                            unset($meal['calories']);
                            $hasChanges = true;
                        }

                        $updatedMeals[] = $meal;
                    }

                    if (! $hasChanges) {
                        continue;
                    }

                    $nextCalories = $row->calories;
                    if ($nextCalories === null && $totalCalories > 0) {
                        $nextCalories = $totalCalories;
                    }

                    DB::table('journal_entries')
                        ->where('id', $row->id)
                        ->update([
                            'meals' => json_encode($updatedMeals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            'calories' => $nextCalories,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // No-op: unable to restore per-meal calories once removed.
    }
};
