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

$db_log = new SQLManager($FANNIE_SERVER,$FANNIE_SERVER_DBMS,$FANNIE_LOG_DB,
                $FANNIE_SERVER_USER,$FANNIE_SERVER_PW);

// are we posting or getting?

// post: are we making a new report, or getting a past report?
if (isset($_POST['submit'])) {
  $start
}else {
}

$page_title = "Fannie : Sales - COGS - Margins";
$header = "Sales - COGS - Margins";
include($FANNIE_ROOT.'src/header.html');
include('display_report.php');
include($FANNIE_ROOT.'src/footer.html');
?>
