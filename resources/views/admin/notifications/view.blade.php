<x-layouts.app>
  <div class="container mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-xl font-medium text-gray-900">Notifications</h2>
        <p class="text-sm text-gray-500 mt-0.5">
          {{ $notifications->total() }} notification{{ $notifications->total() !== 1 ? 's' : '' }}
        </p>
      </div>
      {{-- <form method="POST" action="{{ route('notifications.read') }}">
        @csrf
        <button type="submit"
          class="inline-flex items-center gap-1.5 text-sm px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
          </svg>
          Mark all as read
        </button>
      </form> --}}
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <table class="w-full table-fixed">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200">
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Title</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Message</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Time</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse ($notifications as $note)
            <tr class="hover:bg-gray-50 transition-colors {{ $note->status == 0 ? 'bg-orange-50 hover:bg-orange-100' : '' }}">

              {{-- Title with unread dot --}}
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  @if ($note->status == 0)
                    <span class="h-2 rounded-full bg-orange-500 shrink-0"></span>
                  @else
                    <span class="h-2 rounded-full bg-gray-200 shrink-0"></span>
                  @endif
                  <span class="text-sm font-medium {{ $note->status == 0 ? 'text-gray-900' : 'text-gray-500' }} truncate">
                    {{ $note->title ?? '—' }}
                  </span>
                </div>
              </td>

              {{-- Message --}}
              <td class="px-4 py-3">
                <p class="text-sm text-gray-500 truncate">{{ $note->description ?? '—' }}</p>
              </td>

              {{-- Time --}}
              <td class="px-4 py-3">
                <span class="text-sm text-gray-400">{{ $note->created_at->diffForHumans() }}</span>
              </td>

              {{-- Status Badge --}}
              <td class="px-4 py-3">
                @if ($note->status == 0)
                  <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-orange-100 text-orange-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                    Unread
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-green-50 text-green-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    Read
                  </span>
                @endif
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-12 text-center text-gray-400 text-sm">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
                No notifications found
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      {{-- Pagination footer --}}
      @if ($notifications->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-xs text-gray-400">
            Showing {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }} of {{ $notifications->total() }}
          </span>
          {{ $notifications->links() }}
        </div>
      @endif
    </div>

  </div>
</x-layouts.app>
