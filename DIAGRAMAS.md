# Diagramas de Flujo - Sistema CRM

## MODULO CLIENTES

### 1. Crear Cliente

[DIAGRAMA 1]
Inicio -> Usuario en Lista de Clientes -> Clic en Boton Nuevo Cliente -> Abrir Modal Nuevo Cliente -> Usuario llena el formulario -> Clic en Guardar -> Enviar datos al servidor -> Validar permisos

Si NO tiene permiso -> Mostrar error: No autorizado -> Fin

Si SI tiene permiso -> Validar datos del formulario

Si datos INCORRECTOS -> Mostrar mensajes de error -> Volver a llenar formulario

Si datos CORRECTOS -> Guardar cliente en la base de datos -> Guardar patologias asociadas -> Cerrar el modal -> Recargar la tabla de clientes -> Mostrar mensaje: Cliente creado con exito -> Fin

### 2. Ver Detalle de Cliente

[DIAGRAMA 2]
Inicio -> Usuario en Lista de Clientes -> Clic en Boton Ver -> Abrir pagina de detalle -> Cargar datos del cliente -> Mostrar informacion completa -> Mostrar lista de patologias asociadas -> Usuario puede ver pero no editar -> Clic en Volver al listado -> Regresar a la lista de clientes -> Fin

### 3. Editar Cliente

[DIAGRAMA 3]
Inicio -> Usuario en Lista de Clientes -> Clic en Boton Editar -> Abrir Modal Editar Cliente -> Cargar datos actuales del cliente -> Usuario modifica los campos -> Usuario puede agregar o quitar patologias -> Clic en Guardar cambios -> Enviar datos actualizados al servidor -> Validar permisos

Si NO tiene permiso -> Mostrar error: No autorizado -> Fin

Si SI tiene permiso -> Actualizar datos en la base de datos -> Sincronizar patologias -> Cerrar el modal -> Recargar la pagina -> Mostrar mensaje: Cliente actualizado -> Fin

### 4. Eliminar Cliente

[DIAGRAMA 4]
Inicio -> Usuario en Lista de Clientes -> Clic en Boton Eliminar -> Mostrar ventana de confirmacion

Si Usuario CANCELA -> Volver a lista de clientes -> Fin

Si Usuario CONFIRMA -> Enviar solicitud de eliminacion -> Validar permisos

Si NO tiene permiso -> Mostrar error: No autorizado -> Fin

Si SI tiene permiso -> Eliminar patologias asociadas -> Eliminar cliente de la base de datos -> Eliminar la fila de la tabla -> Mostrar mensaje: Cliente eliminado -> Fin

### 5. Bloquear/Desbloquear Cliente

[DIAGRAMA 5]
Inicio -> Usuario en Lista de Clientes -> Clic en Boton Bloquear o Desbloquear -> Mostrar confirmacion

Si Usuario CANCELA -> Volver a lista de clientes -> Fin

Si Usuario CONFIRMA -> Enviar solicitud al servidor -> Cambiar estado del cliente -> Actualizar la base de datos -> Recargar la tabla -> Mostrar mensaje de exito -> Fin

## SUBMODULO: ENFERMEDADES (PATOLOGIAS)

### Gestionar Patologias

[DIAGRAMA 6]
Inicio -> Usuario accede a Enfermedades -> Mostrar lista de patologias -> Usuario elige una accion

ACCION 1: Clic en Nueva Patologia -> Abrir modal -> Ingresar nombre de la patologia -> Guardar en la base de datos -> Agregar fila a la tabla -> Mostrar mensaje de exito -> Fin

ACCION 2: Clic en Editar -> Abrir modal -> Modificar el nombre -> Actualizar en la base de datos -> Actualizar la fila en la tabla -> Mostrar mensaje de exito -> Fin

ACCION 3: Clic en Eliminar -> Confirmar eliminacion -> Si confirma -> Eliminar de la base de datos -> Quitar la fila de la tabla -> Mostrar mensaje de exito -> Fin

## SUBMODULO: INTERESES

### Gestionar Intereses

[DIAGRAMA 7]
Inicio -> Usuario accede a Intereses -> Mostrar lista de intereses -> Usuario elige una accion

ACCION 1: Clic en Nuevo Interes -> Abrir modal -> Ingresar descripcion -> Guardar en la base de datos -> Agregar fila a la tabla -> Mostrar mensaje de exito -> Fin

ACCION 2: Clic en Editar -> Abrir modal -> Modificar la descripcion -> Actualizar en la base de datos -> Actualizar la fila en la tabla -> Mostrar mensaje de exito -> Fin

ACCION 3: Clic en Eliminar -> Confirmar eliminacion -> Si confirma -> Eliminar de la base de datos -> Quitar la fila de la tabla -> Mostrar mensaje de exito -> Fin

## MODULO VENTAS - COTIZACIONES

### 1. Crear Cotizacion

[DIAGRAMA 8]
Inicio -> Usuario en Lista de Cotizaciones -> Clic en Nueva Cotizacion -> Abrir modal -> Buscar y seleccionar un cliente -> Llenar datos: fase, clasificacion, sucursal -> Buscar y agregar productos -> Definir cantidades -> Clic en Guardar -> Validar que haya cliente y productos

Si FALTAN DATOS -> Mostrar error -> Volver a abrir modal

