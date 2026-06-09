namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // 🔴 Make sure to add this import

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 🔴 Force HTTPS when running on Render production
        if (config('app.env') === 'production' || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
