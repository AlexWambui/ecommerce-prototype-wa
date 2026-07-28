<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import { Button } from '@/components/ui/button';
import FormHeader from '@/components/custom/FormHeader.vue';
import deliveryLocationRoutes from '@/routes/delivery-locations';

const form = useForm({
    name: '',
})

const submitForm = () => {
    form.post(deliveryLocationRoutes.store.url(), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Create Location" />

    <div class="form">
        <FormHeader :backUrl="deliveryLocationRoutes.index().url" title="Create Location" />

        <form @submit.prevent="submitForm">
            <div class="inputs-group-wrapper">
                <div class="inputs-group">
                    <Label for="name" class="required">Name</Label>
                    <Input type="text" id="name" v-model="form.name" autocomplete="name" placeholder="Name" autofocus />
                    <InputError :message="form.errors.name" />
                </div>
            </div>

            <div class="submit-buttons">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Create Location
                </Button>

                <div>
                    <Link :href="deliveryLocationRoutes.index().url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                    </Link>
                </div>
            </div>
        </form>
    </div>
</template>
