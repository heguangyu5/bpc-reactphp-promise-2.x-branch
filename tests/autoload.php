<?php

require __DIR__ . '/../src/autoload.php';

spl_autoload_register(function ($class) {
    if (strpos($class, 'React\\Promise\\') !== 0) {
        return;
    }

    $class = substr($class, strlen('React\\Promise\\'));
    $file = str_replace('\\', '/', $class) . '.php';

    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        require $path;
        return;
    }

    $path = __DIR__ . '/fixtures/' . $file;
    if (file_exists($path)) {
        require $path;
    }
});
