<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Laravel génère automatiquement ce token pour sécuriser les requêtes POST -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Register - Assistant Santé</title>

  @vite(['resources/js/app.js'])
</head>
<body class="antialiased">
  <div id="app">
    <register-form></register-form>
  </div>
</body>
</html>
