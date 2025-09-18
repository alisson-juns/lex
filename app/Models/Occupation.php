<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;

class Occupation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    // Relacionamentos
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function activeEmployees(): HasMany
    {
        return $this->hasMany(Employee::class)->where('active', true);
    }

    public function lawyers(): HasMany
    {
        return $this->hasMany(Employee::class)->whereHas('lawyer');
    }

    // Accessors
    

    public function getEmployeesCountAttribute(): int
    {
        return $this->employees()->count();
    }

    public function getActiveEmployeesCountAttribute(): int
    {
        return $this->activeEmployees()->count();
    }

    public function getLawyersCountAttribute(): int
    {
        return $this->lawyers()->count();
    }

    public function getShortDescriptionAttribute(): string
    {
        if (!$this->description) return '';
        return strlen($this->description) > 100 ? 
            substr($this->description, 0, 97) . '...' : 
            $this->description;
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        return $this->active === true;
    }

    public function hasEmployees(): bool
    {
        return $this->employees_count > 0;
    }

    public function hasActiveEmployees(): bool
    {
        return $this->active_employees_count > 0;
    }

    public function hasLawyers(): bool
    {
        return $this->lawyers_count > 0;
    }


    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    public function scopeWithEmployees($query)
    {
        return $query->has('employees');
    }

    public function scopeWithoutEmployees($query)
    {
        return $query->doesntHave('employees');
    }

    public function scopeWithLawyers($query)
    {
        return $query->whereHas('employees.lawyer');
    }

    
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    // Métodos estáticos úteis
    
    public static function getMostPopular()
    {
        return self::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->first();
    }

   
}