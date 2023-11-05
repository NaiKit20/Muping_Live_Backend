<?php
    $servername = 'localhost';
    $username = 'korrawich';
    $password = 'abc123';
    $dbname = 'shop';

    $connect = new mysqli($servername, $username, $password, $dbname);
    $connect->set_charset("utf8");
