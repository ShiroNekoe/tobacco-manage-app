<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MasterDataManagement extends Component
{
    public string $activeTab = 'customers'; // customers, products, origins, pack_types

    // Customer Form Fields
    public bool $showCustomerModal = false;
    public ?int $customer_id = null;
    public string $customer_name = '';
    public string $customer_code = '';
    public string $contact_person = '';
    public string $phone = '';
    public string $address = '';

    // Product Type Form Fields
    public bool $showProductModal = false;
    public ?int $product_id = null;
    public string $product_code = '';
    public string $product_name = '';

    // Origin Form Fields
    public bool $showOriginModal = false;
    public ?int $origin_id = null;
    public string $region_name = '';

    // Pack Type Form Fields
    public bool $showPackTypeModal = false;
    public string $pack_type_name = '';

    public function mount()
    {
        $this->ensureAdminAccess();
    }

    protected function ensureAdminAccess()
    {
        $user = Auth::user();
        if ($user && ! ($user->isAdmin() || $user->isSupervisor() || $user->isDirector())) {
            abort(403, 'Hanya Admin atau Supervisor yang dapat mengelola Master Data.');
        }
    }

    // --- CUSTOMER ACTIONS ---
    public function openCustomerModal(?int $id = null)
    {
        $this->ensureAdminAccess();
        $this->resetCustomerFields();

        if ($id) {
            $cust = Customer::findOrFail($id);
            $this->customer_id = $cust->id;
            $this->customer_name = $cust->name;
            $this->customer_code = $cust->code;
            $this->contact_person = $cust->contact_person ?? '';
            $this->phone = $cust->phone ?? '';
            $this->address = $cust->address ?? '';
        }

        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        $this->ensureAdminAccess();
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'customer_code' => 'required|string|max:100|unique:customers,code,' . $this->customer_id,
        ]);

        Customer::updateOrCreate(
            ['id' => $this->customer_id],
            [
                'name' => $this->customer_name,
                'code' => strtoupper($this->customer_code),
                'contact_person' => $this->contact_person,
                'phone' => $this->phone,
                'address' => $this->address,
            ]
        );

        $this->showCustomerModal = false;
        $this->resetCustomerFields();
        session()->flash('message', 'Data Pelanggan (Customer) berhasil disimpan.');
    }

    public function deleteCustomer(int $id)
    {
        $this->ensureAdminAccess();
        Customer::findOrFail($id)->delete();
        session()->flash('message', 'Pelanggan berhasil dihapus.');
    }

    protected function resetCustomerFields()
    {
        $this->reset(['customer_id', 'customer_name', 'customer_code', 'contact_person', 'phone', 'address']);
    }

    // --- PRODUCT TYPE ACTIONS ---
    public function openProductModal(?int $id = null)
    {
        $this->ensureAdminAccess();
        $this->resetProductFields();

        if ($id) {
            $prod = ProductType::findOrFail($id);
            $this->product_id = $prod->id;
            $this->product_code = $prod->code;
            $this->product_name = $prod->name;
        }

        $this->showProductModal = true;
    }

    public function saveProduct()
    {
        $this->ensureAdminAccess();
        $this->validate([
            'product_code' => 'required|string|max:100|unique:product_types,code,' . $this->product_id,
            'product_name' => 'required|string|max:255',
        ]);

        ProductType::updateOrCreate(
            ['id' => $this->product_id],
            [
                'code' => strtoupper($this->product_code),
                'name' => $this->product_name,
            ]
        );

        $this->showProductModal = false;
        $this->resetProductFields();
        session()->flash('message', 'Data Jenis Produk (Product Type) berhasil disimpan.');
    }

    public function deleteProduct(int $id)
    {
        $this->ensureAdminAccess();
        ProductType::findOrFail($id)->delete();
        session()->flash('message', 'Jenis Produk berhasil dihapus.');
    }

    protected function resetProductFields()
    {
        $this->reset(['product_id', 'product_code', 'product_name']);
    }

    // --- ORIGIN ACTIONS ---
    public function openOriginModal(?int $id = null)
    {
        $this->ensureAdminAccess();
        $this->resetOriginFields();

        if ($id) {
            $orig = Origin::findOrFail($id);
            $this->origin_id = $orig->id;
            $this->region_name = $orig->region_name;
        }

        $this->showOriginModal = true;
    }

    public function saveOrigin()
    {
        $this->ensureAdminAccess();
        $this->validate([
            'region_name' => 'required|string|max:255|unique:origins,region_name,' . $this->origin_id,
        ]);

        Origin::updateOrCreate(
            ['id' => $this->origin_id],
            ['region_name' => strtoupper($this->region_name)]
        );

        $this->showOriginModal = false;
        $this->resetOriginFields();
        session()->flash('message', 'Data Asal Tembakau (Primary Origin) berhasil disimpan.');
    }

    public function deleteOrigin(int $id)
    {
        $this->ensureAdminAccess();
        Origin::findOrFail($id)->delete();
        session()->flash('message', 'Asal Tembakau berhasil dihapus.');
    }

    protected function resetOriginFields()
    {
        $this->reset(['origin_id', 'region_name']);
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        $origins = Origin::orderBy('region_name')->get();

        return view('livewire.admin.master-data-management', compact('customers', 'productTypes', 'origins'));
    }
}
