<?php
/*******************************************************************************

    Copyright 2010 Whole Foods Co-op

    This file is part of IS4C.

    IS4C is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    IS4C is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IS4C; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

$IS4C_PATH = isset($IS4C_PATH)?$IS4C_PATH:"";
if (empty($IS4C_PATH)){ while(!file_exists($IS4C_PATH."is4c.css")) $IS4C_PATH .= "../"; }

include($IS4C_PATH."ini.php");
if (!function_exists("pDataConnect")) include($IS4C_PATH."lib/connect.php");
if (!function_exists("initiate_session")) include($IS4C_PATH."lib/session.php");
if (!isset($IS4C_LOCAL)) include($IS4C_PATH."lib/LocalStorage/conf.php");

initiate_session();

$id = $_GET['id'];

$db_g = pDataConnect();
$query_q = "select id, FirstName, LastName from custdata where id=".$id;
$result_q = $db_g->query($query_q);

if ($db_g->num_rows($result_q) > 0) {
  $row_q = $db_g->fetch_array($result_q);
  $transno = gettransno($id);
  $IS4C_LOCAL->set("transno",$transno);

  $globals = array(
    'CashierNo' => $id
  );

  setglobalvalues($globals);
}else {
  echo("num rows is 0");
}

?>

