import chardet

with open('index.html', 'rb') as f:
    raw = f.read()
enc = chardet.detect(raw).get('encoding', 'utf-8') or 'utf-8'
content = raw.decode(enc, errors='replace')

# Replace Interest Rates image (old jpg -> new jpg)
old_ir = 'src="Interest Rates.jpg" alt="Interest Rates"'
new_ir = 'src="Interest Rates-NEW.jpg" alt="Interest Rates"'

# Replace IP Licensing image
old_ip = 'src="IP_LicensingIMAGE.jpg" alt="IP Licensing"'
new_ip = 'src="IP_Licensing-NEW.jpg" alt="IP Licensing"'

if old_ir in content:
    content = content.replace(old_ir, new_ir)
    print("Updated Interest Rates image")
else:
    print("WARNING: Interest Rates image not found!")

if old_ip in content:
    content = content.replace(old_ip, new_ip)
    print("Updated IP Licensing image")
else:
    print("WARNING: IP Licensing image not found!")

with open('index.html', 'wb') as f:
    f.write(content.encode(enc, errors='replace'))
print("Done!")
