// Sidebar collapse toggle (soporta menús anidados)
document.querySelectorAll(".nav-collapse-toggle").forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
        e.stopPropagation(); // Evitar que el click suba al padre

        const targetId = this.getAttribute("data-target");
        const submenu = document.getElementById(targetId);
        const icon = this.querySelector(".collapse-icon");

        // Solo cerrar otros menús del MISMO nivel (opcional, para mejor UX)
        // Pero NO cerrar los padres

        // Toggle del menú actual
        submenu.classList.toggle("show");
        this.classList.toggle("active");
        icon.classList.toggle("rotated");
    });
});

// Control del sidebar: colapsable en desktop, drawer en móvil
(function () {
    const mainSidebar = document.getElementById("mainSidebar");
    const sidebarToggleBtn = document.getElementById("sidebarToggleBtn");
    const sidebarCloseBtn = document.getElementById("sidebarCloseBtn");
    const sidebarBackdrop = document.getElementById("sidebarBackdrop");
    const desktopBreakpoint = 992;
    const STORAGE_KEY = "crm_sidebar_collapsed";

    function isDesktop() {
        return window.innerWidth >= desktopBreakpoint;
    }

    // --- Desktop: colapsar/expandir ---
    function applyCollapsedState(collapsed) {
        if (!mainSidebar) return;
        if (collapsed) {
            mainSidebar.classList.add("collapsed");
        } else {
            mainSidebar.classList.remove("collapsed");
        }
    }

    function toggleCollapsed() {
        if (!mainSidebar) return;
        mainSidebar.classList.toggle("collapsed");
    }

    // Al cargar, el sidebar SIEMPRE inicia expandido (se ignora la preferencia guardada)
    if (isDesktop()) {
        applyCollapsedState(false);
    }

    // --- Móvil: drawer off-canvas ---
    function openMobileSidebar() {
        if (mainSidebar) mainSidebar.classList.add("show");
        if (sidebarBackdrop) sidebarBackdrop.classList.add("show");
        document.body.classList.add("sidebar-open");
    }

    function closeMobileSidebar() {
        if (mainSidebar) mainSidebar.classList.remove("show");
        if (sidebarBackdrop) sidebarBackdrop.classList.remove("show");
        document.body.classList.remove("sidebar-open");
    }

    // --- Click en botón de toggle ---
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (isDesktop()) {
                toggleCollapsed();
            } else {
                openMobileSidebar();
            }
        });
    }

    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener("click", function (e) {
            e.preventDefault();
            closeMobileSidebar();
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener("click", function (e) {
            e.preventDefault();
            closeMobileSidebar();
        });
    }

    document.addEventListener("keydown", function (e) {
        if (
            e.key === "Escape" &&
            mainSidebar &&
            mainSidebar.classList.contains("show")
        ) {
            closeMobileSidebar();
        }
    });

    // Si el usuario cambia de tamaño de ventana, limpiar el estado móvil
    let resizeTimeout = null;
    window.addEventListener("resize", function () {
        if (resizeTimeout) clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            if (isDesktop()) {
                closeMobileSidebar();
                // El sidebar mantiene su estado actual al redimensionar
            } else {
                if (mainSidebar) mainSidebar.classList.remove("collapsed");
                closeMobileSidebar();
            }
        }, 150);
    });
})();

// Funcion Global para mostrar toasts de Bootstrap con animación y barra de progreso

