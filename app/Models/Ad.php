<?php

namespace App\Models;

use App\Services\Parsedown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $dates = ['starts_at', 'ends_at'];

    public static function getForCurrentPage(): ?self
    {
        return Ad::getForPage(request()->path());
    }

    public static function getForPage(string $url = ''): ?self
    {
        return static::getPageSpecificAd($url) ?? static::getSiteWideAd();
    }

    public static function getPageSpecificAd(string $url): ?self
    {
        return static::current()
            ->where('display_on_url', $url)
            ->first();
    }

    public static function getSiteWideAd(): ?self
    {
        return static::current()
            ->where(function (Builder $query) {
                $query
                    ->where('display_on_url', '')
                    ->orWhereNull('display_on_url');
            })
            ->first();
    }

    public function scopeCurrent(Builder $query)
    {
        $now = now()->format('Y-m-d');

        $query
            ->whereDate('starts_at', '<=', $now)
            ->whereDate('ends_at', '>=', $now);
    }

    public function getFormattedTextAttribute($original)
    {
        $adText = $this->text . ' (sponsored link)';

        return (new Parsedown())->text($adText);
    }

    public function getExcerptAttribute()
    {
        return str_limit($this->text);
    }
}
