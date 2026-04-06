Diagramas de Flujo - Sistema CRM
MODULO CLIENTES
1. Crear Cliente
```mermaid
graph TD
A[Usuario en Lista de Clientes] --> B{Clic en Boton Nuevo Cliente}
B --> C[Abrir Modal Nuevo Cliente]
C --> D[Usuario llena el formulario]
D --> E{Clic en Guardar}
E --> F[Enviar datos al servidor]
F --> G{Validar permisos}
G -- No --> H[Mostrar error: No autorizado]
G -- Si --> I{Validar datos del formulario}
I -- Incorrectos --> J[Mostrar mensajes de error]
J --> D
I -- Correctos --> K[Guardar cliente en la base de datos]
K --> L[Guardar patologias asociadas]
L --> M[Cerrar el modal]
M --> N[Recargar la tabla de clientes]
N --> O[Mostrar mensaje: Cliente creado con exito]
```

2. Ver Detalle de Cliente
```mermaid
graph TD
A[Usuario en Lista de Clientes] --> B{Clic en Boton Ver}
B --> C[Abrir pagina de detalle]
C --> D[Cargar datos del cliente]
D --> E[Mostrar informacion completa]
E --> F[Mostrar lista de patologias asociadas]
F --> G[Usuario puede ver pero no editar]
G --> H{Clic en Volver al listado}
H --> I[Regresar a la lista de clientes]
```

3. Editar Cliente
```mermaid
graph TD
A[Usuario en Lista de Clientes] --> B{Clic en Boton Editar}
B --> C[Abrir Modal Editar Cliente]
C --> D[Cargar datos actuales del cliente]
D --> E[Usuario modifica los campos]
E --> F[Usuario puede agregar o quitar patologias]
F --> G{Clic en Guardar cambios}
G --> H[Enviar datos actualizados al servidor]
H --> I{Validar permisos}
I -- No --> J[Mostrar error: No autorizado]
I -- Si --> K[Actualizar datos en la base de datos]
K --> L[Sincronizar patologias]
L --> M[Cerrar el modal]
M --> N[Recargar la pagina]
N --> O[Mostrar mensaje: Cliente actualizado]
```

4. Eliminar Cliente
```mermaid
graph TD
A[Usuario en Lista de Clientes] --> B{Clic en Boton Eliminar}
B --> C[Mostrar ventana de confirmacion]
C -- Cancela --> A
C -- Confirma --> D[Enviar solicitud de eliminacion]
D --> E{Validar permisos}
E -- No --> F[Mostrar error: No autorizado]
E -- Si --> G[Eliminar patologias asociadas]
G --> H[Eliminar cliente de la base de datos]
H --> I[Eliminar la fila de la tabla]
I --> J[Mostrar mensaje: Cliente eliminado]
```

5. Bloquear/Desbloquear Cliente
```mermaid
graph TD
A[Usuario en Lista de Clientes] --> B{Clic en Boton Bloquear o Desbloquear}
B --> C[Mostrar confirmacion]
C -- Cancela --> A
C -- Confirma --> D[Enviar solicitud al servidor]
D --> E[Cambiar estado del cliente]
E --> F[Actualizar la base de datos]
F --> G[Recargar la tabla]
G --> H[Mostrar mensaje de exito]
```

SUBMODULO: ENFERMEDADES (PATOLOGIAS)
Gestionar Patologias
```mermaid
graph TD
A[Usuario accede a Enfermedades] --> B[Mostrar lista de patologias]
B --> C{Usuario elige una accion}
C --> D[Clic en Nueva Patologia]
D --> E[Abrir modal]
E --> F[Ingresar nombre de la patologia]
F --> G[Guardar en la base de datos]
G --> H[Agregar fila a la tabla]
H --> I[Mostrar mensaje de exito]

C --> J[Clic en Editar]
J --> K[Abrir modal]
K --> L[Modificar el nombre]
L --> M[Actualizar en la base de datos]
M --> N[Actualizar la fila en la tabla]
N --> I

C --> O[Clic en Eliminar]
O --> P[Confirmar eliminacion]
P -- Confirma --> Q[Eliminar de la base de datos]
Q --> R[Quitar la fila de la tabla]
R --> I
```

SUBMODULO: INTERESES
Gestionar Intereses
```mermaid
graph TD
A[Usuario accede a Intereses] --> B[Mostrar lista de intereses]
B --> C{Usuario elige una accion}
C --> D[Clic en Nuevo Interes]
D --> E[Abrir modal]
E --> F[Ingresar descripcion]
F --> G[Guardar en la base de datos]
G --> H[Agregar fila a la tabla]
H --> I[Mostrar mensaje de exito]

C --> J[Clic en Editar]
J --> K[Abrir modal]
K --> L[Modificar la descripcion]
L --> M[Actualizar en la base de datos]
M --> N[Actualizar la fila en la tabla]
N --> I

C --> O[Clic en Eliminar]
O --> P[Confirmar eliminacion]
P -- Confirma --> Q[Eliminar de la base de datos]
Q --> R[Quitar la fila de la tabla]
R --> I
```

