<?php

// Ce modèle représente la table "users" dans la base de données
namespace App\Models;

// Permet de créer des utilisateurs de test (factory)
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Classe spéciale pour gérer l'authentification 
use Illuminate\Foundation\Auth\User as Authenticatable;

// Permet d’envoyer des notifications 
use Illuminate\Notifications\Notifiable;

// Permet de créer et gérer des tokens API 
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // On utilise ces fonctionnalités dans le modèle
    use HasFactory, Notifiable, HasApiTokens;

    // Les champs qu’on a le droit d’enregistrer dans la base de données
    // Cela protège contre l’injection de données non autorisées
    protected $fillable = [
        'name',
        'email',
        'password',
        'date_of_birth',
    ];

    // Les champs qui ne doivent pas apparaître quand on retourne un JSON
    // Important pour la sécurité
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Conversion automatique de certains champs
    protected function casts(): array
    {
        return [
            // Convertit automatiquement en date
            'email_verified_at' => 'datetime',

            // Convertit date_of_birth en format date
            'date_of_birth' => 'date',

            // Hash automatique du mot de passe avant sauvegarde
            'password' => 'hashed',
        ];
    }

    // Relation : un utilisateur possède un seul profil santé
    public function profilSante()
    {
        return $this->hasOne(\App\Models\ProfilSante::class);
    }

}
