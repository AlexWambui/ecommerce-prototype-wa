<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="Dashboard AdminDashboard">
        <h1>{{ user.email }}</h1>
        <p>{{ user.role_label }}</p>
        <p>{{ user.status_label }}</p>
    </div>
</template>