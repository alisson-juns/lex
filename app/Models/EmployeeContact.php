<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'email',
        'cellphone',
        'phone',
        'optional_email',
        'message_cell_phone',
        'message_phone',
        'note'
    ];

    // Relacionamentos
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Mutators para formatar telefones
    public function setCellphoneAttribute($value): void
    {
        $this->attributes['cellphone'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function setMessageCellPhoneAttribute($value): void
    {
        $this->attributes['message_cell_phone'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function setMessagePhoneAttribute($value): void
    {
        $this->attributes['message_phone'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    // Accessors para formatar telefones
    public function getFormattedCellphoneAttribute(): string
    {
        if (!$this->cellphone) return '';
        
        $phone = $this->cellphone;
        if (strlen($phone) === 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
        }
        return $phone;
    }

    public function getFormattedPhoneAttribute(): string
    {
        if (!$this->phone) return '';
        
        $phone = $this->phone;
        if (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
        }
        return $phone;
    }

    public function getFormattedMessageCellPhoneAttribute(): string
    {
        if (!$this->message_cell_phone) return '';
        
        $phone = $this->message_cell_phone;
        if (strlen($phone) === 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
        }
        return $phone;
    }

    public function getFormattedMessagePhoneAttribute(): string
    {
        if (!$this->message_phone) return '';
        
        $phone = $this->message_phone;
        if (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
        }
        return $phone;
    }

    // Métodos auxiliares
    public function getPrimaryContactAttribute(): string
    {
        return $this->cellphone ?: $this->phone ?: 'Não informado';
    }

    public function getPrimaryEmailAttribute(): string
    {
        return $this->email ?: $this->optional_email ?: 'Não informado';
    }
}