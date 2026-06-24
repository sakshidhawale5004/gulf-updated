import glob
import re

html_files = glob.glob('*.html')
count = 0

for filepath in html_files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Remove font-family from inline styles
    updated_content = re.sub(r'font-family:\s*\'Outfit\',\s*sans-serif;?', '', content)
    updated_content = re.sub(r'font-family:\s*\'TT Commons Pro\',\s*sans-serif;?', '', content)
    updated_content = re.sub(r'font-family:\s*Poppins;?', '', content)

    if updated_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(updated_content)
        count += 1
        print(f"Updated {filepath}")

print(f"Updated fonts in {count} files.")
