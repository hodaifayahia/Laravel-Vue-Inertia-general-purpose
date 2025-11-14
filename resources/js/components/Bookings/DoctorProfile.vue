<script setup lang="ts">
import { computed } from 'vue';
import { 
    Award, 
    Briefcase, 
    Calendar, 
    DollarSign, 
    Globe, 
    GraduationCap, 
    Languages, 
    MapPin, 
    Phone, 
    Star, 
    Users,
    CheckCircle2,
    Facebook,
    Twitter,
    Linkedin,
    Instagram
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

interface ProviderProfile {
    id: number;
    user_id: number;
    specialty: string;
    years_of_experience: number;
    title?: string;
    license_number?: string;
    qualifications?: string[];
    languages?: string[];
    phone?: string;
    office_address?: string;
    clinic_name?: string;
    rating?: number;
    total_reviews?: number;
    total_patients?: number;
    consultation_fee?: number;
    advance_booking_days?: number;
    services_offered?: string[];
    education?: Array<{
        degree: string;
        institution: string;
        year: string;
    }>;
    awards?: Array<{
        title: string;
        year: string;
    }>;
    website?: string;
    social_links?: {
        facebook?: string;
        twitter?: string;
        linkedin?: string;
        instagram?: string;
    };
    province?: {
        id: number;
        name_ar: string;
        name_en: string;
        code: string;
    };
    city?: {
        id: number;
        name_ar: string;
        name_en: string;
    };
    user: {
        name: string;
        email: string;
        photo?: string;
        bio?: string;
    };
}

interface Props {
    provider: ProviderProfile;
    showBookingButton?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showBookingButton: true,
});

const emit = defineEmits<{
    (e: 'bookAppointment'): void;
}>();

const initials = computed(() => {
    return props.provider.user.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase();
});

const fullTitle = computed(() => {
    return `${props.provider.title || 'Dr.'} ${props.provider.user.name}`;
});

