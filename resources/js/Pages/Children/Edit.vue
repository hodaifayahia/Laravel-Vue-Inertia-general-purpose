<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { wTrans } from 'laravel-vue-i18n';

interface Child {
    id: number;
    name: string;
    date_of_birth: string;
    gender: string | null;
    medical_notes: string | null;
}

interface Props {
    child: Child;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.child.name,
    date_of_birth: props.child.date_of_birth,
    gender: props.child.gender || '',
    medical_notes: props.child.medical_notes || '',
});

const submit = () => {
    form.put(`/children/${props.child.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit('/children');
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="wTrans('Edit Child')" />

        <div class="container mx-auto p-6 max-w-2xl">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" @click="router.visit('/children')">
                        <ArrowLeft class="w-4 h-4" />
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Edit Child</h1>
                        <p class="text-muted-foreground mt-1">Update {{ child.name }}'s information</p>
                    </div>
                </div>

                <!-- Form Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Child Information</CardTitle>
                        <CardDescription>Update the details of your child below</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name">Full Name <span class="text-red-500">*</span></Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Enter child's full name"
                                    :class="{ 'border-red-500': form.errors.name }"
                                    required
                                />
                                <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <!-- Date of Birth -->
                            <div class="space-y-2">
                                <Label for="date_of_birth">Date of Birth <span class="text-red-500">*</span></Label>
                                <Input
                                    id="date_of_birth"
                                    v-model="form.date_of_birth"
                                    type="date"
                                    :max="new Date().toISOString().split('T')[0]"
                                    :class="{ 'border-red-500': form.errors.date_of_birth }"
                                    required
                                />
                                <p v-if="form.errors.date_of_birth" class="text-sm text-red-500">
                                    {{ form.errors.date_of_birth }}
                                </p>
                            </div>

                            <!-- Gender -->
                            <div class="space-y-2">
                                <Label for="gender">Gender</Label>
                                <select
                                    id="gender"
                                    v-model="form.gender"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="{ 'border-red-500': form.errors.gender }"
                                >
                                    <option value="">Select gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                <p v-if="form.errors.gender" class="text-sm text-red-500">{{ form.errors.gender }}</p>
                            </div>

                            <!-- Medical Notes -->
                            <div class="space-y-2">
                                <Label for="medical_notes">Medical Notes</Label>
                                <Textarea
                                    id="medical_notes"
                                    v-model="form.medical_notes"
                                    placeholder="Any important medical information, allergies, or conditions..."
                                    rows="4"
                                    :class="{ 'border-red-500': form.errors.medical_notes }"
                                />
                                <p v-if="form.errors.medical_notes" class="text-sm text-red-500">
                                    {{ form.errors.medical_notes }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    This information will help doctors provide better care
                                </p>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-3 pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="flex-1"
                                    @click="router.visit('/children')"
                                    :disabled="form.processing"
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" class="flex-1" :disabled="form.processing">
                                    <Save class="w-4 h-4 mr-2" />
                                    {{ form.processing ? 'Updating...' : 'Update Child' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
