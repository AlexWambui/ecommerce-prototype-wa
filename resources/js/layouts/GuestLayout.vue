<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import Navbar from '@/components/custom/Navbar/Index.vue';
import Footer from '@/components/custom/Footer.vue';
</script>

<template>
    <Toaster />
    
    <Navbar />
    
    <main class="GuestLayout">
        <slot />
    </main>

    <Footer />
</template>
