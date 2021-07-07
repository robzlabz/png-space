<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use League\ColorExtractor\Palette;
use ourcodeworld\NameThatColor\ColorInterpreter;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();

        $this->addMediaCollection('images')->useDisk('s3');
        $this->addMediaCollection('images')->registerMediaConversions(function (Media $media) {
            $this->addMediaConversion('thumb')
                ->width(500)
                ->background('lightgray');
        });
    }

    public function addImage($url, $name, $tags = '')
    {
        info("Add Image with Prams", [$url, $name, $tags]);

        $tempDir = (new TemporaryDirectory())
            ->force()
            ->create();

        $path = $tempDir->path('image.png');

        $response = Http::retry(3)
            ->get($url);

        info("Temaporary Path & Response Status", ['path' => $path, 'status' => $response->status()]);

        file_put_contents($path, $response->body());

        $media = $this
            ->addMedia($path)
            ->setName($name)
            ->preservingOriginal()
            ->toMediaCollection('images');

        // get color from media
        $palette = Palette::fromFilename($path)->getMostUsedColors(5);

        foreach ($palette as $color => $count) {
            $colorHex = \League\ColorExtractor\Color::fromIntToHex($color);
            $colorName = (new ColorInterpreter)->name($colorHex);

            $colorModel = Color::firstOrCreate([
                'hex' => $colorName['hex'],
                'name' => $colorName['name']
            ]);
            $media->colors()->syncWithoutDetaching($colorModel->id);
        }

        if (is_array($tags)) {
            collect($tags)->map(function ($tag) use ($media) {
                $tagModel = Tag::firstOrCreate(['name' => trim($tag)]);
                $media->tags()->syncWithoutDetaching($tagModel->id);
            });
        } else {
            Str::of($tags)->explode(',')->map(function ($tag) use ($media) {
                $tagModel = Tag::firstOrCreate(['name' => trim($tag)]);
                $media->tags()->syncWithoutDetaching($tagModel->id);
            });
        }


        $tempDir->delete();
    }
}
