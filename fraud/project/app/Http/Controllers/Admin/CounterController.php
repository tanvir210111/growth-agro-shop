<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller {
    public function index() {
        $counters = Counter::all();
        return view('admin.counter.index', compact('counters'));
    }

    public function create() {
        return view('admin.counter.create');
    }

    public function store(Request $request) {
        $request->validate(['title' => 'required', 'count_value' => 'required']);
        Counter::create($request->all());
        return redirect()->route('admin.counter.index')->with('success', 'Counter added successfully');
    }

    public function edit($id) {
        $counter = Counter::findOrFail($id);
        return view('admin.counter.edit', compact('counter'));
    }

    public function update(Request $request, $id) {
        $counter = Counter::findOrFail($id);
        $counter->update($request->all());
        return redirect()->route('admin.counter.index')->with('success', 'Counter updated successfully');
    }

    public function destroy($id) {
        Counter::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted successfully');
    }
}