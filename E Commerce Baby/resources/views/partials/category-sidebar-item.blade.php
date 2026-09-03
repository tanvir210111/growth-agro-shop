@php
    $depth = $depth ?? 1;
    $indentPx = 14 + (($depth - 1) * 12);
    $isActive = ($currentCollection['handle'] ?? '') === $category['handle'];
@endphp
<li>
    <a href="{{ route('collection.show', $category['handle']) }}"
       style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding-left: {{ $indentPx }}px; color: {{ $isActive ? '#ea580c' : '#64748b' }}; font-weight: {{ $isActive ? '700' : '400' }}; text-decoration: none;">
        <span>↳ {{ $category['title'] }}</span>
        <span style="font-size: 0.7rem; color: #94a3b8;">{{ $category['item_count'] ?? 0 }}</span>
    </a>
    @if(!empty($category['children']) && count($category['children']) > 0)
        <ul style="list-style: none; padding: 0; margin: 2px 0 4px; display: flex; flex-direction: column; gap: 3px;">
            @foreach($category['children'] as $subChild)
                @include('partials.category-sidebar-item', ['category' => $subChild, 'depth' => $depth + 1, 'currentCollection' => $currentCollection])
            @endforeach
        </ul>
    @endif
</li>
