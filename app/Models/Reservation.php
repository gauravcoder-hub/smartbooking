<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
    'table_id',
    'customer_name',
    'customer_email',
    'guest_count',
    'reservation_date',
    'time_slot',
    'status',
    'special_request'
];

public function table()
{
    return $this->belongsTo(Table::class);
}
}
