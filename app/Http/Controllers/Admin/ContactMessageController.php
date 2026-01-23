<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ContactMessageResource;
use App\Models\ContactMessage;
use App\Repositories\Contracts\ContactMessageRepositoryInterface;

class ContactMessageController extends Controller
{
    public function __construct(protected ContactMessageRepositoryInterface $contactMessageRepository)
    {}

    public function index()
    {
        $contactMessages = $this->contactMessageRepository->paginate();

        return Inertia::render('contact-messages/Index', [
            'contactMessages' => ContactMessageResource::collection($contactMessages),
        ]);
    }

    public function show(ContactMessage $contactMessage)
    {
        $contactMessage->load(['user']);

        return Inertia::render('contact-messages/Show', [
            'contactMessage' => $contactMessage,
        ]);
    }
}
