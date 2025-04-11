<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Set;
use App\Entity\Video;
use App\Filters\VideoFilterFactory;
use App\Forms\FindVideoForm;
use App\Forms\VideosSetSearchForm;
use App\Services\VideoExporter;
use App\Services\VideoSearcher;
use App\Services\VideoUpdater;
use App\Services\YouTubeSearcher;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SetController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {}

    #[Route(path: '/sets', name: 'sets', methods: 'GET')]
    public function list(): Response
    {
        return $this->render('set/list.html.twig', [
            'title' => 'All Sets',
        ]);
    }

    #[Route(path: '/set/new', name:'set.new' , methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createFormBuilder($set = new Set())
            ->add('name', FormType\TextType::class, ['required' => true])
            ->add('save', FormType\SubmitType::class, ['label' => 'Add'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($set);
            $this->entityManager->flush();
            $this->addFlash(LogLevel::NOTICE, "Set \"{$set->getName()}\" created.");
            return $this->redirectToRoute('set.edit', ['set' => $set->getId()]);
        }

        return $this->render('set/add.html.twig', [
            'title' => "Create new set",
            'set' => $set,
            'form' => $form,
        ]);
    }

    #[Route(path: '/set/{set<\d+>}', name: 'set.view', methods: 'GET', priority: -1)]
    public function view(Set $set): Response {
        return $this->render('set/view.html.twig', [
            'title' => $set->getName(),
            'set' => $set,
        ]);
    }

    #[Route(path: '/set/{set<\d+>}/edit', name: 'set.edit', methods: ['GET', 'POST'])]
    public function edit(Set $set, Request $request): Response {
        $form = $this->createFormBuilder($set)
            ->add('name', FormType\TextType::class, ['required' => true])
            ->add('save', FormType\SubmitType::class, ['label' => 'Save'])
            ->add('delete', FormType\SubmitType::class, ['label' => 'Delete'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                return $this->redirectToRoute('set.delete', ['set' => $set->getId()]);
            }

            $set = $form->getData();
            $this->entityManager->persist($set);
            $this->entityManager->flush();
            $this->addFlash(LogLevel::NOTICE, "Set \"{$set->getName()}\" updated.");
        }

        return $this->render('set/edit.html.twig', [
            'title' => "Edit set \"{$set->getName()}\"",
            'set' => $set,
            'form' => $form,
        ]);
    }

    #[Route(path: '/set/{set<\d+>}/delete', name:'set.delete' , methods: ['GET', 'POST'])]
    public function delete(Set $set, Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('delete', FormType\SubmitType::class, ['label' => 'Delete'])
            ->add('cancel', FormType\SubmitType::class, ['label' => 'Cancel'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('set.edit', ['set' => $set->getId()]);
            }

            $this->entityManager->remove($set);
            $this->entityManager->flush();
            $this->addFlash(LogLevel::NOTICE, "Set \"{$set->getName()}\" deleted.");
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('set/edit.html.twig', [
            'title' => "Delete set \"{$set->getName()}\"? This action cannot be undone.",
            'form' => $form,
        ]);
    }

    #[Route(path: '/set/{set<\d+>}/videos', name: 'set.videos', methods: 'GET')]
    public function setVideos(
        Set $set,
        Request $request,
        FormFactoryInterface $formFactory,
        VideoFilterFactory $filterFactory,
        VideoSearcher $searcher,
    ): Response
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = $formFactory->createNamed('', VideosSetSearchForm::class);
        $form->handleRequest($request);

        $filters = $filterFactory->createFromForm($form, $set);
        if ($filters->isReset) {
            return $this->redirectToRoute('set.videos', ['set' => $set->getId()]);
        }

        return $this->render('set/videos.html.twig', [
            'title' => $filters->isEmpty() ? "All Videos in \"{$set->getName()}\"" : "Search Results in \"{$set->getName()}\"",
            'form' => $form,
            'videos' => $searcher->filter($filters),
            'set' => $set,
        ]);
    }

    #[Route(path: '/set/{set<\d+>}/videos', methods: 'POST')]
    public function updateVideos(
        Set $set,
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

        return $this->redirectToRoute('set.videos', ['set' => $set->getId()]);
    }

    #[Route(path: '/set/{set<\d+>}/videos/find', name:'set.videos.find' , methods: 'GET')]
    public function findVideos(
        Set $set,
        YouTubeSearcher $searcher,
        Request $request,
    ): Response
    {
        $title = 'Search for Videos';
        $result = null;
        /** @var \App\Entity\Video[] $videos */
        $videos = [];

        $search = $this->createForm(FindVideoForm::class);

        $search->handleRequest($request);
        if ($search->isSubmitted() && $search->isValid()) {
            $query = $search->get('query')->getData();
            $title = "Search results for \"$query\"";

            $video_repository = $this->entityManager->getRepository(Video::class);
            foreach ($searcher->search($query)->results() as $result) {
                $video = $video_repository->findOneBy(['videoId' => $result->getId()->getVideoId()]);

                if ($video && $video->isHidden()) {
                    continue;
                }

                if ($video && $video->inSet($set)) {
                    continue;
                }

                if ($video) {
                    $videos[] = $video;
                    continue;
                }

                $video = new Video()
                    ->setVideoId($result->getId()->getVideoId())
                    ->addSet($set)
                    ->setTitle($result->getSnippet()->getTitle())
                    ->setHidden(false)
                    ->setPublished(DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $result->getSnippet()->getPublishedAt()));
                $this->entityManager->persist($video);
                $videos[] = $video;
            }
            $this->entityManager->flush();
        }

        return $this->render('set/find.html.twig', [
            'title' => $title,
            'search' => $search,
            'result' => $result,
            'videos' => $videos,
            'set' => $set,
        ]);
    }

    #[Route(path: '/set/{set<\d+>}/videos/find', methods: 'POST')]
    public function findVideosActions(Set $set, Request $request): Response
    {
        $video_repository = $this->entityManager->getRepository(Video::class);

        $added = 0;
        $hidden = 0;

        foreach ($request->request->all() as $id => $action) {
            $video = $video_repository->findOneBy(['videoId' => $id]);

            switch ($action) {
                case 'add':
                    $video->addSet($set);
                    $added++;
                    break;

                case 'hide':
                    $video->setHidden(true);
                    $hidden++;
                    break;
            }
        }
        $this->entityManager->flush();

        $this->addFlash(LogLevel::NOTICE, "$added video(s) added to set \"{$set->getName()}.");
        $this->addFlash(LogLevel::NOTICE, "$hidden video(s) hidden.");

        return $this->redirectToRoute('set.videos.find', ['set' => $set->getId()]);
    }

}
