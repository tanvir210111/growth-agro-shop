<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    public function index() {
        $teams = Team::orderBy('id', 'desc')->get();
        return view('admin.team.index', compact('teams'));
    }

    public function create() {
        return view('admin.team.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|max:191',
            'designation' => 'required|max:191',
            'photo' => 'required|mimes:jpeg,jpg,png,svg|max:2048',
        ]);

        $input = $request->all();
        if ($file = $request->file('photo')) {
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/team/', $name);
            $input['photo'] = $name;
        }

        Team::create($input);
        return redirect()->route('admin.team.index')->with('success', 'Team Member Added!');
    }

    public function edit($id) {
        $team = Team::findOrFail($id);
        return view('admin.team.edit', compact('team'));
    }

    public function update(Request $request, $id) {
        $team = Team::findOrFail($id);
        $input = $request->all();

        if ($file = $request->file('photo')) {
            if(file_exists(public_path('assets/images/team/'.$team->photo))){
                @unlink(public_path('assets/images/team/'.$team->photo));
            }
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/team/', $name);
            $input['photo'] = $name;
        }

        $team->update($input);
        return redirect()->route('admin.team.index')->with('success', 'Member Updated!');
    }

    public function destroy($id) {
        $team = Team::findOrFail($id);
        @unlink(public_path('assets/images/team/'.$team->photo));
        $team->delete();
        return redirect()->back()->with('success', 'Member Deleted!');
    }
}