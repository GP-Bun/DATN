<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(){
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create(){
        return view('admin.users.create');
    }

    public function store(Request $request){
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:6|confirmed',
            'role'=>'required|in:admin,user'
        ]);

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'role'=>$request->role
        ]);

        return redirect()->route('admin.users.index')->with('success','Thêm tài khoản thành công!');
    }

    public function edit(User $user){
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user){
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>"required|email|unique:users,email,$user->id",
            'password'=>'nullable|string|min:6|confirmed',
            'role'=>'required|in:admin,user'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        if($request->password){
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('success','Cập nhật tài khoản thành công!');
    }

    public function destroy(User $user){
        $user->delete();
        return redirect()->route('admin.users.index')->with('success','Xóa tài khoản thành công!');
    }
}
