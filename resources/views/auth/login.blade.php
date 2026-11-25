@extends('layouts.app')

@section('title', 'Sign in — Dwarkadhish Automobiles')

@section('content')
<div style="width: 100%; max-width: 420px; margin: 0 auto;">

    <div class="card" style="padding: 32px; margin-bottom: 0;">
        <div style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 12px; color: var(--text-muted);">
                <span style="width: 6px; height: 6px; background-color: var(--primary); border-radius: 50%; display: inline-block;"></span>
                <span>Amaron Authorized Service</span>
            </div>
            <h1 style="color: var(--text-main); font-weight: 600; font-size: 20px; margin: 0;">
                Dwarkadhish Automobiles
            </h1>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">
                Sign in to battery warranty and inward operations
            </p>
        </div>

        @if($errors->any())
            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-left: 3px solid var(--accent-red); padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; color: #991b1b; font-weight: 500;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="operator@dwarkadhish.com" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            </div>

            <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <label style="font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; color: var(--text-secondary);">
                    <input type="checkbox" name="remember" value="1" style="width: 16px; height: 16px; accent-color: var(--primary);"> Remember me
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; height: 40px; font-size: 14px;">
                Sign in
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color); font-size: 12px; color: var(--text-muted);">
            Dwarkadhish Enterprise · Amara Raja Authorized Service
        </div>
    </div>

</div>
@endsection
