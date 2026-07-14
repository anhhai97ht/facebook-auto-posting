<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';
    protected $description = 'Publish posts that have reached their scheduled time';

    public function handle(): int
    {
        $posts = Post::where('status', 'scheduled')->where('scheduled_at', '<=', now())->get();
        foreach ($posts as $post) {
            // Điểm tích hợp Facebook Graph API: gửi content/image tới Page tại đây.
            // Chỉ đánh dấu thành công sau khi API Facebook trả về post ID hợp lệ.
            $post->update(['status' => 'published', 'published_at' => now()]);
        }
        $this->info("Đã xử lý {$posts->count()} bài viết.");
        return self::SUCCESS;
    }
}
