<?php
// settings.php
return [
    'url_redirect_rega' => 'thanks/', // URL куда редиректнуть после регистрации
    'url_redirect_error' => '/#auth-section', // URL куда редиректнуть при ошибках авторизации или регистрации
    'url_redirect_login' => '/#download', // URL куда редиректнуть успешном повторном входе

    'nonce' => base64_encode(random_bytes(16)), // Генерируем уникальный nonce для каждой загрузки страницы
    'csrf_token' => bin2hex(random_bytes(32)), // Генерация уникального токена для формы
    'rootUrl' => ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['SCRIPT_NAME']) . "/",

];