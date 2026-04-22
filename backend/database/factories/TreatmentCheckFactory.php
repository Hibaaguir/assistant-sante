<?php

namespace Database\Factories;

use App\Models\Treatment;
use App\Models\TreatmentCheck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TreatmentCheck>
 */
class TreatmentCheckFactory extends Factory
{
    protected $model = TreatmentCheck::class;

    public function definition(): array
    {
        $checkDate = Carbon::instance($this->faker->dateTimeBetween('-45 days', 'now'))->toDateString();
        $taken     = $this->faker->boolean(80);

        return [
            'treatment_id'   => Treatment::factory(),
            'user_id'        => User::factory(),
            'check_date'     => $checkDate,
            'medication_key' => 'treatment__dose_1',
            'taken'          => $taken,
            'checked_at'     => $taken
                ? Carbon::parse($checkDate)->setTime(
                    $this->faker->numberBetween(6, 22),
                    $this->faker->numberBetween(0, 59)
                )
                : null,
        ];
    }

    public function taken(): self
    {
        return $this->state(fn () => [
            'taken'      => true,
            'checked_at' => now(),
        ]);
    }

    public function missed(): self
    {
        return $this->state(fn () => [
            'taken'      => false,
            'checked_at' => null,
        ]);
    }
}
