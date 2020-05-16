<?php
define('PROJECT_PATH', dirname(__DIR__));
define('BASE_PATH', PROJECT_PATH . '/');
define('EXT', '.php');

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
foreach ($classMap as $className => $item) {
    $items = [];
    if (is_array($item)) {
        $items = array_merge($items, $item);
    } else {
        $items = array_merge($items, [$item]);
    }
    foreach ($items as $item) {
        $doc = new SwaggerFileDoc($item);
        $content = $doc->getHtml();
        $paths = array_merge($paths, $content);
        $tags[] = [
            'name' => $className,
            'description' => strtolower($className),
        ];
    }
}


$swaggerList["tags"] = $tags;
$swaggerList["paths"] = $paths;

// 生成swagger.json需要的格式，用于swagger-bootstrap-ui-front使用
$result =  $swaggerList;
unset($result['tags']);
$myFile = fopen(BASE_PATH . "web/swagger-ui/json/swagger_api.json", "w");
fwrite($myFile, json_encode($result));
fclose($myFile);

// 输出给yapi使用
header("Content-type: application/json");
echo json_encode($swaggerList);
exit;


