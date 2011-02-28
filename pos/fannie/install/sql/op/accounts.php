<?php
/*
Table: accounts 

Columns:
  id int
  name varchar
  balance double

Use:
This is one of "primary" tables dealing with membership
(the others are custdata and  meminfo). Of them, custdata and 
accounts are present at the checkout. Column meaning may not be 
quite identical across stores.

[Probably] The Same Everywhere:
* id is an identifying integeger
* CardNo associates with custdata.CardNo
* name is the account name, used by Mariposa for decades as the 
real identifier for accounts
* balance is the account balance
* discount is the account discount, an average between all the members' discounts
* max_balance is the maximum account balance

All of the above info, except Cardo, should match MESS at all times.
*/
$CREATE['op.accounts'] = "
	CREATE TABLE `accounts` (
	  `id` int(8) NOT NULL,
          `CardNo` int(8) NOT NULL,
          `name` varchar(255) NOT NULL,
	  `balance` double NOT NULL default '0',
	  `discount` smallint(6) default NULL,
          `max_balance` double NOT NULL default '0'
	  PRIMARY KEY  (`name`),
	  KEY `CardNo` (`id`),
	)
";

?>
