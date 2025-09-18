<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Role;

class UserRoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return RoleResource::collection($roles);
    }


    public function show($roleId)
    {
        $role = Role::where('id', $roleId)
            ->firstOrFail();
        return new RoleResource($role);
    }

    public function store(StoreRoleRequest $request)
    {
        return new RoleResource(Role::create($request->validated()));
    }

    public function update(UpdateRoleRequest $request, $roleId)
    {
        $role = Role::where('id', $roleId)->firstOrFail();
        $role->update($request->validated());
        return new RoleResource($role);
    }

    public function destroy($roleId)
    {
        $role = Role::where('id', $roleId)->firstOrFail();
        $role->delete();
        return response()->json([
            'message' => 'Товар удален'
        ]);
    }
}
