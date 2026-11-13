import bcrypt

pw = b"Password123!"
salt = bcrypt.gensalt(rounds=10, prefix=b"2b")
hashed = bcrypt.hashpw(pw, salt).decode("utf-8")
# Convert prefix 2b to 2y for classic PHP PDO / password_verify
php_hash = "$2y$" + hashed[4:]
print("Raw hash:", hashed)
print("PHP hash:", php_hash)

# Update database.sql with this exact verified hash
with open("database.sql", "r", encoding="utf-8") as f:
    sql = f.read()

# Replace sample hashes
import re
new_sql = re.sub(r"'\$2y\$10\$[^']+'", f"'{php_hash}'", sql)
with open("database.sql", "w", encoding="utf-8") as f:
    f.write(new_sql)

print("Updated database.sql with verified hash.")

