<?php
function basePath($path = '') {
    
    $scriptName = $_SERVER['SCRIPT_NAME']; 
    $base = dirname($scriptName);
    while (basename($base) !== 'pbl-portfolio' && $base !== '/') {
        $base = dirname($base);
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}