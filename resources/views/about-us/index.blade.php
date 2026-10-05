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
                            {{ __('messages.About us') }}
                        </h1>
                        <p class="mb-5 text-base text-body-color dark:text-dark-6">
                            {{ __('messages.We serve humanity with compassion and sustainable development.') }}
                        </p>

                        <ul class="flex items-center justify-center gap-[10px]">
                            <li>
                                <a href="{{ route('index') }}"
                                    class="flex items-center gap-[10px] text-base font-medium text-dark dark:text-white">
                                    {{ __('messages.Home') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('about.index') }}"
                                    class="flex items-center gap-[10px] text-base font-medium text-body-color dark:text-dark-6">
                                    <span class="text-body-color dark:text-dark-6"> / </span>
                                    {{ __('messages.About us') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ====== Banner Section End -->

    <!-- ====== About Section Start -->
    <section class="bg-gray-1 pb-8 pt-20 dark:bg-dark-2 lg:pb-[70px] lg:pt-[120px]">
        <div class="container px-4 mx-auto">
            <div class="wow fadeInUp" data-wow-delay=".2s">
                <div class="flex flex-wrap items-center -mx-4">
                    <div class="w-full px-4 lg:w-1/2 mx-auto w-2/2">
                        <div class="mb-12 lg:mb-0 text-center">
                            <h2
                                class="mb-5 text-3xl font-bold leading-tight text-dark dark:text-white sm:text-[40px] sm:leading-[1.2]">
                                {{ __('messages.haq_title') }}
                            </h2>
                            <p class="mb-10 text-base leading-relaxed text-body-color dark:text-dark-6">
                                {{ __('messages.haq_description') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== About Section End -->

</x-app-layout>
