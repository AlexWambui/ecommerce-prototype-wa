<script setup lang="ts">
import Navbar from '@/components/custom/Navbar/Index.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <Toaster />
    
    <Navbar />
    
    <main class="GuestLayout">
        <slot />
    </main>
</template>
