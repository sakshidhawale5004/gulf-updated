import glob
import re

# Read the header from index.html
with open('c:/Users/Sakshi/Downloads/new/gulftp-20-6-2026/index.html', 'r', encoding='utf-8') as f:
    index_content = f.read()

# Extract header
header_match = re.search(r'(<header class="navbar-custom sticky-top">.*?</header>)', index_content, flags=re.DOTALL)
if not header_match:
    print("Could not find header in index.html")
    exit(1)

new_header = header_match.group(1)

# Apply to all HTML files
html_files = glob.glob('c:/Users/Sakshi/Downloads/new/gulftp-20-6-2026/*.html')
count = 0
for filepath in html_files:
    if 'index.html' in filepath:
        continue

    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Replace the existing header
    updated_content = re.sub(
        r'<header class="navbar-custom sticky-top">.*?</header>',
        # Need to escape backslashes in new_header if any, but since it's a direct replacement string:
        lambda m: new_header,
        content,
        flags=re.DOTALL
    )

    if updated_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(updated_content)
        count += 1

print(f"Updated header in {count} files.")
