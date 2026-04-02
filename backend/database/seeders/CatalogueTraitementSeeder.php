<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatalogueTraitement;

class CatalogueTraitementSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['type' => 'Anti-inflammatoire', 'nom' => 'Ibuprofene'],
            ['type' => 'Anti-inflammatoire', 'nom' => 'Diclofenac'],
            ['type' => 'Anti-inflammatoire', 'nom' => 'Ketoprofene'],
            ['type' => 'Anti-inflammatoire', 'nom' => 'Naproxene'],
            ['type' => 'Antibiotique', 'nom' => 'Amoxicilline'],
            ['type' => 'Antibiotique', 'nom' => 'Cefixime'],
            ['type' => 'Antibiotique', 'nom' => 'Azithromycine'],
            ['type' => 'Antibiotique', 'nom' => 'Ciprofloxacine'],
            ['type' => 'Antidouleur', 'nom' => 'Paracetamol'],
            ['type' => 'Antidouleur', 'nom' => 'Tramadol'],
            ['type' => 'Antidouleur', 'nom' => 'Codeine'],
            ['type' => 'Antihypertenseur', 'nom' => 'Amlodipine'],
            ['type' => 'Antihypertenseur', 'nom' => 'Captopril'],
            ['type' => 'Antidiabetique', 'nom' => 'Metformine'],
            ['type' => 'Antidiabetique', 'nom' => 'Gliclazide'],
            ['type' => 'Anticoagulant', 'nom' => 'Warfarine'],
            ['type' => 'Anticoagulant', 'nom' => 'Heparine'],
            ['type' => 'Antiallergique', 'nom' => 'Cetirizine'],
            ['type' => 'Antiallergique', 'nom' => 'Loratadine'],
            ['type' => 'Antidepresseur', 'nom' => 'Sertraline'],
            ['type' => 'Antidepresseur', 'nom' => 'Fluoxetine'],
            ['type' => 'Corticoide', 'nom' => 'Prednisone'],
            ['type' => 'Traitement hormonal', 'nom' => 'Insuline'],
            ['type' => 'Supplement vitaminique', 'nom' => 'Vitamine D'],
            ['type' => 'Inhalateur respiratoire', 'nom' => 'Salbutamol'],
        ];
        foreach ($data as $item) {
            CatalogueTraitement::firstOrCreate([
                'type' => $item['type'],
                'nom' => $item['nom'],
            ]);
        }
    }
}
