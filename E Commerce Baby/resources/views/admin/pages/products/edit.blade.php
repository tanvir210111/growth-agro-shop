@extends('admin.layouts.master')

@section('title', 'Edit ' . $product->title)

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-edit" style="color:var(--admin-primary); margin-right:8px;"></i> Edit Product</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Update details, pricing, stock, and media for #{{ $product->sku }}</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <a href="{{ route('admin.products.index') }}">Products</a> / <span>Edit</span>
    </div>
</div>

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
        <!-- Left: Product Details & Media -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Basic Info -->
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-info-circle" style="color:var(--admin-primary); margin-right:6px;"></i> Product Information</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label class="form-label">Product Title *</label>
                        <input type="text" name="title" class="form-control-custom" value="{{ old('title', $product->title) }}" required>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-control-custom" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control-custom" value="{{ old('sku', $product->sku) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Available Sizes (comma separated) *</label>
                        <input type="text" name="sizes" class="form-control-custom" value="{{ old('sizes', implode(', ', $product->sizes ?? [])) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Short Summary</label>
                        <input type="text" name="short_description" class="form-control-custom" value="{{ old('short_description', $product->short_description) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Product Description</label>
                        <textarea name="description" rows="5" class="form-control-custom">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Media -->
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-camera" style="color:var(--admin-primary); margin-right:6px;"></i> Update Photos</h3>
                </div>
                <div class="box-body">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; align-items:start;">
                        <div class="form-group">
                            <label class="form-label">Featured Image</label>
                            @if($product->featured_image)
                                <img src="{{ Str::startsWith($product->featured_image, 'http') ? $product->featured_image : asset($product->featured_image) }}" alt="Preview" style="height:70px; object-fit:cover; border-radius:4px; margin-bottom:6px; border:1px solid #e5e7eb; display:block;">
                            @endif
                            <input type="file" name="featured_image" class="form-control-custom" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Hover Image</label>
                            @if($product->hover_image)
                                <img src="{{ Str::startsWith($product->hover_image, 'http') ? $product->hover_image : asset($product->hover_image) }}" alt="Preview" style="height:70px; object-fit:cover; border-radius:4px; margin-bottom:6px; border:1px solid #e5e7eb; display:block;">
                            @endif
                            <input type="file" name="hover_image" class="form-control-custom" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Pricing & Inventory -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <div class="box" style="border-top-color:#00a65a;">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-tag" style="color:#00a65a; margin-right:6px;"></i> Pricing & Stock</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label class="form-label">Regular Price (৳) *</label>
                        <input type="number" name="regular_price" class="form-control-custom" value="{{ old('regular_price', $product->regular_price) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sale Price (৳) *</label>
                        <input type="number" name="sale_price" class="form-control-custom" value="{{ old('sale_price', $product->sale_price) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cost Price (৳)</label>
                        <input type="number" name="cost_price" class="form-control-custom" value="{{ old('cost_price', $product->cost_price) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Available Stock Quantity *</label>
                        <input type="number" name="stock" class="form-control-custom" value="{{ old('stock', $product->stock) }}" required>
                    </div>
                </div>
            </div>

            <div class="box" style="border-top-color:#f39c12;">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-certificate" style="color:#f39c12; margin-right:6px;"></i> Badges & Visibility</h3>
                </div>
                <div class="box-body" style="display:flex; flex-direction:column; gap:12px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <span style="font-weight:600;">Featured on Homepage</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_new_arrival" value="1" {{ old('is_new_arrival', $product->is_new_arrival) ? 'checked' : '' }}>
                        <span style="font-weight:600;">Show in New Arrivals</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_bestseller" value="1" {{ old('is_bestseller', $product->is_bestseller) ? 'checked' : '' }}>
                        <span style="font-weight:600;">Bestseller Badge</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_clearance" value="1" {{ old('is_clearance', $product->is_clearance) ? 'checked' : '' }}>
                        <span style="font-weight:600;">Clearance Sale</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="status" value="1" {{ old('status', $product->status) ? 'checked' : '' }}>
                        <span style="font-weight:700; color:#15803d;">Active & Visible in Store</span>
                    </label>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn-admin btn-admin-primary" style="width:100%; justify-content:center; padding:10px;">
                        <i class="fa fa-save"></i> Save Product Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
