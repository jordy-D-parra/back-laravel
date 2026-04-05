<?php $__env->startSection('title', 'Mi Perfil'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">👤 Mi Perfil</h2>
                        <p class="text-muted mb-0">Gestiona tu información personal y configuración de cuenta</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 4rem;">👤</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información Personal -->
        <div class="col-md-6 mb-4">
            <div class="stat-card">
                <h4 class="mb-3">📋 Información Personal</h4>

                <?php if(session('profile_success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('profile_success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('profile_error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e(session('profile_error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('profile.update')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nombre completo</label>
                        <input type="text"
                               class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="name"
                               name="name"
                               value="<?php echo e(old('name', Auth::user()->name)); ?>"
                               required>
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Correo electrónico</label>
                        <input type="email"
                               class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="email"
                               name="email"
                               value="<?php echo e(old('email', Auth::user()->email)); ?>"
                               required>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="form-text">Tu correo electrónico se usará para iniciar sesión.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Rol en el sistema</label>
                        <div class="form-control bg-light">
                            <?php if(Auth::user()->is_admin): ?>
                                <span class="badge bg-success">Administrador</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Usuario</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de registro</label>
                        <div class="form-control bg-light">
                            <?php echo e(Auth::user()->created_at->format('d/m/Y H:i:s')); ?>

                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        💾 Actualizar información
                    </button>
                </form>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="col-md-6 mb-4">
            <div class="stat-card">
                <h4 class="mb-3">🔒 Cambiar Contraseña</h4>

                <?php if(session('password_success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('password_success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('password_error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e(session('password_error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('profile.password')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">Contraseña actual</label>
                        <input type="password"
                               class="form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="current_password"
                               name="current_password"
                               required>
                        <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">Nueva contraseña</label>
                        <input type="password"
                               class="form-control <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="new_password"
                               name="new_password"
                               required>
                        <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="form-text">Mínimo 6 caracteres.</div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label fw-bold">Confirmar nueva contraseña</label>
                        <input type="password"
                               class="form-control"
                               id="new_password_confirmation"
                               name="new_password_confirmation"
                               required>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        🔄 Actualizar contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preguntas de Seguridad -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <h4 class="mb-3">❓ Preguntas de Seguridad</h4>
                <p class="text-muted mb-3">Estas preguntas te ayudarán a recuperar tu contraseña en caso de olvido.</p>

                <?php if(session('security_success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('security_success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('profile.security')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="security_question_1" class="form-label fw-bold">Pregunta 1</label>
                            <select class="form-select <?php $__errorArgs = ['security_question_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="security_question_1"
                                    name="security_question_1"
                                    required>
                                <option value="">Selecciona una pregunta...</option>
                                <option value="¿Nombre de tu primera mascota?" <?php echo e(Auth::user()->security_question_1 == '¿Nombre de tu primera mascota?' ? 'selected' : ''); ?>>¿Nombre de tu primera mascota?</option>
                                <option value="¿Ciudad donde naciste?" <?php echo e(Auth::user()->security_question_1 == '¿Ciudad donde naciste?' ? 'selected' : ''); ?>>¿Ciudad donde naciste?</option>
                                <option value="¿Nombre de tu madre soltera?" <?php echo e(Auth::user()->security_question_1 == '¿Nombre de tu madre soltera?' ? 'selected' : ''); ?>>¿Nombre de tu madre soltera?</option>
                                <option value="¿Comida favorita?" <?php echo e(Auth::user()->security_question_1 == '¿Comida favorita?' ? 'selected' : ''); ?>>¿Comida favorita?</option>
                                <option value="¿Color favorito?" <?php echo e(Auth::user()->security_question_1 == '¿Color favorito?' ? 'selected' : ''); ?>>¿Color favorito?</option>
                            </select>
                            <?php $__errorArgs = ['security_question_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="security_answer_1" class="form-label fw-bold">Respuesta 1</label>
                            <input type="text"
                                   class="form-control <?php $__errorArgs = ['security_answer_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="security_answer_1"
                                   name="security_answer_1"
                                   placeholder="Ingresa tu respuesta"
                                   required>
                            <?php $__errorArgs = ['security_answer_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">No importan las mayúsculas/minúsculas.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="security_question_2" class="form-label fw-bold">Pregunta 2</label>
                            <select class="form-select <?php $__errorArgs = ['security_question_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="security_question_2"
                                    name="security_question_2"
                                    required>
                                <option value="">Selecciona una pregunta...</option>
                                <option value="¿Nombre de tu primera mascota?" <?php echo e(Auth::user()->security_question_2 == '¿Nombre de tu primera mascota?' ? 'selected' : ''); ?>>¿Nombre de tu primera mascota?</option>
                                <option value="¿Ciudad donde naciste?" <?php echo e(Auth::user()->security_question_2 == '¿Ciudad donde naciste?' ? 'selected' : ''); ?>>¿Ciudad donde naciste?</option>
                                <option value="¿Nombre de tu madre soltera?" <?php echo e(Auth::user()->security_question_2 == '¿Nombre de tu madre soltera?' ? 'selected' : ''); ?>>¿Nombre de tu madre soltera?</option>
                                <option value="¿Comida favorita?" <?php echo e(Auth::user()->security_question_2 == '¿Comida favorita?' ? 'selected' : ''); ?>>¿Comida favorita?</option>
                                <option value="¿Color favorito?" <?php echo e(Auth::user()->security_question_2 == '¿Color favorito?' ? 'selected' : ''); ?>>¿Color favorito?</option>
                            </select>
                            <?php $__errorArgs = ['security_question_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="security_answer_2" class="form-label fw-bold">Respuesta 2</label>
                            <input type="text"
                                   class="form-control <?php $__errorArgs = ['security_answer_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="security_answer_2"
                                   name="security_answer_2"
                                   placeholder="Ingresa tu respuesta"
                                   required>
                            <?php $__errorArgs = ['security_answer_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">No importan las mayúsculas/minúsculas.</div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Si cambias tus preguntas de seguridad, también deberás actualizar tus respuestas.
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        ✅ Actualizar preguntas de seguridad
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/jordanys/Escritorio/proyecto x/back-laravel/resources/views/profile/index.blade.php ENDPATH**/ ?>