require 'rubygems'
require 'mysql'
require 'json'
require "net/https"
require "uri"

# need to get these from other files:
mysqluser = ''
mysqlpass = ''
transdb = ''
url = ''
secret = ''

last_fail = nil
fail_reason = ''
loop do
  if last_fail != nil
    sleep_until = Time.now - last_fail
    if sleep_until > 86400 # if it's more than 24 hours, die
      die
    else
      sleep sleep_until
    end
  end
  begin
    dbh = Mysql.real_connect("localhost", mysqluser, mysqlpass, transdb)
    res = dbh.query("SELECT 
    dtransactions.register_no as register_no, dtransactions.emp_no as emp_no, dtransactions.trans_no as trans_no, dtransactions.trans_id as trans_id, dtransactions.upc as upc, dtransactions.description as description, dtransactions.trans_type as trans_type, dtransactions.trans_subtype as trans_subtype, dtransactions.department as department, dtransactions.quantity as quantity, dtransactions.cost as cost, dtransactions.total as total, accounts.id as account_id 
    from is4c_trans.dtransactions dtransactions LEFT JOIN is4c_op.accounts accounts ON (accounts.CardNo = dtransactions.card_no)
    where mess_ok !=1")
    http = Net::HTTP.new(url)
    http.use_ssl = true
    http.verify_mode = OpenSSL::SSL::VERIFY_NONE
    request = Net::HTTP::Post.new("/transactions/?secret=#{secret}")
    while row = res.fetch_hash do 
      puts row.inspect
      #puts JSON.generate(row)
      request.set_form_data({"transaction" => JSON.generate(row)})
      response = http.request(request)
      if response.code == 200
        dbh.query("UPDATE dtransactions set mess_ok=1 where trans_id="+row['trans_id'])
      else
        raise 'UPDATE to MESS failed: '+response.code
      end    
    end
  rescue Mysql::Error => e
     puts "Error code: #{e.errno}"
     puts "Error message: #{e.error}"
     puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
  rescue
     last_fail = Time.now
     fail_reason = $!
  ensure
     # disconnect from server
     dbh.close if dbh
  end
end
