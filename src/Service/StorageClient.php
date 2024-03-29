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

    public function uploadFile(UploadedFile $file): string
    {
        $contents = file_get_contents($file->getPathname());
        $filename = md5($contents) . "." . $file->getClientOriginalExtension();

        return $this->uploadContent($filename, $contents, $file->getClientMimeType());
    }

    public function uploadContent(string $filename, string $contents, string $contentType = null): string
    {
        $options = [
            "Bucket" => $_ENV["S3_BUCKET"],
            "Key" => $filename,
            "Body" => $contents
        ];

        if ($contentType) {
            $options["ContentType"] = $contentType;
        }

        $this->client->putObject($options);

        return "{$_ENV["S3_ENDPOINT"]}/{$_ENV["S3_BUCKET"]}/{$filename}";
    }

}
