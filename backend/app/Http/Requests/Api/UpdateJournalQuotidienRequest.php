<?php

namespace App\Http\Requests\Api;

// Import de Rule pour utiliser des règles de validation avancées
use Illuminate\Validation\Rule;

// Classe utilisée pour valider les données lors de la mise à jour d'une entrée du journal
// Elle hérite des règles du StoreJournalQuotidienRequest
class UpdateJournalQuotidienRequest extends StoreJournalQuotidienRequest
{
    // Définition des règles de validation pour la mise à jour
    public function rules(): array
    {
        // Récupère toutes les règles définies dans StoreJournalQuotidienRequest
        $rules = parent::rules();

        // Modifie certaines règles pour permettre une mise à jour partielle (champ optionnel)
        $rules['entry_date'] = ['sometimes', 'date'];
        $rules['sugar'] = ['sometimes', Rule::in(['low', 'medium', 'high'])];
        $rules['tobacco'] = ['sometimes', 'boolean'];
        $rules['alcohol'] = ['sometimes', 'boolean'];

        // Retourne la liste finale des règles de validation
        return $rules;
    }
}