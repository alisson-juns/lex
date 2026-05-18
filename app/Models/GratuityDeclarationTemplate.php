<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GratuityDeclarationTemplate extends Model
{
    protected $table = 'gratuity_declaration_templates';

    protected $fillable = ['name', 'body_text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function gratuityDeclarations(): HasMany
    {
        return $this->hasMany(GratuityDeclaration::class);
    }
}
