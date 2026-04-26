<div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            {{ $header }}
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            {{ $body }}
        </tbody>
    </table>
    @isset($footer)
        {{ $footer }}
    @endisset
</div>
