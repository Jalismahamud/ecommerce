<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    shops: { type: Array, default: () => [] },
    shop: { type: Object, default: null },
});

const page = usePage();
const search = ref(page.props.filters?.search ?? '');
const selectedShop = computed(() => props.shop?.slug ?? '');
const cartCount = computed(() => page.props.cartCount ?? 0);
const validationError = computed(() => {
    const errors = page.props.errors ?? {};
    return Object.values(errors)[0] ?? null;
});

function browse() {
    router.get('/', {
        shop: selectedShop.value || undefined,
        search: search.value || undefined,
    }, { preserveState: true, preserveScroll: true });
}

function changeShop(event) {
    router.get('/', { shop: event.target.value || undefined }, { preserveState: true });
}
</script>

<template>
    <div class="min-h-screen bg-white">
        <header class="border-b border-gray-200">
            <div class="bg-gray-100 py-2 text-sm text-gray-700">
                <div class="container flex flex-wrap items-center justify-between gap-2">
                    <span>Super Value Deals - Save more with coupons</span>
                    <span class="hidden md:inline">Fresh groceries, delivered with care</span>
                </div>
            </div>

            <div class="container py-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <Link href="/" class="w-40 shrink-0" aria-label="Fresh Food home">
                        <img :src="'/images/fresh-food-logo.svg'" alt="Fresh Food" class="h-auto w-full">
                    </Link>

                    <form class="order-3 flex w-full lg:order-2 lg:max-w-xl lg:flex-1" @submit.prevent="browse">
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Search for products"
                            class="min-w-0 flex-1 rounded-l-lg border border-gray-300 px-4 py-3 text-base focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100"
                        >
                        <button class="rounded-r-lg bg-gray-800 px-5 text-white hover:bg-gray-900" type="submit" aria-label="Search">
                            <i class="ti ti-search text-lg"></i>
                        </button>
                    </form>

                    <div class="order-2 flex items-center gap-3 lg:order-3">
                        <select
                            v-if="shops.length"
                            :value="selectedShop"
                            class="max-w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm"
                            aria-label="Select shop"
                            @change="changeShop"
                        >
                            <option v-for="item in shops" :key="item.id" :value="item.slug">{{ item.name }}</option>
                        </select>
                        <Link href="/cart" class="relative inline-flex items-center gap-2 text-gray-700 hover:text-green-600">
                            <i class="ti ti-shopping-cart text-2xl"></i>
                            <span class="hidden sm:inline">Cart</span>
                            <span v-if="cartCount" class="absolute -right-3 -top-2 rounded-full bg-green-600 px-1.5 py-0.5 text-xs text-white">{{ cartCount }}</span>
                        </Link>
                        <Link v-if="!page.props.auth?.user" href="/login" class="hidden text-gray-700 hover:text-green-600 sm:inline">Sign in</Link>
                        <Link v-else href="/dashboard" class="hidden text-gray-700 hover:text-green-600 sm:inline">Dashboard</Link>
                    </div>
                </div>
            </div>

            <nav class="border-t border-gray-100">
                <div class="container flex items-center gap-8 py-3 text-sm font-medium">
                    <Link href="/" class="text-green-700 hover:text-green-800">Home</Link>
                    <a href="#featured-categories" class="text-gray-700 hover:text-green-700">Categories</a>
                    <a href="#popular-products" class="text-gray-700 hover:text-green-700">Products</a>
                    <span v-if="shop" class="ml-auto hidden text-gray-500 md:inline">Shopping at <strong class="text-gray-800">{{ shop.name }}</strong></span>
                </div>
            </nav>
        </header>

        <div v-if="page.props.flash?.success" class="container mt-4">
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800" role="status">{{ page.props.flash.success }}</div>
        </div>
        <div v-if="page.props.flash?.error" class="container mt-4">
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">{{ page.props.flash.error }}</div>
        </div>
        <div v-if="validationError" class="container mt-4">
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">{{ validationError }}</div>
        </div>

        <main>
            <slot />
        </main>

        <footer class="mt-16 border-t border-gray-200 bg-gray-50">
            <div class="container grid gap-8 py-12 md:grid-cols-3">
                <div>
                    <img :src="'/images/fresh-food-logo.svg'" alt="Fresh Food" class="mb-4 w-40">
                    <p class="max-w-sm text-sm text-gray-600">Fresh groceries and everyday essentials from your local shop.</p>
                </div>
                <div>
                    <h2 class="mb-3 font-semibold text-gray-900">Quick links</h2>
                    <div class="flex flex-col gap-2 text-sm text-gray-600">
                        <Link href="/" class="hover:text-green-700">Home</Link>
                        <Link href="/cart" class="hover:text-green-700">Shopping cart</Link>
                    </div>
                </div>
                <div class="flex items-start md:justify-end">
                    <div class="flex items-center gap-3">
                        <img :src="'/frontend/fresh-food-theme/src/assets/images/payment/visa.svg'" alt="Visa" class="h-7">
                        <img :src="'/frontend/fresh-food-theme/src/assets/images/payment/mastercard.svg'" alt="Mastercard" class="h-7">
                        <img :src="'/frontend/fresh-food-theme/src/assets/images/payment/paypal.svg'" alt="PayPal" class="h-7">
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 py-4 text-center text-sm text-gray-500">© {{ new Date().getFullYear() }} Fresh Food</div>
        </footer>
    </div>
</template>
