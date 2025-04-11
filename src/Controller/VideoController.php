<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Video;
use App\Filters\VideoFilterFactory;
use App\Forms\VideosSearchForm;
use App\Repository\SetRepository;
use App\Services\VideoExporter;
use App\Services\VideoSearcher;
use App\Services\VideoUpdater;
use App\Services\YouTubeTranscriptDownloader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VideoController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {}

    #[Route(path: '/video/{video<\d+>}', name: 'video.view', methods: 'GET')]
    public function view(Video $video): Response
    {
        return $this->render('video/view.html.twig', [
            'title' => "View {$video->getTitle()}",
            'video' => $video,
        ]);
    }

    #[Route(path: '/videos', name: 'videos', methods: 'GET')]
    public function allVideos(
        Request $request,
        FormFactoryInterface $formFactory,
        VideoFilterFactory $filterFactory,
        VideoSearcher $searcher,
        SetRepository $sets,
    ): Response
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = $formFactory->createNamed('', VideosSearchForm::class);
        $form->handleRequest($request);

        $filters = $filterFactory->createFromForm($form);
        if ($filters->isReset) {
            return $this->redirectToRoute('videos');
        }

        return $this->render('video/list.html.twig', [
            'title' => $filters->isEmpty(true) ? 'All Videos' : 'Search Results',
            'form' => $form,
            'videos' => $searcher->filter($filters),
        ]);
    }

    #[Route(path: '/videos', methods: 'POST')]
    public function updateVideos(
        Request $request,
        VideoExporter $exporter,
        VideoUpdater $updater,
    ): Response
    {
        $export = $exporter->export($request->request);
        if ($export instanceof Response) {
            return $export;
        }

        $updater->update($request->request);

        return $this->redirectToRoute('videos');
    }

    #[Route(path: '/video/{video<\d+>}/transcript', name: 'video.retrieve_transcript', methods: 'GET')]
    public function retrieveTranscript(
        Video $video,
        YouTubeTranscriptDownloader $transcripts,
    ): Response
    {
        if (!$video->hasRetrievedTranscript()) {
            $transcript = $transcripts->retrieveTranscript($video->getVideoId());
            $video->setTranscript(serialize($transcript));
            $this->entityManager->flush();

            if ($transcript) {
                $this->addFlash(LogLevel::NOTICE, "Transcript retrieved for video \"{$video->getTitle()}\".");
            }
            else {
                $this->addFlash(LogLevel::WARNING, "Transcript could not be retrieved or did not exist for video \"{$video->getTitle()}\".");
            }
        }

        return $this->redirectToRoute('video.view', ['video' => $video->getId()]);
    }

    #[Route(path: '/video/{video<\d+>}/hide', name: 'video.hide', methods: 'GET')]
    public function hide(Video $video): Response
    {
        $video->setHidden(true);
        $this->entityManager->flush();
        $this->addFlash(LogLevel::NOTICE, "Video \"{$video->getTitle()}\" has been hidden.");
        return $this->redirectToRoute('video.view', ['video' => $video->getId()]);
    }

    #[Route(path: '/video/{video<\d+>}/show', name: 'video.show', methods: 'GET')]
    public function show(Video $video): Response
    {
        $video->setHidden(false);
        $this->entityManager->flush();
        $this->addFlash(LogLevel::NOTICE, "Video \"{$video->getTitle()}\" is no longer hidden.");
        return $this->redirectToRoute('video.view', ['video' => $video->getId()]);
    }

}
