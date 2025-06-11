<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    /** @use HasFactory<\Database\Factories\ResepFactory> */
    use HasFactory;

    protected $fillable = [
        'id_user',
        'judul',
        'kategori',
        'deskripsi',
        'imageUrl'
    ];

    protected $primaryKey = 'id_resep';
}
