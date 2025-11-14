<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface City { id: number; name_ar: string; name_en: string }
interface Province { id: number; code: string; name_ar: string; name_en: string; cities: City[] }

interface Props { provinces: Province[] }
const props = defineProps<Props>()

const provinceForm = useForm({ code: '', name_ar: '', name_en: '' })
const cityForm = useForm({ province_id: '', name_ar: '', name_en: '' })

// Add selected province state
const selectedProvince = ref<Province | null>(null)

// Function to select a province
const selectProvince = (province: Province) => {
  selectedProvince.value = selectedProvince.value?.id === province.id ? null : province
}

// Get filtered cities based on selected province
const filteredCities = computed(() => {
  if (!selectedProvince.value) return []
  return selectedProvince.value.cities
})

// Get total cities count
const totalCities = computed(() => {
  return props.provinces.reduce((total, province) => total + province.cities.length, 0)
})

const submitProvince = () => {
  provinceForm.post('/locations/provinces', {
    onSuccess: () => provinceForm.reset(),
  })
}

const submitCity = () => {
  cityForm.post('/locations/cities', {
    onSuccess: () => cityForm.reset(),
  })
}

const removeProvince = (id: number) => {
  router.delete(`/locations/provinces/${id}`)
}

const removeCity = (id: number) => {
  router.delete(`/locations/cities/${id}`)
}
</script>

<template>
  <AppLayout>
    <Head title="Algeria Locations" />

    <div class="container mx-auto p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">المناطق الجزائرية (Algeria Locations)</h1>
        <div class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
          {{ provinces.length }} ولايات • {{ totalCities }} مدينة
        </div>
      </div>
      
      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Provinces Card -->
        <Card class="h-fit">
          <CardHeader class="pb-3">
            <CardTitle class="flex items-center gap-2">
              <span>الولايات (Provinces)</span>
              <span class="text-sm font-normal text-gray-500">({{ provinces.length }})</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2 max-h-96 overflow-y-auto">
              <div 
                v-for="province in provinces" 
                :key="province.id" 
                :class="[
                  'flex justify-between items-center p-3 rounded-lg cursor-pointer transition-all duration-200 hover:shadow-md',
                  selectedProvince?.id === province.id 
                    ? 'bg-blue-100 border-2 border-blue-300 shadow-md' 
                    : 'bg-gray-50 hover:bg-gray-100 border-2 border-transparent'
                ]"
                @click="selectProvince(province)"
              >
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ province.name_ar }}</div>
                  <div class="text-sm text-gray-600">{{ province.name_en }}</div>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs bg-gray-200 px-2 py-1 rounded">{{ province.code }}</span>
                    <span class="text-xs text-gray-500">{{ province.cities.length }} commune(s)</span>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <div v-if="selectedProvince?.id === province.id" class="w-3 h-3 bg-blue-500 rounded-full"></div>
                  <Button variant="destructive" size="sm" @click.stop="removeProvince(province.id)">حذف</Button>
                </div>
              </div>
            </div>

            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
              <Label class="block mb-3 font-semibold text-blue-900">إضافة ولاية جديدة (Add Province)</Label>
              <div class="space-y-3">
                <Input v-model="provinceForm.code" placeholder="Code (e.g., 01)" class="border-blue-300 focus:border-blue-500" />
                <Input v-model="provinceForm.name_ar" placeholder="Arabic name" class="border-blue-300 focus:border-blue-500" />
                <Input v-model="provinceForm.name_en" placeholder="English name" class="border-blue-300 focus:border-blue-500" />
                <Button @click="submitProvince" class="w-full bg-blue-600 hover:bg-blue-700">إضافة الولاية</Button>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Cities Card -->
        <Card class="h-fit">
          <CardHeader class="pb-3">
            <CardTitle class="flex items-center gap-2">
              <span>المدن (Cities/Communes)</span>
              <span class="text-sm font-normal text-gray-500">
                ({{ selectedProvince ? filteredCities.length : totalCities }})
              </span>
            </CardTitle>
            <div v-if="selectedProvince" class="text-sm text-blue-600 bg-blue-50 px-3 py-2 rounded-md border border-blue-200">
              عرض المدن في: <strong>{{ selectedProvince.name_ar }}</strong> ({{ selectedProvince.code }})
              <Button 
                variant="ghost" 
                size="sm" 
                class="ml-2 h-6 px-2 text-xs" 
                @click="selectedProvince = null"
              >
                عرض الكل
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="!selectedProvince" class="text-center py-8 text-gray-500">
              <div class="text-lg mb-2">🏛️</div>
              <p>اختر ولاية لعرض مدنها</p>
              <p class="text-sm">Click on a province to view its cities</p>
            </div>
            
            <div v-else class="space-y-2 max-h-96 overflow-y-auto">
              <div v-for="city in filteredCities" :key="city.id" class="flex justify-between items-center p-3 bg-green-50 rounded-lg border border-green-200">
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ city.name_ar }}</div>
                  <div class="text-sm text-gray-600">{{ city.name_en }}</div>
                </div>
                <Button variant="destructive" size="sm" @click="removeCity(city.id)">حذف</Button>
              </div>
            </div>

            <div v-if="selectedProvince" class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
              <Label class="block mb-3 font-semibold text-green-900">إضافة مدينة جديدة (Add City)</Label>
              <div class="space-y-3">
                <div class="text-sm text-gray-600 mb-2">
                  سيتم إضافة المدينة إلى: <strong>{{ selectedProvince.name_ar }}</strong>
                </div>
                <Input v-model="cityForm.name_ar" placeholder="Arabic name" class="border-green-300 focus:border-green-500" />
                <Input v-model="cityForm.name_en" placeholder="English name" class="border-green-300 focus:border-green-500" />
                <Button 
                  @click="() => { cityForm.province_id = selectedProvince!.id.toString(); submitCity(); }" 
                  class="w-full bg-green-600 hover:bg-green-700"
                >
                  إضافة المدينة
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>