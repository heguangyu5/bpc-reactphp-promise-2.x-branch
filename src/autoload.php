<?php

require __DIR__ . '/functions_include.php';

if (defined('__BPC__')) {
} else {
spl_autoload_register(function ($class) {
    if (strpos($class, 'React\\Promise\\') !== 0) {
        return;
    }
    $class = substr($class, strlen('React\\Promise\\'));
    $path = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});
}
