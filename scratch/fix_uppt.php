$uppts = App\Models\Uppt::all();
foreach(App\Models\User::where('role', 'popt')->get() as $user) {
    if (!$user->uppt_id && $uppts->isNotEmpty()) {
        $user->update(['uppt_id' => $uppts->random()->id]);
    }
}
echo "Assigned UPPT to POPT users.\n";
