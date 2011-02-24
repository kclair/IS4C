<?php
/*******************************************************************************

    Copyright 2011 Whole Foods Co-op

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
include('../config.php');
include($FANNIE_ROOT.'src/mysql_connect.php');

$page_title = "Fannie :: Special Order Receiving";
$header = "Special Order Receiving";
include($FANNIE_ROOT.'src/header.html');
?>
<script type="text/javascript">
function refilter(f){
	var o = $('#orderSetting').val();
	location = "receivingReport.php?f="+f+"&order="+o;
}
function resort(o){
	var f= $('#sF').val();
	location = "receivingReport.php?f="+f+"&order="+o;
}
</script>
<?
$status = array(
	""=> "Any",
	0 => "New",
	2 => "Pending",
	4 => "Placed"
);

$order = isset($_REQUEST['order'])?$_REQUEST['order']:'mixMatch';
$filter = isset($_REQUEST['f'])?$_REQUEST['f']:'';
if ($filter !== '') $filter = (int)$filter;

echo '<select id="sF" onchange="refilter($(this).val());">';
foreach($status as $k=>$v){
	printf('<option value="%s" %s>%s</option>',
		$k,($k===$filter?'selected':''),$v);
}
echo '</select><br /><br />';
printf('<input type="hidden" id="orderSetting" value="%s" />',$order);

$where = "p.trans_type = 'I'";
if (!empty($filter)){
	$where .= " AND s.status_flag=".((int)$filter);
}

$q = "SELECT upc,description,ItemQtty,mixMatch
	FROM PendingSpecialOrder AS p
	LEFT JOIN SpecialOrderStatus as s
	ON p.order_id=s.order_id
	WHERE $where
	ORDER BY $order";
if ($order != "upc") $q .= ",upc";
$r = $dbc->query($q);
echo '<table cellspacing="0" cellpadding="4" border="1"><tr>';
echo '<th><a href="" onclick="resort(\'upc\');return false;">UPC</a></td>';
echo '<th><a href="" onclick="resort(\'description\');return false;">Desc.</a></td>';
echo '<th><a href="" onclick="resort(\'ItemQtty\');return false;"># Cases</a></td>';
echo '<th><a href="" onclick="resort(\'mixMatch\');return false;">Supplier</a></td>';
echo '</tr>';
while ($w = $dbc->fetch_row($r)){
	printf('<tr><td>%s</td><td>%s</td><td>%d</td><td>%s</td></tr>',
		$w['upc'],$w['description'],$w['ItemQtty'],$w['mixMatch']);
}
echo '</table>';

include($FANNIE_ROOT.'src/footer.html');
?>
