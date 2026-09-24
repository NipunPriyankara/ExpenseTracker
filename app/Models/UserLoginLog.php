<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'platform',
        'login_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a user login log and update user last login attributes
     */
    public static function recordLogin(User $user, ?Request $request = null, string $status = 'success'): self
    {
        $request = $request ?: request();
        $userAgent = $request ? $request->userAgent() : null;
        $ip = $request ? $request->ip() : null;
        $parsed = self::parseUserAgent($userAgent);

        $now = now();

        // Prevent duplicate log in short interval (e.g. 3 seconds)
        $existing = self::where('user_id', $user->id)
            ->where('login_at', '>=', now()->subSeconds(3))
            ->first();

        if ($existing) {
            return $existing;
        }

        $log = self::create([
            'user_id' => $user->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
            'login_at' => $now,
            'status' => $status,
        ]);

        $user->forceFill([
            'last_login_at' => $now,
            'last_login_ip' => $ip,
        ])->saveQuietly();

        return $log;
    }

    /**
     * Parse User-Agent string to extract device, browser, and OS platform
     */
    public static function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'device' => 'Desktop',
                'browser' => 'Browser',
                'platform' => 'Unknown OS',
            ];
        }

        // Platform detection
        $platform = 'Unknown OS';
        if (preg_match('/windows nt 10/i', $userAgent)) {
            $platform = 'Windows 10/11';
        } elseif (preg_match('/windows nt 6\.3/i', $userAgent)) {
            $platform = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6\.2/i', $userAgent)) {
            $platform = 'Windows 8';
        } elseif (preg_match('/windows nt 6\.1/i', $userAgent)) {
            $platform = 'Windows 7';
        } elseif (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone/i', $userAgent)) {
            $platform = 'iOS (iPhone)';
        } elseif (preg_match('/ipad/i', $userAgent)) {
            $platform = 'iPadOS';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Device detection
        $device = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            $device = 'Tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop/i', $userAgent)) {
            $device = 'Mobile';
        }

        // Browser detection
        $browser = 'Browser';
        if (preg_match('/edg/i', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/opr|opera/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/msie|trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        }

        return [
            'device' => $device,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }
}
