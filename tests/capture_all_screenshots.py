"""
TaskFlow – Automated High-Fidelity Screenshot Capture Engine
Captures the 9 required submission screenshots using Chrome Headless and Chrome DevTools Protocol (CDP).
"""

import subprocess
import socket
import json
import base64
import os
import time
import urllib.request
import urllib.parse
import http.cookiejar
import struct

BASE_URL = "http://localhost/taskflow"
SCREENSHOTS_DIR = os.path.abspath("screenshots")
PROFILE_DIR = os.path.abspath("chrome_profile_capture")
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

os.makedirs(SCREENSHOTS_DIR, exist_ok=True)
os.makedirs(PROFILE_DIR, exist_ok=True)

# -------------------------------------------------------------
# 1. Helper: Perform HTTP Login to Obtain PHPSESSID
# -------------------------------------------------------------
def get_authenticated_session_id(email, password):
    cj = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
    
    # 1. Fetch login form to obtain CSRF token
    resp = opener.open(f"{BASE_URL}/auth/login.php")
    html = resp.read().decode('utf-8')
    import re
    m = re.search(r'name="csrf_token"\s+value="([a-f0-9]+)"', html)
    if not m:
        raise ValueError("Could not extract CSRF token from login page")
    csrf_token = m.group(1)
    
    # 2. Submit login credentials
    post_data = urllib.parse.urlencode({
        "csrf_token": csrf_token,
        "email": email,
        "password": password
    }).encode('utf-8')
    
    login_resp = opener.open(urllib.request.Request(f"{BASE_URL}/auth/login.php", data=post_data, method="POST"))
    login_html = login_resp.read().decode('utf-8')
    
    # Extract PHPSESSID cookie
    sess_id = None
    for cookie in cj:
        if cookie.name == "PHPSESSID":
            sess_id = cookie.value
            break
            
    if not sess_id:
        raise ValueError(f"Failed to obtain PHPSESSID for {email}")
        
    print(f"[✓] Successfully authenticated {email} -> PHPSESSID: {sess_id[:8]}...")
    return sess_id

# -------------------------------------------------------------
# 2. Minimal RFC 6455 WebSocket Client for Chrome DevTools Protocol
# -------------------------------------------------------------
class CDPClient:
    def __init__(self, ws_url):
        # ws_url looks like ws://localhost:9222/devtools/page/...
        parsed = urllib.parse.urlparse(ws_url)
        self.host = parsed.hostname
        self.port = parsed.port or 80
        self.path = parsed.path
        self.sock = socket.create_connection((self.host, self.port), timeout=15)
        self._handshake()
        self.msg_id = 0

    def _handshake(self):
        key = base64.b64encode(os.urandom(16)).decode('ascii')
        req = (
            f"GET {self.path} HTTP/1.1\r\n"
            f"Host: {self.host}:{self.port}\r\n"
            f"Upgrade: websocket\r\n"
            f"Connection: Upgrade\r\n"
            f"Sec-WebSocket-Key: {key}\r\n"
            f"Sec-WebSocket-Version: 13\r\n\r\n"
        )
        self.sock.sendall(req.encode('ascii'))
        
        # Read HTTP response headers
        buf = bytearray()
        while b"\r\n\r\n" not in buf:
            chunk = self.sock.recv(4096)
            if not chunk:
                raise ConnectionError("WebSocket handshake failed: EOF")
            buf.extend(chunk)
            
        header_text = buf.decode('ascii', errors='replace')
        if " 101 " not in header_text:
            raise ConnectionError(f"WebSocket handshake failed: {header_text[:100]}")

    def send_frame(self, text):
        data = text.encode('utf-8')
        length = len(data)
        frame = bytearray([0x81]) # text frame
        mask = os.urandom(4)
        if length <= 125:
            frame.append(0x80 | length)
        elif length <= 65535:
            frame.append(0x80 | 126)
            frame.extend(struct.pack("!H", length))
        else:
            frame.append(0x80 | 127)
            frame.extend(struct.pack("!Q", length))
        frame.extend(mask)
        frame.extend(bytes(b ^ mask[i % 4] for i, b in enumerate(data)))
        self.sock.sendall(frame)

    def recv_frame(self):
        def recv_exact(n):
            buf = bytearray()
            while len(buf) < n:
                chunk = self.sock.recv(n - len(buf))
                if not chunk:
                    raise ConnectionError("Socket closed prematurely")
                buf.extend(chunk)
            return bytes(buf)

        header = recv_exact(2)
        opcode = header[0] & 0x0F
        has_mask = (header[1] & 0x80) != 0
        length = header[1] & 0x7F
        if length == 126:
            length = struct.unpack("!H", recv_exact(2))[0]
        elif length == 127:
            length = struct.unpack("!Q", recv_exact(8))[0]
        
        mask = recv_exact(4) if has_mask else None
        payload = recv_exact(length)
        if has_mask:
            payload = bytes(b ^ mask[i % 4] for i, b in enumerate(payload))
        return payload.decode('utf-8', errors='replace')

    def call(self, method, params=None):
        self.msg_id += 1
        req_id = self.msg_id
        req = {"id": req_id, "method": method, "params": params or {}}
        self.send_frame(json.dumps(req))
        
        while True:
            raw = self.recv_frame()
            try:
                res = json.loads(raw)
                if res.get("id") == req_id:
                    return res.get("result", {})
            except Exception:
                pass

    def close(self):
        try:
            self.sock.close()
        except Exception:
            pass

