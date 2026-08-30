@extends('admin.layouts.master')

@section('title', 'Homepage Sliders')

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-images" style="color:var(--admin-primary); margin-right:8px;"></i> Homepage Hero Sliders</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Upload and arrange top hero promotional banners</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <span>Sliders</span>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:20px;">
    <!-- Left: Add Slide Form -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><i class="fa fa-plus-circle" style="color:var(--admin-primary); margin-right:6px;"></i> Add New Banner Slide</h3>
        </div>
        <div class="box-body">
            <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">Banner Image (16:9) *</label>
                    <input type="file" name="image" class="form-control-custom" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Title / Caption</label>
                    <input type="text" name="title" class="form-control-custom" placeholder="e.g. Baby Boy Collection">
                </div>

                <div class="form-group">
                    <label class="form-label">Target Collection / Page Link *</label>
                    <select name="link" class="form-control-custom" required>
                        <option value="/shop">All Shop / Catalog</option>
                        @foreach($categories as $cat)
                            <option value="/collections/{{ $cat->handle }}">Category: {{ $cat->title }} (/collections/{{ $cat->handle }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" class="form-control-custom" value="SHOP NOW >">
                </div>

                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="sort_order" class="form-control-custom" value="{{ $sliders->count() + 1 }}">
                </div>

                <button type="submit" class="btn-admin btn-admin-success" style="width:100%; justify-content:center; padding:10px;">
                    <i class="fa fa-upload"></i> Upload & Publish Slide
                </button>
            </form>
        </div>
    </div>

    <!-- Right: Sliders List Table -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><i class="fa fa-list" style="color:var(--admin-primary); margin-right:6px;"></i> Active Hero Slides ({{ $sliders->count() }})</h3>
        </div>
        <div class="box-body" style="padding:0;">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width:40px;">Order</th>
                            <th style="width:120px;">Banner Preview</th>
                            <th>Title & Target Link</th>
                            <th>Status</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sliders as $slide)
                            <tr>
                                <td style="font-weight:700; color:#6b7280; text-align:center;">{{ $slide->sort_order }}</td>
                                <td>
                                    <img src="{{ Str::startsWith($slide->image, 'http') ? $slide->image : asset($slide->image) }}" alt="{{ $slide->title }}" style="width:110px; height:60px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb;">
                                </td>
                                <td>
                                    <strong>{{ $slide->title ?: 'Hero Banner Slide' }}</strong>
                                    <code style="display:block; font-size:11px; color:#2563eb;">{{ $slide->link }}</code>
                                </td>
                                <td>
                                    @if($slide->status)
                                        <span style="background:#dcfce7; color:#166534; font-weight:700; font-size:11px; padding:2px 8px; border-radius:10px;">Active</span>
                                    @else
                                        <span style="background:#fee2e2; color:#991b1b; font-weight:700; font-size:11px; padding:2px 8px; border-radius:10px;">Disabled</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <form action="{{ route('admin.sliders.destroy', $slide->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this banner slide?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger btn-sm" title="Delete"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:30px; color:#9ca3af;">No hero sliders uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
