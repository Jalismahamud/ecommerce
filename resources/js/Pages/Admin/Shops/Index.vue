<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';

const props = defineProps({
    shops: { type: Object, required: true },
    owners: { type: Array, default: () => [] },
});

const dialog = ref(false);
const editing = ref(false);
const form = useForm({
    id: null,
    name: '',
    slug: '',
    description: '',
    contact_email: '',
    phone: '',
    address: '',
    is_active: true,
    owner_ids: [],
});

function openCreate() {
    editing.value = false;
    form.reset();
    form.is_active = true;
    form.owner_ids = [];
    dialog.value = true;
}

function openEdit(shop) {
    editing.value = true;
    form.id = shop.id;
    form.name = shop.name;
    form.slug = shop.slug;
    form.description = shop.description ?? '';
    form.contact_email = shop.contact_email ?? '';
    form.phone = shop.phone ?? '';
    form.address = shop.address ?? '';
    form.is_active = shop.is_active;
    form.owner_ids = shop.owners.map((owner) => owner.id);
    dialog.value = true;
}

function save() {
    const options = { preserveScroll: true, onSuccess: () => { dialog.value = false; form.reset(); } };
    if (editing.value) {
        form.put(`/admin/shops/${form.id}`, options);
    } else {
        form.post('/admin/shops', options);
    }
}

function remove(shop) {
    if (window.confirm(`Delete "${shop.name}"? Its shop-specific offers will also be removed.`)) {
        router.delete(`/admin/shops/${shop.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Shops" />
    <DashboardLayout title="Shops">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
            <div>
                <h1 class="text-h5 font-weight-bold">Shops</h1>
                <p class="text-body-2 text-medium-emphasis mt-1">Create shops and assign shop owners.</p>
            </div>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">Add shop</v-btn>
        </div>

        <v-card rounded="lg" elevation="1">
            <v-table>
                <thead><tr><th>Shop</th><th>Owners</th><th>Products</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    <tr v-for="shop in shops.data" :key="shop.id">
                        <td><div class="font-weight-medium">{{ shop.name }}</div><div class="text-caption text-medium-emphasis">{{ shop.slug }}</div></td>
                        <td><div v-for="owner in shop.owners" :key="owner.id">{{ owner.name }}</div><span v-if="!shop.owners.length" class="text-medium-emphasis">Unassigned</span></td>
                        <td>{{ shop.offers_count }}</td>
                        <td><v-chip :color="shop.is_active ? 'success' : 'default'" size="small">{{ shop.is_active ? 'Active' : 'Inactive' }}</v-chip></td>
                        <td class="text-right">
                            <v-btn icon="mdi-pencil-outline" variant="text" size="small" aria-label="Edit shop" @click="openEdit(shop)" />
                            <v-btn icon="mdi-delete-outline" color="error" variant="text" size="small" aria-label="Delete shop" @click="remove(shop)" />
                        </td>
                    </tr>
                    <tr v-if="!shops.data.length"><td colspan="5" class="py-8 text-center text-medium-emphasis">No shops have been created.</td></tr>
                </tbody>
            </v-table>
            <div v-if="shops.last_page > 1" class="d-flex justify-end pa-3">
                <v-pagination :model-value="shops.current_page" :length="shops.last_page" density="comfortable" @update:model-value="(page) => router.get(`/admin/shops?page=${page}`)" />
            </div>
        </v-card>

        <v-dialog v-model="dialog" max-width="720">
            <v-card rounded="lg">
                <v-card-title class="pa-5">{{ editing ? 'Edit shop' : 'Create shop' }}</v-card-title>
                <v-divider />
                <v-card-text class="pa-5">
                    <v-form @submit.prevent="save">
                        <v-row>
                            <v-col cols="12" sm="6"><v-text-field v-model="form.name" label="Shop name" variant="outlined" :error-messages="form.errors.name" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="form.slug" label="Slug (optional)" variant="outlined" :error-messages="form.errors.slug" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="form.contact_email" label="Contact email" variant="outlined" :error-messages="form.errors.contact_email" /></v-col>
                            <v-col cols="12" sm="6"><v-text-field v-model="form.phone" label="Phone" variant="outlined" :error-messages="form.errors.phone" /></v-col>
                            <v-col cols="12"><v-textarea v-model="form.address" label="Address" rows="2" variant="outlined" :error-messages="form.errors.address" /></v-col>
                            <v-col cols="12"><v-textarea v-model="form.description" label="Description" rows="2" variant="outlined" :error-messages="form.errors.description" /></v-col>
                            <v-col cols="12">
                                <v-select v-model="form.owner_ids" :items="owners" item-title="name" item-value="id" label="Shop owners" multiple chips variant="outlined" :error-messages="form.errors.owner_ids" />
                            </v-col>
                            <v-col cols="12"><v-switch v-model="form.is_active" label="Active shop" color="success" hide-details /></v-col>
                        </v-row>
                        <div class="d-flex justify-end ga-2 mt-4">
                            <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
                            <v-btn type="submit" color="primary" :loading="form.processing">{{ editing ? 'Save changes' : 'Create shop' }}</v-btn>
                        </div>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </DashboardLayout>
</template>
