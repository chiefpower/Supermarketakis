import random
import os

# Config
num_suppliers = 40
num_products = 61
target_entries = 2000

# Generate unique (supplier_id, product_id) pairs
pairs = set()
while len(pairs) < target_entries:
    supplier_id = random.randint(1, num_suppliers)
    product_id = random.randint(1, num_products)
    pairs.add((supplier_id, product_id))

# Generate entries with realistic pricing
entries = []
for supplier_id, product_id in pairs:
    # Random price between 0.50 and 200.00
    price = round(random.uniform(0.50, 200.00), 2)
    entries.append(f"({supplier_id}, {product_id}, {price})")

# Chunk insert statements into batches of 100
chunks = [entries[i:i+100] for i in range(0, len(entries), 100)]

# Build SQL insert script
sql_script = ""
for chunk in chunks:
    sql_script += "INSERT INTO Supplier_Product (supplier_id, product_id, sale_price) VALUES\n"
    sql_script += ",\n".join(chunk) + ";\n\n"

# Save to file
os.makedirs("supermarket", exist_ok=True)
with open("supermarket/populate_supplier_product.sql", "w") as f:
    f.write(sql_script)

#print("SQL script with 4000 entries has been saved to 'supermarket/populate_supplier_product.sql'")
