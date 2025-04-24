import random
from faker import Faker

fake = Faker('el_GR')  # Greek locale for realistic names and addresses

num_employees = 1500
store_ids = list(range(1, 41))  # Assuming store_id from 1 to 40

employees = []
for i in range(1, num_employees + 1):
    full_name = fake.name()
    address = fake.address().replace("\n", ", ")
    phone = fake.phone_number()
    salary = round(random.uniform(800.00, 2500.00), 2)
    id_number = f"ID{random.randint(100000, 999999)}{random.choice('ABCDEFGH')}"
    store_id = random.choice(store_ids)
    
    employees.append(
        f"({i}, '{full_name}', '{address}', '{phone}', {salary}, '{id_number}', {store_id})"
    )

# Build SQL insert
with open("populate_employees.sql", "w", encoding="utf-8") as file:
    file.write("INSERT INTO Employees (employee_id, full_name, address, phone, salary, id_number, store_id) VALUES\n")
    file.write(",\n".join(employees))
    file.write(";")

#print("✅ 1500 employee records saved to populate_employees.sql")
