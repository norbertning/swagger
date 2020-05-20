<?php
define('PROJECT_PATH', dirname(__DIR__));
define('BASE_PATH', PROJECT_PATH . '/');
define('EXT', '.php');

// 增加环境判断，提高安全性。只有开发、开发测试、测试环境才能访问
$evnList = ["development", "dev_testing", "testing"];
$evn = $_SERVER['ENVIRONMENT'];
if (!in_array($evn, $evnList)) {
    exit('Access denied.');
}

require_once __DIR__ . '/inc/util.php';
require_once __DIR__ . '/inc/SwaggerFileDoc.php';

$controllerPath = BASE_PATH . 'controllers';
if (!is_dir($controllerPath)) {
    exit('controllers dir not exists: ' . $controllerPath);
}


$content = '';

$swaggerList["swagger"] = "2.0";
$swaggerList["info"] = [
    "title" => "后端API接口文档",
    "version" => "last"
];
$swaggerList["definitions"] = $paths = $tags = [];


$classMap = getClassMap($controllerPath);
if (empty($classMap)) {
    exit('class not exists.');
}

$fileList = [];
foreach ($classMap as $className => $item) {
    if (is_array($item)) {
        $fileList = array_merge($fileList, array_values($item));
    } else {
        array_push($fileList, $item);
    }
}

foreach ($fileList as $file) {
    $doc = new SwaggerFileDoc($file);
    $content = $doc->getHtml();
    $paths = array_merge($paths, $content);

    $fileArr = explode("/", $file);
    $tags[] = [
        'name' => substr($fileArr[count($fileArr) - 1], 0, -4),
        'description' => strtolower(substr($file, strpos($file, 'controllers/') + strlen('controllers/'), -4)),
    ];
}

$swaggerList["tags"] = $tags;
$swaggerList["paths"] = $paths;

// 生成swagger.json需要的格式，用于swagger-bootstrap-ui-front使用
$result = $swaggerList;
unset($result['tags']);
$myFile = fopen(BASE_PATH . "web/swagger-ui/json/swagger_api.json", "w");
fwrite($myFile, json_encode($result));
fclose($myFile);

// 输出给yapi使用
header("Content-type: application/json");
echo json_encode($swaggerList);
exit;


