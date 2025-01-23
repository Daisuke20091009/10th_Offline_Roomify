@extends('layouts.app')

<style>
    html,body
    {
        overflow: hidden;
    }
    body
    {
        background-color: #004aad !important;
    }

    .btn
    {
        border-color:#dcbf7d;
        color: #dcbf7d;
        background-color: transparent;
        align-content: left;
        font-weight: bold;
    }


    .btn:hover
    {
        border-color:#dcbf7d !important;
        color:#ffffff !important;
        background-color: #dcbf7d !important;
    }

    .form-control
    {
        border-color: #ffffff;
        background-color: transparent;
    }

    .card .card-header
    {
        color: #ffffff;
    }

    .input-group .input-icon
    {
        color: #ffffff;
        font-size: 30px;
    }

    .input-group input
    {
        border: 1px solid #ffffff;
    }

    .form-control
    {
        background-color: transparent;
        align-content: center;
        padding-left: 2.5rem;
    }
    p
    {
        font-size: 18px;
        font-weight: lighter;
        color: #ffffff;
    }
    ::placeholder
    {
        color: #D3D3D3 !important;
        font-weight: bold;
    }

    a
    {
        color: #dcbf7d !important;
    }

    .input-group select
    {
        width: 100%;
        padding-left: 4rem;
        background-color: transparent;
        color: #ffffff;
    }

    #national_flag
    {
        position: absolute;
        top: 35%;
        transform: translateY(-50%);
    }

    select:invalid
    {
        color:#D3D3D3;
        font-weight: bold
    }


</style>
@section('content')

<div class="container">
    <div class="corner-circle circle-left-1"></div>
    <div class="corner-circle circle-left-2"></div>
    <div class="corner-circle circle-right-1"></div>
    <div class="corner-circle circle-right-2"></div>

    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card border-0 mx-auto w-50">
                <div class="card-header border-0">
                    {{ __('Register') }}
                    <p>Please enter your Name, Email and your Password</p>
                </div>



                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6 input-group">
                                <i class="fa-regular fa-user input-icon"></i>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" style="border-radius: 15px" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Username">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 input-group">
                                <i class="fa-regular fa-envelope input-icon"></i>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" style="border-radius: 15px"name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 input-group">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" style="border-radius: 15px" name="password" required autocomplete="new-password" placeholder="Password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 input-group">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" style="border-radius: 15px" required autocomplete="new-password" placeholder="Re-enter Password">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 input-group">
                                <i class="fa-solid fa-earth-americas input-icon" id="national_flag"></i>

                                <select class="form-select mb-3 input-group" aria-label="Default select example" style="border-radius: 15px;" required>
                                    <option value="" selected><span> Nationality</span></option>
                                    <option value="1">🇯🇵 Japan</option>
                                    <option value="2">🇰🇷 Korea</option>
                                    <option value="3">🇦🇺 Australia</option>
                                    <option value="4">🇪🇸 Spain</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4 mx-auto w-100">
                                <button type="submit" class="btn w-100 fw-bold" style="color:#dcbf7d; border-color: #dcbf7d">
                                    {{ __('Register') }}
                                </button>
                            </div>

                            <p class="fw-light mt-1">Already have an Account? <a href="#">Login!</a></p>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
