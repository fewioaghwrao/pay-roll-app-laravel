<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class WithholdingStatement extends Model
{
use HasFactory;
protected $fillable = ['user_id','year','total_paid','total_tax','meta', 'employee_number'];
protected $casts = [ 'meta' => 'array' ];



    public function user()
    {
        return $this->belongsTo(User::class, 'employee_number', 'employee_number');
    }
}