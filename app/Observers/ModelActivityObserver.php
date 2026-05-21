<?php

namespace App\Observers;

use App\Logging\LogContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ModelActivityObserver
{
    public static bool $enabled = true;

    /**
     * Fields that indicate a sensitive status or assignment change (logged at notice level).
     *
     * @var array<int, string>
     */
    private const SENSITIVE_FIELDS = [
        'status',
        'is_active',
        'keputusan',
        'reviewed_by',
        'interviewer_id',
        'user_id',
        'must_change_password',
    ];

    /**
     * Fields to strip before logging dirty attributes (sensitive data).
     *
     * @var array<int, string>
     */
    private const REDACTED_FIELDS = [
        'password',
        'remember_token',
        'token',
        'api_token',
    ];

    private const MAX_FIELD_LENGTH = 200;

    public function created(Model $model): void
    {
        if (! static::$enabled) {
            return;
        }

        Log::info('Model created', array_merge(LogContext::make(), [
            'model' => class_basename($model),
            'model_id' => $model->getKey(),
        ]));
    }

    public function updated(Model $model): void
    {
        if (! static::$enabled) {
            return;
        }

        $dirty = $this->sanitizeDirty($model->getDirty());

        $hasSensitiveChange = count(array_intersect(array_keys($dirty), self::SENSITIVE_FIELDS)) > 0;

        $context = array_merge(LogContext::make(), [
            'model' => class_basename($model),
            'model_id' => $model->getKey(),
            'changed' => $dirty,
        ]);

        if ($hasSensitiveChange) {
            Log::notice('Model updated (sensitive change)', $context);
        } else {
            Log::info('Model updated', $context);
        }
    }

    public function deleted(Model $model): void
    {
        if (! static::$enabled) {
            return;
        }

        Log::info('Model deleted', array_merge(LogContext::make(), [
            'model' => class_basename($model),
            'model_id' => $model->getKey(),
        ]));
    }

    /**
     * Remove sensitive fields from dirty attributes before logging.
     *
     * @param  array<string, mixed>  $dirty
     * @return array<string, mixed>
     */
    private function sanitizeDirty(array $dirty): array
    {
        $filtered = array_diff_key($dirty, array_flip(self::REDACTED_FIELDS));

        return array_map(function (mixed $value): mixed {
            if (is_string($value) && mb_strlen($value) > self::MAX_FIELD_LENGTH) {
                return mb_substr($value, 0, self::MAX_FIELD_LENGTH).'… [truncated]';
            }

            return $value;
        }, $filtered);
    }
}
