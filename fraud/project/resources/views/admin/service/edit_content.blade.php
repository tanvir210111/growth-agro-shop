@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="add-product-content">
        <div class="product-description">
            <div class="body-area">
                <h4 class="mb-4">{{ __('Edit Section Text') }}</h4>
                <form action="{{ route('admin.service.update_text') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-lg-4 text-right"><h4 class="heading">সাব-টাইটেল (উপরে ছোট লেখা)</h4></div>
                        <div class="col-lg-7">
                            <input type="text" class="input-field" name="subtitle" value="{{ $content->subtitle }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-4 text-right"><h4 class="heading">মূল টাইটেল (বড় লেখা)</h4></div>
                        <div class="col-lg-7">
                            <input type="text" class="input-field" name="title" value="{{ $content->title }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4"></div>
                        <div class="col-lg-7">
                            <button class="addProductSubmit-btn" type="submit">Update Text</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection