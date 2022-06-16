<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Form\ConferenceFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    #[Route('/', name: 'app_conference')]
    public function index(ConferenceRepository $conferenceRepo, Request $request): Response
    {
        $years = $conferenceRepo->getListYear();
        $citys = $conferenceRepo->getListCity();

        $year_search = $request->query->get('year_search', '');
        $city_search = $request->query->get('city_search', '');

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $conferenceRepo->getConferencePaginator($year_search, $city_search, $offset);

        return $this->render('conference/index.html.twig', [
            'conferences' => $paginator,
            'previous' => $offset - ConferenceRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + ConferenceRepository::PAGINATOR_PER_PAGE),
            'years' => $years,
            'year_search' => $year_search,
            'citys' => $citys,
            'city_search' => $city_search,
        ]);
    }

    #[Route('/conference/newConference', name: 'newConference')]
    public function newConference(ConferenceRepository $conferenceRepo, Request $request, string $photoDir): Response {
        $conference = new Conference();

        return $this->afficheFormulaire($conference, $conferenceRepo, $request, $photoDir);
    }

    #[Route('/conference/{id}/modify', name: 'modifyConference')]
    public function modifyConference(Conference $conference, ConferenceRepository $conferenceRepo, Request $request, string $photoDir): Response {
        return $this->afficheFormulaire($conference, $conferenceRepo, $request, $photoDir);
    }

    private function afficheFormulaire(Conference $conference, ConferenceRepository $conferenceRepo, Request $request, string $photoDir) {
        $form = $this->createForm(ConferenceFormType::class, $conference);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                }
                $conference->setPhotoFilename($filename);
            }
            $conferenceRepo->add($conference, true);
            
            return $this->redirectToRoute('ficheConference', [ 'id' => $conference->getId()]);
        }

        return $this->render('conference/newconference.html.twig', [
            'conference' => $conference,
            'form_conference' => $form->createView(),
        ]);
    }
   
    #[Route('/conference/{id}/delete', name: 'delConference')]
    public function delConference(Conference $conference, ConferenceRepository $conferenceRepo): Response {
        $conferenceRepo->remove($conference, true);
        return $this->redirectToRoute('app_conference');
}

    #[Route('/conference/{id}', name: 'ficheConference')]
    public function show(Conference $conference, CommentRepository $commentRepo, Request $request): Response {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepo->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/conference/{id}/newComment', name: 'conference_newcomment')]
    public function newComment(Conference $conference, Request $request, CommentRepository $commentRepo, ManagerRegistry $doctrine): Response {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $commentRepo->add($comment, true);

            // Ou si on utilise le manager de doctrine
            // $manager = $doctrine->getManager();
            // $manager->persist($comment);
            // $manager->flush();
            
            return $this->redirectToRoute('ficheConference', [ 'id' => $conference->getId()]);
        }

        return $this->render('conference/newcomment.html.twig', [
            'conference' => $conference,
            'form_comment' => $form->createView(),
        ]);
    }
}
