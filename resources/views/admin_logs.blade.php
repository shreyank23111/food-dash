@extends('layout')

@section('content')
<div class="w-full max-w-6xl bg-white shadow-2xl rounded-3xl overflow-hidden">
    <div class="bg-slate-900 p-8 text-white flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">System Action Logs</h1>
            <p class="text-slate-400 text-sm">Requirement 1.4i: Audit trail of admin activities</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm bg-slate-800 px-4 py-2 rounded-xl hover:bg-slate-700 transition">Back to Panel</a>
    </div>

    <div class="p-8">
        <div class="overflow-x-auto border border-slate-100 rounded-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase text-xs font-bold">
                        <th class="p-4">Admin Name</th>
                        <th class="p-4">Action</th>
                        <th class="p-4">Remark</th>
                        <th class="p-4">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-800">{{ $log->first_name }} {{ $log->last_name }}</td>
                        <td class="p-4"><span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-black">{{ $log->action }}</span></td>
                        <td class="p-4 text-slate-600">{{ $log->remark }}</td>
                        <td class="p-4 text-slate-400 text-sm italic">{{ $log->action_date }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-20 text-center text-slate-400 italic">No logs found. Approve a restaurant to generate data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection