<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';

defineProps({
    owners: { type: Object, required: true },
    shops: { type: Array, default: () => [] },
});

const dialog = ref(false);
const form = useForm({ name: '', email: '', password: '', password_confirmation: '', shop_ids: [] });

function save() {
    form.post('/admin/shop-owners', {
        preserveScroll: true,
        onSuccess: () => { dialog.value = false; form.reset(); },
    });
}
</script>

<template>
    <Head title="Shop owners" />
    <DashboardLayout title="Shop owners">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
            <div>
                <h1 class="text-h5 font-weight-bold">Shop owners</h1>
                <p class="text-body-2 text-medium-emphasis mt-1">Create owner accounts and assign their shops.</p>
            </div>
            <v-btn color="primary" prepend-icon="mdi-account-plus-outline" @click="dialog = true">Create owner</v-btn>
        </div>

        <v-card rounded="lg" elevation="1">
            <v-table>
                <thead><tr><th>Name</th><th>Email</th><th>Assigned shops</th></tr></thead>
                <tbody>
                    <tr v-for="owner in owners.data" :key="owner.id">
                        <td class="font-weight-medium">{{ owner.name }}</td>
                        <td>{{ owner.email }}</td>
                        <td><v-chip v-for="shop in owner.shops" :key="shop.id" class="mr-1" size="small">{{ shop.name }}</v-chip><span v-if="!owner.shops.length" class="text-medium-emphasis">Unassigned</span></td>
                    </tr>
                    <tr v-if="!owners.data.length"><td colspan="3" class="py-8 text-center text-medium-emphasis">No shop owners have been created.</td></tr>
                </tbody>
            </v-table>
            <div v-if="owners.last_page > 1" class="d-flex justify-end pa-3">
                <v-pagination :model-value="owners.current_page" :length="owners.last_page" @update:model-value="(page) => router.get(`/admin/shop-owners?page=${page}`)" />
            </div>
        </v-card>

        <v-dialog v-model="dialog" max-width="620">
            <v-card rounded="lg">
                <v-card-title class="pa-5">Create shop owner</v-card-title>
                <v-divider />
                <v-card-text class="pa-5">
                    <v-form @submit.prevent="save">
                        <v-text-field v-model="form.name" label="Full name" variant="outlined" :error-messages="form.errors.name" />
                        <v-text-field v-model="form.email" label="Email" type="email" variant="outlined" :error-messages="form.errors.email" />
                        <v-text-field v-model="form.password" label="Password (at least 12 characters)" type="password" variant="outlined" :error-messages="form.errors.password" />
                        <v-text-field v-model="form.password_confirmation" label="Confirm password" type="password" variant="outlined" />
                        <v-select v-model="form.shop_ids" :items="shops" item-title="name" item-value="id" label="Assign shops (optional)" multiple chips variant="outlined" :error-messages="form.errors.shop_ids" />
                        <div class="d-flex justify-end ga-2 mt-4">
                            <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
                            <v-btn type="submit" color="primary" :loading="form.processing">Create account</v-btn>
                        </div>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </DashboardLayout>
</template>
