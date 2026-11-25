<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\LearningService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create(['name' => 'Demo Admin', 'email' => 'admin@demo.test', 'password' => 'demo1234', 'role' => 'admin']);
        $teacher = User::create(['name' => 'Aiko Tanaka', 'email' => 'instructor@demo.test', 'password' => 'demo1234', 'role' => 'instructor']);
        $student = User::create(['name' => 'Demo Student', 'email' => 'student@demo.test', 'password' => 'demo1234', 'role' => 'student']);
        $other = User::create(['name' => 'Minh Pham', 'email' => 'minh.instructor@example.com', 'password' => str()->random(32), 'role' => 'instructor']);

        foreach ($this->courses() as $i => $c) {
            $course = Course::create([
                'instructor_id' => $i % 2 === 0 ? $teacher->id : $other->id,
                'slug' => $c['slug'],
                'title' => $c['title'],
                'summary' => $c['summary'],
                'level' => $c['level'],
                'color' => $c['color'],
                'pass_score' => 70,
                'is_published' => $c['published'] ?? true,
            ]);

            foreach ($c['lessons'] as $p => [$title, $body, $minutes]) {
                $course->lessons()->create(['position' => $p + 1, 'title' => $title, 'body' => $body, 'duration_minutes' => $minutes]);
            }

            foreach ($c['questions'] as $p => [$prompt, $options, $correct]) {
                $course->questions()->create(['position' => $p + 1, 'prompt' => $prompt, 'options' => $options, 'correct_option' => $correct]);
            }
        }

        $this->sampleLearners($student);
    }

    /** 40 sample learners with realistic progress so dashboards have data. */
    private function sampleLearners(User $demoStudent): void
    {
        $learning = app(LearningService::class);
        $names = ['Sato Haruto', 'Nguyen Lan', 'Emma Brown', 'Suzuki Yui', 'Tran Duc', 'Liam Smith', 'Ito Ren', 'Le Mai', 'Olivia Jones', 'Kato Mio'];
        $courses = Course::published()->with('lessons', 'questions')->get();
        mt_srand(42);

        $learners = collect(range(1, 40))->map(fn ($n) => User::create([
            'name' => $names[$n % 10].' '.$n,
            'email' => 'learner'.$n.'@example.com',
            'password' => Str::random(32),
            'role' => 'student',
        ]))->push($demoStudent);

        foreach ($learners as $user) {
            foreach ($courses->random(mt_rand(1, $courses->count())) as $course) {
                $enrollment = $learning->enroll($user, $course);
                $enrollment->forceFill(['created_at' => now()->subDays(mt_rand(1, 40))])->save();

                $done = mt_rand(0, $course->lessons->count());
                DB::table('lesson_completions')->insert($course->lessons->take($done)->map(fn ($l) => [
                    'user_id' => $user->id, 'lesson_id' => $l->id, 'created_at' => now()->subDays(mt_rand(0, 20)),
                ])->all());

                if ($done === $course->lessons->count() && $course->questions->isNotEmpty()) {
                    $answers = $course->questions->mapWithKeys(fn ($q) => [$q->id => mt_rand(1, 100) <= 80 ? $q->correct_option : ($q->correct_option + 1) % count($q->options)])->all();
                    $learning->submitQuiz($user, $course, $answers);
                }

                $learning->refreshProgress($enrollment->fresh());
            }
        }
    }

    private function courses(): array
    {
        $t = fn ($en, $ja, $vi) => ['en' => $en, 'ja' => $ja, 'vi' => $vi];

        return [
            [
                'slug' => 'laravel-basics', 'level' => 'beginner', 'color' => '#dc2626',
                'title' => $t('Laravel basics in one afternoon', '半日で学ぶ Laravel 入門', 'Laravel cơ bản trong một buổi chiều'),
                'summary' => $t('Routes, controllers, Blade and Eloquent: build a small CRUD app step by step.', 'ルーティング、コントローラ、Blade、Eloquent を使って小さな CRUD アプリを作ります。', 'Route, controller, Blade và Eloquent: từng bước xây một ứng dụng CRUD nhỏ.'),
                'lessons' => [
                    [$t('Routing and controllers', 'ルーティングとコントローラ', 'Routing và controller'), $t("Every request starts in `routes/web.php`.\n\n```php\nRoute::get('/posts', [PostController::class, 'index']);\n```\n\nKeep controllers thin: validate input, call a service, return a view or redirect.", "すべてのリクエストは `routes/web.php` から始まります。\n\n```php\nRoute::get('/posts', [PostController::class, 'index']);\n```\n\nコントローラは薄く保ち、入力チェック・サービス呼び出し・レスポンスだけを担当させます。", "Mọi request bắt đầu từ `routes/web.php`.\n\n```php\nRoute::get('/posts', [PostController::class, 'index']);\n```\n\nGiữ controller gọn: validate, gọi service, trả về view hoặc redirect."), 8],
                    [$t('Blade templates', 'Blade テンプレート', 'Template Blade'), $t("Blade compiles to plain PHP and escapes output by default with `{{ }}`.\n\nUse layouts with `@extends` and `@section`, and components for repeated UI.", "Blade は PHP にコンパイルされ、`{{ }}` で自動エスケープされます。\n\n`@extends` と `@section` でレイアウトを共通化し、繰り返す UI はコンポーネントにします。", "Blade được biên dịch thành PHP và `{{ }}` tự escape dữ liệu.\n\nDùng `@extends`, `@section` cho layout và component cho phần giao diện lặp lại."), 10],
                    [$t('Eloquent models', 'Eloquent モデル', 'Model Eloquent'), $t("Models map tables to classes. Define relationships and always eager load what a page needs:\n\n```php\nPost::with('author')->latest()->paginate(20);\n```", "モデルはテーブルとクラスを対応させます。リレーションを定義し、ページで使うデータは必ず eager load します。\n\n```php\nPost::with('author')->latest()->paginate(20);\n```", "Model ánh xạ bảng sang class. Khai báo quan hệ và luôn eager load dữ liệu trang cần:\n\n```php\nPost::with('author')->latest()->paginate(20);\n```"), 12],
                    [$t('Validation and forms', 'バリデーションとフォーム', 'Validation và form'), $t("`\$request->validate()` returns only validated data and redirects back with errors on failure. Never trust prices or IDs coming from the browser.", "`\$request->validate()` は検証済みデータだけを返し、失敗時はエラー付きで戻ります。ブラウザから来る価格や ID は信用しないこと。", "`\$request->validate()` chỉ trả về dữ liệu hợp lệ và tự quay lại kèm lỗi. Đừng tin giá hay ID gửi từ trình duyệt."), 9],
                ],
                'questions' => [
                    [$t('Which file defines web routes?', 'Web ルートを定義するファイルは？', 'File nào khai báo web route?'), [$t('routes/web.php', 'routes/web.php', 'routes/web.php'), $t('config/app.php', 'config/app.php', 'config/app.php'), $t('public/index.php', 'public/index.php', 'public/index.php')], 0],
                    [$t('What does {{ }} do in Blade?', 'Blade の {{ }} の役割は？', '{{ }} trong Blade làm gì?'), [$t('Runs raw PHP', 'PHP をそのまま実行', 'Chạy PHP thô'), $t('Echoes escaped output', 'エスケープして出力', 'In ra đã được escape'), $t('Includes a file', 'ファイルを読み込む', 'Nhúng một file')], 1],
                    [$t('How do you avoid N+1 queries?', 'N+1 問題を避けるには？', 'Làm sao tránh truy vấn N+1?'), [$t('Use with() to eager load', 'with() で eager load', 'Dùng with() để eager load'), $t('Add more indexes', 'インデックスを増やす', 'Thêm index'), $t('Use raw SQL', '生 SQL を使う', 'Dùng SQL thô')], 0],
                ],
            ],
            [
                'slug' => 'business-japanese-email', 'level' => 'intermediate', 'color' => '#7c3aed',
                'title' => $t('Business Japanese for email', 'ビジネスメールの日本語', 'Tiếng Nhật thương mại qua email'),
                'summary' => $t('Openings, requests and apologies that sound natural to Japanese clients.', '日本のお客様に自然に伝わる書き出し、依頼、お詫びの表現。', 'Mở đầu, nhờ vả và xin lỗi tự nhiên với khách hàng Nhật.'),
                'lessons' => [
                    [$t('Opening lines', '書き出し', 'Câu mở đầu'), $t("Start with **いつもお世話になっております。** followed by your company and name.", "**いつもお世話になっております。** の後に会社名と名前を書きます。", "Bắt đầu bằng **いつもお世話になっております。** rồi tới tên công ty và tên bạn."), 6],
                    [$t('Making requests', '依頼の表現', 'Cách nhờ vả'), $t("Soften requests: **ご確認いただけますでしょうか。** is more polite than **確認してください。**", "**確認してください。** より **ご確認いただけますでしょうか。** の方が丁寧です。", "Nói mềm hơn: **ご確認いただけますでしょうか。** lịch sự hơn **確認してください。**"), 7],
                    [$t('Apologising for delays', '遅延のお詫び', 'Xin lỗi khi trễ hạn'), $t("**ご連絡が遅くなり、申し訳ございません。** Then give the reason briefly and the new date.", "**ご連絡が遅くなり、申し訳ございません。** その後、理由を簡潔に、新しい日程を伝えます。", "**ご連絡が遅くなり、申し訳ございません。** Sau đó nêu ngắn gọn lý do và ngày mới."), 8],
                ],
                'questions' => [
                    [$t('Most common email opening?', '最も一般的な書き出しは？', 'Câu mở đầu email phổ biến nhất?'), [$t('おはよう', 'おはよう', 'おはよう'), $t('いつもお世話になっております', 'いつもお世話になっております', 'いつもお世話になっております'), $t('よろしく', 'よろしく', 'よろしく')], 1],
                    [$t('Which request is most polite?', '最も丁寧な依頼は？', 'Cách nhờ nào lịch sự nhất?'), [$t('確認して', '確認して', '確認して'), $t('確認してください', '確認してください', '確認してください'), $t('ご確認いただけますでしょうか', 'ご確認いただけますでしょうか', 'ご確認いただけますでしょうか')], 2],
                ],
            ],
            [
                'slug' => 'sql-performance', 'level' => 'advanced', 'color' => '#0891b2',
                'title' => $t('SQL performance for web apps', 'Web アプリの SQL パフォーマンス', 'Tối ưu SQL cho ứng dụng web'),
                'summary' => $t('Read EXPLAIN, design composite indexes and cache the right things.', 'EXPLAIN の読み方、複合インデックス設計、キャッシュ戦略。', 'Đọc EXPLAIN, thiết kế index kết hợp và cache đúng chỗ.'),
                'lessons' => [
                    [$t('Reading EXPLAIN', 'EXPLAIN を読む', 'Đọc EXPLAIN'), $t("Look at `type`, `key` and `rows`. A full scan (`ALL`) on a big table is the first thing to fix.", "`type`、`key`、`rows` を確認します。大きなテーブルでの `ALL`（フルスキャン）は最優先で直します。", "Xem `type`, `key`, `rows`. Quét toàn bảng (`ALL`) trên bảng lớn là thứ cần sửa đầu tiên."), 12],
                    [$t('Composite indexes', '複合インデックス', 'Index kết hợp'), $t("Put equality columns first, then the range or sort column: `INDEX (course_id, completed_at)`.", "等価条件の列を先に、範囲・ソートの列を後に：`INDEX (course_id, completed_at)`。", "Cột so sánh bằng đặt trước, cột range/sort đặt sau: `INDEX (course_id, completed_at)`."), 14],
                    [$t('Caching read-heavy pages', '読み取りが多いページのキャッシュ', 'Cache trang đọc nhiều'), $t("Cache catalog pages with a version key and bump the version on writes. No need to hunt for every stale key.", "バージョンキーでカタログをキャッシュし、更新時にバージョンを上げます。古いキーを探して消す必要はありません。", "Cache trang danh mục bằng version key và tăng version khi ghi. Không cần đi xoá từng key cũ."), 10],
                ],
                'questions' => [
                    [$t('Which EXPLAIN type means a full table scan?', 'フルスキャンを表す type は？', 'Type nào trong EXPLAIN là quét toàn bảng?'), [$t('ref', 'ref', 'ref'), $t('ALL', 'ALL', 'ALL'), $t('const', 'const', 'const')], 1],
                    [$t('In a composite index, the range column goes…', '複合インデックスで範囲条件の列は…', 'Trong index kết hợp, cột range đặt…'), [$t('first', '先頭', 'đầu tiên'), $t('after equality columns', '等価条件の列の後', 'sau các cột so sánh bằng'), $t('anywhere', 'どこでもよい', 'ở đâu cũng được')], 1],
                ],
            ],
            [
                'slug' => 'vue-for-laravel-devs', 'level' => 'intermediate', 'color' => '#16a34a', 'published' => false,
                'title' => $t('Vue 3 for Laravel developers (draft)', 'Laravel 開発者のための Vue 3（下書き）', 'Vue 3 cho dev Laravel (bản nháp)'),
                'summary' => $t('A draft course, visible only to its instructor and admins.', '講師と管理者だけが見られる下書きコース。', 'Khoá học nháp, chỉ giảng viên và admin thấy.'),
                'lessons' => [[$t('Why Vue with Laravel', 'なぜ Laravel と Vue か', 'Vì sao Vue với Laravel'), $t('Coming soon.', '準備中です。', 'Sắp ra mắt.'), 5]],
                'questions' => [],
            ],
        ];
    }
}
