import glob

html_files = glob.glob('*.html')
count = 0

for filepath in html_files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Simple string replace instead of regex
    updated_content = content.replace("font-family: 'Outfit', sans-serif;", "")
    updated_content = updated_content.replace("font-family: 'TT Commons Pro', sans-serif;", "")
    
    if updated_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(updated_content)
        count += 1
        print(f"Updated {filepath}")

print(f"Updated fonts in {count} files.")
