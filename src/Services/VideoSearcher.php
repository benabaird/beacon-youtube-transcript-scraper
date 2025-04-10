<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Video;
use App\Filters\VideoFilter;
use App\Repository\VideoRepository;
use App\Transcript;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

final readonly class VideoSearcher
{

    public function __construct(
        private VideoRepository $videos,
    ) {}

    public function filter(VideoFilter $filter): Collection
    {
        if ($filter->set) {
            $videos = $filter->hidden ? $filter->set->getHiddenVideos() : $filter->set->getShownVideos();
        }
        else {
            $videos = new ArrayCollection($this->videos->findAll());
            $videos = $filter->hidden ? $videos->filter(fn(Video $video) => $video->isHidden()) : $videos;
        }

        if ($filter->transcript) {
            $transcript_filter = match ($filter->transcript) {
                Transcript::Retrieved => fn(Video $video) => $video->hasRetrievedTranscript(),
                Transcript::NotRetrieved => fn(Video $video) => !$video->hasRetrievedTranscript() && !$video->hasTranscript(),
                Transcript::NotAvailable => fn(Video $video) => !$video->hasRetrievedTranscript() && $video->hasTranscript(),
                default => fn(Video $video) => true,
            };
            $videos = $videos->filter($transcript_filter);
        }

        if ($filter->search) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->contains('title', $filter->search))
                ->orWhere(Criteria::expr()->contains('transcript', $filter->search));
            $videos = $videos->matching($criteria);
        }

        return $videos;
    }

}
