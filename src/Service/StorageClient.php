<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StorageClient
{
    private S3Client $client;

    public function __construct()
    {
        $this->client = new S3Client([
            "version" => "latest",
            "region" => "us-east-1",
            "endpoint" => $_ENV["S3_ENDPOINT"],
            "use_path_style_endpoint" => true,
            "credentials" => [
                "key" => $_ENV["S3_KEY"],
                "secret" => $_ENV["S3_SECRET"],
            ],
        ]);
    }

    public function uploadContent(string $contents, string $extension): string
    {
        $filename = md5($contents) . "." . $extension;

        $this->client->putObject([
            "Bucket" => $_ENV["S3_BUCKET"],
            "Key" => $filename,
            "Body" => $contents
        ]);

        return "{$_ENV["CDN_HOST"]}/{$_ENV["S3_BUCKET"]}/{$filename}";
    }

    public function upload(UploadedFile $file): string
    {
        $contents = file_get_contents($file->getPathname());
        $filename = md5($contents) . "." . $file->getClientOriginalExtension();

        return $this->uploadContent(
            file_get_contents($file->getPathname()),
            $filename
        );
    }


}
