<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Accommodation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminHomeController extends Controller
{
    public function getRanking()
    {
        $rankings = Booking::selectRaw('accommodation_id, COUNT(*) as reservation_count')
            ->groupBy('accommodation_id')
            ->orderByDesc('reservation_count')
            ->limit(3)
            ->get();


        foreach ($rankings as $ranking) {
            $accommodation = Accommodation::find($ranking->accommodation_id);
            $ranking->name = $accommodation ? $accommodation->name : "不明";
        }

        return response()->json($rankings);
    }

    public function getMonthlyBookings()
    {
        $monthlyBookings = Booking::selectRaw('DATE_FORMAT(check_in_date, "%Y-%m") as month, COUNT(*) as reservation_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($monthlyBookings);
    }

    public function getUserRanking()
    {
        $userRankings = User::select('users.id', 'users.name', DB::raw('COUNT(bookings.id) as reservation_count'))
            ->join('bookings', 'users.id', '=', 'bookings.guest_id') 
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('reservation_count')
            ->take(5)
            ->get();

        return response()->json($userRankings);
    }

    public function getCityShare()
    {
        $cityShares = DB::table('bookings')
            ->join('accommodations', 'bookings.accommodation_id', '=', 'accommodations.id')
            ->select('accommodations.city', DB::raw('COUNT(bookings.id) as reservation_count'))
            ->groupBy('accommodations.city')
            ->orderByDesc('reservation_count')
            ->get();

        return response()->json($cityShares);
    }
}
