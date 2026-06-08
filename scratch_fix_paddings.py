import os

files = [
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\admin\users\index.blade.php',
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\admin\roles\index.blade.php',
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\admin\risk_master\index.blade.php',
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\branches\index.blade.php'
]

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Replace outer padding
    content = content.replace('<div class="py-6 sm:py-12">', '<div class="pt-4 pb-8 sm:pb-12">')
    content = content.replace('<div class="py-6 sm:py-12" x-data="{', '<div class="pt-4 pb-8 sm:pb-12" x-data="{')
    content = content.replace('<div class="py-6 sm:py-8">', '<div class="pt-4 pb-8 sm:pb-12">')

    # Replace page-shell wrapper to be full width
    content = content.replace('<div class="page-shell page-stack">', '<div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto page-stack">')
    content = content.replace('<div class="page-shell">', '<div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">')

    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)

print("Done replacing paddings.")
