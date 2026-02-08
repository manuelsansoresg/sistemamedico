@props(['links' => []])

<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <!-- Dashboard Home -->
        <li class="inline-flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#27ADFA] transition-colors">
                <i class="fas fa-home mr-2 text-gray-400"></i>
                Dashboard
            </a>
        </li>
        
        @foreach($links as $link)
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                    @if(isset($link['url']) && !$loop->last)
                        <a href="{{ $link['url'] }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#27ADFA] md:ml-2 transition-colors">{{ $link['label'] }}</a>
                    @else
                        <span class="ml-1 text-sm font-medium text-[#1E293B] md:ml-2">{{ $link['label'] }}</span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>