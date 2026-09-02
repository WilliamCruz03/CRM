<?php

use Illuminate\Support\Facades\Broadcast;

// Canal privado para notificaciones de usuario
Broadcast::channel('user-notifications.{userId}', function ($user, $userId) {
    // Usamos id_personal_empresa como identificador del usuario
    return (int) $user->id_personal_empresa === (int) $userId;
});

// Canal privado para notificaciones de pedidos
Broadcast::channel('pedidos-notifications', function ($user) {
    return $user->es_crm || $user->es_sucursal || $user->es_repartidor;
});

// Canal privado SOLO para usuarios CRM
Broadcast::channel('crm-notifications', function ($user) {
    return $user->es_crm === true;
});