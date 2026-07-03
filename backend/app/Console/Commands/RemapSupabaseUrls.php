<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Project;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\SupabaseStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemapSupabaseUrls extends Command
{
    protected $signature = 'storage:remap-supabase-urls
                            {--from=hiucucocvvhgmszgqnxc.supabase.co : Legacy Supabase host to replace}
                            {--dry-run : Show changes without writing}';

    protected $description = 'Rewrite stored media URLs from a dead Supabase project to SUPABASE_URL';

    public function handle(SupabaseStorage $storage): int
    {
        if (!$storage->isConfigured()) {
            $this->error('SUPABASE_URL and SUPABASE_SECRET_KEY must be set to a live project first.');

            return self::FAILURE;
        }

        $fromHost = strtolower((string) $this->option('from'));
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        $this->info('Target: ' . config('services.supabase.url'));
        $this->info('Replacing host: ' . $fromHost);
        if ($dryRun) {
            $this->warn('Dry run — no database writes.');
        }

        $remap = function (?string $value) use ($storage, $fromHost): ?string {
            if ($value === null || $value === '') {
                return $value;
            }

            if (!str_contains(strtolower($value), $fromHost)) {
                $resolved = $storage->resolvePublicUrl($value);

                return $resolved ?? $value;
            }

            $resolved = $storage->resolvePublicUrl($value);

            return $resolved ?? $value;
        };

        foreach (User::whereNotNull('avatar')->cursor() as $user) {
            $next = $remap($user->avatar);
            if ($next !== $user->avatar) {
                $this->line("user #{$user->id} avatar");
                if (!$dryRun) {
                    $user->update(['avatar' => $next]);
                }
                $updated++;
            }
        }

        foreach (TeamMember::whereNotNull('avatar')->cursor() as $member) {
            $next = $remap($member->avatar);
            if ($next !== $member->avatar) {
                $this->line("team_member #{$member->id} avatar");
                if (!$dryRun) {
                    $member->update(['avatar' => $next]);
                }
                $updated++;
            }
        }

        foreach (Project::whereNotNull('cover_image')->cursor() as $project) {
            if (($project->storage_provider ?? 'supabase') === 'cloudinary') {
                continue;
            }
            $next = $remap($project->cover_image);
            if ($next !== $project->cover_image) {
                $this->line("project #{$project->id} cover_image");
                if (!$dryRun) {
                    $project->update(['cover_image' => $next]);
                }
                $updated++;
            }
        }

        foreach (BlogPost::whereNotNull('cover_image')->cursor() as $post) {
            if (($post->storage_provider ?? 'supabase') === 'cloudinary') {
                continue;
            }
            $next = $remap($post->cover_image);
            $content = $storage->rewriteUrlsInHtml($post->content);
            $changed = $next !== $post->cover_image || $content !== $post->content;
            if ($changed) {
                $this->line("blog_post #{$post->id}");
                if (!$dryRun) {
                    $post->update([
                        'cover_image' => $next,
                        'content' => $content,
                    ]);
                }
                $updated++;
            }
        }

        $tables = [
            ['content_collections', 'cover_image'],
            ['series', 'cover_image'],
            ['media', 'path'],
        ];

        foreach ($tables as [$table, $column]) {
            if (!DB::getSchemaBuilder()->hasTable($table) || !DB::getSchemaBuilder()->hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->whereNotNull($column)
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($table, $column, $remap, $dryRun, &$updated) {
                    foreach ($rows as $row) {
                        $current = $row->{$column};
                        $next = $remap($current);
                        if ($next === $current) {
                            continue;
                        }
                        $this->line("{$table} #{$row->id} {$column}");
                        if (!$dryRun) {
                            DB::table($table)->where('id', $row->id)->update([$column => $next]);
                        }
                        $updated++;
                    }
                });
        }

        $this->info("Done. {$updated} record(s) " . ($dryRun ? 'would be ' : '') . 'updated.');

        return self::SUCCESS;
    }
}
