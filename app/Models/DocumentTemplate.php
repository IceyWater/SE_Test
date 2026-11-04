<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $appends = ['file_type', 'download_url'];

    public function getFileTypeAttribute(): string
    {
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('document-templates.download', $this->id, false);
    }

    protected $fillable = [
        'name', 'code', 'defense_type', 'file_path', 'page_count', 'version', 'fields', 'fields_meta', 'created_by',
    ];

    protected $casts = [
        'fields' => 'array',
        'fields_meta' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
