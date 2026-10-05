<!doctype html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}" dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}"
    class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="google-site-verification" content="kk5Fdpx355TSI9qMxCMUXQ9bBhFZu0DveMlcV-H1Qzk" />
    <meta name="description" content="{{ __('messages.haq_description') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-maps-api-key" content="{{ config('services.google_maps.key') }}">
    
    <!--icon-->
    <link rel="manifest" href="/manifest-users.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/png" sizes="32x32" href="/files/users-192.png">


    <title>
        {{ __('messages.Haq Foundation') }}
    </title>

    <link rel="shortcut icon" href="{{ asset('/files/logo.png') }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('/files/app/assets/css/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('/files/app/assets/css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('/files/app/src/css/tailwind.css') }}" />

    <!-- ==== WOW JS ==== -->
    <script src="{{ asset('/files/app/assets/js/wow.min.js') }}"></script>
    <script>
        new WOW().init();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: "Cairo", sans-serif;
            font-optical-sizing: auto;
            font-weight: <weight>;
            font-style: normal;
            font-variation-settings:
                "slnt" 0;
        }
    </style>
    @stack('styles')
</head>

<body>
    @include('inc.app.navbar')

    <div>
        {{ $slot }}
    </div>

    @include('inc.app.footer')

    <!-- ====== Back To Top Start -->
    <a href="javascript:void(0)"
        class="fixed left-auto items-center justify-center hidden w-10 h-10 text-white transition duration-300 ease-in-out rounded-md shadow-md back-to-top bottom-8 right-8 z-999 bg-primary hover:bg-dark">
        <span class="mt-[6px] h-3 w-3 rotate-45 border-l border-t border-white"></span>
    </a>
    <!-- ====== Back To Top End -->

    <!-- ====== All Scripts -->

    <script src="{{ asset('/files/app/assets/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('/files/app/assets/js/main.js') }}"></script>
    <script>
        // section menu active
        function onScroll(event) {
            const sections = document.querySelectorAll(".ud-menu-scroll");
            const scrollPos =
                window.pageYOffset ||
                document.documentElement.scrollTop ||
                document.body.scrollTop;

            for (let i = 0; i < sections.length; i++) {
                const currLink = sections[i];
                const val = currLink.getAttribute("href");
                const refElement = document.querySelector(val);
                const scrollTopMinus = scrollPos + 73;
                if (
                    refElement.offsetTop <= scrollTopMinus &&
                    refElement.offsetTop + refElement.offsetHeight > scrollTopMinus
                ) {
                    document
                        .querySelector(".ud-menu-scroll")
                        .classList.remove("active");
                    currLink.classList.add("active");
                } else {
                    currLink.classList.remove("active");
                }
            }
        }

        window.document.addEventListener("scroll", onScroll);

    </script>
    <script>
        window.addEventListener("scroll", function () {
            const backToTop = document.querySelector(".back-to-top");
            
            // حساب المسافة المتبقية للوصول للقاع
            const scrollHeight = document.documentElement.scrollHeight;
            const scrollPosition = window.innerHeight + window.pageYOffset;
            
            // يظهر الزر فقط إذا كان المستخدم على بعد 300 بكسل أو أقل من نهاية الصفحة
            if ((scrollHeight - scrollPosition) < 300) {
                backToTop.style.display = "flex";
            } else {
                backToTop.style.display = "none";
            }
        });

        // تفعيل خاصية الصعود للأعلى عند الضغط
        document.querySelector(".back-to-top").addEventListener("click", function () {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    </script>
    @stack('scripts')

    <script src="{{ mix('js/app.js') }}" defer></script>
</body>

</html>
