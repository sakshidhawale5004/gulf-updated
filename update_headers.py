import os
import glob
import re

html_files = glob.glob('c:/Users/Sakshi/Downloads/new/gulftp-20-6-2026/*.html')

new_buttons = '''<div class="d-none d-lg-flex gap-2">
                    <a href="book-search.html" class="btn-conxora">BOOK SEARCH</a>
                    <a href="book-an-appointment.html" class="btn-conxora" style="background-color: transparent !important; color: var(--primary-green) !important; border: 1px solid var(--primary-green) !important;">BOOK APPOINTMENT</a>
                    <a href="#" class="btn-conxora" style="background-color: var(--primary-orange) !important; color: white !important;">BOOK DEMO</a>
                </div>'''

count = 0
for filepath in html_files:
    if os.path.basename(filepath) == 'index.html':
        continue

    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content

    content = re.sub(
        r'(<header class="navbar-custom sticky-top">\s*)<div class="container">',
        r'\1<div class="container-fluid px-xl-5">',
        content,
        flags=re.IGNORECASE
    )

    button_block_regex = re.compile(
        r'<div class="d-none d-lg-flex gap-\d+">\s*<a href="book-search\.html" class="btn-conxora">[^<]+</a>\s*<a href="book-an-appointment\.html"[^>]*>[^<]+</a>(?:\s*<a href="#"[^>]*>[^<]+</a>)?\s*</div>',
        re.IGNORECASE
    )
    
    content = button_block_regex.sub(new_buttons, content)

    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        count += 1

print(f"Updated {count} HTML files.")
