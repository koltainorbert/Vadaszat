import pymysql

try:
    conn = pymysql.connect(host='127.0.0.1', port=10018, user='root', password='root', database='local', connect_timeout=5)
    c = conn.cursor()
    c.execute("SELECT option_name, option_value FROM wp_options WHERE option_name IN ('template','stylesheet','active_plugins')")
    for r in c.fetchall():
        print(r[0], '=', r[1][:300])
    conn.close()
except Exception as e:
    print(f"Hiba: {e}")
