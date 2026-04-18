<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocSection extends Model
{
    protected $fillable = ['doc_id', 'heading', 'content', 'order_num'];

    public function doc()
    {
        return $this->belongsTo(Doc::class);
    }
}