window.mostrarToast = function (mensaje, tipo = "success") {
    // Intentar mostrar con Bootstrap si está disponible
    if (typeof bootstrap !== "undefined" && bootstrap.Toast) {
        let toastContainer = document.querySelector(".toast-container-center");
        if (!toastContainer) {
            toastContainer = document.createElement("div");
            toastContainer.className = "toast-container-center";
            document.body.appendChild(toastContainer);
        }

        const toastId = "toast-" + Date.now();
        const duration = 3000; // 3 segundos

        const bgClass =
            tipo === "success"
                ? "bg-success"
                : tipo === "warning"
                  ? "bg-warning"
                  : "bg-danger";
        const iconClass =
            tipo === "success"
                ? "bi-check-circle-fill"
                : tipo === "warning"
                  ? "bi-exclamation-triangle-fill"
                  : "bi-x-circle-fill";
        const textColor = tipo === "warning" ? "text-dark" : "text-white";
        const closeBtnClass =
            tipo === "warning" ? "btn-close" : "btn-close btn-close-white";

        const toastHtml = `
            <div id="${toastId}" class="toast fade" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="${duration}">
                <div class="toast-header ${bgClass} ${textColor}">
                    <i class="bi ${iconClass} me-2"></i>
                    <strong class="me-auto">CRM</strong>
                    <small>ahora</small>
                    <button type="button" class="${closeBtnClass}" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
                <div class="toast-body p-0">
                    <div class="p-3 pb-2">${mensaje}</div>
                    <div class="progress">
                        <div class="progress-bar ${bgClass}" role="progressbar" style="width: 100%; transition: width linear ${duration}ms;"></div>
                    </div>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML("beforeend", toastHtml);
        const toastElement = document.getElementById(toastId);
        const progressBar = toastElement.querySelector(".progress-bar");

        setTimeout(() => {
            if (progressBar) progressBar.style.width = "0%";
        }, 50);

        const toast = new bootstrap.Toast(toastElement, {
            animation: true,
            autohide: true,
            delay: duration,
        });
        toast.show();

        // Agregar clase de animación de salida al cerrar
        toastElement.addEventListener("hidden.bs.toast", function () {
            this.classList.add("removing");
            setTimeout(() => this.remove(), 400);
        });

        // Si el usuario hace clic en cerrar, también animar
        const closeBtn = toastElement.querySelector(".btn-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", function (e) {
                e.preventDefault();
                const toast = this.closest(".toast");
                toast.classList.add("removing");
                setTimeout(() => {
                    const bsToast = bootstrap.Toast.getInstance(toast);
                    if (bsToast) bsToast.hide();
                }, 200);
            });
        }
    } else {
        // Fallback: console log
        console.log(`[${tipo}] ${mensaje}`);
    }
};

// ============================================
// VALIDACIONES GLOBALES EN TIEMPO REAL
// ============================================

window.soloLetras = function (e) {
    // Ignorar completamente las teclas de sistema y modificadores
    const teclasIgnoradas = [
        8, 9, 16, 17, 18, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46, 112,
        113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145,
    ];

    if (teclasIgnoradas.includes(e.keyCode)) {
        return true;
    }

    // Obtener el carácter
    const char = e.key;

    // PERMITIR: letras (con tildes y ñ), espacios, punto (.) y asterisco (*)
    if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s*.]$/.test(char)) {
        return true;
    }

    e.preventDefault();
    if (window.mostrarToast) {
        window.mostrarToast(
            "Solo se permiten letras, *, . y espacios",
            "warning",
        );
    }
    return false;
};

window.soloNumeros = function (e) {
    const teclasIgnoradas = [
        8, 9, 16, 17, 18, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46, 112,
        113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145,
    ];

    if (teclasIgnoradas.includes(e.keyCode)) {
        return true;
    }

    // Obtener el carácter
    const char = e.key;

    // Permitir números, +, -, espacio, * (asterisco) y . (punto)
    if (/^[0-9+\-\s*.]$/.test(char)) {
        return true;
    }

    e.preventDefault();
    if (window.mostrarToast) {
        window.mostrarToast(
            "Solo se permiten números, +, -, *, . y espacios",
            "warning",
        );
    }
    return false;
};

// Convertir a mayúsculas mientras se escribe
window.aMayusculas = function (e) {
    const inicio = e.target.selectionStart;
    const fin = e.target.selectionEnd;

    e.target.value = e.target.value.toUpperCase();

    // Restaurar la posición del cursor
    e.target.setSelectionRange(inicio, fin);
};

/**
 * Muestra un spinner de carga dentro de un contenedor de tabla.
 * @param {string} selectorContenedor - Selector CSS del tbody donde se muestra el spinner
 * @param {string} mensaje - Texto opcional a mostrar
 * @param {number} colspan - Cantidad de columnas a cubrir
 */
window.mostrarSpinnerTabla = function (
    selectorContenedor,
    mensaje = "Buscando...",
    colspan = 10,
) {
    const contenedor = document.querySelector(selectorContenedor);
    if (!contenedor) return;

    contenedor.innerHTML = `
        <tr>
            <td colspan="${colspan}" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">${mensaje}</span>
                </div>
                <p class="mt-2 text-muted">${mensaje}</p>
            </td>
        </tr>
    `;
};

// Actualizar fecha y hora del sidebar en tiempo real
function actualizarHoraSidebar() {
    const ahora = new Date();

    // Formatear fecha: "DIA SEMANA, NUMERO DIA, MES Y AÑO"
    const opcionesFecha = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    };
    const fechaFormateada = ahora.toLocaleDateString("es-MX", opcionesFecha);
    // Capitalizar primera letra
    const fechaCapitalizada =
        fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);

    // Formatear hora: "HORA, MINUTOS, SEGUNDOS"
    // Comentado porque el div de la hora está oculto
    // const horaFormateada = ahora.toLocaleTimeString('es-MX', {
    //     hour: '2-digit',
    //     minute: '2-digit',
    //     second: '2-digit',
    //     hour12: false
    // });

    const fechaElement = document.getElementById("sidebarFecha");
    // const horaElement = document.getElementById('sidebarHora');

    if (fechaElement) fechaElement.textContent = fechaCapitalizada;
    //if (horaElement) horaElement.textContent = horaFormateada;
}

// Actualizar cada segundo
document.addEventListener("DOMContentLoaded", function () {
    actualizarHoraSidebar();
    setInterval(actualizarHoraSidebar, 1000);
});

// ============================================
// SELECTOR DE RESULTADOS POR PÁGINA - GLOBAL
// ============================================

// Valor actual de per_page (compartido entre vistas)
window.perPageActual = 15;

window.inicializarSelectorPerPage = function (
    selectId,
    callbackRefrescar = null,
) {
    const perPageSelect = document.getElementById(selectId);
    if (!perPageSelect) return;

    window.perPageActual = parseInt(perPageSelect.value, 10) || 15;

    // onchange reemplaza el handler anterior (no duplica)
    perPageSelect.onchange = function () {
        const perPage = parseInt(this.value, 10) || 15;
        window.perPageActual = perPage;

        const urlObj = new URL(window.location.href);
        urlObj.searchParams.set("per_page", perPage);
        urlObj.searchParams.delete("page");

        if (typeof callbackRefrescar === "function") {
            window.history.replaceState({}, "", urlObj);
            callbackRefrescar();
            return;
        }

        window.location.href = urlObj.toString();
    };
};
