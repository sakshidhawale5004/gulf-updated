from PIL import Image
import os

try:
    filepath = 'herosectionimagefinal.jpg'
    file_size_mb = os.path.getsize(filepath) / (1024 * 1024)
    print(f"Original size: {file_size_mb:.2f} MB")
    
    img = Image.open(filepath)
    # Convert RGBA to RGB if necessary
    if img.mode in ('RGBA', 'P'):
        img = img.convert('RGB')
        
    # Resize if too large
    max_width = 1600
    if img.width > max_width:
        ratio = max_width / img.width
        new_height = int(img.height * ratio)
        img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)
    
    # Save as compressed JPEG
    img.save(filepath, 'JPEG', quality=80, optimize=True)
    
    new_size_mb = os.path.getsize(filepath) / (1024 * 1024)
    print(f"Compressed size: {new_size_mb:.2f} MB")
    print("Successfully compressed image.")
except Exception as e:
    print(f"Error: {e}")
