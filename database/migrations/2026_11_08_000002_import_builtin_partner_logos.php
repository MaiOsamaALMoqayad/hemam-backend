<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * The public site used to fall back to 9 logos bundled in its own
     * /public/Partners folder whenever the partners table was empty. They're
     * copied here (database/data/partners) and imported once, so they can be
     * edited, deleted and reordered like any partner. Does nothing when the
     * table already has partners.
     */
    private const FILES = ['P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'P9', 'P10'];

    public function up(): void
    {
        if (DB::table('partners')->exists()) {
            return;
        }

        $disk = Storage::disk('public');

        foreach (self::FILES as $i => $name) {
            $source = database_path("data/partners/$name.jpg");
            if (!is_file($source)) {
                continue;
            }

            $path = 'partners/builtin-' . strtolower($name) . '.jpg';
            $disk->put($path, file_get_contents($source));

            DB::table('partners')->insert([
                'name'        => null,
                'image'       => $path,
                'order_index' => $i + 1,
            ]);
        }
    }

    public function down(): void
    {
        $paths = DB::table('partners')->where('image', 'like', 'partners/builtin-%')->pluck('image');
        Storage::disk('public')->delete($paths->all());
        DB::table('partners')->where('image', 'like', 'partners/builtin-%')->delete();
    }
};
