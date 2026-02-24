<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
      <!-- a page responsive (adaptée aux téléphones) -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Santé</title>
     <!-- Charge le fichier app.js avec Vite (JavaScript / Vue) -->
    @vite(['resources/js/app.js'])

</head>
 <!-- antialiased améliore l’affichage des textes -->
<body class="antialiased">
     <!-- Zone principale où Vue va s’exécuter -->
    <div id="app">
        <!-- Composant Vue qui affiche l’interface Profil Santé -->
        <profil-sante></profil-sante>
    </div>
</body>
</html>
