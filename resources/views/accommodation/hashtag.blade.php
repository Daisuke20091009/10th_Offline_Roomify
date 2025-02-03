@extends('layouts.app')

@section('title', 'hashtag accommodation')

<style>
.card-title, .card-text {
    text-align: left !important;
  }
</style>
@section('content')

<div class="container w-75 vh-100">
    <a href="{{ url()->previous() }}" class="text-black d-block mt-5"><i class="fa-solid fa-angles-left"></i> Back to the accommodation page</a>

    <h2 class="fs-5 mb-0 mt-5">Hashtag: <span class="fw-bold">{{$hashtag->name}}</span></h2>
    <h2 class="fs-5 mb-0 mt-1">City: <span class="fw-bold">{{ $city }}</span></h2>



    <div class="container mt-4">
        <div class="row">

    @forelse ($all_accommodations as $accommodation)
    <div class="col-12 col-md-4 mb-4">
        <div class="card" style="height:450px;">
            @if($accommodation->photos->isNotEmpty())
                <img src="{{ asset('storage/'. $accommodation->photos[0]->image) }}" class="card-img-top" alt="Card image 1" style="height: 320px;">
            @else
                <img src="#" alt="">
            @endif
          <div class="card-body">
            <h5 class="card-title">{{ $accommodation->name }}</h5>
            <p class="card-text">{{ $accommodation->description }}</p>
          </div>
        </div>
      </div>
    @empty
    <div>
        <h3>No Accommodations here.</h3>
    </div>
    @endforelse
</div>

</div>
@endsection
