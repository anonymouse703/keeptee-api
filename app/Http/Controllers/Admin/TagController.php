<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\StoreRequest;
use App\Http\Requests\Admin\Tag\UpdateRequest;
use App\Http\Resources\Admin\TagResource;
use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;

class TagController extends Controller
{
    public function __construct(protected TagRepositoryInterface $tagRepository)
    {}

    public function index()
    {
        $tags = $this->tagRepository->paginate();

        return Inertia::render('tags/Index', [
            'tags' => TagResource::collection($tags),
        ]);
    }

    public function create()
    {
        return Inertia::render('tags/Create');
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->validated();

        $tag = new Tag();
        $tag->forceFill($payload);

        try {
            $this->tagRepository->save($tag);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('tags.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Tag successfully created.'),
            ]);
    }

    public function edit(Tag $tag)
    {
        return Inertia::render('tags/Edit', [
            'tag' => new TagResource($tag),
        ]);
    }


    public function update(UpdateRequest $request, Tag $tag)
    {
        $payload = $request->validated();

        $tag->forceFill($payload);

        try {
            $this->tagRepository->save($tag);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('tags.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('Tag successfully updated.'),
            ]);
    }

    public function destroy(Tag $tag)
    {
        try {
            $this->tagRepository->delete($tag);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('tags.index')
            ->with('flash', [
                'type' => 'danger',
                'message' => __('Tag successfully deleted.'),
            ]);
    }

    public function toggleStatus(Tag $tag)
    {
        $tag->update([
            'is_active' => ! $tag->is_active,
        ]);

         return redirect()
            ->route('tags.index')
            ->with('flash', [
                'type' => 'success', 
                'message' => __('Tag status updated.'), 
            ]);
    }

}