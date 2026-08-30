@extends('admin.layouts.master')

@section('title', 'Products Catalog')

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-tshirt" style="color:var(--admin-primary); margin-right:8px;"></i> Products Catalog</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Manage catalog, prices, stock inventory, and badges</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <span>Products</span>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <form action="{{ route('admin.products.index') }}" method="GET" style="display:flex; gap:10px; max-width:600px; width:100%; flex-wrap:wrap;">
            <input type="text" name="search" value="{{ $search }}" class="form-control-custom" placeholder="Search product title or SKU..." style="flex:2; min-width:180px;">
            
            <select name="category_id" class="form-control-custom" style="flex:1.5; min-width:150px;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-admin btn-admin-primary"><i class="fa fa-filter"></i> Filter</button>
            @if($search || $categoryId)
                <a href="{{ route('admin.products.index') }}" class="btn-admin btn-admin-default"><i class="fa fa-times"></i></a>
            @endif
        </form>

        <a href="{{ route('admin.products.create') }}" class="btn-admin btn-admin-success">
            <i class="fa fa-plus-circle"></i> Add New Product
        </a>
    </div>

    <div class="box-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width:60px;">Image</th>
                        <th>Product Title</th>
                        <th>Category</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Badges</th>
                        <th>Status</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                        <tr>
                            <td>
                                <img src="{{ Str::startsWith($prod->featured_image, 'http') ? $prod->featured_image : asset($prod->featured_image) }}" alt="{{ $prod->title }}" style="width:48px; height:48px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb;">
                            </td>
                            <td>
                                <a href="{{ route('admin.products.edit', $prod->id) }}" style="font-weight:600; color:#111827; text-decoration:none;">
                                    {{ $prod->title }}
                                </a>
                                <small style="display:block; color:#888;">{{ implode(', ', $prod->sizes ?? []) }}</small>
                            </td>
                            <td>
                                <span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:600;">
                                    {{ $prod->category->title ?? 'General' }}
                                </span>
                            </td>
                            <td style="font-family:monospace; font-weight:600; font-size:12px;">{{ $prod->sku }}</td>
                            <td>
                                <span style="font-weight:700; color:#059669;">৳ {{ number_format($prod->sale_price) }}</span>
                                @if($prod->regular_price > $prod->sale_price)
                                    <small style="text-decoration:line-through; color:#9ca3af; display:block;">৳ {{ number_format($prod->regular_price) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($prod->stock > 10)
                                    <span style="color:#059669; font-weight:700;">{{ $prod->stock }} in stock</span>
                                @elseif($prod->stock > 0)
                                    <span style="color:#f59e0b; font-weight:700;">Low ({{ $prod->stock }})</span>
                                @else
                                    <span style="color:#ef4444; font-weight:700;">Out of Stock</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                    @if($prod->is_featured) <span style="background:#fef3c7; color:#92400e; font-size:10px; font-weight:700; padding:2px 5px; border-radius:3px;">Featured</span> @endif
                                    @if($prod->is_bestseller) <span style="background:#dbeafe; color:#1e40af; font-size:10px; font-weight:700; padding:2px 5px; border-radius:3px;">Bestseller</span> @endif
                                    @if($prod->is_new_arrival) <span style="background:#dcfce7; color:#166534; font-size:10px; font-weight:700; padding:2px 5px; border-radius:3px;">New</span> @endif
                                    @if($prod->is_clearance) <span style="background:#fee2e2; color:#991b1b; font-size:10px; font-weight:700; padding:2px 5px; border-radius:3px;">Sale</span> @endif
                                </div>
                            </td>
                            <td>
                                <form action="{{ route('admin.products.toggle-status', $prod->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" style="background:none; border:none; cursor:pointer;" title="Click to Toggle">
                                        @if($prod->status)
                                            <span style="background:#dcfce7; color:#15803d; font-weight:700; font-size:11px; padding:3px 8px; border-radius:12px;">Active</span>
                                        @else
                                            <span style="background:#fee2e2; color:#b91c1c; font-weight:700; font-size:11px; padding:3px 8px; border-radius:12px;">Inactive</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="{{ route('product.show', $prod->slug) }}" target="_blank" class="btn-admin btn-admin-default btn-sm" title="View in Store"><i class="fa fa-external-link-alt"></i></a>
                                <a href="{{ route('admin.products.edit', $prod->id) }}" class="btn-admin btn-admin-primary btn-sm" title="Edit Product"><i class="fa fa-edit"></i></a>
                                <form action="{{ route('admin.products.destroy', $prod->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-admin btn-admin-danger btn-sm" title="Delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:40px; color:#9ca3af;">
                                No products found. Click 'Add New Product' to add one!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="box-footer">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
