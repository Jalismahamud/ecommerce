<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { computed } from 'vue';
import ProductCard from '../../Components/Storefront/ProductCard.vue';
import StorefrontLayout from '../../Layouts/StorefrontLayout.vue';

const props = defineProps({
    shops: { type: Array, default: () => [] },
    shop: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    offers: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const fallbackCategories = [
    { id: 'dairy-bread-eggs', name: 'Dairy, Bread & Eggs', slug: 'dairy-bread-eggs', image: 'category-dairy-bread-eggs.jpg' },
    { id: 'snack-munchies', name: 'Snack & Munchies', slug: 'snack-munchies', image: 'category-snack-munchies.jpg' },
    { id: 'bakery-biscuits', name: 'Bakery & Biscuits', slug: 'bakery-biscuits', image: 'category-bakery-biscuits.jpg' },
    { id: 'fruits-vegetables', name: 'Fruits & Vegetables', slug: 'fruits-vegetables', image: 'category-fruits-vegetables.jpg' },
    { id: 'cold-drinks-juices', name: 'Cold Drinks & Juices', slug: 'cold-drinks-juices', image: 'category-cold-drinks-juices.jpg' },
    { id: 'atta-rice-dal', name: 'Atta, Rice & Dal', slug: 'atta-rice-dal', image: 'category-atta-rice-dal.jpg' },
    { id: 'baby-care', name: 'Baby Care', slug: 'baby-care', image: 'category-baby-care.jpg' },
    { id: 'chicken-meat-fish', name: 'Chicken, Meat & Fish', slug: 'chicken-meat-fish', image: 'category-chicken-meat-fish.jpg' },
    { id: 'cleaning-essentials', name: 'Cleaning Essentials', slug: 'cleaning-essentials', image: 'category-cleaning-essentials.jpg' },
    { id: 'instant-food', name: 'Instant Food', slug: 'instant-food', image: 'category-instant-food.jpg' },
    { id: 'pet-care', name: 'Pet Care', slug: 'pet-care', image: 'category-pet-care.jpg' },
    { id: 'tea-coffee-drinks', name: 'Tea, Coffee & Drinks', slug: 'tea-coffee-drinks', image: 'category-tea-coffee-drinks.jpg' },
];
const displayCategories = computed(() => props.categories.length
    ? props.categories
    : fallbackCategories.map((category) => ({
        ...category,
        image: `/frontend/fresh-food-theme/src/assets/images/category/${category.image}`,
    })));

const sliderModules = [Autoplay, Navigation, Pagination];
const sliderOptions = {
    modules: sliderModules,
    loop: true,
    autoplay: { delay: 5000, disableOnInteraction: false },
    pagination: { clickable: true },
    navigation: true,
};

</script>

<template>
    <Head title="Fresh groceries" />
    <StorefrontLayout :shops="shops" :shop="shop">
        <section class="mt-8">
            <div class="container">
                <Swiper v-bind="sliderOptions" class="freshfood-hero rounded-lg">
                    <SwiperSlide
                        v-for="slide in [
                            { image: 'slide-1.jpg', badge: 'Opening Sale Discount 50%', title: 'SuperMarket For Fresh Grocery', text: 'Introduced a new model for online grocery shopping and convenient home delivery.' },
                            { image: 'slider-2.jpg', badge: 'Free Shipping - orders over $100', title: 'Free Shipping on', highlight: '$100', suffix: 'orders over', text: 'Free Shipping to First-Time Customers Only, after promotions and discounts are applied.' },
                        ]"
                        :key="slide.image"
                        :style="{ backgroundImage: `url('/frontend/fresh-food-theme/src/assets/images/slider/${slide.image}')` }"
                        class="flex min-h-[350px] items-center bg-cover bg-center md:min-h-[440px]"
                    >
                        <div class="max-w-xl px-8 py-14 md:px-12">
                            <span class="inline-block rounded-lg bg-yellow-400 p-2 text-sm font-semibold text-gray-900">{{ slide.badge }}</span>
                            <div class="my-7 flex flex-col gap-3">
                                <h1 class="text-3xl font-bold leading-tight text-gray-900 md:text-5xl">
                                    {{ slide.title }}
                                    <template v-if="slide.highlight">
                                        <br>{{ slide.suffix }} <span class="text-green-600">{{ slide.highlight }}</span>
                                    </template>
                                </h1>
                                <p class="max-w-md text-base font-light text-gray-700">{{ slide.text }}</p>
                            </div>
                            <a href="#popular-products" class="btn inline-flex items-center gap-x-2 border-gray-800 bg-gray-800 text-white hover:border-gray-900 hover:bg-gray-900">
                                Shop Now <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    </SwiperSlide>
                </Swiper>
            </div>
        </section>

        <section id="featured-categories" class="mt-10">
            <div class="container">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Featured Categories</h2>
                    <div class="hidden gap-2 sm:flex">
                        <button class="category-prev rounded-full border border-gray-300 p-2 hover:border-green-600 hover:text-green-600" aria-label="Previous categories"><i class="ti ti-chevron-left"></i></button>
                        <button class="category-next rounded-full border border-gray-300 p-2 hover:border-green-600 hover:text-green-600" aria-label="Next categories"><i class="ti ti-chevron-right"></i></button>
                    </div>
                </div>
                <Swiper
                    :modules="[Navigation]"
                    :navigation="{ prevEl: '.category-prev', nextEl: '.category-next' }"
                    :space-between="20"
                    :breakpoints="{ 480: { slidesPerView: 2 }, 768: { slidesPerView: 4 }, 1024: { slidesPerView: 6 } }"
                >
                    <SwiperSlide v-for="category in displayCategories" :key="category.id">
                        <Link :href="shop ? `/?shop=${shop.slug}&category=${category.slug}` : '#popular-products'" class="block rounded-lg border border-gray-300 bg-white transition hover:border-green-600 hover:shadow-md">
                            <div class="py-8 text-center">
                                <img :src="category.image" :alt="category.name" class="mb-3 mx-auto h-20 w-20 object-contain">
                                <div class="px-2 text-base text-gray-800">{{ category.name }}</div>
                            </div>
                        </Link>
                    </SwiperSlide>
                </Swiper>
            </div>
        </section>

        <section class="mt-10">
            <div class="container">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <div class="rounded-lg px-8 py-10" style="background: url('/frontend/fresh-food-theme/src/assets/images/banner/grocery-banner.png') no-repeat center; background-size: cover">
                            <div class="flex flex-col gap-5">
                                <div class="flex flex-col gap-1">
                                    <h2 class="text-xl font-bold">Fruits & Vegetables</h2>
                                    <p>Get Upto <span class="font-bold text-gray-800">30%</span> Off</p>
                                </div>
                                <a href="#popular-products" class="btn inline-flex items-center gap-x-2 border-gray-800 bg-gray-800 text-white hover:border-gray-900 hover:bg-gray-900">Shop Now</a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="rounded-lg px-8 py-10" style="background: url('/frontend/fresh-food-theme/src/assets/images/banner/grocery-banner-2.jpg') no-repeat center; background-size: cover">
                            <div class="flex flex-col gap-5">
                                <div class="flex flex-col gap-1">
                                    <h2 class="text-xl font-bold">Freshly Baked Buns</h2>
                                    <p>Get Upto <span class="font-bold text-gray-800">25%</span> Off</p>
                                </div>
                                <a href="#popular-products" class="btn inline-flex items-center gap-x-2 border-gray-800 bg-gray-800 text-white hover:border-gray-900 hover:bg-gray-900">Shop Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="popular-products" class="my-8 lg:my-14">
            <div class="container">
                <div class="mb-6 flex flex-wrap">
                    <div class="mb-3 w-full">
                        <h2 class="text-lg">Popular Products</h2>
                        <p v-if="shop" class="mt-1 text-sm text-gray-500">Available at {{ shop.name }}</p>
                    </div>
                </div>
                <Link v-if="filters?.category || filters?.search" :href="`/?shop=${shop?.slug ?? ''}`" class="text-sm text-green-700 hover:underline">Clear filters</Link>

                <div v-if="offers.data?.length" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:gap-4 xl:grid-cols-5">
                    <ProductCard v-for="offer in offers.data" :key="offer.id" :offer="offer" :shop="shop" />
                </div>
                <div v-else class="rounded-lg border border-dashed border-gray-300 px-6 py-14 text-center text-gray-600">
                    <p v-if="!shop">No active shops are available yet.</p>
                    <p v-else>No products are currently available{{ filters?.search || filters?.category ? ' for these filters' : '' }}.</p>
                </div>
                <div v-if="offers.last_page > 1" class="mt-8 flex items-center justify-center gap-4">
                    <Link v-if="offers.prev_page_url" :href="offers.prev_page_url" class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-green-600 hover:text-green-700">Previous</Link>
                    <span class="text-sm text-gray-500">Page {{ offers.current_page }} of {{ offers.last_page }}</span>
                    <Link v-if="offers.next_page_url" :href="offers.next_page_url" class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-green-600 hover:text-green-700">Next</Link>
                </div>
            </div>
        </section>

        <section>
            <div class="container">
                <div class="mb-6 flex flex-wrap">
                    <div class="mb-3 w-full"><h2 class="text-lg">Daily Best Sells</h2></div>
                </div>
                <div class="block w-full overflow-x-auto scrolling-touch pb-6">
                    <div class="flex flex-nowrap gap-4 md:grid md:grid-cols-2 xl:grid-cols-4">
                        <div class="block w-full flex-0 md:w-auto">
                            <div class="rounded-lg px-6 pt-8" style="background: url('/frontend/fresh-food-theme/src/assets/images/banner/banner-deal.jpg') no-repeat; background-size: cover; height: 470px">
                                <div class="flex flex-col gap-5">
                                    <div class="flex flex-col gap-2">
                                        <h3 class="text-lg text-white">100% Organic Coffee Beans.</h3>
                                        <p class="text-base text-white">Fresh picks and daily essentials{{ shop ? ` from ${shop.name}` : ' from your local shop' }}.</p>
                                    </div>
                                    <a href="#popular-products" class="btn inline-flex items-center gap-x-2 border-green-600 bg-green-600 text-white hover:border-green-700 hover:bg-green-700">
                                        Shop Now <i class="ti ti-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <ProductCard
                            v-for="offer in offers.data.slice(0, 3)"
                            :key="`daily-${offer.id}`"
                            :offer="offer"
                            :shop="shop"
                        />
                        <div v-if="!offers.data?.length" class="flex min-h-[470px] items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-gray-600 md:col-span-1 xl:col-span-3">
                            <p>{{ shop ? 'Daily best sellers will appear here when products are added to this shop.' : 'Create and activate a shop to start showing products here.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container grid gap-5 pb-5 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="feature in [
                { icon: 'ti ti-clock', title: '10 minute grocery now', text: 'Get your order delivered quickly.' },
                { icon: 'ti ti-tag', title: 'Best Prices & Offers', text: 'Great everyday value on essentials.' },
                { icon: 'ti ti-basket', title: 'Wide Assortment', text: 'Explore a variety of fresh products.' },
                { icon: 'ti ti-refresh', title: 'Easy Returns', text: 'Shop with confidence and care.' },
            ]" :key="feature.title" class="py-4">
                <i :class="`${feature.icon} mb-3 text-3xl text-green-600`"></i>
                <h3 class="mb-1 font-semibold text-gray-900">{{ feature.title }}</h3>
                <p class="text-sm text-gray-500">{{ feature.text }}</p>
            </div>
        </section>
    </StorefrontLayout>
</template>
