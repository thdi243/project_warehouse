@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="page-title">Selamat Datang {{ Auth::user()->username }}</h1>
                    <p>Here you can manage your tasks, view reports, and access various features.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
