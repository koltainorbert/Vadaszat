import os

def undouble(text):
    result = []
    i = 0
    while i < len(text):
        ch = text[i]
        if ord(ch) < 0x80:
            result.append(ch)
            i += 1
        else:
            # Egymást követő non-ASCII karaktereket EGYÜTT kell encode+decode-olni
            j = i
            while j < len(text) and ord(text[j]) >= 0x80:
                j += 1
            chunk = text[i:j]
            try:
                fixed = chunk.encode('cp1250').decode('utf-8')
                result.append(fixed)
            except (UnicodeEncodeError, UnicodeDecodeError):
                result.append(chunk)
            i = j
    return ''.join(result)

files = [
    r'd:\Vadaszat2026\single-va_listing.php',
    r'd:\Vadaszat2026\wp-theme\vadaszapro-theme\single-va_listing.php',
]

for path in files:
    raw = open(path, 'rb').read()
    if raw.startswith(b'\xef\xbb\xbf'):
        raw = raw[3:]
    txt = raw.decode('utf-8')
    if '\u0102' in txt:
        fixed = undouble(txt)
        with open(path, 'w', encoding='utf-8') as f:
            f.write(fixed)
        print(f'FIXED: {path}')
    else:
        print(f'OK: {path}')
