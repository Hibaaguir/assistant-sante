<?php

namespace Database\Factories;

use App\Models\TreatmentCatalogItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthTreatmentCheck>
 */
class HealthTreatmentCheckFactory extends Factory
{
    public function definition(): array
    {
        $taken = fake()->boolean(82);
        $checkDate = fake()->dateTimeBetween('-45 days', 'now');
        $catalogItem = TreatmentCatalogItem::query()
            ->where('name', 'not like', 'Traitement %')
            ->inRandomOrder()
            ->first(['type', 'name']);

        if ($catalogItem === null) {
            $catalogItem = TreatmentCatalogItem::query()->firstOrCreate(
                ['type' => 'Antidouleur', 'name' => 'Paracetamol'],
                ['created_by_user_id' => null],
            );
        }

        $name = trim((string) ($catalogItem->name ?? ''));
        $type = trim((string) ($catalogItem->type ?? ''));
        if ($name === '') {
            $name = $type !== '' ? $type : 'Paracetamol';
        }

        $keyBase = Str::slug($name);
        if ($keyBase === '') {
            $keyBase = 'traitement';
        }

        return [
            'user_id' => User::factory(),
            'check_date' => $checkDate->format('Y-m-d'),
            'treatment_type' => $type,
            'medication_key' => $keyBase . '-' . fake()->numberBetween(1, 4),
            'treatment_name' => $name,
            'dose' => fake()->randomElement(['5 mg', '10 mg', '250 mg', '1 comprime']),
            'taken' => $taken,
            'checked_at' => $taken ? fake()->dateTimeBetween($checkDate->format('Y-m-d') . ' 06:00:00', $checkDate->format('Y-m-d') . ' 22:00:00') : null,
        ];
    }
}
