<?php

namespace App\Models;

use App\Enums\DocumentCategoryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'process_id',
        'client_id',
        'uploaded_by',
        'title',
        'description',
        'category',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'category' => DocumentCategoryEnum::class,
        'file_size' => 'integer',
    ];

    protected $appends = ['size_label'];

    protected static function booted(): void
    {
        static::creating(function (Document $document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getSizeLabelAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return "{$bytes} B";
    }

    public function downloadName(): string
    {
        return "{$this->title}.{$this->extension()}";
    }

    private function extension(): string
    {
        $name = $this->file_name;
        $dot = strrpos($name, '.');

        return $dot === false ? 'pdf' : substr($name, $dot + 1);
    }

    public function deleteFile(): void
    {
        Storage::delete($this->file_path);
    }
}
