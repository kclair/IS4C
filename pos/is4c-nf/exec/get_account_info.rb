#!/usr/bin/ruby

require 'rubygems'
require 'mysql'
require 'json'
require "net/https"
require "uri"


exit 0 unless ARGV[0] =~ /^\d+$/

outfile = File.open(File.expand_path(File.dirname(__FILE__)) + '/out', 'a')
path = '/is4c/account/'+ARGV[0]+'/'

secrets = File.open(File.expand_path(File.dirname(__FILE__)) + '/.secrets', 'r').readlines
url = secrets[0].chomp
secret = secrets[1].chomp
mysqluser = secrets[2].chomp
mysqlpass = secrets[3].chomp
opdb = secrets[4].chomp

uri = URI.parse(url)
http = Net::HTTP.new(uri.host, uri.port)
http.use_ssl = true
http.verify_mode = OpenSSL::SSL::VERIFY_NONE
http.read_timeout = 10 

request = Net::HTTP::Get.new("#{path}?secret=#{secret}")
response = http.request(request)

begin
  dbh = Mysql.real_connect("localhost",  mysqluser, mysqlpass, opdb)
  a = JSON.parse(response.body)
  sth = dbh.prepare("REPLACE INTO accounts (CardNo, name, max_balance, balance, account_flags, account_flags_html) VALUES(?, ?, ?, ?, ?, ?)")
  sth.execute(a['id'], a['name'], a['balance_limit'], a['balance'], a['json_flags'].to_json, a['html_flags'])
  outfile.puts 'successfully imported '+a['name']
rescue Mysql::Error => e
   puts "Error code: #{e.errno}"
   puts "Error message: #{e.error}"
   puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
   exit 0
ensure
   # disconnect from server
   dbh.close if dbh
end
outfile.close

exit 1
