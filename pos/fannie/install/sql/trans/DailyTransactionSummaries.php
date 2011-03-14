<?php
/*
Table: DailyTransactionSummaries 

Columns:
        id int
	start_time datetime
	end_time datetime
	member_equity_start float
	member_equity_end float
	account_balances_start float
	account_balances_end float
	cash_balance_start float
	cash_balance_end float
	sales_dept_data text
	misc_transactions_total float
	cc_payments float
	ck_payments float
	ebt_payments float
	ca_payments float
	pp_payments float

Use:
This table stores daily transaction summaries.
The current day is the entry where end_time is null.

*/
$CREATE['trans.DailyTransactionSummaries] = "
	CREATE TABLE DailyTransactionSummaries (
        id int(11) NOT NULL auto_increment,
        start_time datetime NOT NULL,
        end_time datetime,
        member_equity_start decimal(),
        member_equity_end decimal(),
        account_balances_start decimal(),
        account_balances_end decimal(),
        cash_balance_start decimal(),
        cash_balance_end decimal(),
        sales_dept_data text(),
        misc_transactions_total decimal(),
        cc_payments decimal(),
        ck_payments decimal(),
        ebt_payments decimal(),
        ca_payments decimal(),
        pp_payments decimal()
	)
";
?>
