require 'rubygems'
require 'mysql'
#require 'fastercsv'
require 'yaml'

all_products_file = File.new('./unfi.txt', 'r')
desccol = 2
packcol = 3
sizecol = 4
upccol = 5
costcol = 7
srpcol = 10
brandcol = 1

products = {}
while (line = all_products_file.gets)
  row = line.split("\t")
  id = row[0]
  products[id] = {} # make hash with product ids as the keys
  desc = row[desccol]
  if (!desc)
    puts 'no description found for row '+row.inspect
    exit
  end
  desc = desc.gsub(/"/, '\"') if desc
  products[id]['desc'] = desc
  products[id]['pack'] = row[packcol]
  products[id]['size'] = row[sizecol]
  products[id]['upc'] = row[upccol]
  products[id]['cost'] = row[costcol]
  brand = row[brandcol]
  brand = brand.gsub(/"/, '\"') if brand
  products[id]['brand'] = brand 
  if !products[id]['cost']
    puts 'no cost found for row '+row.inspect
    exit
  end
  products[id]['cost'] = products[id]['cost'].sub(/^0+/, '').sub!(/(\d\d)$/, '.\1') || 0
  products[id]['srp'] = row[srpcol].sub!(/^0+/, '').sub!(/(\d\d)$/, '.\1') || 0
end
all_products_file.close

begin
  dbh = Mysql.real_connect("127.0.0.1", "root", "", "is4c_op")
  dbh.query("TRUNCATE table products")
  dbh.query("TRUNCATE table prodExtra")
rescue Mysql::Error => e
  puts "Error code: #{e.errno}"
  puts "Error message: #{e.error}"
  puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
  die
end

unfi_cats = YAML.load_file('./unfi_categories.yml')
mfc_products_file = File.new('./mariposa_products.txt','r')
mfc_cat = 1
mfc_prodid = 3
mfc_chkdgt = 4

notfound = 0
while (line = mfc_products_file.gets)
  row = line.split("\t")
  desc = row[desccol]
  desc.gsub!(/"/, '\"')
  pack = row[packcol]
  size = row[sizecol]
  upc = row[upccol]
  cost = row[costcol]
  srp = row[srpcol].sub!(/^0+/, '').sub!(/(\d\d)$/, '.\1') || 0
  cat = row[mfc_cat].to_i
  if unfi_cats.include?(cat)
    dept = unfi_cats[cat]['dept'] || 0
    subdept = unfi_cats[cat]['subdept'] || 0 
    fs = unfi_cats[cat]['nofs'] ? 0 : 1
  else
    puts 'no category for '+cat.to_s+' for row '+row.inspect
    exit
    dept = 0
    subdept = 0
    fs = 1
  end
  id = row[mfc_prodid].to_s + row[mfc_chkdgt].to_s
  if id.nil? or !products[id]
    puts 'id or products[id] was nil for '+id
    notfound += 1
    next
  end
  upc = products[id]['upc']
  desc = products[id]['desc']
  srp = products[id]['srp']
  size = products[id]['size']
  cost = products[id]['cost']
  pack = products[id]['pack']
  brand = products[id]['brand']
  begin
    realcost = cost.to_f / pack.to_f
    dbh ||= Mysql.real_connect("localhost", "is4clane", "is4clane", "opdata")
    query = "INSERT INTO products (upc, description, normal_price, size, department, subdept, foodstamp, cost, discount, inUse) values (#{upc}, \"#{desc}\", #{srp}, '#{size}', #{dept}, #{subdept}, #{fs}, #{realcost}, 1, 1)"
    dbh.query(query)   
    query = "INSERT INTO prodExtra (upc, distributor, manufacturer, cost, case_cost, case_quantity) values (#{upc}, 'UNFI', \"#{brand}\", #{realcost}, #{cost}, #{pack})"
    dbh.query(query)   
  rescue Mysql::Error => e
     puts "Query: #{query}"
     puts "Error code: #{e.errno}"
     puts "Error message: #{e.error}"
     puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
   end
end
puts notfound.to_s+' products not found.'
