<?php

namespace App\Filament\Resources;

use Illuminate\Support\Str;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Rmsramos\Activitylog\Resources\ActivitylogResource;

class CustomActivitylogResource extends ActivitylogResource
{
    /**
     * Traduz as chaves antes de exibir no KeyValue da ViewPage.
     */
    protected static function flattenArrayForKeyValue(array $data): array
    {
        $flattened = [];

        foreach ($data as $key => $value) {
            $translatedKey = __('activitylog_keys.' . $key) !== 'activitylog_keys.' . $key
                ? __('activitylog_keys.' . $key)
                : $key;

            if (is_array($value) || is_object($value)) {
                $flattened[$translatedKey] = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            } else {
                $flattened[$translatedKey] = $value;
            }
        }

        return $flattened;
    }

    /**
     * Sobrescreve a coluna Assunto para exibir o nome do registro.
     */
    public static function getSubjectTypeColumnComponent(): Column
    {
        return TextColumn::make('subject_type')
            ->label('Assunto')
            ->formatStateUsing(function ($state, $record) {
                if (! $state) {
                    return '-';
                }

                $subject = $record->subject;

                if (! $subject) {
                    return Str::of($state)->afterLast('\\')->headline()
                        . " #{$record->subject_id} (Deletado)";
                }

                if (method_exists($subject, 'getActivitylogSubjectLabel')) {
                    $label = $subject->getActivitylogSubjectLabel();

                    if (method_exists($subject, 'trashed') && $subject->trashed()) {
                        $label .= ' (Excluído)';
                    }

                    return $label;
                }

                $fallback = $subject->name
                    ?? $subject->title
                    ?? $subject->corporate_reason
                    ?? Str::of($state)->afterLast('\\')->headline() . " #{$record->subject_id}";

                if (method_exists($subject, 'trashed') && $subject->trashed()) {
                    $fallback .= ' (Excluído)';
                }

                return $fallback;
            })
            ->searchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CustomActivitylogResource\Pages\ListCustomActivitylog::route('/'),
            'view'  => \App\Filament\Resources\CustomActivitylogResource\Pages\ViewCustomActivitylog::route('/{record}'),
        ];
    }
}
