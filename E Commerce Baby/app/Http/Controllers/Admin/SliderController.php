<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Models\Category;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('sort_order', 'asc')->get();
        $categories = Category::where('status', true)->get();
        return view('admin.pages.sliders.index', compact('sliders', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = '';
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'slider_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/sliders'), $filename);
            $imagePath = 'uploads/sliders/' . $filename;
        }

        Slider::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'image' => $imagePath,
            'link' => $request->link ?: '/shop',
            'button_text' => $request->button_text ?: 'SHOP NOW >',
            'sort_order' => (int) ($request->sort_order ?? (Slider::count() + 1)),
            'status' => true,
        ]);

        return back()->with('success', 'Banner slide added successfully.');
    }

    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);

        $request->validate([
            'image' => 'nullable|image|max:5120',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'slider_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/sliders'), $filename);
            $slider->image = 'uploads/sliders/' . $filename;
        }

        $slider->title = $request->title;
        $slider->subtitle = $request->subtitle;
        $slider->link = $request->link;
        $slider->button_text = $request->button_text ?: 'SHOP NOW >';
        $slider->sort_order = (int) ($request->sort_order ?? $slider->sort_order);
        $slider->status = $request->boolean('status');
        $slider->save();

        return back()->with('success', 'Banner slide updated successfully.');
    }

    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->delete();

        return back()->with('success', 'Banner slide deleted successfully.');
    }
}
