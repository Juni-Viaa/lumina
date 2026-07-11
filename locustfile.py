from locust import HttpUser, task, between
from bs4 import BeautifulSoup
import random

QUESTIONS = [
    "Apa tujuan PBL?",
    "Apa saja capaian pembelajaran?",
    "Bagaimana sistem penilaian?",
    "Jelaskan tahapan pelaksanaan PBL.",
    "Siapa yang menjadi pembimbing?"
]


class RagUser(HttpUser):

    host = "http://127.0.0.1:8000"

    wait_time = between(2, 5)

    def on_start(self):

        nomor = random.randint(1, 50)

        self.username = f"user{nomor:02d}"
        self.password = "password123"

        # ==========================================
        # STEP 1 : GET LOGIN
        # ==========================================

        response = self.client.get("/login")

        soup = BeautifulSoup(response.text, "html.parser")

        token_input = soup.find("input", {"name": "_token"})

        if token_input is None:
            print("❌ Tidak menemukan CSRF pada halaman login")
            return

        login_csrf = token_input["value"]

        # ==========================================
        # STEP 2 : LOGIN
        # ==========================================

        login = self.client.post(
            "/login",
            data={
                "_token": login_csrf,
                "username": self.username,
                "password": self.password,
            },
            allow_redirects=True,
        )

        if login.status_code != 200:
            print("❌ LOGIN GAGAL :", login.status_code)
            return

        print("✅ LOGIN BERHASIL :", self.username)

        # ==========================================
        # STEP 3 : BUKA DASHBOARD
        # ==========================================

        dashboard = self.client.get("/")

        soup = BeautifulSoup(dashboard.text, "html.parser")

        meta = soup.find("meta", {"name": "csrf-token"})

        if meta is None:
            print("❌ META CSRF TIDAK DITEMUKAN")
            return

        self.csrf = meta["content"]

        print("✅ CSRF BARU :", self.csrf[:20], "...")

        print("===== COOKIE =====")

        for cookie in self.client.cookies:
            print(cookie)

        print("==================")

    @task
    def ask_question(self):

        question = random.choice(QUESTIONS)

        with self.client.post(
            "/ask",
            json={
                "question": question
            },
            headers={
                "Accept": "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": self.csrf,
                "X-Requested-With": "XMLHttpRequest",
            },
            catch_response=True,
        ) as response:

            print("\n========== REQUEST ==========")
            print("Question :", question)
            print("Status   :", response.status_code)

            if response.status_code != 200:

                print("BODY :")
                print(response.text)
                print("=============================\n")

                response.failure(f"HTTP {response.status_code}")
                return

            try:
                data = response.json()

            except Exception:

                print("❌ RESPONSE BUKAN JSON")
                print(response.text)

                response.failure("Response bukan JSON")
                return

            if data.get("error"):

                print("❌ ERROR DARI LARAVEL")
                print(data)

                response.failure(data["error"])
                return

            if not data.get("answer"):

                print("❌ JAWABAN KOSONG")
                print(data)

                response.failure("Jawaban kosong")
                return

            print("✅ QUERY BERHASIL")
            print("Response Time :", data.get("response_time_ms"))

            response.success()