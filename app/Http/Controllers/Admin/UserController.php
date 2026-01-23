<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserController extends Controller
{
    public function __construct(protected UserRepositoryInterface $userRepository)
    {}

    public function index()
    {
        $users = $this->userRepository->paginate();

        return Inertia::render('users/Index', [
            'users' => UserResource::collection($users),
        ]);
    }

    public function create()
    {
        return Inertia::render('tags/Create');
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->validated();

     $user = new User();
     $user->forceFill($payload);

        try {
            $this->userRepository->save($user);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('users.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('User successfully created.'),
            ]);
    }

    public function show(User $user)
    {
        return Inertia::render('users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('users/Edit', [
            'user' => new UserResource($user),
        ]);
    }


    public function update(UpdateRequest $request, User $user)
    {
        $payload = $request->validated();

     $user->forceFill($payload);

        try {
            $this->userRepository->save($user);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('users.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('User successfully updated.'),
            ]);
    }

    public function destroy(User $user)
    {
        try {
            $this->userRepository->delete($user);
        } catch (Exception $exception) {
            report($exception);
        }

        return redirect()
            ->route('users.index')
            ->with('flash', [
                'type' => 'danger',
                'message' => __('User successfully deleted.'),
            ]);
    }

    public function toggleStatus(User $user)
    {
     $user->update([
            'is_active' => ! $user->is_active,
        ]);

         return redirect()
            ->route('users.index')
            ->with('flash', [
                'type' => 'success', 
                'message' => __('User status updated.'), 
            ]);
    }

}
