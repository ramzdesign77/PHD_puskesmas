<?php

namespace App\Models;

use App\Models\Concerns\HasCustomId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'category', 'excerpt', 'content', 'image', 'id_author', 'is_published'])]
class Education extends Model
{
    use HasCustomId;

    protected $table = 'ref_edukasi';

    protected $primaryKey = 'id_edukasi';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'content',
        'image',
        'id_author',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author', 'id_user');
    }
}
