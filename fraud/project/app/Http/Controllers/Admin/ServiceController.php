<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    // ১. সব সার্ভিস লিস্ট দেখার জন্য
    public function index()
    {
        $services = Service::orderBy('id', 'desc')->get();
        // সেকশন টেক্সট (টাইটেল ও সাব-টাইটেল) ডাটাবেস থেকে আনা
        $section_text = DB::table('section_contents')->where('section_key', 'service_section')->first();
        
        return view('admin.service.index', compact('services', 'section_text'));
    }

    // ২. নতুন সার্ভিস তৈরির ফর্ম
    public function create()
    {
        return view('admin.service.create');
    }

    // ৩. ডাটা সেভ করা
    public function store(Request $request)
    {
        $rules = [
            'title'       => 'required|max:191',
            'description' => 'required',
            'icon'        => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Service::create($request->all());

        return redirect()->route('admin.service.index')->with('success', 'নতুন সার্ভিস সফলভাবে যোগ হয়েছে।');
    }

    // ৪. এডিট ফর্ম
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.service.edit', compact('service'));
    }

    // ৫. আপডেট করা
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $rules = [
            'title'       => 'required|max:191',
            'description' => 'required',
            'icon'        => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $service->update($request->all());

        return redirect()->route('admin.service.index')->with('success', 'সার্ভিস সফলভাবে আপডেট হয়েছে।');
    }

    // ৬. ডিলিট করা
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return redirect()->route('admin.service.index')->with('success', 'সার্ভিস সফলভাবে ডিলিট করা হয়েছে।');
    }

    // --- সেকশন টেক্সট এডিট অপশন (Service Section Title) ---

    public function editContent()
    {
        $content = DB::table('section_contents')->where('section_key', 'service_section')->first();
        return view('admin.service.edit_content', compact('content'));
    }

    public function updateContent(Request $request)
    {
        $rules = ['title' => 'required|max:191', 'subtitle' => 'required|max:191'];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::table('section_contents')->where('section_key', 'service_section')->update([
            'title'      => $request->title,
            'subtitle'   => $request->subtitle,
            'updated_at' => now()
        ]);

        return redirect()->route('admin.service.index')->with('success', 'সেকশন টেক্সট সফলভাবে আপডেট হয়েছে।');
    }

    // --- নতুন যোগ করা মেথড (About Section Edit - আপনার অনলাইন ব্যবসার সহযোগী) ---

    // ৯. About সেকশন এডিট পেজ
    public function editAboutSection()
    {
        // নিশ্চিত করুন SQL কমান্ড দিয়ে ID 1 ইনসার্ট করেছেন
        $data = DB::table('about_section_contents')->where('id', 1)->first();
        return view('admin.service.edit_about', compact('data'));
    }

    // ১০. About সেকশন আপডেট মেথড
    public function updateAboutSection(Request $request)
    {
        $request->validate([
            'title' => 'required|max:191',
            'subtitle' => 'required',
        ]);

        $data = [
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'updated_at' => now()
        ];

        // ছবি আপলোড লজিক
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('assets/images/about', $name);
            $data['image'] = $name;
        }

        DB::table('about_section_contents')->where('id', 1)->update($data);

        return redirect()->back()->with('success', 'About Section সফলভাবে আপডেট হয়েছে।');
    }
}