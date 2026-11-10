<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';

const props = defineProps({
    shops: { type: Array, default: () => [] },
    shop: { type: Object, default: null },
    offers: { type: Object, required: true },
    catalog: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const activeTab = ref('shop-products');
const catalogSearch = ref(props.filters.search ?? '');
const createDialog = ref(false);
const attachDialog = ref(false);
const editDialog = ref(false);
const selectedProduct = ref(null);

const attachForm = useForm({
    shop_id: props.shop?.id ?? null,
    product_id: null,
    price: '',
    compare_at_price: '',
    stock: 0,
    description: '',
    is_active: true,
    is_featured: false,
    image: null,
});
const createForm = useForm({
    shop_id: props.shop?.id ?? null,
    category_id: null,
    name: '',
    sku: '',
    brand: '',
    short_description: '',
    product_description: '',
    price: '',
    compare_at_price: '',
    stock: 0,
    description: '',
    is_active: true,
    is_featured: false,
    image: null,
});
const editForm = useForm({
    shop_id: props.shop?.id ?? null,
    price: '',
    compare_at_price: '',
    stock: 0,
    description: '',
    is_active: true,
    is_featured: false,
    image: null,
});

const shopOptions = computed(() => props.shops.map((shop) => ({ title: shop.name, value: shop.id })));

watch(() => props.shop?.id, (id) => {
    attachForm.shop_id = id ?? null;
    createForm.shop_id = id ?? null;
    editForm.shop_id = id ?? null;
});

function selectShop(shopId) {
    router.get('/shop/products', { shop_id: shopId }, { preserveState: true, preserveScroll: true });
}

function searchCatalog() {
    router.get('/shop/products', {
        shop_id: props.shop?.id,
        search: catalogSearch.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

function openAttach(product) {
    selectedProduct.value = product;
    attachForm.reset();
    attachForm.shop_id = props.shop.id;
    attachForm.product_id = product.id;
    attachForm.stock = 0;
    attachForm.is_active = true;
    attachDialog.value = true;
}

function attachProduct() {
    attachForm.post('/shop/products/catalog', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { attachDialog.value = false; attachForm.reset(); },
    });
}

function createProduct() {
    createForm.shop_id = props.shop.id;
    createForm.post('/shop/products', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { createDialog.value = false; createForm.reset(); },
    });
}

function openEdit(offer) {
    selectedProduct.value = offer;
    Object.assign(editForm, {
        shop_id: props.shop.id,
        price: offer.price,
        compare_at_price: offer.compare_at_price ?? '',
        stock: offer.stock,
        description: offer.description ?? '',
        is_active: offer.is_active,
        is_featured: offer.is_featured,
        image: null,
    });
    editDialog.value = true;
}

function saveOffer() {
    editForm.transform((data) => ({ ...data, _method: 'patch' }))
        .post(`/shop/products/${selectedProduct.value.id}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => { editDialog.value = false; editForm.reset(); },
        });
}

function removeOffer(offer) {
    if (window.confirm(`Remove "${offer.name}" from ${props.shop.name}? The shared catalog product will remain.`)) {
        router.delete(`/shop/products/${offer.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="My shop products" />
    <DashboardLayout title="My shop products">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
            <div><h1 class="text-h5 font-weight-bold">Products & offers</h1><p class="text-body-2 text-medium-emphasis mt-1">Set your shop's price, stock, description and thumbnail.</p></div>
            <v-btn v-if="shop" color="primary" prepend-icon="mdi-plus" @click="createDialog = true">Create new product</v-btn>
        </div>

        <v-card v-if="shops.length" class="mb-5" rounded="lg" elevation="1">
            <v-card-text class="d-flex flex-wrap align-center ga-3">
                <span class="text-body-2 font-weight-medium">Manage shop</span>
                <v-select :model-value="shop?.id" :items="shopOptions" density="comfortable" hide-details variant="outlined" style="max-width: 320px" @update:model-value="selectShop" />
            </v-card-text>
        </v-card>

        <v-alert v-if="!shops.length" type="info" variant="tonal">No shops have been assigned to your account yet. Please contact an administrator.</v-alert>
        <template v-else>
            <v-tabs v-model="activeTab" color="primary" class="mb-4">
                <v-tab value="shop-products">My shop products ({{ offers.total }})</v-tab>
                <v-tab value="catalog">Add from shared catalog</v-tab>
            </v-tabs>

            <v-window v-model="activeTab">
                <v-window-item value="shop-products">
                    <v-card rounded="lg" elevation="1">
                        <v-table>
                            <thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                <tr v-for="offer in offers.data" :key="offer.id">
                                    <td>
                                        <div class="d-flex align-center ga-3">
                                            <v-avatar rounded="lg" size="48" color="grey-lighten-3"><v-img :src="offer.image" :alt="offer.name" cover /></v-avatar>
                                            <div><div class="font-weight-medium">{{ offer.name }}</div><div class="text-caption text-medium-emphasis">{{ offer.sku || 'No SKU' }}</div></div>
                                        </div>
                                    </td>
                                    <td>{{ Number(offer.price).toFixed(2) }}</td>
                                    <td><v-chip :color="offer.stock <= 5 ? 'warning' : 'success'" size="small">{{ offer.stock }}</v-chip></td>
                                    <td><v-chip :color="offer.is_active ? 'success' : 'default'" size="small">{{ offer.is_active ? 'Active' : 'Hidden' }}</v-chip></td>
                                    <td class="text-right">
                                        <v-btn icon="mdi-pencil-outline" variant="text" size="small" aria-label="Edit offer" @click="openEdit(offer)" />
                                        <v-btn icon="mdi-delete-outline" color="error" variant="text" size="small" aria-label="Remove offer" @click="removeOffer(offer)" />
                                    </td>
                                </tr>
                                <tr v-if="!offers.data.length"><td colspan="5" class="py-8 text-center text-medium-emphasis">No products assigned to this shop yet.</td></tr>
                            </tbody>
                        </v-table>
                    </v-card>
                </v-window-item>

                <v-window-item value="catalog">
                    <v-card class="mb-4" rounded="lg" elevation="1">
                        <v-card-text>
                            <form class="d-flex ga-2" @submit.prevent="searchCatalog">
                                <v-text-field v-model="catalogSearch" label="Search shared catalog" prepend-inner-icon="mdi-magnify" hide-details variant="outlined" />
                                <v-btn type="submit" color="primary" class="align-self-center">Search</v-btn>
                            </form>
                        </v-card-text>
                    </v-card>
                    <div v-if="catalog.data.length" class="d-grid ga-4" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr))">
                        <v-card v-for="product in catalog.data" :key="product.id" rounded="lg" elevation="1">
                            <v-card-text>
                                <div class="text-subtitle-1 font-weight-semibold">{{ product.name }}</div>
                                <div class="text-caption text-medium-emphasis mt-1">{{ product.category?.name || 'Uncategorized' }} · {{ product.sku || 'No SKU' }}</div>
                                <p class="text-body-2 mt-3">{{ product.short_description }}</p>
                                <v-btn block color="primary" variant="tonal" prepend-icon="mdi-plus" @click="openAttach(product)">Add to {{ shop.name }}</v-btn>
                            </v-card-text>
                        </v-card>
                    </div>
                    <v-alert v-else type="info" variant="tonal">No unassigned products match your search.</v-alert>
                </v-window-item>
            </v-window>
        </template>

        <v-dialog v-model="attachDialog" max-width="650">
            <v-card rounded="lg">
                <v-card-title class="pa-5">Add {{ selectedProduct?.name }} to {{ shop?.name }}</v-card-title>
                <v-divider />
                <v-card-text class="pa-5">
                    <v-form @submit.prevent="attachProduct">
                        <v-row>
                            <v-col cols="12" sm="6"><v-text-field v-model="attachForm.price" label="Shop price" type="number" min="0" step="0.01" variant="outlined" :error-messages="attachForm.errors.price" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="attachForm.compare_at_price" label="Compare-at price (optional)" type="number" min="0" step="0.01" variant="outlined" :error-messages="attachForm.errors.compare_at_price" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="attachForm.stock" label="Stock" type="number" min="0" variant="outlined" :error-messages="attachForm.errors.stock" /></v-col>
                            <v-col cols="12" sm="6"><v-file-input v-model="attachForm.image" label="Shop thumbnail" accept="image/*" variant="outlined" :error-messages="attachForm.errors.image" /></v-col>
                            <v-col cols="12"><v-textarea v-model="attachForm.description" label="Shop-specific description" rows="2" variant="outlined" :error-messages="attachForm.errors.description" /></v-col>
                            <v-col cols="12" sm="6"><v-switch v-model="attachForm.is_active" label="Visible to customers" color="success" /></v-col>
                            <v-col cols="12" sm="6"><v-switch v-model="attachForm.is_featured" label="Featured product" color="success" /></v-col>
                        </v-row>
                        <div class="d-flex justify-end ga-2"><v-btn variant="text" @click="attachDialog = false">Cancel</v-btn><v-btn type="submit" color="primary" :loading="attachForm.processing">Add product</v-btn></div>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-dialog v-model="createDialog" max-width="760">
            <v-card rounded="lg">
                <v-card-title class="pa-5">Create a new catalog product</v-card-title>
                <v-divider />
                <v-card-text class="pa-5">
                    <p class="text-body-2 text-medium-emphasis mb-4">This product will be added to the shared catalog and to {{ shop?.name }}.</p>
                    <v-form @submit.prevent="createProduct">
                        <v-row>
                            <v-col cols="12" sm="8"><v-text-field v-model="createForm.name" label="Product name" variant="outlined" :error-messages="createForm.errors.name" /></v-col>
                            <v-col cols="12" sm="4"><v-text-field v-model="createForm.sku" label="SKU" variant="outlined" :error-messages="createForm.errors.sku" /></v-col>
                            <v-col cols="12" sm="6"><v-select v-model="createForm.category_id" :items="categories" item-title="name" item-value="id" label="Category" clearable variant="outlined" :error-messages="createForm.errors.category_id" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="createForm.brand" label="Brand" variant="outlined" :error-messages="createForm.errors.brand" /></v-col>
                            <v-col cols="12"><v-text-field v-model="createForm.short_description" label="Short catalog description" variant="outlined" :error-messages="createForm.errors.short_description" /></v-col>
                            <v-col cols="12"><v-textarea v-model="createForm.product_description" label="Catalog description" rows="2" variant="outlined" :error-messages="createForm.errors.product_description" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="createForm.price" label="Shop price" type="number" min="0" step="0.01" variant="outlined" :error-messages="createForm.errors.price" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="createForm.compare_at_price" label="Compare-at price" type="number" min="0" step="0.01" variant="outlined" :error-messages="createForm.errors.compare_at_price" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="createForm.stock" label="Stock" type="number" min="0" variant="outlined" :error-messages="createForm.errors.stock" /></v-col>
                            <v-col cols="12" sm="6"><v-file-input v-model="createForm.image" label="Shop thumbnail" accept="image/*" variant="outlined" :error-messages="createForm.errors.image" /></v-col>
                            <v-col cols="12"><v-textarea v-model="createForm.description" label="Shop-specific description" rows="2" variant="outlined" :error-messages="createForm.errors.description" /></v-col>
                            <v-col cols="12" sm="6"><v-switch v-model="createForm.is_active" label="Visible to customers" color="success" /></v-col>
                            <v-col cols="12" sm="6"><v-switch v-model="createForm.is_featured" label="Featured product" color="success" /></v-col>
                        </v-row>
                        <div class="d-flex justify-end ga-2"><v-btn variant="text" @click="createDialog = false">Cancel</v-btn><v-btn type="submit" color="primary" :loading="createForm.processing">Create product</v-btn></div>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-dialog v-model="editDialog" max-width="650">
            <v-card rounded="lg">
                <v-card-title class="pa-5">Edit {{ selectedProduct?.name }}</v-card-title>
                <v-divider />
                <v-card-text class="pa-5">
                    <v-form @submit.prevent="saveOffer">
                        <v-row>
                            <v-col cols="12" sm="6"><v-text-field v-model="editForm.price" label="Shop price" type="number" min="0" step="0.01" variant="outlined" :error-messages="editForm.errors.price" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="editForm.compare_at_price" label="Compare-at price" type="number" min="0" step="0.01" variant="outlined" :error-messages="editForm.errors.compare_at_price" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="editForm.stock" label="Stock" type="number" min="0" variant="outlined" :error-messages="editForm.errors.stock" /></v-col>
                            <v-col cols="12" sm="6"><v-file-input v-model="editForm.image" label="Replace shop thumbnail" accept="image/*" variant="outlined" :error-messages="editForm.errors.image" /></v-col>
                            <v-col cols="12"><v-textarea v-model="editForm.description" label="Shop-specific description" rows="3" variant="outlined" :error-messages="editForm.errors.description" /></v-col>
                            <v-col cols="12" sm="6"><v-switch v-model="editForm.is_active" label="Visible to customers" color="success" /></v-col>
                            <v-col cols="12" sm="6"><v-switch v-model="editForm.is_featured" label="Featured product" color="success" /></v-col>
                        </v-row>
                        <div class="d-flex justify-end ga-2"><v-btn variant="text" @click="editDialog = false">Cancel</v-btn><v-btn type="submit" color="primary" :loading="editForm.processing">Save offer</v-btn></div>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </DashboardLayout>
</template>