MODULO VENTAS - COTIZACIONES
1. Crear Cotizacion
```mermaid
graph TD
A[Usuario en Lista de Cotizaciones] --> B{Clic en Nueva Cotizacion}
B --> C[Abrir modal]
C --> D[Buscar y seleccionar un cliente]
D --> E[Llenar datos: fase, clasificacion, sucursal]
E --> F[Buscar y agregar productos]
F --> G[Definir cantidades]
G --> H{Clic en Guardar}
H --> I{Validar que haya cliente y productos}
I -- Faltan datos --> J[Mostrar error]
J --> C
I -- Datos completos --> K[Guardar en la base de datos]
K --> L[Calcular fecha de entrega sugerida]
L --> M[Si certeza es Alta, apartar productos]
M --> N[Cerrar modal]
N --> O[Recargar la lista]
O --> P[Mostrar mensaje: Cotizacion creada]
```

2. Editar Cotizacion y Sistema de Versiones
```mermaid
graph TD
A[Usuario en Lista de Cotizaciones] --> B{Clic en Editar}
B --> C{La cotizacion ya fue enviada?}
C -- No --> D[Mostrar opciones]
D --> E[Editar cotizacion actual]
D --> F[Crear nueva version]
C -- Si --> G[Crear nueva version directamente]

E --> H[Abrir modal de edicion]
H --> I[Modificar datos y/o productos]
I --> J[Enviar cambios al servidor]
J --> K{Validar cambios significativos}
K -- Cambios mayores al 50% --> L[Pedir confirmacion al usuario]
L --> M[Sobrescribir actual]
L --> N[Crear nueva cotizacion]
K -- Cambios menores --> O[Actualizar cotizacion actual]

F --> P[Abrir modal con datos precargados]
G --> P
P --> Q[Modificar la cotizacion]
Q --> R[Guardar como nueva version]
R --> S[Desactivar cotizacion original]
S --> T[Crear nueva con version +1]

M --> U[Cerrar modal y recargar]
N --> U
O --> U
T --> U
U --> V[Mostrar mensaje de exito]
```

3. Ver Detalle de Cotizacion con Historial
```mermaid
graph TD
A[Usuario en Lista de Cotizaciones] --> B{Clic en Ver detalles}
B --> C[Abrir modal]
C --> D[Mostrar pestana Informacion]
D --> E[Cargar datos de la cotizacion]
E --> F[Mostrar productos]
F --> G[Usuario cambia a pestana Versiones]
G --> H[Cargar historial de versiones anteriores]
H --> I[Mostrar lista de versiones]
I --> J[Usuario expande una version]
J --> K[Ver productos de esa version]
```

4. Enviar Cotizacion (Generar PDF)
```mermaid
graph TD
A[Usuario en Lista de Cotizaciones] --> B{Clic en Boton Enviar/PDF}
B --> C[Generar archivo PDF]
C --> D{La cotizacion ya estaba enviada?}
D -- No --> E[Actualizar base de datos]
E --> F[Cambiar estado a enviada]
F --> G[Cambiar fase a Completada]
G --> H[Registrar fecha de envio]
D -- Si --> I[Solo generar el PDF]
H --> J[Descargar el PDF automaticamente]
I --> J
J --> K[Recargar la lista]
K --> L[Mostrar mensaje: PDF generado]
```

5. Eliminar Cotizacion
```mermaid
graph TD
A[Usuario en Lista de Cotizaciones] --> B{Clic en Eliminar}
B --> C[Mostrar confirmacion]
C -- Cancela --> A
C -- Confirma --> D[Enviar solicitud al servidor]
D --> E[Eliminar detalles de la cotizacion]
E --> F[Eliminar la cotizacion]
F --> G[Quitar la fila de la tabla]
G --> H[Mostrar mensaje: Cotizacion eliminada]
```

MODULO SEGURIDAD
1. Gestionar Usuarios
```mermaid
graph TD
A[Usuario accede a Usuarios] --> B[Mostrar lista de usuarios]
B --> C{Usuario elige una accion}

C --> D[Clic en Registrar]
D --> E[Abrir modal]
E --> F[Llenar datos del usuario]
F --> G[Guardar en la base de datos]
G --> H[Agregar fila a la tabla]
H --> I[Mostrar mensaje de exito]

C --> J[Clic en Editar]
J --> K[Abrir modal]
K --> L[Cargar datos actuales]
L --> M[Modificar informacion]
M --> N[Cambiar permisos de modulos]
N --> O[Guardar cambios]
O --> P[Actualizar base de datos]
P --> Q[Recargar la pagina]
Q --> I

C --> R[Clic en Eliminar]
R --> S[Confirmar eliminacion]
S -- Cancela --> B
S -- Confirma --> T[Eliminar de la base de datos]
T --> U[Quitar la fila de la tabla]
U --> I
```

2. Ver Permisos de Usuarios
```mermaid
graph TD
A[Usuario accede a Permisos] --> B[Cargar la pagina]
B --> C[Obtener todos los usuarios]
C --> D[Obtener sus permisos asignados]
D --> E[Mostrar lista de usuarios en acordeon]
E --> F[Usuario hace clic en un usuario]
F --> G[Expandir el acordeon]
G --> H[Mostrar tabla de permisos]
H --> I[Ver que submodulos tiene acceso]
I --> J[Ver que acciones puede realizar]
J --> K[Solo consulta, no se puede editar]
```
