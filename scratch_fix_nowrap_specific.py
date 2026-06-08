import re

path = r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\review.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# We need to add whitespace-nowrap to certain headers
headers_to_nowrap = ['ID Laporan', 'Tgl Lapor & Ketahui', 'Kategori', 'Status Tindak Lanjut', 'Detail', 'Aksi', 'Update Status']
for h in headers_to_nowrap:
    # Match: <th class="...">ID Laporan</th>
    # Replace with <th class="... whitespace-nowrap">ID Laporan</th>
    pattern = r'(<th class="[^"]*)(">\s*' + re.escape(h) + r'\s*</th>)'
    content = re.sub(pattern, r'\1 whitespace-nowrap\2', content)

# Now for the specific <td> elements.
# The ID Laporan td is the one containing $report->kode_laporan or similar.
# In the original, it was something like:
# <td class="... text-center align-middle" data-sort-value="...">
#   <span class="... text-indigo-700 bg-indigo-50 ...">RISK-003TL-202605-0001</span>
# </td>
# Let's add whitespace-nowrap directly to the span or the td.
# The easiest way is to find spans that contain the report ID logic, but since it's blade, it's:
# {{ $report->kode_laporan }} or {{ $tl->kode_laporan }}
# Let's just add whitespace-nowrap to any element that prints kode_laporan.
content = re.sub(r'(<td class="[^"]*)("\s*data-sort-value="\{\{\s*\$(?:report|tl)->kode_laporan)', r'\1 whitespace-nowrap\2', content)

# Dates
content = re.sub(r'(<td class="[^"]*)("\s*data-sort-value="\{\{\s*\$(?:report|tl)->created_at->format)', r'\1 whitespace-nowrap\2', content)

# Status
content = re.sub(r'(<span class="px-2 py-1 inline-flex text-[10px] leading-4 font-extrabold rounded-sm uppercase tracking-wider[^"]*)(">)', r'\1 whitespace-nowrap\2', content)

# Dampak
content = re.sub(r'(<span class="font-bold[^"]*)("\s*>\s*Rp \{\{ number_format)', r'\1 whitespace-nowrap\2', content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Done")