const socialIcons: Record<string, any> = {
    facebook: Facebook,
    twitter: Twitter,
    linkedin: Linkedin,
    instagram: Instagram,
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header Card with Photo and Basic Info -->
        <Card>
            <CardContent class="pt-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <Avatar class="h-32 w-32 rounded-lg">
                        <AvatarImage :src="provider.user.photo" :alt="provider.user.name" />
                        <AvatarFallback class="rounded-lg text-2xl">{{ initials }}</AvatarFallback>
                    </Avatar>

                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-bold">{{ fullTitle }}</h1>
                                <p class="text-lg text-muted-foreground mt-1">{{ provider.specialty }}</p>
                                
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <Badge variant="secondary" class="flex items-center gap-1">
                                        <Briefcase class="h-3 w-3" />
                                        {{ provider.years_of_experience }} {{ $t('bookings.years_experience') }}
                                    </Badge>
                                    
                                    <Badge v-if="provider.license_number" variant="secondary" class="flex items-center gap-1">
                                        <CheckCircle2 class="h-3 w-3" />
                                        {{ $t('bookings.license') }}: {{ provider.license_number }}
                                    </Badge>
                                </div>

                                <div v-if="provider.rating" class="flex items-center gap-2 mt-3">
                                    <div class="flex items-center gap-1">
                                        <Star class="h-5 w-5 fill-yellow-400 text-yellow-400" />
                                        <span class="font-bold">{{ provider.rating.toFixed(1) }}</span>
                                    </div>
                                    <span class="text-sm text-muted-foreground">
                                        ({{ provider.total_reviews }} {{ $t('bookings.reviews') }})
                                    </span>
                                    <span v-if="provider.total_patients" class="text-sm text-muted-foreground">
                                        • {{ provider.total_patients }} {{ $t('bookings.patients') }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right">
                                <div v-if="provider.consultation_fee" class="flex items-center justify-end gap-2 mb-3">
                                    <DollarSign class="h-5 w-5 text-muted-foreground" />
                                    <div>
                                        <p class="text-2xl font-bold">${{ provider.consultation_fee }}</p>
                                        <p class="text-xs text-muted-foreground">{{ $t('bookings.consultation_fee') }}</p>
                                    </div>
                                </div>
                                
                                <Button v-if="showBookingButton" @click="emit('bookAppointment')" size="lg" class="w-full md:w-auto">
                                    <Calendar class="h-4 w-4 mr-2" />
                                    {{ $t('bookings.book_appointment') }}
                                </Button>
                            </div>
                        </div>

                        <p v-if="provider.user.bio" class="mt-4 text-muted-foreground">
                            {{ provider.user.bio }}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Contact Information -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Phone class="h-5 w-5" />
                        {{ $t('bookings.contact_information') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <!-- Location Information -->
                    <div v-if="provider.province || provider.city" class="flex items-start gap-3">
                        <MapPin class="h-5 w-5 text-muted-foreground mt-0.5" />
                        <div>
                            <p class="font-medium">{{ $t('الولايات (Provinces)') }} & {{ $t('المدن (Cities/Communes)') }}</p>
                            <p v-if="provider.province" class="text-sm text-muted-foreground">
                                {{ provider.province.name_ar }} ({{ provider.province.name_en }}) - {{ provider.province.code }}
                            </p>
                            <p v-if="provider.city" class="text-sm text-muted-foreground">
                                {{ provider.city.name_ar }} ({{ provider.city.name_en }})
                            </p>
                        </div>
                    </div>

                    <div v-if="provider.clinic_name" class="flex items-start gap-3">
                        <Briefcase class="h-5 w-5 text-muted-foreground mt-0.5" />
                        <div>
                            <p class="font-medium">{{ provider.clinic_name }}</p>
                            <p v-if="provider.office_address" class="text-sm text-muted-foreground">
                                {{ provider.office_address }}
                            </p>
                        </div>
                    </div>

                    <div v-if="provider.phone" class="flex items-center gap-3">
                        <Phone class="h-5 w-5 text-muted-foreground" />
                        <p>{{ provider.phone }}</p>
                    </div>

                    <div v-if="provider.office_address && !provider.clinic_name" class="flex items-start gap-3">
                        <MapPin class="h-5 w-5 text-muted-foreground mt-0.5" />
                        <p>{{ provider.office_address }}</p>
                    </div>

                    <div v-if="provider.website" class="flex items-center gap-3">
                        <Globe class="h-5 w-5 text-muted-foreground" />
                        <a :href="provider.website" target="_blank" class="text-primary hover:underline">
                            {{ provider.website }}
                        </a>
                    </div>

                    <div v-if="provider.social_links && Object.keys(provider.social_links).length > 0">
                        <Separator class="my-3" />
                        <div class="flex gap-2">
                            <Button
                                v-for="(url, platform) in provider.social_links"
                                :key="platform"
                                variant="outline"
                                size="icon"
                                :as="'a'"
                                :href="url"
                                target="_blank"
                            >
                                <component :is="socialIcons[platform]" class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Languages & Qualifications -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Languages class="h-5 w-5" />
                        {{ $t('bookings.languages') }} & {{ $t('bookings.qualifications') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="provider.languages && provider.languages.length > 0">
                        <p class="text-sm font-medium mb-2">{{ $t('bookings.languages_spoken') }}</p>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="lang in provider.languages" :key="lang" variant="outline">
                                {{ lang }}
                            </Badge>
                        </div>
                    </div>

                    <div v-if="provider.qualifications && provider.qualifications.length > 0">
                        <Separator class="my-3" />
                        <p class="text-sm font-medium mb-2">{{ $t('bookings.qualifications') }}</p>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="qual in provider.qualifications" :key="qual" variant="secondary">
                                {{ qual }}
                            </Badge>
                        </div>
                    </div>

                    <div v-if="provider.advance_booking_days">
                        <Separator class="my-3" />
                        <div class="flex items-center gap-2 text-sm">
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                            <span>{{ $t('bookings.advance_booking') }}: {{ provider.advance_booking_days }} {{ $t('bookings.days') }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Services Offered -->
        <Card v-if="provider.services_offered && provider.services_offered.length > 0">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <CheckCircle2 class="h-5 w-5" />
                    {{ $t('bookings.services_offered') }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-2">
                    <div 
                        v-for="service in provider.services_offered" 
                        :key="service"
                        class="flex items-center gap-2 p-2 rounded-md bg-secondary/50"
                    >
                        <CheckCircle2 class="h-4 w-4 text-primary" />
                        <span class="text-sm">{{ service }}</span>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Education -->
        <Card v-if="provider.education && provider.education.length > 0">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <GraduationCap class="h-5 w-5" />
                    {{ $t('bookings.education') }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-3">
                    <div 
                        v-for="(edu, index) in provider.education" 
                        :key="index"
                        class="flex gap-3"
                    >
                        <div class="flex-shrink-0 w-16 text-sm text-muted-foreground font-medium">
                            {{ edu.year }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium">{{ edu.degree }}</p>
                            <p class="text-sm text-muted-foreground">{{ edu.institution }}</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Awards & Achievements -->
        <Card v-if="provider.awards && provider.awards.length > 0">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Award class="h-5 w-5" />
                    {{ $t('bookings.awards') }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-3">
                    <div 
                        v-for="(award, index) in provider.awards" 
                        :key="index"
                        class="flex gap-3"
                    >
                        <div class="flex-shrink-0 w-16 text-sm text-muted-foreground font-medium">
                            {{ award.year }}
                        </div>
                        <div class="flex-1 flex items-center gap-2">
                            <Award class="h-4 w-4 text-yellow-500" />
                            <p class="font-medium">{{ award.title }}</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
