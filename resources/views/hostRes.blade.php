@extends('layouts.app')
<style>
    img
    {
        width: 195px;
        height: 120px;
        border-radius: 15px;
        margin: 15px;
    }
    #acm-booking
    {
        border: 1px, solid, #000000;
        border-radius: 30px;
        box-shadow:  0 4px 6px rgba(0, 0, 0, 0.1);
    }

    #info{
        padding: 20px;
        text-align: left;
    }

    .date{
        text-decoration: border;
    }
    .start-date {
    
        padding: 5px;
        border-radius: 5px;
        font-weight: bold;
        font-family: arial
    }

    .end-date {
       
        padding: 5px;
        border-radius: 5px;
        font-weight: bold;
        font-family: arial
    }

    #spaced{
        margin-top: 15px;
    }

    .custom-btn {
        background-color: #dcbf7d;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 15px;
        text-align: center;
        display: inline-block;
        font-weight: bold;
        transition: background-color 0.3s ease;
        margin-top: 50px;
    }
</style>
<!-- @section('content') -->
<h1 class="h4 mx-5"><i class="fa-regular fa-clock"></i>Reservation Status</h1>
@if($all_bookings->count() > 0)
    @foreach($all_bookings as $booking)
    <div class="card mx-auto mb-4 w-75" id="acm-booking">
        <div class="row">
            <div class="col">
                <img src="{{ asset('images/' . $booking->accomodation->image) }}" alt="Accomodation Image">
            </div>
            <div class="col" id="info">
                <h5 class="date">
                    <span class="start-date">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('Y/m/d') }}</span> ~ 
                    <span class="end-date">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('Y/m/d') }}</span>
                </h5>
                <div class="row" id="spaced">
                    <div class="col">{{ $booking->user->name }}</div>
                    <div class="col">{{ $booking->num_guest }} people</div>
                </div>
                <div class="row" id="spaced">
                    <div class="col">{{ $booking->accommodation->name }}</div>
                    <div class="col">{{ $booking->special_request ?? 'No special requests' }}</div>
                </div>
            </div>
            <div class="col">
                <form action="{{ route('host.booking.cancel', ['bookingId' => $booking->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="custom-btn">
                        <i class="fa-solid fa-trash"></i> Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
@endforeach
    <div class="pagination justify-content-center">
        {{ $all_bookings->links() }}
    </div>
@else
    <p class="text-center">No reservations found.</p>
@endif
<!-- @endsection -->




