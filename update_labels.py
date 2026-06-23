import glob

html_files = glob.glob('*.html')
for file in html_files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    new_content = content.replace('class="form-label fw-semibold text-secondary"', 'class="form-label fw-semibold" style="color: var(--primary-green) !important;"')
    new_content = new_content.replace('class="form-label fw-semibold text-secondary mb-2"', 'class="form-label fw-semibold mb-2" style="color: var(--primary-green) !important;"')
    
    if new_content != content:
        with open(file, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f'Updated {file}')
