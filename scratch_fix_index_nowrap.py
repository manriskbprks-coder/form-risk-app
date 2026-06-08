import re

path = r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\index.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove all whitespace-nowrap
content = re.sub(r'(<(?:th|td)[^>]*?class="[^"]*?)\bwhitespace-nowrap\b([^"]*?")', r'\1\2', content)
content = re.sub(r'  +', ' ', content)

# 2. Add whitespace-nowrap back to specific headers
headers_to_nowrap = ['ID Laporan', 'Tgl Lapor & Kejadian', 'Dampak', 'Status', 'Aksi']
for h in headers_to_nowrap:
    pattern = r'(<th class="[^"]*)(">\s*' + re.escape(h) + r'\s*</th>)'
    content = re.sub(pattern, r'\1 whitespace-nowrap\2', content)

# 3. Add whitespace-nowrap back to specific data cells
# ID Laporan td
content = re.sub(r'(<td class="[^"]*)("\s*data-sort-value="\{\{\s*\$report->kode_laporan)', r'\1 whitespace-nowrap\2', content)

# Tgl Lapor td
content = re.sub(r'(<td class="[^"]*)("\s*data-sort-value="\{\{\s*\$report->created_at->format)', r'\1 whitespace-nowrap\2', content)

# Status td
content = re.sub(r'(<td class="[^"]*)("\s*data-sort-value="\{\{\s*\$report->status)', r'\1 whitespace-nowrap\2', content)

# Aksi td (usually the last one containing buttons)
content = re.sub(r'(<td class="[^"]*)("\s*>\s*<div class="flex items-center)', r'\1 whitespace-nowrap\2', content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Done index.blade.php")
