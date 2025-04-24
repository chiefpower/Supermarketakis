import random

# Define base values
types = [
    "Dairy", "Bakery", "Meat", "Fruit", "Drinks", "Grains", "Sauces", "Canned Goods", "Household", 
    "Personal Care", "Frozen Food", "Snacks", "Baby Care", "Oils & Vinegars", "Spices", 
    "Breakfast Foods", "Sweeteners", "Spreads"
]

manufacturers = [
    "BrandA", "BrandB", "BrandC", "FreshFarm", "FarmFresh", "OceanFresh", "CleanHome", 
    "FreshLocks", "BrightSmile", "CleanWiz", "SweetTreats", "SackMaster", "ChocoDelights",
    "BrewMasters", "FineVines", "BabySoft", "SkinGlow", "HealthGuard", "BrewPerfect",
    "NoodleKing", "VeggieDelight", "WingWorld", "SpicyDelight", "OliveGold", "PureVinegar",
    "PureSalt", "SpiceCo", "SpiceMaster", "FreshRoots", "NutHouse", "NatureBites", 
    "OceanHarvest", "OceanDelight", "BrandG", "HealthyOats", "BrandH", "SweetDrizzle",
    "Nature'sBest", "NuttyDelights", "BerryGood"
]

name_prefixes = [
    "Fresh", "Organic", "Premium", "Budget", "Classic", "Healthy", "Quick", "Easy", "Deluxe", "Golden",
    "Tasty", "Natural", "Crunchy", "Creamy", "Savory", "Sweet", "Zesty", "Tangy", "Smoky", "Rich"
]

base_names = [
    "Milk", "Bread", "Butter", "Eggs", "Chicken", "Beef", "Apple", "Banana", "Juice", "Cola", 
    "Rice", "Pasta", "Ketchup", "Tuna", "Toilet Paper", "Shampoo", "Toothpaste", "Pizza", "Ice Cream",
    "Chips", "Chocolate", "Soda", "Beer", "Wine", "Soap", "Tablets", "Bleach", "Diapers", 
    "Wipes", "Sanitizer", "Cream", "Conditioner", "Deodorant", "Coffee", "Noodles", "Corn", 
    "Beans", "Vegetables", "Fries", "Wings", "Sauce", "Olive Oil", "Vinegar", "Salt", "Pepper", 
    "Garlic", "Ginger", "Almonds", "Cashews", "Bars", "Shrimp", "Salmon", "Cereal", "Oatmeal",
    "Pancake Mix", "Syrup", "Honey", "Peanut Butter", "Jam"
]

photo_source = "images/thumb-milk.png"

# Generate 3000 products
products = []
for i in range(62, 3062):  # Starting from product_id 62
    name = f"{random.choice(name_prefixes)} {random.choice(base_names)}"
    prod_type = random.choice(types)
    manufacturer = random.choice(manufacturers)
    price = round(random.uniform(0.5, 20.0), 2)
    new_prod = random.choices([0, 1], weights=[85, 15])[0]  # 15% chance it's a new product
    products.append(
        f"({i}, '{name}', '{prod_type}', '{manufacturer}', {price}, '{photo_source}', {new_prod})"
    )

# Combine into full SQL insert statement
with open("populate_products.sql", "w", encoding="utf-8") as file:
    file.write("INSERT INTO products (product_id, name, type, manufacturer, price, photo_source, new_prod) VALUES\n")
    file.write(",\n".join(products))
    file.write(";")

#print("Done! SQL saved to populate_products.sql")
