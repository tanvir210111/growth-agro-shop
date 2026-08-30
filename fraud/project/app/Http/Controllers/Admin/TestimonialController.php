<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller {
    public function index() {
        $testimonials = Testimonial::orderBy('id', 'desc')->get();
        return view('admin.testimonial.index', compact('testimonials'));
    }

    public function create() {
        return view('admin.testimonial.create');
    }

    public function store(Request $request) {
        $request->validate(['name' => 'required', 'photo' => 'required|image']);
        $input = $request->all();
        if ($file = $request->file('photo')) {
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/testimonials/', $name);
            $input['photo'] = $name;
        }
        Testimonial::create($input);
        return redirect()->route('admin.testimonial.index')->with('success', 'Testimonial added!');
    }

    public function edit($id) {
        $testimonial = Testimonial::findOrFail($id);
        return view('admin.testimonial.edit', compact('testimonial'));
    }

    public function update(Request $request, $id) {
        $testimonial = Testimonial::findOrFail($id);
        $input = $request->all();
        if ($file = $request->file('photo')) {
            @unlink(public_path('assets/images/testimonials/'.$testimonial->photo));
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/testimonials/', $name);
            $input['photo'] = $name;
        }
        $testimonial->update($input);
        return redirect()->route('admin.testimonial.index')->with('success', 'Updated!');
    }

    public function destroy($id) {
        $testimonial = Testimonial::findOrFail($id);
        @unlink(public_path('assets/images/testimonials/'.$testimonial->photo));
        $testimonial->delete();
        return redirect()->back()->with('success', 'Deleted!');
    }
}