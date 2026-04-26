<tr class="hover:bg-gray-50 transition-colors">
    @foreach($columns as $column)
        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider {{ $column['align'] ?? 'text-left' }}">
            {{ $column['label'] }}
        </th>
    @endforeach
</tr>
