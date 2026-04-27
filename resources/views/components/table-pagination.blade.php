<div class="flex items-center justify-between px-6 py-3 bg-gray-50 border-t border-gray-200">
    <div class="text-sm text-gray-500">
        {{ __('common.footer', ['start' => $paginator->firstItem(), 'end' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
    </div>
    <div>
        {{ $paginator->links() }}
    </div>
</div>
