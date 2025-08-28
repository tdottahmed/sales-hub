<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Admin\PermissionRequest;

class PermissionController extends Controller
{

    public function __construct()
    {
        $permissions = [
            'view permission' => ['index'],
            'create permission' => ['create', 'store'],
            'update permission' => ['edit', 'update'],
            'delete permission' => ['destroy'],
        ];

        foreach ($permissions as $permission => $actions) {
            $this->middleware("permission:{$permission}", ['only' => $actions]);
        }
    }

    /**
     * Display a listing of permissions.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $permissions = Permission::all();
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created permission in database.
     *
     * @param  \App\Http\Requests\Admin\PermissionRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PermissionRequest $request)
    {
        try {
            $request->handle();
            return $this->redirectWithSuccess('Permission created successfully');
        } catch (\Throwable $th) {
            Log::error('Permission creation failed: ' . $th->getMessage());
            return $this->redirectWithError('Something went wrong. Please try again.');
        }
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }


    public function update(PermissionRequest $request, Permission $permission)
    {
        try {
            $request->handle($permission);
            $this->redirectWithSuccess('Permission updated successfully');
        } catch (\Throwable $th) {
            Log::error('Permission update failed: ' . $th->getMessage());
            return $this->redirectWithError('Something went wrong. Please try again.');
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            $permission->delete();
            return $this->redirectWithSuccess('Permission deleted successfully');
        } catch (\Throwable $th) {
            Log::error('Permission deletion failed: ' . $th->getMessage());
            return $this->redirectWithError('Something went wrong. Please try again.');
        }
    }

    private function redirectWithSuccess($message)
    {
        return redirect()->route('permissions.index')
            ->with('success', $message);
    }

    private function redirectWithError($message)
    {
        return back()->with('error', $message);
    }
}
