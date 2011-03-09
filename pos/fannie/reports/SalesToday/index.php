<?php
/*******************************************************************************

    Copyright 2009 Whole Foods Co-op

    This file is part of Fannie.

    Fannie is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Fannie is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IS4C; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

include('../../config.php');
include($FANNIE_ROOT.'src/mysql_connect.php');
include('../lib/reports_functions.php');

if (isset($_GET['dept'])) {
  $name = $_GET['dept'];
  $only_dept = $name;
}else {
  $name = "All";
  $only_dept = null;
}

$depts = get_departments();
$result = SalesFromDay($today, $only_dept);

$page_title = "Fannie : Today's $name Sales";
$header = "Today's $name Sales";
include($FANNIE_ROOT.'src/header.html');

echo "<div align=\"center\"><h1>Today's <span style=\"color:green;\">$name</span> Sales!</h1>";
echo "<table cellpadding=4 cellspacing=2>";
echo "<tr><td><b>Hour</b></td><td><b>Sales</b></td><td><b>Department</b></td></tr>";
$sum = 0;
foreach ($result as $row) {
	printf("<tr><td>%d</td><td>%.2f</td><td>%s</td></tr>",
		$row[0],
                $row[1],
                $row[2];
	$sum += $row[1];
}
echo "<tr><th width=60px align=left>Total</th><td>";
echo $sum;
echo "</td></tr></table>";

echo "<p />";
echo "Also available: <select onchange=\"top.location='index.php?dept='+this.value;\">";
foreach($depts as $k=>$v){
	echo "<option value=$k";
	if ($k == $selected)
		echo " selected";
	echo ">$v</option>";
}
echo "</select></div>";

include($FANNIE_ROOT.'src/footer.html');
?>
