<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Activite;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/projet")
 */
class ProjetController extends AbstractController
{
    private function getAvancement($projet){
        $estime = $projet->getChargeEstime();
        $query = $projet->getActivites();
      
       $total = 0;
       foreach($query as $q){
           $total += $q->getTemps();
       }
       $ret = $estime > 0 ? $total * 100 / $estime : 0;
       return $ret;
   }


    /**
     * @Route("/", name="projet_index", methods="POST|GET")
     */
    public function index(Request $request, ProjetRepository $projetRepository): Response
    {   

        if ($request->isXmlHttpRequest()) {
            $current = $request->request->get('current');
            $rowCount = $request->request->get('rowCount');
            $searchPhrase = $request->request->get('searchPhrase');
            $sort = $request->request->get('sort');

            $projets = $projetRepository->findByFilter( $sort, $searchPhrase);

            if ($searchPhrase != "" ) {
                $count = count($projets->getQuery()->getResult());
            } else {
                $count = $projetRepository->compte();
            }


            if ($rowCount != -1) {
                $min = ($current - 1) * $rowCount;
                $max = $rowCount;

                $projets->setMaxResults($max)
                    ->setFirstResult($min);
            }
            $projets = $projets->getQuery()->getResult();

            $rows = array();
            foreach ($projets as $projet) {
                $row = [
                    "id" => $projet->getId(),
                    "projet" => $projet->getName(),
                    "chargeEstime" => $projet->getChargeEstime(),
                    "progression" => round($this->getAvancement($projet), 1)." %",
                    "avancement" => $projet->getProgression(),
                ];
                array_push($rows, $row);
            }

            $data = array(
                "current" => intval($current),
                "rowCount" => intval($rowCount),
                "rows" => $rows,
                "total" => intval($count)
            );

            return new JsonResponse($data);
        }


        return $this->render('projet/index.html.twig', [
            'controller_name' => 'IndexController',
            ]);
    }

    /**
     * @Route("/new", name="projet_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($projet);
            $em->flush();

            return $this->redirectToRoute('projet_index');
        }

        return $this->render('projet/new.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="projet_show", methods="GET")
     */
    public function show(Projet $projet): Response
    {
        return $this->render('projet/show.html.twig', ['projet' => $projet]);
    }

    /**
     * @Route("/{id}/edit", name="projet_edit", methods="GET|POST")
     */
    public function edit(Request $request, Projet $projet): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="projet_delete", methods="DELETE")
     */
    public function delete(Request $request, Projet $projet): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($projet);
            $em->flush();
        }

        return $this->redirectToRoute('projet_index');
    }
}
