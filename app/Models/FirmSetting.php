<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirmSetting extends Model
{
    protected $fillable = [
        'firm_name',
        'firm_address',
        'firm_city',
        'firm_state',
        'firm_zipcode',
        'firm_phone',
        'firm_email',
        'firm_logo',
        'firm_lawyers',
        'holiday_states',
        'holiday_cities',
    ];

    protected $casts = [
        'holiday_states' => 'array',
        'holiday_cities' => 'array',
    ];

    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}