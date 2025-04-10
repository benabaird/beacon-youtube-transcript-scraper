<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class VideoUpdater
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $request,
        private VideoRepository $videos,
        private YouTubeTranscriptDownloader $transcripts,
    ) {}

    public function update(InputBag $post): void
    {
        if ($post->has('update_visibility')) {
            $this->updateVisibility($post);
        }

        if ($post->has('retrieve_transcripts')) {
            $this->retrieveTranscripts($post);
        }
    }

    public function updateVisibility(InputBag $post): void
    {
        $updated = 0;
        foreach (array_keys($post->all('hide') ?: $post->all('show')) as $id) {
            $updated++;
            $this->videos->find($id)->setHidden($post->has('hide'));
        }
        $this->entityManager->flush();

        $new_state = $post->has('hide') ? 'hidden' : 'shown';
        $this->request->getSession()->getFlashBag()->add(LogLevel::INFO, "$updated video(s) $new_state.");
    }

    public function retrieveTranscripts(InputBag $post): void
    {
        $successes = 0;
        $failures = 0;
        foreach (array_keys($post->all('video_id') ?? []) as $id) {
            $video = $this->videos->find($id);

            // Do not get the transcript of a video which already has one.
            if ($video->hasTranscript()) {
                continue;
            }

            $transcript = $this->transcripts->retrieveTranscript($video->getVideoId());
            $video->setTranscript(serialize($transcript));
            $this->entityManager->flush();

            if ($transcript) {
                $successes++;
            }
            else {
                $failures++;
            }
        }

        $this->request->getSession()->getFlashBag()->add(LogLevel::INFO, "$successes transcript(s) retrieved.");
        $this->request->getSession()->getFlashBag()->add(LogLevel::WARNING, "$failures transcript(s) could not be retrieved or do not exist on the video(s).");
    }

}
