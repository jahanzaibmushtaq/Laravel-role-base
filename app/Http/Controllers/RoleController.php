<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

   public function __construct()
    {
        $this->middleware('permission:view roles')->only('index');
        $this->middleware('permission:edit roles')->only('edit');
        $this->middleware('permission:create roles')->only('create');
        $this->middleware('permission:delete roles')->only('destroy');
    }

    //this method will show roles page
    public function index(){
        $roles = Role::orderBy('name', 'ASC')->paginate(10);
    return view('roles.list',['roles'=> $roles]);
    }

    //this method will show createroles page
    public function create(){
        $permission = Permission::orderBy('name', 'ASC')->get();
        return view('roles.create', [
            'permissions' => $permission
        ]);

    }

    //this method will insert roles in db 
    public function store(Request $request){
       $validator = Validator::make($request->all(), [
        'name' => 'required|unique:roles|min:3'
      ]);

      if($validator->passes()){
       $role = Role::create(['name' => $request->name]);

        if(!empty($request->permission)){
            foreach($request->permission as $name){
                     $role->givePermissionTo($name);
            }
        }

        return redirect()->route('roles.index')->with('success', 'Role added successfully.');

      } else {
        return redirect()->route('roles.create')->withInput()->withErrors($validator);
      }
    }


   public function edit($id){
         $role = Role::findOrFail($id);
         $hasPermissions = $role->permissions->pluck('name');
        $permission = Permission::orderBy('name', 'ASC')->get();

         return view('roles.edit', ['permissions'=> $permission, 'hasPermissions' => $hasPermissions, 'role' => $role]);
    }

    public function update($id, Request $request){
         $role = Role::findOrFail($id);

          $validator = Validator::make($request->all(), [
        'name' => 'required|unique:roles,name, '.$id.' ,id'
      ]);

      if($validator->passes()){
      $role->name=$request->name;
      $role->save();

        if(!empty($request->permission)){
          $role->syncPermissions($request->permission);
        }else{
          $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');

      } else {
        return redirect()->route('roles.edit', $id)->withInput()->withErrors($validator);
      }

    }

      public function destroy(Request $request){
       $id = $request->id;
      $role = Role::find($id);

      if($role == null){ 
        session()->flash('error', 'role not found');
        return response()->json([
          'status' => false
        ]);
      }

      $role->delete();

        session()->flash('success', 'Permission deleted successfully');
      return response()->json([
          'status' => true
        ]);
    }
}
