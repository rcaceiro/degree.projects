<?php

namespace App\Http\Controllers;

use App\Http\Resources\Images as ImagesResource;
use App\Http\Resources\ImageTile as ImageTileResource;
use App\Image;
use Intervention\Image\ImageManagerStatic as ImageIntervention;
use Illuminate\Http\Request;


class ImageControllerAPI extends Controller
{
    public function index()
    {
        return ImagesResource::collection(Image::all());
    }

    public function getTiles(Request $request)
    {
        if ($request->has('page')) {
            return ImagesResource::collection(Image::where('face', Image::FACE_TILE)->paginate(10));
        }
        return ImagesResource::collection(Image::where('face', Image::FACE_TILE)->get());
    }

    public function getHidden(Request $request)
    {
        if ($request->has('page')) {
            return ImagesResource::collection(Image::where('face', Image::FACE_HIDDEN)->paginate(10));
        }
        return ImagesResource::collection(Image::where('face', Image::FACE_HIDDEN)->get());
    }

    public function destroy($id)
    {

        $image = Image::findOrFail($id);

        if ($image->isFaceTile() && $image->isActive()) {
            if (Image::where('face', 'tile')->where('active', Image::IS_ACTIVE)->get()->count() == 32) {
                return response()->json('Error. Cannot delete. Minimum value of active tile images needed for the game reached.', 303);
            }
        }

        if ($image->isFaceHidden() && $image->isActive()) {
            if (Image::where('face', 'hidden')->where('active', Image::IS_ACTIVE)->get()->count() == 1) {
                return response()->json('Error. Cannot delete. Minimum value of active hidden images needed for the game reached.', 303);
            }
        }

        $filename = $image->path;

        Image::destroy($id);
        unlink('img/' . $filename);
    }

    public function store()
    {
        $imgCounter = Image::max('id');

        if (request('images')) {
            foreach (request('images') as $index => $base64) {
                $imgCounter++;
                $imgType = request('type');

                if (strcmp($imgType, Image::FACE_HIDDEN) != 0 &&
                    strcmp($imgType, Image::FACE_TILE) != 0) {
                    return response()->json("Unsupported tile type.
                     Only hidden or tile allowed.", 415);
                }

                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $data = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif

                    if (!in_array($type, ['jpg', 'jpeg', 'png'])) {

                        return response()->json("Unsupported type. Only JPG or PNG allowed.", 415);
                    }
                    $data = base64_decode($data);

                    if ($data === false) {
                        return response()->json("Failed to decode base64", 422);
                    }
                } else {
                    return response()->json("Data didn't match", 409);
                }

                ImageIntervention::make($data)->resize(48, 48)->save("img/{$imgCounter}.{$type}");

                Image::create([
                    'face' => "{$imgType}",
                    'active' => '1',
                    'path' => "{$imgCounter}.{$type}"
                ]);


            }
        }
        return response()->json(201);
    }

    public function getShuffledPieces($numberOfPieces)
    {
        $count = Image::where('face', Image::FACE_TILE)
            ->where('active', Image::IS_ACTIVE)
            ->count();

        if ($numberOfPieces > $count) {
            return response()->json(null, 404);
        }

        $shuffledTiles = Image::where('face', Image::FACE_TILE)
            ->where('active', Image::IS_ACTIVE)
            ->get()
            ->shuffle()
            ->slice(0, $numberOfPieces);

        $shuffledTiles = ImageTileResource::
        collection($shuffledTiles
            ->concat($shuffledTiles)
            ->shuffle());

        $randomHidden = Image::where('face', Image::FACE_HIDDEN)
            ->where('active', Image::IS_ACTIVE)
            ->get()
            ->random();
        $randomHidden = new ImageTileResource($randomHidden);

        return response()->json(['tiles' => $shuffledTiles,
            'hidden' => $randomHidden],
            200);
    }

    public function updateActive($id)
    {

        $image = Image::findOrFail($id);

        if (request('active') == Image::IS_NOT_ACTIVE) {
            if ($image->isFaceTile() && $image->isActive()) {
                if (Image::where('face', 'tile')->where('active', Image::IS_ACTIVE)->get()->count() == 32) {
                    return response()->json('Error. Cannot block. Minimum value of active tile images needed for the game reached.', 303);
                }
            }
            if ($image->isFaceHidden() && $image->isActive()) {
                if (Image::where('face', 'hidden')->where('active', Image::IS_ACTIVE)->get()->count() == 1) {
                    return response()->json('Error. Cannot block. Minimum value of active hidden images needed for the game reached.', 303);
                }
            }

            $image->active = Image::IS_NOT_ACTIVE;
            $image->save();
            return new ImagesResource($image);
        }

        if (request('active') == Image::IS_ACTIVE) {
            $image->active = Image::IS_ACTIVE;

            $image->save();
            return new ImagesResource($image);
        }


        return response()->json(null, 400);

    }
}
