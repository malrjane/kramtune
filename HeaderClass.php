<?php
/*
	Инструкция по установке заголовков безопасности в верстку!

1.	Необходимо подключить файл HeaderClass.php в каждый html файл верстки.
	Просто вставьте строку ниже в самое начало файла html,
	что бы вначале строки не было ни единого пробела, символа или тега
	
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/HeaderClass.php'; ?>

2.	Атрибут nonce вставляется в подключаемый inline скрипт
	(он необходим, что бы политика безопасности не блокировала этот скрипт)
	
	nonce="<?php echo $config['nonce']; ?>"
	
	Например:
		<!-- Google Tag Manager -->
		<script nonce="<?php echo $config['nonce']; ?>">
			(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','dataLayer','GTM-XXXXXX');
		</script>
		<!-- End Google Tag Manager -->

3.	Input с csrf_token токеном вставляется во все формы отправки, что есть в верстке без исключений
	и на серверном файле этот токен должен сверяться например так - 
	--------------------------------------------------------------------------------------------
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		header('Location: 'Если токен не совпадает, тогда редирект обратно на форму или возвращаем по ajax ошибку'), true, 303);
		exit;
	} Если токен верный тогда код ниже этой конструкции будет выполнится
	--------------------------------------------------------------------------------------------
	
	<input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">
	
	Например:
		<form action="submit.php" method="POST">
			<label for="name">Имя:</label>
			<input type="text" id="name" name="name" required>
			
			<label for="email">Email:</label>
			<input type="email" id="email" name="email" required>
			
			<label for="message">Сообщение:</label>
			<textarea id="message" name="message" rows="5" required></textarea>
			
			<!-- CSRF-токен -->
			<input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">
			
			<button type="submit">Отправить</button>
		</form>
		
*/

$config = require_once 'settings.php';


// Генерируем уникальный nonce для каждой загрузки страницы

// Список всех доменных зон Google с подстановочным знаком для поддоменов
$google_zones = array_map(function($zone) {
    return "https://*.$zone"; // Добавляем * для поддоменов
}, [
    "google.com", "google.ad", "google.ae", "google.com.af", "google.com.ag", "google.al", "google.am", "google.co.ao", 
    "google.com.ar", "google.as", "google.at", "google.com.au", "google.az", "google.ba", "google.com.bd", "google.be", 
    "google.bf", "google.bg", "google.com.bh", "google.bi", "google.bj", "google.com.bn", "google.com.bo", "google.com.br", 
    "google.bs", "google.bt", "google.co.bw", "google.by", "google.com.bz", "google.ca", "google.cd", "google.cf", 
    "google.cg", "google.ch", "google.ci", "google.co.ck", "google.cl", "google.cm", "google.cn", "google.com.co", 
    "google.co.cr", "google.com.cu", "google.cv", "google.com.cy", "google.cz", "google.de", "google.dj", "google.dk", 
    "google.dm", "google.com.do", "google.dz", "google.com.ec", "google.ee", "google.com.eg", "google.es", "google.com.et", 
    "google.fi", "google.com.fj", "google.fm", "google.fr", "google.ga", "google.ge", "google.gg", "google.com.gh", 
    "google.com.gi", "google.gl", "google.gm", "google.gr", "google.com.gt", "google.gy", "google.com.hk", "google.hn", 
    "google.hr", "google.ht", "google.hu", "google.co.id", "google.ie", "google.co.il", "google.im", "google.co.in", 
    "google.iq", "google.is", "google.it", "google.je", "google.com.jm", "google.jo", "google.co.jp", "google.co.ke", 
    "google.com.kh", "google.ki", "google.kg", "google.co.kr", "google.com.kw", "google.kz", "google.la", "google.com.lb", 
    "google.li", "google.lk", "google.co.ls", "google.lt", "google.lu", "google.lv", "google.com.ly", "google.co.ma", 
    "google.md", "google.me", "google.mg", "google.mk", "google.ml", "google.com.mm", "google.mn", "google.com.mt", 
    "google.mu", "google.mv", "google.mw", "google.com.mx", "google.com.my", "google.co.mz", "google.com.na", 
    "google.com.ng", "google.com.ni", "google.ne", "google.nl", "google.no", "google.com.np", "google.nr", "google.nu", 
    "google.co.nz", "google.com.om", "google.com.pa", "google.com.pe", "google.com.pg", "google.com.ph", "google.com.pk", 
    "google.pl", "google.pn", "google.com.pr", "google.ps", "google.pt", "google.com.py", "google.com.qa", "google.ro", 
    "google.ru", "google.rw", "google.com.sa", "google.com.sb", "google.sc", "google.se", "google.com.sg", "google.sh", 
    "google.si", "google.sk", "google.com.sl", "google.sn", "google.so", "google.sm", "google.sr", "google.st", 
    "google.com.sv", "google.td", "google.tg", "google.co.th", "google.com.tj", "google.tl", "google.tm", "google.tn", 
    "google.to", "google.com.tr", "google.tt", "google.com.tw", "google.co.tz", "google.com.ua", "google.co.ug", 
    "google.co.uk", "google.com.uy", "google.co.uz", "google.com.vc", "google.co.ve", "google.co.vi", "google.com.vn", 
    "google.vu", "google.ws", "google.rs", "google.co.za", "google.co.zm", "google.co.zw", "google.cat"
]);

