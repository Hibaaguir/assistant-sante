<?php

require 'bootstrap/app.php';

$app = app();

$user = \App\Models\User::orderByDesc('id')->first();
$profile = \App\Models\ProfilSante::where('user_id', $user->id)->first();

echo json_encode([
    'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
    ],
    'profile' => [
        'id' => $profile?->id,
        'objectifs' => $profile?->objectifs,
        'allergies' => $profile?->allergies,
        'maladies_chroniques' => $profile?->maladies_chroniques,
        'traitements' => $profile?->traitements,
        'consulte_medecin' => $profile?->consulte_medecin,
        'medecin_peut_consulter' => $profile?->medecin_peut_consulter,
        'medecin_email' => $profile?->medecin_email,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
?>
