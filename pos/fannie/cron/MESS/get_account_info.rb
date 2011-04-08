require 'rubygems'
require 'mysql'
require 'json'
require "net/https"
require "uri"

path = ARGV[0] ? '/is4c/account/'+ARGV[0] : '/is4c/accounts/'

secrets = File.open('./.secrets', 'r').readlines 
url = secrets[0].chomp
secret = secrets[1].chomp
mysqluser = secrets[2].chomp
mysqlpass = secrets[3].chomp
opdb = secrets[4].chomp

uri = URI.parse(url)
http = Net::HTTP.new(uri.host, uri.port)
http.use_ssl = true
http.verify_mode = OpenSSL::SSL::VERIFY_NONE
http.read_timeout = 2000

request = Net::HTTP::Get.new("#{path}?secret=#{secret}")
puts request.inspect
response = http.request(request)

begin
  dbh = Mysql.real_connect("localhost", mysqluser, mysqlpass, opdb)
  puts 'connected to mysql.'
  JSON.parse(response.body).each do |a|
    puts 'looking at '+a.inspect
    sth = dbh.prepare("REPLACE INTO accounts (CardNo, name, max_balance, balance, account_flags, account_flags_html) VALUES(?, ?, ?, ?, ?, ?)")
    sth.execute(a['id'], a['name'], a['balance_limit'], a['balance'], a['json_flags'].to_json, a['html_flags'])
    puts 'imported account '+a['name']
  end
rescue Mysql::Error => e
   puts "Error code: #{e.errno}"
   puts "Error message: #{e.error}"
   puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
ensure
   # disconnect from server
   dbh.close if dbh
end
