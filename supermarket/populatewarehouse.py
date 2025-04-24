import random

# Settings
num_warehouses = 10
num_products = 61
num_entries = 500

# Ensure unique (warehouse_id, product_id) combinations
unique_pairs = set()
while len(unique_pairs) < num_entries:
    warehouse_id = random.randint(1, num_warehouses)
    product_id = random.randint(1, num_products)
    unique_pairs.add((warehouse_id, product_id))

# Create the SQL entries
entries = [(wid, pid, random.randint(10, 500)) for (wid, pid) in unique_pairs]

# Format into SQL insert statements
insert_chunks = []
chunk_size = 100
for i in range(0, len(entries), chunk_size):
    chunk = entries[i:i + chunk_size]
    values = ",\n".join([f"({wid}, {pid}, {qty})" for wid, pid, qty in chunk])
    insert_statement = f"INSERT INTO Warehouse_Inventory (warehouse_id, product_id, quantity) VALUES\n{values};\n"
    insert_chunks.append(insert_statement)

# Combine and output to console or file
full_sql_script = "\n".join(insert_chunks)
print(full_sql_script)

# Optionally save to a file
with open("populate_warehouse_inventory.sql", "w") as f:
     f.write(full_sql_script)
