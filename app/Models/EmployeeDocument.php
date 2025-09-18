<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'cpf',
        'rg',
        'cnh',
        'pis',
        'ctps',
        'rnm',
        'other_documents'
    ];

    // Relacionamentos
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Mutators para limpar documentos
    public function setCpfAttribute($value): void
    {
        $this->attributes['cpf'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function setRgAttribute($value): void
    {
        $this->attributes['rg'] = $value ? preg_replace('/[^0-9X]/', '', strtoupper($value)) : null;
    }

    public function setCnhAttribute($value): void
    {
        $this->attributes['cnh'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function setPisAttribute($value): void
    {
        $this->attributes['pis'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function setCtpsAttribute($value): void
    {
        $this->attributes['ctps'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function setRnmAttribute($value): void
    {
        $this->attributes['rnm'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    // Accessors para formatar documentos
    public function getFormattedCpfAttribute(): string
    {
        if (!$this->cpf) return '';
        
        $cpf = $this->cpf;
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $cpf;
    }

    public function getFormattedRgAttribute(): string
    {
        if (!$this->rg) return '';
        
        $rg = $this->rg;
        if (strlen($rg) >= 8) {
            return substr($rg, 0, 2) . '.' . substr($rg, 2, 3) . '.' . substr($rg, 5, 3) . '-' . substr($rg, 8);
        }
        return $rg;
    }

    public function getFormattedPisAttribute(): string
    {
        if (!$this->pis) return '';
        
        $pis = $this->pis;
        if (strlen($pis) === 11) {
            return substr($pis, 0, 3) . '.' . substr($pis, 3, 5) . '.' . substr($pis, 8, 2) . '-' . substr($pis, 10, 1);
        }
        return $pis;
    }

    // Métodos de validação
    public static function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    public function isCpfValid(): bool
    {
        return self::validateCpf($this->cpf);
    }

    // Scopes
    public function scopeByCpf($query, $cpf)
    {
        $cleanCpf = preg_replace('/[^0-9]/', '', $cpf);
        return $query->where('cpf', $cleanCpf);
    }
}