# -------------------------------------------------------------
# 3. Main Screenshot Runner
# -------------------------------------------------------------
def run_capture():
    print("==================================================")
    print("CAPTURING 9 HIGH-FIDELITY APPLICATION SCREENSHOTS")
    print("==================================================")

    # Launch Chrome with CDP
    chrome_cmd = [
        CHROME_PATH,
        "--headless=new",
        "--remote-debugging-port=9222",
        "--disable-gpu",
        "--no-sandbox",
        "--window-size=1280,820",
        f"--user-data-dir={PROFILE_DIR}"
    ]
    proc = subprocess.Popen(chrome_cmd)
    try:
        time.sleep(2.5)
        
        # Get list of targets
        with urllib.request.urlopen("http://localhost:9222/json") as resp:
            targets = json.loads(resp.read().decode())
        page_ws = targets[0]["webSocketDebuggerUrl"]
        client = CDPClient(page_ws)
        
        # Enable domains & set viewport
        client.call("Page.enable")
        client.call("Network.enable")
        client.call("Emulation.setDeviceMetricsOverride", {
            "width": 1280,
            "height": 820,
            "deviceScaleFactor": 1,
            "mobile": False
        })

        # Helper to navigate and capture
        def capture(filename, url, setup_js=None, wait_sec=1.5):
            print(f"[*] Capturing: {filename} from {url}...")
            client.call("Page.navigate", {"url": url})
            time.sleep(wait_sec)
            if setup_js:
                client.call("Runtime.evaluate", {"expression": setup_js})
                time.sleep(0.8)
            res = client.call("Page.captureScreenshot", {"format": "png"})
            png_bytes = base64.b64decode(res["data"])
            out_path = os.path.join(SCREENSHOTS_DIR, filename)
            with open(out_path, "wb") as f:
                f.write(png_bytes)
            print(f"[✓] Saved: {out_path} ({len(png_bytes):,} bytes)")

        # 1. 01-registration.png: Registration Screen
        capture("01-registration.png", f"{BASE_URL}/auth/register.php")

        # 2. 02-login.png: Login Screen
        capture("02-login.png", f"{BASE_URL}/auth/login.php")

        # Get session for John Doe
        john_sess = get_authenticated_session_id("john.doe@example.com", "Password123!")
        
        # Set cookie in Chrome for John Doe
        client.call("Network.setCookie", {
            "name": "PHPSESSID",
            "value": john_sess,
            "domain": "localhost",
            "path": "/"
        })

        # 3. 03-dashboard.png: Task Dashboard (John Doe)
        capture("03-dashboard.png", f"{BASE_URL}/tasks/index.php")

        # 4. 04-create-task.png: Create Task Screen
        capture("04-create-task.png", f"{BASE_URL}/tasks/create.php")

        # 5. 05-task-list.png: Task Table with Status Badges
        capture("05-task-list.png", f"{BASE_URL}/tasks/index.php")

        # 6. 06-edit-task.png: Edit Task Form
        capture("06-edit-task.png", f"{BASE_URL}/tasks/edit.php?id=1")

        # 7. 07-status-management.png: Status Change Modal open
        setup_modal = """
        (function() {
            var modalEl = document.getElementById('statusModal');
            if (modalEl) {
                document.getElementById('modalTaskId').value = '1';
                document.getElementById('modalTaskTitle').textContent = 'Design Responsive Dashboard UI';
                document.getElementById('modalStatusSelect').value = 'Completed';
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        })();
        """
        capture("07-status-management.png", f"{BASE_URL}/tasks/index.php", setup_js=setup_modal)

        # 8. 08-search-filter.png: Search & Filtering Active
        capture("08-search-filter.png", f"{BASE_URL}/tasks/index.php?search=Database&status=")

        # 9. 09-user-isolation.png: Jane Smith's Dashboard (User 2 Isolation)
        jane_sess = get_authenticated_session_id("jane.smith@example.com", "Password123!")
        client.call("Network.setCookie", {
            "name": "PHPSESSID",
            "value": jane_sess,
            "domain": "localhost",
            "path": "/"
        })
        capture("09-user-isolation.png", f"{BASE_URL}/tasks/index.php")

        client.close()
        print("==================================================")
        print("ALL 9 SCREENSHOTS CAPTURED SUCCESSFULLY!")
        print("==================================================")
    finally:
        proc.terminate()
        proc.wait()

if __name__ == "__main__":
    run_capture()

