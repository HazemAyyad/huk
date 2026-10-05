<x-admin-layout>

    <div class="w-full flex justify-center items-center py-[5rem]">
        <div class="flex flex-col justify-center items-center">
            <h1 class="text-9xl font-bold text-red-500">404</h1>
            <h2 class="text-2xl font-semibold mt-4">{{ __('messages.Page not found') }}</h2>
            <p class="text-gray-600 mt-2">
                {{ __('messages.The page you are looking for does not exist or has been moved.') }}
            </p>
            <a href="{{ route('admin.index') }}"
                class="mt-6 inline-block px-4 py-2 bg-blue-600 text-white text-base rounded-lg shadow hover:bg-blue-700 transition">
                {{ __('messages.Back to Home') }}
            </a>
        </div>
    </div>


</x-admin-layout>
