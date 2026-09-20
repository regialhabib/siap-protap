$query = App\Models\Pengamatan::query();
$query->paginate(10);
echo "SUM: " . $query->sum('serangan_jumlah') . "\n";
