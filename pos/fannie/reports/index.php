<?php
/*******************************************************************************

    Copyright 2010 Whole Foods Co-op

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

include('../../src/mysql_connect.php');
include('../../src/select_dlog.php');

if (isset($_REQUEST['batchID'])){
	$batchID = "(".$_REQUEST['batchID'].")";
	if (is_array($_REQUEST['batchID'])){
		$batchID = "(";
		foreach($_REQUEST['batchID'] as $bid)
			$batchID .= $bid.",";
		$batchID = rtrim($batchID,",").")";	
	}

	$batchInfoQ = "SELECT batchName,
			year(startDate) as sy, month(startDate) as sm, day(startDate) as sd,
			year(endDate) as ey, month(endDate) as em, day(endDate) as ed,
			FROM batches where batchID IN $batchID";
	$batchInfoR = $dbc->query($batchInfoQ);

	if(isset($_GET['excel'])){
	   header('Content-Type: application/ms-excel');
	   header('Content-Disposition: attachment; filename="batchSales.xls"');
	}
	$bStart = isset($_REQUEST['start'])?$_REQUEST['start']:'';
	$bEnd = isset($_REQUEST['end'])?$_REQUEST['end']:'';
	$bName = "";
	while($batchInfoW = $dbc->fetch_array($batchInfoR)){
		$bName .= $batchInfoW['batchName']." ";
		if (empty($bStart)) {
			$bStart = sprintf("%d-%02d-%02d",$batchInfoW['sy'],
				$batchInfoW['sm'],$batchInfoW['sd']);
		}
		if (empty($bEnd)){ 
			$bEnd = sprintf("%d-%02d-%02d",$batchInfoW['ey'],
				$batchInfoW['em'],$batchInfoW['ed']);
		}
	}

	echo "<h2>$bName</h2>";
	echo "<p><font color=black>From: </font> $bStart <font color=black>to: </font> $bEnd</p>";

	$dlog = select_dlog($bnStart,$bnEnd);

	if(!isset($_GET['excel'])){
	   echo "<p class=excel><a href=batchReport.php?batchID=$batchID&excel=1&startDate=$bnStart&endDate=$bnEnd>Click here for Excel version</a></p>";
	}

	$salesBatchQ ="select d.upc, b.description, sum(d.total) as sales, 
		 sum(case when d.trans_status in ('M','V') then d.itemQtty else d.quantity end) as quantity
		 FROM $dlog as d left join batchMergeTable as b
		 ON d.upc = b.upc
		 WHERE d.tdate BETWEEN '$bStart' and '$bEnd' 
		 AND b.batchID IN $batchID 
		 AND d.trans_status <> 'M'
		 GROUP BY d.upc, b.description
		 ORDER BY d.upc";

	$salesBatchR= $dbc->query($salesBatchQ);

	$i = 0;

	echo "<table border=0 cellpadding=1 cellspacing=0 ><th>UPC<th>Description<th>$ Sales<th>Quantity";
	while($salesBatchW = $dbc->fetch_array($salesBatchR)){
		$upc = $salesBatchW['upc'];
		$desc = $salesBatchW['description'];
		$sales = $salesBatchW['sales'];
		$qty = $salesBatchW['quantity'];
		$imod = $i%2;
   
		if($imod==1){
			$rColor= '#ffffff';
		}else{
			$rColor= '#ffffcc';
		}

		echo "<tr bgcolor=$rColor><td width=120>$upc</td><td width=300>$desc</td><td width=50>$sales</td><td width=50 align=right>$qty</td></tr>";
		$i++;
	}
	echo "</table>";
}
else {
	include("../../src/header.html");

	include("../../src/footer.html");
}

?>
