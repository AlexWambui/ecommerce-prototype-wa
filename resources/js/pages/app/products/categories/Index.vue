<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import ProductsNav from '../components/ProductsNav.vue';
import productCategoryRoutes from '@/routes/product-categories';

interface ProductCategory {
    id: number;
    uuid: string;
    name: string;
    slug: string;
    thumbnail_url: string;
    products_count: number;
};

interface Props {
    categories: ProductCategory[];
    search?: string;
};

const props = defineProps<Props>();

const search = ref(props.search || '');

const debouncedSearch = useDebounceFn(() => {
    router.get(productCategoryRoutes.index().url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search], () => {
    debouncedSearch();
});
</script>

<template>
    <Head title="Product Categories" />

    <ProductsNav current-page="product-categories" />

    <div class="app-header">
        <h1>Categories</h1>

        <div class="search">
            <input v-model="search" type="text" placeholder="Search categories by name, slug...">
        </div>

        <div class="action">
            <Link :href="productCategoryRoutes.create().url">
                <Button>New Category</Button>
            </Link>
        </div>
    </div>

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Image</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Products</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(category, index) in props.categories" :key="category.id">
                    <TableCell class="id">{{ index + 1 }}</TableCell>
                    <TableCell class="w-20"><img :src="category.thumbnail_url" :alt="category.slug"></TableCell>
                    <TableCell>{{ category.name }}</TableCell>
                    <TableCell>{{ category.products_count }}</TableCell>
                    <TableCell class="actions">
                        <div class="actions-wrapper">
                            <Link :href="productCategoryRoutes.edit(category.uuid).url" class="action edit">
                                <Pencil />
                            </Link>
                            <span class="divider">|</span>
                            <DeleteConfirmationDialog :url="productCategoryRoutes.destroy(category.uuid).url" title="Delete Category?" description="This category will be deleted permanently!" confirm-text="Delete Category">
                                <template #trigger>
                                    <button class="action delete">
                                        <Trash2 />
                                    </button>
                                </template>
                            </DeleteConfirmationDialog>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="props.categories.length === 0">
                    <TableCell colspan="5" class="blank-table-row">
                        No categories found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>