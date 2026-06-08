import os
import re

files_and_titles = {
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\review.blade.php': "{{ __('Review Laporan Risiko') }}",
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\create.blade.php': "{{ __('Form Input Risiko Operasional (' . ($kategori === 'finansial' ? 'Finansial' : 'Non-Finansial') . ')') }}",
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_free_declarations\create.blade.php': "{{ __('Deklarasi Nihil Risiko') }}",
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_free_declarations\history.blade.php': "{{ __('Riwayat Deklarasi Nihil Risiko') }}",
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\show.blade.php': "{{ __('Detail Laporan Risiko') }}"
}

standardized_header = """<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight tracking-tight">
            {title}
        </h2>
    </div>
</x-slot>"""

for path, title in files_and_titles.items():
    if not os.path.exists(path):
        continue
        
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    # Find the <x-slot name="header">...</x-slot> block and replace it
    pattern = r'<x-slot name="header">.*?</x-slot>'
    new_header = standardized_header.replace('{title}', title)
    
    # We use re.DOTALL so .*? matches newlines too
    content = re.sub(pattern, new_header, content, flags=re.DOTALL)
    
    # For risk_free_declarations, set the page_title to 'Deklarasi'
    if 'risk_free_declarations' in path:
        if "@section('page_title'" not in content:
            content = content.replace('<x-app-layout>', "<x-app-layout>\n    @section('page_title', 'Deklarasi')")
        else:
            content = re.sub(r"@section\('page_title',\s*'.*?'\)", "@section('page_title', 'Deklarasi')", content)
            
    # For show.blade.php, we might need to add page_title
    if 'show.blade.php' in path:
        if "@section('page_title'" not in content:
            content = content.replace('<x-app-layout>', "<x-app-layout>\n    @section('page_title', 'Detail Laporan')")
            
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

print("Done standardizing headers.")
