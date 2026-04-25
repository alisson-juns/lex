<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enterprise extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'corporate_reason',
        'trade_name',
        'note',
    ];

    public function enterprise_documents(): HasOne
    {
        return $this->hasOne(EnterpriseDocument::class);
    }

    public function enterprise_representatives(): HasMany
    {
        return $this->hasMany(EnterpriseRepresentative::class);
    }
}