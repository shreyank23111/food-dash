@extends('layout')

@section('content')
<div class="w-full max-w-5xl bg-white shadow-2xl rounded-3xl overflow-hidden">
    <div class="bg-indigo-900 p-8 text-white">
        <h2 class="text-2xl font-bold">Approval Queue</h2>
        <p class="text-indigo-300">Review and authorize pending restaurant registrations</p>
    </div>

    <div class="p-8">
        <div class="overflow-x-auto b
        
        order border-slate-100 rounded-2xl">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-bold">
                    <tr>
                        <th class="p-4">Restaurant Name</th>
                        <th class="p-4">Phone</th>
                        <th class="p-4">Request Date</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $req)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-800">{{ $req->name }}</td>
                        <td class="p-4 text-slate-600">{{ $req->phone }}</td>
                        <td class="p-4 text-slate-400 text-sm">{{ $req->request_date }}</td>
                        <td class="p-4 text-right">
                            <form action="/admin/approve/{{ $req->request_id }}/{{ $req->restaurant_id }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-indigo-600 hover:bg-green-600 text-white px-5 py-2 rounded-xl font-bold transition duration-300 transform active:scale-95">
                                    Approve
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-16 text-center text-slate-400 italic">
                            No pending requests in the system.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-8">
            <a href="/admin/dashboard" class="text-slate-500 font-bold hover:text-indigo-600 transition flex items-center gap-2">
                <span>&larr;</span> Back to System Control Panel
            </a>
        </div>
    </div>
</div>
@endsection