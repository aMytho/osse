<?php

namespace App\Http\Controllers;

use App\Models\CoverArt;
use Illuminate\Support\Facades\Storage;

class CoverArtController extends Controller
{
  public function show(CoverArt $cover)
  {
    try {
      return Storage::response($cover->getCoverLocation())
        ->setPublic()
        ->setEtag($cover->hash)
        ->setMaxAge(86400)
        ->setLastModified($cover->updated_at);
    } catch (\Throwable $th) {
      return response()->make(status:404);
    }
  }
}
