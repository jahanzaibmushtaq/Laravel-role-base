<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

     public function __construct()
    {
        $this->middleware('permission:view permissions')->only('index');
        $this->middleware('permission:edit permissions')->only('edit');
        $this->middleware('permission:create permissions')->only('create');
        $this->middleware('permission:delete permissions')->only('destroy');
    }

    //this method will show permission page
    public function index(){
        $permissions = Permission::orderBy('created_at', 'DESC')->paginate(10);
        return view('permissions.list', [
            'permissions' => $permissions,
        ]);

    }

    //this method will show createpermission page
    public function create(){
        return view('permissions.create');

    }

    //this method will insert a permission in db
    public function store(Request $request){
      $validator = Validator::make($request->all(), [
        'name' => 'required|unique:permissions|min:3'
      ]);

      if($validator->passes()){
        Permission::create(['name' => $request->name]);
        return redirect()->route('permissions.index')->with('success', 'Permission added successfully.');

      } else {
        return redirect()->route('permissions.create')->withInput()->withErrors($validator);
      }

    }

    //this method will edit permission page
    public function edit($id){
     $permission = Permission::findOrFail($id);
        return view('permissions.edit', [
            'permission' => $permission
        ]);

    }

    //this method will update a permission page
    public function update($id, Request $request){
       $permission = Permission::findOrFail($id);
         $validator = Validator::make($request->all(), [
        'name' => 'required|min:3|unique:permissions,name, ' .$id. ' ,id'
      ]);

      if($validator->passes()){
        $permission->name= $request->name;
        $permission->save();

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');

      } else {
        return redirect()->route('permissions.edit', $id)->withInput()->withErrors($validator);
      }

    }

    //this method will destroy/delete a permission in db
    public function destroy(Request $request){
      $id = $request-> id;
      $permission = Permission::find($id);

      if($permission == null){
        session()->flash('error', 'Permission not found');
        return response()->json([
          'status' => false
        ]);
      }

      $permission->delete();
        session()->flash('success', 'Permission deleted successfully');
      return response()->json([
          'status' => true
        ]);
    }
}
