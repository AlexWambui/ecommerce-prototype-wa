<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch, computed } from 'vue';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import Pagination from '@/components/custom/Pagination.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import UsersNav from '@/pages/app/users/components/UsersNav.vue';
import loyaltyMemberRoutes from '@/routes/loyalty-members';

interface LoyaltyMember {
    id: number;
    uuid: string;
    name: string;
    phone_number: string;
    loyalty_id: number;
    points: number;
}

interface Props {
    loyalty_members: {
        data: LoyaltyMember[];
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
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');

const debouncedSearch = useDebounceFn(() => {
    router.get(loyaltyMemberRoutes.index().url, {
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
    const { current_page, per_page, total } = props.loyalty_members.meta;
    const start = (current_page - 1) * per_page + 1;
    const end = Math.min(current_page * per_page, total);

    return { start, end, total };
});

const hasActiveFilters = computed(() => !!search.value);
</script>

<template>
    <UsersNav current-page="loyalty-members" />

    <div class="app-header">
        <div class="info">
            <h1 class="title">Loyalty Members</h1>
        </div>

        <div class="search">
            <Input
                v-model="search"
                type="text"
                placeholder="Search by name or phone number..."
            />
        </div>

        <div class="action">
            <Link :href="loyaltyMemberRoutes.create().url">
                <Button>New Member</Button>
            </Link>
        </div>
    </div>

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>User</TableHead>
                    <TableHead>Phone</TableHead>
                    <TableHead>Loyalty ID</TableHead>
                    <TableHead>Points</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(member, index) in loyalty_members.data" :key="member.id">
                    <TableCell class="id">{{ (loyalty_members.meta.current_page - 1) * loyalty_members.meta.per_page + index + 1 }}</TableCell>
                    <TableCell>{{ member.name }}</TableCell>
                    <TableCell>{{ member.phone_number }}</TableCell>
                    <TableCell>{{ member.loyalty_id }}</TableCell>
                    <TableCell>{{ member.points }}</TableCell>
                    <TableCell class="actions w-20">
                        <div class="actions-wrapper">
                            <Link :href="loyaltyMemberRoutes.edit(member.uuid).url" class="action edit">
                                <Pencil />
                            </Link>
                            <span class="divider">|</span>
                            <DeleteConfirmationDialog 
                                :url="loyaltyMemberRoutes.destroy(member.uuid).url" 
                                title="Delete Member?" 
                                description="This member will be deleted permanently!" 
                                confirm-text="Delete Member"
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

                <TableRow v-if="loyalty_members.data.length === 0">
                    <TableCell colspan="9" class="blank-table-row">
                        No products found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <Pagination :meta="loyalty_members.meta" />

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
