<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-300">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-bold text-lg text-gray-800 dark:text-gray-100">Absensi Kontraktor</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    @if(auth()->user()->isAdmin())
                        <x-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                            Karyawan
                        </x-nav-link>
                        <x-nav-link :href="route('attendances.index')" :active="request()->routeIs('attendances.*')">
                            Absensi
                        </x-nav-link>
                        <x-nav-link :href="route('locations.index')" :active="request()->routeIs('locations.*')">
                            Lokasi
                        </x-nav-link>
                        <x-nav-link :href="route('overtimes.index')" :active="request()->routeIs('overtimes.*')">
                            Lembur
                        </x-nav-link>
                        <x-nav-link :href="route('kasbons.index')" :active="request()->routeIs('kasbons.*')">
                            Kasbon
                        </x-nav-link>
                        <x-nav-link :href="route('payrolls.index')" :active="request()->routeIs('payrolls.*')">
                            Payroll
                        </x-nav-link>
                    @endif

                    @if(auth()->user()->isSupervisor())
                        <x-nav-link :href="route('supervisor.workerMonitoring')" :active="request()->routeIs('supervisor.workerMonitoring')">
                            Monitoring Worker
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="flex items-center me-4">
                    <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5 transition-colors">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <div class="me-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if(auth()->user()->isAdmin()) bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                        @elseif(auth()->user()->isSupervisor()) bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                        @else bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 @endif">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 dark:focus:border-gray-600 transition">
                            <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <button @click="$dispatch('open-profile-modal')" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition duration-150 ease-in-out">
                            {{ __('Profile') }}
                        </button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <div class="flex items-center me-2">
                    <button id="theme-toggle-mobile" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2 transition-colors">
                        <svg id="theme-toggle-dark-icon-mobile" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg id="theme-toggle-light-icon-mobile" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
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
                Dashboard
            </x-responsive-nav-link>

            @if(auth()->user()->isAdmin())
                <x-responsive-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                    Karyawan
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('attendances.index')" :active="request()->routeIs('attendances.*')">
                    Absensi
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('locations.index')" :active="request()->routeIs('locations.*')">
                    Lokasi
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('overtimes.index')" :active="request()->routeIs('overtimes.*')">
                    Lembur
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kasbons.index')" :active="request()->routeIs('kasbons.*')">
                    Kasbon
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('payrolls.index')" :active="request()->routeIs('payrolls.*')">
                    Payroll
                </x-responsive-nav-link>
            @endif

            @if(auth()->user()->isSupervisor())
                <x-responsive-nav-link :href="route('supervisor.workerMonitoring')" :active="request()->routeIs('supervisor.workerMonitoring')">
                    Monitoring Worker
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="px-4 flex items-center">
                <div class="shrink-0 me-3">
                    <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                </div>
                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <button @click="$dispatch('open-profile-modal')" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out">
                    {{ __('Profile') }}
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Profile Modal (AlpineJS) -->
<div 
    x-data="{
        show: false,
        firstName: '{{ Auth::user()->first_name ?? '' }}',
        lastName: '{{ Auth::user()->last_name ?? '' }}',
        education: '{{ Auth::user()->education ?? '' }}',
        photoPreview: '{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : '' }}',
        position: '{{ Auth::user()->employee ? Auth::user()->employee->position : 'Belum Ada Jabatan' }}',
        isSubmitting: false,
        message: '',
        
        updatePreview(event) {
            const file = event.target.files[0];
            if (file) {
                this.photoPreview = URL.createObjectURL(file);
            }
        },
        
        async submitProfile() {
            this.isSubmitting = true;
            this.message = '';
            
            let formData = new FormData(this.$refs.profileForm);
            
            try {
                let response = await fetch('{{ route('profile.update.ajax') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });
                
                let data = await response.json();
                
                if (response.ok && data.success) {
                    this.message = data.message;
                    // Update current names on page without refresh 
                    // (Optional if they have alpine binds, but we let it be for now)
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.message = data.message || 'Terjadi kesalahan validasi.';
                }
            } catch (error) {
                this.message = 'Gagal terhubung ke server.';
            }
            
            this.isSubmitting = false;
        }
    }"
    @open-profile-modal.window="show = true"
    x-show="show"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
    aria-labelledby="modal-title" role="dialog" aria-modal="true"
>
    <!-- Background overlay -->
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/40 backdrop-blur-md transition-opacity" @click="show = false" aria-hidden="true"></div>

    <!-- Modal Box -->
    <div x-show="show" x-transition.scale.origin.center class="relative bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <form x-ref="profileForm" @submit.prevent="submitProfile">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 transition-colors">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Update Profile</h3>
                    
                    <div x-show="message" x-text="message" class="mb-4 p-3 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 rounded text-sm font-medium transition-colors"></div>

                    <div class="space-y-4">
                        <!-- Photo -->
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" class="h-16 w-16 aspect-square rounded-full object-cover border border-gray-200 bg-white">
                                </template>
                                <template x-if="!photoPreview">
                                    <div class="h-16 w-16 aspect-square rounded-full bg-gray-200 flex items-center justify-center text-gray-400 text-xs text-center border border-gray-200 shadow-inner">Foto</div>
                                </template>
                            </div>
                            
                            <input type="file" name="photo" @change="updatePreview" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/40 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/60 transition-colors">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Depan</label>
                                <input type="text" name="first_name" x-model="firstName" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Belakang</label>
                                <input type="text" name="last_name" x-model="lastName" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pendidikan</label>
                            <select name="education" x-model="education" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                                <option value="">-- Pilih Pendidikan --</option>
                                <option value="SD/SMP">SD / SMP</option>
                                <option value="SMA/SMK">SMA / SMK</option>
                                <option value="D3">D3</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan Saat Ini <span class="text-xs text-red-500 dark:text-red-400 font-normal">(Hanya dapat diubah Admin)</span></label>
                            <input type="text" :value="position" disabled class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-300 shadow-sm sm:text-sm cursor-not-allowed transition-colors">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-reverse sm:space-x-3 rounded-b-xl gap-3 sm:gap-0 transition-colors">
                    <button type="button" @click="show = false" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition-colors">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none transition-colors disabled:opacity-50">
                        <span x-show="!isSubmitting">Simpan Profil</span>
                        <span x-show="isSubmitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
