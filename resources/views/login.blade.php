@extends('layout')

@section('content')
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-slate-800">Welcome Back</h1>
        <p class="text-slate-500 mt-2">Log in to manage your orders</p>
    </div>

    @if(session('error'))
        <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm border border-red-100">
            {{ session('error') }}
        </div>
    @endif

    <form action="/login-process" method="POST" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
            <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition" placeholder="name@company.com" required>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition" placeholder="••••••••" required>
        </div>
        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition transform active:scale-95 shadow-lg shadow-orange-200">
            Sign In
        </button>
    </form>

    <div class="mt-8 text-center text-sm text-slate-600">
        Don't have an account? <a href="/signup" class="text-orange-500 font-semibold hover:underline">Create one</a>
    </div>
</div>
@endsection