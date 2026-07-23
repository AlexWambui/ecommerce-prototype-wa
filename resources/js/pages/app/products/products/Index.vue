<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, Loader2 } from '@lucide/vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import Pagination from '@/components/custom/Pagination.vue';
import ProductsNav from '../components/ProductsNav.vue';
import type { Product } from '@/types/product';
import productRoutes from '@/routes/products';
import { usePriceFormatter } from '@/composables/usePriceFormatter';

const { formatPrice } = usePriceFormatter();

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
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const toggling = ref<{ id: number; attribute: string } | null>(null);

const debouncedSearch = useDebounceFn(() => {
    router.get(productRoutes.index().url, {
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

const hasActiveFilters = computed(() => !!search.value);

const toggleAttribute = async (product: Product, attribute: 'is_featured' | 'is_new' | 'is_active') => {
    const currentValue = product[attribute];
    const newValue = !currentValue;
    
    toggling.value = { id: product.id, attribute };
    
    try {
        const response = await fetch(productRoutes.toggleAttribute(product.uuid).url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                attribute: attribute,
                value: newValue
            })
        });

        if (!response.ok) {
            throw new Error('Failed to update');
        }

        const data = await response.json();
        
        if (data.success) {
            product[attribute] = newValue;
        }
    } catch (error) {
        console.error('Error toggling attribute:', error);
        product[attribute] = currentValue;
    } finally {
        toggling.value = null;
    }
};

const getTagClasses = (type: string, value: boolean) => {
    const baseClasses = 'px-1 py-0.5 text-xs font-medium rounded-sm transition-all duration-200 cursor-pointer select-none';
    
    if (!value) {
        return `${baseClasses} bg-gray-100 text-gray-400 hover:bg-gray-200`;
    }
    
    switch (type) {
        case 'is_featured':
            return `${baseClasses} bg-yellow-400 text-yellow-900 hover:bg-yellow-500`;
        case 'is_new':
            return `${baseClasses} bg-green-500 text-white hover:bg-green-600`;
        case 'is_active':
            return `${baseClasses} bg-blue-500 text-white hover:bg-blue-600`;
        default:
            return baseClasses;
    }
};

const isLoading = (product: Product, attribute: string) => {
    return toggling.value?.id === product.id && toggling.value?.attribute === attribute;
};

const getTagLabel = (type: string, product: Product) => {
    const attribute = type as 'is_featured' | 'is_new' | 'is_active';
    if (isLoading(product, attribute)) {
        return ''; // Return empty string, we'll show the spinner
    }
    
    switch (type) {
        case 'is_featured':
            return 'Featured';
        case 'is_new':
            return 'New';
        case 'is_active':
            return 'Active';
        default:
            return '';
    }
};
</script>

<template>
    <ProductsNav current-page="products" />
    
    <div class="app-header">
        <div class="info">
            <h1 class="title">Products</h1>
        </div>

        <div class="search">
            <Input
                v-model="search"
                type="text"
                placeholder="Search by name or slug..."
            />
        </div>

        <div class="action">
            <Link :href="productRoutes.create().url">
                <Button>New Product</Button>
            </Link>
        </div>
    </div>

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Image</TableHead>
                    <TableHead>Product</TableHead>
                    <TableHead>SKU</TableHead>
                    <TableHead>Price (Ksh)</TableHead>
                    <TableHead>Stock</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead class="tags">Tags</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(product, index) in products.data" :key="product.id">
                    <TableCell class="id">{{ (products.meta.current_page - 1) * products.meta.per_page + index + 1 }}</TableCell>
                    <TableCell class="w-20"><img :src="product.thumbnail_url" :alt="product.slug"></TableCell>
                    <TableCell class="max-w-30 overflow-hidden text-ellipsis cursor-help" :title="product.name">{{ product.name }}</TableCell>
                    <TableCell>{{ product.sku ?? '-' }}</TableCell>
                    <TableCell>{{ formatPrice(product.price) }}</TableCell>
                    <TableCell>{{ product.stock ?? 0 }}</TableCell>
                    <TableCell>{{ product.category_name }}</TableCell>
                    <TableCell class="tags min-w-50 w-45">
                        <div class="tags-wrapper flex flex-wrap gap-4">
                            <!-- Featured -->
                            <button
                                @click="toggleAttribute(product, 'is_featured')"
                                :disabled="isLoading(product, 'is_featured')"
                                :class="getTagClasses('is_featured', product.is_featured)"
                            >
                                <Loader2 v-if="isLoading(product, 'is_featured')" class="w-3 h-3 animate-spin" />
                                <span v-else>Featured</span>
                            </button>

                            <!-- New -->
                            <button
                                @click="toggleAttribute(product, 'is_new')"
                                :disabled="isLoading(product, 'is_new')"
                                :class="getTagClasses('is_new', product.is_new)"
                            >
                                <Loader2 v-if="isLoading(product, 'is_new')" class="w-3 h-3 animate-spin" />
                                <span v-else>New</span>
                            </button>

                            <!-- Active -->
                            <button
                                @click="toggleAttribute(product, 'is_active')"
                                :disabled="isLoading(product, 'is_active')"
                                :class="getTagClasses('is_active', product.is_active)"
                            >
                                <Loader2 v-if="isLoading(product, 'is_active')" class="w-3 h-3 animate-spin" />
                                <span v-else>Active</span>
                            </button>
                        </div>
                    </TableCell>
                    <TableCell class="actions w-20">
                        <div class="actions-wrapper">
                            <Link :href="productRoutes.edit(product.uuid).url" class="action edit">
                                <Pencil />
                            </Link>
                            <span class="divider">|</span>
                            <DeleteConfirmationDialog 
                                :url="productRoutes.destroy(product.uuid).url" 
                                title="Delete Product?" 
                                description="This product will be deleted permanently!" 
                                confirm-text="Delete Product"
                            >
                                <template #trigger>
                                    <button class="action delete">
                                        <Trash2 />
                                    </button>
                                </template>
                            </DeleteConfirmationDialog>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="products.data.length === 0">
                    <TableCell colspan="9" class="blank-table-row">
                        No products found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <Pagination :meta="products.meta" />

    <div class="table-results-summary">
        <p>
            Showing {{ getDisplayRange.start }} to {{ getDisplayRange.end }}
            of {{ getDisplayRange.total }} products
        </p>
        <p v-if="hasActiveFilters" class="filtered-results">
            Filtered results
        </p>
    </div>
</template>
