<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\OrderController;

// Mock Admin User
$admin = User::where('email', 'admin@shopswift.com')->first();
if (!$admin) die("Admin not found\n");

// Mock Request
$request = Request::create('/api/v1/orders', 'GET');
$request->setUserResolver(function () use ($admin) {
    return $admin;
});

// Resolve Controller
Illuminate\Support\Facades\Auth::login($admin);
$controller = $app->make(OrderController::class);

try {
    // Call index
    $response = $controller->index($request);
    $data = $response->getData(true);

    echo "Response Keys: " . implode(', ', array_keys($data)) . "\n";
    
    if (isset($data['orders'])) {
        echo "✅ 'orders' key found!\n";
        echo "Orders count: " . count($data['orders']['data']) . "\n";
    } else {
        echo "❌ 'orders' key MISSING!\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
