<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-100" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- Link Dashboard chung --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- ===== LINKS CHO ADMIN (Menu chính) ===== --}}
                    {{-- Sử dụng cách kiểm tra vai trò bạn đã có --}}
                    @if(Auth::check() && Auth::user()->role && Auth::user()->role->name == 'Admin')
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Quản lý Users') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.schools.index')" :active="request()->routeIs('admin.schools.*')">
                            {{ __('Quản lý Trường') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.majors.index')" :active="request()->routeIs('admin.majors.*')">
                            {{ __('Quản lý Ngành') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.schedules.index')" :active="request()->routeIs('admin.schedules.*')">
                            {{ __('Quản lý Lịch') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.diaries.index')" :active="request()->routeIs('admin.diaries.*')">
                            {{ __('Xem Nhật ký') }}
                        </x-nav-link>
                        {{-- >>> THÊM LINK QUẢN LÝ ĐIỂM DANH <<< --}}
                        <x-nav-link :href="route('admin.attendances.index')" :active="request()->routeIs('admin.attendances.*')">
                            {{ __('Quản lý Điểm danh') }}
                        </x-nav-link>
                        {{-- >>> THÊM LINK QUẢN LÝ NHẬN XÉT <<< --}}
                        <x-nav-link :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')">
                            {{ __('Quản lý Nhận xét') }}
                        </x-nav-link>
                        {{-- >>> Thêm Link vào Navigation Admin <<< --}}
                        <x-nav-link :href="route('admin.schedules.index')" :active="request()->routeIs('admin.schedules.index') || request()->routeIs('admin.schedules.create') || request()->routeIs('admin.schedules.edit')">
                            {{ __('Quản lý Lịch') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.schedules.pending')" :active="request()->routeIs('admin.schedules.pending')">
                            {{ __('Duyệt đổi lịch') }}
                             {{-- Optional: Badge đếm số lượng, cần truyền count từ controller hoặc dùng View Composer --}}
                             {{-- @inject('pendingCount', 'App\Services\ScheduleService') --}}
                             {{-- @if($pendingCount->getPendingScheduleCount() > 0)
                                 <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                     {{ $pendingCount->getPendingScheduleCount() }}
                                 </span>
                             @endif --}}
                        </x-nav-link>
                        {{-- Thêm các link Admin khác ở đây sau --}}

                    @endif
                    {{-- ========================================= --}}

                    {{-- ===== LINKS CHO SINH VIÊN (Menu chính) ===== --}}
                    @if(Auth::check() && Auth::user()->role && Auth::user()->role->name == 'SinhVien')
                        <x-nav-link :href="route('student.schedule.index')" :active="request()->routeIs('student.schedule.index')">
                            {{ __('Lịch thực tập') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.diaries.index')" :active="request()->routeIs('student.diaries.*')">
                            {{ __('Nhật ký thực tập') }}
                        </x-nav-link>
                        {{-- Thêm các link Sinh viên khác nếu cần --}}
                    @endif
                    {{-- =========================================== --}}

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            @auth
                                <div>{{ Auth::user()->name }}</div>
                            @else
                                <div>Guest</div>
                            @endauth
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-300 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

             {{-- ===== LINKS CHO ADMIN (Menu Responsive) ===== --}}
             @if(Auth::check() && Auth::user()->role && Auth::user()->role->name == 'Admin')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Quản lý Users') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.schools.index')" :active="request()->routeIs('admin.schools.*')">
                    {{ __('Quản lý Trường') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.majors.index')" :active="request()->routeIs('admin.majors.*')">
                    {{ __('Quản lý Ngành') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.schedules.index')" :active="request()->routeIs('admin.schedules.*')">
                    {{ __('Quản lý Lịch') }}
                </x-responsive-nav-link>
                 <x-responsive-nav-link :href="route('admin.diaries.index')" :active="request()->routeIs('admin.diaries.*')">
                     {{ __('Quản lý Nhật ký') }}
                 </x-responsive-nav-link>
                 {{-- >>> THÊM LINK QUẢN LÝ ĐIỂM DANH (RESPONSIVE) <<< --}}
                 <x-responsive-nav-link :href="route('admin.attendances.index')" :active="request()->routeIs('admin.attendances.*')">
                     {{ __('Quản lý Điểm danh') }}
                 </x-responsive-nav-link>
                 {{-- >>> THÊM LINK QUẢN LÝ NHẬN XÉT (RESPONSIVE) <<< --}}
                 <x-responsive-nav-link :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')">
                    {{ __('Quản lý Nhận xét') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.schedules.index')" :active="request()->routeIs('admin.schedules.index') || request()->routeIs('admin.schedules.create') || request()->routeIs('admin.schedules.edit')">
                    {{ __('Quản lý Lịch') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.schedules.pending')" :active="request()->routeIs('admin.schedules.pending')">
                    {{ __('Duyệt đổi lịch') }}
                </x-responsive-nav-link>
            @endif
            {{-- ============================================= --}}

             {{-- ===== LINKS CHO SINH VIÊN (Menu Responsive) ===== --}}
             @if(Auth::check() && Auth::user()->role && Auth::user()->role->name == 'SinhVien')
                 <x-responsive-nav-link :href="route('student.schedule.index')" :active="request()->routeIs('student.schedule.index')">
                     {{ __('Lịch thực tập') }}
                 </x-responsive-nav-link>
                 <x-responsive-nav-link :href="route('student.diaries.index')" :active="request()->routeIs('student.diaries.*')">
                     {{ __('Nhật ký thực tập') }}
                 </x-responsive-nav-link>
             @endif
             {{-- ================================================== --}}

        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="px-4">
                @auth
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                @else
                     <div class="font-medium text-base text-gray-800 dark:text-gray-200">Guest</div>
                     <div class="font-medium text-sm text-gray-500">Not logged in</div>
                 @endauth
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>