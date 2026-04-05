<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Mi Aplicación'); ?></title>

    <!-- Cargar Bootstrap -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/bootstrap.css', 'resources/js/app.js']); ?>

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <?php echo $__env->yieldContent('content'); ?>
</body>
</html>
<?php /**PATH /home/jordanys/Escritorio/proyecto x/back-laravel/resources/views/layouts/app.blade.php ENDPATH**/ ?>