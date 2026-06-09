<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GratuityDeclaration extends Model
{
    protected $table = 'gratuity_declarations';

    protected $fillable = [
        'client_id',
        'gratuity_declaration_template_id',
        'user_id',
        'rendered_body',
        'pdf_path',
        'is_draft',
    ];

    protected $casts = [
        'is_draft' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(GratuityDeclarationTemplate::class, 'gratuity_declaration_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
