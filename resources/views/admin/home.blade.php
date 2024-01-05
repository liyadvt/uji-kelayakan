@extends('layouts.template')

@section('content')

@if (Session::get('AlreadyAccess'))
<div class="alert alert-danger">{{ Session::get('AlreadyAccess') }}</div>
@endif

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<style>
    .col{
        font-family: 'Rethink Sans', sans-serif;
        color: #272A39;
    }

    .icon{
        background-color:  #F1F4FF;
        border-radius: 100%;
        width: 70px;
        height: 70px;
        display: flex;
    }

    #icons{
        font-size: 50px;
        margin: 10px;
        color:  #0024AC;
    }

    .jumlah{
        font-size: 40px;
        font-weight: 400;
        margin-left: 10px;
        margin-top: 8px;
        color:  #0024AC;
    }

    .material-symbols-outlined {
        font-variation-settings:
        'FILL' 1,
        'wght' 400,
        'GRAD' 0,
        'opsz' 48
        }

</style>
    <div class="title mx-4" >
        <h3>Dashboard</h3>
        <a class="link-offset-2 link-underline link-underline-opacity-0 text-black"> Dashboard  </a>
    </div>

    <div class="d-flex flex-row align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100 mx-3">
    <div class="row">      
        <div class="card m-2 shadow p-2 bg-body rounded " style="width: 26rem; height: 10rem;">
            <div class="card-body">
                <h5 class="card-title">Peserta Didik</h5>
                <div class="icon">
                    <span class="material-symbols-outlined" id="icons">
                        person
                    </span>
                        <p class="jumlah">{{App\Models\Student::count()}}</p>
                </div>
            </div>
        </div>

        <div class="card m-2 shadow p-2 bg-body rounded" style="width: 16rem;  height: 10rem;">
            <div class="card-body">
                <h5 class="card-title">Administrator</h5>
                <div class="icon">
                    <span class="material-symbols-outlined" id="icons">
                        person
                    </span>
                        <p class="jumlah">{{App\Models\User::where('role', '=', 'admin')->count()}}</p>
                </div>
            </div>
        </div>
                        
        <div class="card m-2 shadow p-2 bg-body rounded" style="width: 16rem;  height: 10rem;">
            <div class="card-body">
            <h5 class="card-title">Pembimbing Siswa</h5>
            <div class="icon">
                    <span class="material-symbols-outlined" id="icons">
                        person
                    </span>
                    <p class="jumlah">{{App\Models\User::where('role', '=', 'ps')->count()}}</p>
            </div>
            </div>
        </div>

        <div class="card m-2 shadow p-2 bg-body rounded " style="width: 26rem; height: 10rem;">
            <div class="card-body">
                <h5 class="card-title">Rombel</h5>
                <div class="icon">
                    <span class="material-symbols-outlined" id="icons">
                        bookmark
                        </span>
                        <p class="jumlah">{{App\Models\Rombel::count()}}</p>
                </div>
            </div>
        </div>

        <div class="card m-2 shadow p-2 bg-body rounded " style="width: 33rem; height: 10rem;">
            <div class="card-body">
                <h5 class="card-title">Rayon</h5>
                <div class="icon">
                    <span class="material-symbols-outlined" id="icons">
                        bookmark
                        </span>
                        <p class="jumlah">{{App\Models\Rayon::count()}}</p>
                </div>
            </div>
        </div>
    </div>


@endsection