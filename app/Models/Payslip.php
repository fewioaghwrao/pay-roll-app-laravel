<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'employee_number', 'year', 'month', 'pay_type', 'gross_amount', 'tax_amount', 'net_amount', 'items'
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function user()
    {
        // Belongs to user via user_id
        return $this->belongsTo(User::class, 'user_id');
    }
}