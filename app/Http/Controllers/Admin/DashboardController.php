<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditEvent;
use App\Models\Badge;
use App\Models\Company;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'صفحه‌ها' => Page::count(),
                'خبرها' => Post::count(),
                'شرکت‌ها' => Company::count(),
                'کارت سینهٔ در انتظار تأیید' => Badge::where('status', 'pending')->count(),
            ],
            'events' => auth()->user()->isAdmin() ? AuditEvent::latest('id')->limit(10)->get() : collect(),
        ]);
    }
}
