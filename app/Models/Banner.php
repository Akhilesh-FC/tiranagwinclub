<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Banner extends Model
{
    use HasFactory;
	 protected $table = 'sliders';
    protected $fillable = [
        
        'image',
		'activity_image',
		'title',
        'status',
    ];
}