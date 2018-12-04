<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Projet;
use App\Entity\Site;
use App\Entity\Utilisateur;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/activite")
 */
class ActiviteController extends AbstractController
{
    /**
     * @Route("/index", name="activite_index", methods="GET")
     */
    public function index(ActiviteRepository $activiteRepository) : Response
    {
        return $this->render('activite/index.html.twig', ['activites' => $activiteRepository->findAll()]);
    }

    /**
     * @Route("/new", name="activite_new", methods="GET|POST")
     */
    public function new(Request $request) : Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('activite/new.html.twig', [
            'activite' => $activite,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="activite_show", methods="GET")
     */
    public function show(Activite $activite) : Response
    {
        return $this->render('activite/show.html.twig', ['activite' => $activite]);
    }

    /**
     * @Route("/{id}/edit", name="activite_edit", methods="GET|POST")
     */
    public function edit(Request $request, Activite $activite) : Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('activite/edit.html.twig', [
            'activite' => $activite,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="activite_delete", methods="DELETE")
     */
    public function delete(Request $request, Activite $activite) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $activite->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($activite);
            $em->flush();
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/add", name="activite_add", methods="GET|POST")
     */
    public function add(Request $request) : Response
    {
        if ($request->isXmlHttpRequest()) {
            $date = $request->request->get('date');
            $temps = $request->request->get('temps');
            $temps = (intval($temps) < 0 || $temps == "") ? 0 : $temps;
            $site = $request->request->get('site');
            $projet = $request->request->get('projet');
            $tache = $request->request->get('tache');
            $utilisateur = $request->request->get('utilisateur');

            $exp = explode("/", $date);
            $date = ($date != "" && strlen($exp[2])) != 4 ? "" : $date;

            $em = $this->getDoctrine();
            if ($date != "" && $site != "" && $projet != "" && $tache != "") {
                $activite = new Activite();
                $activite->setDate(\DateTime::createFromFormat("d/m/Y H:i:s", $date . " 00:00:00"));
                $activite->setTemps(intval($temps));
                $activite->setSite($em->getRepository(Site::class)->findOneBy(['id' => $site]));
                $activite->setProjet($em->getRepository(Projet::class)->findOneBy(['id' => $projet]));
                $activite->setTache($tache);
                $activite->setUtilisateur($em->getRepository(Utilisateur::class)->findOneBy(['id' => $utilisateur]));

                $em->getManager()->persist($activite);
                $em->getManager()->flush();
            
                return new JsonResponse(1);
            }

            return new JsonResponse(0);
        }
    }

}
