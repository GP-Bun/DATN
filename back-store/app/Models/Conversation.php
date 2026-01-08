<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_id','admin_id','last_message_at','unread_count'];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
    public function messages() { return $this->hasMany(Message::class); }
}
