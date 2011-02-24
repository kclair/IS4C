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

require('../../config.php');
require($FANNIE_ROOT."src/SQLManager.php");

$dbc = new SQLManager($FANNIE_SERVER,$FANNIE_SERVER_DBMS,$FANNIE_TRANS_DB,
	$FANNIE_SERVER_USER,$FANNIE_SERVER_PW);

$where = '';
if (isset($_GET['upc'])){
	$where = sprintf("WHERE upc='%s'",str_pad($_GET['upc'],13,'0',STR_PAD_LEFT));
}
else if (isset($_GET['likecode'])){
	$where = "LEFT JOIN upclike AS u ON d.upc=u.upc WHERE u.likecode=".$_GET['likecode'];
}
else
	exit;

echo "<table><th>&nbsp;<th align=right>Qty<th align=right>Sales<tr>";

$q = "SELECT sum(case when ".$dbc->datediff($dbc->now(),'tdate')."=1 AND trans_status<>'M' THEN quantity else 0 end) as qty1,
	sum(case when ".$dbc->datediff($dbc->now(),'tdate')."=1 THEN total else 0 end) as total1,
	sum(case when ".$dbc->datediff($dbc->now(),'tdate')."=2 AND trans_status<>'M' THEN quantity else 0 end) as qty2,
	sum(case when ".$dbc->datediff($dbc->now(),'tdate')."=2 THEN total else 0 end) as total2,
	sum(case when ".$dbc->datediff($dbc->now(),'tdate')."=3 AND trans_status<>'M' THEN quantity else 0 end) as qty3,
	sum(case when ".$dbc->datediff($dbc->now(),'tdate')."=3 THEN total else 0 end) as total3,
	sum(case when ".$dbc->weekdiff($dbc->now(),'tdate')."=0 AND trans_status<>'M' THEN quantity else 0 end) as qtywk0,
	sum(case when ".$dbc->weekdiff($dbc->now(),'tdate')."=0 THEN total else 0 end) as totalwk0,
	sum(case when ".$dbc->weekdiff($dbc->now(),'tdate')."=1 AND trans_status<>'M' THEN quantity else 0 end) as qtywk1,
	sum(case when ".$dbc->weekdiff($dbc->now(),'tdate')."=1 THEN total else 0 end) as totalwk1
	FROM dlog_15 as d $where";
$r = $dbc->query($q);
$w = $dbc->fetch_row($r);

echo "<td><font color=blue>Yesterday</font></td>";
printf("<td style=\"padding-left: 20px;\" align=right>%.2f</td><td style=\"padding-left: 20px;\" align=right>$%.2f",
	$w['qty1'],$w['total1']);

echo "</td><tr><td><font color=blue>2 Days ago</font></td>";
printf("<td align=right>%.2f</td><td align=right>$%.2f",$w['qty2'],$w['total2']);

echo "</td><tr><td><font color=blue>3 Days ago</font></td>";
printf("<td align=right>%.2f</td><td align=right>$%.2f",$w['qty3'],$w['total3']);

echo "</td><tr><td><font color=blue>This Week</font></td>";
printf("<td align=right>%.2f</td><td align=right>$%.2f",$w['qtywk0'],$w['totalwk0']);

echo "</tr><tr><td><font color=blue>Last Week</font></td>";
printf("<td align=right>%.2f</td><td align=right>$%.2f",$w['qtywk1'],$w['totalwk1']);

$q = "SELECT sum(case when ".$dbc->monthdiff($dbc->now(),'tdate')."=0 AND trans_status<>'M' THEN quantity else 0 end) as qtym0,
	sum(case when ".$dbc->monthdiff($dbc->now(),'tdate')."=0 THEN total else 0 end) as totalm0,
	sum(case when ".$dbc->monthdiff($dbc->now(),'tdate')."=1 AND trans_status<>'M' THEN quantity else 0 end) as qtym1,
	sum(case when ".$dbc->monthdiff($dbc->now(),'tdate')."=1 THEN total else 0 end) as totalm1
	FROM dlog_90_view as d $where";
$r = $dbc->query($q);
$w = $dbc->fetch_row($r);

echo "</td><tr><td><font color=blue>This Month</font></td>";
printf("<td align=right>%.2f</td><td align=right>$%.2f",$w['qtym0'],$w['totalm0']);

echo "</td><tr><td><font color=blue>Last Month</font></td>";
printf("<td align=right>%.2f</td><td align=right>$%.2f",$w['qtym1'],$w['totalm1']);

echo "</tr></table>";
?>
