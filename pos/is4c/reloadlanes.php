<?

/* modified to only sync data on local server 
   --kclair 02/11/2011
*/

$tables = array('products', 'custdata', 'employees', 'departments', 'tenders');

foreach ($tables as $t) {
  synctable($t);
}

function synctable($table) {
    $table = strtolower($table);

    $server = "192.168.1.109";
    $serveruser = "root";
    $serverpass = "is4c";

    $laneserver = "localhost";
    $laneuser = "root";
    $lanepass = "is4c";

    $outfile = "/home/k/IS4C/pos/is4c/download/" . $table . ".sql";
    $mysqldump = "mysqldump -u $serveruser --password=$serverpass -h $server ";
    $mysqldump .= "--add-drop-table --complete-insert --create-options is4c_op $table ";
    $mysqldump .= "> $outfile";

    $exec_commands = array($mysqldump, 
      "mysql -u $laneuser --password=$lanepass -h $laneserver is4c_op < $outfile"
    );

    $opdata_commands = array(
      "CREATE TABLE IF NOT EXISTS ".$table."_bak LIKE ".$table,
      "truncate table ".$table."_bak",
      "insert into " . $table . "_bak select * from " . $table,
      "replace into " . $table . " select * from is4c_op." . $table
    );

    if (file_exists($outfile)) {
        exec("rm ".$outfile);
    }

    foreach ($exec_commands as $ecom) {
      $out = system($ecom);
      if ($out) { error_and_die($ecom, $out); }
    }

    if (file_exists($outfile)) {
        $lane_conn = mysql_connect($laneserver, $laneuser, $lanepass) or die ("Failed to connect to "  .$laneserver);
        mysql_select_db("opdata", $lane_conn) or error_and_die ("select database opdata", mysql_error());
        foreach ($opdata_commands as $ocom) {
          mysql_query($ocom, $lane_conn) or error_and_die ($ocom, mysql_error());
        }
    } 
    else {
        echo "<p>Outfile from server not found</p>";
    }
}

function error_and_die($com, $error) {
  die('Failed to execute: '.$com.' : '.$error);
}

