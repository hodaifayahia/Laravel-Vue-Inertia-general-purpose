<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Testing Enhanced Doctor Profiles with Location Data\n";
echo "==================================================\n\n";

$providers = \App\Models\ProviderProfile::with(['user', 'specialization', 'province', 'city'])->get();

foreach ($providers as $provider) {
    echo "👨‍⚕️ {$provider->user->name}\n";
    echo "   Specialization: {$provider->specialization->name}\n";

    if ($provider->province) {
        echo "   📍 Province: {$provider->province->name_ar} ({$provider->province->name_en}) - {$provider->province->code}\n";
    }

    if ($provider->city) {
        echo "   🏛️ City: {$provider->city->name_ar} ({$provider->city->name_en})\n";
    }

    echo "   💼 Clinic: {$provider->clinic_name}\n";
    echo "   📞 Phone: {$provider->phone}\n";
    echo "   💰 Fee: \${$provider->consultation_fee}\n";
    echo "   🏆 Experience: {$provider->years_experience} years\n";

    if ($provider->qualifications) {
        $qualifications = json_decode($provider->qualifications, true);
        echo "   🎓 Qualifications: " . implode(', ', $qualifications) . "\n";
    }

    if ($provider->services_offered) {
        $services = json_decode($provider->services_offered, true);
        echo "   🛠️ Services: " . implode(', ', $services) . "\n";
    }

    echo "\n";
}

echo "✅ Test completed!\n";