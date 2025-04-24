import random
import os

# Configuration
num_stores = 40
num_products = 61
total_entries = 2000

# Generate unique (store_id, product_id) pairs
pairs = set()
while len(pairs) < total_entries:
    store_id = random.randint(1, num_stores)
    product_id = random.randint(1, num_products)
    pairs.add((store_id, product_id))

# Build SQL values
entries = []
for store_id, product_id in pairs:
    quantity = random.randint(20, 500)
    minimum_stock = random.randint(10, min(100, quantity - 1))
    entries.append(f"({store_id}, {product_id}, {quantity}, {minimum_stock})")

# Chunk entries into batches of 100
chunks = [entries[i:i+100] for i in range(0, len(entries), 100)]

# Create full SQL insert script
sql_script = ""
for chunk in chunks:
    sql_script += "INSERT INTO Store_Inventory (store_id, product_id, quantity, minimum_stock) VALUES\n"
    sql_script += ",\n".join(chunk) + ";\n\n"

# Output to terminal (or optionally save)
print(sql_script)

# OPTIONAL: Save to file
os.makedirs("supermarket", exist_ok=True)
with open("supermarket/populate_store_inventory.sql", "w") as f:
    f.write(sql_script)
