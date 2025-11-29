<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'is_read'];
    protected $casts = ['is_read' => 'boolean', 'created_at' => 'datetime'];

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}
