<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public $photo;
    public string $currentPhoto = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->currentPhoto = $user->photo ? Storage::url($user->photo) : '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        if ($this->photo) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            // Server-side compression using GD
            $tempPath = $this->photo->getRealPath();
            $imageInfo = getimagesize($tempPath);
            $mime = $imageInfo['mime'];

            switch ($mime) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($tempPath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($tempPath);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($tempPath);
                    break;
                default:
                    $image = null;
            }

            if ($image) {
                // Resize if needed (already handled by client, but safe to have)
                $width = imagesx($image);
                $height = imagesy($image);
                $maxDim = 800;
                if ($width > $maxDim || $height > $maxDim) {
                    $ratio = $width / $height;
                    if ($ratio > 1) {
                        $newWidth = $maxDim;
                        $newHeight = $maxDim / $ratio;
                    } else {
                        $newWidth = $maxDim * $ratio;
                        $newHeight = $maxDim;
                    }
                    $newImage = imagecreatetruecolor($newWidth, $newHeight);
                    
                    // Handle transparency for PNG/GIF
                    if ($mime == 'image/png' || $mime == 'image/gif') {
                        imagealphablending($newImage, false);
                        imagesavealpha($newImage, true);
                    }

                    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    $image = $newImage;
                }

                // Save to temp file
                $compressedTempPath = tempnam(sys_get_temp_dir(), 'photo');
                imagejpeg($image, $compressedTempPath, 80); // 80 quality
                
                // Store the compressed file
                $path = 'profile-photos/' . $this->photo->hashName();
                Storage::disk('public')->put($path, file_get_contents($compressedTempPath));
                $validated['photo'] = $path;
                
                // Clean up
                imagedestroy($image);
                unlink($compressedTempPath);
            } else {
                $validated['photo'] = $this->photo->store('profile-photos', 'public');
            }
        } else {
            unset($validated['photo']);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->photo = null;
        $this->currentPhoto = $user->photo ? Storage::url($user->photo) : '';

        $this->dispatch('toast', type: 'success', message: 'Profil berhasil diperbarui.');
        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-slate-400">
            {{ __('Perbarui informasi profil dan alamat email akun Anda.') }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6" x-data="photoUpload()">
        <!-- Photo Upload Section -->
        <div class="flex flex-col items-center sm:items-start space-y-4">
            <x-input-label :value="__('Foto Profil')" />
            
            <div class="group relative">
                <!-- Avatar Container -->
                <div class="h-32 w-32 rounded-3xl overflow-hidden ring-4 ring-white dark:ring-slate-700 shadow-xl transition-all duration-300 group-hover:ring-indigo-500 group-hover:shadow-indigo-500/20">
                    <template x-if="photoPreview">
                        <img class="h-full w-full object-cover" :src="photoPreview" alt="Preview photo" />
                    </template>
                    <template x-if="!photoPreview && currentPhoto">
                        <img class="h-full w-full object-cover" src="{{ $currentPhoto }}" alt="Current photo" />
                    </template>
                    <template x-if="!photoPreview && !currentPhoto">
                        <div class="h-full w-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <span class="text-3xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        </div>
                    </template>

                    <!-- Loading Overlay -->
                    <div wire:loading wire:target="photo" class="absolute inset-0 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-all duration-300">
                        <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <!-- Upload Hover Overlay -->
                    <div @click="$refs.photoInput.click()" 
                         class="absolute inset-0 bg-slate-900/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 cursor-pointer">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Camera Action Button (Visible on mobile or as fallback) -->
                <button type="button" 
                        @click="$refs.photoInput.click()"
                        class="absolute -bottom-2 -right-2 bg-indigo-500 hover:bg-indigo-600 text-white p-2.5 rounded-2xl shadow-lg transition-transform duration-300 hover:scale-110 active:scale-95 focus:outline-none ring-4 ring-white dark:ring-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>

            <!-- Hidden File Input -->
            <input type="file" 
                x-ref="photoInput"
                accept="image/*"
                @change="handleFileSelect"
                class="hidden" />
            
            <div class="text-center sm:text-left">
                <p class="text-sm text-gray-500 dark:text-slate-400 font-medium">JPEG, PNG up to 2MB</p>
                <p class="text-xs text-indigo-500 dark:text-indigo-400">Kompresi otomatis aktif</p>
                <x-input-error class="mt-2" :messages="$errors->get('photo')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
            <div>
                <x-input-label for="name" :value="__('Nama Lengkap')" />
                <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Nomor WhatsApp')" />
                <x-text-input wire:model="phone" id="phone" name="phone" type="text" class="mt-1 block w-full" placeholder="0812xxx" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-4 p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50">
                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ __('Alamat email Anda belum terverifikasi.') }}
                    </p>

                    <button wire:click.prevent="sendVerification" class="mt-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 underline decoration-2 underline-offset-4 transition-colors">
                        {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Tautan verifikasi baru telah dikirim.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end border-t border-gray-100 dark:border-slate-700/50 pt-6">
            <x-action-message class="me-4 text-emerald-500 font-medium" on="profile-updated">
                {{ __('Tersimpan.') }}
            </x-action-message>

            <x-primary-button class="dark:bg-indigo-500 dark:text-white dark:hover:bg-indigo-600 px-8 py-3 rounded-2xl shadow-lg shadow-indigo-500/20 transition-all duration-300 hover:scale-105 active:scale-95">
                {{ __('Simpan Perubahan') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function photoUpload() {
            return {
                photoPreview: null,
                currentPhoto: @js($currentPhoto),
                
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            // Compression logic
                            const canvas = document.createElement('canvas');
                            let width = img.width;
                            let height = img.height;
                            
                            // Max dimensions
                            const MAX_WIDTH = 800;
                            const MAX_HEIGHT = 800;

                            if (width > height) {
                                if (width > MAX_WIDTH) {
                                    height *= MAX_WIDTH / width;
                                    width = MAX_WIDTH;
                                }
                            } else {
                                if (height > MAX_HEIGHT) {
                                    width *= MAX_HEIGHT / height;
                                    height = MAX_HEIGHT;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);

                            // Compress to JPEG with 0.7 quality
                            canvas.toBlob((blob) => {
                                const compressedFile = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });

                                // Preview
                                this.photoPreview = URL.createObjectURL(compressedFile);
                                
                                // Upload to Livewire
                                @this.upload('photo', compressedFile, (uploadedFilename) => {
                                    // Success
                                }, () => {
                                    // Error
                                }, (event) => {
                                    // Progress
                                });
                            }, 'image/jpeg', 0.7);
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    </script>
</section>

