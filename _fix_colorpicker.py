import re

f = 'D:/Vadaszat2026/wp-plugin/vadaszapro-core/admin/admin.css'
content = open(f, encoding='utf-8').read()

old_start = content.find('/* \u2500\u2500 Color picker')
old_end   = content.find('\n/* \u2500\u2500 Number spinner')

print(f'Replacing chars {old_start}..{old_end}')

new_block = """\
/* \u2500\u2500 Color picker \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */
body.va-admin-page .wp-picker-container {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    overflow: visible;
}
body.va-admin-page .wp-color-result.button {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 6px 14px 6px 8px !important;
    min-width: 130px !important;
    background: rgba(255,255,255,.06) !important;
    border: 1px solid rgba(255,255,255,.15) !important;
    border-radius: 8px !important;
    color: rgba(255,255,255,.85) !important;
    font-size: 12px !important;
    height: auto !important;
    cursor: pointer !important;
    transition: background .15s !important;
}
body.va-admin-page .wp-color-result.button:hover {
    background: rgba(255,255,255,.10) !important;
    color: #fff !important;
}
/* Swatch - CSS custom property-vel tolti JS */
body.va-admin-page .wp-color-result.button::before {
    content: '';
    display: inline-block;
    width: 18px; height: 18px;
    border-radius: 4px;
    background: var(--va-sw, #555) !important;
    border: 1px solid rgba(255,255,255,.2);
    flex-shrink: 0;
}
/* Popup holder */
body.va-admin-page .wp-picker-holder {
    z-index: 999999 !important;
    background: #1a1a24 !important;
    border: 1px solid rgba(255,255,255,.14) !important;
    border-radius: 10px !important;
    box-shadow: 0 16px 48px rgba(0,0,0,.75) !important;
    padding: 10px !important;
    overflow: visible !important;
}
body.va-admin-page .wp-picker-holder .iris-picker { background: transparent !important; }
body.va-admin-page .wp-picker-holder .iris-square { border-radius: 6px !important; overflow: hidden; }
body.va-admin-page .wp-picker-holder .iris-palette-container .iris-palette {
    border-radius: 3px !important;
    width: 22px !important; height: 22px !important;
}
body.va-admin-page .wp-picker-input-wrap {
    display: flex; align-items: center; gap: 6px; margin-top: 8px;
}
body.va-admin-page .wp-picker-input-wrap input[type="text"] {
    min-width: 80px !important; max-width: 100px !important;
    font-size: 12px !important; padding: 5px 8px !important;
}
body.va-admin-page .wp-picker-input-wrap .button {
    font-size: 11px !important; padding: 5px 10px !important;
    min-width: auto !important; pointer-events: auto !important;
}"""

new_content = content[:old_start] + new_block + content[old_end:]
open(f, 'w', encoding='utf-8').write(new_content)
print('OK wrote', len(new_content), 'chars')
