USE [fp_central_crm];

SET XACT_ABORT ON;
BEGIN TRANSACTION;

IF OBJECT_ID(N'dbo.crm_mensajes_ticket_sobre_pedido', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.crm_mensajes_ticket_sobre_pedido (
        id_mensaje INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        motivo NVARCHAR(100) NOT NULL,
        mensaje NVARCHAR(300) NOT NULL,
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE NOT NULL,
        activo BIT NOT NULL,
        CONSTRAINT CK_crm_mensajes_ticket_sobre_pedido_fechas
            CHECK (fecha_fin >= fecha_inicio)
    );
END;

IF NOT EXISTS (
    SELECT 1
    FROM dbo.crm_mensajes_ticket_sobre_pedido
    WHERE motivo = N'Buen Fin' AND fecha_inicio = '20261113'
)
BEGIN
    INSERT INTO dbo.crm_mensajes_ticket_sobre_pedido
        (motivo, mensaje, fecha_inicio, fecha_fin, activo)
    VALUES
        (N'Buen Fin',
         N'Reclama tu descuento sobre la compra presentando este comprobante en caja.',
         '20261113', '20261117', 1);
END;

COMMIT TRANSACTION;
