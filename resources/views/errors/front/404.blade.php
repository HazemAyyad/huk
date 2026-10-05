<x-app-layout>

    <!-- ====== Banner Section Start -->
    <div class="relative z-10 overflow-hidden pb-[60px] pt-[120px] dark:bg-dark md:pt-[130px] lg:pt-[160px]">
        <div
            class="absolute bottom-0 left-0 w-full h-px bg-linear-to-r from-stroke/0 via-stroke to-stroke/0 dark:via-dark-3">
        </div>
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full px-4">
                    <div class="text-center">
                        <h1
                            class="mb-4 text-3xl font-bold text-dark dark:text-white sm:text-4xl md:text-[40px] md:leading-[1.2]">
                            {{ __('messages.Page not found') }}
                        </h1>

                        <ul class="flex items-center justify-center gap-[10px]">
                            <li>
                                <a href="{{ route('index') }}"
                                    class="flex items-center gap-[10px] text-base font-medium text-dark dark:text-white">
                                    {{ __('messages.Home') }}
                                </a>
                            </li>
                            <li>
                                <a href="#"
                                    class="flex items-center gap-[10px] text-base font-medium text-body-color dark:text-dark-6">
                                    <span class="text-body-color dark:text-dark-6"> / </span>
                                    {{ __('messages.Page not found') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ====== Banner Section End -->

    <div class="w-full flex justify-center items-center" style="margin: 1rem 0; padding: 1rem 0;">
        <div class="flex flex-col justify-center items-center">
            <h1 style="color: red; font-size: 7.5rem; font-weight: 800;">404</h1>
            <h2 class="text-2xl font-semibold">{{ __('messages.Page not found') }}</h2>
            <p>
                {{ __('messages.The page you are looking for does not exist or has been moved.') }}
            </p>
            <a href="{{ route('index') }}"
                class="flex items-center gap-[10px] text-base font-medium text-dark dark:text-white"
                style=" text-decoration: underline;">
                {{ __('messages.Back to Home') }}
            </a>
        </div>
    </div>


</x-app-layout>
