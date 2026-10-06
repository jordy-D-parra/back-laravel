// resources/js/auth/robot-intro.js
// ============================================================
// ROBOT INTRO ANIMATION - Secuencia profesional de bienvenida
// ============================================================

(function () {
    'use strict';

    const SESSION_KEY = 'robot_intro_shown';

    // Si ya se mostró en esta sesión, no volver a mostrarlo
    if (sessionStorage.getItem(SESSION_KEY) === 'true') {
        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('robotIntroOverlay');
            if (overlay) overlay.remove();
            document.body.classList.remove('intro-active');
        });
        return;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const overlay = document.getElementById('robotIntroOverlay');
        const robot = document.getElementById('introRobot');
        const bubble = document.getElementById('robotBubble');
        const panelSistema = document.getElementById('panelSistema');
        const panelLogin = document.getElementById('panelLoginIntro');
        const skipBtn = document.getElementById('skipIntroBtn');
        const loginReal = document.getElementById('loginReal');

        if (!overlay || !robot) return;

        // ⭐ CLAVE: marcar el body como "intro-active" para que el CSS
        // posicione el login real FUERA de la pantalla (abajo)
        document.body.classList.add('intro-active');

        // Bloquear scroll mientras se reproduce
        document.body.style.overflow = 'hidden';

        // ============================================================
        // UTILIDADES
        // ============================================================
        const setBubble = (text) => {
            if (!bubble) return;
            bubble.textContent = text;
            bubble.classList.add('show');
        };

        const hideBubble = () => {
            if (!bubble) return;
            bubble.classList.remove('show');
        };

        const createMagicParticles = (count = 20) => {
            const rect = robot.getBoundingClientRect();
            for (let i = 0; i < count; i++) {
                setTimeout(() => {
                    const p = document.createElement('div');
                    p.className = 'magic-particle';
                    const x = rect.left + rect.width / 2 + (Math.random() - 0.5) * 200;
                    const y = rect.top + rect.height / 2 + (Math.random() - 0.5) * 200;
                    p.style.left = x + 'px';
                    p.style.top = y + 'px';
                    document.body.appendChild(p);

                    const tx = (Math.random() - 0.5) * 300;
                    const ty = (Math.random() - 0.5) * 300;
                    p.animate(
                        [
                            { transform: 'translate(0,0) scale(1)', opacity: 1 },
                            { transform: `translate(${tx}px, ${ty}px) scale(0)`, opacity: 0 }
                        ],
                        { duration: 800, easing: 'cubic-bezier(0.4, 0, 0.2, 1)' }
                    ).onfinish = () => p.remove();
                }, i * 30);
            }
        };

        // Finaliza la intro
        const finishIntro = () => {
            hideBubble();

            // Marcar el login real como "done" (posición natural)
            if (loginReal) {
                loginReal.classList.remove('lifting');
                loginReal.classList.add('done');
            }

            // Quitar la clase intro-active (para que el login quede en su sitio)
            document.body.classList.remove('intro-active');

            overlay.classList.add('hidden');
            document.body.style.overflow = '';

            setTimeout(() => {
                overlay.remove();
            }, 800);

            sessionStorage.setItem(SESSION_KEY, 'true');
        };

        // Saltar intro
        if (skipBtn) {
            skipBtn.addEventListener('click', () => {
                if (loginReal) {
                    loginReal.classList.remove('lifting');
                    loginReal.classList.add('done');
                }
                document.body.classList.remove('intro-active');
                finishIntro();
            });
        }

        // ============================================================
        // SECUENCIA DE ANIMACIÓN
        // ============================================================

        // 1) Robot entra caminando (0s - 2.5s)
        robot.classList.add('walking');

        // 2) A los 2.5s: se detiene y saluda
        setTimeout(() => {
            robot.classList.remove('walking');
            robot.classList.add('waving');
            setBubble('¡Hola! Como estas, Mucho Gusto me llamo SIGIET');

            setTimeout(() => {
                robot.classList.remove('waving');
            }, 2000);
        }, 2500);

        // 3) A los 5s: jala el panel del SISTEMA
        setTimeout(() => {
            hideBubble();
            robot.classList.add('pulling-left');
            createMagicParticles(25);

            if (panelSistema) panelSistema.classList.add('pulled');

            setTimeout(() => {
                robot.classList.remove('pulling-left');
                setBubble('¡en la parte izquierda esta el titulo del sistema');
            }, 1500);

            setTimeout(() => hideBubble(), 3000);
        }, 5000);

        // 4) A los 8s: jala el panel del LOGIN
        setTimeout(() => {
            hideBubble();
            robot.classList.add('pulling-right');
            createMagicParticles(25);

            if (panelLogin) panelLogin.classList.add('pulled');

            setTimeout(() => {
                robot.classList.remove('pulling-right');
                setBubble('¡Y aqui en la parte derecha esta el panel de inicio de sesión!');
            }, 1500);

            setTimeout(() => hideBubble(), 3000);
        }, 8000);

        // 5) A los 11s: se despide con la mano
        setTimeout(() => {
            hideBubble();
            robot.classList.add('goodbye');
            setBubble('¡Adelante! ✨');

            setTimeout(() => {
                robot.classList.remove('goodbye');
            }, 2500);
        }, 11000);

        // 6) A los 13.5s: se mueve ABAJO al CENTRO (se agacha)
        setTimeout(() => {
            hideBubble();
            robot.classList.add('moving-to-center-bottom');
            robot.classList.add('arms-down');
        }, 13500);

        // 7) A los 14.3s: coloca ambas manos DEBAJO del login real
        setTimeout(() => {
            setBubble('¡Toma! 🚀');
            robot.classList.remove('arms-down');
            robot.classList.add('grabbing-login');
            createMagicParticles(30);
        }, 14300);

        // 8) A los 15s: empieza a LEVANTAR el login + sube con él
        setTimeout(() => {
            hideBubble();
            createMagicParticles(50);

            // El robot pasa a modo "levantando" y sube junto con el panel
            robot.classList.remove('grabbing-login');
            robot.classList.remove('moving-to-center-bottom');
            robot.classList.add('lifting-up');

            // El panel de intro del login sube y se desvanece
            if (panelLogin) {
                panelLogin.classList.remove('pulled');
                panelLogin.classList.add('lifting-up');
            }

            // El panel del sistema se desvanece al fondo
            if (panelSistema) {
                panelSistema.classList.remove('pulled');
                panelSistema.classList.add('fading-out');
            }

            // ⭐ EL LOGIN REAL EMPIEZA A SUBIR DESDE ABAJO
            // Ya está posicionado fuera de pantalla por el CSS
            // (body.intro-active #loginReal { transform: translateY(100vh); })
            // Solo agregamos la clase "lifting" para que anime a subir.
            if (loginReal) {
                loginReal.classList.add('lifting');
            }
        }, 15000);

        // 9) A los 18s: el robot desaparece al llegar arriba
        setTimeout(() => {
            robot.classList.remove('lifting-up');
            robot.classList.add('vanished');
        }, 18000);

        // 10) A los 18.5s: el overlay se desvanece
        setTimeout(() => {
            overlay.classList.add('powerpoint-exit');
        }, 18500);

        // 11) A los 19.3s: se limpia todo y el login queda seco
        setTimeout(() => {
            finishIntro();
        }, 19300);
    });

    // ============================================================
    // ESTRELLAS DE FONDO
    // ============================================================
    document.addEventListener('DOMContentLoaded', () => {
        const starsContainer = document.getElementById('robotIntroStars');
        if (!starsContainer) return;

        for (let i = 0; i < 60; i++) {
            const star = document.createElement('div');
            star.className = 'robot-intro-star';
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.animationDelay = Math.random() * 3 + 's';
            star.style.animationDuration = (2 + Math.random() * 2) + 's';
            starsContainer.appendChild(star);
        }
    });
})();