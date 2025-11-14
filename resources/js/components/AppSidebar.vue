<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import * as usersRoutes from '@/routes/users';
import * as rolesRoutes from '@/routes/roles';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, Shield, MessageCircle, Settings, Calendar, CalendarCheck, Stethoscope, Clock, Baby, MapPin } from 'lucide-vue-next';
import * as locationsRoutes from '@/routes/locations';
import AppLogo from './AppLogo.vue';
import { wTrans } from 'laravel-vue-i18n';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePermissions } from '@/composables/usePermissions';

// Determine sidebar side based on language direction
// Watch for changes in the HTML dir attribute which changes when language changes
const documentDir = ref(document.documentElement.getAttribute('dir') || 'ltr');

const sidebarSide = computed(() => documentDir.value === 'rtl' ? 'right' : 'left');

// Watch for dir attribute changes using MutationObserver
let observer: MutationObserver | null = null;

onMounted(() => {
    observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'dir') {
                documentDir.value = document.documentElement.getAttribute('dir') || 'ltr';
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['dir'],
    });
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});

// Get permissions
const { hasPermission } = usePermissions();

// Define all possible nav items with their required permissions
const allNavItems = [
    // Main Section
    {
        title: wTrans('sidebar.dashboard'),
        href: dashboard(),
        icon: LayoutGrid,
        permission: 'view dashboard sidebar',
        group: 'main'
    },
    {
        title: wTrans('sidebar.chat'),
        href: '/chat',
        icon: MessageCircle,
        permission: 'view chat',
        group: 'main'
    },

    // Appointments & Bookings Section
    {
        title: wTrans('sidebar.bookings'),
        icon: Calendar,
        permission: 'can-book',
        group: 'appointments',
        items: [
            {
                title: wTrans('sidebar.book_appointment'),
                href: '/book',
                icon: CalendarCheck,
            },
            {
                title: wTrans('sidebar.my_appointments'),
                href: '/appointments',
                icon: Calendar,
            },
        ],
    },
    {
        title: wTrans('sidebar.bookings'),
        icon: Stethoscope,
        permission: 'book-sys',
        group: 'appointments',
        items: [
            {
                title: wTrans('sidebar.provider_profile'),
                href: '/provider/configuration',
                icon: Stethoscope,
            },
            {
                title: wTrans('sidebar.my_appointments'),
                href: '/appointments',
                icon: Calendar,
            },
        ],
    },
    {
        title: wTrans('sidebar.appointments_management'),
        icon: Calendar,
        permission: 'manage bookings',
        group: 'appointments',
        items: [
            {
                title: wTrans('sidebar.all_appointments'),
                href: '/appointments',
                icon: Calendar,
            },
            {
                title: wTrans('sidebar.pending_approvals'),
                href: '/appointments?status=pending',
                icon: Clock,
            },
            {
                title: wTrans('sidebar.confirmed'),
                href: '/appointments?status=confirmed',
                icon: CalendarCheck,
            },
        ],
    },

    // Management Section
    {
        title: wTrans('sidebar.users'),
        href: usersRoutes.index(),
        icon: Users,
        permission: 'view users sidebar',
        group: 'management'
    },
    {
        title: wTrans('sidebar.roles'),
        href: rolesRoutes.index(),
        icon: Shield,
        permission: 'view roles sidebar',
        group: 'management'
    },
    {
        title: wTrans('sidebar.locations'),
        href: locationsRoutes.index(),
        icon: MapPin,
        permission: 'view locations sidebar',
        group: 'management'
    },
    {
        title: wTrans('sidebar.children'),
        href: '/children',
        icon: Baby,
        permission: 'view children sidebar',
        group: 'management'
    },
    {
        title: wTrans('sidebar.specializations'),
        href: '/specializations',
        icon: Stethoscope,
        permission: 'manage bookings',
        group: 'management'
    },

    // Settings Section
    {
        title: wTrans('sidebar.chat_permissions'),
        href: '/chat/permission-settings',
        icon: Settings,
        permission: 'manage chat',
        group: 'settings'
    },
    {
        title: wTrans('sidebar.settings'),
        href: '/settings',
        icon: Settings,
        permission: 'view settings',
        group: 'settings'
    },
];

// Filter nav items based on user permissions and group them
const groupedNavItems = computed(() => {
    const filtered = allNavItems.filter(item => hasPermission(item.permission));
    
    const groups = {
        main: { title: wTrans('sidebar.main') || 'Main', items: [] as any[] },
        appointments: { title: wTrans('sidebar.appointments') || 'Appointments & Bookings', items: [] as any[] },
        management: { title: wTrans('sidebar.management') || 'Management', items: [] as any[] },
        settings: { title: wTrans('sidebar.settings_group') || 'Settings', items: [] as any[] },
    };

    filtered.forEach(item => {
        const group = item.group || 'main';
        if (groups[group]) {
            groups[group].items.push(item);
        } else {
            groups.main.items.push(item);
        }
    });

    // Return only groups that have items
    return Object.values(groups).filter(group => group.items.length > 0);
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: wTrans('sidebar.documentation'),
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" :side="sidebarSide">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <div v-for="group in groupedNavItems" :key="group.title" class="mb-6">
                <div class="px-3 py-2">
                    <h3 class="mb-2 px-4 text-xs font-semibold text-sidebar-foreground/70 uppercase tracking-wider">
                        {{ group.title }}
                    </h3>
                </div>
                <NavMain :items="group.items" />
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
