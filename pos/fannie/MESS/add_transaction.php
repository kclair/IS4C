<?php
// this is a RESTful resource and doesn't return any html.

include('../config.php');
include('../src/SQLManager.php');
$conn = new SQLManager($FANNIE_SERVER,$FANNIE_SERVER_DBMS,$FANNIE_TRANS_DB,
                $FANNIE_SERVER_USER,$FANNIE_SERVER_PW);

$desc = $_POST['description'];
$total = $_POST['total'];
$id = $_POST['id'];

if (!$conn) {
  respond_with(500, 0, 'could not connect to local database');
} 

if (!$desc or !$total or !$id) {
  respond_with(400, 0, 'description, total, and member id are required');
}

//auth token method of storage TBD
if (!$_POST['auth_token'] or !($_POST['auth_token'] == 'foo')) {
  respond_with(403, 0, 'Forbidden');
}

// get trans_no
$query = 'SELECT max(trans_no), max(trans_id) from dtransactions WHERE register_no=1000 and emp_no=1000';
$result = $conn->query($query);
if ($result) {
  $row = $conn->fetch_array($result);
  $trans_no = $row[0] + 1;
  $trans_id = $row[1] + 1;
}else {
  respond_with(500, 0, 'could not get trans_no and trans_id from local database: '.$conn->error());
}

$now = date('Y-m-d H-i-s O');
$fields = '(datetime, register_no, emp_no, description, trans_no, trans_id, total, card_no)';
$values = "('$now', 1000, 1000, '$description', $trans_no, $trans_id, $total, $id)";
$query = "INSERT INTO dtransactions $fields values $values";
$result = $conn->query($query);
if ($result) {
  respond_with(200, 1);
}else {
  respond_with(400, 0, "mysql error ($query): ".$conn->error());
}

function respond_with($code, $val=NULL, $msg=NULL) {
  header("HTTP/1.1 $code", false, $code);
  header('Content-type: application/json');
  echo("{'status':'$val', 'message': '$msg'}");
  exit;
}

?>
