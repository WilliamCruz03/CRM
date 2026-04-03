# Diagramas de Flujo - Sistema CRM

## MÓDULO CLIENTES

### 1. Crear Cliente

```mermaid
graph TD
    A[Usuario en Lista de Clientes] --> B{Clic en Botón Nuevo Cliente}
    B --> C[Abrir Modal Nuevo Cliente]
    C --> D[Usuario llena el formulario]
    D --> E{Clic en Guardar}
    E --> F[Enviar datos al servidor]
    F --> G{Validar permisos}
    G -- Sin permiso --> H[Mostrar error: No autorizado]
    G -- Con permiso --> I{Validar datos del formulario}
    I -- Datos incorrectos --> J[Mostrar mensajes de error]
    J --> D
    I -- Datos correctos --> K[Guardar cliente en la base de datos]
    K --> L[Guardar patologías asociadas]
    L --> M[Cerrar el modal]
    M --> N[Recargar la tabla de clientes]
    N --> O[Mostrar mensaje: Cliente creado con éxito]

    graph TD
    A[Usuario en Lista de Clientes] --> B{Clic en Botón Ver}
    B --> C[Abrir página de detalle]
    C --> D[Cargar datos del cliente]
    D --> E[Mostrar información completa]
    E --> F[Mostrar lista de patologías asociadas]
    F --> G[Usuario puede ver pero no editar]
    G --> H{Clic en Volver al listado}
    H --> I[Regresar a la lista de clientes]

    graph TD
    A[Usuario en Lista de Clientes] --> B{Clic en Botón Editar}
    B --> C[Abrir Modal Editar Cliente]
    C --> D[Cargar datos actuales del cliente]
    D --> E[Usuario modifica los campos]
    E --> F[Usuario puede agregar o quitar patologías]
    F --> G{Clic en Guardar cambios}
    G --> H[Enviar datos actualizados al servidor]
    H --> I{Validar permisos}
    I -- Sin permiso --> J[Mostrar error: No autorizado]
    I -- Con permiso --> K[Actualizar datos en la base de datos]
    K --> L[Sincronizar patologías]
    L --> M[Cerrar el modal]
    M --> N[Recargar la página]
    N --> O[Mostrar mensaje: Cliente actualizado]

    graph TD
    A[Usuario en Lista de Clientes] --> B{Clic en Botón Eliminar}
    B --> C[Mostrar ventana de confirmación]
    C -- Usuario cancela --> A
    C -- Usuario confirma --> D[Enviar solicitud de eliminación]
    D --> E{Validar permisos}
    E -- Sin permiso --> F[Mostrar error: No autorizado]
    E -- Con permiso --> G[Eliminar patologías asociadas]
    G --> H[Eliminar cliente de la base de datos]
    H --> I[Eliminar la fila de la tabla]
    I --> J[Mostrar mensaje: Cliente eliminado]

    graph TD
    A[Usuario en Lista de Clientes] --> B{Clic en Botón Bloquear o Desbloquear}
    B --> C[Mostrar confirmación]
    C -- Cancelar --> A
    C -- Confirmar --> D[Enviar solicitud al servidor]
    D --> E[Cambiar estado del cliente]
    E --> F[Actualizar la base de datos]
    F --> G[Recargar la tabla]
    G --> H[Mostrar mensaje de éxito]

    graph TD
    A[Usuario accede a Enfermedades] --> B[Mostrar lista de patologías]
    B --> C{El usuario elige una acción}
    
    C --> D[Clic en Nueva Patología]
    D --> E[Abrir modal]
    E --> F[Ingresar nombre de la patología]
    F --> G[Guardar en la base de datos]
    G --> H[Agregar fila a la tabla]
    
    C --> I[Clic en Editar]
    I --> J[Abrir modal]
    J --> K[Modificar el nombre]
    K --> L[Actualizar en la base de datos]
    L --> M[Actualizar la fila en la tabla]
    
    C --> N[Clic en Eliminar]
    N --> O[Confirmar eliminación]
    O -- Confirmar --> P[Eliminar de la base de datos]
    P --> Q[Quitar la fila de la tabla]
    
    H --> R[Mostrar mensaje de éxito]
    M --> R
    Q --> R

    graph TD
    A[Usuario accede a Intereses] --> B[Mostrar lista de intereses]
    B --> C{El usuario elige una acción}
    
    C --> D[Clic en Nuevo Interés]
    D --> E[Abrir modal]
    E --> F[Ingresar descripción]
    F --> G[Guardar en la base de datos]
    G --> H[Agregar fila a la tabla]
    
    C --> I[Clic en Editar]
    I --> J[Abrir modal]
    J --> K[Modificar la descripción]
    K --> L[Actualizar en la base de datos]
    L --> M[Actualizar la fila en la tabla]
    
    C --> N[Clic en Eliminar]
    N --> O[Confirmar eliminación]
    O -- Confirmar --> P[Eliminar de la base de datos]
    P --> Q[Quitar la fila de la tabla]
    
    H --> R[Mostrar mensaje de éxito]
    M --> R
    Q --> R

    graph TD
    A[Usuario en Lista de Cotizaciones] --> B{Clic en Nueva Cotización}
    B --> C[Abrir modal]
    C --> D[Buscar y seleccionar un cliente]
    D --> E[Llenar datos: fase, clasificación, sucursal]
    E --> F[Buscar y agregar productos]
    F --> G[Definir cantidades]
    G --> H{Clic en Guardar}
    H --> I[Validar que haya cliente y productos]
    I -- Faltan datos --> J[Mostrar error]
    J --> C
    I -- Datos completos --> K[Guardar en la base de datos]
    K --> L[Calcular fecha de entrega sugerida]
    L --> M[Si certeza es Alta, apartar productos]
    M --> N[Cerrar modal]
    N --> O[Recargar la lista]
    O --> P[Mostrar mensaje: Cotización creada]

    graph TD
    A[Usuario en Lista de Cotizaciones] --> B{Clic en Editar}
    B --> C{¿La cotización ya fue enviada?}
    
    C -- No fue enviada --> D[Mostrar opciones]
    D --> E[Editar cotización actual]
    D --> F[Crear nueva versión]
    
    C -- Sí fue enviada --> G[Crear nueva versión directamente]
    
    E --> H[Abrir modal de edición]
    H --> I[Modificar datos y/o productos]
    I --> J[Enviar cambios al servidor]
    J --> K{Validar cambios significativos}
    K -- Cambios mayores al 50% --> L[Pedir confirmación al usuario]
    L --> M[Sobrescribir actual]
    L --> N[Crear nueva cotización]
    K -- Cambios menores --> O[Actualizar cotización actual]
    
    F --> P[Abrir modal con datos precargados]
    G --> P
    P --> Q[Modificar la cotización]
    Q --> R[Guardar como nueva versión]
    R --> S[Desactivar cotización original]
    S --> T[Crear nueva con versión +1]
    
    M --> U[Cerrar modal y recargar]
    N --> U
    O --> U
    T --> U
    U --> V[Mostrar mensaje de éxito]

    graph TD
    A[Usuario en Lista de Cotizaciones] --> B{Clic en Ver detalles}
    B --> C[Abrir modal]
    C --> D[Mostrar pestaña Información]
    D --> E[Cargar datos de la cotización]
    E --> F[Mostrar productos]
    F --> G[Usuario cambia a pestaña Versiones]
    G --> H[Cargar historial de versiones anteriores]
    H --> I[Mostrar lista de versiones]
    I --> J[Usuario expande una versión]
    J --> K[Ver productos de esa versión]
    K --> L[Puede revisar cotizaciones canceladas]

    graph TD
    A[Usuario en Lista de Cotizaciones] --> B{Clic en Botón Enviar/PDF}
    B --> C[Generar archivo PDF]
    C --> D{¿La cotización ya estaba enviada?}
    D -- No --> E[Actualizar base de datos]
    E --> F[Cambiar estado a enviada]
    F --> G[Cambiar fase a Completada]
    G --> H[Registrar fecha de envío]
    D -- Sí --> I[Solo generar el PDF]
    H --> J[Descargar el PDF automáticamente]
    I --> J
    J --> K[Recargar la lista]
    K --> L[Mostrar mensaje: PDF generado]

    graph TD
    A[Usuario en Lista de Cotizaciones] --> B{Clic en Eliminar}
    B --> C[Mostrar confirmación]
    C -- Cancelar --> A
    C -- Confirmar --> D[Enviar solicitud al servidor]
    D --> E[Eliminar detalles de la cotización]
    E --> F[Eliminar la cotización]
    F --> G[Quitar la fila de la tabla]
    G --> H[Mostrar mensaje: Cotización eliminada]

    graph TD
    A[Usuario accede a Usuarios] --> B[Mostrar lista de usuarios]
    B --> C{El usuario elige una acción}
    
    C --> D[Clic en Registrar]
    D --> E[Abrir modal]
    E --> F[Llenar datos del usuario]
    F --> G[Guardar en la base de datos]
    G --> H[Agregar fila a la tabla]
    
    C --> I[Clic en Editar]
    I --> J[Abrir modal]
    J --> K[Cargar datos actuales]
    K --> L[Modificar información]
    L --> M[Cambiar permisos de módulos]
    M --> N[Guardar cambios]
    N --> O[Actualizar base de datos]
    O --> P[Recargar la página]
    
    C --> Q[Clic en Eliminar]
    Q --> R[Confirmar eliminación]
    R -- Cancelar --> B
    R -- Confirmar --> S[Eliminar de la base de datos]
    S --> T[Quitar la fila de la tabla]
    
    H --> U[Mostrar mensaje de éxito]
    P --> U
    T --> U

    graph TD
    A[Usuario accede a Permisos] --> B[Cargar la página]
    B --> C[Obtener todos los usuarios]
    C --> D[Obtener sus permisos asignados]
    D --> E[Mostrar lista de usuarios en acordeón]
    E --> F[Usuario hace clic en un usuario]
    F --> G[Expandir el acordeón]
    G --> H[Mostrar tabla de permisos]
    H --> I[Ver qué submódulos tiene acceso]
    I --> J[Ver qué acciones puede realizar]
    J --> K[Solo consulta, no se puede editar]

    