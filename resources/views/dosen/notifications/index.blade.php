@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Notifikasi</h1>
            <button onclick="markAllAsRead()" class="btn-gradient inline-flex items-center gap-2">
                <i class="fas fa-check-double"></i> Tandai semua dibaca
            </button>
        </div>

        <div class="space-y-4">
            @forelse($notifications as $notification)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 @if(!$notification->read_at) bg-blue-50 border-blue-200 @endif">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @if($notification->type === 'App\Notifications\SuratStatusUpdatedNotification')
                                <i class="fas fa-sync-alt text-green-500 text-lg mt-1"></i>
                            @else
                                <i class="fas fa-info-circle text-gray-500 text-lg mt-1"></i>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900">
                                    {{ $notification->data['message'] ?? 'Notifikasi' }}
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500">
                                        {{ $notification->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }}
                                    </span>
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('dosen.notifications.markNotificationAsRead', $notification->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 text-xs">
                                                <i class="fas fa-check"></i> Tandai dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            
                            @if(isset($notification->data['jenis_surat']))
                                <p class="text-sm text-gray-600 mt-1">
                                    Jenis Surat: {{ $notification->data['jenis_surat'] }}
                                </p>
                            @endif
                            
                            @if(isset($notification->data['status_text']))
                                <p class="text-sm text-gray-600 mt-1">
                                    Status: {{ $notification->data['status_text'] }}
                                </p>
                            @endif
                            
                            @if(isset($notification->data['action_url']))
                                <div class="mt-2">
                                    <a href="{{ $notification->data['action_url'] }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Lihat Detail
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <i class="fas fa-bell-slash text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-500">Belum ada notifikasi</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function markAllAsRead() {
    fetch('{{ route("dosen.notifications.markAsRead") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush
