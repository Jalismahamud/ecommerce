<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';

defineProps({ categories: { type: Object, required: true } });

const dialog = ref(false);
const editing = ref(false);
const form = useForm({ id: null, name: '', slug: '', parent_id: null, is_active: true });

function openCreate() {
    editing.value = false;
    form.reset();
    form.is_active = true;
    dialog.value = true;
}

function openEdit(category) {
    editing.value = true;
    form.id = category.id;
    form.name = category.name;
    form.slug = category.slug;
    form.parent_id = category.parent_id;
    form.is_active = category.is_active;
    dialog.value = true;
}

function save() {
    const options = { preserveScroll: true, onSuccess: () => { dialog.value = false; form.reset(); } };
    if (editing.value) form.put(`/admin/categories/${form.id}`, options);
    else form.post('/admin/categories', options);
}

function remove(category) {
    if (window.confirm(`Delete "${category.name}"? Products will remain but lose this category.`)) {
        router.delete(`/admin/categories/${category.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Categories" />
    <DashboardLayout title="Categories">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
            <div><h1 class="text-h5 font-weight-bold">Categories</h1><p class="text-body-2 text-medium-emphasis mt-1">Organize the shared product catalog.</p></div>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">Add category</v-btn>
        </div>
        <v-card rounded="lg" elevation="1">
            <v-table>
                <thead><tr><th>Name</th><th>Slug</th><th>Products</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    <tr v-for="category in categories.data" :key="category.id">
                        <td class="font-weight-medium">{{ category.name }}</td>
                        <td>{{ category.slug }}</td>
                        <td>{{ category.products_count }}</td>
                        <td><v-chip :color="category.is_active ? 'success' : 'default'" size="small">{{ category.is_active ? 'Active' : 'Inactive' }}</v-chip></td>
                        <td class="text-right">
                            <v-btn icon="mdi-pencil-outline" variant="text" size="small" aria-label="Edit category" @click="openEdit(category)" />
                            <v-btn icon="mdi-delete-outline" color="error" variant="text" size="small" aria-label="Delete category" @click="remove(category)" />
                        </td>
                    </tr>
                    <tr v-if="!categories.data.length"><td colspan="5" class="py-8 text-center text-medium-emphasis">No categories have been created.</td></tr>
                </tbody>
            </v-table>
        </v-card>
        <v-dialog v-model="dialog" max-width="560">
            <v-card rounded="lg">
                <v-card-title class="pa-5">{{ editing ? 'Edit category' : 'Create category' }}</v-card-title>
                <v-divider />
                <v-card-text class="pa-5">
                    <v-form @submit.prevent="save">
                        <v-text-field v-model="form.name" label="Category name" variant="outlined" :error-messages="form.errors.name" />
                        <v-text-field v-model="form.slug" label="Slug (optional)" variant="outlined" :error-messages="form.errors.slug" />
                        <v-switch v-model="form.is_active" label="Active category" color="success" />
                        <div class="d-flex justify-end ga-2"><v-btn variant="text" @click="dialog = false">Cancel</v-btn><v-btn type="submit" color="primary" :loading="form.processing">Save category</v-btn></div>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </DashboardLayout>
</template>
