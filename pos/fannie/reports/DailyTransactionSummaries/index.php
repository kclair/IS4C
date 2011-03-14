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

// are we posting or getting?

// post: are we making a new report, or getting a past report?
if (isset($_POST['id'])) {
  $id = $_POST['id'];
}else if (isset($_POST['newday'])) {

}else {
  // display today's
  $id = get_todays_transaction_report_id();
}
$report = get_transaction_report($id);
include('display_report.php');

// make the form
$available_reports = get_available_transaction_summaries();
include('reports_form.php');

$page_title = "Fannie : Today's $name Sales";
$header = "Today's $name Sales";
include($FANNIE_ROOT.'src/header.html');

include($FANNIE_ROOT.'src/footer.html');
?>
