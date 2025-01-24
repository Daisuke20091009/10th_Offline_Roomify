@extends('layouts.app')

@section('title', 'accommodation_create')

<style>
html,body
    {
        height: 100vh;
        overflow-x:hidden;
    }

    .bg-gold {
        background-color: #DCBF7D;
    }

    .w-45 {
         width: 45%;
    }
</style>

@section('content')

<div class="corner-circle circle-left-1" style="z-index:-1;"></div>
<div class="corner-circle circle-left-2" style="z-index:-1;"></div>
<div class="corner-circle circle-right-1" style="z-index:-1;"></div>
<div class="corner-circle circle-right-2" style="z-index:-1;"></div>

<div class="card w-50 mx-auto box-shadow bg-white">
    <h2 class="mt-4">Register Accommodation!</h2>
    <form action="{{ route('host.accommodation.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label text-start w-100 ms-4 fw-bold">Accommodation Name</label>
            <input type="text" class="form-control mx-auto" id="name" name="name" placeholder="Accommodation Name" style="width: 95%; border-radius: 10px;">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label text-start w-100 ms-4 fw-bold">Accommodation Address</label>
            <input type="text" class="form-control mx-auto" id="address" name="address" placeholder="Accommodation Address" style="width: 95%; border-radius: 10px;" onfocus="initAutocomplete()">
        </div>

        <div class="mb-3">
            <label for="price" class="form-label text-start w-100 ms-4 fw-bold">Price</label>
            <input type="number" class="form-control mx-auto" id="price" name="price" placeholder="Price" style="width: 95%; border-radius: 10px;">
        </div>

        <div class="mb-3">
            <label for="capacity" class="form-label text-start w-100 ms-4 fw-bold">Capacity</label>
            <input type="number" class="form-control mx-auto" id="capacity" name="capacity" placeholder="Capacity" style="width: 95%; border-radius: 10px;">
        </div>


        <div class="mb-3">
            <label for="city" class="form-label text-start w-100 ms-4 fw-bold">City Name</label>
            <input type="text" class="form-control mx-auto" id="city" name="city"  placeholder="City Name" style="width: 95%; border-radius: 10px;">
        </div>

        <div class="mb-3">
            <label for="photos" class="form-label text-start w-100 ms-4 fw-bold">Photo</label>
            <input type="file" name="photos[]" class="form-control mx-auto" id="photos" style="width: 95%; border-radius: 10px;" multiple>
            <p class="m-0 ms-4 text-start">You can display your portrait or farm picture in our market.</p>
            <p class="m-0 ms-4 text-start">Acceptable formats are jpeg, jpg, png, and gif only.</p>
            <p class="m-0 ms-4 text-start">The file size is 1048Kb.</p>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label text-start w-100 ms-4 fw-bold">Description</label>
            <input type="text" class="form-control mx-auto" id="description" name="description" placeholder="Description (#hashtag)" style="width: 95%; border-radius: 10px;">
        </div>



        <div class="form-check form-check-inline d-flex justify-content-center align-items-center w-50 mx-auto">

            @foreach ($all_categories as $category)
            <div class="form-check form-check-inline">
                <input type="checkbox" name="category[]" id="{{ $category->category_name }}" value="{{ $category->id }}" class="form-check-input">
                <label for="{{ $category->name }}" class="form-check-label">{{ $category->category_name }}</label>
            </div>
            @endforeach
            {{-- <input type="checkbox" name="category[]" id="" value="" class="form-check-input">
            <label for="category" class="form-check-label me-5">Category</label>
            <input type="checkbox" name="category[]" id="" value="" class="form-check-input">
            <label for="category" class="form-check-label me-5">Category</label>
            <input type="checkbox" name="category[]" id="" value="" class="form-check-input">
            <label for="category" class="form-check-label me-5">Category</label>
            <input type="checkbox" name="category[]" id="" value="" class="form-check-input">
            <label for="category" class="form-check-label me-5">Category</label>
            <input type="checkbox" name="category[]" id="" value="" class="form-check-input">
            <label for="category" class="form-check-label me-5">Category</label> --}}
        </div>




        <button type="submit" class="bg-gold rounded border border-black text-white w-50 fs-2 my-4">Register</button>

    </form>
</div>

<script>
    let autocomplete;

    function initAutocomplete() {
        const input = document.getElementById('address');
        const options = {
            fields: ["address_components", "formatted_address"], // 必要なフィールドのみ
            types: ['geocode'],  // 住所に関連するタイプのみ
            language: 'en',  // 英語表記を優先
        };

        // Autocompleteのインスタンスを作成
        autocomplete = new google.maps.places.Autocomplete(input, options);

        // 住所が選択された後の処理
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            const addressComponents = place.address_components;

            // 英語表記の住所を取得
            const formattedAddress = place.formatted_address;

            // フォームに英語表記の住所を設定
            document.getElementById('address').value = formattedAddress;

            // 市区町村を設定
            let city = '';
            addressComponents.forEach(component => {
                const types = component.types;

                if (types.includes('locality')) {
                    city = component.long_name; // 市区町村（町名を含む場合あり）
                } else if (types.includes('neighborhood')) {
                    city = component.long_name; // 町名（場合によってはここに入ることも）
                }
            });

            // city を対応する入力フィールドに設定
            document.getElementById('city').value = city;
        });
    }
</script>


<script>
    const apiKey = "{{ config('services.google_maps.api_key') }}";

    // Google Maps APIスクリプトを動的に作成して読み込む
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`;
    script.async = true;
    script.defer = true;

    // スクリプトをHTMLに追加
    document.head.appendChild(script);

    script.onload = function () {
        console.log('Google Maps API loaded successfully.');
        // 必要ならここでGoogle Maps API関連の初期化を呼び出す
        initAutocomplete();
    };

    script.onerror = function () {
        console.error('Failed to load Google Maps API.');
    };
</script>

@endsection
