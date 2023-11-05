<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// เพิ่มใบเสร็จ(ตะกร้า)
$app->post('/iorder/{cusID}', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("INSERT INTO `iorder`(`cusID`,`status`) VALUES (? , 0)");
    $stmt->bind_param("i", $args['cusID']);
    $stmt->execute();
    $result = $stmt->affected_rows;

    $response->getBody()->write(json_encode([$result], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// แสดงข้อมูลใบเสร็จของลูกค้า ที่ยังไม่จ่ายเงิน
$app->get('/iorder/{cusID}', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("select * from iorder where cusID = ? and status = 0");
    $stmt->bind_param("i", $args['cusID']);
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

// แสดง iorder ทั้งหมดที่จ่ายเงินแล้ว
$app->get('/iorder', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $sql = 'select iorder.oid, iorder.time, customer.name, iorder.total, iorder.status, customer.phone, customer.address from iorder, customer where iorder.cusID = customer.cusID and status != 0';
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

// แสดงใบเสร็จลูกค้าที่รับเข้ามา
$app->get('/iorder/by/{cusID}', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("select iorder.oid, iorder.time, customer.name, iorder.total, iorder.status, customer.phone, customer.address from iorder, customer where iorder.cusID = customer.cusID and status != 0 and customer.cusID = ?");
    $stmt->bind_param("i", $args['cusID']);
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

// ************************************************************************
$app->post('/iorder/by/{cusID}', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("select iorder.oid, iorder.time, customer.name, iorder.total, iorder.status, customer.phone, customer.address from iorder, customer where iorder.cusID = customer.cusID and status != 0 and customer.cusID = ?");
    $stmt->bind_param("i", $args['cusID']);
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

// เพิ่มรายการสินค้าในใบเสร็จ(ตะกร้า)
$app->post('/iorder/{iorder}/{proID}/{amount}', function (Request $request, Response $response, $args) {

    if(getAmountOfProID($args['iorder'], $args['proID']) <= 0) {
        $conn = $GLOBALS['connect'];
        $stmt = $conn->prepare("INSERT INTO `orderamount`(`oid`, `proID`, `amount`) VALUES (?,?,?)");
        $stmt->bind_param("iii", $args['iorder'], $args['proID'], $args['amount']);
        $stmt->execute();
        $result = $stmt->affected_rows;
    }
    else {
        $conn = $GLOBALS['connect'];
        $stmt = $conn->prepare("UPDATE `orderamount` SET `amount`= (`amount` + 1) WHERE oid = ? and proID = ?");
        $stmt->bind_param("ii", $args['iorder'], $args['proID']);
        $stmt->execute();
        $result = $stmt->affected_rows;
    }

    $response->getBody()->write(json_encode([$result], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// ลบสินค้าออกจากใบเสร็จ(ตะกร้า)
$app->delete('/iorder/{iorder}/{proID}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("DELETE FROM `orderamount` WHERE oid = ? and proID = ?");
    $stmt->bind_param("ii", $args['iorder'], $args['proID']);
    $stmt->execute();
    $result = $stmt->affected_rows;

    $response->getBody()->write(json_encode([$result], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// แสดงรายการสินค้าในใบเสร็จ(ตะกร้า)
$app->get('/iorder/amount/{iorder}', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("select products.image, products.proID, products.name, products.price, orderamount.amount from orderamount, products where orderamount.proID = products.proID and orderamount.oid = ?");
    $stmt->bind_param("i", $args['iorder']);
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

// แก้ไขจำนวนสินค้าในรายการสินค้า
$app->put('/iorder/{iorder}/{proID}/{amount}', function (Request $request, Response $response, $args) {

    if($args['amount'] > 0) {
        $conn = $GLOBALS['connect'];
        $stmt = $conn->prepare("UPDATE `orderamount` SET `amount`= ? WHERE oid = ? and proID = ?");
        $stmt->bind_param("iii", $args['amount'], $args['iorder'], $args['proID']);
        $stmt->execute();
        $result = $stmt->affected_rows;
    }

    $response->getBody()->write(json_encode([$result], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// เปลี่ยนสถานะ iorder เป็นจ่ายเงินแล้วรอส่ง
$app->put('/iorder/{iorder}/{status}/{total}/{time}', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("UPDATE `iorder` SET `status`= ?, `total`= ?, `time`= ? WHERE oid = ?");
    $stmt->bind_param("iisi", $args['status'], $args['total'], $args['time'], $args['iorder']);
    $stmt->execute();
    $result = $stmt->affected_rows;

    $response->getBody()->write(json_encode([$result], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// เปลี่ยนสถานะ iorde
$app->put('/iorder/{iorder}/{status}', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("UPDATE `iorder` SET `status`= ? WHERE oid = ?");
    $stmt->bind_param("ii", $args['status'], $args['iorder']);
    $stmt->execute();
    $result = $stmt->affected_rows;

    $response->getBody()->write(json_encode([$result], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// แสดงจำนวนสินค้าที่ต้องการหาในใบเสร็จ
function getAmountOfProID($oid, $proID) {
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("select * from orderamount where oid = ? and proID = ?");
    $stmt->bind_param("ii", $oid, $proID);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    return sizeof($data);
}



// $app->get('/iorder/{oid}/{proID}', function (Request $request, Response $response, $args) {

//     $conn = $GLOBALS['connect'];
//     $stmt = $conn->prepare("select * from orderamount where oid = ? and proID = ?");
//     $stmt->bind_param("ii", $args['oid'], $args['proID']);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $data = array();
//     foreach ($result as $row) {
//         array_push($data, $row);
//     }

//     $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
//     return $response
//         ->withHeader('Content-Type', 'application/json; charset=utf-8')
//         ->withStatus(200);
// });