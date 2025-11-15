<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Plus, Edit, Trash2, Baby, Calendar, Save } from 'lucide-vue-next';
import { wTrans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';

interface Child {
    id: number;
    name: string;
    date_of_birth: string;
    gender: string | null;
    medical_notes: string | null;
    partner?: {
        id: number;
        name: string;
        email: string;
    };
    age: number;
}

interface Props {
    children: {
        data: Child[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    canManageAll: boolean;
}

const props = defineProps<Props>();

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editingChild = ref<Child | null>(null);

const createForm = useForm({
    name: '',
    date_of_birth: '',
    gender: '',
    medical_notes: '',
});

const editForm = useForm({
    name: '',
    date_of_birth: '',
    gender: '',
    medical_notes: '',
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    isCreateModalOpen.value = true;
};

const openEditModal = (child: Child) => {
    editingChild.value = child;
    editForm.name = child.name;
    editForm.date_of_birth = child.date_of_birth;
    editForm.gender = child.gender || '';
    editForm.medical_notes = child.medical_notes || '';
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitCreate = () => {
    createForm.post('/children', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        },
    });
};

const submitEdit = () => {
    if (!editingChild.value) return;
    
    editForm.put(`/children/${editingChild.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
            editingChild.value = null;
        },
    });
};

const deleteChild = (childId: number) => {
    if (confirm('Are you sure you want to delete this child?')) {
        router.delete(`/children/${childId}`);
    }
};

const getGenderBadge = (gender: string | null) => {
    if (gender === 'male') return { text: 'Male', class: 'bg-blue-100 text-blue-800' };
    if (gender === 'female') return { text: 'Female', class: 'bg-pink-100 text-pink-800' };
    return { text: 'Not specified', class: 'bg-gray-100 text-gray-800' };
};

const calculateAge = (dateOfBirth: string) => {
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
};
</script>

<template>
    <AppLayout>
        <Head :title="wTrans('My Children')" />

        <div class="container mx-auto p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ canManageAll ? 'All Children' : 'My Children' }}
                    </h1>
                    <p class="text-muted-foreground mt-1">
                        {{ canManageAll ? 'View all children in the system' : 'Manage your children information' }}
                    </p>
                </div>
                <Button @click="openCreateModal">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Child
                </Button>
            </div>

            <!-- Children List -->
            <div v-if="children.data.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="child in children.data" :key="child.id" class="hover:shadow-lg transition-shadow">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg">
                                    <Baby class="w-5 h-5 text-primary" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">{{ child.name }}</CardTitle>
                                    <CardDescription v-if="child.partner && canManageAll">
                                        Parent: {{ child.partner.name }}
                                    </CardDescription>
                                </div>
                            </div>
                            <Badge :class="getGenderBadge(child.gender).class">
                                {{ getGenderBadge(child.gender).text }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Calendar class="w-4 h-4" />
                            <span>Age: {{ calculateAge(child.date_of_birth) }} years old</span>
                        </div>
                        
                        <div v-if="child.medical_notes" class="p-3 bg-muted rounded-lg">
                            <p class="text-sm font-medium mb-1">Medical Notes:</p>
                            <p class="text-sm text-muted-foreground">{{ child.medical_notes }}</p>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <Button variant="outline" size="sm" @click="openEditModal(child)" class="flex-1">
                                <Edit class="w-3 h-3 mr-1" />
                                Edit
                            </Button>
                            <Button 
                                variant="destructive" 
                                size="sm" 
                                @click="deleteChild(child.id)"
                                class="flex-1"
                            >
                                <Trash2 class="w-3 h-3 mr-1" />
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else class="text-center py-12">
                <CardContent class="space-y-4">
                    <div class="mx-auto w-16 h-16 bg-muted rounded-full flex items-center justify-center">
                        <Baby class="w-8 h-8 text-muted-foreground" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">No children added yet</h3>
                        <p class="text-muted-foreground mt-1">
                            Start by adding your first child to manage their appointments
                        </p>
                    </div>
                    <Button @click="openCreateModal">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Your First Child
                    </Button>
                </CardContent>
            </Card>

            <!-- Create Child Modal -->
            <Dialog v-model:open="isCreateModalOpen">
                <DialogContent class="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle>Add New Child</DialogTitle>
                        <DialogDescription>Enter your child's information below</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitCreate" class="space-y-4 mt-4">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="create-name">Full Name <span class="text-red-500">*</span></Label>
                            <Input
                                id="create-name"
                                v-model="createForm.name"
                                type="text"
                                placeholder="Enter child's full name"
                                :class="{ 'border-red-500': createForm.errors.name }"
                                required
                            />
                            <p v-if="createForm.errors.name" class="text-sm text-red-500">{{ createForm.errors.name }}</p>
                        </div>

                        <!-- Date of Birth -->
                        <div class="space-y-2">
                            <Label for="create-dob">Date of Birth <span class="text-red-500">*</span></Label>
                            <Input
                                id="create-dob"
                                v-model="createForm.date_of_birth"
                                type="date"
                                :max="new Date().toISOString().split('T')[0]"
                                :class="{ 'border-red-500': createForm.errors.date_of_birth }"
                                required
                            />
                            <p v-if="createForm.errors.date_of_birth" class="text-sm text-red-500">
                                {{ createForm.errors.date_of_birth }}
                            </p>
                        </div>

                        <!-- Gender -->
                        <div class="space-y-2">
                            <Label for="create-gender">Gender</Label>
                            <select
                                id="create-gender"
                                v-model="createForm.gender"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                :class="{ 'border-red-500': createForm.errors.gender }"
                            >
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <p v-if="createForm.errors.gender" class="text-sm text-red-500">{{ createForm.errors.gender }}</p>
                        </div>

                        <!-- Medical Notes -->
                        <div class="space-y-2">
                            <Label for="create-notes">Medical Notes</Label>
                            <Textarea
                                id="create-notes"
                                v-model="createForm.medical_notes"
                                placeholder="Any important medical information..."
                                rows="3"
                                :class="{ 'border-red-500': createForm.errors.medical_notes }"
                            />
                            <p v-if="createForm.errors.medical_notes" class="text-sm text-red-500">
                                {{ createForm.errors.medical_notes }}
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3 pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                class="flex-1"
                                @click="isCreateModalOpen = false"
                                :disabled="createForm.processing"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" class="flex-1" :disabled="createForm.processing">
                                <Save class="w-4 h-4 mr-2" />
                                {{ createForm.processing ? 'Saving...' : 'Save Child' }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Edit Child Modal -->
            <Dialog v-model:open="isEditModalOpen">
                <DialogContent class="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle>Edit Child</DialogTitle>
                        <DialogDescription>Update {{ editingChild?.name }}'s information</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitEdit" class="space-y-4 mt-4">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="edit-name">Full Name <span class="text-red-500">*</span></Label>
                            <Input
                                id="edit-name"
                                v-model="editForm.name"
                                type="text"
                                placeholder="Enter child's full name"
                                :class="{ 'border-red-500': editForm.errors.name }"
                                required
                            />
                            <p v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</p>
                        </div>

                        <!-- Date of Birth -->
                        <div class="space-y-2">
                            <Label for="edit-dob">Date of Birth <span class="text-red-500">*</span></Label>
                            <Input
                                id="edit-dob"
                                v-model="editForm.date_of_birth"
                                type="date"
                                :max="new Date().toISOString().split('T')[0]"
                                :class="{ 'border-red-500': editForm.errors.date_of_birth }"
                                required
                            />
                            <p v-if="editForm.errors.date_of_birth" class="text-sm text-red-500">
                                {{ editForm.errors.date_of_birth }}
                            </p>
                        </div>

                        <!-- Gender -->
                        <div class="space-y-2">
                            <Label for="edit-gender">Gender</Label>
                            <select
                                id="edit-gender"
                                v-model="editForm.gender"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                :class="{ 'border-red-500': editForm.errors.gender }"
                            >
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <p v-if="editForm.errors.gender" class="text-sm text-red-500">{{ editForm.errors.gender }}</p>
                        </div>

                        <!-- Medical Notes -->
                        <div class="space-y-2">
                            <Label for="edit-notes">Medical Notes</Label>
                            <Textarea
                                id="edit-notes"
                                v-model="editForm.medical_notes"
                                placeholder="Any important medical information..."
                                rows="3"
                                :class="{ 'border-red-500': editForm.errors.medical_notes }"
                            />
                            <p v-if="editForm.errors.medical_notes" class="text-sm text-red-500">
                                {{ editForm.errors.medical_notes }}
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3 pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                class="flex-1"
                                @click="isEditModalOpen = false"
                                :disabled="editForm.processing"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" class="flex-1" :disabled="editForm.processing">
                                <Save class="w-4 h-4 mr-2" />
                                {{ editForm.processing ? 'Updating...' : 'Update Child' }}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
