<?php
// index.php
// PHP 5.6.4 совместимо
// Обрабатывает FindGames и FindGameOffers запросы
// Если Locale не en-US и не ru-RU → использовать en-US

error_reporting(0);
header('Content-Type: application/xml; charset=utf-8');

$basePath = __DIR__ . '/content/query';
$params = array_merge($_GET, $_POST);
$methodName = isset($params['methodName']) ? $params['methodName'] : '';

if ($methodName === 'FindGameOffers') {
    // --- Всегда возвращаем offer.xml ---
    $file = $basePath . '/offer.xml';
    if (is_file($file)) {
        readfile($file);
    } else {
        http_response_code(404);
        echo "<error>offer.xml not found</error>";
    }
    exit;
}

if ($methodName === 'FindGames') {
    // --- Получаем строку запроса (GET или POST тело) ---
    $raw = file_get_contents("php://input");
    $queryString = $raw ? $raw : $_SERVER['QUERY_STRING'];

    // --- Извлекаем Locale ---
    $locale = 'en-US'; // по умолчанию
    if (preg_match('/Names=Locale&Values=([A-Za-z0-9\-]+)/', $queryString, $m)) {
        $locale = $m[1];
    }

    // --- Если Locale не en-US и не ru-RU, используем en-US ---
    if ($locale !== 'en-US' && $locale !== 'ru-RU') {
        $locale = 'en-US';
    }

    // --- Извлекаем 8 символов ID после d802 ---
    $id = '';
    if (preg_match('/66acd000-77fe-1000-9115-d802([0-9A-Fa-f]{8})/', $queryString, $m)) {
        $id = strtolower($m[1]);
    }

    // --- Путь до нужного XML ---
    $xmlPath = $basePath . '/' . $locale . '/' . $id . '.xml';
    $defaultOffer = $basePath . '/offer.xml';

    // --- Возврат ---
    if ($id && is_file($xmlPath)) {
        readfile($xmlPath);
        exit;
    } elseif (is_file($defaultOffer)) {
        readfile($defaultOffer);
        exit;
    } else {
        http_response_code(404);
        echo "<error>offer.xml not found</error>";
        exit;
    }
}

// --- Неизвестный methodName ---
$defaultOffer = $basePath . '/offer.xml';
if (is_file($defaultOffer)) {
    readfile($defaultOffer);
} else {
    http_response_code(404);
    echo "<error>Unknown method or offer.xml missing</error>";
}
exit;
?>
