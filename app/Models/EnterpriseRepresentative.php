<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseRepresentative extends Model
{
    protected $table = 'enterprise_representatives';

    protected $fillable = [
        'enterprise_id',
        'name',
        'gender',
        'position',
        'note',
    ];

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }
}