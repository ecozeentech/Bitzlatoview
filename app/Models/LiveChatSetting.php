<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton settings row for the Tawk.to live-support widget, managed at
 * /admin/settings/live-chat. The widget only renders (see partials.tawk-widget) once both
 * property_id and widget_id are set and is_enabled is true.
 */
class LiveChatSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], ['is_enabled' => false]);
    }

    public function isActive(): bool
    {
        return $this->is_enabled && $this->property_id && $this->widget_id;
    }
}
