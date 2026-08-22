<?php

namespace App\Http\Controllers;

use App\Models\User;
use Footup\Http\Request;
use Footup\Http\Response;
use Footup\Routing\Controller;

class UserController extends Controller
{
    public function index()
    {
        return $this->json(array_map(fn (User $user) => $user->toArray(), User::all()));
    }

    // {id} from the route is auto-bound to $id by name
    public function show(Request $request, string $id): Response
    {
        $user = User::find((int) $id);

        if (! $user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json($user->toArray());
    }

    public function store(Request $request): Response
    {
        $name = $request->input('name');

        if (! $name) {
            return $this->json(['error' => 'name is required'], 422);
        }

        $user = (new User(['name' => $name]))->save();

        return $this->json($user->toArray(), 201);
    }
}