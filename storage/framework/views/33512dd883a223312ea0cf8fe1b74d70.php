<?php $__env->startSection('title', 'Registro de Actividad'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">📋 Registro de Actividad</h2>
                        <p class="text-muted mb-0">Historial de acciones realizadas por los usuarios</p>
                    </div>
                    <div style="font-size: 2rem;">📊</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                              <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>IP</th>
                              </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                              <tr>
                                <td><?php echo e($log->created_at->format('d/m/Y H:i:s')); ?></td>
                                <td>
                                    <strong><?php echo e($log->user->name ?? 'Usuario eliminado'); ?></strong><br>
                                    <small class="text-muted"><?php echo e($log->user->email ?? ''); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo e($log->action_color); ?>">
                                        <?php echo e($log->action_icon); ?> <?php echo e(ucfirst(str_replace('_', ' ', $log->action))); ?>

                                    </span>
                                </td>
                                <td><?php echo e($log->description); ?></td>
                                <td><code><?php echo e($log->ip_address); ?></code></td>
                              </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                              <tr>
                                <td colspan="5" class="text-center">No hay registros de actividad</td>
                              </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php echo e($logs->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/jordanys/Escritorio/proyecto x/back-laravel/resources/views/audit/index.blade.php ENDPATH**/ ?>