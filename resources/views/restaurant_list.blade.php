@extends('layout')

@section('content')
<div style="padding: 40px; background: #f8fafc; min-height: 100vh; font-family: sans-serif;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 32px; font-weight: bold; color: #1e293b; margin-bottom: 10px;">Order Food Online</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Discover the best food & drinks in your area.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
            @forelse($restaurants as $res)
                <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: transform 0.2s;">
                    <div style="height: 180px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                        <span style="font-size: 40px;">🍴</span>
                    </div>
                    
                    <div style="padding: 20px;">
                        <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 5px; color: #1e293b;">{{ $res->name }}</h3>
                        <p style="color: #64748b; font-size: 14px; margin-bottom: 15px;">
                            📍 {{ $res->address ?? 'Address not listed' }}
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #22c55e; font-weight: bold; font-size: 14px;">● {{ $res->status }}</span>
                            <a href="/restaurant/{{ $res->restaurant_id }}/menu" style="background: #ff7a2d; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">
                                View Menu
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 100px; color: #94a3b8;">
                    <h2>No restaurants are currently open.</h2>
                    <p>Please check back later!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection