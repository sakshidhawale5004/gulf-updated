import os
import re

AOS_CSS = '    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">\n'
AOS_JS = '''
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
        });
    </script>
'''

def process_html_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Don't process if already has AOS
    if 'aos.css' in content:
        return False

    # Insert CSS
    head_end = content.find('</head>')
    if head_end != -1:
        content = content[:head_end] + AOS_CSS + content[head_end:]

    # Insert JS
    body_end = content.find('</body>')
    if body_end != -1:
        content = content[:body_end] + AOS_JS + content[body_end:]

    # Inject data-aos into specific elements using regex
    # Target sections that are not hero-section
    content = re.sub(r'<section class="(?!hero-section)([^"]+)">', r'<section data-aos="fade-up" class="\1">', content)
    
    # Target common layout columns but NOT inside hero section (hero has col-lg-6)
    # We will just target col-lg-3, col-lg-4, col-md-6 which are typically feature cards
    content = re.sub(r'<div class="(col-lg-[34]|col-md-[46])([^"]*)">', r'<div data-aos="fade-up" class="\1\2">', content)
    
    # Target custom cards
    content = re.sub(r'<div class="(conxora-card|value-card|feature-card)([^"]*)">', r'<div data-aos="fade-up" data-aos-delay="100" class="\1\2">', content)

    # Target typography headers that might not be in the above columns
    content = re.sub(r'<h2 class="(section-title)([^"]*)">', r'<h2 data-aos="fade-up" class="\1\2">', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
        
    return True

if __name__ == "__main__":
    count = 0
    for file in os.listdir('.'):
        if file.endswith('.html'):
            if process_html_file(file):
                count += 1
                print(f"Processed: {file}")
    
    print(f"Successfully added AOS to {count} files.")