Si DATOS COMPLETOS -> Guardar en la base de datos -> Calcular fecha de entrega sugerida -> Si certeza es Alta, apartar productos -> Cerrar modal -> Recargar la lista -> Mostrar mensaje: Cotizacion creada -> Fin

### 2. Editar Cotizacion y Sistema de Versiones

[DIAGRAMA 9]
Inicio -> Usuario en Lista de Cotizaciones -> Clic en Editar

PREGUNTA: La cotizacion ya fue enviada?

Si NO fue enviada -> Mostrar opciones

Opcion 1: Editar cotizacion actual
Opcion 2: Crear nueva version

Si SI fue enviada -> Crear nueva version directamente

--- OPCION EDITAR ACTUAL ---
Abrir modal de edicion -> Modificar datos y/o productos -> Enviar cambios al servidor -> Validar cambios significativos

Si cambios MAYORES al 50% -> Pedir confirmacion al usuario -> Usuario elige Sobrescribir actual -> Cerrar modal y recargar -> Mostrar mensaje de exito -> Fin

Si cambios MENORES -> Actualizar cotizacion actual -> Cerrar modal y recargar -> Mostrar mensaje de exito -> Fin

--- OPCION CREAR NUEVA VERSION ---
Abrir modal con datos precargados -> Modificar la cotizacion -> Guardar como nueva version -> Desactivar cotizacion original -> Crear nueva con version +1 -> Cerrar modal y recargar -> Mostrar mensaje de exito -> Fin

### 3. Ver Detalle de Cotizacion con Historial

[DIAGRAMA 10]
Inicio -> Usuario en Lista de Cotizaciones -> Clic en Ver detalles -> Abrir modal -> Mostrar pestana Informacion -> Cargar datos de la cotizacion -> Mostrar productos -> Usuario cambia a pestana Versiones -> Cargar historial de versiones anteriores -> Mostrar lista de versiones -> Usuario expande una version -> Ver productos de esa version -> Puede revisar cotizaciones canceladas -> Fin

### 4. Enviar Cotizacion (Generar PDF)

[DIAGRAMA 11]
Inicio -> Usuario en Lista de Cotizaciones -> Clic en Boton Enviar/PDF -> Generar archivo PDF

PREGUNTA: La cotizacion ya estaba enviada?

Si NO -> Actualizar base de datos -> Cambiar estado a enviada -> Cambiar fase a Completada -> Registrar fecha de envio -> Descargar el PDF automaticamente -> Recargar la lista -> Mostrar mensaje: PDF generado -> Fin

Si SI -> Solo generar el PDF -> Descargar el PDF automaticamente -> Recargar la lista -> Mostrar mensaje: PDF generado -> Fin

### 5. Eliminar Cotizacion

[DIAGRAMA 12]
Inicio -> Usuario en Lista de Cotizaciones -> Clic en Eliminar -> Mostrar confirmacion

Si Usuario CANCELA -> Volver a lista -> Fin

Si Usuario CONFIRMA -> Enviar solicitud al servidor -> Eliminar detalles de la cotizacion -> Eliminar la cotizacion -> Quitar la fila de la tabla -> Mostrar mensaje: Cotizacion eliminada -> Fin

## MODULO SEGURIDAD

### 1. Gestionar Usuarios

[DIAGRAMA 13]
Inicio -> Usuario accede a Usuarios -> Mostrar lista de usuarios -> Usuario elige una accion

ACCION 1: Clic en Registrar -> Abrir modal -> Llenar datos del usuario -> Guardar en la base de datos -> Agregar fila a la tabla -> Mostrar mensaje de exito -> Fin

ACCION 2: Clic en Editar -> Abrir modal -> Cargar datos actuales -> Modificar informacion -> Cambiar permisos de modulos -> Guardar cambios -> Actualizar base de datos -> Recargar la pagina -> Mostrar mensaje de exito -> Fin

ACCION 3: Clic en Eliminar -> Confirmar eliminacion

Si CANCELA -> Volver a lista

Si CONFIRMA -> Eliminar de la base de datos -> Quitar la fila de la tabla -> Mostrar mensaje de exito -> Fin

### 2. Ver Permisos de Usuarios

[DIAGRAMA 14]
Inicio -> Usuario accede a Permisos -> Cargar la pagina -> Obtener todos los usuarios -> Obtener sus permisos asignados -> Mostrar lista de usuarios en acordeon -> Usuario hace clic en un usuario -> Expandir el acordeon -> Mostrar tabla de permisos -> Ver que submodulos tiene acceso -> Ver que acciones puede realizar -> Solo consulta, no se puede editar -> Fin

## RESUMEN DE FLUJOS PRINCIPALES

MODULO CLIENTES:
- Crear
- Ver
- Editar
- Eliminar
- Bloquear/Desbloquear

MODULO ENFERMEDADES:
- Crear
- Editar
- Eliminar

MODULO INTERESES:
- Crear
- Editar
- Eliminar

MODULO COTIZACIONES:
- Crear
- Editar
- Sistema de Versiones
- Ver con historial
- Enviar PDF
- Eliminar

MODULO USUARIOS:
- Crear
- Editar (con asignacion de permisos)
- Eliminar

MODULO PERMISOS:
- Solo consultar