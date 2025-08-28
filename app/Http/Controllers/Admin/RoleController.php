<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\RoleRequest;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * UserRoleController constructor.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('permission:view role|create role|update role|delete role', ['only' => ['index', 'store']]);
        $this->middleware('permission:create role', ['only' => ['create', 'store']]);
        $this->middleware('permission:update role', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete role', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of roles.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $roles = Role::orderBy('id', 'DESC')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $permissionGroups = Permission::all()->groupBy('group');
        return view('admin.roles.create', compact('permissionGroups'));
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  \App\Http\Requests\Admin\RoleRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        try {
            $request->handle();
            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully');
        } catch (\Throwable $th) {
            Log::error('Role creation failed: ' . $th->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Show the specified role.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\View\View
     */
    public function show(Role $role): View
    {
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\View\View
     */
    public function edit(Role $role): View
    {
        $permissionGroups = Permission::all()->groupBy('group');
        return view('admin.roles.edit', compact('role', 'permissionGroups'));
    }

    /**
     * Update the specified role in storage.
     *
     * @param  \App\Http\Requests\Admin\RoleRequest  $request
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $request->handle($role);
            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully');
        } catch (\Throwable $th) {
            Log::error('Role update failed: ' . $th->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Remove the specified role from storage.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        try {
            $role->permissions()->detach();
            $role->delete();
            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully');
        } catch (\Throwable $th) {
            Log::error('Role deletion failed: ' . $th->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
