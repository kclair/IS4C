require 'mysql'
#require 'rubygems'
#require 'fastercsv'
require 'yaml'

file = File.new('./unfi.txt', 'r')
unfi_cats = YAML.load_file('./unfi_categories.yml')
desccol = 2
packcol = 3
sizecol = 4
upccol = 5
costcol = 7
srpcol = 10
catcol = 6

begin
  dbh = Mysql.real_connect("localhost", "is4clane", "is4clane", "opdata")
  dbh.query("TRUNCATE table products")
rescue Mysql::Error => e
  puts "Error code: #{e.errno}"
  puts "Error message: #{e.error}"
  puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
  die
end

while (line = file.gets)
  row = line.split("\t")
  desc = row[desccol]
  desc.gsub!(/"/, '\"')
  pack = row[packcol]
  size = row[sizecol]
  upc = row[upccol]
  cost = row[costcol]
  srp = row[srpcol].sub!(/^0+/, '').sub!(/(\d\d)$/, '.\1') || 0
  cat = row[catcol]
  dept = unfi_cats[cat]['dept']
  subdept = unfi_cats[cat]['subdept'] || ''
  fs = unfi_cats[cat]['nofs'] ? 0 : 1
  begin
    realcost = cost.to_f / pack.to_f
    dbh ||= Mysql.real_connect("localhost", "is4clane", "is4clane", "opdata")
    query = "INSERT INTO products (upc, description, normal_price, size, department, subdept, foodstamp) values (#{upc}, \"#{desc}\", #{srp}, '#{size}', #{dept}, #{subdept}, #{fs})"
    dbh.query(query)   
  rescue Mysql::Error => e
     puts "Query: #{query}"
     puts "Error code: #{e.errno}"
     puts "Error message: #{e.error}"
     puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
   end
end
