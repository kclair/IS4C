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

if ($dbms == "MSSQL"){
	$CREATE['op.custdata'] = "
		CREATE TABLE [custdata] (
			[CardNo] [varchar] (25) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
			[personNum] [varchar] (3) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
			[LastName] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
			[FirstName] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
			[CashBack] [money] NULL ,
			[Balance] [money] NULL ,
			[Discount] [smallint] NULL ,
			[MemDiscountLimit] [money] NULL ,
			[ChargeOk] [bit] NULL ,
			[WriteChecks] [bit] NULL ,
			[StoreCoupons] [bit] NULL ,
			[Type] [varchar] (10) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
			[memType] [smallint] NULL ,
			[staff] [tinyint] NULL ,
			[SSI] [tinyint] NULL ,
			[Purchases] [money] NULL ,
			[NumberOfChecks] [smallint] NULL ,
			[memCoupons] [int] NULL ,
			[blueLine] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
			[Shown] [tinyint] NULL ,
			[id] [int] IDENTITY (1, 1) NOT NULL 
		) ON [PRIMARY]
	";
}

?>
