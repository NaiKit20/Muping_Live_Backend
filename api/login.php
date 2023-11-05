<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/admin/{email}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    
    $sql = 'select * from admin where email = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $args['email']);
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

$app->get('/customer/{email}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    
    $sql = 'select * from customer where email = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $args['email']);
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

$app->get('/login/{email}/{pass}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    
    $sql = 'select * from customer where email = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $args['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    if(sizeof($data) > 0) { // Customer
        if(password_verify($args['pass'], getpasswordCustomer($args['email']))) {
            // $response->getBody()->write("c");
            $response->getBody()->write(json_encode(["c"], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

        }
        else {
            // $response->getBody()->write("0");
            $response->getBody()->write(json_encode(["0"], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

        } 
    }
    else {
        $sql = 'select * from admin where email = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $args['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array();
        foreach ($result as $row) {
            array_push($data, $row);
        }

        if(sizeof($data) > 0) { // Admin
            if(password_verify($args['pass'], getpasswordAdmin($args['email']))) {
                // $response->getBody()->write("a");
                $response->getBody()->write(json_encode(["a"], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
            }
            else {
                // $response->getBody()->write("0");
                $response->getBody()->write(json_encode(["0"], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

            } 
        }

    }
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// แก้ไขข้อมูลลูกค้า
$app->put('/customer/{cusID}/{name}/{phone}/{address}', function (Request $request, Response $response, $args) {

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("UPDATE `customer` SET `name`= ?, `phone`= ?, `address`= ? WHERE `cusID` = ?");
    $stmt->bind_param("sssi", $args['name'], $args['phone'], $args['address'], $args['cusID']);
    $stmt->execute();
    $result = $stmt->affected_rows;

    $response->getBody()->write(json_encode([$result], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

// register user
$app->post('/register/{email}/{password}', function (Request $request, Response $response, $args) {
  
    $email = $args['email'];
    $password = $args['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("update customer set password = ? where email = ?");
    $stmt->bind_param("ss", $hash, $email);
    $stmt->execute();
    $result = $stmt->affected_rows;

    $response->getBody()->write($result."");
    return $response->withHeader('Content-Type','application/json');
   
});

function getpasswordCustomer($email) {
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("select password from customer where email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row["password"];
    }
}

function getpasswordAdmin($email) {
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("select password from admin where email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row["password"];
    }
}

