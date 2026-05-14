<x-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">{{ __('cobros.articles.edit') }}</h2>
                    <form action="{{ route('articulos-cobro.update', $articulo) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.articulos_cobro.form', ['articulo' => $articulo])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
