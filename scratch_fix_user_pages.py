import os

files = [
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\create.blade.php',
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\review.blade.php',
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_reports\show.blade.php',
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_free_declarations\create.blade.php',
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\risk_free_declarations\history.blade.php',
    r'd:\xampp\htdocs\form_risk - Copy\resources\views\profile\edit.blade.php'
]

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Normalize the top padding
    content = content.replace('<div class="py-6 sm:py-12">', '<div class="pt-4 pb-8 sm:pb-12">')
    content = content.replace('<div class="py-6 sm:py-8">', '<div class="pt-4 pb-8 sm:pb-12">')
    content = content.replace('<div class="py-6">', '<div class="pt-4 pb-8 sm:pb-12">')

    # Normalize container widths
    # For create (Risk Report), change max-w-4xl to max-w-full so the form cards stretch out like the other pages if the user wants full width consistency
    # But wait, max-w-5xl is probably better for form so inputs don't become 1920px wide. Let's make it max-w-7xl so it's wider but not insane, or just max-w-full but let the inner contents be managed.
    # User said "ukuran dan formatnya gua mau seragam" -> I will make them all max-w-full w-full so they match index.blade.php, which is max-w-full.
    
    content = content.replace('<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">', '<div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">')
    content = content.replace('<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">', '<div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">')
    content = content.replace('<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">', '<div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">')
    content = content.replace('<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">', '<div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto">')
    content = content.replace('<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">', '<div class="max-w-full w-full px-4 sm:px-6 lg:px-8 mx-auto space-y-6">')

    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)

print("Done standardizing user-facing pages.")
