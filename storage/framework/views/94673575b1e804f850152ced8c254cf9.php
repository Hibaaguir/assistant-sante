<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Register - Assistant Santé</title>

  <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
</head>
<body class="antialiased">
  <div id="app">
    <register-form></register-form>
  </div>
</body>
</html>
<?php /**PATH C:\Users\Asus\assistant-sante\resources\views/register.blade.php ENDPATH**/ ?>