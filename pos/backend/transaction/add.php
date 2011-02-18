<?php
// this is a RESTful resource and doesn't return any html.
require_once($_SERVER['DOCUMENT_ROOT'].'/define.conf');
$conn = mysql_connect($_SESSION['mServer'], $_SESSION['mUser'], $_SESSION['mPass']);

$desc = $_POST['description'];
$total = $_POST['total'];
$id = $_POST['id'];

if (!$conn) {
  respond_with(500, 0, 'could not connect to local database');
} 

if (!$desc or !$total or !$id) {
  respond_with(400, 0, 'description, total, and member id are required');
}

if (!$_POST['auth_token'] or !($_POST['auth_token'] == $_SESSION['auth_toke'])) {
  respond_with(403, 0, 'Forbidden');
}

// get trans_no
mysql_select_db('is4c_log', $conn);
$query = 'SELECT max(trans_no), max(trans_id) from dtransactions WHERE register_no=1000 and emp_no=1000';
$result = mysql_query($query, $conn);
if ($result) {
  $row = mysql_fetch_array($result);
  $trans_no = $row[0] + 1;
  $trans_id = $row[1] + 1;
}else {
  respond_with(500, 0, 'could not get trans_no and trans_id from local database');
}

$now = date('Y-m-d H-i-s O');
$fields = '(datetime, register_no, emp_no, description, trans_no, trans_id, total, card_no)';
$values = "('$now', 1000, 1000, '$description', $trans_no, $trans_id, $total, $id)";
$query = "INSERT INTO dtransactions $fields values $values";
$result = mysql_query($query, $conn);
if ($result) {
  respond_with(200, 1);
}else {
  respond_with(400, 0, "mysql error ($query): ".mysql_error());
}

function respond_with($code, $val=NULL, $msg=NULL) {
  header("HTTP/1.1 $code", false, $code);
  header('Content-type: application/json');
  echo("{'status':'$val', 'message': '$msg'}");
  exit;
}

?>
