<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignee_changes', 
        'status_cahnges', 
        'tasks_assigned_to_me',
        'document_edited',
        'new_version_published',
        'due_date_changes',
        'due_date_overdue',
        'before_due_date_reminder',
    ];
}
