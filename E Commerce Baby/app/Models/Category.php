<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'handle',
        'image',
        'banner_image',
        'description',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');
    }

    public function activeChildren()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('status', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Recursively collect all descendant category IDs.
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];
        $children = $this->children()->get();
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        return array_values(array_unique($ids));
    }

    /**
     * Check if a category is a descendant of the given candidate parent ID.
     */
    public function isDescendantOf(int $candidateParentId): bool
    {
        $currentParent = $this->parent;
        while ($currentParent) {
            if ($currentParent->id === $candidateParentId) {
                return true;
            }
            $currentParent = $currentParent->parent;
        }
        return false;
    }
}
