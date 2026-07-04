<?php
// app/Session/FileSessionHandler.php

namespace App\Session;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\FileSessionHandler as BaseFileSessionHandler;

class FileSessionHandler extends BaseFileSessionHandler
{
    public function __construct(Filesystem $files, $path, $lifetime)
    {
        parent::__construct($files, $path, $lifetime);
    }
    
    // Sobrescribir métodos que usen APP_KEY si es necesario
}