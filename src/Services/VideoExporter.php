<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Video;
use App\Repository\VideoRepository;
use DateTimeInterface;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class VideoExporter
{

    public function __construct(
        private VideoRepository $videos,
    ) {}

    public function export(InputBag $post): ?Response
    {
        $now = new \DateTimeImmutable()->format(DateTimeInterface::ATOM);

        if ($post->has('download_csv')) {
            return new Response($this->asCsv($post), Response::HTTP_OK, [
                'Content-Encoding' => 'UTF-8',
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"export-$now.csv\"",
            ]);
        }

        if ($post->has('download_json')) {
            return new JSONResponse($this->getVideosAsData($post), Response::HTTP_OK, [
                'Content-Encoding' => 'UTF-8',
                'Content-Disposition' => "attachment; filename=\"export-$now.json\"",
            ]);
        }

        return null;
    }

    public function asJson(InputBag $post): string
    {
        return json_encode($this->getVideosAsData($post));
    }

    public function asCsv(InputBag $post): string
    {
        $data = $this->getVideosAsData($post);
        $csv = Writer::createFromString();
        $csv->insertOne(array_keys(reset($data)));
        $csv->insertAll($data);
        return $csv->toString();
    }

    private function getVideos(InputBag $post): array
    {
        return $this->videos->findBy(['id' => array_keys($post->all('video_id') ?? [])]);
    }

    private function getVideosAsData(InputBag $post): array
    {
        return array_map(
            fn(Video $video) => $video->toArray(),
            $this->getVideos($post),
        );
    }

}
