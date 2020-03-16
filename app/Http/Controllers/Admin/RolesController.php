<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\Role;
use Buzzex\Models\Permission;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    /**
     * Allowed guards guards
     *
     * @return void
     */
    protected $guards = array(
            'api' => 'api',
            'channel' => 'channel',
            'web' => 'web'
        );

    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 15;

        if (!empty($keyword)) {
            $roles = Role::where('name', 'LIKE', "%$keyword%")->orWhere('guard_name', 'LIKE', "%$keyword%")
                ->latest()->paginate($perPage);
        } else {
            $roles = Role::latest()->paginate($perPage);
        }

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        $permissions = Permission::select('id', 'name', 'guard_name')->get()->pluck('name', 'name');
        $guards = $this->guards;

        return view('admin.roles.create', compact('permissions', 'guards'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:roles,name',
            'guard_name' => 'required|in:'.implode(",", $this->guards)
        ]);

        $role = Role::create($request->except('permissions'));
        $role->permissions()->detach();

        if ($request->has('permissions')) {
            foreach ($request->permissions as $permission_name) {
                $permission = Permission::whereName($permission_name)->first();
                if ($permissions) {
                    $role->givePermissionTo($permission);
                }
            }
        }
        toast('Role added!', 'success', 'top-right');

        return redirect('admin/roles');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return void
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return void
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::select('id', 'name', 'guard_name')->get()->pluck('name', 'name');
        $guards = $this->guards;

        return view('admin.roles.edit', compact('role', 'permissions', 'guards'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return void
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:roles,name,'.$id,
            'guard_name' => 'required|in:'.implode(",", $this->guards)
        ]);

        $role = Role::findOrFail($id);
        $role->update($request->except('permissions'));
        $role->permissions()->detach();

        if ($request->has('permissions')) {
            foreach ($request->permissions as $permission_name) {
                $permission = Permission::whereName($permission_name)->first();
                if ($permissions) {
                    $role->givePermissionTo($permission);
                }
            }
        }
        
        toast('Role updated!', 'success', 'top-right');

        return redirect('admin/roles');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return void
     */
    public function destroy($id)
    {
        Role::destroy($id);

        toast('Role deleted!', 'success', 'top-right');

        return redirect('admin/roles');
    }
}
