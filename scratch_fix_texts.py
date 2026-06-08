import os

# 1. Halaman riwayat-risiko (index.blade.php)
index_path = r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\index.blade.php'
with open(index_path, 'r', encoding='utf-8') as f:
    index_content = f.read()
if "@section('page_title'" not in index_content:
    index_content = index_content.replace('<x-app-layout>', "<x-app-layout>\n    @section('page_title', 'Riwayat Laporan')")
with open(index_path, 'w', encoding='utf-8') as f:
    f.write(index_content)

# 2 & 3. Halaman form-risiko (create.blade.php)
create_path = r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\create.blade.php'
with open(create_path, 'r', encoding='utf-8') as f:
    create_content = f.read()
if "@section('page_title'" not in create_content:
    create_content = create_content.replace('<x-app-layout>', "<x-app-layout>\n    @section('page_title', 'Form Laporan')")

# Change "Form Input Risiko Operasional (Maker)"
old_maker = "{{ __('Form Input Risiko Operasional (Maker)') }}"
new_maker = "{{ __('Form Input Risiko Operasional (' . ($kategori === 'finansial' ? 'Finansial' : 'Non-Finansial') . ')') }}"
create_content = create_content.replace(old_maker, new_maker)

with open(create_path, 'w', encoding='utf-8') as f:
    f.write(create_content)

# 4. Halaman review-laporan (review.blade.php)
review_path = r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\review.blade.php'
with open(review_path, 'r', encoding='utf-8') as f:
    review_content = f.read()
if "@section('page_title'" not in review_content:
    review_content = review_content.replace('<x-app-layout>', "<x-app-layout>\n    @section('page_title', 'Review Laporan')")

# 4b. "Review Laporan Risiko (Checker)" menjadi "Review Laporan Risiko"
old_checker = "{{ __('Review Laporan Risiko (Checker)') }}"
new_checker = "{{ __('Review Laporan Risiko') }}"
review_content = review_content.replace(old_checker, new_checker)

# 4c. Hapus kalimat = "Antrian approval dan tindak lanjut dipisahkan lebih jelas supaya proses review terasa lebih fokus."
sentence_to_remove = "Antrian approval dan tindak lanjut dipisahkan lebih jelas supaya proses review terasa lebih fokus."
review_content = review_content.replace(sentence_to_remove, "")

with open(review_path, 'w', encoding='utf-8') as f:
    f.write(review_content)

print("Done making replacements.")
