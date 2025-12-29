<?php
session_start();

// CSRF проверка
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
	$_SESSION["error"] = "Error! CSRF Token mismatch.";
}

$config = require_once 'settings.php';

$phone = $_POST['phoneNumber'] ?? '';
$name = $_POST['name'] ?? '';
$password = $_POST['password'] ?? '';
$privacy = $_POST['privacy'] ?? null;

if (!$phone || !$password || !$privacy) {
	$_SESSION["error"] = "Error! All fields are required.";
}

$usersFile = __DIR__ . '/set/user/db/users.json';
$dir = dirname($usersFile); // Получаем путь к папке

if (!is_dir($dir)) {
    mkdir($dir, 0777, true); // Рекурсивно создаём все вложенные папки
}


// Путь к .htaccess-файлу в папке /set/user/db/
$htaccessPath = __DIR__ . '/set/user/db/.htaccess';

// Содержимое файла: запрет доступа ко всем .json-файлам
$rule = <<<HTACCESS
<FilesMatch "\.json$">
    Require all denied
</FilesMatch>
HTACCESS;

// Проверяем, существует ли папка — если нет, создаём её
$dir = dirname($htaccessPath);
if (!is_dir($dir)) {
    mkdir($dir, 0777, true); // true — создаёт вложенные папки, если нужно
}

// Проверяем, существует ли уже .htaccess
if (!file_exists($htaccessPath)) {
    // Пишем правило в файл
    if (file_put_contents($htaccessPath, $rule) !== false) {
        // echo ".htaccess успешно создан и защищает .json файлы.";
    } else {
        // echo "Ошибка при записи .htaccess файла.";
    }
} else {
    // echo ".htaccess уже существует — создание не требуется.";
}












// Создаём пустой JSON-файл, если он не существует
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, '{}');
}

$users = json_decode(file_get_contents($usersFile), true) ?? [];

// Авторизация или регистрация
if (isset($users[$phone])) {
    // Пользователь найден — проверяем пароль
    if (password_verify($password, $users[$phone]['password'])) {
        $_SESSION['user'] = $users[$phone]['name'];
		header("Location: " . $config['url_redirect_login']); // Отправляем на блок с кнопкой для скачки прилки
        exit;
    } else {
		$_SESSION["error"] = "Error! Incorrect phone number or password entered.";
		header("Location: " . $config['url_redirect_error']); // Отправляем обратно на вход в аккаунт
    }
} else {
    // Регистрация нового пользователя
    $users[$phone] = [
        'name' => $name,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s')
    ];
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    $_SESSION['user'] = $name;
    header("Location: " . $config['url_redirect_rega']); // Отправляем на блок в кнопкой скачать прилку
    exit;
}
?>
