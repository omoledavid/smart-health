$kernel = app(\Illuminate\Contracts\Http\Kernel::class);
$user = \App\Models\User::where("role", "admin")->first();
\Illuminate\Support\Facades\Auth::setUser($user);
$pages = ["claims", "waitlist", "care-plans", "referrals", "analytics", "invoices/create"];
foreach ($pages as $path) {
    $req = \Illuminate\Http\Request::create("/$path", "GET");
    $req->headers->set("X-Inertia", "true");
    try {
        $res = $kernel->handle($req);
        echo "$path => " . $res->getStatusCode() . PHP_EOL;
    } catch (\Throwable $e) {
        echo "$path => ERROR: " . $e->getMessage() . PHP_EOL;
    }
}
