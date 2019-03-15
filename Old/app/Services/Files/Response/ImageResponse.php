<?php namespace App\Services\Files\Response;

use Config;
use Storage;
use App\Upload;
use Symfony\Component\HttpFoundation\Response;

class ImageResponse {

    /**
     * Create response for previewing specified image.
     * Optionally resize image to specified size.
     *
     * @param Upload $upload
     * @param array  $params
     *
     * @return Response
     */
    public function create(Upload $upload, $params = [])
    {
        if (isset($params['width']) && isset($params['height'])) {
            $content = $this->resizeImage([
                'path'      => $upload->path,
                'extension' => $upload->extension,
                'width'     => $params['width'],
                'height'    => $params['height'],
            ]);
        } else {
            $content = Storage::get($upload->path);
        }

        return response($content, 200, ['Content-Type' => $upload->mime]);
    }

    /**
     * Fit image to given size.
     *
     * @param array $params
     * @param string $extension
     *
     * @return \Intervention\Image\Image
     */
    private function resizeImage($params)
    {
        list($path, $extension, $width, $height) = $params;

        $data = Config::get('filesystems.default') === 'local' ? $path : Storage::get($path);

        try {
            return Image::cache(function($image) use($data, $extension, $width, $height) {
                $image->make($data)->fit($width, $height)->encode($extension);
            });
        } catch (\Exception $e) {
            return Image::make($data)->fit($width, $height)->encode($extension);
        }
    }
}