@extends('emails.layout')

@section('content')
<div style="max-width: 400px; margin: 0 auto;">
    <p style="color: #737373;">
        You have entered <b style="color: #405D80;">{{ $email }}</b> as the <br />
        email address for your account. Please verify this <br />
        email address by clicking the button below.
    </p>

    <a href="{{ $link }}"
        style="background-color: #F0F6FF; font-weight: bold; text-decoration: none; display: inline-block; cursor: pointer; color: #222; margin-top: 32px; border: 1px solid #737373; border-radius: 4px; min-with: 358px; padding: 10px 150px;">
        Verify now
    </a>
</div>
@endsection
