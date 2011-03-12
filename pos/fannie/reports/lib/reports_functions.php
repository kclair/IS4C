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

function get_departments($dbc=null) {
  # connect here to is4c_op unless $dbc is already defined
  $deptR = $dbc->query("SELECT dept_no, dept_name FROM departments ORDER BY dept_name");
  $depts = array();
  while($row = $dbc->fetch_row($deptR)){
    $depts[$row[0]] = $row[1];
  }
  return $depts;
}

function get_subdepartments_for($dept_no, $dbc=null) {
  # connect here to is4c_op unless $dbc is already defined
  $subR = $dbc->query("SELECT subdept_no, subdept_name FROM subdepts WHERE dept_no=$dept_no ORDER BY subdept_name");
  $subdepts = array();
  while ($row = $dbc->fetch_row($subR)) {
    $subdepts[$row[0]] = $row[1];
  }
  return $subdepts;
}

// column can be department, category, or product
// range can be week, month, quarter, year
function get_margin_report_for($column_key, $column_val, $start, $end, $dbc)
  # connect here to is4c_op unless $dbc is already defined
  $query = "SELECT sum(cost), sum(total) from dtransactions 
    where $column_key='$column_val' AND datetime BETWEEN ($start, $end) AND trans_type='I'";
  $result = $dbc->query($query);
  $row = $dbc->fetch_row($result);
  $margin = ($row[1] - $row[0]) / $row[1];
  return array($row[0], $row[1], $margin);
}

function SalesFromDay($date=null, $name=null) {
  $date = $date ? $date : today;
  $whereplus = $name ? " AND t.dept_name=='$name' " : '';
  $query1="SELECT ".$dbc->hour('tdate').", 
    sum(total)as Sales, t.dept_name  
    FROM dlog as d left join departments as t
    on d.department = t.dept_no
    WHERE datediff(dd,getdate(),tDate)=0
    AND (trans_type ='I' OR Trans_type = 'D' or trans_type='M')
    AND t.dept_no > 0".$whereplus."
    GROUP BY ".$dbc->hour('tdate')."
    order by ".$dbc->hour('tdate');

  $result = $dbc->query($query1);
  $sum = 0;
  $sum2 = 0;
  $sales = array();
  while($row=$dbc->fetch_row($result)){
    $sales[] = $row;
  }
  return $sales;
}

