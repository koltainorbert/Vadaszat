import pymysql
import socket

# Find MySQL socket/pipe on Windows (LocalWP uses named pipes or different host)
# Try localhost with different approaches
configs = [
    {'host': 'localhost', 'port': 3306, 'user': 'root', 'password': 'root'},
    {'host': '127.0.0.1', 'port': 3306, 'user': 'root', 'password': 'root'},
    {'host': 'localhost', 'port': 10017, 'user': 'root', 'password': 'root'},
    {'unix_socket': r'\\.\pipe\mysql', 'user': 'root', 'password': 'root'},
]

# Check open ports
import socket
for port in [3306, 3307, 3308, 3309, 3310, 10016, 10017, 10018, 10019, 10020]:
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.settimeout(0.5)
        result = s.connect_ex(('127.0.0.1', port))
        if result == 0:
            print(f"Port {port}: NYITVA")
        s.close()
    except:
        pass
