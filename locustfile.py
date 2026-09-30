from locust import HttpUser, task, between
from locust.exception import StopUser
from html.parser import HTMLParser
import random
from urllib.parse import urlparse


class CsrfTokenParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.token = None

    def handle_starttag(self, tag, attrs):
        if tag.lower() == "input":
            attributes = dict(attrs)
            if attributes.get("name") == "_token":
                self.token = attributes.get("value")

class PenggunaSiLacak(HttpUser):
    wait_time = between(1, 3)

    def on_start(self):
        """Login sekali di awal sesi"""
        with self.client.get("/login", name="GET /login", catch_response=True) as login_page:
            if login_page.status_code != 200:
                login_page.failure(f"Halaman login gagal: HTTP {login_page.status_code}")
                raise StopUser()

            parser = CsrfTokenParser()
            parser.feed(login_page.text)
            if not parser.token:
                login_page.failure("Token CSRF tidak ditemukan di halaman login")
                raise StopUser()
            csrf_token = parser.token
            login_page.success()

        with self.client.post(
            "/login",
            {
                "_token": csrf_token,
                "email": "admin@silacak.test",
                "password": "Password!12345",
            },
            name="POST /login",
            catch_response=True,
        ) as response:
            final_path = urlparse(response.url).path.rstrip("/")
            
            # PERBAIKAN DI SINI: Izinkan /dashboard ATAU /shipments
            valid_paths = ["/dashboard", "/shipments"]
            
            if response.status_code >= 400 or final_path not in valid_paths:
                response.failure(
                    f"Login gagal: HTTP {response.status_code}, halaman akhir {final_path}"
                )
                raise StopUser()
            
            response.success()

    @task(5)
    def lihat_dashboard(self):
        """Task paling sering: buka dashboard"""
        self.client.get("/dashboard", name="/dashboard")

    @task(3)
    def lihat_daftar_resi(self):
        """Lihat daftar resi dengan paginasi"""
        self.client.get("/shipments", name="/shipments")

    @task(2)
    def lacak_resi(self):
        """Lacak resi (publik)"""
        self.client.get("/lacak?resi=SLN260929015496", name="/lacak?resi=...")

    @task(1)
    def cek_tarif(self):
        """Buka halaman tarif"""
        self.client.get("/ongkir", name="/ongkir")

    @task(1)
    def filter_resi(self):
        """Filter resi dengan query string"""
        self.client.get("/shipments?q=SLN260929015496", name="/shipments?q=...")