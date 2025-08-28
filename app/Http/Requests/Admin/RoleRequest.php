<?php

namespace App\Http\Requests\Admin;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('create role') || Auth::user()->can('update role');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:255|unique:roles,name,' . $this->route('role')->id,
            'permissions' => 'required|array',
        ];
    }

    /**
     * Handle the role creation or update.
     */
    public function handle(Role $role = null)
    {
        if ($role) {
            $this->update($role, $this->validated());
        } else {
            $role = $this->store($this->validated());
        }
        return $role;
    }

    /**
     * Create a new role and sync permissions.
     */
    public function store($data)
    {
        $permissionsID = array_map(
            function ($value) {
                return (int)$value;
            },
            $data['permissions']
        );
        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($permissionsID);
        return $role;
    }

    /**
     * Update the specified role with new data and sync its permissions.
     *
     * @param \Spatie\Permission\Models\Role $role The role to update.
     * @param array $data The new data for the role, including permissions.
     * @return \Spatie\Permission\Models\Role The updated role.
     */
    public function update(Role $role, $data)
    {
        $permissionsID = array_map(
            function ($value) {
                return (int)$value;
            },
            $data['permissions']
        );
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($permissionsID);
        return $role;
    }
}
