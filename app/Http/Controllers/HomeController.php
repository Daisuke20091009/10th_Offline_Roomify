<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Category;
use App\Models\Booking;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private $accommodation;
    private $category;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Accommodation $accommodation, Category $category)
    {
        $this->accommodation = $accommodation;
        $this->category = $category;
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $accommodations = $this->accommodation->withCount('bookings')
                                              ->orderByDesc('bookings_count')
                                              ->take(6)
                                              ->get();

        return view('home', compact('accommodations'));
    }


    public function search_by_filters(Request $request)
    {
        $query = $this->accommodation->query();
        $daterange = $request->input('daterange');

        if ($daterange) {
            $date = array_map('trim', explode(' - ', $daterange));
            $starting_date = $date[0];
            $ending_date = $date[1];
        }

        $query->when($request->filled('city'), function ($q) use ($request, $starting_date, $ending_date) {
            $q->where('city', 'LIKE', '%' . $request->input('city') . '%')
              ->where('capacity', '>=', $request->input('capacity'));
        });

        if ($starting_date && $ending_date) {
            $query->whereDoesntHave('bookings', function ($q) use ($starting_date, $ending_date) {
                $q->where(function ($query) use ($starting_date, $ending_date) {
                    $query->whereBetween('check_in_date', [$starting_date, $ending_date])
                          ->orWhereBetween('check_out_date', [$starting_date, $ending_date])
                          ->orWhere(function ($query) use ($starting_date, $ending_date) {
                              $query->where('check_in_date', '<=', $starting_date)
                                    ->where('check_out_date', '>=', $ending_date);
                          });
                });
            });
        }

        $accommodations = $query->get();
        $categories = $this->category->get();

        return view('accommodation.search')->with('all_accommodations', $accommodations)
                                                 ->with('categories', $categories);
    }
}
