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
import type { User } from '@/types/auth';
import userRoutes from '@/routes/users';

interface Props {
    users: {
        data: User[];
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

const debouncedSearch = useDebounceFn(() => {
    router.get(userRoutes.index().url, {
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
    const { current_page, per_page, total } = props.users.meta;
    const start = (current_page - 1) * per_page + 1;
    const end = Math.min(current_page * per_page, total);
    return { start, end, total };
});

const hasActiveFilters = computed(() => !!search.value);
</script>

<template>
    <div class="app-header">
        <div class="info">
            <h1 class="title">Users</h1>
        </div>

        <div class="search">
            <Input
                v-model="search"
                type="text"
                placeholder="Search by name or slug..."
            />
        </div>

        <div class="action">
            <!-- <Link :href="userRoutes.create().url">
                <Button>New Product</Button>
            </Link> -->
        </div>
    </div>

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Image</TableHead>
                    <TableHead>User</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Phone</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(user, index) in users.data" :key="user.id">
                    <TableCell class="id">{{ (users.meta.current_page - 1) * users.meta.per_page + index + 1 }}</TableCell>
                    <TableCell class="w-20"><img :src="user.image_url" :alt="user.name"></TableCell>
                    <TableCell class="max-w-30 overflow-hidden text-ellipsis cursor-help" :title="user.name">{{ user.name }}</TableCell>
                    <TableCell>{{ user.email }}</TableCell>
                    <TableCell>{{ user.phone ?? '-' }}</TableCell>
                    <TableCell class="actions w-20">
                        <div class="actions-wrapper">
                            <Link :href="userRoutes.edit(user.id).url" class="action edit">
                                <Pencil />
                            </Link>
                            <span class="divider">|</span>
                            <DeleteConfirmationDialog 
                                :url="userRoutes.destroy(user.id).url" 
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

                <TableRow v-if="users.data.length === 0">
                    <TableCell colspan="9" class="blank-table-row">
                        No products found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <Pagination :meta="users.meta" />

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
