@extends('layouts.app')

@section('title', 'Login - Amaron Service Portal')

@section('content')
<div style="width: 100%; max-width: 440px; margin: 0 auto;">

    <!-- AMARON PORTAL BRANDED LOGIN CARD -->
    <div class="card" style="box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08); border-top: 5px solid #76b82a; border-radius: 12px; padding: 2.25rem 2rem; background: #ffffff;">
        
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <!-- Official Amaron Logo Header -->
            <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center;">
                <span style="color: #76b82a; font-weight: 900; font-size: 2.2rem; letter-spacing: -0.03em; line-height: 1;">
                    AMARON
                </span>
                <span style="font-size: 0.65rem; font-weight: 800; color: #111827; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 0.15rem;">
                    LASTS LONG, REALLY LONG.
                </span>
            </div>

            <div style="height: 1px; background-color: #e5e7eb; width: 100%; margin: 1.25rem 0 1rem 0;"></div>

            <h2 style="color: #1f2937; font-weight: 800; font-size: 1.2rem; margin-top: 0;">
                Dwarkadhish Automobiles
            </h2>
            <p style="color: #6b7280; font-size: 0.82rem; margin-top: 0.2rem;">
                Amara Raja Energy & Mobility Ltd. Authorized Portal
            </p>
        </div>

        @if($errors->any())
            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; font-size: 0.85rem; color: #991b1b; font-weight: 600;">
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email" style="font-weight: 700; color: #374151;">Operator Email Address</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="operator@dwarkadhish.com" required autofocus style="border-radius: 8px; padding: 0.7rem 0.9rem;">
            </div>

            <div class="form-group">
                <label class="form-label" for="password" style="font-weight: 700; color: #374151;">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required style="border-radius: 8px; padding: 0.7rem 0.9rem;">
            </div>

            <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <label style="font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 0.45rem; color: #4b5563; font-weight: 600;">
                    <input type="checkbox" name="remember" value="1" style="width: 16px; height: 16px; accent-color: #76b82a;"> Remember Me
                </label>
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 0.78rem; font-size: 0.95rem; font-weight: 800; background-color: #76b82a; color: #ffffff; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.15s ease;">
                LOGIN TO PORTAL →
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; font-size: 0.75rem; color: #9ca3af;">
            Dwarkadhish Enterprise ERP • Amara Raja Authorized Service
        </div>
    </div>

</div>
@endsection
