<?php

namespace App\Http\Controllers;

use App\Models\WhyChooseUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhyChooseUsController extends Controller
{
    // ১. সব ডেটা দেখানোর জন্য
    public function index()
    {
        $features = WhyChooseUs::orderBy('id', 'desc')->get();
        return view('admin.why_choose_us.index', compact('features'));
    }

    // ২. নতুন ডেটা তৈরির পেজ
    public function create()
    {
        return view('admin.why_choose_us.create');
    }

    // ৩. ডেটাবেসে সেভ করা (Store)
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required',
            'description' => 'required',
            'icon'        => 'required',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $input = $request->all();

        if ($file = $request->file('image')) {
            $name = time() . '_' . $file->getClientOriginalName();
            
            // সরাসরি পাথ ব্যবহার (আপনার ব্রান্ড কন্ট্রোলারের মতো)
            $file->move('assets/images/why-choose-us', $name); 
            
            $input['image'] = $name;
        }

        WhyChooseUs::create($input);

        return redirect()->route('admin.why-choose-us.index')->with('success', 'সফলভাবে যোগ হয়েছে!');
    }

    // ৪. এডিট পেজ (Edit Form)
    public function edit($id)
    {
        $data = WhyChooseUs::findOrFail($id);
        return view('admin.why_choose_us.edit', compact('data'));
    }

    // ৫. আপডেট করা (Update)
    public function update(Request $request, $id)
    {
        $data = WhyChooseUs::findOrFail($id);

        $request->validate([
            'title'       => 'required',
            'description' => 'required',
            'icon'        => 'required',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $input = $request->all();

        if ($file = $request->file('image')) {
            // পুরাতন ইমেজ ডিলিট করা
            $oldFilePath = 'assets/images/why-choose-us/' . $data->image;
            if($data->image && file_exists($oldFilePath)){
                @unlink($oldFilePath);
            }

            $name = time() . '_' . $file->getClientOriginalName();
            $file->move('assets/images/why-choose-us', $name);
            $input['image'] = $name;
        }

        $data->update($input);

        return redirect()->route('admin.why-choose-us.index')->with('success', 'সফলভাবে আপডেট করা হয়েছে!');
    }

    // ৬. ডিলিট করা (Delete)
    public function destroy($id)
    {
        $data = WhyChooseUs::findOrFail($id);
        
        $filePath = 'assets/images/why-choose-us/' . $data->image;
        
        if($data->image && file_exists($filePath)){
            @unlink($filePath);
        }

        $data->delete();
        return back()->with('success', 'সফলভাবে ডিলিট করা হয়েছে!');
    }
}