@extends('admin.layouts.master')

@section('title', 'Add New Product')

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-plus-circle" style="color:var(--admin-primary); margin-right:8px;"></i> Add New Product</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Upload images, set pricing, sizes, inventory and badges</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <a href="{{ route('admin.products.index') }}">Products</a> / <span>Create</span>
    </div>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

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
                        <input type="text" name="title" class="form-control-custom" placeholder="e.g. Pastel Dino Cotton Romper Set" value="{{ old('title') }}" required>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-control-custom" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">SKU (Stock Keeping Unit)</label>
                            <input type="text" name="sku" class="form-control-custom" placeholder="e.g. BFB-DR-01" value="{{ old('sku') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Available Sizes (comma separated) *</label>
                        <input type="text" name="sizes" class="form-control-custom" placeholder="0-3M, 3-6M, 6-12M, 1-2Y, 2-3Y" value="{{ old('sizes', '0-3M, 3-6M, 6-12M, 1-2Y') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Short Summary</label>
                        <input type="text" name="short_description" class="form-control-custom" placeholder="e.g. Ultra-breathable 100% pure organic cotton snap-button romper." value="{{ old('short_description') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Product Description</label>
                        <textarea name="description" rows="5" class="form-control-custom" placeholder="Full details, fabric composition, wash care instructions...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Product Media -->
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-camera" style="color:var(--admin-primary); margin-right:6px;"></i> Product Media & Images</h3>
                </div>
                <div class="box-body">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Primary Featured Image</label>
                            <input type="file" name="featured_image" class="form-control-custom" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Secondary Hover Image</label>
                            <input type="file" name="hover_image" class="form-control-custom" accept="image/*">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Additional Gallery Images</label>
                        <input type="file" name="gallery_images[]" class="form-control-custom" accept="image/*" multiple>
                        <small style="color:#6b7280;">You can select multiple photos at once.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Pricing & Inventory -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Pricing & Inventory -->
            <div class="box" style="border-top-color:#00a65a;">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-tag" style="color:#00a65a; margin-right:6px;"></i> Pricing & Stock</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label class="form-label">Regular / Original Price (৳) *</label>
                        <input type="number" name="regular_price" class="form-control-custom" placeholder="e.g. 850" value="{{ old('regular_price') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sale / Offer Price (৳) *</label>
                        <input type="number" name="sale_price" class="form-control-custom" placeholder="e.g. 590" value="{{ old('sale_price') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purchase / Cost Price (৳)</label>
                        <input type="number" name="cost_price" class="form-control-custom" placeholder="e.g. 320" value="{{ old('cost_price', 0) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Available Stock Quantity *</label>
                        <input type="number" name="stock" class="form-control-custom" placeholder="e.g. 50" value="{{ old('stock', 50) }}" required>
                    </div>
                </div>
            </div>

            <!-- Promotion Badges & Status -->
            <div class="box" style="border-top-color:#f39c12;">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-certificate" style="color:#f39c12; margin-right:6px;"></i> Badges & Visibility</h3>
                </div>
                <div class="box-body" style="display:flex; flex-direction:column; gap:12px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <span style="font-weight:600;">Featured on Homepage</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_new_arrival" value="1" {{ old('is_new_arrival', true) ? 'checked' : '' }}>
                        <span style="font-weight:600;">Show in New Arrivals</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_bestseller" value="1" {{ old('is_bestseller') ? 'checked' : '' }}>
                        <span style="font-weight:600;">Bestseller Badge</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_clearance" value="1" {{ old('is_clearance') ? 'checked' : '' }}>
                        <span style="font-weight:600;">Clearance Sale</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="status" value="1" checked>
                        <span style="font-weight:700; color:#15803d;">Active & Visible in Store</span>
                    </label>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn-admin btn-admin-success" style="width:100%; justify-content:center; padding:10px;">
                        <i class="fa fa-save"></i> Publish Product
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
