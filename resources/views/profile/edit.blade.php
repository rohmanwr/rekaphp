@extends('layouts.app')

@section('title', 'Pengaturan Profil')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">Pengaturan Profil</h3>

    <div class="row g-4">
        <!-- Update Profile Information -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Informasi Profil</h5>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <!-- Update Password -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Perbarui Kata Sandi</h5>
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <!-- Delete User Account -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-danger mb-3">Hapus Akun</h5>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection