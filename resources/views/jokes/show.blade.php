<x-app-layout>
    <x-slot name="header">
        {{ $joke->title }}
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-bold mb-4">{{ $joke->title }}</h2>
            <p class="text-gray-700">{{ $joke->content }}</p>

            <div class="mt-4">
                <a href="{{ route('jokes.edit', $joke) }}" class="text-blue-600 hover:underline">Edit</a> |
                <a href="{{ route('jokes.index') }}" class="text-gray-600 hover:underline">Back to List</a>
            </div>
        </div>
    </div>
</x-app-layout>
