<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted(): void
    {
        static::saving(function (Specialization $s) {
            if (! $s->slug && $s->name) {
                $s->slug = Str::slug($s->name);
            }
        });
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