// Дополнительные сервисы Google с подстановочным знаком
$google_services = array_merge($google_zones, [
    "https://*.doubleclick.net",         // Все поддомены DoubleClick
    "https://*.googleadservices.com",    // Google Ad Services
    "https://*.google-analytics.com",    // Google Analytics
    "https://*.googletagmanager.com",    // Google Tag Manager
    "https://ipapi.co",    // Для определения ГЕО
    "https://*.g.doubleclick.net"        // Статистика DoubleClick
]);

// Определяем базовые директивы CSP
$csp = [
    "default-src" => "'none'",
    "script-src" => [
        "'self'",
        "'unsafe-hashes'",
        "'nonce-{$config['nonce']}'",
        "https://*.googletagmanager.com",
        "https://*.google-analytics.com",
        "https://*.googleapis.com",
        "https://*.googleadservices.com"
    ],
    "connect-src" => ["'self'"],
    "img-src" => ["'self'", "data:"],
    "frame-src" => [
        "'self'",
        "https://youtube.com/",         // YouTube
        "https://*.youtube.com/",         // YouTube
        "https://google.com/",          // Google Maps и другие фреймы
        "https://*.google.com/",          // Google Maps и другие фреймы
    ],
    "object-src" => "'none'",
    "base-uri" => "'self'",
    "frame-ancestors" => ["'self'"],
    "form-action" => "'self'",
    "style-src" => ["'self'", "https://fonts.googleapis.com"],
    "style-src-elem" => ["'self'", "https://fonts.googleapis.com"],
    "font-src" => ["'self'", "https://fonts.gstatic.com"],
    // "report-uri" => "/csp-report.php" // Для мониторинга нарушений
];

// Функция для добавления сервисов в нужные директивы
function addServiceDomains(array &$csp, array $services, array $directives) {
    foreach ($directives as $directive) {
        if (isset($csp[$directive])) {
            $csp[$directive] = array_merge($csp[$directive], $services);
        }
    }
}

// Добавляем все Google-сервисы в connect-src, img-src и frame-src
addServiceDomains($csp, $google_services, ["connect-src", "img-src", "frame-src"]);

// Генерируем строку CSP
$csp_header = "";
foreach ($csp as $directive => $sources) {
    $sources_string = is_array($sources) ? implode(" ", $sources) : $sources;
    $csp_header .= "$directive $sources_string; ";
}

// Устанавливаем заголовок CSP
header("Content-Security-Policy: " . trim($csp_header));

// Настройка параметров cookie перед запуском сессии
session_set_cookie_params([
    'lifetime' => 0,          // Время жизни cookie (0 = до закрытия браузера)
    'path' => '/',            // Путь, где cookie доступен
    'domain' => '',           // Домен (пусто = текущий домен)
    'secure' => true,         // Только через HTTPS
    'httponly' => true,       // Установка флага HttpOnly
    'samesite' => 'Lax'       // Защита от CSRF (Lax или Strict)
]);

if (session_status() === PHP_SESSION_NONE) {
    if (headers_sent()) {
        // Заголовки уже отправлены, сессию начать нельзя
        error_log("Ошибка: Невозможно начать сессию, заголовки уже отправлены.");
    } else {
        session_start();
    }
}

// Добавляем уникальный csrf_token в сессию
$_SESSION["csrf_token"] = $config['csrf_token'];


// Абсолютный путь до нужного файла
$filename = 'index.html';

// Проверка: файл должен находиться в корне (относительно этого скрипта)
if (is_file($filename) && dirname($filename) === '.') {
    // URL для canonical
    $canonicalUrl = 'https://' . $_SERVER['HTTP_HOST'];
    // Читаем файл
    $html = file_get_contents($filename);
    // Проверяем, есть ли уже тег canonical
    if (strpos($html, 'rel="canonical"') === false) {
        $canonicalTag = '<link rel="canonical" href="' . $canonicalUrl . '">' . PHP_EOL;
        // Вставляем перед </head>
        if (preg_match('/<\/head>/i', $html)) {
            $updatedHtml = preg_replace('/<\/head>/i', $canonicalTag . '</head>', $html, 1);
            file_put_contents($filename, $updatedHtml);
            // echo "Canonical добавлен.";
        } else {
            // echo "Не найден </head>.";
        }
    } else {
        // echo "Canonical уже есть.";
    }
}

?>