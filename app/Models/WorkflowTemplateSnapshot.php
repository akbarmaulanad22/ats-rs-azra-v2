<?php

namespace App\Models;

use Database\Factories\WorkflowTemplateSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplateSnapshot extends Model
{
    /** @use HasFactory<WorkflowTemplateSnapshotFactory> */
    use HasFactory;

    protected $fillable = ['nama'];

    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowTemplateSnapshotStage::class)
            ->orderBy('position');
    }

    public static function createFromTemplate(WorkflowTemplate $template): self
    {
        $snapshot = self::create([
            'nama' => $template->nama,
        ]);

        $template->stages->each(function (Stage $stage) use ($snapshot) {
            $snapshot->stages()->create([
                'position' => $stage->pivot->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'is_locked_first' => $stage->is_locked_first,
                'is_locked_last' => $stage->is_locked_last,
            ]);
        });

        return $snapshot;
    }
}
