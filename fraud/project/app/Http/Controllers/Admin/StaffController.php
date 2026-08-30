<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Datatables;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function datatables()
    {
        $datas = User::orderBy('id','desc');
        return Datatables::of($datas)
            ->addColumn('action', function(User $data) {
                $delete = '<a href="javascript:;" data-href="'.route('admin.staff.delete',$data->id).'" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a>';
                return '<div class="action-list"><a data-href="'.route('admin.staff.edit',$data->id) .'" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a>'.$delete.'</div>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function index()
    {
        return view('admin.staff.index');
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    // --- নতুন ইউজার তৈরি (Store) ---
    public function store(Request $request)
    {
        // ১. ভ্যালিডেশন
        $rules = [
            'name'      => 'required',
            'email'     => 'required|unique:users', // users টেবিলে চেক হবে
            'phone'     => 'required',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'password'  => 'required',
            // SMS Fields Validation (Optional but recommended)
            'wallet_balance'   => 'nullable|numeric',
            'non_masking_rate' => 'nullable|numeric',
            'masking_rate'     => 'nullable|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // ২. ডাটা সেভ
        $data = new User();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/admin/', $name);
            $data->photo = $name;
        }

        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->designation = $request->designation; // যদি ফর্ম থেকে আসে
        $data->password = bcrypt($request->password);
        $data->verified = 1;
        $data->email_verified = 'Yes';

        // [নতুন] SMS Reseller Fields
        $data->wallet_balance = $request->wallet_balance ?? 0.00;
        $data->non_masking_rate = $request->non_masking_rate ?? 0.50;
        $data->masking_rate = $request->masking_rate ?? 0.80;
        
        // API Key জেনারেট (যদি না থাকে)
        if(empty($data->api_key)){
            $data->api_key = \Illuminate\Support\Str::random(60);
        }

        $data->save();

        $msg = 'New User Added Successfully';
        return response()->json($msg);
    }

    public function edit($id)
    {
        $data = User::find($id);
        return view('admin.staff.edit', compact('data'));
    }

    // --- ইউজার আপডেট (Update) ---
    public function update(Request $request, $id)
    {
        // ১. ভ্যালিডেশন
        $rules = [
            'name'      => 'required',
            'email'     => 'required|unique:users,email,'.$id,
            'phone'     => 'required',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'wallet_balance'   => 'nullable|numeric',
            'non_masking_rate' => 'nullable|numeric',
            'masking_rate'     => 'nullable|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()->toArray()]);
        }

        // ২. ডাটা আপডেট
        $data = User::find($id);
        $input = $request->all(); // সব ডাটা ইনপুট হিসেবে নিলাম

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/admin/', $name);
            
            if($data->photo != null) {
                if (file_exists(public_path('assets/images/admin/'.$data->photo))) {
                    @unlink('assets/images/admin/'.$data->photo);
                }
            }
            $input['photo'] = $name;
        }

        // [নতুন] পাসওয়ার্ড যদি খালি না থাকে তবে আপডেট হবে
        if($request->password){
            $input['password'] = bcrypt($request->password);
        } else {
            unset($input['password']); // পাসওয়ার্ড না দিলে আগেরটাই থাকবে
        }

        // [নতুন] SMS ফিল্ডগুলো নিশ্চিত করা
        $input['wallet_balance'] = $request->wallet_balance;
        $input['non_masking_rate'] = $request->non_masking_rate;
        $input['masking_rate'] = $request->masking_rate;

        $data->update($input);

        $msg = 'User Data Updated Successfully';
        return response()->json($msg);
    }

    public function delete($id)
    {
        $data = User::find($id);
        
        if($data->photo != null){
            if (file_exists(public_path('assets/images/admin/'.$data->photo))) {
                @unlink('assets/images/admin/'.$data->photo);
            }
        }
        
        $data->delete();
    }
}