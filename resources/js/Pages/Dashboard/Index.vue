<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardLayout from '../../Layouts/DashboardLayout.vue';

defineProps({
    stats: { type: Object, required: true },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.roles?.includes('admin'));
const cards = computed(() => [
    { title: isAdmin.value ? 'Shops' : 'Assigned shops', value: page.props.stats?.shops ?? 0, icon: 'mdi-store-outline', color: 'primary' },
    { title: 'Catalog products', value: page.props.stats?.products ?? 0, icon: 'mdi-package-variant-closed', color: 'success' },
    { title: isAdmin.value ? 'Shop offers' : 'Low stock offers', value: page.props.stats?.offers ?? 0, icon: 'mdi-chart-box-outline', color: 'warning' },
]);
</script>

<template>
    <Head title="Dashboard" />
    <DashboardLayout title="Dashboard overview">
        <div class="mb-6">
            <h1 class="text-h5 font-weight-bold">Welcome, {{ page.props.auth?.user?.name }}</h1>
            <p class="text-body-2 text-medium-emphasis mt-1">{{ isAdmin ? 'Manage shops and the shared product catalog.' : 'Manage products and offers for your assigned shops.' }}</p>
        </div>
        <v-row>
            <v-col v-for="card in cards" :key="card.title" cols="12" sm="6" lg="4">
                <v-card rounded="lg" elevation="1">
                    <v-card-text class="d-flex align-center justify-space-between pa-6">
                        <div>
                            <div class="text-body-2 text-medium-emphasis">{{ card.title }}</div>
                            <div class="mt-2 text-h4 font-weight-bold">{{ card.value }}</div>
                        </div>
                        <v-avatar :color="card.color" variant="tonal" size="56">
                            <v-icon :icon="card.icon" size="28" />
                        </v-avatar>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
        <v-card class="mt-6" rounded="lg" elevation="1">
            <v-card-title class="pa-5">Quick actions</v-card-title>
            <v-card-text class="d-flex flex-wrap ga-3 px-5 pb-5">
                <v-btn v-if="isAdmin" color="primary" prepend-icon="mdi-store-plus" @click="router.visit('/admin/shops')">Manage shops</v-btn>
                <v-btn v-if="isAdmin" variant="outlined" prepend-icon="mdi-package-variant" @click="router.visit('/admin/products')">Manage catalog</v-btn>
                <v-btn v-if="isAdmin" variant="outlined" prepend-icon="mdi-account-plus" @click="router.visit('/admin/shop-owners')">Create shop owner</v-btn>
                <v-btn v-else color="primary" prepend-icon="mdi-package-variant" @click="router.visit('/shop/products')">Manage shop products</v-btn>
            </v-card-text>
        </v-card>
    </DashboardLayout>
</template>
