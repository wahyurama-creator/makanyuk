<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Makanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-10">
                <a href="{{ route('food.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    + Tambahkan Makanan
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="border px-6 py-4">ID</th>
                            <th class="border px-6 py-4">Nama</th>
                            <th class="border px-6 py-4">Harga</th>
                            <th class="border px-6 py-4">Nilai</th>
                            <th class="border px-6 py-4">Kategori</th>
                            <th class="border px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($food as $item)
                            <tr>
                                <td class="border px-6 py-4">{{ $item->id }}</td>
                                <td class="border px-6 py-4">{{ $item->name }}</td>
                                <td class="border px-6 py-4">{{ number_format($item->price) }}</td>
                                <td class="border px-6 py-4">{{ $item->rate }}</td>
                                <td class="border px-6 py-4">{{ $item->types }}</td>
                                <td class="border px-6 py-4 text-center">
                                    <a href="{{ route('food.edit', $item->id) }}"
                                        class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 mr-1 px-4 rounded"
                                    >
                                        Ubah
                                    </a>
                                    <form action="{{ route('food.destroy', $item->id) }}" method="post" class="inline-block">
                                        {!! method_field('delete') . csrf_field() !!}
                                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border text-center p-5">
                                    Data Tidak Ada
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-5">
                {{ $food->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
