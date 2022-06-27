<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UsersController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $usersQuery = User::query();

        if ($request->queryString) {
            $usersQuery->where('name', 'LIKE', '%' . $request->queryString . '%');
        }

        $users = $usersQuery->whereNot('id', $request->user()->id)->get();

        return response()->success(
            UserResource::collection($users),
            'Users List Fetched',
            200
        );
    }
}
