document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;

    const idMenuAlumno = body.dataset.idMenu;
    let idDiaActivo = body.dataset.diaActivo;
    let idTiempoActivo = body.dataset.tiempoActivo;
    const objetivoBase = parseInt(body.dataset.objetivo || "2000", 10);
    let objetivoCalorico = leerMetaGuardada();
    let caloriasActualesDia = 0;

    const listaPlatillos = document.getElementById("listaPlatillos");
    const menuDia = document.getElementById("menuDia");
    const menuSemana = document.getElementById("menuSemana");
    const listaComprasMenu = document.getElementById("listaComprasMenu");
    const statKcalElegidas = document.getElementById("statKcalElegidas");
    const statAgua = document.getElementById("statAgua");
    const toast = document.getElementById("toast");
    const buscarPlatillo = document.getElementById("buscarPlatillo");
    const limpiarBusqueda = document.getElementById("limpiarBusqueda");
    const contadorPlatillos = document.getElementById("contadorPlatillos");
    const estadoBusqueda = document.getElementById("estadoBusqueda");
    const inputMetaDiaria = document.getElementById("inputMetaDiaria");
    const btnGuardarMetaDiaria = document.getElementById("btnGuardarMetaDiaria");
    const heroMetaObjetivo = document.getElementById("heroMetaObjetivo");
    const statMetaKcal = document.getElementById("statMetaKcal");
    const mensajeMetaDiaria = document.getElementById("mensajeMetaDiaria");
    const iconoMensajeMeta = document.getElementById("iconoMensajeMeta");
    const tituloMensajeMeta = document.getElementById("tituloMensajeMeta");
    const textoMensajeMeta = document.getElementById("textoMensajeMeta");

    const preparacionesRespaldo = {
            "Yogur con fruta y avena": [
                    "Coloca el yogur en un recipiente.",
                    "Agrega las fresas y la avena.",
                    "Espolvorea canela y mezcla."
            ],
            "Tostada con aguacate y huevo": [
                    "Tuesta el pan.",
                    "Machaca el aguacate y úntalo sobre la tostada.",
                    "Rebana el huevo y colócalo encima.",
                    "Agrega sal y pimienta."
            ],
            "Omelette de claras con verduras": [
                    "Saltea las verduras con el aceite.",
                    "Agrega las claras batidas.",
                    "Cuando empiece a cocerse, añade el queso.",
                    "Dobla el omelette y sirve."
            ],
            "Quesadilla ligera": [
                    "Coloca el queso en la tortilla y calienta en un comal.",
                    "Dobla la tortilla y cocina hasta que el queso se derrita.",
                    "Sirve con nopales y salsa."
            ],
            "Avena con plátano y nuez": [
                    "Hierve la leche y agrega la avena.",
                    "Cocina durante 5 minutos, moviendo constantemente.",
                    "Sirve y añade el plátano en rodajas y la nuez."
            ],
            "Mollete ligero": [
                    "Tuesta el pan.",
                    "Unta los frijoles y agrega el queso.",
                    "Gratina ligeramente.",
                    "Sirve con pico de gallo y aguacate."
            ],
            "Yogur con fruta y granola": [
                    "Coloca el yogur en un recipiente.",
                    "Añade el mango y la granola.",
                    "Espolvorea canela."
            ],
            "Huevos revueltos con tortilla y aguacate": [
                    "Sofríe el jitomate y la cebolla con el aceite.",
                    "Agrega los huevos y revuelve hasta cocer.",
                    "Sirve con las tortillas y el aguacate."
            ],
            "Sándwich de pavo y queso": [
                    "Arma el sándwich con todos los ingredientes.",
                    "Acompaña con la manzana."
            ],
            "Sandwich de pavo y queso": [
                    "Arma el sándwich con todos los ingredientes.",
                    "Acompaña con la manzana."
            ],
            "Hotcakes de avena y plátano": [
                    "Licúa la avena, el huevo y el plátano.",
                    "Cocina pequeños hotcakes en un sartén ligeramente engrasado.",
                    "Sirve con la miel."
            ],
            "Chilaquiles ligeros": [
                    "Calienta la salsa y agrega los totopos horneados.",
                    "Mezcla rápidamente para que no se remojen demasiado.",
                    "Añade el queso, la cebolla y el huevo encima."
            ],
            "Manzana con canela": [
                    "Lava y corta la manzana en gajos.",
                    "Espolvorea canela y consume."
            ],
            "Pepino con limón": [
                    "Corta el pepino en rodajas.",
                    "Agrega limón y chile al gusto."
            ],
            "Yogur natural": [
                    "Sirve frío y consume."
            ],
            "Uvas y queso panela": [
                    "Lava las uvas.",
                    "Acompaña con el queso en cubos."
            ],
            "Uvas con queso panela": [
                    "Lava las uvas.",
                    "Acompaña con el queso en cubos."
            ],
            "Palomitas naturales": [
                    "Prepara las palomitas sin mantequilla.",
                    "Agrega una pizca de sal."
            ],
            "Tostada de aguacate": [
                    "Machaca el aguacate.",
                    "Úntalo sobre la tostada.",
                    "Agrega jitomate, limón y sal."
            ],
        "Ensalada de pollo y manzana": [
                        "Mezcla la lechuga y la manzana en un plato.",
                        "Añade el pollo desmenuzado.",
                        "Agrega el aceite de oliva, el jugo de limón, sal y pimienta.",
                        "Revuelve y sirve."
            ],
        "Tostadas de atún": [
                        "Mezcla el atún con el pico de gallo.",
                        "Coloca la lechuga sobre las tostadas.",
                        "Agrega el atún y el aguacate en rebanadas.",
                        "Exprime limón antes de comer."
            ],
        "Bowl de arroz, pollo y verduras": [
                        "Coloca el arroz en un tazón.",
                        "Añade el pollo en cubos.",
                        "Agrega las verduras.",
                        "Aliña con aceite, limón, sal y pimienta."
            ],
        "Enchiladas ligeras de pollo": [
                        "Calienta las tortillas.",
                        "Rellénalas con el pollo y dóblalas.",
                        "Baña con la salsa caliente.",
                        "Agrega queso y cebolla."
            ],
        "Filete de pescado con verduras": [
                        "Sazona el pescado con ajo, limón y especias.",
                        "Cocínalo a la plancha.",
                        "Saltea las verduras con el aceite.",
                        "Sirve junto con la papa cocida."
            ],
        "Burrito ligero de pollo": [
                        "Calienta la tortilla.",
                        "Coloca el pollo, frijoles y verduras.",
                        "Añade el yogur como aderezo.",
                        "Enrolla y sirve."
            ],
        "Tacos de carne asada": [
                        "Asa la carne y córtala en tiras.",
                        "Calienta las tortillas.",
                        "Rellénalas con la carne.",
                        "Añade cebolla, cilantro, salsa y aguacate."
            ],
        "Ensalada completa de atún": [
                        "Mezcla las verduras y el atún.",
                        "Agrega los huevos en cuartos.",
                        "Aliña con el aceite de oliva.",
                        "Acompaña con las galletas."
            ],
        "Pasta con pollo y verduras": [
                        "Cocina la pasta según las instrucciones.",
                        "Saltea el pollo hasta que esté cocido.",
                        "Añade las verduras y cocina 2 minutos más.",
                        "Mezcla con la pasta y sirve."
            ],
        "Bowl mexicano": [
                        "Coloca una base de lechuga.",
                        "Agrega arroz, frijoles y pollo.",
                        "Añade aguacate y pico de gallo.",
                        "Mezcla antes de comer."
            ],
        "Ensalada de pollo con pan tostado": [
                        "Cocina el pollo y córtalo en tiras.",
                        "Mezcla las verduras.",
                        "Agrega el pollo y el aceite de oliva.",
                        "Sirve con el pan tostado."
            ],
        "Omelette de espinacas y queso": [
                        "Saltea las espinacas.",
                        "Agrega los huevos batidos.",
                        "Añade el queso y dobla el omelette.",
                        "Sirve con las tortillas."
            ],
        "Sopa de verduras con queso panela": [
                        "Calienta la sopa.",
                        "Agrega el queso en cubos.",
                        "Acompaña con las galletas."
            ],
        "Quesadillas de pollo y verduras": [
                        "Rellena las tortillas con pollo y queso.",
                        "Calienta en comal hasta que el queso se derrita.",
                        "Sirve con nopales y salsa."
            ],
        "Ensalada mediterránea": [
                        "Mezcla las verduras.",
                        "Añade el pollo en tiras y el queso.",
                        "Aliña con el aceite.",
                        "Sirve con el pan pita."
            ],
        "Molletes ligeros": [
                        "Abre el bolillo y unta los frijoles.",
                        "Agrega el queso y hornea hasta que se derrita.",
                        "Añade el pico de gallo.",
                        "Acompaña con la ensalada."
            ],
        "Wrap de pavo y verduras": [
                        "Unta el hummus sobre la tortilla.",
                        "Coloca el pavo, queso y verduras.",
                        "Enrolla firmemente.",
                        "Corta por la mitad y sirve."
            ]};

    let platillosActuales = [];
    let metaAguaMl = 0;
    let registrosAgua = [];
    let breathingTimer = null;
    let breathingRunning = false;
    let breathingElapsed = 0;
    let breathingPhaseIndex = 0;
    let breathingPhaseElapsed = 0;
    const breathingRoutines = {
        diafragmatica: {
            title: "Respiración diafragmática",
            subtitle: "5 minutos · calma corporal",
            badge: "5 min",
            totalSeconds: 300,
            advice: "Si te distraes, vuelve suavemente a tu respiración.",
            phases: [
                { label: "Inhala", seconds: 4, className: "inhalar", icon: "🌬️", help: "Procura que se eleve el abdomen más que el pecho." },
                { label: "Exhala", seconds: 6, className: "exhalar", icon: "😮‍💨", help: "Suelta el aire lentamente por la boca." }
            ],
            steps: [
                "Siéntate o recuéstate cómodamente.",
                "Coloca una mano sobre el pecho y otra sobre el abdomen.",
                "Inhala por la nariz durante 4 segundos.",
                "Exhala lentamente por la boca durante 6 segundos.",
                "Repite durante 5 minutos."
            ]
        },
        cuadrada: {
            title: "Respiración 4-4-4-4",
            subtitle: "3 a 5 minutos · respiración cuadrada",
            badge: "3–5 min",
            totalSeconds: 300,
            advice: "Mantén el ritmo sin forzar el aire.",
            phases: [
                { label: "Inhala", seconds: 4, className: "inhalar", icon: "🌬️", help: "Inhala por la nariz." },
                { label: "Mantén", seconds: 4, className: "mantener", icon: "🫧", help: "Sostén el aire suavemente." },
                { label: "Exhala", seconds: 4, className: "exhalar", icon: "😮‍💨", help: "Exhala por la boca." },
                { label: "Pausa", seconds: 4, className: "pausa", icon: "☾", help: "Mantente sin aire antes de repetir." }
            ],
            steps: [
                "Inhala por la nariz durante 4 segundos.",
                "Mantén el aire durante 4 segundos.",
                "Exhala por la boca durante 4 segundos.",
                "Mantente sin aire durante 4 segundos.",
                "Repite de 5 a 10 veces."
            ]
        },
        cuatroSeis: {
            title: "Respiración 4-6",
            subtitle: "5 minutos · exhalación lenta",
            badge: "5 min",
            totalSeconds: 300,
            advice: "Mantén un ritmo suave y constante.",
            phases: [
                { label: "Inhala", seconds: 4, className: "inhalar", icon: "🌬️", help: "Inhala por la nariz." },
                { label: "Exhala", seconds: 6, className: "exhalar", icon: "😮‍💨", help: "Exhala lentamente por la boca." }
            ],
            steps: [
                "Inhala por la nariz durante 4 segundos.",
                "Exhala lentamente por la boca durante 6 segundos.",
                "Mantén un ritmo suave y constante.",
                "Repite durante 5 minutos."
            ]
        },
        cuatroSieteOcho: {
            title: "Respiración 4-7-8",
            subtitle: "2 a 4 minutos · relajación profunda",
            badge: "2–4 min",
            totalSeconds: 240,
            advice: "Empieza con pocas repeticiones y aumenta gradualmente.",
            phases: [
                { label: "Inhala", seconds: 4, className: "inhalar", icon: "🌬️", help: "Inhala por la nariz." },
                { label: "Mantén", seconds: 7, className: "mantener", icon: "🫧", help: "Mantén el aire sin tensión." },
                { label: "Exhala", seconds: 8, className: "exhalar", icon: "😮‍💨", help: "Exhala lenta y completamente." }
            ],
            steps: [
                "Inhala por la nariz durante 4 segundos.",
                "Mantén el aire durante 7 segundos.",
                "Exhala lentamente por la boca durante 8 segundos.",
                "Repite 4 veces al principio y aumenta gradualmente."
            ]
        },
        rutinaDiez: {
            title: "Rutina de 10 minutos",
            subtitle: "10 minutos · sesión completa",
            badge: "10 min",
            totalSeconds: 600,
            advice: "Combina respiraciones para cerrar con calma.",
            phases: [
                { label: "Diafragmática", seconds: 120, className: "inhalar", icon: "✿", help: "Respira desde el abdomen." },
                { label: "Respiración 4-6", seconds: 180, className: "exhalar", icon: "☾", help: "Exhala más lento de lo que inhalas." },
                { label: "Respiración cuadrada", seconds: 180, className: "mantener", icon: "◎", help: "Sigue el ritmo 4-4-4-4." },
                { label: "Respiración libre", seconds: 120, className: "pausa", icon: "♧", help: "Respira lento y profundo." }
            ],
            steps: [
                "2 minutos de respiración diafragmática.",
                "3 minutos de respiración 4-6.",
                "3 minutos de respiración cuadrada 4-4-4-4.",
                "2 minutos de respiración libre, lenta y profunda."
            ]
        }
    };
    let activeBreathingRoutineKey = "diafragmatica";

    const moodRecommendations = {
        diafragmatica: "Te recomiendo respiración diafragmática para bajar el ritmo y reconectar con tu cuerpo.",
        cuadrada: "Te recomiendo respiración 4-4-4-4 para ordenar tu respiración y recuperar control.",
        cuatroSeis: "Te recomiendo respiración 4-6 para soltar tensión con una exhalación más lenta.",
        cuatroSieteOcho: "Te recomiendo respiración 4-7-8 para relajar el cuerpo antes de descansar.",
        rutinaDiez: "Te recomiendo la rutina de 10 minutos para una sesión completa de relajación."
    };
    let breathingPhases = breathingRoutines[activeBreathingRoutineKey].phases;
    let breathingTotalSeconds = breathingRoutines[activeBreathingRoutineKey].totalSeconds;

    function leerMetaGuardada() {
        const posiblesClaves = [
            `metaCalorica_${idMenuAlumno}`,
            "metaCalorica_planificador",
            "metaDiariaKcal"
        ];

        for (const clave of posiblesClaves) {
            const valor = parseInt(localStorage.getItem(clave) || "", 10);
            if (valor && valor >= 800 && valor <= 6000) {
                return valor;
            }
        }

        return objetivoBase;
    }

    function guardarMetaLocal(valor) {
        localStorage.setItem(`metaCalorica_${idMenuAlumno}`, String(valor));
        localStorage.setItem("metaCalorica_planificador", String(valor));
        localStorage.setItem("metaDiariaKcal", String(valor));
    }

    function sincronizarMetaVisual() {
        if (inputMetaDiaria) inputMetaDiaria.value = objetivoCalorico;
        if (heroMetaObjetivo) heroMetaObjetivo.textContent = objetivoCalorico;
        if (statMetaKcal) statMetaKcal.textContent = `de ${objetivoCalorico} kcal`;
    }

    const inputHoraAgua = document.getElementById("inputAguaHora");

    if (inputHoraAgua && !inputHoraAgua.value) {
        inputHoraAgua.value = obtenerHoraActualInput();
    }

    sincronizarMetaVisual();
    cargarMenuDia();
    cargarPlatillos();
    cargarRegistroAguaLocal();

    if (btnGuardarMetaDiaria) {
        btnGuardarMetaDiaria.addEventListener("click", guardarMetaDiaria);
    }

    if (inputMetaDiaria) {
        inputMetaDiaria.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                guardarMetaDiaria();
            }
        });
    }

    const exerciseRoutines = {
        sedentaria: {
            title: "Rutina suave para personas sedentarias",
            subtitle: "10 minutos · bajo impacto",
            badge: "10 min",
            duration: 600,
            calories: 45,
            intensity: "Suave",
            note: "Ideal para iniciar",
            icon: "🚶",
            recommendation: "Empieza con una rutina suave y repítela una vez al día.",
            steps: [
                { text: "Camina por la casa.", seconds: 120, icon: "🚶" },
                { text: "Haz 10 sentadillas asistidas sosteniéndote de una silla.", seconds: 90, icon: "🪑" },
                { text: "Haz 10 elevaciones de piernas por lado.", seconds: 90, icon: "🦵" },
                { text: "Haz 10 flexiones de pared.", seconds: 90, icon: "🧱" },
                { text: "Realiza respiración profunda.", seconds: 60, icon: "🌬️" },
                { text: "Repite una vez el circuito.", seconds: 150, icon: "↻" }
            ]
        },
        despertar: {
            title: "Rutina para activar el cuerpo al despertar",
            subtitle: "5 minutos · activación suave",
            badge: "5 min",
            duration: 300,
            calories: 28,
            intensity: "Suave",
            note: "Perfecta por la mañana",
            icon: "☀️",
            recommendation: "Activa tu cuerpo al despertar para iniciar con más energía.",
            steps: [
                { text: "Marcha en el lugar.", seconds: 60, icon: "🚶" },
                { text: "Haz 10 círculos de brazos hacia adelante.", seconds: 35, icon: "🌀" },
                { text: "Haz 10 círculos de brazos hacia atrás.", seconds: 35, icon: "🌀" },
                { text: "Haz 10 sentadillas suaves.", seconds: 55, icon: "🦵" },
                { text: "Haz 10 elevaciones de talones.", seconds: 45, icon: "⬆️" },
                { text: "Estira brazos y piernas.", seconds: 70, icon: "🤸" }
            ]
        },
        calorias: {
            title: "Rutina rápida para quemar calorías",
            subtitle: "10 minutos · circuito activo",
            badge: "10 min",
            duration: 600,
            calories: 85,
            intensity: "Media",
            note: "Mayor movimiento",
            icon: "🔥",
            recommendation: "Usa esta rutina cuando quieras una activación más intensa.",
            steps: [
                { text: "Jumping jacks o pasos laterales de bajo impacto.", seconds: 30, icon: "⭐" },
                { text: "Sentadillas.", seconds: 30, icon: "🦵" },
                { text: "Rodillas al pecho.", seconds: 30, icon: "⬆️" },
                { text: "Plancha.", seconds: 30, icon: "▰" },
                { text: "Desplantes alternos.", seconds: 30, icon: "↔️" },
                { text: "Descanso breve y repite el circuito.", seconds: 350, icon: "↻" }
            ]
        }
    };

    let activeExerciseRoutineKey = "sedentaria";
    let exerciseTimer = null;
    let exerciseRunning = false;
    let exerciseElapsed = 0;
    let exerciseStepIndex = 0;
    let exerciseStepElapsed = 0;

    function obtenerClaveEjercicio(sufijo) {
        return `exercise_${sufijo}_${idMenuAlumno || "general"}`;
    }

    function obtenerRutinaEjercicioActiva() {
        return exerciseRoutines[activeExerciseRoutineKey] || exerciseRoutines.sedentaria;
    }

    function formatearTiempoEjercicio(segundos) {
        const minutos = Math.floor(segundos / 60);
        const resto = segundos % 60;

        return `${String(minutos).padStart(2, "0")}:${String(resto).padStart(2, "0")}`;
    }

    function seleccionarRutinaEjercicio(clave, iniciar = false) {
        if (!exerciseRoutines[clave]) {
            return;
        }

        if (exerciseTimer) {
            clearInterval(exerciseTimer);
            exerciseTimer = null;
        }

        activeExerciseRoutineKey = clave;
        exerciseRunning = false;
        exerciseElapsed = 0;
        exerciseStepIndex = 0;
        exerciseStepElapsed = 0;

        document.querySelectorAll(".exercise-routine-card").forEach(card => {
            card.classList.toggle("activo", card.dataset.routine === clave);
        });

        renderRutinaEjercicio();
        actualizarEjercicioSesion(true);

        if (iniciar) {
            iniciarEjercicioSesion();
        }
    }

    function renderRutinaEjercicio() {
        const rutina = obtenerRutinaEjercicioActiva();
        const title = document.getElementById("exercisePlayerTitle");
        const subtitle = document.getElementById("exercisePlayerSubtitle");
        const badge = document.getElementById("exercisePlayerBadge");
        const total = document.getElementById("exerciseTotalText");
        const list = document.getElementById("exerciseStepsList");

        if (title) title.textContent = rutina.title;
        if (subtitle) subtitle.textContent = rutina.subtitle;
        if (badge) badge.textContent = rutina.badge;
        if (total) total.textContent = formatearTiempoEjercicio(rutina.duration);

        if (list) {
            list.innerHTML = rutina.steps.map((step, index) => `
                <li class="${index === 0 ? "activo" : ""}">
                    <span>${index + 1}</span>
                    <p>${escapeHTML(step.text)}</p>
                    <small>${Math.round(step.seconds / 60) || 1} min</small>
                </li>
            `).join("");
        }
    }

    function iniciarEjercicioSesion() {
        const rutina = obtenerRutinaEjercicioActiva();

        exerciseRunning = true;
        actualizarEjercicioSesion();

        if (exerciseTimer) {
            clearInterval(exerciseTimer);
        }

        exerciseTimer = setInterval(function () {
            const step = rutina.steps[exerciseStepIndex];

            exerciseElapsed += 1;
            exerciseStepElapsed += 1;

            if (step && exerciseStepElapsed >= step.seconds) {
                exerciseStepIndex = Math.min(exerciseStepIndex + 1, rutina.steps.length - 1);
                exerciseStepElapsed = 0;
            }

            if (exerciseElapsed >= rutina.duration) {
                finalizarEjercicioSesion();
                return;
            }

            actualizarEjercicioSesion();
        }, 1000);
    }

    function pausarEjercicioSesion() {
        exerciseRunning = false;

        if (exerciseTimer) {
            clearInterval(exerciseTimer);
            exerciseTimer = null;
        }

        actualizarEjercicioSesion();
    }

    function alternarEjercicioSesion() {
        if (exerciseRunning) {
            pausarEjercicioSesion();
            return;
        }

        if (exerciseElapsed >= obtenerRutinaEjercicioActiva().duration) {
            reiniciarEjercicioSesion();
        }

        iniciarEjercicioSesion();
    }

    function reiniciarEjercicioSesion() {
        exerciseRunning = false;
        exerciseElapsed = 0;
        exerciseStepIndex = 0;
        exerciseStepElapsed = 0;

        if (exerciseTimer) {
            clearInterval(exerciseTimer);
            exerciseTimer = null;
        }

        actualizarEjercicioSesion(true);
    }

    function finalizarEjercicioSesion() {
        const rutina = obtenerRutinaEjercicioActiva();
        const minutos = Math.round(rutina.duration / 60);
        const calorias = rutina.calories;
        const keyMin = obtenerClaveEjercicio("minutes");
        const keyCal = obtenerClaveEjercicio("calories");
        localStorage.setItem(keyMin, String(parseInt(localStorage.getItem(keyMin) || "0", 10) + minutos));
        localStorage.setItem(keyCal, String(parseInt(localStorage.getItem(keyCal) || "0", 10) + calorias));

        exerciseRunning = false;
        exerciseElapsed = rutina.duration;

        if (exerciseTimer) {
            clearInterval(exerciseTimer);
            exerciseTimer = null;
        }

        actualizarEjercicioSesion();
        renderResumenEjercicio();
        mostrarToast("Rutina registrada.", "ok");
    }

    function actualizarEjercicioSesion(reset = false) {
        const rutina = obtenerRutinaEjercicioActiva();
        const step = rutina.steps[exerciseStepIndex] || rutina.steps[0];
        const currentIcon = document.getElementById("exerciseCurrentIcon");
        const currentStep = document.getElementById("exerciseCurrentStep");
        const currentTime = document.getElementById("exerciseCurrentTime");
        const elapsed = document.getElementById("exerciseElapsedText");
        const progress = document.getElementById("exerciseSessionProgress");
        const btnPlay = document.getElementById("btnExercisePlay");
        const items = document.querySelectorAll("#exerciseStepsList li");

        if (reset) {
            if (currentIcon) currentIcon.textContent = rutina.icon;
            if (currentStep) currentStep.textContent = "Presiona comenzar";
            if (currentTime) currentTime.textContent = "00:00";
            if (elapsed) elapsed.textContent = "00:00";
            if (progress) progress.style.width = "0%";
            if (btnPlay) btnPlay.textContent = "Comenzar";
            items.forEach((item, index) => item.classList.toggle("activo", index === 0));
            return;
        }

        const restante = Math.max((step?.seconds || 0) - exerciseStepElapsed, 0);
        const porcentaje = Math.min((exerciseElapsed / rutina.duration) * 100, 100);

        if (currentIcon) currentIcon.textContent = step?.icon || rutina.icon;
        if (currentStep) currentStep.textContent = step?.text || rutina.title;
        if (currentTime) currentTime.textContent = formatearTiempoEjercicio(restante);
        if (elapsed) elapsed.textContent = formatearTiempoEjercicio(exerciseElapsed);
        if (progress) progress.style.width = `${porcentaje}%`;
        if (btnPlay) btnPlay.textContent = exerciseRunning ? "Pausar" : "Continuar";

        items.forEach((item, index) => {
            item.classList.toggle("activo", index === exerciseStepIndex);
            item.classList.toggle("completo", index < exerciseStepIndex);
        });
    }

    function renderResumenEjercicio() {
        const minutos = parseInt(localStorage.getItem(obtenerClaveEjercicio("minutes")) || "0", 10);
        const calorias = parseInt(localStorage.getItem(obtenerClaveEjercicio("calories")) || "0", 10);
        const meta = 200;
        const porcentaje = Math.min((minutos / meta) * 100, 100);
        const statMinutes = document.getElementById("exerciseStatMinutes");
        const statCalories = document.getElementById("exerciseStatCalories");
        const statIntensity = document.getElementById("exerciseStatIntensity");
        const statNote = document.getElementById("exerciseStatIntensityNote");
        const minutesDone = document.getElementById("exerciseMinutesDone");
        const progressPercent = document.getElementById("exerciseProgressPercent");
        const ring = document.getElementById("exerciseRing");
        const statusTitle = document.getElementById("exerciseStatusTitle");
        const statusText = document.getElementById("exerciseStatusText");
        const rutina = obtenerRutinaEjercicioActiva();

        if (statMinutes) statMinutes.textContent = `${minutos} min`;
        if (statCalories) statCalories.textContent = `${calorias} kcal`;
        if (minutesDone) minutesDone.textContent = minutos;
        if (progressPercent) progressPercent.textContent = `${Math.round(porcentaje)}%`;
        if (ring) ring.style.background = `conic-gradient(#39aa68 ${porcentaje}%, #e8ecee ${porcentaje}%)`;
        if (statIntensity) statIntensity.textContent = rutina.intensity;
        if (statNote) statNote.textContent = rutina.note;

        if (statusTitle && statusText) {
            if (minutos <= 0) {
                statusTitle.textContent = "Empieza hoy";
                statusText.textContent = "Elige una rutina para sumar minutos.";
            } else if (porcentaje < 50) {
                statusTitle.textContent = "Buen inicio";
                statusText.textContent = "Cada sesión cuenta para tu progreso.";
            } else if (porcentaje < 100) {
                statusTitle.textContent = "¡Vas muy bien!";
                statusText.textContent = `${Math.max(meta - minutos, 0)} min para alcanzar tu meta.`;
            } else {
                statusTitle.textContent = "Meta cumplida";
                statusText.textContent = "Excelente constancia esta semana.";
            }
        }
    }

    function copiarTextoContacto(texto) {
        if (!texto) {
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(texto)
                .then(() => mostrarToast("Dato copiado.", "ok"))
                .catch(() => mostrarToast("No se pudo copiar automáticamente.", "error"));
            return;
        }

        const temp = document.createElement("textarea");
        temp.value = texto;
        temp.setAttribute("readonly", "");
        temp.style.position = "fixed";
        temp.style.opacity = "0";
        document.body.appendChild(temp);
        temp.select();

        try {
            document.execCommand("copy");
            mostrarToast("Dato copiado.", "ok");
        } catch (error) {
            mostrarToast("No se pudo copiar automáticamente.", "error");
        }

        document.body.removeChild(temp);
    }

    document.addEventListener("click", function (event) {
        const btnTab = event.target.closest(".btn-tab");
        const btnDia = event.target.closest(".btn-dia");
        const btnTiempo = event.target.closest(".btn-tiempo");
        const btnSeleccionar = event.target.closest(".btn-seleccionar");
        const btnCalcularAgua = event.target.closest("#btnCalcularAgua");
        const btnActualizarSemana = event.target.closest("#btnActualizarSemana");
        const btnImprimirMenu = event.target.closest("#btnImprimirMenu");
        const btnAddAgua = event.target.closest(".btn-add-agua");
        const btnAgregarAguaManual = event.target.closest("#btnAgregarAguaManual");
        const btnEliminarAgua = event.target.closest(".btn-eliminar-agua");
        const btnReiniciarAgua = event.target.closest("#btnReiniciarAgua");
        const btnBreathingPlay = event.target.closest("#btnBreathingPlay");
        const btnBreathingReset = event.target.closest("#btnBreathingReset");
        const btnRoutinePlay = event.target.closest(".relax-routine-card em");
        const btnRoutineCard = event.target.closest(".relax-routine-card");
        const btnMoodChip = event.target.closest(".mood-chips button");
        const btnBreathingFinishClick = event.target.closest("#btnBreathingFinish");
        const btnGuardarMetaRelaxClick = event.target.closest("#btnGuardarMetaRelajacion");
        const btnExerciseCard = event.target.closest(".exercise-routine-card");
        const btnExerciseStart = event.target.closest(".btn-exercise-start");
        const btnExercisePlay = event.target.closest("#btnExercisePlay");
        const btnExerciseReset = event.target.closest("#btnExerciseReset");
        const btnExerciseFinish = event.target.closest("#btnExerciseFinish");
        const btnExerciseStartMeta = event.target.closest("#btnExerciseStartMeta");
        const btnCopyContact = event.target.closest("[data-copy-contact]");

        if (btnTab) {
            cambiarPanel(btnTab);
        }

        if (btnDia) {
            document.querySelectorAll(".btn-dia").forEach(btn => btn.classList.remove("activo"));
            btnDia.classList.add("activo");

            idDiaActivo = btnDia.dataset.idDia;

            cargarMenuDia();
            cargarPlatillos();
        }

        if (btnTiempo) {
            document.querySelectorAll(".btn-tiempo").forEach(btn => btn.classList.remove("activo"));
            btnTiempo.classList.add("activo");

            idTiempoActivo = btnTiempo.dataset.idTiempo;

            cargarPlatillos();
        }

        if (btnSeleccionar) {
            const idPlatillo = btnSeleccionar.dataset.idPlatillo;
            guardarPlatillo(idPlatillo);
        }

        if (btnCalcularAgua) {
            calcularHidratacion();
        }

        if (btnActualizarSemana) {
            cargarMenuSemana();
        }

        if (btnImprimirMenu) {
            imprimirMenuSemanal();
        }

        if (btnAddAgua) {
            const inputHora = document.getElementById("inputAguaHora");
            const horaSeleccionada = inputHora && inputHora.value ? inputHora.value : obtenerHoraActualInput();

            agregarAgua(parseInt(btnAddAgua.dataset.ml || "0", 10), horaSeleccionada);
        }

        if (btnAgregarAguaManual) {
            const input = document.getElementById("inputAguaManual");
            const inputHora = document.getElementById("inputAguaHora");
            const cantidad = parseInt(input?.value || "0", 10);
            const horaSeleccionada = inputHora && inputHora.value ? inputHora.value : obtenerHoraActualInput();

            if (cantidad > 0) {
                agregarAgua(cantidad, horaSeleccionada);
                input.value = "";
            } else {
                mostrarToast("Ingresa una cantidad válida.", "error");
            }
        }

        if (btnEliminarAgua) {
            eliminarRegistroAgua(parseInt(btnEliminarAgua.dataset.id || "0", 10));
        }

        if (btnReiniciarAgua) {
            registrosAgua = [];
            guardarRegistroAguaLocal();
            actualizarRegistroAgua();
            mostrarToast("Registro de agua reiniciado.", "ok");
        }

        if (btnBreathingPlay) {
            alternarRespiracionGuiada();
        }

        if (btnBreathingReset) {
            reiniciarRespiracionGuiada();
        }

        if (btnBreathingFinishClick) {
            finalizarRespiracionGuiada();
        }

        if (btnGuardarMetaRelaxClick) {
            guardarMetaRelajacion();
        }

        if (btnRoutinePlay) {
            const card = btnRoutinePlay.closest(".relax-routine-card");

            if (card) {
                seleccionarRutinaRespiracion(card.dataset.routine, true);
            }

            return;
        }

        if (btnRoutineCard) {
            seleccionarRutinaRespiracion(btnRoutineCard.dataset.routine, false);
        }

        if (btnMoodChip) {
            recomendarRutinaPorEstado(btnMoodChip);
        }

        if (btnExerciseStart) {
            const card = btnExerciseStart.closest(".exercise-routine-card");

            if (card) {
                seleccionarRutinaEjercicio(card.dataset.routine, true);
            }

            return;
        }

        if (btnExerciseCard) {
            seleccionarRutinaEjercicio(btnExerciseCard.dataset.routine, false);
        }

        if (btnExercisePlay) {
            alternarEjercicioSesion();
        }

        if (btnExerciseReset) {
            reiniciarEjercicioSesion();
        }

        if (btnExerciseFinish) {
            finalizarEjercicioSesion();
        }

        if (btnExerciseStartMeta) {
            alternarEjercicioSesion();
        }

        if (btnCopyContact) {
            copiarTextoContacto(btnCopyContact.dataset.copyContact);
        }
    });

    if (buscarPlatillo) {
        buscarPlatillo.addEventListener("input", function () {
            document.querySelectorAll(".chip-busqueda").forEach(btn => {
                btn.classList.remove("activo");
            });

            renderPlatillos(this.value.trim().toLowerCase());
        });
    }

    if (limpiarBusqueda) {
        limpiarBusqueda.addEventListener("click", function () {
            document.querySelectorAll(".chip-busqueda").forEach(btn => {
                btn.classList.remove("activo");
            });

            if (buscarPlatillo) {
                buscarPlatillo.value = "";
                renderPlatillos("");
                buscarPlatillo.focus();
            }
        });
    }

    document.querySelectorAll(".chip-busqueda").forEach(chip => {
        chip.addEventListener("click", function () {
            const termino = this.dataset.search || "";

            document.querySelectorAll(".chip-busqueda").forEach(btn => {
                btn.classList.remove("activo");
            });

            this.classList.add("activo");

            if (buscarPlatillo) {
                buscarPlatillo.value = termino;
                renderPlatillos(termino.toLowerCase());
            }
        });
    });

    function cambiarPanel(boton) {
        const panelID = boton.dataset.panel;

        document.querySelectorAll(".btn-tab").forEach(btn => btn.classList.remove("activo"));
        boton.classList.add("activo");

        document.querySelectorAll(".panel-principal").forEach(panel => {
            panel.classList.remove("activo");
        });

        const panel = document.getElementById(panelID);

        if (panel) {
            panel.classList.add("activo");
        }

        if (panelID === "panelMiMenu") {
            cargarMenuSemana();
        }
    }

    function cargarPlatillos() {
        listaPlatillos.innerHTML = `
            <div class="loading-card">
                <span>🍽️</span>
                <p>Cargando platillos...</p>
            </div>
        `;

        fetch("obtener_platillos.php?id_tiempo=" + encodeURIComponent(idTiempoActivo))
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    listaPlatillos.innerHTML = `<p class="mensaje-error">${escapeHTML(data.mensaje)}</p>`;
                    return;
                }

                platillosActuales = data.platillos || [];

                if (buscarPlatillo) {
                    buscarPlatillo.value = "";
                }

                if (platillosActuales.length === 0) {
                    listaPlatillos.innerHTML = `
                        <div class="estado-vacio">
                            <h3>No hay platillos registrados</h3>
                            <p>Agrega platillos para este tiempo de comida.</p>
                        </div>
                    `;
                    return;
                }

                renderPlatillos("");
            })
            .catch(error => {
                console.error(error);
                listaPlatillos.innerHTML = "<p class='mensaje-error'>Error al cargar platillos.</p>";
            });
    }

    function renderPlatillos(busqueda) {
        let platillos = platillosActuales;

        if (busqueda) {
            platillos = platillosActuales.filter(platillo => {
                const base = `${platillo.nombre_platillo || ""} ${platillo.descripcion || ""}`.toLowerCase();
                const ingredientes = (platillo.ingredientes || [])
                    .map(ing => `${ing.nombre_ingrediente || ""} ${ing.nombre_grupo || ""} ${ing.cantidad || ""}`)
                    .join(" ")
                    .toLowerCase();

                return base.includes(busqueda) || ingredientes.includes(busqueda);
            });
        }

        actualizarEstadoBusqueda(platillos.length, busqueda);

        if (platillos.length === 0) {
            listaPlatillos.innerHTML = `
                <div class="estado-vacio">
                    <h3>No encontramos platillos</h3>
                    <p>Prueba con otro nombre o ingrediente.</p>
                </div>
            `;
            return;
        }

        let html = "";

        platillos.forEach(platillo => {
            html += crearTarjetaPlatillo(platillo);
        });

        listaPlatillos.innerHTML = html;
    }

    function crearTarjetaPlatillo(platillo) {
        let ingredientesHTML = "";

        if (platillo.ingredientes && platillo.ingredientes.length > 0) {
            platillo.ingredientes.forEach(ing => {
                const color = ing.color || "#95d5b2";
                const icono = ing.icono || "🍽️";
                const nombreGrupo = ing.nombre_grupo || "";

                ingredientesHTML += `
                    <span class="chip-ingrediente" style="--grupo-color: ${escapeHTML(color)};" title="${escapeHTML(nombreGrupo)}">
                        ${escapeHTML(icono)} ${escapeHTML(ing.nombre_ingrediente)}
                    </span>
                `;
            });
        } else {
            ingredientesHTML = `<span class="chip-ingrediente">🍽️ Sin ingredientes registrados</span>`;
        }

        const imagenHTML = platillo.imagen
            ? `
                <img
                    src="${escapeHTML(platillo.imagen)}"
                    alt="${escapeHTML(platillo.nombre_platillo)}"
                    class="img-platillo"
                    loading="lazy"
                    onerror="this.outerHTML='<div class=&quot;img-fallback sin-imagen&quot;><span class=&quot;texto-sin-imagen&quot;>Sin imagen</span></div>';"
                >
              `
            : `<div class="img-fallback sin-imagen"><span class="texto-sin-imagen">Sin imagen</span></div>`;

        const preparacionHTML = formatearPreparacion(obtenerPreparacionPlatillo(platillo));

        return `
            <article class="tarjeta-platillo">
                <div class="platillo-imagen">
                    ${imagenHTML}
                    <span class="kcal">${escapeHTML(platillo.calorias)} kcal</span>
                </div>

                <div class="platillo-body">
                    <div class="tarjeta-top">
                        <div>
                            <h3>${escapeHTML(platillo.nombre_platillo)}</h3>
                            <p>${escapeHTML(platillo.descripcion || "")}</p>
                        </div>
                    </div>

                    <div class="ingredientes">
                        ${ingredientesHTML}
                    </div>

                    <details class="preparacion">
                        <summary>Preparación</summary>
                        <div class="preparacion-contenido">
                            ${preparacionHTML}
                        </div>
                    </details>

                    <button
                        class="btn-seleccionar"
                        data-id-platillo="${escapeHTML(platillo.id_platillo)}"
                    >
                        Agregar al día
                    </button>
                </div>
            </article>
        `;
    }

    function normalizarNombrePlatillo(nombre) {
        return (nombre || "")
            .toString()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/[^\w\sñÑ]/g, "")
            .replace(/\s+/g, " ")
            .trim()
            .toLowerCase();
    }

    function obtenerPreparacionPlatillo(platillo) {
        const preparacionBD = (platillo.preparacion || "").trim();

        if (preparacionBD && preparacionBD.toLowerCase() !== "sin preparación." && preparacionBD.toLowerCase() !== "sin preparacion.") {
            return preparacionBD;
        }

        const nombreBase = normalizarNombrePlatillo(platillo.nombre_platillo);

        for (const [nombre, pasos] of Object.entries(preparacionesRespaldo)) {
            if (normalizarNombrePlatillo(nombre) === nombreBase) {
                return pasos.map((paso, index) => `${index + 1}. ${paso}`).join("\n");
            }
        }

        return "Sin preparación.";
    }

    function guardarPlatillo(idPlatillo) {
        fetch("guardar_platillo.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_menu_alumno: idMenuAlumno,
                id_dia: idDiaActivo,
                id_tiempo: idTiempoActivo,
                id_platillo: idPlatillo
            })
        })
        .then(response => response.text())
        .then(texto => {
            let data;

            try {
                data = JSON.parse(texto);
            } catch (error) {
                mostrarToast("El servidor no devolvió JSON válido.", "error");
                console.error("Respuesta no válida:", texto);
                return;
            }

            if (!data.success) {
                mostrarToast(data.mensaje, "error");
                return;
            }

            mostrarToast("Platillo agregado al día.", "ok");
            cargarMenuDia();

            const panelMiMenuActivo = document.getElementById("panelMiMenu");
            if (panelMiMenuActivo && panelMiMenuActivo.classList.contains("activo")) {
                cargarMenuSemana();
            }
        })
        .catch(error => {
            console.error("Error en fetch:", error);
            mostrarToast("Error al guardar el platillo.", "error");
        });
    }

    function cargarMenuDia() {
        menuDia.innerHTML = `
            <div class="loading-card mini">
                <span>📋</span>
                <p>Cargando menú del día...</p>
            </div>
        `;

        const diaActivoBtn = document.querySelector(`.btn-dia[data-id-dia="${idDiaActivo}"]`);
        const nombreDia = diaActivoBtn ? diaActivoBtn.dataset.nombreDia : "Día seleccionado";

        fetch(
            "obtener_menu_dia.php?id_menu_alumno=" + encodeURIComponent(idMenuAlumno) +
            "&id_dia=" + encodeURIComponent(idDiaActivo)
        )
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                menuDia.innerHTML = `<p class="mensaje-error">${escapeHTML(data.mensaje)}</p>`;
                return;
            }

            actualizarCalorias(data.total_calorias);

            if (data.menu.length === 0) {
                menuDia.innerHTML = `
                    <div class="empty-menu">
                        <span>🍱</span>
                        <p>Aún no has seleccionado platillos para este día.</p>
                    </div>
                `;
                return;
            }

            let html = "";

            data.menu.forEach(item => {
                const imagenMenu = item.imagen
                    ? `
                        <div class="item-menu-img">
                            <img
                                src="${escapeHTML(item.imagen)}"
                                alt="${escapeHTML(item.nombre_platillo)}"
                                loading="lazy"
                                onerror="this.parentElement.classList.add('sin-imagen'); this.parentElement.innerHTML='<span class=&quot;texto-sin-imagen&quot;>Sin imagen</span>';"
                            >
                        </div>
                    `
                    : `
                        <div class="item-menu-img sin-imagen">
                            <span class="texto-sin-imagen">Sin imagen</span>
                        </div>
                    `;

                html += `
                    <div class="item-menu-dia item-menu-dia-visual">
                        ${imagenMenu}

                        <div class="item-menu-info">
                            <span>${escapeHTML(item.nombre_tiempo)}</span>
                            <strong>${escapeHTML(item.nombre_platillo)}</strong>
                            <small>${escapeHTML(item.calorias)} kcal</small>
                        </div>
                    </div>
                `;
            });

            menuDia.innerHTML = html;
        })
        .catch(error => {
            console.error(error);
            menuDia.innerHTML = "<p class='mensaje-error'>Error al cargar menú del día.</p>";
        });
    }

    function cargarMenuSemana() {
        if (!menuSemana) return;

        menuSemana.innerHTML = `
            <div class="loading-card">
                <span>📅</span>
                <p>Cargando semana...</p>
            </div>
        `;

        if (listaComprasMenu) {
            listaComprasMenu.querySelector(".lista-compras-body").innerHTML = `
                <p class="lista-compras-empty">Preparando lista de ingredientes...</p>
            `;
        }

        const botonesDias = Array.from(document.querySelectorAll(".btn-dia"));

        const promesas = botonesDias.map(btn => {
            const idDia = btn.dataset.idDia;
            const nombreDia = btn.dataset.nombreDia;

            return fetch(
                "obtener_menu_dia.php?id_menu_alumno=" + encodeURIComponent(idMenuAlumno) +
                "&id_dia=" + encodeURIComponent(idDia)
            )
            .then(response => response.json())
            .then(data => ({ idDia, nombreDia, data }));
        });

        Promise.all(promesas)
            .then(resultados => {
                const html = resultados.map(resultado => {
                    const data = resultado.data;
                    const menu = data.success && Array.isArray(data.menu) ? data.menu : [];
                    const totalDia = data.success ? data.total_calorias : 0;
                    const totalPlatillos = menu.length;
                    const tieneMenu = totalPlatillos > 0;

                    const platillosHTML = tieneMenu
                        ? menu.map(item => {
                            const nombre = escapeHTML(item.nombre_platillo);
                            const tiempo = escapeHTML(item.nombre_tiempo);
                            const calorias = escapeHTML(item.calorias);
                            const imagenHTML = item.imagen
                                ? `
                                    <div class="semana-platillo-media">
                                        <img
                                            src="${escapeHTML(item.imagen)}"
                                            alt="${nombre}"
                                            loading="lazy"
                                            onerror="this.parentElement.classList.add('sin-imagen'); this.parentElement.innerHTML='<span class=&quot;texto-sin-imagen&quot;>Sin imagen</span>';"
                                        >
                                    </div>
                                  `
                                : `
                                    <div class="semana-platillo-media sin-imagen">
                                        <span class="texto-sin-imagen">Sin imagen</span>
                                    </div>
                                  `;

                            return `
                                <article class="semana-platillo-card">
                                    ${imagenHTML}
                                    <div class="semana-platillo-copy">
                                        <h4>${nombre}</h4>
                                        <div class="semana-platillo-meta">
                                            <span>${tiempo}</span>
                                            <span>${calorias} kcal</span>
                                        </div>
                                    </div>
                                </article>
                            `;
                        }).join("")
                        : `
                            <div class="semana-dia-vacio">
                                <span>Sin selección</span>
                            </div>
                        `;

                    return `
                        <article class="semana-dia-columna ${tieneMenu ? 'con-menu' : 'sin-menu'}">
                            <div class="semana-dia-head">
                                <h3>${escapeHTML(resultado.nombreDia)}</h3>
                                <span>${escapeHTML(totalDia)} kcal</span>
                            </div>

                            <div class="semana-dia-body">
                                ${platillosHTML}
                            </div>
                        </article>
                    `;
                }).join("");

                menuSemana.innerHTML = html;
                renderListaComprasMenu(resultados);
            })
            .catch(error => {
                console.error(error);
                menuSemana.innerHTML = "<p class='mensaje-error'>Error al cargar la semana.</p>";
                if (listaComprasMenu) {
                    listaComprasMenu.querySelector(".lista-compras-body").innerHTML = "<p class='mensaje-error'>Error al generar la lista de compras.</p>";
                }
            });
    }

    function renderListaComprasMenu(resultados) {
        if (!listaComprasMenu) return;

        const contenedor = listaComprasMenu.querySelector(".lista-compras-body");

        if (!contenedor) return;

        const mapa = new Map();

        resultados.forEach(resultado => {
            const nombreDia = resultado.nombreDia || "";
            const data = resultado.data;
            const menu = data && data.success && Array.isArray(data.menu) ? data.menu : [];

            menu.forEach(platillo => {
                const nombrePlatillo = platillo.nombre_platillo || "Platillo";
                const ingredientes = Array.isArray(platillo.ingredientes) ? platillo.ingredientes : [];

                ingredientes.forEach(ingrediente => {
                    const nombre = (ingrediente.nombre_ingrediente || "").trim();

                    if (!nombre) return;

                    const grupo = (ingrediente.nombre_grupo || "Otros").trim();
                    const cantidad = (ingrediente.cantidad || "Cantidad no especificada").trim();
                    const icono = (ingrediente.icono || "•").trim();
                    const color = (ingrediente.color || "#ff625d").trim();
                    const clave = `${grupo.toLowerCase()}|${nombre.toLowerCase()}`;

                    if (!mapa.has(clave)) {
                        mapa.set(clave, {
                            nombre,
                            grupo,
                            icono,
                            color,
                            cantidades: new Set(),
                            platillos: new Set(),
                            dias: new Set()
                        });
                    }

                    const item = mapa.get(clave);
                    item.cantidades.add(cantidad);
                    item.platillos.add(nombrePlatillo);
                    item.dias.add(nombreDia);
                });
            });
        });

        const ingredientes = Array.from(mapa.values()).sort((a, b) => {
            const grupo = a.grupo.localeCompare(b.grupo, "es");
            return grupo !== 0 ? grupo : a.nombre.localeCompare(b.nombre, "es");
        });

        if (ingredientes.length === 0) {
            contenedor.innerHTML = `
                <p class="lista-compras-empty">Agrega platillos a tu menú semanal para generar la lista de ingredientes.</p>
            `;
            return;
        }

        const grupos = ingredientes.reduce((acc, ingrediente) => {
            if (!acc[ingrediente.grupo]) acc[ingrediente.grupo] = [];
            acc[ingrediente.grupo].push(ingrediente);
            return acc;
        }, {});

        const totalPlatillos = resultados.reduce((acc, resultado) => {
            const data = resultado.data;
            const menu = data && data.success && Array.isArray(data.menu) ? data.menu : [];
            return acc + menu.length;
        }, 0);

        const gruposHTML = Object.entries(grupos).map(([grupo, items]) => {
            const primer = items[0];

            const itemsHTML = items.map(item => {
                const cantidades = Array.from(item.cantidades).join(" / ");
                const platillos = Array.from(item.platillos).join(", ");
                const dias = Array.from(item.dias).join(", ");

                return `
                    <article class="compra-item" style="--grupo-color:${escapeHTML(item.color)}">
                        <span class="compra-icono">${escapeHTML(item.icono)}</span>
                        <div>
                            <h4>${escapeHTML(item.nombre)}</h4>
                            <p>${escapeHTML(cantidades)}</p>
                            <small>${escapeHTML(platillos)} · ${escapeHTML(dias)}</small>
                        </div>
                    </article>
                `;
            }).join("");

            return `
                <section class="compra-grupo">
                    <div class="compra-grupo-head" style="--grupo-color:${escapeHTML(primer.color)}">
                        <span>${escapeHTML(primer.icono)}</span>
                        <h4>${escapeHTML(grupo)}</h4>
                        <small>${items.length} ingredientes</small>
                    </div>
                    <div class="compra-grid">
                        ${itemsHTML}
                    </div>
                </section>
            `;
        }).join("");

        contenedor.innerHTML = `
            <div class="lista-compras-resumen">
                <span>${ingredientes.length}</span>
                <p>ingredientes detectados en ${totalPlatillos} platillos seleccionados.</p>
            </div>
            ${gruposHTML}
        `;
    }

    function imprimirMenuSemanal() {
        const panelMiMenu = document.getElementById("panelMiMenu");

        if (panelMiMenu && !panelMiMenu.classList.contains("activo")) {
            document.querySelectorAll(".btn-tab").forEach(btn => btn.classList.remove("activo"));
            const btnMiMenu = document.querySelector('.btn-tab[data-panel="panelMiMenu"]');
            if (btnMiMenu) btnMiMenu.classList.add("activo");

            document.querySelectorAll(".panel-principal").forEach(panel => {
                panel.classList.remove("activo");
            });

            panelMiMenu.classList.add("activo");
        }

        cargarMenuSemana();

        setTimeout(() => {
            window.print();
        }, 700);
    }

