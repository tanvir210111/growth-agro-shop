@extends('admin.layouts.master')

@section('title', 'Categories & Banners')

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-tags" style="color:var(--admin-primary); margin-right:8px;"></i> Categories & Category Banners</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Manage store categories, handles, thumbnail icons, and dedicated category hero banners</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <span>Categories</span>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:20px;">
    <!-- Left: Add Category Form -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><i class="fa fa-plus-circle" style="color:var(--admin-primary); margin-right:6px;"></i> Add New Category</h3>
        </div>
        <div class="box-body">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">Category Title *</label>
                    <input type="text" name="title" class="form-control-custom" placeholder="e.g. Newborn Sets" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Custom URL Handle (optional)</label>
                    <input type="text" name="handle" class="form-control-custom" placeholder="e.g. newborn-sets">
                </div>

                <div class="form-group">
                    <label class="form-label">Icon / Circular Thumbnail Image</label>
                    <input type="file" name="image" class="form-control-custom" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Dedicated Category Hero Banner (16:9)</label>
                    <input type="file" name="banner_image" class="form-control-custom" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Short Description</label>
                    <textarea name="description" rows="2" class="form-control-custom" placeholder="Brief summary of this category..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Display Order Number</label>
                    <input type="number" name="sort_order" class="form-control-custom" value="{{ $categories->count() + 1 }}">
                </div>

                <button type="submit" class="btn-admin btn-admin-success" style="width:100%; justify-content:center; padding:10px;">
                    <i class="fa fa-save"></i> Save Category
                </button>
            </form>
        </div>
    </div>

    <!-- Right: Categories List Table -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><i class="fa fa-list" style="color:var(--admin-primary); margin-right:6px;"></i> Existing Categories ({{ $categories->count() }})</h3>
        </div>
        <div class="box-body" style="padding:0;">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width:40px;">Order</th>
                            <th>Icon</th>
                            <th>Category Title & Slug</th>
                            <th>Banner Image</th>
                            <th>Products</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr>
                                <td style="font-weight:700; color:#6b7280; text-align:center;">{{ $cat->sort_order }}</td>
                                <td>
                                    <img src="{{ Str::startsWith($cat->image, 'http') ? $cat->image : asset($cat->image) }}" alt="{{ $cat->title }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:1px solid #e5e7eb;">
                                </td>
                                <td>
                                    <strong>{{ $cat->title }}</strong>
                                    <code style="display:block; font-size:11px; color:#2563eb;">/collections/{{ $cat->handle }}</code>
                                </td>
                                <td>
                                    @if($cat->banner_image)
                                        <img src="{{ Str::startsWith($cat->banner_image, 'http') ? $cat->banner_image : asset($cat->banner_image) }}" alt="Banner" style="height:36px; width:64px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb;">
                                    @else
                                        <span style="color:#9ca3af; font-size:11px;">No banner</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:11px; padding:2px 8px; border-radius:10px;">
                                        {{ $cat->products_count }} items
                                    </span>
                                </td>
                                <td style="text-align:center; white-space:nowrap;">
                                    <a href="{{ route('collection.show', $cat->handle) }}" target="_blank" class="btn-admin btn-admin-default btn-sm" title="View Collection"><i class="fa fa-external-link-alt"></i></a>
                                    <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger btn-sm" title="Delete"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
