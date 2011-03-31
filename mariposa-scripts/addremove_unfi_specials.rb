require 'rubygems'
require 'mysql'
require 'yaml'

action = ARGV[0]
file = ARGV[1]

unless action and action =~ /^add|rem$/
  puts 'first argument must be "add" or "rem"'
  exit
end

unless file and File.exists?(file)
  puts "file #{file} does not exist"
  exit
end

begin
  dbh = Mysql.real_connect("localhost", "root", "", "is4c_op")
rescue Mysql::Error => e
  puts "Error code: #{e.errno}"
  puts "Error message: #{e.error}"
  puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
  exit
end

deals_file = File.new(file, 'r')
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
  brand = brand.gsub(/"/, '\"') if brand
  prod_id = row[deal_prodidcol]
  identifier = (ogdtype == 1) ? brand : prod_id  # a type of 1 indicates a brand deal
  if identifier == 'prod_id'
    res = dbh.query("SELECT products.* from products, prodExtra where products.id=prodExtra.products_id and prodExtra.dist_id=#{prod_id}")
  else #brand
    res = dbh.query("SELECT products.* from products, prodExtra where products.id=prodExtra.products_id and prodExtra.manufacturer=\"#{brand}\"")
  end
  while sqlrow  = res.fetch_hash do
    if action == 'rem'
      special_price='NULL'
    else
      price = sqlrow['normal_price'].to_f
      if row[deal_typecol] == 1 # dollar amount deal
        special_price = price - row[deal_amountcol].to_f
      else # percent deal
        special_price = price - (price * (row[deal_amountcol].to_f / 100) )
      end
    end
    special_price = (special_price * 100).round() / 100.0
    puts 'updating product '+sqlrow['upc']+' from '+price.to_s+' to '+special_price.to_s+' ('+row[deal_amountcol]+')'
    dbh.query("UPDATE products set special_price=#{special_price} WHERE id=#{sqlrow['id']}")
  end
end
deals_file.close
