import os, glob
bad = []
for f in glob.glob('**/*.php', recursive=True):
    if '.vscode' in f: continue
    try:
        txt = open(f, encoding='utf-8').read()
        if '\u0102' in txt:
            bad.append(f)
    except Exception as e:
        bad.append(f + ' [read error: ' + str(e) + ']')

if bad:
    for b in bad:
        print('GARBLED:', b)
    print(f'\n{len(bad)} fajlban van karakterhiba!')
else:
    print('OK - nincs karakterhiba egyetlen PHP fajlban sem')
