require 'rubygems'
require 'mysql'
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

deals_file = File.new('./ogdeals.txt', 'r')
deal_ogdtypecol = 2
deal_brandcol = 3
deal_prodidcol = 5
deal_typecol = 14
deal_amountcol = 15
deals = {}
while (line = deals_file.gets)
  row = line.split("\t")
  ogdtype = row[deal_ogdtypecol]
  brand = row[deal_brandcol]
  prod_id = row[deal_prodidcol]
  identifier = (ogdtype == 1) ? brand : prod_id  # a type of 1 indicates a brand deal
  deals[identifier] ||= {}
  deals[identifier]['type'] = (row[deal_typecol] == 1) ? '$' : '%'
  deals[identifier]['amount'] = row[deal_amountcol]
end
deals_file.close

begin
  dbh = Mysql.real_connect("localhost", "root", "", "is4c_op")
#  dbh.query("TRUNCATE table products")
#  dbh.query("TRUNCATE table prodExtra")
rescue Mysql::Error => e
  puts "Error code: #{e.errno}"
  puts "Error message: #{e.error}"
  puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
  exit
end

unfi_cats = YAML.load_file('./unfi_categories.yml')
mfc_products_file = File.new('./mariposa_products.txt','r')
notfound = 0
while (line = mfc_products_file.gets)
  row = line.split("\t")
  desc = row[desccol]
  desc.gsub!(/"/, '\"')
  srp = row[srpcol].sub!(/^0+/, '').sub!(/(\d\d)$/, '.\1') || 0
  cat = row[6].to_i
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
  id = row[0]
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
  scale = (dept == 4) ? 1 : 0
  realcost = cost.to_f / pack.to_f
  # get special price if there are any deals by brand or product
  deal = deals[brand] || deals[id]
  if deal and deal['type'] == '%'
    special_price = srp * (deal['amount'] / 100)
  elsif deal and deal['type'] == '$'
    special_price = srp - deal['amount']
  else
    special_price = 'NULL' 
  end
  begin
    # see if product exists
    dbh ||= Mysql.real_connect("localhost", "is4clane", "is4clane", "opdata")
    query = "SELECT id from products WHERE upc=#{upc}" #dist_id=#{id}"
    res = dbh.query(query)
    if res and (row= res.fetch_row)
      existing_id = row[0]
    else
      existing_id = nil
    end
    if existing_id
      # don't overwrite most values if replacing a product
      query = "REPLACE INTO products (description, normal_price, special_price, size, cost, id) values (\"#{desc}\", #{srp}, #{special_price}, '#{size}', #{realcost}, #{existing_id})"  
      query2 = "REPLACE INTO prodExtra (upc, distributor, manufacturer, cost, case_cost, case_quantity, dist_id, products_id) values (#{upc}, 'UNFI', \"#{brand}\", #{realcost}, #{cost}, #{pack}, #{id}, #{existing_id})"
    else 
      next
      query = "INSERT INTO products (upc, description, normal_price, special_price, size, department, subdept, foodstamp, cost, discount, inUse, scale) values (#{upc}, \"#{desc}\", #{srp}, #{special_price}, '#{size}', #{dept}, #{subdept}, #{fs}, #{realcost}, 1, 1, #{scale})"
      query2 = "INSERT INTO prodExtra (upc, distributor, manufacturer, cost, case_cost, case_quantity, dist_id) values (#{upc}, 'UNFI', \"#{brand}\", #{realcost}, #{cost}, #{pack}, #{id})"
    end
    dbh.query(query)   
    dbh.query(query2)   
  rescue Mysql::Error => e
     puts "Query: #{query}"
     puts "Error code: #{e.errno}"
     puts "Error message: #{e.error}"
     puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
   end
end
puts notfound.to_s+' products not found.'
