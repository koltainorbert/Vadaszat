import os

def undouble(text):
    result = []
    for ch in text:
        if ord(ch) <= 0x7F:
            result.append(ch)
            continue
        try:
            result.append(ch.encode('cp1250').decode('utf-8'))
        except (UnicodeEncodeError, UnicodeDecodeError):
            result.append(ch)
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
