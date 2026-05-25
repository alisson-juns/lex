<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Promethys\Revive\Concerns\Recyclable;
use App\Traits\LogsActivityInPortuguese;

class Task extends Model
{
    use SoftDeletes;
    use Recyclable;
    use LogsActivityInPortuguese;

    protected $fillable = [
        'legal_case_id',
        'created_by',
        'title',
        'description',
        'due_date',
        'due_time',
        'status',
        'note',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status'   => TaskStatus::class,
    ];

    protected array $activitylogFields = [
        'title',
        'description',
        'due_date',
        'due_time',
        'status',
        'note',
    ];

    protected function activitylogEventDescriptions(): array
    {
        return [
            'created'  => 'Tarefa criada',
            'updated'  => 'Tarefa atualizada',
            'deleted'  => 'Tarefa excluída',
            'restored' => 'Tarefa restaurada',
        ];
    }

    public function getActivitylogSubjectLabel(): string
    {
        return $this->title;
    }

    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(Lawyer::class, 'lawyer_task');
    }
}
