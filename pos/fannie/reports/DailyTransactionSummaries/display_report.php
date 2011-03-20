<?php

// results go here

?>

<div id='showform'>
  <form action='index.php' method='POST'>
    Store Day: <select name='id'>
        <option value=''>Current Day</option>
        <?php foreach ($available_reports as $r) {
          echo("<option value='$r['id']'>$r['start']</option>");
        }?>
      </select>
    <br>
    Start: <input type='text' id='starttime' name='starttime' 
            value='<?php echo($today_start); ?>'>
    <br>
    End: <input type='text' id='endtime' name='endtime'>
    <br>
    <input type='submit' value='Show Report'>
    <br>
    <input type='submit' value'Begin new Store Day now'>
  </form>
</div>

<?php
?>
