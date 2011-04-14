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

ini_set('display_errors','1');

if (!class_exists("NoInputPage")) include_once($IS4C_PATH."gui-class-lib/NoInputPage.php");
if (!function_exists("pDataConnect")) include($IS4C_PATH."lib/connect.php");
if (!function_exists("setMember")) include($IS4C_PATH."lib/prehkeys.php");
if (!function_exists("printfooter")) include($IS4C_PATH."lib/drawscreen.php");
if (!isset($IS4C_LOCAL)) include($IS4C_PATH."lib/LocalStorage/conf.php");

class memlist extends NoInputPage {

	var $temp_result;
	var $temp_num_rows;
	var $entered;
	var $db;

	function preprocess(){
		global $IS4C_LOCAL,$IS4C_PATH;
		$IS4C_LOCAL->set("away",1);
		$entered = "";
		if ($IS4C_LOCAL->get("idSearch") && strlen($IS4C_LOCAL->get("idSearch")) > 0) {
			$entered = $IS4C_LOCAL->get("idSearch");
			$IS4C_LOCAL->set("idSearch","");
		}
		elseif (isset($_REQUEST['search'])){
			$entered = strtoupper(trim($_REQUEST["search"]));
			$entered = str_replace("'", "''", $entered);
		}
		else return True;

		if (substr($entered, -2) == "ID") $entered = substr($entered, 0, strlen($entered) - 2);

		$personNum = 1;
		$selected_name = False;

		// No input available, stop
		if (!$entered || strlen($entered) < 1 || $entered == "CL") {
			$IS4C_LOCAL->set("mirequested",0);
			$IS4C_LOCAL->set("scan","scan");
			$IS4C_LOCAL->set("reprintNameLookup",0);
			header("Location: {$IS4C_PATH}gui-modules/pos2.php");
			return False;
		}

		$memberID = $entered;
		$db_a = pDataConnect();

		if (isset($_REQUEST['search'])) {
                	$query = "select CardNo, name, balance as Balance, discount as Discount from accounts 
                          where name = '".$entered."'";
		}else {
                        $query = "select CardNo, name, balance as Balance, discount as Discount from accounts 
                          where name LIKE '".$entered."%' order by name";
		}

		$result = $db_a->query($query);
		$num_rows = $db_a->num_rows($result);

		// if there's one result and either
		// then set the member number
		if ($num_rows == 1) {
                        //once we have one result, do the real query
			$row = $db_a->fetch_array($result);

		        $sync_account_out = array();
		        $res = '';
			$exec = $IS4C_PATH."/exec/get_account_info.rb ".$row["CardNo"];
		        exec($exec, &$sync_account_out, &$res);
                $query = "select custdata.CardNo,custdata.personNum,custdata.LastName,custdata.FirstName,custdata.CashBack,
                accounts.balance as Balance,accounts.max_balance, accounts.discount as Discount, accounts.name, accounts.account_flags, 
                custdata.MemDiscountLimit,custdata.ChargeOk,custdata.WriteChecks,custdata.StoreCoupons,custdata.Type,custdata.memType,custdata.staff,
                custdata.SSI,custdata.Purchases,custdata.NumberOfChecks,custdata.memCoupons,custdata.blueLine,custdata.Shown,custdata.id 
                from custdata, accounts
                where custdata.CardNo = accounts.CardNo and custdata.CardNo = '".$row["CardNo"]."'";
                        $result = $db_a->query($query);
			$row = $db_a->fetch_array($result);
			setMember($row["CardNo"], $personNum,$row,$res);
			$IS4C_LOCAL->set("scan","scan");
			header("Location: {$IS4C_PATH}gui-modules/pos2.php");
			return False;
		}

		$this->temp_result = $result;
		$this->temp_num_rows = $num_rows;
		$this->entered = $entered;
		$this->db = $db_a;
		return True;
	} // END preprocess() FUNCTION

	function head_content(){
		global $IS4C_LOCAL;
		$this->add_onload_command("\$('#search').focus();\n");
		if ($this->temp_num_rows > 0)
			$this->add_onload_command("\$('#search').keypress(processkeypress);\n");
		?>
		<script type="text/javascript">
		var prevKey = -1;
		var prevPrevKey = -1;
		function processkeypress(e) {
			var jsKey;
			if (e.keyCode) // IE
				jsKey = e.keyCode;
			else if(e.which) // Netscape/Firefox/Opera
				jsKey = e.which;
			if (jsKey==13) {
				if ( (prevPrevKey == 99 || prevPrevKey == 67) &&
				(prevKey == 108 || prevKey == 76) ){ //CL<enter>
					$('#search option:selected').each(function(){
						$(this).val('');
					});
				}
				$('#selectform').submit();
			}
			prevPrevKey = prevKey;
			prevKey = jsKey;
		}
		</script> 
		<?php
	} // END head() FUNCTION

	function body_content(){
		global $IS4C_LOCAL;
		$num_rows = $this->temp_num_rows;
		$result = $this->temp_result;
		$entered = $this->entered;
		$db = $this->db;

		echo "<div class=\"baseHeight\">"
			."<form id=\"selectform\" method=\"post\" action=\"{$_SERVER['PHP_SELF']}\">";

		/* for no results, just throw up a re-do
		 * otherwise, put results in a select box
		 */
?>
		<div class="colored centeredDisplay">
			<span class="larger">
			Enter Account name:
			</span>
			<input type="text" id="account-name" name="account-name" class="autocomplete" autocomplete="off" /> 
			<br /><br />
			<a href='/gui-modules/pos2.php' style='color: white;'>click here to cancel</a>
		</div>
		<script type="text/javascript">
		<!--
		$(function()  {
		  $('input[name=account-name]').autoComplete({
			ajax: '/ajax-callbacks/ajax-autocomplete.php', 
			onSelect: function(event, ui) { window.location = '/gui-modules/memlist.php?search='+ui.data.value; } });
		  $('input[name=account-name]').focus();
		});
		-->
		</script>

<?php
/*

			echo "<div class=\"listbox\">"
				."<select name=\"search\" size=\"15\" "
				."onblur=\"\$('#search').focus()\" id=\"search\">";

			$selectFlag = 0;
                        /* don't  know what this is --kclair
			if (!is_numeric($entered) && $IS4C_LOCAL->get("memlistNonMember") == 1) {
				echo "<option value='3::1' selected> 3 "
					."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Customer";
				$selectFlag = 1;
			}

			for ($i = 0; $i < $num_rows; $i++) {
				$row = $db->fetch_array($result);
				if( $i == 0 && $selectFlag == 0) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				echo "<option value='".$row["name"]."' ".$selected.">"
					.$row["name"]."</option>\n";
			}
			echo "</select></div>"
				."<div class=\"listboxText centerOffset\">"
				."use arrow keys to navigate<p>[clear] to cancel</div>"
				."<div class=\"clear\"></div>";
		}
		echo "</form></div>";
                        */
	} // END body_content() FUNCTION
}

new memlist();

?>
