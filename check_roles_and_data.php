<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== Available Roles ===\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "- " . $role->name . "\n";
}

echo "\n=== Users with Roles ===\n";
$users = User::with('roles')->limit(5)->get();
foreach ($users as $user) {
    echo "User: " . $user->name . " (" . $user->email . ")\n";
    echo "  Roles: ";
    if ($user->roles->count() > 0) {
        echo implode(", ", $user->roles->pluck('name')->toArray());
    } else {
        echo "No roles";
    }
    echo "\n";
}

echo "\n=== ProviderProfile Count ===\n";
$providerProfiles = \App\Models\ProviderProfile::count();
echo "Total ProviderProfiles: " . $providerProfiles . "\n";

if ($providerProfiles > 0) {
    $profile = \App\Models\ProviderProfile::with('user', 'specialization', 'city.province')->first();
    if ($profile) {
        echo "\nFirst Provider Profile:\n";
        echo "  User: " . ($profile->user?->name ?? 'N/A') . "\n";
        echo "  Specialization: " . ($profile->specialization?->name_en ?? 'N/A') . "\n";
        echo "  City: " . ($profile->city?->name_en ?? 'N/A') . "\n";
        echo "  Province: " . ($profile->city?->province?->name_en ?? 'N/A') . "\n";
    }
}
?>
