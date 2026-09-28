<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'category', 'excerpt', 'content', 'image'])]
class Education extends Model
{
    protected $table = 'educations';

    protected static function booted(): void
    {
        static::saving(function (Education $education): void {
            if ($education->isDirty('title') || blank($education->slug)) {
                $baseSlug = Str::slug($education->title);
                $slug = $baseSlug;
                $suffix = 2;

                while (static::query()
                    ->where('slug', $slug)
                    ->when($education->exists, fn ($query) => $query->whereKeyNot($education->getKey()))
                    ->exists()) {
                    $slug = $baseSlug.'-'.$suffix++;
                }

                $education->slug = $slug;
            }
        });
    }
}
