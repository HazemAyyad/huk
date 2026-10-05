<x-app-layout>
    <!-- ====== Hero Section Start -->
    <div id="home" class="relative overflow-hidden bg-primary pt-[120px] md:pt-[130px] lg:pt-[160px]">
        <div class="container px-4 mx-auto">
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full px-4">
                    <div class="hero-content wow fadeInUp mx-auto max-w-[780px] text-center" data-wow-delay=".2s">
                    <!-- Logo -->
                    <div class="mb-6 flex justify-center">
                        <img 
                            src="{{ asset('files/logo2.png') }}" 
                            alt="HAQ Logo"
                            id="heroLogo"
                            class="w-30 sm:w-34 md:w-38 lg:w-42
                                transition-transform duration-300 ease-out
                                drop-shadow-lg"
                        >
                    </div>
                    <h1
                        class="mb-6 text-3xl font-bold leading-snug text-white sm:text-4xl sm:leading-snug lg:text-5xl lg:leading-[1.2]">
                        {{ __('messages.haq_title') }}
                    </h1>
                    <p
                        class="mx-auto mb-9 max-w-[600px] text-base font-medium text-white sm:text-lg sm:leading-[1.44]">
                        {{ __('messages.haq_sub_title') }}
                    </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ====== Hero Section End -->

    <!-- ====== Features Section Start -->
    <section class="pb-8 pt-20 dark:bg-dark lg:pb-[70px] lg:pt-[120px]">
        <div class="container px-4 mx-auto">
            <div class="flex flex-wrap -mx-4">
                <div class="w-full px-4">
                    <div class="mx-auto mb-12 max-w-[485px] text-center lg:mb-[70px]">
                        <span class="block mb-2 text-lg font-semibold text-primary">
                            {{ __('messages.Humanitarian cases') }}
                        </span>
                        <h2
                            class="mb-3 text-3xl font-bold text-dark dark:text-white sm:text-4xl md:text-[40px] md:leading-[1.2]">
                            {{ __('messages.The main humanitarian cases at Haq Association') }}
                        </h2>
                        <p class="text-base text-body-color dark:text-dark-6">
                            {{ __('messages.Provides humanitarian and developmental services to support the needy with dignity.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap -mx-4">
                <div class="w-full px-4 md:w-1/2 lg:w-1/4">
                    <a href="{{ route('detail-cases.orphans.create') }}" class="w-full h-full">
                        <div class="mb-12 wow fadeInUp group" data-wow-delay=".1s">
                            <div
                                class="relative z-10 mb-10 flex h-[70px] w-[70px] items-center justify-center rounded-[14px] bg-primary">
                                <span
                                    class="absolute left-0 top-0 -z-1 mb-8 flex h-[70px] w-[70px] rotate-[25deg] items-center justify-center rounded-[14px] bg-primary/20 duration-300 group-hover:rotate-45"></span>
                                <img src="{{ asset('files/orphan.png') }}" alt="Orphans" class="w-10 h-10 object-contain">

                            </div>
                            <h4 class="mb-3 text-xl font-bold text-dark dark:text-white">
                                {{ __('messages.Orphan cases') }}
                            </h4>
                            <p class="mb-8 text-body-color dark:text-dark-6 lg:mb-9">
                                {{ __('messages.Orphan cases refer to children who have lost one or both parents and are in need of emotional support, proper care, and opportunities to ensure a stable and dignified life.') }}
                            </p>
                            <a href="{{ route('detail-cases.orphans.create') }}"
                                class="text-base font-medium text-dark hover:text-primary dark:text-white dark:hover:text-primary">
                                {{ __('messages.Register a case') }}
                            </a>
                        </div>
                    </a>
                </div>

                <div class="w-full px-4 md:w-1/2 lg:w-1/4">
                    <a href="{{ route('detail-cases.amputation.create') }}" class="w-full h-full">
                        <div class="mb-12 wow fadeInUp group" data-wow-delay=".15s">
                            <div
                                class="relative z-10 mb-10 flex h-[70px] w-[70px] items-center justify-center rounded-[14px] bg-primary">
                                <span
                                    class="absolute left-0 top-0 -z-1 mb-8 flex h-[70px] w-[70px] rotate-[25deg] items-center justify-center rounded-[14px] bg-primary/20 duration-300 group-hover:rotate-45"></span>
                                <img src="{{ asset('files/amputation.png') }}" alt="Amputation" class="w-10 h-10 object-contain">
                            </div>
                            <h4 class="mb-3 text-xl font-bold text-dark dark:text-white">
                                {{ __('messages.Amputation cases') }}
                            </h4>

                            <p class="mb-8 text-body-color dark:text-dark-6 lg:mb-9">
                                {{ __('messages.Amputation cases involve the partial or complete loss of a limb due to an accident or illness and require medical care, as well as psychological and physical rehabilitation, to regain mobility and reintegrate into society.') }}
                            </p>

                            <a href="{{ route('detail-cases.amputation.create') }}"
                                class="text-base font-medium text-dark hover:text-primary dark:text-white dark:hover:text-primary">
                                {{ __('messages.Register a case') }}
                            </a>
                        </div>
                    </a>
                </div>


                <div class="w-full px-4 md:w-1/2 lg:w-1/4">
                    <a href="{{ route('wheelchair.check') }}" class="w-full h-full">
                        <div class="mb-12 wow fadeInUp group" data-wow-delay=".15s">
                            <div
                                class="relative z-10 mb-10 flex h-[70px] w-[70px] items-center justify-center rounded-[14px] bg-primary">
                                <span
                                    class="absolute left-0 top-0 -z-1 mb-8 flex h-[70px] w-[70px] rotate-[25deg] items-center justify-center rounded-[14px] bg-primary/20 duration-300 group-hover:rotate-45"></span>
                                <img src="{{ asset('files/wheelchair.png') }}" alt="Wheelchair" class="w-10 h-10 object-contain">
                            </div>
                            <h4 class="mb-3 text-xl font-bold text-dark dark:text-white">
                                كرسي متحرك
                            </h4>

                            <p class="mb-8 text-body-color dark:text-dark-6 lg:mb-9">
                              تستهدف هذه الحالات أصحاب البتر ومستخدمي الكراسي الكهربائية الذين يحتاجون لمساعدة متقدمة للتنقل بسلاسة.
                            </p>

                            <a href="{{ route('wheelchair.check') }}"
                                class="text-base font-medium text-dark hover:text-primary dark:text-white dark:hover:text-primary">
                                لتسجيل حالة اضغط هنا
                            </a>
                        </div>
                    </a>
                </div>


            </div>
        </div>
    </section>
    <!-- ====== Features Section End -->

    <!-- ====== About Section Start -->
    <section id="about" class="bg-gray-1 pb-8 pt-20 dark:bg-dark-2 lg:pb-[70px] lg:pt-[120px]">
        <div class="container px-4 mx-auto">
            <div class="wow fadeInUp" data-wow-delay=".2s">
                <div class="flex flex-wrap items-center -mx-4">
                    <div class="w-full px-4 lg:w-2/3 mx-auto">
                        <div class="mb-12 lg:mb-0 text-center">
                            <!-- Logo -->
                            <div class="mb-6 flex justify-center">
                                <img 
                                    src="{{ asset('files/logo2.png') }}" 
                                    alt="HAQ Logo"
                                    id="heroLogo"
                                    class="w-30 sm:w-34 md:w-38 lg:w-42
                                        transition-transform duration-300 ease-out
                                        drop-shadow-lg"
                                >
                            </div>
                            <h2 class="mb-5 text-3xl font-bold leading-tight text-dark dark:text-white sm:text-[40px] sm:leading-[1.2]">
                                {{ __('messages.haq_title') }}
                            </h2>
                            <p class="mb-10 text-base leading-relaxed text-body-color dark:text-dark-6 max-w-[800px] mx-auto text-pretty">
                                {{ __('messages.haq_description') }}
                            </p>
                            
                            <ul class="flex flex-wrap items-center justify-center gap-5">
                                <li>
                                    <a href="https://t.me/Hakinsan" target="_blank"
                                        class="flex items-center gap-4 rounded-md border border-primary bg-transparent px-6 py-[14px] text-base font-medium text-primary transition duration-300 ease-in-out hover:bg-primary hover:text-white group">
                                        
                                        <img src="{{ asset('files/telegram.png') }}" alt="Telegram" class="w-10 h-10 object-contain transition duration-300 group-hover:brightness-0 group-hover:invert">

                                        {{ __('messages.Contact us via Telegram') }}
                                    </a>
                                </li>

                                <li>
                                    <a href="https://t.me/HakInsan2023" target="_blank"
                                        class="flex items-center gap-4 rounded-md border border-primary bg-transparent px-6 py-[14px] text-base font-medium text-primary transition duration-300 ease-in-out hover:bg-primary hover:text-white group">
                                        
                                        <img src="{{ asset('files/telegram.png') }}" alt="Telegram" class="w-10 h-10 object-contain transition duration-300 group-hover:brightness-0 group-hover:invert">

                                        {{ __('messages.Follow us on Telegram') }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div style="margin: 2rem auto;" class="max__w" id="app">
                            <reviews-component></reviews-component>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== About Section End -->

    <section
      id="testimonials"
      class="overflow-hidden bg-gray-1 py-20 dark:bg-dark-2 md:py-[120px]"
    >
      <div class="container px-4 mx-auto">
        <div class="flex flex-wrap justify-center -mx-4">
          <div class="w-full px-4">
            <div class="mx-auto mb-[60px] max-w-[485px] text-center">
              <span class="block mb-2 text-lg font-semibold text-primary">
                {{ __("messages.Testimonials") }}
              </span>
              <h2
                class="mb-3 text-3xl font-bold leading-[1.2] text-dark dark:text-white sm:text-4xl md:text-[40px]"
              >
                {{ __("messages.What our Clients Say") }}
              </h2>
            </div>
          </div>
        </div>

        <div class="-m-5">
          <div class="p-5 swiper testimonial-carousel common-carousel">
            <div class="swiper-wrapper">
              @foreach ($reviews as $item)    
                <div class="swiper-slide">
                  <div
                    class="rounded-xl bg-white px-4 py-[30px] shadow-testimonial dark:bg-dark sm:px-[30px]"
                  >
                    <div class="mb-[18px] flex items-center gap-[2px]">
                      <div class="review-rating stars">
                        @for ($star = 1; $star <= 5; $star++)
                            <span class="star {{ $star <= $item->rating ? 'active' : '' }}">
                                ★
                            </span>
                        @endfor
                      </div>
                    </div>

                    <p class="mb-6 text-base text-body-color dark:text-dark-6">
                      “{{ $item->message }}’’
                    </p>

                    <a href="#" class="flex items-center gap-4">
                      <div class="h-[50px] w-[50px] overflow-hidden rounded-full">
                        <img
                          src="{{ asset("/files/app/assets/images/testimonials/author-01.jpg") }}"
                          alt="author"
                          class="h-[50px] w-[50px] overflow-hidden rounded-full"
                        />
                      </div>

                      <div>
                        <h3
                          class="text-sm font-semibold text-dark dark:text-white"
                        >
                          {{ $item->name }}
                        </h3>
                      </div>
                    </a>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="mt-[60px] flex items-center justify-center gap-1">
              <div class="swiper-button-prev">
                <svg
                  class="fill-current"
                  width="22"
                  height="22"
                  viewBox="0 0 22 22"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M19.25 10.2437H4.57187L10.4156 4.29687C10.725 3.9875 10.725 3.50625 10.4156 3.19687C10.1062 2.8875 9.625 2.8875 9.31562 3.19687L2.2 10.4156C1.89062 10.725 1.89062 11.2063 2.2 11.5156L9.31562 18.7344C9.45312 18.8719 9.65937 18.975 9.86562 18.975C10.0719 18.975 10.2437 18.9062 10.4156 18.7687C10.725 18.4594 10.725 17.9781 10.4156 17.6688L4.60625 11.7906H19.25C19.6625 11.7906 20.0063 11.4469 20.0063 11.0344C20.0063 10.5875 19.6625 10.2437 19.25 10.2437Z"
                  />
                </svg>
              </div>

              <div class="swiper-button-next">
                <svg
                  class="fill-current"
                  width="22"
                  height="22"
                  viewBox="0 0 22 22"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M19.8 10.45L12.6844 3.2313C12.375 2.92192 11.8938 2.92192 11.5844 3.2313C11.275 3.54067 11.275 4.02192 11.5844 4.3313L17.3594 10.2094H2.75C2.3375 10.2094 1.99375 10.5532 1.99375 10.9657C1.99375 11.3782 2.3375 11.7563 2.75 11.7563H17.4281L11.5844 17.7032C11.275 18.0126 11.275 18.4938 11.5844 18.8032C11.7219 18.9407 11.9281 19.0094 12.1344 19.0094C12.3406 19.0094 12.5469 18.9407 12.6844 18.7688L19.8 11.55C20.1094 11.2407 20.1094 10.7594 19.8 10.45Z"
                  />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    @push('scripts')
        <script>
            const testimonialSwiper = new Swiper(".testimonial-carousel", {
                slidesPerView: 1,
                spaceBetween: 30,

                // Navigation arrows
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },

                autoplay: {
                    delay: 3000, // الوقت بين كل سلايد والتانية (3 ثواني)
                    disableOnInteraction: false, // يكمل autoplay حتى بعد ما المستخدم يلمس السلايدر
                },

                loop: true, // مهم علشان يلف بشكل مستمر


                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                    1280: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                },
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .reviews-wrapper {
                margin: 1.5rem 0;
            }

            .review-card {
                background-color: #f1f1f1;
                padding: 1rem;
                border-radius: 16px;
                margin-bottom: 1rem;
            }

            .review-content {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                width: 100%;
            }

            /* Header */
            .review-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1.25rem;
            }

            .review-date {
                color: #111111;
                font-size: 14px;
                font-weight: 400;
            }

            /* Rating */
            .stars {
                display: flex;
                gap: 4px;
            }

            .star {
                font-size: 22px;
                color: #ccc; /* نجمة فاضية */
            }

            .star.active {
                color: #f5b301; /* نجمة مليانة */
            }


            .rating-number {
                color: #000;
                font-weight: 700;
                font-size: 32px;
            }

            /* Message */
            .review-message {
                color: #000;
                font-size: 15px;
                line-height: 1.6;
            }

            @media (min-width: 641px) {
                .max__w{
                    max-width: 55%;
                }
            }
        </style>
    @endpush

</x-app-layout>
