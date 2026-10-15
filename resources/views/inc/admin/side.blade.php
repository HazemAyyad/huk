<div id="dw-s1" class="bmd-layout-drawer bg-faded">
    <div class="container-fluid side-bar-container">
        <header class="pb-0" style="background-color: #f4f4f4;">
            <a href="{{ route('admin.index') }}" class="navbar-brand">
                <img src="{{ asset('/files/logo.png') }}" class="w-full h-full" alt="logo">
            </a>
        </header>
        <p class="side-comment">{{ __('messages.Dashboard') }}</p>
        @if(auth()->check() && auth()->user()->hasRole('superadmin'))
            <ul class="side short"><li class="side-item">
                <a href="{{ route('admin.religious.index') }}" class="side-item">
                    <i class="fas fa-book-open mr-2"></i> الأدعية والمحتوى الديني
                </a>
            </li></ul>
        @endif
        <ul class="side a-collapse short">
            <a class="ul-text">
                <i class="fab fa-telegram-plane"></i> روابط سريعة
                <i class="fas fa-chevron-down arrow"></i>
            </a>

            <div class="side-item-container">
                <li class="side-item">
                    <a href="{{ url('ar/admin/telegram') }}" target="_blank" class="side-item">
                        <i class="fas fa-angle-right mr-2"></i> تيليجرام الإدارة
                    </a>
                </li>

                <li class="side-item">
                    <a href="{{ url('/') }}" target="_blank" class="side-item">
                        <i class="fas fa-angle-right mr-2"></i> صفحة المستفيدين
                    </a>
                </li>
                <li class="side-item">
                    <a href="{{ route('admin.orphans.index') }}" target="_blank" class="side-item">
                        <i class="fas fa-angle-right mr-2"></i> طلبات الايتام
                    </a>
                </li>
            </div>
        </ul>
        <ul class="side a-collapse short">
            <a class="ul-text"><i class="fas fa-chart-line"></i> {{ __('messages.Dashboard') }}
                <i class="fas fa-chevron-down arrow"></i></a>

            <div class="side-item-container">
                <li class="side-item"><a href="{{ route('admin.useradmin') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.Users') }}</a>
                </li>
                <li class="side-item"><a href="{{ route('admin.users.managers.index') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.Managers') }}</a>
                </li>
                <li class="side-item"><a href="{{ route('employee.payments.index') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __("messages.accounts") }}</a>
                </li>
            </div>
        </ul>

        <ul class="side a-collapse short">
            <a class="ul-text"><i class="fas fa-chart-area"></i> {{ __('messages.Areas') }}
                <i class="fas fa-chevron-down arrow"></i></a>

            <div class="side-item-container">
                <li class="side-item"><a href="{{ route('admin.governorates.index') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.Governorates') }}</a>
                </li>

                <li class="side-item"><a href="{{ route('admin.cities.index') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.Cities') }}</a>
                </li>

                <li class="side-item"><a href="{{ route('admin.areas.index') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.Areas') }}</a>
                </li>
            </div>
        </ul>

        <ul class="side a-collapse short">
            <a class="ul-text"><i class="fas fa-tasks"></i> {{ __('messages.Programs') }}
                <i class="fas fa-chevron-down arrow"></i></a>

            <div class="side-item-container">
                <li class="side-item"><a href="{{ route('admin.programs.index') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.Programs') }}</a>
                </li>
            </div>
        </ul>

        <ul class="side a-collapse short">
            <a class="ul-text"><i class="fas fa-comments"></i> الاراء
                <i class="fas fa-chevron-down arrow"></i></a>

            <div class="side-item-container">
                <li class="side-item"><a href="{{ route('admin.reviews.index') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>الاراء</a>
                </li>
            </div>
        </ul>

        <ul class="side a-collapse short">
            <a class="ul-text"><i class="fas fa-user"></i> {{ __('messages.Humanitarian cases') }}
                <i class="fas fa-chevron-down arrow"></i></a>

            <div class="side-item-container">
                <li class="side-item"><a href="{{ route('admin.humanitarian.cases.orphans.index') }}"
                        class="side-item"><i class="fas fa-angle-right mr-2"></i>{{ __('messages.Orphan cases') }}</a>
                </li>
                <li class="side-item"><a href="{{ route('admin.humanitarian.cases.amputation.index') }}"
                        class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.Amputation cases') }}</a>
                </li>
                <li class="side-item"><a href="{{ route('admin.humanitarian.cases.solidarity-housing.index') }}"
                        class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.solidarity-housing') }}</a>
                </li>

                <li class="side-item"><a href="{{ route('admin.humanitarian.cases.family-breadwinner.index') }}"
                        class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.family breadwinner cases') }}</a>
                </li>
                <li class="side-item"><a href="{{ route('admin.humanitarian.cases.stranded-family.index') }}"
                        class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.stranded family cases') }}</a>
                </li>
                <li class="side-item"><a href="{{ route('admin.humanitarian.cases.injured.index') }}"
                        class="side-item"><i class="fas fa-angle-right mr-2"></i>{{ __('messages.injured cases') }}</a>
                </li>
                <li class="side-item"><a href="{{ route('admin.humanitarian.cases.patients.index') }}"
                        class="side-item"><i class="fas fa-angle-right mr-2"></i>{{ __('messages.patient cases') }}</a>
                </li>
                <li class="side-item"><a href="{{ route('admin.humanitarian.cases.assistance-request.index') }}"
                        class="side-item"><i class="fas fa-angle-right mr-2"></i>الحالات العامة</a>
                </li>
            </div>
        </ul>

        <ul class="side a-collapse short">
            <a class="ul-text"><i class="fas fa-map-marker"></i> {{ __('messages.Location') }}
                <i class="fas fa-chevron-down arrow"></i></a>

            <div class="side-item-container">
                <li class="side-item"><a href="{{ route('admin.location') }}" class="side-item"><i
                            class="fas fa-angle-right mr-2"></i>{{ __('messages.Location') }}</a>
                </li>
            </div>
        </ul>
        <ul class="side a-collapse short">
            <a class="ul-text">
                <i class="fas fa-bullhorn"></i> الاعلانات
                <i class="fas fa-chevron-down arrow"></i>
            </a>

            <div class="side-item-container">
                <li class="side-item">
                    <a href="{{ route('photographer.applications') }}" class="side-item">
                        <i class="fas fa-angle-right mr-2"></i> عرض الاعلانات
                    </a>
                </li>
            </div>
        </ul>
    </div>
</div>
