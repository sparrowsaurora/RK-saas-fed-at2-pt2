@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 text-start text-base font-medium
            border-l-4 border-indigo-500
            text-indigo-700 dark:text-indigo-300
            bg-indigo-100 dark:bg-indigo-800
            focus:outline-none
            focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700
            transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 text-start text-base font-medium
            border-l-4 border-transparent
            text-gray-600 hover:text-gray-800
            hover:bg-gray-50 hover:border-gray-300
            focus:outline-none focus:text-gray-800
            focus:bg-gray-50 focus:border-gray-300
            transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
