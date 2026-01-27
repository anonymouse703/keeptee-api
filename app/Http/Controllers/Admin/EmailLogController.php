<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\EmailLogResource;
use App\Models\EmailLog;
use App\Repositories\Contracts\EmailLogRepositoryInterface;

class EmailLogController extends Controller
{
    public function __construct(protected EmailLogRepositoryInterface $emailLogRepository)
    {}

    public function index()
    {
        $emailLogs = $this->emailLogRepository->paginate();

        return Inertia::render('email-logs/Index', [
            'emailLogs' => EmailLogResource::collection($emailLogs),
        ]);
    }

    public function show(EmailLog $emailLog)
    {
        $emailLog->load(['user']);

        return Inertia::render('email-logs/Show', [
            'emailLog' => $emailLog,
        ]);
    }
}
