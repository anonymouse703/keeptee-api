<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TagResource;
use App\Repositories\Contracts\TagRepositoryInterface;

class TagController extends Controller
{
    public function __construct(protected TagRepositoryInterface $tagRepository)
    {}

    public function index()
    {
        $tags = $this->tagRepository
                ->paginate();

        return Inertia::render('tags/Index', [
            'tags' => TagResource::collection($tags),
        ]);
    }
}
