<x-app-layout>
    <x-slot name="header">
        {{ __('Create New Joke') }}
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto">
        <div class="bg-white p-6 rounded shadow">
            <form method="POST" action="{{ route('jokes.store') }}">
                @csrf

                <x-input-label for="title" :value="__('Title')" />
                <x-text-input name="title" class="block w-full mt-1 mb-4" required />

                <x-input-label for="content" :value="__('Content')" />
                <textarea name="content" class="block w-full mt-1 mb-4 border-gray-300 rounded" rows="5" required></textarea>

                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
