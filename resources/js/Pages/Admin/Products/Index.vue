<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';

const props = defineProps({
    products: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
});

const dialog = ref(false);
const editing = ref(false);
const form = useForm({
    id: null,
    category_id: null,
    name: '',
    slug: '',
    sku: '',
    brand: '',
    short_description: '',
    description: '',
    is_active: true,
    image: null,
});

function openCreate() {
    editing.value = false;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    dialog.value = true;
}

function openEdit(product) {
    editing.value = true;
    form.clearErrors();
    Object.assign(form, {
        id: product.id,
        category_id: product.category_id,
        name: product.name,
        slug: product.slug,
        sku: product.sku ?? '',
        brand: product.brand ?? '',
        short_description: product.short_description ?? '',
        description: product.description ?? '',
        is_active: product.is_active,
        image: null,
    });
    dialog.value = true;
}

function save() {
    const url = editing.value ? `/admin/products/${form.id}` : '/admin/products';
    form.transform((data) => editing.value ? { ...data, _method: 'put' } : data)
        .post(url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => { dialog.value = false; form.reset(); },
        });
}

function remove(product) {
    if (window.confirm(`Delete "${product.name}" from the shared catalog and all shops?`)) {
        router.delete(`/admin/products/${product.id}`, { preserveScroll: true });
    }
}

function productImage(product) {
    return product.image || `/frontend/fresh-food-theme/src/assets/images/products/product-img-${(product.id % 19) + 1}.jpg`;
}
</script>

<template>
    <Head title="Catalog products" />
    <DashboardLayout title="Shared catalog">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
            <div><h1 class="text-h5 font-weight-bold">Shared catalog</h1><p class="text-body-2 text-medium-emphasis mt-1">Manage products available to shop owners.</p></div>
            <div class="d-flex flex-wrap ga-2">
                <v-btn variant="outlined" prepend-icon="mdi-shape-outline" @click="router.visit('/admin/categories')">Categories</v-btn>
                <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">Add product</v-btn>
            </div>
        </div>

        <v-card rounded="lg" elevation="1">
            <v-table>
                <thead><tr><th>Product</th><th>Category</th><th>SKU</th><th>Shop offers</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    <tr v-for="product in products.data" :key="product.id">
                        <td>
                            <div class="d-flex align-center ga-3">
                                <v-avatar rounded="lg" size="46" color="grey-lighten-3"><img :src="productImage(product)" :alt="product.name" class="h-100 w-100" style="object-fit: cover"></v-avatar>
                                <div><div class="font-weight-medium">{{ product.name }}</div><div class="text-caption text-medium-emphasis">{{ product.slug }}</div></div>
                            </div>
                        </td>
                        <td>{{ product.category || 'Uncategorized' }}</td>
                        <td>{{ product.sku || '—' }}</td>
                        <td>{{ product.offers_count }}</td>
                        <td><v-chip :color="product.is_active ? 'success' : 'default'" size="small">{{ product.is_active ? 'Active' : 'Inactive' }}</v-chip></td>
                        <td class="text-right">
                            <v-btn icon="mdi-pencil-outline" variant="text" size="small" aria-label="Edit product" @click="openEdit(product)" />
                            <v-btn icon="mdi-delete-outline" color="error" variant="text" size="small" aria-label="Delete product" @click="remove(product)" />
                        </td>
                    </tr>
                    <tr v-if="!products.data.length"><td colspan="6" class="py-8 text-center text-medium-emphasis">No catalog products have been created.</td></tr>
                </tbody>
            </v-table>
            <div v-if="products.last_page > 1" class="d-flex justify-end pa-3">
                <v-pagination :model-value="products.current_page" :length="products.last_page" @update:model-value="(page) => router.get(`/admin/products?page=${page}`)" />
            </div>
        </v-card>

        <v-dialog v-model="dialog" max-width="760">
            <v-card rounded="lg">
                <v-card-title class="pa-5">{{ editing ? 'Edit catalog product' : 'Create catalog product' }}</v-card-title>
                <v-divider />
                <v-card-text class="pa-5">
                    <v-form @submit.prevent="save">
                        <v-row>
                            <v-col cols="12" sm="8"><v-text-field v-model="form.name" label="Product name" variant="outlined" :error-messages="form.errors.name" /></v-col>
                            <v-col cols="12" sm="4"><v-text-field v-model="form.sku" label="SKU" variant="outlined" :error-messages="form.errors.sku" /></v-col>
                            <v-col cols="12" sm="6"><v-select v-model="form.category_id" :items="categories" item-title="name" item-value="id" label="Category" clearable variant="outlined" :error-messages="form.errors.category_id" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="form.brand" label="Brand" variant="outlined" :error-messages="form.errors.brand" /></v-col>
                            <v-col cols="12"><v-text-field v-model="form.short_description" label="Short description" variant="outlined" :error-messages="form.errors.short_description" /></v-col>
                            <v-col cols="12"><v-textarea v-model="form.description" label="Catalog description" rows="3" variant="outlined" :error-messages="form.errors.description" /></v-col>
                            <v-col cols="12" sm="8"><v-file-input v-model="form.image" label="Catalog image" accept="image/*" prepend-icon="mdi-image-outline" variant="outlined" :error-messages="form.errors.image" /></v-col>
                            <v-col cols="12" sm="4"><v-switch v-model="form.is_active" label="Active product" color="success" /></v-col>
                        </v-row>
                        <div class="d-flex justify-end ga-2 mt-3"><v-btn variant="text" @click="dialog = false">Cancel</v-btn><v-btn type="submit" color="primary" :loading="form.processing">{{ editing ? 'Save changes' : 'Create product' }}</v-btn></div>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </DashboardLayout>
</template>
