@extends('layouts.app')

@section('title', 'Profile User')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <!-- Profile Card -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <!-- Profile Picture -->
                                <div class="col-md-4 text-center">
                                    <div class="mb-3">
                                        <img src="{{ $user->image_url }}" alt="Profile Picture"
                                            class="rounded-circle img-fluid"
                                            style="width: 150px; height: 150px; object-fit: cover;">
                                    </div>

                                    {{-- @if (auth()->id() == $user->id)
                                        <a href="{{ route('user.edit', '') . $user->id }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit Profile
                                        </a>
                                    @endif --}}
                                </div>

                                <!-- Profile Info -->
                                <div class="col-md-8">
                                    <h3 class="mb-3">{{ $user->username }}</h3>

                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>Email:</strong></div>
                                        <div class="col-sm-8">{{ $user->email }}</div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>NIK:</strong></div>
                                        <div class="col-sm-8">{{ $user->nik }}</div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>Jabatan:</strong></div>
                                        <div class="col-sm-8">{{ $user->jabatan }}</div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>Departemen:</strong></div>
                                        <div class="col-sm-8">{{ $user->departemen }}</div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>Bagian:</strong></div>
                                        <div class="col-sm-8">{{ $user->bagian }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
