<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'street',
        'number',
        'complement',
        'zipcode',
        'district',
        'city',
        'state'
    ];

    // Relacionamentos
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Mutators
    public function setZipcodeAttribute($value): void
    {
        $this->attributes['zipcode'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function setStateAttribute($value): void
    {
        $this->attributes['state'] = $value ? strtoupper($value) : null;
    }

    // Accessors
    public function getFormattedZipcodeAttribute(): string
    {
        if (!$this->zipcode) return '';
        
        $zipcode = $this->zipcode;
        if (strlen($zipcode) === 8) {
            return substr($zipcode, 0, 5) . '-' . substr($zipcode, 5, 3);
        }
        return $zipcode;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->number,
            $this->complement,
            $this->district,
            $this->city,
            $this->state
        ]);

        return implode(', ', $parts);
    }

    public function getShortAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->number,
            $this->city,
            $this->state
        ]);

        return implode(', ', $parts);
    }

    // Métodos auxiliares
    public function hasCompleteAddress(): bool
    {
        return !empty($this->street) && 
               !empty($this->city) && 
               !empty($this->state);
    }

    // Scopes
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', '%' . $city . '%');
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', strtoupper($state));
    }

    public function scopeByZipcode($query, $zipcode)
    {
        $cleanZipcode = preg_replace('/[^0-9]/', '', $zipcode);
        return $query->where('zipcode', $cleanZipcode);
    }

    // Estados brasileiros para validação
    public static function getValidStates(): array
    {
        return [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins'
        ];
    }

    public function isValidState(): bool
    {
        return array_key_exists($this->state, self::getValidStates());
    }
}