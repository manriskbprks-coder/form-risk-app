import re

path = r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\review.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace whitespace-nowrap with empty string for th and td
content = re.sub(r'(<(?:th|td)[^>]*?class="[^"]*?)\bwhitespace-nowrap\b([^"]*?")', r'\1\2', content)

# Clean up any double spaces left behind
content = re.sub(r'  +', ' ', content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Done")
