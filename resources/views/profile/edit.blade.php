<x-app-layout>
    <div class="space-y-6">
        <!-- Main Form Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header inside Form Container -->
            <div class="p-6 border-b border-gray-100 bg-white">
                <h1 class="text-xl font-bold text-gray-900">Pengaturan Profil Akun</h1>
                <p class="text-sm text-gray-500 mt-1">Perbarui informasi profil akun dan kata sandi Anda.</p>
            </div>

            <!-- Form Content: Dua Kolom Bersebelahan -->
            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:divide-x lg:divide-gray-100 items-start">
                    <!-- Kolom Informasi Profil -->
                    <div class="lg:pr-8">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <!-- Kolom Ubah Kata Sandi -->
                    <div class="pt-8 lg:pt-0 lg:pl-8">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
