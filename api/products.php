<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// $app->get('/products', function (Request $request, Response $response, $args) {
//     $response->getBody()->write("Hello world!");
//     return $response;
// });

// แสดงข้อมูลทั้งหมด
$app->get('/products', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'select products.proID, products.name, products.price, type.name as type, products.image from products, type
    where products.typeID = type.typeID';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// แสดงประเภทอาหาร
$app->get('/products/type', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'select type.typeID, type.name from type';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// ค้นหาจาก ประเภท
$app->get('/products/type/{typeName}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    
    $sql = 'select products.proID, products.name, products.price, type.name as type, products.image from products, type
    where products.typeID = type.typeID
    and type.name like ?';
    $stmt = $conn->prepare($sql);
    $name = '%' . $args['typeName'] . '%';
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// ค้นหาจากชื่อ
$app->get('/products/{name}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    
    $sql = 'select products.name, products.price, type.name as type, products.image from products, type
    where products.typeID = type.typeID
    and products.name like ?';
    $stmt = $conn->prepare($sql);
    $name = '%' . $args['name'] . '%';
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// เพิ่มสินค้า