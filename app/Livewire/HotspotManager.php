<?php

namespace App\Livewire;

use App\Models\HotspotUser;
use App\Models\ActivityLog;
use App\Jobs\BulkGenerateHotspotVouchersJob;
use App\Jobs\ProvisionHotspotUserJob;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class HotspotManager extends Component
{
    use WithPagination;

    public $username, $password, $profile, $hotspot_user_id, $package_id, $expired_at;
    public $type = 'account'; // 'account' or 'voucher'
    public $quantity = 1;
    public $filterPackage = '';
    public $perPage = 10;
    public $packages_list = [];
    public $isOpen = false;
    
    // Progress tracking
    public $voucherProgress = null; // ['current' => x, 'total' => y, 'status' => 'processing|done']

    // Selection
    public $selectedIds = [];
    public $selectAll = false;

    protected $listeners = [
        'confirmDelete' => 'delete',
        'confirmBulkDelete' => 'deleteSelected'
    ];


    public function mount()
    {
        $this->loadPackages();
    }

    public function checkVoucherProgress()
    {
        $progress = \Illuminate\Support\Facades\Cache::get("voucher_progress_" . auth()->id());

        if ($progress) {
            $this->voucherProgress = $progress;

            if ($progress['status'] === 'done') {
                // Bersihkan cache & reset progress setelah 2 detik berikutnya
                \Illuminate\Support\Facades\Cache::forget("voucher_progress_" . auth()->id());
                $this->voucherProgress = null;
                $this->dispatch('toast', type: 'success', message: 'Semua voucher berhasil di-generate!');
            }
        } else {
            $this->voucherProgress = null;
        }
    }

    public function updatedFilterPackage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $limit = $this->perPage === 'all' ? 999999 : (int) $this->perPage;
        
        $query = auth()->user()->hotspotUsers()
            ->with('package');

        if ($this->filterPackage) {
            $query->where('package_id', $this->filterPackage);
        }

        $hotspotUsers = $query->orderBy('id', 'desc')
            ->paginate($limit);

        return view('livewire.hotspot-manager', ['hotspotUsers' => $hotspotUsers])
            ->layout('layouts.app');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $limit = $this->perPage === 'all' ? 999999 : (int) $this->perPage;
            $query = auth()->user()->hotspotUsers();
            if ($this->filterPackage) {
                $query->where('package_id', $this->filterPackage);
            }
            $this->selectedIds = $query->orderBy('id', 'desc')
                ->limit($limit)
                ->pluck('id')
                ->map(fn($id) => (string)$id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds()
    {
        $limit = $this->perPage === 'all' ? 999999 : (int) $this->perPage;
        $count = auth()->user()->hotspotUsers()->limit($limit)->count();
        $this->selectAll = count($this->selectedIds) === $count && $count > 0;
    }

    public function printSelected()
    {
        if (empty($this->selectedIds)) {
            return;
        }

        $ids = implode(',', $this->selectedIds);
        return redirect()->route('hotspot.print-vouchers', ['ids' => $ids]);
    }


    public function loadPackages()
    {
        $this->packages_list = auth()->user()->packages()
            ->where('tipe', 'hotspot')
            ->orderBy('name')
            ->get();
    }


    public function create()
    {
        $this->loadPackages();
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->username = '';
        $this->password = '';
        $this->package_id = null;
        $this->profile = 'default';
        $this->hotspot_user_id = '';
        $this->type = 'account';
        $this->quantity = 1;
        $this->expired_at = now()->addDay()->format('Y-m-d\TH:i');
    }

    public function updatedPackageId($value)
    {
        if ($value) {
            $package = auth()->user()->packages()->find($value);
            if ($package) {
                $this->profile = $package->mikrotik_profile ?: 'default';
            }
        }
    }

    public function store()
    {
        $rules = [
            'package_id' => 'required|exists:packages,id',
            'type' => 'required|in:account,voucher',
        ];

        if ($this->type === 'account') {
            $rules['username'] = 'required';
            $rules['password'] = 'required';
            $rules['expired_at'] = 'nullable|date';
        } else {
            $rules['quantity'] = 'required|integer|min:1';
            $rules['expired_at'] = 'nullable|date';
        }

        $this->validate($rules);

        try {
            $package = auth()->user()->packages()->findOrFail($this->package_id);
            $this->profile = $package->mikrotik_profile ?: 'default';
            $oldUsername = null;

            if ($this->type === 'voucher') {
                // Dispatch Job untuk proses background (Insert DB + API Mikrotik)
                BulkGenerateHotspotVouchersJob::dispatch(
                    auth()->id(),
                    $this->package_id,
                    (int) $this->quantity,
                    null,
                    $this->expired_at
                );
                
                session()->flash('message', 'Proses generate ' . $this->quantity . ' voucher sedang berjalan di background.');

                // Set awal progress
                $this->voucherProgress = [
                    'current' => 0,
                    'total'   => (int) $this->quantity,
                    'status'  => 'processing',
                ];

                // Log Activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'title' => 'GENERATE VOUCHER',
                    'message' => "Men-generate {$this->quantity} voucher hotspot baru (Proses Background)",
                    'type' => 'hotspot_crud'
                ]);
            } else {
                if ($this->hotspot_user_id) {
                    $hotspotUser = HotspotUser::findOrFail($this->hotspot_user_id);
                    $oldUsername = $hotspotUser->username;
                    $hotspotUser->update([
                        'username' => $this->username,
                        'password' => $this->password,
                        'profile' => $this->profile,
                        'package_id' => $this->package_id,
                        'expired_at' => $this->expired_at,
                    ]);
                    $action = 'update';
                } else {
                    $hotspotUser = HotspotUser::create([
                        'user_id' => auth()->id(),
                        'username' => $this->username,
                        'password' => $this->password,
                        'profile' => $this->profile,
                        'package_id' => $this->package_id,
                        'expired_at' => $this->expired_at,
                    ]);
                    $action = 'create';
                    $actionTitle = 'TAMBAH USER HOTSPOT';
                    $actionMsg = "Menambahkan user hotspot: {$hotspotUser->username}";
                }

                // Log Activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'title' => $actionTitle ?? ($this->hotspot_user_id ? 'UPDATE USER HOTSPOT' : 'TAMBAH USER HOTSPOT'),
                    'message' => $actionMsg ?? ($this->hotspot_user_id ? "Memperbarui user hotspot: {$hotspotUser->username}" : "Menambahkan user hotspot: {$hotspotUser->username}"),
                    'type' => 'hotspot_crud'
                ]);

                // Dispatch Job untuk single user
                ProvisionHotspotUserJob::dispatch($hotspotUser, $action, $oldUsername);
                session()->flash('message', 'User Hotspot berhasil disimpan.');
            }

            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = HotspotUser::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $this->hotspot_user_id = $id;
        $this->username = $user->username;
        $this->password = $user->password;
        $this->package_id = $user->package_id;
        $this->profile = $user->profile;
        $this->expired_at = $user->expired_at ? \Carbon\Carbon::parse($user->expired_at)->format('Y-m-d\TH:i') : null;
        $this->loadPackages();
        $this->type = 'account';
        $this->openModal();
    }

    public function requestDelete($id)
    {
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'title' => 'Hapus User Hotspot?',
            'text' => 'Data akan dihapus dari database dan MikroTik.',
            'id' => $id,
            'callback' => 'confirmDelete'
        ]);
    }

    public function delete($id)
    {
        try {
            $user = HotspotUser::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $hotspotName = $user->username;
            ProvisionHotspotUserJob::dispatchSync($user, 'delete');
            $user->delete();
            session()->flash('message', 'User Hotspot dihapus.');

            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'HAPUS USER HOTSPOT',
                'message' => "Menghapus user hotspot: {$hotspotName}",
                'type' => 'hotspot_crud'
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function requestBulkDelete()
    {
        if (empty($this->selectedIds)) return;

        $this->dispatch('swal:confirm', [
            'type' => 'danger',
            'title' => 'Hapus Masal?',
            'text' => count($this->selectedIds) . ' data akan dihapus permanen dari database dan MikroTik.',
            'id' => null,
            'callback' => 'confirmBulkDelete'
        ]);
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) return;

        try {
            $users = HotspotUser::whereIn('id', $this->selectedIds)
                ->where('user_id', auth()->id())
                ->get();

            $count = count($users);
            foreach ($users as $user) {
                ProvisionHotspotUserJob::dispatchSync($user, 'delete');
                $user->delete();
            }

            $this->selectedIds = [];
            $this->selectAll = false;
            session()->flash('message', 'Berhasil menghapus ' . $count . ' data.');

            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'HAPUS MASAL HOTSPOT',
                'message' => "Menghapus masal {$count} user hotspot",
                'type' => 'hotspot_crud'
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal hapus masal: ' . $e->getMessage());
        }
    }
}
