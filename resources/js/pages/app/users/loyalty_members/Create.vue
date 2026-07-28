<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3';
import FormHeader from '@/components/custom/FormHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import loyaltyMemberRoutes from '@/routes/loyalty-members';

const form = useForm({
    name: '',
    phone_number: '',
    loyalty_id: '',
    points: ''
});

const submitForm = () => {
    form.post(loyaltyMemberRoutes.store.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Product" />

    <div class="form">
        <FormHeader :backUrl="loyaltyMemberRoutes.index().url" title="Create Product" />

        <form @submit.prevent="submitForm" enctype="multipart/form-data">
            <div class="form-section">
                <h3 class="section-title">Basic Information</h3>

                <div class="inputs-group">
                    <Label for="name" class="required">Member's Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="Product name"
                        autofocus
                    />
                    <InputError :message="form.errors.name" />
                </div>
                
                <div class="inputs-group">
                    <Label for="phone_number" class="required">Phone Number</Label>
                    <Input
                        id="phone_number"
                        v-model="form.phone_number"
                        type="text"
                        placeholder="0746xxxxxx"
                    />
                    <InputError :message="form.errors.phone_number" />
                </div>

                <div class="inputs-group">
                    <Label for="points" class="required">Points</Label>
                    <Input
                        id="points"
                        v-model="form.points"
                        type="text"
                        placeholder="10"
                    />
                    <InputError :message="form.errors.points" />
                </div>
            </div>

            <div class="submit-buttons">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Create Member
                </Button>

                <div>
                    <Link :href="loyaltyMemberRoutes.index().url">
                        <Button type="button" variant="outline">
                            Cancel
                        </Button>
                    </Link>
                </div>
            </div>
        </form>
    </div>
</template>