<?php
use Illuminate\Database\Eloquent\Model as model;

class follow extends model
{

    protected $guarded = [
        'id'
    ];

    protected $table = 'follow';
}
