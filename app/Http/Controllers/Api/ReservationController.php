<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Annotations as OA;

class ReservationController extends Controller
{
    
    public function availability(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->date;

        $timeSlots = ['18:00-20:00', '20:00-22:00'];

        $response = [];

        foreach ($timeSlots as $slot) {

            $bookedTableIds = Reservation::where([
                    ['reservation_date', '=', $date],
                    ['time_slot', '=', $slot],
                    ['status', '=', 'booked']
                ])
                ->pluck('table_id');

            $availableTables = Table::whereNotIn('id', $bookedTableIds)
                ->select('id', 'table_number', 'capacity', 'location')
                ->get();

            $response[] = [
                'time_slot' => $slot,
                'available_tables' => $availableTables,
                'available_count' => $availableTables->count()
            ];
        }

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $response
        ]);
    }

    private function findBestTable($guestCount, $date, $timeSlot)
{
    return \App\Models\Table::where('capacity', '>=', $guestCount)
        ->whereDoesntHave('reservations', function ($query) use ($date, $timeSlot) {
            $query->where('reservation_date', $date)
                  ->where('time_slot', $timeSlot)
                  ->where('status', 'booked');
        })
        ->orderBy('capacity', 'asc') // smallest suitable table
        ->first();
}

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'guest_count' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'time_slot' => 'required|string'
        ]);

        $bestTable = $this->findBestTable($request->guest_count, $request->reservation_date, $request->time_slot);

        if (!$bestTable) {
            return response()->json([
                'success' => false,
                'message' => 'No available tables for the selected date and time slot.'
            ], 400);
        }

        $reservation = Reservation::create([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'guest_count' => $request->guest_count,
            'reservation_date' => $request->reservation_date,
            'time_slot' => $request->time_slot,
            'table_id' => $bestTable->id,
            'status' => 'booked'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservation created successfully',
            'data' => $reservation
        ], 201);
    }

    public function show($id)
{
    $reservation = \App\Models\Reservation::with('table')->find($id);

    if (!$reservation) {
        return response()->json([
            'success' => false,
            'message' => 'Reservation not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $reservation
    ]);
}


public function cancel($id)
{
    $reservation = \App\Models\Reservation::find($id);

    if (!$reservation) {
        return response()->json([
            'success' => false,
            'message' => 'Reservation not found'
        ], 404);
    }

    // Combine date + time slot start
    $startTime = explode('-', $reservation->time_slot)[0];
    $reservationDateTime = Carbon::parse($reservation->reservation_date . ' ' . $startTime);

    // Check 2-hour rule
    if (now()->diffInHours($reservationDateTime, false) < 2) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot cancel within 2 hours of reservation'
        ], 400);
    }

    $reservation->update([
        'status' => 'cancelled'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Reservation cancelled successfully'
    ]);
}
}