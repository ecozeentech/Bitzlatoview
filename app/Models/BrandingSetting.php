<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton-style settings row for site branding (logo/favicon), managed at
 * /admin/settings/branding. Falls back to the default Bitzlatoview wordmark when unset.
 *
 * Looked up on every page load (head/logo partials), so kept as a single indexed-PK query
 * rather than cached — a serialized-object cache adds fragility for negligible benefit here.
 */
class BrandingSetting extends Model
{
    protected $guarded = [];

    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], ['site_name' => 'Bitzlatoview']);
    }

    public static function forget(): void
    {
        //
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon_path ? asset('storage/'.$this->favicon_path) : null;
    }
}
