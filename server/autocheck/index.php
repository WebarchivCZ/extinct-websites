<?php
include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/connect.php";
include "./check.php";

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$status=check($_GET['url'], false, false);

echo json_encode($status);

?>
