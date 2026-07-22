<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Link, router } from '@inertiajs/vue3';
import Input from '@/components/ui/input/Input.vue';
import ProductCard from '../components/ProductCardSingleImage.vue';
import Pagination from '@/components/custom/Pagination.vue';
import type { Product } from '@/types/product';
import shopPageRoutes from '@/routes/shop-page';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    products: {
        data: Product[];
        links: any[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            links: any[];
        };
    };
    product_categories: Category[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');

const debouncedSearch = useDebounceFn(() => {
    router.get(shopPageRoutes.index().url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(search, () => {
    debouncedSearch();
});

const getDisplayRange = computed(() => {
    const { current_page, per_page, total } = props.products.meta;
    const start = (current_page - 1) * per_page + 1;
    const end = Math.min(current_page * per_page, total);
    return { start, end, total };
});
</script>

<template>
    <div class="search lg:w-[30dvw] lg:mx-auto py-4">
        <Input
            v-model="search"
            type="text"
            placeholder="Search products by name..."
            class="rounded-full px-6 py-6"
        />
    </div>

    <div class="categories">
        <div class="container-fluid flex flex-wrap gap-4">
            <Link 
                href="/shop" 
                class="category-link"
                :class="{ 
                    'active text-blue-800 font-semibold' : $page.url === '/shop' 
                }"
            >
                All
            </Link>
            <Link 
                v-for="category in product_categories" 
                :key="category.id"
                :href="`/shop/${category.slug}`"
                class="category-link"
                :class="{ 
                    'active text-blue-800 font-semibold' : $page.url === `/shop/${category.slug}` 
                }"
            >
                {{ category.name }}
            </Link>
        </div>
    </div>

    <div class="products-wrapper">
        <div class="container-fluid">
            <!-- Results count -->
            <div v-if="products.data.length > 0" class="text-sm text-gray-500 mb-4">
                Showing {{ getDisplayRange.start }} to {{ getDisplayRange.end }} 
                of {{ getDisplayRange.total }} products
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                <ProductCard 
                    v-for="product in products.data" 
                    :key="product.id" 
                    :product="product"
                />
            </div>

            <!-- Empty State -->
            <div v-if="products.data.length === 0" class="text-center py-16">
                <div class="text-gray-400 text-6xl mb-4">📦</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No products found</h3>
                <p class="text-gray-400">Try adjusting your search or filters</p>
            </div>

            <!-- Pagination -->
            <Pagination :meta="products.meta" />
        </div>
    </div>
</template>
