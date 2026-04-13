<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\BlogPost;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $totalProjects = $isSuperAdmin
            ? Project::count()
            : Project::where('created_by', $user->id)->count();

        $totalBlogPosts = $isSuperAdmin
            ? BlogPost::where('status', 'published')->count()
            : BlogPost::where('status', 'published')->where('author_id', $user->id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => User::count(),
                'total_projects' => $totalProjects,
                'total_blog_posts' => $totalBlogPosts,
                'unread_messages' => Message::where('is_read', false)->count(),
                'pending_applications' => Application::where('status', 'pending')->count(),
            ],
        ]);
    }

    public function activity(Request $request): JsonResponse
    {
        $user = $request->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $query = ActivityLog::with('user')->orderByDesc('created_at')->limit(15);

        if (!$isSuperAdmin) {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }
}
