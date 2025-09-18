<?php

namespace App\Models\Enterprises;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enterprise extends Model
{
    protected $fillable = [
        'corporate_reason',
        'trade_name',
        'note',
    ];

    public function documents(): HasOne
    {
        return $this->hasOne(Document::class);
    }
}