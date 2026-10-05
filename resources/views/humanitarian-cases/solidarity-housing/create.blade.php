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
                            {{ __('messages.Solidarity Housing') }}
                        </h1>
                        <p class="mb-5 text-base text-body-color dark:text-dark-6">
                            {{ __('messages.A program aimed at providing affordable housing for low-income families through community support and cooperation to achieve housing equity.') }}
                        </p>

                        <ul class="flex items-center justify-center gap-[10px]">
                            <li>
                                <a href="{{ route('index') }}"
                                    class="flex items-center gap-[10px] text-base font-medium text-dark dark:text-white">
                                    {{ __('messages.Home') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('humanitarian-cases.solidarity.housing.create') }}"
                                    class="flex items-center gap-[10px] text-base font-medium text-body-color dark:text-dark-6">
                                    <span class="text-body-color dark:text-dark-6"> / </span>
                                    {{ __('messages.Solidarity Housing') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ====== Banner Section End -->

    <!-- ====== Forms Section Start -->
    <section class="bg-[#F4F7FF] py-14 lg:py-[90px] dark:bg-dark">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap -mx-4">
                <div class="w-full px-4">
                    <div class="wow fadeInUp relative max-w-11/12 overflow-hidden rounded-xl shadow-form bg-white dark:bg-dark-2 py-14 px-8 text-center sm:px-12 md:px-[60px]"
                        data-wow-delay=".15s">
                        <h4
                            class="mb-4 text-xl font-bold text-dark dark:text-white sm:text-4xl md:text-[40px] md:leading-[1.2]">
                            {{ __('messages.important_notice') }}
                        </h4>
                        <h4
                            class="mb-4 text-xl text-dark dark:text-white md:leading-[1.2]">
                            {{ __('messages.registration_coming_soon') }}
                        </h4>
                        <h4
                            class="mb-4 text-xl text-dark dark:text-white md:leading-[1.2]">
                            {{ __('messages.transitional_phase') }}
                        </h4>

                        <a href="https://t.me/hakinsan" class="inline-block rounded-md border border-primary bg-transparent px-6 py-[14px] text-base font-medium text-primary transition duration-300 ease-in-out hover:bg-primary hover:text-white group">{{ __('messages.Contact us via Telegram') }}</a>
                        <h4
                            class="text-xl text-dark dark:text-white md:leading-[1.2]" style="margin: 1rem 0">
                            {{ __('messages.Thank you for your understanding and cooperation') }}
                        </h4>
                    </div>
                </div>
                
                {{-- <div class="w-full px-4">
                    <div class="wow fadeInUp relative max-w-11/12 overflow-hidden rounded-xl shadow-form bg-white dark:bg-dark-2 py-14 px-8 text-center sm:px-12 md:px-[60px]"
                        data-wow-delay=".15s" id="app">

                        <h1
                            class="mb-4 text-xl font-bold text-dark dark:text-white sm:text-4xl md:text-[40px] md:leading-[1.2]">
                            {{ __('messages.Register a case') }}
                        </h1>

                        <humanitarian-case-solidarity-create-component></humanitarian-case-solidarity-create-component>
                    </div>
                </div> --}}
            </div>
        </div>
    </section>
    <!-- ====== Forms Section End -->

</x-app-layout>
