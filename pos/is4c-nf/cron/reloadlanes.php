<?

/* modified to only sync data on local server 
   --kclair 02/11/2011
*/

//$tables = array('products', 'custdata', 'employees', 'departments', 'tenders');
// i think we really only need custdata and accounts synced automatically
$tables = array('custdata', 'accounts');

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

    $exec_commands = array(
      "rm -f $outfile",
      $mysqldump, 
      "mysql -u $laneuser --password=$lanepass -h $laneserver is4c_op < $outfile"
    );

    $opdata_commands = array(
      "CREATE TABLE IF NOT EXISTS ".$table."_bak LIKE ".$table,
      "truncate table ".$table."_bak",
      "insert into " . $table . "_bak select * from " . $table,
      "replace into " . $table . " select * from is4c_op." . $table
    );

    foreach ($exec_commands as $ecom) {
      $out = system("$ecom");
    }

    if (filesize($outfile) > 0) {
        $lane_conn = mysql_connect($laneserver, $laneuser, $lanepass) or error_and_die("connect to $laneserver", mysql_error());
        mysql_select_db("opdata", $lane_conn) or error_and_die ("select database opdata", mysql_error());
        foreach ($opdata_commands as $ocom) {
          mysql_query($ocom, $lane_conn) or error_and_die ($ocom, mysql_error());
        }
    } 
    else {
        die("outfile ($outfile) is empty.\n");
    }
}

function error_and_die($com, $error) {
  $errstr = "failed to execute '$com': $error\n";
  die($errstr);
}

