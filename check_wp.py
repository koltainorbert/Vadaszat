import subprocess, sys

# Install pymysql if missing
subprocess.run([sys.executable, '-m', 'pip', 'install', 'pymysql', '-q'], capture_output=True)

import pymysql

ports = [10017, 3306, 3308, 10018]
for port in ports:
    try:
        conn = pymysql.connect(host='127.0.0.1', port=port, user='root', password='root', database='local', connect_timeout=3)
        c = conn.cursor()
        c.execute("SELECT option_name, option_value FROM wp_options WHERE option_name IN ('template','stylesheet','active_plugins')")
        print(f"=== PORT {port} OK ===")
        for r in c.fetchall():
            print(r[0], '=', r[1][:200])
        conn.close()
        break
    except Exception as e:
        print(f"Port {port}: {e}")
