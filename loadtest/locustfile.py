"""
Load test for the Manabi LMS demo.

Simulates students who browse the catalog, open a course, read lessons and
take the quiz. Run against a deployed instance, for example:

    locust -f loadtest/locustfile.py --host https://<your-domain> \
        --users 500 --spawn-rate 25 --run-time 5m --headless --csv results/run500

Each virtual user logs in through the one-click demo student account, so no
real credentials are needed.
"""
import random
import re

from locust import HttpUser, between, task

COURSES = ["laravel-basics", "business-japanese-email", "sql-performance"]
CSRF = re.compile(r'name="_token" value="([^"]+)"')


class Student(HttpUser):
    wait_time = between(1, 4)

    def on_start(self):
        page = self.client.get("/login", name="/login")
        token = CSRF.search(page.text).group(1)
        self.client.post("/demo-login/student", data={"_token": token}, name="/demo-login/student")
        self.token = token

    @task(5)
    def browse_catalog(self):
        self.client.get("/", name="/ (catalog)")

    @task(4)
    def open_course(self):
        self.client.get(f"/courses/{random.choice(COURSES)}", name="/courses/[slug]")

    @task(6)
    def read_lesson(self):
        course = random.choice(COURSES)
        self.client.get(f"/courses/{course}/lessons/{random.randint(1, 3)}", name="/courses/[slug]/lessons/[n]")

    @task(2)
    def dashboard(self):
        self.client.get("/my-learning", name="/my-learning")

    @task(1)
    def quiz(self):
        page = self.client.get("/courses/laravel-basics/quiz", name="/courses/[slug]/quiz")
        ids = sorted(set(re.findall(r'name="answers\[(\d+)\]"', page.text)))
        token = CSRF.search(page.text)
        if ids and token:
            data = {"_token": token.group(1)}
            data.update({f"answers[{i}]": random.randint(0, 2) for i in ids})
            self.client.post("/courses/laravel-basics/quiz", data=data, name="POST quiz")
