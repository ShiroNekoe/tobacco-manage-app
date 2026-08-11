<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\MaterialType;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MasterDataManagement extends Component
{
    public string $activeTab = 'customers'; // customers, products, origins, materials

    protected $queryString = [
        'activeTab' => ['except' => 'customers'],
    ];

    public string $search = '';

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
    public string $origin_code_abbr = '';

    // Material Type Form Fields (Jenis Muatan DN Shipment)
    public bool $showMaterialModal = false;
    public ?int $material_id = null;
    public string $material_code = '';
    public string $material_name = '';
    public string $material_description = '';
    public float $default_sack_weight = 50.00;
    public float $default_tare_weight = 0.70;
    public bool $is_active = true;

    public function mount()
    {
        $this->ensureAdminAccess();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->search = '';
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
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
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
            $this->origin_code_abbr = $orig->code ?? '';
        }

        $this->showOriginModal = true;
    }

    public function saveOrigin()
    {
        $this->ensureAdminAccess();
        $this->validate([
            'region_name' => 'required|string|max:255|unique:origins,region_name,' . $this->origin_id,
            'origin_code_abbr' => 'nullable|string|max:50',
        ]);

        Origin::updateOrCreate(
            ['id' => $this->origin_id],
            [
                'region_name' => strtoupper($this->region_name),
                'code' => $this->origin_code_abbr ? strtoupper($this->origin_code_abbr) : null,
            ]
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
        $this->reset(['origin_id', 'region_name', 'origin_code_abbr']);
    }

    // --- MATERIAL TYPE ACTIONS (JENIS MUATAN DN SHIPMENT) ---
    public function openMaterialModal(?int $id = null)
    {
        $this->ensureAdminAccess();
        $this->resetMaterialFields();

        if ($id) {
            $mat = MaterialType::findOrFail($id);
            $this->material_id = $mat->id;
            $this->material_code = $mat->code;
            $this->material_name = $mat->name;
            $this->material_description = $mat->description ?? '';
            $this->default_sack_weight = (float) ($mat->default_sack_weight ?: 50.00);
            $this->default_tare_weight = (float) ($mat->default_tare_weight ?: 0.70);
            $this->is_active = (bool) $mat->is_active;
        }

        $this->showMaterialModal = true;
    }

    public function saveMaterial()
    {
        $this->ensureAdminAccess();
        $this->validate([
            'material_code' => 'required|string|max:50|unique:material_types,code,' . $this->material_id,
            'material_name' => 'required|string|max:100',
            'material_description' => 'nullable|string|max:255',
            'default_sack_weight' => 'required|numeric|min:0.01',
            'default_tare_weight' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        MaterialType::updateOrCreate(
            ['id' => $this->material_id],
            [
                'code' => $this->material_code,
                'name' => $this->material_name,
                'description' => $this->material_description,
                'default_sack_weight' => $this->default_sack_weight,
                'default_tare_weight' => $this->default_tare_weight,
                'is_active' => $this->is_active,
            ]
        );

        $this->showMaterialModal = false;
        $this->resetMaterialFields();
        session()->flash('message', 'Data Jenis Muatan Pengiriman (Material Type) berhasil disimpan.');
    }

    public function deleteMaterial(int $id)
    {
        $this->ensureAdminAccess();
        MaterialType::findOrFail($id)->delete();
        session()->flash('message', 'Jenis Muatan Pengiriman berhasil dihapus.');
    }

    protected function resetMaterialFields()
    {
        $this->reset([
            'material_id',
            'material_code',
            'material_name',
            'material_description',
            'default_sack_weight',
            'default_tare_weight',
            'is_active',
        ]);
        $this->default_sack_weight = 50.00;
        $this->default_tare_weight = 0.70;
        $this->is_active = true;
    }

    public function render()
    {
        $search = '%' . trim($this->search) . '%';

        // Customers
        $customersQuery = Customer::query();
        if ($this->search && $this->activeTab === 'customers') {
            $customersQuery->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('code', 'like', $search)->orWhere('contact_person', 'like', $search));
        }
        $customers = $customersQuery->orderBy('name')->get();
        $totalCustomers = Customer::count();

        // Product Types
        $productsQuery = ProductType::query();
        if ($this->search && $this->activeTab === 'products') {
            $productsQuery->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('code', 'like', $search));
        }
        $productTypes = $productsQuery->orderBy('name')->get();
        $totalProductTypes = ProductType::count();

        // Origins
        $originsQuery = Origin::query();
        if ($this->search && $this->activeTab === 'origins') {
            $originsQuery->where(fn ($q) => $q->where('region_name', 'like', $search)->orWhere('code', 'like', $search));
        }
        $origins = $originsQuery->orderBy('region_name')->get();
        $totalOrigins = Origin::count();

        // Material Types (Jenis Muatan)
        $materialsQuery = MaterialType::query();
        if ($this->search && $this->activeTab === 'materials') {
            $materialsQuery->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('code', 'like', $search)->orWhere('description', 'like', $search));
        }
        $materials = $materialsQuery->orderBy('id')->get();
        $totalMaterials = MaterialType::count();

        return view('livewire.admin.master-data-management', compact(
            'customers',
            'totalCustomers',
            'productTypes',
            'totalProductTypes',
            'origins',
            'totalOrigins',
            'materials',
            'totalMaterials'
        ));
    }
}
