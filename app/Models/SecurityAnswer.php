<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAnswer extends Model
{
    protected $fillable = ['user_id', 'question_number', 'answer_hash'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