function actualizarCalorias(total) {
        caloriasActualesDia = parseInt(total || 0, 10);
        sincronizarMetaVisual();
        const objetivoSeguro = Math.max(objetivoCalorico || 2000, 1);
        const porcentajeReal = (total / objetivoSeguro) * 100;
        const porcentaje = Math.min(porcentajeReal, 100);

        if (statKcalElegidas) {
            statKcalElegidas.textContent = `${total} kcal`;
        }

        if (statMetaKcal) {
            statMetaKcal.textContent = `de ${objetivoSeguro} kcal`;
        }

        const heroKcalActual = document.getElementById("heroKcalActual");
        const heroMetaProgress = document.getElementById("heroMetaProgress");
        const heroPorcentajeKcal = document.getElementById("heroPorcentajeKcal");

        if (heroKcalActual) heroKcalActual.textContent = total;
        if (heroMetaObjetivo) heroMetaObjetivo.textContent = objetivoSeguro;
        if (heroMetaProgress) heroMetaProgress.style.width = porcentaje + "%";
        if (heroPorcentajeKcal) heroPorcentajeKcal.textContent = Math.round(porcentajeReal) + "%";

        actualizarMensajeMeta(porcentajeReal, total, objetivoSeguro);
    }

    function actualizarMensajeMeta(porcentaje, total, objetivo) {
        if (!mensajeMetaDiaria || !iconoMensajeMeta || !tituloMensajeMeta || !textoMensajeMeta) return;

        mensajeMetaDiaria.classList.remove("meta-baja", "meta-media", "meta-bien", "meta-exceso");

        if (total === 0) {
            iconoMensajeMeta.textContent = "○";
            tituloMensajeMeta.textContent = "Empieza tu día";
            textoMensajeMeta.textContent = "Agrega platillos para ver tu avance.";
            mensajeMetaDiaria.classList.add("meta-baja");
            return;
        }

        if (porcentaje < 50) {
            iconoMensajeMeta.textContent = "↗";
            tituloMensajeMeta.textContent = "Aún falta energía";
            textoMensajeMeta.textContent = `Llevas ${Math.round(porcentaje)}%. Agrega una opción balanceada.`;
            mensajeMetaDiaria.classList.add("meta-baja");
            return;
        }

        if (porcentaje < 85) {
            iconoMensajeMeta.textContent = "★";
            tituloMensajeMeta.textContent = "Vas avanzando";
            textoMensajeMeta.textContent = `Llevas ${Math.round(porcentaje)}%. Estás cerca de tu meta.`;
            mensajeMetaDiaria.classList.add("meta-media");
            return;
        }

        if (porcentaje <= 110) {
            iconoMensajeMeta.textContent = "✓";
            tituloMensajeMeta.textContent = "¡Vas muy bien!";
            textoMensajeMeta.textContent = "Tu selección está dentro de un buen rango.";
            mensajeMetaDiaria.classList.add("meta-bien");
            return;
        }

        iconoMensajeMeta.textContent = "!";
        tituloMensajeMeta.textContent = "Revisa tu selección";
        textoMensajeMeta.textContent = `Superaste tu meta por ${total - objetivo} kcal.`;
        mensajeMetaDiaria.classList.add("meta-exceso");
    }

    function guardarMetaDiaria() {
        if (!inputMetaDiaria) return;

        const nuevaMeta = parseInt(inputMetaDiaria.value, 10);

        if (!nuevaMeta || nuevaMeta < 800 || nuevaMeta > 6000) {
            mostrarToast("Ingresa una meta entre 800 y 6000 kcal.", "error");
            inputMetaDiaria.focus();
            return;
        }

        objetivoCalorico = nuevaMeta;
        guardarMetaLocal(objetivoCalorico);
        sincronizarMetaVisual();
        actualizarCalorias(caloriasActualesDia);
        mostrarToast("Meta diaria actualizada.", "ok");
    }

    function obtenerHoraActualInput() {
        const ahora = new Date();
        const horas = String(ahora.getHours()).padStart(2, "0");
        const minutos = String(ahora.getMinutes()).padStart(2, "0");

        return `${horas}:${minutos}`;
    }

    function obtenerValorSelectNumerico(id, mapa, fallback = 0) {
        const campo = document.getElementById(id);

        if (!campo) {
            return fallback;
        }

        const valor = campo.value;

        if (Object.prototype.hasOwnProperty.call(mapa, valor)) {
            return mapa[valor];
        }

        const numero = parseFloat(valor);

        return Number.isFinite(numero) ? numero : fallback;
    }

    function calcularHidratacion() {
        const pesoInput = document.getElementById("pesoAgua");
        const edadInput = document.getElementById("edadAgua");

        if (!pesoInput || !edadInput) {
            return;
        }

        const peso = parseFloat(pesoInput.value || "0");
        const edad = parseInt(edadInput.value || "0", 10);

        if (!peso || peso <= 0) {
            mostrarToast("Ingresa un peso válido.", "error");
            pesoInput.focus();
            return;
        }

        let factorMlKg = 35;

        if (edad > 0 && edad <= 17) {
            factorMlKg = 40;
        }

        if (edad >= 60) {
            factorMlKg = 30;
        }

        const extraActividad = obtenerValorSelectNumerico("actividadAgua", {
            baja: 0,
            moderada: 200,
            alta: 400
        });

        const campoEjercicio = document.getElementById("ejercicioAgua");
        const minutosEjercicio = campoEjercicio ? Math.max(0, parseInt(campoEjercicio.value || "0", 10)) : 0;

        const multiplicadorIntensidad = obtenerValorSelectNumerico("intensidadAgua", {
            baja: 4,
            moderada: 7,
            alta: 10
        }, 7);

        const extraEjercicio = minutosEjercicio * multiplicadorIntensidad;

        const extraClima = obtenerValorSelectNumerico("climaAgua", {
            templado: 0,
            caluroso: 300,
            humedo: 350
        });

        const extraSudor = obtenerValorSelectNumerico("sudoracionAgua", {
            baja: 0,
            moderada: 200,
            alta: 350
        });

        const totalMl = Math.max(800, Math.round((peso * factorMlKg) + extraActividad + extraEjercicio + extraClima + extraSudor));

        metaAguaMl = totalMl;
        guardarMetaAguaLocal();

        if (statAgua) {
            statAgua.textContent = `${(metaAguaMl / 1000).toFixed(1)} L`;
        }

        actualizarRegistroAgua();
        mostrarToast("Hidratación calculada.", "ok");
    }

    function obtenerClaveRegistroAgua() {
        const fecha = new Date().toISOString().slice(0, 10);
        return `registroAgua_${idMenuAlumno}_${fecha}`;
    }

    function obtenerClaveMetaAgua() {
        const fecha = new Date().toISOString().slice(0, 10);
        return `metaAgua_${idMenuAlumno}_${fecha}`;
    }

    function guardarMetaAguaLocal() {
        localStorage.setItem(obtenerClaveMetaAgua(), String(metaAguaMl || 0));
    }

    function guardarRegistroAguaLocal() {
        localStorage.setItem(obtenerClaveRegistroAgua(), JSON.stringify(registrosAgua));
    }

    function cargarRegistroAguaLocal() {
        const metaGuardada = parseInt(localStorage.getItem(obtenerClaveMetaAgua()) || "0", 10);
        const registrosGuardados = localStorage.getItem(obtenerClaveRegistroAgua());

        metaAguaMl = Number.isFinite(metaGuardada) ? metaGuardada : 0;

        try {
            registrosAgua = registrosGuardados ? JSON.parse(registrosGuardados) : [];
        } catch (error) {
            registrosAgua = [];
        }

        if (!Array.isArray(registrosAgua)) {
            registrosAgua = [];
        }

        actualizarRegistroAgua();
    }

    function agregarAgua(cantidadMl, horaManual = null) {
        if (!cantidadMl || cantidadMl <= 0) {
            mostrarToast("Ingresa una cantidad válida.", "error");
            return;
        }

        const horaRegistro = horaManual && /^\d{2}:\d{2}$/.test(horaManual)
            ? horaManual
            : obtenerHoraActualInput();

        registrosAgua.push({
            id: Date.now(),
            hora: horaRegistro,
            cantidad: cantidadMl
        });

        guardarRegistroAguaLocal();
        actualizarRegistroAgua();
        mostrarToast(`Agua registrada a las ${horaRegistro}.`, "ok");
    }

    function eliminarRegistroAgua(idRegistro) {
        registrosAgua = registrosAgua.filter(item => item.id !== idRegistro);
        guardarRegistroAguaLocal();
        actualizarRegistroAgua();
    }

    function obtenerTotalAguaBebida() {
        return registrosAgua.reduce((total, item) => total + Number(item.cantidad || 0), 0);
    }

    function actualizarRegistroAgua() {
        const totalBebido = obtenerTotalAguaBebida();
        const porcentajeReal = metaAguaMl > 0 ? (totalBebido / metaAguaMl) * 100 : 0;
        const porcentaje = Math.min(porcentajeReal, 100);
        const faltanteMl = Math.max(metaAguaMl - totalBebido, 0);

        const metaAguaLitros = document.getElementById("metaAguaLitros");
        const metaAguaTotal = document.getElementById("metaAguaTotal");
        const aguaConsumidaTexto = document.getElementById("aguaConsumidaTexto");
        const aguaFaltanteTexto = document.getElementById("aguaFaltanteTexto");
        const progresoAguaBarra = document.getElementById("progresoAguaBarra");
        const metaAguaMitad = document.getElementById("metaAguaMitad");
        const metaAguaFinal = document.getElementById("metaAguaFinal");
        const porcentajeAgua = document.getElementById("porcentajeAgua");
        const hidroRing = document.getElementById("hidroRing");
        const tablaAguaBody = document.getElementById("tablaAguaBody");
        const hidroEstadoTitulo = document.getElementById("hidroEstadoTitulo");
        const hidroEstadoTexto = document.getElementById("hidroEstadoTexto");
        const hidroStatusCard = document.querySelector("#panelHidratacion .hidro-status-card");
        const aguaManana = document.getElementById("aguaManana");
        const aguaTarde = document.getElementById("aguaTarde");
        const aguaNoche = document.getElementById("aguaNoche");

        const metaLitros = metaAguaMl > 0 ? metaAguaMl / 1000 : 0;
        const totalLitros = totalBebido / 1000;
        const faltanteLitros = faltanteMl / 1000;

        if (metaAguaLitros) {
            metaAguaLitros.textContent = metaAguaMl > 0 ? `${metaLitros.toFixed(2)} L` : "0.0 L";
        }

        if (metaAguaTotal) {
            metaAguaTotal.textContent = metaAguaMl > 0 ? `${metaLitros.toFixed(2)} L` : "0.0 L";
        }

        if (aguaConsumidaTexto) {
            aguaConsumidaTexto.textContent = totalBebido >= 1000 ? `${totalLitros.toFixed(2)} L` : `${totalBebido} ml`;
        }

        if (aguaFaltanteTexto) {
            aguaFaltanteTexto.textContent = metaAguaMl > 0 ? `${faltanteLitros.toFixed(2)} L` : "0 ml";
        }

        if (progresoAguaBarra) {
            progresoAguaBarra.style.width = `${porcentaje}%`;
        }

        if (metaAguaMitad) {
            metaAguaMitad.textContent = metaAguaMl > 0 ? `${(metaLitros / 2).toFixed(1)} L` : "0 L";
        }

        if (metaAguaFinal) {
            metaAguaFinal.textContent = metaAguaMl > 0 ? `${metaLitros.toFixed(1)} L` : "0 L";
        }

        if (porcentajeAgua) {
            porcentajeAgua.textContent = `${Math.round(porcentaje)}%`;
        }

        if (hidroRing) {
            hidroRing.style.background = `conic-gradient(#1267e8 ${porcentaje}%, #e8edf5 ${porcentaje}%)`;
        }

        if (aguaManana) {
            aguaManana.textContent = metaAguaMl > 0 ? `${Math.round(metaAguaMl * 0.35)} ml` : "0 ml";
        }

        if (aguaTarde) {
            aguaTarde.textContent = metaAguaMl > 0 ? `${Math.round(metaAguaMl * 0.45)} ml` : "0 ml";
        }

        if (aguaNoche) {
            aguaNoche.textContent = metaAguaMl > 0 ? `${Math.round(metaAguaMl * 0.20)} ml` : "0 ml";
        }

        if (statAgua) {
            statAgua.textContent = metaAguaMl > 0 ? `${metaLitros.toFixed(1)} L` : "Sin calcular";
        }

        if (hidroStatusCard) {
            hidroStatusCard.classList.remove("estado-rojo", "estado-naranja", "estado-verde");
        }

        if (hidroEstadoTitulo && hidroEstadoTexto) {
            if (metaAguaMl <= 0) {
                hidroEstadoTitulo.textContent = "Calcula tu meta";
                hidroEstadoTexto.textContent = "Ingresa tus datos para empezar.";
                hidroStatusCard?.classList.add("estado-rojo");
            } else if (porcentaje < 50) {
                hidroEstadoTitulo.textContent = "Ritmo bajo";
                hidroEstadoTexto.textContent = "Toma agua poco a poco durante el día.";
                hidroStatusCard?.classList.add("estado-rojo");
            } else if (porcentaje < 85) {
                hidroEstadoTitulo.textContent = "Vas a medio camino";
                hidroEstadoTexto.textContent = "Continúa con pequeños sorbos durante el día.";
                hidroStatusCard?.classList.add("estado-naranja");
            } else {
                hidroEstadoTitulo.textContent = "¡Todo bien!";
                hidroEstadoTexto.textContent = "Vas por buen camino. Sigue así, tu cuerpo te lo agradece.";
                hidroStatusCard?.classList.add("estado-verde");
            }
        }

        if (!tablaAguaBody) {
            return;
        }

        if (registrosAgua.length === 0) {
            tablaAguaBody.innerHTML = `
                <tr>
                    <td colspan="4">Aún no agregas agua.</td>
                </tr>
            `;
            return;
        }

        tablaAguaBody.innerHTML = registrosAgua.slice().reverse().map(item => `
            <tr>
                <td>${escapeHTML(item.hora || "Sin hora")}</td>
                <td><strong>+${escapeHTML(item.cantidad)} ml</strong></td>
                <td>Agua</td>
                <td>
                    <button type="button" class="btn-eliminar-agua" data-id="${escapeHTML(item.id)}">Quitar</button>
                </td>
            </tr>
        `).join("");
    }

    function actualizarEstadoBusqueda(total, busqueda) {
        if (contadorPlatillos) {
            contadorPlatillos.textContent = `${total} resultado${total === 1 ? "" : "s"}`;
        }
        if (estadoBusqueda) {
            estadoBusqueda.textContent = busqueda
                ? `"${busqueda}"`
                : "Nombre o ingrediente";
        }
    }

    function formatearPreparacion(texto) {
        const limpio = (texto || "").trim();

        if (!limpio) {
            return `<p>Sin preparación.</p>`;
        }

        const conSaltos = limpio.replace(/\r/g, "\n");
        let pasos = [];

        if (/\d+\./.test(conSaltos)) {
            pasos = conSaltos
                .split(/\s*\d+\.\s*/)
                .map(item => item.trim())
                .filter(Boolean);
        }

        if (pasos.length === 0) {
            pasos = conSaltos
                .split(/\n+/)
                .map(item => item.trim())
                .filter(Boolean);
        }

        if (pasos.length <= 1) {
            return `<p>${escapeHTML(limpio)}</p>`;
        }

        return `
            <ol class="lista-preparacion">
                ${pasos.map(paso => `<li>${escapeHTML(paso)}</li>`).join("")}
            </ol>
        `;
    }

    function mostrarToast(mensaje, tipo = "ok") {
        if (!toast) return;

        toast.textContent = mensaje;
        toast.className = "toast " + tipo;

        setTimeout(() => {
            toast.classList.add("oculto");
        }, 2600);
    }

    function escapeHTML(texto) {
        if (texto === null || texto === undefined) {
            return "";
        }

        return texto
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function actualizarInteractividadHidratacion() {
        const ids = [
            "pesoAgua",
            "sexoAgua",
            "edadAgua",
            "actividadAgua",
            "ejercicioAgua",
            "intensidadAgua",
            "climaAgua",
            "sudoracionAgua"
        ];

        const campos = ids
            .map(id => document.getElementById(id))
            .filter(Boolean);

        const boton = document.getElementById("btnCalcularAgua");

        if (!campos.length || !boton) {
            return;
        }

        const completados = campos.filter(campo => {
            if (campo.tagName === "SELECT") {
                return campo.value !== "";
            }

            return campo.value.trim() !== "";
        }).length;

        boton.classList.toggle("is-ready", completados >= 6);
    }

    function aplicarPresetHidratacion(btnPreset) {
        const actividad = document.getElementById("actividadAgua");
        const ejercicio = document.getElementById("ejercicioAgua");
        const intensidad = document.getElementById("intensidadAgua");
        const clima = document.getElementById("climaAgua");
        const sudor = document.getElementById("sudoracionAgua");

        if (actividad && btnPreset.dataset.actividad !== undefined) {
            actividad.value = btnPreset.dataset.actividad;
        }

        if (ejercicio && btnPreset.dataset.ejercicio !== undefined) {
            ejercicio.value = btnPreset.dataset.ejercicio;
        }

        if (intensidad && btnPreset.dataset.intensidad !== undefined) {
            intensidad.value = btnPreset.dataset.intensidad;
        }

        if (clima && btnPreset.dataset.clima !== undefined) {
            clima.value = btnPreset.dataset.clima;
        }

        if (sudor && btnPreset.dataset.sudor !== undefined) {
            sudor.value = btnPreset.dataset.sudor;
        }

        document.querySelectorAll(".hydration-preset").forEach(btn => btn.classList.remove("activo"));
        btnPreset.classList.add("activo");

        actualizarInteractividadHidratacion();
        mostrarToast("Datos rápidos aplicados.", "ok");
    }

    function obtenerRutinaRespiracionActiva() {
        return breathingRoutines[activeBreathingRoutineKey] || breathingRoutines.diafragmatica;
    }

    function formatearTiempo(segundos) {
        const minutos = Math.floor(segundos / 60);
        const resto = segundos % 60;

        return `${String(minutos).padStart(2, "0")}:${String(resto).padStart(2, "0")}`;
    }

    function seleccionarRutinaRespiracion(clave, iniciar = false) {
        if (!breathingRoutines[clave]) {
            return;
        }

        if (breathingTimer) {
            clearInterval(breathingTimer);
            breathingTimer = null;
        }

        activeBreathingRoutineKey = clave;
        breathingPhases = breathingRoutines[clave].phases;
        breathingTotalSeconds = breathingRoutines[clave].totalSeconds;
        breathingRunning = false;
        breathingElapsed = 0;
        breathingPhaseIndex = 0;
        breathingPhaseElapsed = 0;

        document.querySelectorAll(".relax-routine-card").forEach(card => {
            const activa = card.dataset.routine === clave;
            card.classList.toggle("activo", activa);
            if (!activa) {
                card.classList.remove("recomendada");
            }
        });

        renderRutinaRespiracion();
        actualizarRespiracionGuiada(true);

        if (iniciar) {
            iniciarRespiracionGuiada();
        }
    }

    function renderRutinaRespiracion() {
        const rutina = obtenerRutinaRespiracionActiva();
        const titulo = document.getElementById("breathingRoutineTitle");
        const subtitulo = document.getElementById("breathingRoutineSubtitle");
        const badge = document.getElementById("breathingRoutineBadge");
        const total = document.getElementById("breathingTotalText");
        const advice = document.getElementById("breathingAdviceText");
        const lista = document.getElementById("breathingPhaseList");

        if (titulo) titulo.textContent = rutina.title;
        if (subtitulo) subtitulo.textContent = rutina.subtitle;
        if (badge) badge.textContent = rutina.badge;
        if (total) total.textContent = formatearTiempo(rutina.totalSeconds);
        if (advice) advice.textContent = rutina.advice;

        if (lista) {
            lista.innerHTML = rutina.steps.map((paso, index) => `
                <p class="${index === 0 ? "activo" : ""}">
                    <span>${index + 1}</span>
                    ${escapeHTML(paso)}
                </p>
            `).join("");
        }
    }

    function alternarRespiracionGuiada() {
        if (breathingRunning) {
            pausarRespiracionGuiada();
            return;
        }

        if (breathingElapsed >= breathingTotalSeconds) {
            reiniciarRespiracionGuiada();
        }

        iniciarRespiracionGuiada();
    }

    function iniciarRespiracionGuiada() {
        breathingRunning = true;
        actualizarRespiracionGuiada();

        const boton = document.getElementById("btnBreathingPlay");
        if (boton) {
            boton.classList.add("is-playing");
        }

        if (breathingTimer) {
            clearInterval(breathingTimer);
        }

        breathingTimer = setInterval(function () {
            breathingElapsed += 1;
            breathingPhaseElapsed += 1;

            const faseActual = breathingPhases[breathingPhaseIndex];

            if (breathingPhaseElapsed >= faseActual.seconds) {
                breathingPhaseIndex = (breathingPhaseIndex + 1) % breathingPhases.length;
                breathingPhaseElapsed = 0;
            }

            if (breathingElapsed >= breathingTotalSeconds) {
                finalizarRespiracionGuiada();
                return;
            }

            actualizarRespiracionGuiada();
        }, 1000);
    }

    function pausarRespiracionGuiada() {
        breathingRunning = false;

        if (breathingTimer) {
            clearInterval(breathingTimer);
            breathingTimer = null;
        }

        const boton = document.getElementById("btnBreathingPlay");
        const instruccion = document.getElementById("breathingInstruction");
        const icono = document.getElementById("breathingIcon");

        if (boton) {
            boton.classList.remove("is-playing");
        }

        if (instruccion) {
            instruccion.textContent = "Pausa";
        }

        if (icono) {
            icono.textContent = "▶";
        }
    }

    function reiniciarRespiracionGuiada() {
        breathingRunning = false;
        breathingElapsed = 0;
        breathingPhaseIndex = 0;
        breathingPhaseElapsed = 0;

        if (breathingTimer) {
            clearInterval(breathingTimer);
            breathingTimer = null;
        }

        actualizarRespiracionGuiada(true);
    }

    function finalizarRespiracionGuiada() {
        breathingRunning = false;
        breathingElapsed = breathingTotalSeconds;

        if (breathingTimer) {
            clearInterval(breathingTimer);
            breathingTimer = null;
        }

        const boton = document.getElementById("btnBreathingPlay");
        const instruccion = document.getElementById("breathingInstruction");
        const contador = document.getElementById("breathingCounter");
        const visual = document.getElementById("breathingVisual");
        const icono = document.getElementById("breathingIcon");

        if (boton) {
            boton.classList.remove("is-playing");
        }

        if (instruccion) {
            instruccion.textContent = "Rutina completada";
        }

        if (contador) {
            contador.textContent = "Buen trabajo";
        }

        if (visual) {
            visual.classList.remove("inhalar", "mantener", "exhalar", "pausa");
            visual.classList.add("completo");
        }

        if (icono) {
            icono.textContent = "✓";
        }

        actualizarMinutosRelajacion();
        mostrarToast("Respiración completada.", "ok");
    }

    function actualizarRespiracionGuiada(reset = false) {
        const fase = breathingPhases[breathingPhaseIndex];
        const instruccion = document.getElementById("breathingInstruction");
        const contador = document.getElementById("breathingCounter");
        const progreso = document.getElementById("breathingProgress");
        const visual = document.getElementById("breathingVisual");
        const icono = document.getElementById("breathingIcon");
        const boton = document.getElementById("btnBreathingPlay");
        const elapsed = document.getElementById("breathingElapsedText");
        const phaseList = document.querySelectorAll("#breathingPhaseList p");

        if (!instruccion || !contador || !progreso || !visual || !icono) {
            return;
        }

        visual.classList.remove("inhalar", "mantener", "exhalar", "pausa", "completo");

        if (reset) {
            instruccion.textContent = "Presiona play";
            contador.textContent = obtenerRutinaRespiracionActiva().phases.map(f => f.seconds).join(" - ");
            progreso.style.width = "0%";
            icono.textContent = "▶";
            if (elapsed) elapsed.textContent = "00:00";

            if (boton) {
                boton.classList.remove("is-playing");
            }

            phaseList.forEach((item, index) => item.classList.toggle("activo", index === 0));
            return;
        }

        const restanteFase = Math.max(fase.seconds - breathingPhaseElapsed, 1);
        const avance = Math.min((breathingElapsed / breathingTotalSeconds) * 100, 100);

        visual.classList.add(fase.className);
        instruccion.textContent = fase.label;
        contador.textContent = `${restanteFase}s`;
        progreso.style.width = `${avance}%`;
        icono.textContent = breathingRunning ? "Ⅱ" : "▶";
        if (elapsed) elapsed.textContent = formatearTiempo(breathingElapsed);

        phaseList.forEach((item, index) => {
            item.classList.toggle("activo", index === breathingPhaseIndex);
        });
    }

    function recomendarRutinaPorEstado(btnMood) {
        if (!btnMood) {
            return;
        }

        const clave = btnMood.dataset.recommend || "diafragmatica";
        const texto = document.getElementById("relaxRecommendationText");

        document.querySelectorAll(".mood-chips button").forEach(btn => {
            btn.classList.remove("activo");
        });

        btnMood.classList.add("activo");

        seleccionarRutinaRespiracion(clave, false);

        document.querySelectorAll(".relax-routine-card").forEach(card => {
            card.classList.toggle("recomendada", card.dataset.routine === clave);
        });

        if (texto) {
            texto.textContent = moodRecommendations[clave] || "Te recomiendo iniciar con una respiración suave.";
        }
    }

    function obtenerClaveMetaRelajacion() {
        return `relaxDailyGoal_${idMenuAlumno || "general"}`;
    }

    function leerMetaRelajacion() {
        const valor = parseInt(localStorage.getItem(obtenerClaveMetaRelajacion()) || "10", 10);

        if (!valor || valor < 5 || valor > 120) {
            return 10;
        }

        return valor;
    }

    function guardarMetaRelajacion() {
        const input = document.getElementById("relaxGoalInput");

        if (!input) {
            return;
        }

        const nuevaMeta = parseInt(input.value || "0", 10);

        if (!nuevaMeta || nuevaMeta < 5 || nuevaMeta > 120) {
            mostrarToast("Ingresa una meta diaria entre 5 y 120 minutos.", "error");
            input.focus();
            return;
        }

        localStorage.setItem(obtenerClaveMetaRelajacion(), String(nuevaMeta));
        renderMinutosRelajacion();
        mostrarToast("Meta diaria de relajación actualizada.", "ok");
    }

    function actualizarMinutosRelajacion() {
        const key = `relaxMinutes_${idMenuAlumno || "general"}`;
        const rutina = obtenerRutinaRespiracionActiva();
        const minutos = Math.round(rutina.totalSeconds / 60);
        const meta = leerMetaRelajacion();
        const acumulado = Math.min(parseInt(localStorage.getItem(key) || "0", 10) + minutos, meta);

        localStorage.setItem(key, String(acumulado));
        renderMinutosRelajacion(acumulado);
    }

    function renderMinutosRelajacion(valor = null) {
        const key = `relaxMinutes_${idMenuAlumno || "general"}`;
        const minutos = valor === null ? parseInt(localStorage.getItem(key) || "0", 10) : valor;
        const total = leerMetaRelajacion();
        const porcentaje = Math.min((minutos / total) * 100, 100);
        const textoMinutos = document.getElementById("relaxWeeklyMinutes");
        const barra = document.getElementById("relaxWeeklyBar");
        const texto = document.getElementById("relaxWeeklyText");
        const target = document.getElementById("relaxWeeklyTarget");
        const inputMetaRelax = document.getElementById("relaxGoalInput");

        if (textoMinutos) textoMinutos.textContent = minutos;
        if (target) target.textContent = total;
        if (inputMetaRelax) inputMetaRelax.value = total;
        if (barra) barra.style.width = `${porcentaje}%`;
        if (texto) texto.textContent = `${Math.round(porcentaje)}% de tu meta diaria`;

    }

    document.querySelectorAll(".relax-routine-card").forEach(card => {
        card.addEventListener("click", function () {
            seleccionarRutinaRespiracion(card.dataset.routine, false);
        });
    });

    document.querySelectorAll(".relax-routine-card em").forEach(play => {
        play.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            const card = play.closest(".relax-routine-card");

            if (card) {
                seleccionarRutinaRespiracion(card.dataset.routine, true);
            }
        });
    });

    document.querySelectorAll(".mood-chips button").forEach(btn => {
        btn.addEventListener("click", function () {
            recomendarRutinaPorEstado(btn);
        });
    });

    const btnBreathingFinish = document.getElementById("btnBreathingFinish");

    if (btnBreathingFinish) {
        btnBreathingFinish.addEventListener("click", finalizarRespiracionGuiada);
    }

    const btnGuardarMetaRelajacion = document.getElementById("btnGuardarMetaRelajacion");
    const inputMetaRelajacion = document.getElementById("relaxGoalInput");

    if (btnGuardarMetaRelajacion) {
        btnGuardarMetaRelajacion.addEventListener("click", guardarMetaRelajacion);
    }

    if (inputMetaRelajacion) {
        inputMetaRelajacion.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                guardarMetaRelajacion();
            }
        });
    }

    renderRutinaRespiracion();
    renderMinutosRelajacion();
    actualizarRespiracionGuiada(true);

    document.querySelectorAll("#panelHidratacion input, #panelHidratacion select").forEach(campo => {
        campo.addEventListener("input", actualizarInteractividadHidratacion);
        campo.addEventListener("change", actualizarInteractividadHidratacion);
    });

    document.querySelectorAll(".hydration-preset").forEach(btn => {
        btn.addEventListener("click", function () {
            aplicarPresetHidratacion(btn);
        });
    });

    actualizarInteractividadHidratacion();

});
