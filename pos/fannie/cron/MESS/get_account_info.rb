require 'rubygems'
require 'mysql'
require 'json'
require "net/https"
require "uri"

secrets = File.open('./.secrets', 'r').readlines 
url = secrets[0].chomp
secret = secrets[1].chomp

uri = URI.parse(url)
http = Net::HTTP.new(uri.host, uri.port)
http.use_ssl = true
http.verify_mode = OpenSSL::SSL::VERIFY_NONE

request = Net::HTTP::Get.new("/is4c/accounts/?secret=#{secret}")
puts request.inspect
response = http.request(request)

puts response.body

begin
  dbh = Mysql.real_connect("localhost", mysqluser, mysqlpass, transdb)
  #dbh.query("REPLACE INTO accounts ...")
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
