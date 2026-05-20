<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleToken extends Model
{
    protected $fillable = [
        'user_id',
        'token_json',        // JSON completo do token — criptografado no banco
        'google_calendar_id',
    ];

    /**
     * 'encrypted' usa a chave APP_KEY para criptografar/descriptografar automaticamente.
     * O valor nunca fica em plaintext na coluna.
     */
    protected $casts = [
        'token_json' => 'encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Converte token_json (string) para array PHP.
     */
    public function toArray(): array
    {
        return json_decode($this->token_json, true) ?? [];
    }
}
